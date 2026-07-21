<?php

namespace App\Console\Commands;

use App\Mail\VacationPaymentReport;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\PenaltyRule;
use App\Models\Setting;
use App\Services\VacationCalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVacationPaymentReports extends Command
{
    protected $signature = 'vacation:send-payment-reports';

    protected $description = "Envoyer à chaque enseignant sa fiche des heures de vacation du mois précédent (1er du mois 09h)";

    public function handle()
    {
        $this->info("🚀 Envoi des fiches des heures de vacation...");

        $previousMonth = Carbon::now()->subMonthNoOverflow();
        $service = new VacationCalculationService();
        $totalSent = 0;

        foreach (Client::all() as $client) {
            $settings = Setting::where('client_id', $client->id)->first();

            if (!$settings || !$settings->email_employees_is_active) {
                continue;
            }

            $penaltyRule = PenaltyRule::forClientOrDefaults($client->id);
            $employees = $this->teachersForClient($client->id);

            foreach ($employees as $employee) {
                if (!filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $result = $service->calculateMonth($employee, $previousMonth->year, $previousMonth->month);

                if (empty($result['details'])) {
                    continue;
                }

                try {
                    Mail::to($employee->email)->send(new VacationPaymentReport([
                        'employee' => $employee,
                        'client' => $client,
                        'period_label' => $previousMonth->locale('fr')->isoFormat('MMMM YYYY'),
                        'result' => $result,
                        'penalty_rule' => $penaltyRule,
                    ]));

                    $totalSent++;
                    $this->info("✅ Envoyé à {$employee->email}");
                } catch (\Exception $e) {
                    Log::error("Erreur envoi fiche vacation paie à {$employee->email}: " . $e->getMessage());
                    $this->error("❌ Erreur pour {$employee->email}: " . $e->getMessage());
                }
            }
        }

        $this->info("🏁 Terminé : {$totalSent} email(s) envoyé(s).");
    }

    private function teachersForClient(int $clientId)
    {
        $employeeIds = EmployeeSchedule::where('client_id', $clientId)
            ->where('schedule_type', 'fixe')
            ->whereNotNull('class_id')
            ->distinct()
            ->pluck('employee_id');

        return Employee::whereIn('id', $employeeIds)->orderBy('first_name')->get();
    }
}
