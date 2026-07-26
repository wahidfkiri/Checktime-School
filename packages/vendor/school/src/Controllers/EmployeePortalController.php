<?php

namespace Vendor\School\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PenaltyRule;
use App\Models\SchoolClass;
use App\Services\VacationCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Espace enseignant : le module school vu par un enseignant (rôle 'employee'),
 * strictement limité à ses propres données (planning, classes en lecture seule,
 * ses deux rapports individuels). Aucune donnée d'un autre enseignant n'est
 * jamais exposée ici.
 */
class EmployeePortalController extends Controller
{
    private function currentEmployee(): Employee
    {
        return Employee::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Planning des vacations de l'enseignant connecté (lecture seule).
     */
    public function index(Request $request)
    {
        $employee = $this->currentEmployee();

        $schedules = $employee->schedules()
            ->where('schedule_type', 'fixe')
            ->where('is_active', true)
            ->with('schoolClass')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('school::employee-portal.index', compact('employee', 'schedules'));
    }

    /**
     * Liste des classes de l'établissement (lecture seule).
     */
    public function classes(Request $request)
    {
        $employee = $this->currentEmployee();

        $classes = SchoolClass::where('client_id', $employee->client_id)
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('school::employee-portal.classes', compact('classes'));
    }

    /**
     * Écran de sélection du mois pour ses propres rapports.
     */
    public function reports(Request $request)
    {
        return view('school::employee-portal.reports');
    }

    public function presencePdf(Request $request)
    {
        $employee = $this->currentEmployee();
        [$year, $month, $label] = $this->resolvePeriod($request);

        $result = (new VacationCalculationService())->calculateMonth($employee, $year, $month);

        $pdf = Pdf::loadView('school::reports.presence-ponctualite-pdf', [
            'client' => $employee->client,
            'period_label' => $label,
            'employees_data' => [['employee' => $employee, 'result' => $result]],
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ma-fiche-presence-ponctualite-' . $year . '-' . $month . '.pdf');
    }

    public function paymentPdf(Request $request)
    {
        $employee = $this->currentEmployee();
        [$year, $month, $label] = $this->resolvePeriod($request);

        $result = (new VacationCalculationService())->calculateMonth($employee, $year, $month);
        $penaltyRule = PenaltyRule::forClientOrDefaults($employee->client_id);

        $pdf = Pdf::loadView('school::reports.vacation-payment-pdf', [
            'client' => $employee->client,
            'period_label' => $label,
            'employees_data' => [['employee' => $employee, 'result' => $result]],
            'penalty_rule' => $penaltyRule,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('ma-fiche-heures-vacation-' . $year . '-' . $month . '.pdf');
    }

    private function resolvePeriod(Request $request): array
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        [$year, $month] = explode('-', $validated['month']);

        $months = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];

        $label = ($months[(int) $month] ?? $month) . ' ' . $year;

        return [(int) $year, (int) $month, $label];
    }
}
