<?php

namespace App\Console\Commands;

use App\Mail\VacationAttendanceSummaryReport;
use App\Models\Client;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Setting;
use App\Services\VacationCalculationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVacationAttendanceSummary extends Command
{
    protected $signature = 'vacation:send-attendance-summary';

    protected $description = "Envoyer à la direction le point d'assiduité consolidé du mois précédent (1er du mois 10h)";

    public function handle()
    {
        $this->info("🚀 Envoi du point d'assiduité consolidé (direction)...");

        $previousMonth = Carbon::now()->subMonthNoOverflow();
        $service = new VacationCalculationService();
        $totalSent = 0;

        foreach (Client::all() as $client) {
            $settings = Setting::where('client_id', $client->id)->first();

            if (!$settings || !$settings->email_is_active || empty($settings->email)
                || !filter_var(trim($settings->email), FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $employees = $this->teachersForClient($client->id);
            $employeesData = [];

            foreach ($employees as $employee) {
                $result = $service->calculateMonth($employee, $previousMonth->year, $previousMonth->month);

                if (empty($result['details'])) {
                    continue;
                }

                $employeesData[] = ['employee' => $employee, 'result' => $result];
            }

            if (empty($employeesData)) {
                continue;
            }

            try {
                Mail::to(trim($settings->email))->send(new VacationAttendanceSummaryReport([
                    'client' => $client,
                    'period_label' => $previousMonth->locale('fr')->isoFormat('MMMM YYYY'),
                    'employees_data' => $employeesData,
                ]));

                $totalSent++;
                $this->info("✅ Envoyé à {$settings->email} pour le client {$client->id}");
            } catch (\Exception $e) {
                Log::error("Erreur envoi point d'assiduité vacation client {$client->id}: " . $e->getMessage());
                $this->error("❌ Erreur pour le client {$client->id}: " . $e->getMessage());
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
