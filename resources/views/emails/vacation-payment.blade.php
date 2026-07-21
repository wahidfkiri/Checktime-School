<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche des heures de vacation</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #333; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
        .header { text-align: center; padding: 20px 0; border-bottom: 3px solid #1a5276; }
        .header h1 { color: #1a5276; font-size: 22px; margin: 0; }
        .period { background-color: #e8edf9; padding: 12px; text-align: center; margin: 20px 0; border-radius: 6px; }
        .amount-box { background-color: #eaf7ea; border-left: 4px solid #2ecc71; padding: 16px; margin: 20px 0; border-radius: 4px; text-align: center; }
        .amount-value { font-size: 26px; font-weight: bold; color: #198754; }
        .message-box { background-color: #fff8e7; border-left: 4px solid #f0dbaa; padding: 12px 16px; margin: 20px 0; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 11px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Fiche des heures de vacation</h1>
        </div>

        <p>Bonjour <strong>{{ $employeeName }}</strong>,</p>

        <p>Veuillez trouver ci-joint votre fiche de pointage des heures de vacation pour <strong>{{ $periodLabel }}</strong>.</p>

        <div class="period">
            📅 <strong>Mois :</strong> {{ $periodLabel }}
        </div>

        <div class="amount-box">
            <div class="amount-value">{{ number_format($result['amount_to_pay'], 0, ',', ' ') }} F CFA</div>
            <div>Montant total à payer</div>
        </div>

        <div class="message-box">
            📎 <strong>Document joint</strong><br>
            Le détail des vacations, montants et éventuelles pénalités figure dans le PDF ci-joint.
        </div>

        <p>
            Cordialement,<br>
            <strong>Direction</strong>
        </p>

        <div class="footer">
            <p>Cet email a été généré automatiquement. Merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
