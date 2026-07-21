<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\PenaltyRule;
use App\Models\SchoolClass;
use App\Services\VacationCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jeux d'essai (Lot 10) pour les rapports §8 : génération réelle des 3 PDF
 * (présence & ponctualité, heures de vacation, point d'assiduité consolidé)
 * à partir du scénario chiffré de M. AHO (§6.1.1 / §8.1 / §8.2 / §8.3).
 */
class VacationReportTest extends TestCase
{
    use RefreshDatabase;

    private function seedAhoScenario(): array
    {
        $client = Client::create(['raison_sociale' => 'École Test', 'rccm' => 'RCCM-TEST', 'email' => 'ecole@test.local']);
        $employee = Employee::create(['client_id' => $client->id, 'emp_code' => 'AHO', 'first_name' => 'M.', 'last_name' => 'AHO', 'email' => 'aho@test.local', 'status' => 'active']);

        $class6eA = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '6e A', 'hourly_rate' => 1700, 'status' => true]);
        $class5eB = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => '5e B', 'hourly_rate' => 1800, 'status' => true]);
        $classTleD = SchoolClass::create(['client_id' => $client->id, 'level' => 'Secondaire', 'name' => 'Tle D', 'hourly_rate' => 2000, 'status' => true]);

        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class6eA->id, 'subject' => 'Maths', 'schedule_type' => 'fixe', 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '10:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $class5eB->id, 'subject' => 'Maths', 'schedule_type' => 'fixe', 'day_of_week' => 3, 'start_time' => '14:00', 'end_time' => '16:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);
        EmployeeSchedule::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'class_id' => $classTleD->id, 'subject' => 'SPCT', 'schedule_type' => 'fixe', 'day_of_week' => 5, 'start_time' => '07:00', 'end_time' => '11:00', 'repeat_weekly' => true, 'is_working_day' => true, 'is_active' => true]);

        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-01', 'check_in' => '2026-06-01 07:47:00', 'check_out' => '2026-06-01 12:15:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-03', 'check_in' => '2026-06-03 14:07:00', 'check_out' => '2026-06-03 16:10:00']);
        DailyAttendance::create(['client_id' => $client->id, 'employee_id' => $employee->id, 'emp_code' => $employee->emp_code, 'attendance_date' => '2026-06-05', 'check_in' => '2026-06-05 07:03:00', 'check_out' => '2026-06-05 10:58:00']);

        return compact('client', 'employee');
    }

    private function assertIsPdf(string $output): void
    {
        $this->assertGreaterThan(1000, strlen($output));
        $this->assertStringStartsWith('%PDF', $output);
    }

    public function test_presence_ponctualite_pdf_renders_for_worked_example(): void
    {
        ['client' => $client, 'employee' => $employee] = $this->seedAhoScenario();
        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-05'));

        $pdf = Pdf::loadView('school::reports.presence-ponctualite-pdf', [
            'client' => $client,
            'period_label' => 'Juin 2026',
            'employees_data' => [['employee' => $employee, 'result' => $result]],
            'export_date' => now(),
        ]);

        $this->assertIsPdf($pdf->output());
    }

    public function test_vacation_payment_pdf_renders_for_worked_example(): void
    {
        ['client' => $client, 'employee' => $employee] = $this->seedAhoScenario();
        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-05'));
        $penaltyRule = PenaltyRule::forClientOrDefaults($client->id);

        $this->assertSame(14623, $result['amount_to_pay']);

        $pdf = Pdf::loadView('school::reports.vacation-payment-pdf', [
            'client' => $client,
            'period_label' => 'Juin 2026',
            'employees_data' => [['employee' => $employee, 'result' => $result]],
            'penalty_rule' => $penaltyRule,
            'export_date' => now(),
        ]);

        $this->assertIsPdf($pdf->output());
    }

    public function test_attendance_summary_pdf_renders_for_all_teachers(): void
    {
        ['client' => $client, 'employee' => $employee] = $this->seedAhoScenario();
        $result = (new VacationCalculationService())->calculateMonth($employee, 2026, 6, \Carbon\Carbon::parse('2026-06-05'));

        $pdf = Pdf::loadView('school::reports.attendance-summary-pdf', [
            'client' => $client,
            'period_label' => 'Juin 2026',
            'employees_data' => [['employee' => $employee, 'result' => $result]],
            'export_date' => now(),
        ]);

        $this->assertIsPdf($pdf->output());
    }
}
