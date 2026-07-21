<?php

namespace Vendor\School\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\PenaltyRule;
use App\Services\VacationCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class VacationReportController extends Controller
{
    public function index(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->first();

        $employees = Employee::where('client_id', $client->id ?? 0)
            ->orderBy('first_name')
            ->get();

        return view('school::reports.index', compact('employees'));
    }

    public function presencePdf(Request $request)
    {
        [$client, $employees, $year, $month] = $this->resolveReportInputs($request);

        $service = new VacationCalculationService();
        $data = $this->buildReportData($employees, $year, $month, $service);

        $pdf = Pdf::loadView('school::reports.presence-ponctualite-pdf', [
            'client' => $client,
            'period_label' => $this->periodLabel($year, $month),
            'employees_data' => $data,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('fiche-presence-ponctualite-' . $year . '-' . $month . '.pdf');
    }

    public function paymentPdf(Request $request)
    {
        [$client, $employees, $year, $month] = $this->resolveReportInputs($request);

        $service = new VacationCalculationService();
        $data = $this->buildReportData($employees, $year, $month, $service);
        $penaltyRule = PenaltyRule::forClientOrDefaults($client->id ?? 0);

        $pdf = Pdf::loadView('school::reports.vacation-payment-pdf', [
            'client' => $client,
            'period_label' => $this->periodLabel($year, $month),
            'employees_data' => $data,
            'penalty_rule' => $penaltyRule,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('fiche-heures-vacation-' . $year . '-' . $month . '.pdf');
    }

    public function attendanceSummaryPdf(Request $request)
    {
        $client = Client::where('user_id', auth()->id())->first();

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        [$year, $month] = explode('-', $validated['month']);

        $employees = Employee::where('client_id', $client->id ?? 0)
            ->orderBy('first_name')
            ->get();

        $service = new VacationCalculationService();
        $data = $this->buildReportData($employees, (int) $year, (int) $month, $service);

        $pdf = Pdf::loadView('school::reports.attendance-summary-pdf', [
            'client' => $client,
            'period_label' => $this->periodLabel((int) $year, (int) $month),
            'employees_data' => $data,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('point-assiduite-' . $year . '-' . $month . '.pdf');
    }

    private function resolveReportInputs(Request $request): array
    {
        $client = Client::where('user_id', auth()->id())->first();

        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'employee_id' => 'nullable|string',
        ]);

        [$year, $month] = explode('-', $validated['month']);

        $employeeId = $request->input('employee_id', 'all');

        $query = Employee::where('client_id', $client->id ?? 0);
        if ($employeeId !== 'all' && $employeeId !== '') {
            $query->where('id', $employeeId);
        }
        $employees = $query->orderBy('first_name')->get();

        return [$client, $employees, (int) $year, (int) $month];
    }

    private function buildReportData($employees, int $year, int $month, VacationCalculationService $service): array
    {
        $data = [];

        foreach ($employees as $employee) {
            $result = $service->calculateMonth($employee, $year, $month);

            if (empty($result['details'])) {
                continue;
            }

            $data[] = [
                'employee' => $employee,
                'result' => $result,
            ];
        }

        return $data;
    }

    private function periodLabel(int $year, int $month): string
    {
        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        return ($months[$month] ?? $month) . ' ' . $year;
    }
}
