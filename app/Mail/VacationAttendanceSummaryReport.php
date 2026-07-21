<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationAttendanceSummaryReport extends Mailable
{
    use Queueable, SerializesModels;

    public array $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function build(): static
    {
        $periodLabel = $this->emailData['period_label'];
        $employeesData = $this->emailData['employees_data'];

        $pdf = Pdf::loadView('school::reports.attendance-summary-pdf', [
            'client' => $this->emailData['client'],
            'period_label' => $periodLabel,
            'employees_data' => $employeesData,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $pdfFileName = 'point-assiduite-' . now()->format('Y-m-d') . '.pdf';

        $totalAmount = collect($employeesData)->sum(fn ($e) => $e['result']['amount_to_pay']);

        return $this
            ->subject("Point d'assiduité et montant des vacations — {$periodLabel}")
            ->view('emails.vacation-attendance-summary')
            ->with([
                'clientName' => $this->emailData['client']->raison_sociale ?? '',
                'periodLabel' => $periodLabel,
                'teacherCount' => count($employeesData),
                'totalAmount' => $totalAmount,
            ])
            ->attachData($pdf->output(), $pdfFileName, ['mime' => 'application/pdf']);
    }
}
