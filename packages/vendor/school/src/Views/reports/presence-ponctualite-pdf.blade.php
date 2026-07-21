<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fiche de présence et ponctualité - {{ $period_label }}</title>
    <style>
        @page {
            margin: 20px;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
        }

        .employee-page {
            page-break-after: always;
        }

        .employee-page:last-child {
            page-break-after: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .title {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .employee-info {
            text-align: left;
            margin-bottom: 10px;
            font-size: 11px;
        }

        .employee-info strong {
            display: inline-block;
            width: 90px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 6px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        td {
            padding: 5px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        .total-row {
            background-color: #e6e6e6;
            font-weight: bold;
        }

        .absence-justifiee { color: #0d6efd; font-style: italic; }
        .absence-non-justifiee { color: #dc3545; font-weight: bold; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    @php
        $formatMinutes = function ($minutes) {
            $minutes = (int) $minutes;
            return sprintf('%dh%02d', intdiv($minutes, 60), $minutes % 60);
        };
    @endphp

    @forelse($employees_data as $entry)
        @php
            $employee = $entry['employee'];
            $result = $entry['result'];
            $totalLate = 0;
            $totalAbsences = 0;
        @endphp
        <div class="employee-page">
            <div class="header">
                <div class="title">Fiche de présence et ponctualité</div>
            </div>
            <div class="employee-info">
                <div><strong>Nom :</strong> {{ $employee->full_name }}</div>
                <div><strong>Période :</strong> {{ $period_label }}</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2">Jour</th>
                        <th rowspan="2">Date</th>
                        <th colspan="2">Horaire prévu</th>
                        <th rowspan="2">Classe</th>
                        <th colspan="2">Horaire effectué</th>
                        <th rowspan="2">Heure validée</th>
                        <th rowspan="2">Cumul retard et<br>départ anticipé (min)</th>
                        <th rowspan="2">Nb<br>absence</th>
                    </tr>
                    <tr>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Arrivée</th>
                        <th>Départ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($result['details'] as $detail)
                        @php
                            $cumul = $detail['late_minutes'] + $detail['early_leave_minutes'];
                            $isAbsence = $detail['status'] !== 'presence';
                            $totalLate += $cumul;
                            $totalAbsences += $isAbsence ? 1 : 0;
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($detail['date'])->locale('fr')->isoFormat('dddd') }}</td>
                            <td>{{ \Carbon\Carbon::parse($detail['date'])->format('d/m/Y') }}</td>
                            <td>{{ $detail['planned_start'] }}</td>
                            <td>{{ $detail['planned_end'] }}</td>
                            <td>{{ $detail['class_name'] }}</td>
                            @if($isAbsence)
                                <td colspan="2" class="{{ $detail['status'] === 'absence_justifiee' ? 'absence-justifiee' : 'absence-non-justifiee' }}">
                                    {{ $detail['status'] === 'absence_justifiee' ? 'Absence justifiée' : 'Absence non justifiée' }}
                                </td>
                            @else
                                <td>{{ $detail['actual_arrival'] }}</td>
                                <td>{{ $detail['actual_departure'] }}</td>
                            @endif
                            <td>{{ $isAbsence ? '-' : $formatMinutes($detail['validated_minutes']) }}</td>
                            <td>{{ $cumul }}</td>
                            <td>{{ $isAbsence ? 1 : 0 }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="7" style="text-align: right;">TOTAL</td>
                        <td>{{ $totalLate }}</td>
                        <td>{{ $totalAbsences }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <p>Aucune donnée pour la période sélectionnée.</p>
    @endforelse

    <div class="footer">
        Rapport généré par CheckTime le {{ $export_date->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
