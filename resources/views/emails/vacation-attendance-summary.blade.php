<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Point d'assiduité</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; line-height: 1.5; color: #333; background-color: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; }
        .header { text-align: center; padding: 20px 0; border-bottom: 3px solid #1a5276; }
        .header h1 { color: #1a5276; font-size: 22px; margin: 0; }
        .period { background-color: #e8edf9; padding: 12px; text-align: center; margin: 20px 0; border-radius: 6px; }
        .stats-grid { display: table; width: 100%; margin: 20px 0; border-collapse: collapse; }
        .stat-item { display: table-cell; width: 50%; text-align: center; padding: 12px 8px; border: 1px solid #ddd; background-color: #f9f9f9; }
        .stat-value { font-size: 24px; font-weight: bold; color: #1a5276; }
        .stat-label { font-size: 12px; color: #666; }
        .message-box { background-color: #eaf7ea; border-left: 4px solid #2ecc71; padding: 12px 16px; margin: 20px 0; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 11px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Point d'assiduité — Vacations</h1>
        </div>

        <p>Bonjour,</p>

        <p>Veuillez trouver ci-joint le point consolidé d'assiduité, de ponctualité et de montant total des heures de vacation pour <strong>{{ $clientName }}</strong>, période <strong>{{ $periodLabel }}</strong>.</p>

        <div class="period">
            📅 <strong>Mois :</strong> {{ $periodLabel }}
        </div>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $teacherCount }}</div>
                <div class="stat-label">Enseignants concernés</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ number_format($totalAmount, 0, ',', ' ') }}</div>
                <div class="stat-label">Montant total (F CFA)</div>
            </div>
        </div>

        <div class="message-box">
            📎 <strong>Document joint</strong><br>
            Le détail par enseignant (taux de présence, ponctualité, montant payé) figure dans le PDF ci-joint.
        </div>

        <div class="footer">
            <p>Cet email a été généré automatiquement. Merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>
