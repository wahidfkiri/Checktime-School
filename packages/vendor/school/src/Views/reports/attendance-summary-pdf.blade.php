<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Point d'assiduité - {{ $period_label }}</title>
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

        .period-info {
            text-align: center;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: bold;
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

        .employee-name { text-align: left; font-weight: bold; }

        .total-row {
            background-color: #e6e6e6;
            font-weight: bold;
        }

        .rate-high { color: #008000; font-weight: bold; }
        .rate-medium { color: #ff9900; font-weight: bold; }
        .rate-low { color: #ff0000; font-weight: bold; }

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
        $formatMoney = function ($amount) {
            return number_format($amount, 0, ',', ' ');
        };
        $rateClass = function ($rate) {
            return $rate >= 90 ? 'rate-high' : ($rate >= 75 ? 'rate-medium' : 'rate-low');
        };
        $totalAmount = collect($employees_data)->sum(fn($e) => $e['result']['amount_to_pay']);
    @endphp

    <div class="header">
        <div class="title">Points des présences, ponctualités et montant total des heures de vacations</div>
        <div class="period-info">Mois : {{ $period_label }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">N°</th>
                <th style="width:22%;">Nom de l'enseignant</th>
                <th style="width:14%;">Taux de présence</th>
                <th style="width:12%;">Nombre d'absence</th>
                <th style="width:14%;">Taux de ponctualité</th>
                <th style="width:15%;">Montant payé<br>(F CFA)</th>
                <th style="width:18%;">Observation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees_data as $index => $entry)
                @php $result = $entry['result']; @endphp
                <tr>
                    <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="employee-name">{{ $entry['employee']->full_name }}</td>
                    <td class="{{ $rateClass($result['presence_rate']) }}">{{ $result['presence_rate'] }}%</td>
                    <td>{{ $result['unjustified_absences_count'] }}</td>
                    <td class="{{ $rateClass($result['punctuality_rate']) }}">{{ $result['punctuality_rate'] }}%</td>
                    <td>{{ $formatMoney($result['amount_to_pay']) }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Aucune donnée pour la période sélectionnée.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">MONTANT TOTAL</td>
                <td>{{ $formatMoney($totalAmount) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Rapport généré par CheckTime le {{ $export_date->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
