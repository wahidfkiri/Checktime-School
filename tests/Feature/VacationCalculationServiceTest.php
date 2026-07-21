<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\DailyAttendance;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PenaltyRule;
use App\Models\SchoolClass;
use App\Services\VacationCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jeux d'essai (Lot 10) rejouant les scénarios chiffrés du dossier de conception :
 * §6.1.1 (heures validées de M. AHO) et §8.2 (fiche des heures de vacation, montant total 14 623 F CFA).
 */
class VacationCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeClient(): Client
    {
        return Client::create([
            'raison_sociale' => 'École Test',
            'rccm' => 'RCCM-TEST',
            'email' => 'ecole@test.local',
        ]);
    }

    private function makeTeacher(Client $client, string $empCode = 'AHO'): Employee
    {
        return Employee::create([
            'client_id' => $client->id,
            'emp_code' => $empCode,
            'first_name' => 'M.',
            'last_name' => 'AHO',
            'email' => strtolower($empCode) . '@test.local',
            'status' => 'active',
        ]);
    }

    /**
     * §6.1.1 + §8.2 : M. AHO, juin 2026 — Lundi 6e A, Mercredi 5e B, Vendredi Tle D.
     * Montant total attendu : 3 400 + 3 390 + 7 833 = 14 623 F CFA. Cumul retard : 12 min. 0 pénalité.
     */
    public function test_engine_matches_conception_document_worked_example(): void
    {
        $client = $this->makeClient();
        $employee = $this->makeTeacher($client);

        $class6eA = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1700, 'status' => true]);
        $class5eB = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '5e B', 'hourly_rate' => 1800, 'status' => true]);
        $classTleD = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => 'Tle D', 'hourly_rate' => 2000, 'status' => true]);

        // Juin 2026 : le 1er est un lundi (confirmé), donc 1=lundi, 3=mercredi, 5=vendredi.
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class6eA->id, 'subject' => 'Maths', 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class5eB->id, 'subject' => 'Maths', 'schedule_type' => 'fixe', 'day_of_week' => 3, 'start_time' => '14:00', 'end_time' => '16:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $classTleD->id, 'subject' => 'SPCT', 'schedule_type' => 'fixe', 'day_of_week' => 5, 'start_time' => '07:00', 'end_time' => '11:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-01', 'check_in' => '2026-06-01 07:47:00', 'check_out' => '2026-06-01 12:15:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-03', 'check_in' => '2026-06-03 14:07:00', 'check_out' => '2026-06-03 16:10:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-05', 'check_in' => '2026-06-05 07:03:00', 'check_out' => '2026-06-05 10:58:00']);

        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-05'));

        $this->assertSame(14623, $result['total_amount']);
        $this->assertSame(12, $result['total_late_minutes']);
        $this->assertSame(0, $result['unjustified_absences_count']);
        $this->assertSame(0, $result['absence_penalty']);
        $this->assertSame(0, $result['late_penalty']);
        $this->assertSame(14623, $result['amount_to_pay']);

        [$lundi, $mercredi, $vendredi] = $result['details'];
        $this->assertSame(0, $lundi['late_minutes']);
        $this->assertSame(0, $lundi['early_leave_minutes']);
        $this->assertSame(120, $lundi['validated_minutes']);
        $this->assertSame(3400, $lundi['amount']);

        $this->assertSame(7, $mercredi['late_minutes']);
        $this->assertSame(0, $mercredi['early_leave_minutes']);
        $this->assertSame(113, $mercredi['validated_minutes']);
        $this->assertSame(3390, $mercredi['amount']);

        $this->assertSame(3, $vendredi['late_minutes']);
        $this->assertSame(2, $vendredi['early_leave_minutes']);
        $this->assertSame(235, $vendredi['validated_minutes']);
        $this->assertSame(7833, $vendredi['amount']);
    }

    public function test_unjustified_absence_is_counted_and_penalized(): void
    {
        $client = $this->makeClient();
        $employee = $this->makeTeacher($client);
        $class = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1000, 'status' => true]);

        // Deux vacations le lundi (juin 2026 a 4 lundis) : une assurée, une non.
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class->id, 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-01', 'check_in' => '2026-06-01 08:00:00', 'check_out' => '2026-06-01 10:00:00']);
        // Pas de pointage le 08/06 (lundi suivant) => absence non justifiée.

        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-08'));

        $this->assertSame(1, $result['unjustified_absences_count']);
        $this->assertSame(2000, $result['total_amount']); // 2h à 1000 F CFA
        // Seuil par défaut = 1 absence => 1 pénalité de 7% du montant total = 140
        $this->assertSame(140, $result['absence_penalty']);
        $this->assertSame(1860, $result['amount_to_pay']);
    }

    public function test_leave_justifies_absence_without_penalty(): void
    {
        $client = $this->makeClient();
        $employee = $this->makeTeacher($client);
        $class = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1000, 'status' => true]);
        $leaveType = LeaveType::forceCreate(['client_id' => $client->id, 'name' => 'Congé annuel']);

        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class->id, 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        Leave::create([
            'client_id' => $client->id,
            'employee_id' => $employee->id,
            'type_id' => $leaveType->id,
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-01',
            'status' => 'approved',
        ]);

        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-01'));

        $this->assertSame(0, $result['unjustified_absences_count']);
        $this->assertSame(0, $result['absence_penalty']);
        $this->assertSame('absence_justifiee', $result['details'][0]['status']);
    }

    public function test_late_penalty_applies_per_threshold_reached(): void
    {
        $client = $this->makeClient();
        $employee = $this->makeTeacher($client);
        $class = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1000, 'status' => true]);

        // Palier par défaut = 30 min. On cumule 65 min de retard sur 2 vacations => 2 paliers atteints.
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class->id, 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class->id, 'schedule_type' => 'fixe', 'day_of_week' => 3, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-01', 'check_in' => '2026-06-01 08:35:00', 'check_out' => '2026-06-01 10:00:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-03', 'check_in' => '2026-06-03 08:30:00', 'check_out' => '2026-06-03 10:00:00']);

        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-03'));

        $this->assertSame(65, $result['total_late_minutes']);
        // 2 paliers de 30 min atteints (65 / 30 = 2) => 2 x 5% du montant total
        $expectedLatePenalty = (int) round(2 * 0.05 * $result['total_amount']);
        $this->assertSame($expectedLatePenalty, $result['late_penalty']);
    }

    public function test_presence_and_punctuality_rates(): void
    {
        $client = $this->makeClient();
        $employee = $this->makeTeacher($client);
        $class = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1000, 'status' => true]);

        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class->id, 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        // Lundi 1 : présent et à l'heure. Lundi 8 : présent mais en retard. Lundi 15 : absent.
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-01', 'check_in' => '2026-06-01 08:00:00', 'check_out' => '2026-06-01 10:00:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-08', 'check_in' => '2026-06-08 08:10:00', 'check_out' => '2026-06-08 10:00:00']);

        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-15'));

        $this->assertSame(3, $result['planned_count']);
        $this->assertSame(2, $result['present_count']);
        $this->assertSame(1, $result['on_time_count']);
        $this->assertSame(66.7, $result['presence_rate']);
        $this->assertSame(50.0, $result['punctuality_rate']);
    }
}
