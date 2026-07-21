<?php

namespace App\Console\Commands;

use App\Mail\VacationPresencePonctualiteReport;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Setting;
use App\Services\VacationCalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVacationPresenceReports extends Command
{
    protected $signature = 'vacation:send-presence-reports';

    protected $description = "Envoyer à chaque enseignant sa fiche de présence et ponctualité du mois en cours (chaque dimanche 09h)";

    public function handle()
    {
        $this->info("🚀 Envoi des fiches de présence et ponctualité (vacation)...");

        $today = Carbon::now();
        $service = new VacationCalculationService();
        $totalSent = 0;

        foreach (Client::all() as $client) {
            $settings = Setting::where('client_id', $client->id)->first();

            if (!$settings || !$settings->email_employees_is_active) {
                continue;
            }

            $employees = $this->teachersForClient($client->id);

            foreach ($employees as $employee) {
                if (!filter_var($employee->email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                $result = $service->calculateMonth($employee, $today->year, $today->month, $today);

                if (empty($result['details'])) {
                    continue;
                }

                try {
                    Mail::to($employee->email)->send(new VacationPresencePonctualiteReport([
                        'employee' => $employee,
                        'client' => $client,
                        'period_label' => $today->locale('fr')->isoFormat('MMMM YYYY'),
                        'result' => $result,
                    ]));

                    $totalSent++;
                    $this->info("✅ Envoyé à {$employee->email}");
                } catch (\Exception $e) {
                    Log::error("Erreur envoi fiche présence vacation à {$employee->email}: " . $e->getMessage());
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
