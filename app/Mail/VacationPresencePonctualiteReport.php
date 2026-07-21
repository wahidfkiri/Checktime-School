<?php

namespace App\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationPresencePonctualiteReport extends Mailable
{
    use Queueable, SerializesModels;

    public array $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function build(): static
    {
        $employee = $this->emailData['employee'];
        $periodLabel = $this->emailData['period_label'];

        $pdf = Pdf::loadView('school::reports.presence-ponctualite-pdf', [
            'client' => $this->emailData['client'],
            'period_label' => $periodLabel,
            'employees_data' => [['employee' => $employee, 'result' => $this->emailData['result']]],
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $safeCode = strtolower(str_replace([' ', '/'], '_', $employee->emp_code));
        $pdfFileName = "fiche-presence-ponctualite-{$safeCode}-" . now()->format('Y-m-d') . '.pdf';

        return $this
            ->subject("Fiche de présence et ponctualité — {$periodLabel}")
            ->view('emails.vacation-presence-ponctualite')
            ->with([
                'employeeName' => $employee->full_name,
                'periodLabel' => $periodLabel,
                'result' => $this->emailData['result'],
            ])
            ->attachData($pdf->output(), $pdfFileName, ['mime' => 'application/pdf']);
    }
}
