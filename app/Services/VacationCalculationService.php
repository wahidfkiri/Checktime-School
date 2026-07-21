<?php

namespace App\Services;

use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\EmployeePermission;
use App\Models\EmployeeSchedule;
use App\Models\Leave;
use App\Models\Mission;
use App\Models\PenaltyRule;
use Carbon\CarbonPeriod;

class VacationCalculationService
{
    /**
     * Calcule les heures validées, montants et pénalités de vacation d'un enseignant
     * pour un mois donné, selon les règles du dossier de conception (§6).
     *
     * @param \Carbon\Carbon|null $until Borne la période au jour indiqué (pour un état
     *   "à date" en cours de mois, ex. l'envoi hebdomadaire du dimanche). Par défaut,
     *   la période couvre le mois entier (état de fin de mois, pour la paie).
     */
    public function calculateMonth(Employee $employee, int $year, int $month, ?\Carbon\Carbon $until = null): array
    {
        $penaltyRule = PenaltyRule::forClientOrDefaults((int) $employee->client_id);

        $vacationsByDay = EmployeeSchedule::where('employee_id', $employee->id)
            ->where('schedule_type', 'fixe')
            ->where('is_active', true)
            ->with('schoolClass')
            ->get()
            ->groupBy('day_of_week');

        $monthStart = sprintf('%04d-%02d-01', $year, $month);
        $monthEnd = sprintf('%04d-%02d-%02d', $year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year));
        $periodEnd = $until && $until->format('Y-m-d') < $monthEnd ? $until->format('Y-m-d') : $monthEnd;

        $period = CarbonPeriod::create($monthStart, $periodEnd);

        $details = [];
        $totalAmount = 0;
        $totalLateMinutes = 0;
        $unjustifiedAbsences = 0;
        $plannedCount = 0;
        $presentCount = 0;
        $onTimeCount = 0;

        foreach ($period as $date) {
            $dayOfWeek = $date->dayOfWeekIso;
            $vacations = $vacationsByDay->get($dayOfWeek, collect());

            if ($vacations->isEmpty()) {
                continue;
            }

            $dateStr = $date->format('Y-m-d');
            $justified = $this->isAbsenceJustified($employee->id, $dateStr);
            $attendance = $justified ? null : DailyAttendance::where('employee_id', $employee->id)
                ->whereDate('attendance_date', $dateStr)
                ->first();

            foreach ($vacations as $vacation) {
                $plannedCount++;

                if ($justified) {
                    $details[] = $this->buildDetail($date, $vacation, 'absence_justifiee');
                    continue;
                }

                if (!$attendance || !$attendance->check_in || !$attendance->check_out) {
                    $unjustifiedAbsences++;
                    $details[] = $this->buildDetail($date, $vacation, 'absence_non_justifiee');
                    continue;
                }

                $detail = $this->computeVacationDetail($date, $vacation, $attendance);
                $totalAmount += $detail['amount'];
                $totalLateMinutes += $detail['late_minutes'] + $detail['early_leave_minutes'];
                $presentCount++;
                $onTimeCount += $detail['late_minutes'] === 0 ? 1 : 0;
                $details[] = $detail;
            }
        }

        $absencePenaltyUnits = intdiv($unjustifiedAbsences, max(1, $penaltyRule->absence_count));
        $absencePenalty = round($absencePenaltyUnits * ($penaltyRule->absence_rate / 100) * $totalAmount);

        $latePenaltyUnits = intdiv($totalLateMinutes, max(1, $penaltyRule->late_minutes));
        $latePenalty = round($latePenaltyUnits * ($penaltyRule->late_rate / 100) * $totalAmount);

        $amountToPay = max(0, $totalAmount - $absencePenalty - $latePenalty);

        return [
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'details' => $details,
            'total_amount' => (int) $totalAmount,
            'total_late_minutes' => $totalLateMinutes,
            'unjustified_absences_count' => $unjustifiedAbsences,
            'absence_penalty' => (int) $absencePenalty,
            'late_penalty' => (int) $latePenalty,
            'amount_to_pay' => (int) $amountToPay,
            'planned_count' => $plannedCount,
            'present_count' => $presentCount,
            'on_time_count' => $onTimeCount,
            'presence_rate' => $plannedCount > 0 ? round(($presentCount / $plannedCount) * 100, 1) : 0.0,
            'punctuality_rate' => $presentCount > 0 ? round(($onTimeCount / $presentCount) * 100, 1) : 0.0,
        ];
    }

    private function computeVacationDetail($date, EmployeeSchedule $vacation, DailyAttendance $attendance): array
    {
        $dateStr = $date->format('Y-m-d');
        $plannedStart = \Carbon\Carbon::parse($dateStr . ' ' . \Carbon\Carbon::parse($vacation->start_time)->format('H:i:s'));
        $plannedEnd = \Carbon\Carbon::parse($dateStr . ' ' . \Carbon\Carbon::parse($vacation->end_time)->format('H:i:s'));
        $actualArrival = \Carbon\Carbon::parse($attendance->check_in);
        $actualDeparture = \Carbon\Carbon::parse($attendance->check_out);

        $plannedMinutes = ($plannedEnd->timestamp - $plannedStart->timestamp) / 60;
        $lateMinutes = max(0, ($actualArrival->timestamp - $plannedStart->timestamp) / 60);
        $earlyLeaveMinutes = max(0, ($plannedEnd->timestamp - $actualDeparture->timestamp) / 60);
        $validatedMinutes = max(0, $plannedMinutes - $lateMinutes - $earlyLeaveMinutes);

        $hourlyRate = $vacation->schoolClass->hourly_rate ?? 0;
        $amount = round(($validatedMinutes / 60) * $hourlyRate);

        $detail = $this->buildDetail($date, $vacation, 'presence');
        $detail['actual_arrival'] = $actualArrival->format('H:i');
        $detail['actual_departure'] = $actualDeparture->format('H:i');
        $detail['late_minutes'] = (int) $lateMinutes;
        $detail['early_leave_minutes'] = (int) $earlyLeaveMinutes;
        $detail['validated_minutes'] = (int) $validatedMinutes;
        $detail['hourly_rate'] = $hourlyRate;
        $detail['amount'] = (int) $amount;

        return $detail;
    }

    private function buildDetail($date, EmployeeSchedule $vacation, string $status): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'day_name' => $vacation->day_name,
            'planned_start' => \Carbon\Carbon::parse($vacation->start_time)->format('H:i'),
            'planned_end' => \Carbon\Carbon::parse($vacation->end_time)->format('H:i'),
            'class_name' => $vacation->schoolClass->name ?? 'N/A',
            'subject' => $vacation->subject,
            'status' => $status,
            'actual_arrival' => null,
            'actual_departure' => null,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'validated_minutes' => 0,
            'hourly_rate' => $vacation->schoolClass->hourly_rate ?? 0,
            'amount' => 0,
        ];
    }

    private function isAbsenceJustified(int $employeeId, string $dateStr): bool
    {
        $onLeave = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->exists();

        if ($onLeave) {
            return true;
        }

        $onMission = Mission::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->exists();

        if ($onMission) {
            return true;
        }

        return EmployeePermission::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->overlappingPeriod($dateStr, $dateStr)
            ->exists();
    }
}
