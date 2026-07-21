<?php

namespace App\Mail;

use App\Models\PenaltyRule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationPaymentReport extends Mailable
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
        $penaltyRule = $this->emailData['penalty_rule'] ?? PenaltyRule::forClientOrDefaults((int) $employee->client_id);

        $pdf = Pdf::loadView('school::reports.vacation-payment-pdf', [
            'client' => $this->emailData['client'],
            'period_label' => $periodLabel,
            'employees_data' => [['employee' => $employee, 'result' => $this->emailData['result']]],
            'penalty_rule' => $penaltyRule,
            'export_date' => now(),
        ]);
        $pdf->setPaper('A4', 'portrait');

        $safeCode = strtolower(str_replace([' ', '/'], '_', $employee->emp_code));
        $pdfFileName = "fiche-heures-vacation-{$safeCode}-" . now()->format('Y-m-d') . '.pdf';

        return $this
            ->subject("Fiche des heures de vacation — {$periodLabel}")
            ->view('emails.vacation-payment')
            ->with([
                'employeeName' => $employee->full_name,
                'periodLabel' => $periodLabel,
                'result' => $this->emailData['result'],
            ])
            ->attachData($pdf->output(), $pdfFileName, ['mime' => 'application/pdf']);
    }
}
