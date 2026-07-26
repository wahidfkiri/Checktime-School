@extends('layouts.app')
@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Tableau de Bord</h3>
                        <p class="text-subtitle text-muted">
                            Vue d'ensemble de votre école
                            @if(isset($client) && $client)
                                — {{ $client->raison_sociale }}
                            @endif
                        </p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Accueil</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Aujourd'hui -->
            <section class="section">
                <div class="section-eyebrow">Aujourd'hui</div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon brand"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <div class="stat-label">Présents</div>
                                    <div class="stat-value">{{ $totalPresentToday ?? 0 }}</div>
                                    <div class="stat-trend text-muted">sur {{ $activeEmployees ?? 0 }} enseignants actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon critical"><i class="bi bi-x-circle"></i></div>
                                <div>
                                    <div class="stat-label">Absents</div>
                                    <div class="stat-value">{{ $totalAbsentToday ?? 0 }}</div>
                                    <div class="stat-trend text-muted">sur {{ $activeEmployees ?? 0 }} enseignants actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon warning"><i class="bi bi-alarm"></i></div>
                                <div>
                                    <div class="stat-label">Retards</div>
                                    <div class="stat-value">{{ $totalRetardToday ?? 0 }}</div>
                                    <div class="stat-trend text-muted">{{ $totalPresentToday > 0 ? round(($totalRetardToday / $totalPresentToday) * 100, 1) : 0 }}% des présents</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon gold"><i class="bi bi-graph-up-arrow"></i></div>
                                <div>
                                    <div class="stat-label">Taux de présence</div>
                                    <div class="stat-value">{{ $attendanceRate ?? 0 }}%</div>
                                    <div class="stat-trend text-muted">enseignants actifs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- École -->
            <section class="section mt-3">
                <div class="section-eyebrow">École</div>
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon brand"><i class="bi bi-mortarboard"></i></div>
                                <div>
                                    <div class="stat-label">Enseignants actifs</div>
                                    <div class="stat-value">{{ $activeEmployees ?? 0 }}</div>
                                    <div class="stat-trend text-muted">sur {{ $totalEmployees ?? 0 }} au total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon gold"><i class="bi bi-easel"></i></div>
                                <div>
                                    <div class="stat-label">Classes actives</div>
                                    <div class="stat-value">{{ $activeClasses ?? 0 }}</div>
                                    <div class="stat-trend text-muted">sur {{ $totalClasses ?? 0 }} au total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon warning"><i class="bi bi-calendar-week"></i></div>
                                <div>
                                    <div class="stat-label">Vacations planifiées</div>
                                    <div class="stat-value">{{ $totalVacations ?? 0 }}</div>
                                    <div class="stat-trend text-muted">séances / semaine</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon critical"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <div class="stat-label">Comptes de connexion</div>
                                    <div class="stat-value">{{ $teachersWithAccount ?? 0 }}</div>
                                    <div class="stat-trend text-muted">sur {{ $totalEmployees ?? 0 }} enseignants</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Vacations & paie (mois en cours) -->
            <section class="section mt-3">
                <div class="section-eyebrow">Vacations &amp; paie — {{ now()->locale('fr')->isoFormat('MMMM YYYY') }}</div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon brand"><i class="bi bi-cash-coin"></i></div>
                                <div>
                                    <div class="stat-label">Montant à payer (mois en cours)</div>
                                    <div class="stat-value">{{ number_format($vacationMonthStats['amount'] ?? 0, 0, ',', ' ') }} <small>F CFA</small></div>
                                    <div class="stat-trend text-muted">avant clôture du mois</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon gold"><i class="bi bi-graph-up"></i></div>
                                <div>
                                    <div class="stat-label">Présence des vacations</div>
                                    <div class="stat-value">{{ $vacationMonthStats['presence_rate'] ?? 0 }}%</div>
                                    <div class="stat-trend text-muted">séances effectuées / prévues</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon warning"><i class="bi bi-hourglass-split"></i></div>
                                <div>
                                    <div class="stat-label">Retard cumulé</div>
                                    <div class="stat-value">{{ $vacationMonthStats['late_minutes'] ?? 0 }} <small>min</small></div>
                                    <div class="stat-trend text-muted">retards + départs anticipés</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Graphiques et Visualisations -->
            <section class="section mt-3">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Présence Aujourd'hui</h4>
                            </div>
                            <div class="card-body">
                                <div id="attendance-today-chart"></div>
                                <div class="text-center mt-3">
                                    <span class="badge bg-success me-2">{{ $totalPresentToday ?? 0 }} Présents</span>
                                    <span class="badge bg-warning me-2">{{ $totalRetardToday ?? 0 }} Retards</span>
                                    <span class="badge bg-danger">{{ $totalAbsentToday ?? 0 }} Absents</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Classes par niveau</h4>
                            </div>
                            <div class="card-body">
                                <div id="classes-level-chart"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Vacations par jour</h4>
                            </div>
                            <div class="card-body">
                                <div id="vacations-day-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Présence Hebdomadaire -->
                @if(isset($weeklyAttendance) && count($weeklyAttendance) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Présence Hebdomadaire</h4>
                            </div>
                            <div class="card-body">
                                <div id="weekly-attendance-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Planning du jour -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Planning d'aujourd'hui — {{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM')) }}</h4>
                                <a href="{{ route('vacation-schedules.index') }}" class="btn btn-primary btn-sm">Voir le planning</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Heure</th>
                                                <th>Enseignant</th>
                                                <th>Classe</th>
                                                <th>Matière</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($todaySchedule ?? [] as $vacation)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($vacation->start_time)->format('H:i') }} — {{ \Carbon\Carbon::parse($vacation->end_time)->format('H:i') }}</td>
                                                <td><strong>{{ $vacation->employee->full_name ?? 'N/A' }}</strong></td>
                                                <td>{{ $vacation->schoolClass->name ?? 'N/A' }}</td>
                                                <td>{{ $vacation->subject ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Aucune vacation planifiée aujourd'hui</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dernières Présences -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Dernières Présences Enregistrées</h4>
                                <a href="{{ route('admin.daily-attendance.index') }}" class="btn btn-primary btn-sm">Voir toutes</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Enseignant</th>
                                                <th>Date</th>
                                                <th>Heure Arrivée</th>
                                                <th>Heure Départ</th>
                                                <th>Heures</th>
                                                <th>Statut</th>
                                                <th>Retard</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentAttendances ?? [] as $attendance)
                                            <tr>
                                                <td>
                                                    <strong>{{ $attendance->employee->first_name ?? '' }} {{ $attendance->employee->last_name ?? '' }}</strong>
                                                    <br>
                                                    <small>{{ $attendance->emp_code }}</small>
                                                </td>
                                                <td>{{ $attendance->attendance_date ? $attendance->attendance_date->format('d/m/Y') : 'N/A' }}</td>
                                                <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i:s') : 'N/A' }}</td>
                                                <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i:s') : 'N/A' }}</td>
                                                <td>{{ $attendance->work_hours ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $status = strtolower($attendance->status ?? 'present');
                                                        $statusClasses = [
                                                            'present' => 'badge bg-success',
                                                            'absent' => 'badge bg-danger',
                                                            'late' => 'badge bg-warning',
                                                            'holiday' => 'badge bg-info',
                                                            'leave' => 'badge bg-secondary'
                                                        ];
                                                        $statusClass = $statusClasses[$status] ?? 'badge bg-secondary';
                                                    @endphp
                                                    <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                                                </td>
                                                <td>
                                                    @if($attendance->is_late)
                                                        <span class="badge bg-warning">Retard</span>
                                                    @else
                                                        <span class="badge bg-success">À l'heure</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Aucune présence enregistrée</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tableau des Derniers Enseignants -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Derniers Enseignants Ajoutés</h4>
                                <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm">Voir tous</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Nom Complet</th>
                                                <th>Téléphone</th>
                                                <th>Email</th>
                                                <th>Statut</th>
                                                <th>Compte</th>
                                                <th>Date Ajout</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentEmployees as $employee)
                                            <tr>
                                                <td>
                                                    <strong>{{ $employee->emp_code ?? 'N/A' }}</strong>
                                                </td>
                                                <td>{{ $employee->first_name ?? '' }} {{ $employee->last_name ?? '' }}</td>
                                                <td>{{ $employee->phone ?? 'N/A' }}</td>
                                                <td>{{ $employee->email ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $status = strtolower($employee->status ?? 'active');
                                                        $statusClasses = [
                                                            'active' => 'badge bg-success',
                                                            'inactive' => 'badge bg-danger',
                                                            'suspended' => 'badge bg-warning'
                                                        ];
                                                        $statusClass = $statusClasses[$status] ?? 'badge bg-secondary';
                                                    @endphp
                                                    <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                                                </td>
                                                <td>
                                                    @if($employee->user_id)
                                                        <span class="badge bg-success">Actif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Aucun</span>
                                                    @endif
                                                </td>
                                                <td>{{ $employee->created_at ? $employee->created_at->format('d/m/Y') : 'N/A' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Aucun enseignant trouvé</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Évolution des enseignants -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Évolution des Enseignants</h4>
                            </div>
                            <div class="card-body">
                                <div id="monthly-growth-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.section-eyebrow {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
    color: var(--ct-text-muted);
    margin-bottom: 0.6rem;
}

.stat-card .card-body {
    padding: 1.25rem;
}

.stat-icon {
    flex: 0 0 auto;
    width: 52px;
    height: 52px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.stat-icon.brand { background: var(--ct-brand-soft); color: var(--ct-brand); }
.stat-icon.gold { background: var(--ct-gold-soft); color: var(--ct-gold); }
.stat-icon.warning { background: var(--ct-warning-soft); color: var(--ct-warning); }
.stat-icon.critical { background: var(--ct-critical-soft); color: var(--ct-critical); }

.stat-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ct-text-muted);
    margin-bottom: 2px;
}

.stat-value {
    font-family: var(--ct-font-display);
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--ct-text);
}

.stat-value small {
    font-family: var(--ct-font-body);
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ct-text-muted);
}

.stat-trend {
    font-size: 0.75rem;
    margin-top: 2px;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-3px);
}

.gap-3 { gap: 1rem; }
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctColors = {
        brand: '#2F6F62',
        gold: '#A9782E',
        warning: '#C97A2B',
        critical: '#B4483D'
    };

    // Présence aujourd'hui
    @if(isset($attendanceTodayData))
    var attendanceTodayOptions = {
        series: [
            {{ $attendanceTodayData['present'] ?? 0 }},
            {{ $attendanceTodayData['retard'] ?? 0 }},
            {{ $attendanceTodayData['absent'] ?? 0 }}
        ],
        chart: { type: 'donut', height: 250, fontFamily: 'Public Sans, sans-serif' },
        labels: ['Présents', 'Retards', 'Absents'],
        colors: [ctColors.brand, ctColors.warning, ctColors.critical],
        legend: { position: 'bottom' },
        tooltip: { y: { formatter: function (val) { return val + " enseignants"; } } }
    };
    new ApexCharts(document.querySelector("#attendance-today-chart"), attendanceTodayOptions).render();
    @endif

    // Classes par niveau
    @if(isset($classesByLevelData) && count($classesByLevelData) > 0)
    var classesLevelOptions = {
        series: @json($classesByLevelData ?? []),
        chart: { type: 'donut', height: 250, fontFamily: 'Public Sans, sans-serif' },
        labels: @json($classesByLevelLabels ?? []),
        colors: [ctColors.brand, ctColors.gold, ctColors.warning, ctColors.critical],
        legend: { position: 'bottom' },
        tooltip: { y: { formatter: function (val) { return val + " classe(s)"; } } }
    };
    new ApexCharts(document.querySelector("#classes-level-chart"), classesLevelOptions).render();
    @else
    document.querySelector("#classes-level-chart").innerHTML = '<p class="text-muted text-center py-5 mb-0">Aucune classe enregistrée</p>';
    @endif

    // Vacations par jour de la semaine
    @if(isset($vacationsByDayData))
    var vacationsDayOptions = {
        series: [{ name: 'Vacations', data: @json($vacationsByDayData ?? []) }],
        chart: { type: 'bar', height: 250, toolbar: { show: false }, fontFamily: 'Public Sans, sans-serif' },
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
        dataLabels: { enabled: true },
        xaxis: { categories: @json($vacationsByDayLabels ?? []) },
        colors: [ctColors.brand],
        tooltip: { y: { formatter: function (val) { return val + " vacation(s)"; } } }
    };
    new ApexCharts(document.querySelector("#vacations-day-chart"), vacationsDayOptions).render();
    @endif

    // Présence hebdomadaire
    @if(isset($weeklyAttendance) && count($weeklyAttendance) > 0)
    var weeklyDays = @json(array_column($weeklyAttendance, 'day'));
    var weeklyPresent = @json(array_column($weeklyAttendance, 'present'));
    var weeklyAbsent = @json(array_column($weeklyAttendance, 'absent'));

    var weeklyAttendanceOptions = {
        series: [
            { name: 'Présents', data: weeklyPresent },
            { name: 'Absents', data: weeklyAbsent }
        ],
        chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: true }, fontFamily: 'Public Sans, sans-serif' },
        colors: [ctColors.brand, ctColors.critical],
        plotOptions: { bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' } },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: { categories: weeklyDays },
        yaxis: { title: { text: "Nombre d'enseignants" } },
        fill: { opacity: 1 },
        tooltip: { y: { formatter: function (val) { return val + " enseignants"; } } },
        legend: { position: 'top' }
    };
    new ApexCharts(document.querySelector("#weekly-attendance-chart"), weeklyAttendanceOptions).render();
    @endif

    // Évolution des enseignants
    @if(isset($monthlyNewEmployees) && isset($monthlyLabels))
    var monthlyGrowthOptions = {
        series: [{ name: "Nouveaux Enseignants", data: @json($monthlyNewEmployees ?? []) }],
        chart: { height: 350, type: 'area', toolbar: { show: true }, fontFamily: 'Public Sans, sans-serif' },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: { categories: @json($monthlyLabels ?? []) },
        yaxis: { title: { text: 'Nombre' } },
        colors: [ctColors.gold],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3, stops: [0, 90, 100] }
        }
    };
    new ApexCharts(document.querySelector("#monthly-growth-chart"), monthlyGrowthOptions).render();
    @endif
});
</script>
@endsection
