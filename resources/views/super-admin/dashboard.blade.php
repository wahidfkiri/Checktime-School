@extends('layouts.app')
@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Tableau de Bord — Super Admin</h3>
                        <p class="text-subtitle text-muted">Vue d'ensemble des écoles du système CheckTime</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Accueil</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Écoles -->
            <section class="section">
                <div class="section-eyebrow">Écoles</div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon brand"><i class="bi bi-building"></i></div>
                                <div>
                                    <div class="stat-label">Écoles au total</div>
                                    <div class="stat-value">{{ $totalClients ?? 0 }}</div>
                                    <div class="stat-trend text-muted">clients du système</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon gold"><i class="bi bi-check2-circle"></i></div>
                                <div>
                                    <div class="stat-label">Écoles actives</div>
                                    <div class="stat-value">{{ $activeClients ?? 0 }}</div>
                                    <div class="stat-trend text-muted">accès portail ouvert</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon critical"><i class="bi bi-slash-circle"></i></div>
                                <div>
                                    <div class="stat-label">Écoles inactives</div>
                                    <div class="stat-value">{{ $inactiveClients ?? 0 }}</div>
                                    <div class="stat-trend text-muted">accès suspendu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Global -->
            <section class="section mt-3">
                <div class="section-eyebrow">Global (toutes écoles)</div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon brand"><i class="bi bi-mortarboard"></i></div>
                                <div>
                                    <div class="stat-label">Enseignants</div>
                                    <div class="stat-value">{{ $totalTeachers ?? 0 }}</div>
                                    <div class="stat-trend text-muted">toutes écoles</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon gold"><i class="bi bi-easel"></i></div>
                                <div>
                                    <div class="stat-label">Classes</div>
                                    <div class="stat-value">{{ $totalClasses ?? 0 }}</div>
                                    <div class="stat-trend text-muted">toutes écoles</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="card stat-card h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="stat-icon warning"><i class="bi bi-hdd"></i></div>
                                <div>
                                    <div class="stat-label">Appareils</div>
                                    <div class="stat-value">{{ $totalDevices ?? 0 }}</div>
                                    <div class="stat-trend text-muted">pointeuses biométriques</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Écoles récentes -->
            <section class="section mt-3">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Écoles récemment ajoutées</h4>
                                <a href="{{ route('clients.index') }}" class="btn btn-primary btn-sm">Gérer les écoles</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>École</th>
                                                <th>RCCM</th>
                                                <th>Directeur</th>
                                                <th>Enseignants</th>
                                                <th>Statut</th>
                                                <th>Date d'ajout</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentClients ?? [] as $client)
                                            <tr>
                                                <td><strong>{{ $client->nom_complet }}</strong></td>
                                                <td class="text-uppercase">{{ $client->rccm ?? '-' }}</td>
                                                <td>{{ $client->directeur ?? '-' }}</td>
                                                <td>{{ $client->employees_count }}</td>
                                                <td>
                                                    @if($client->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Aucune école enregistrée</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.section-eyebrow { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600; color: var(--ct-text-muted); margin-bottom: 0.6rem; }
.stat-card .card-body { padding: 1.25rem; }
.stat-icon { flex: 0 0 auto; width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
.stat-icon.brand { background: var(--ct-brand-soft); color: var(--ct-brand); }
.stat-icon.gold { background: var(--ct-gold-soft); color: var(--ct-gold); }
.stat-icon.warning { background: var(--ct-warning-soft); color: var(--ct-warning); }
.stat-icon.critical { background: var(--ct-critical-soft); color: var(--ct-critical); }
.stat-label { font-size: 0.78rem; font-weight: 600; color: var(--ct-text-muted); margin-bottom: 2px; }
.stat-value { font-family: var(--ct-font-display); font-size: 1.6rem; font-weight: 700; line-height: 1.2; color: var(--ct-text); }
.stat-trend { font-size: 0.75rem; margin-top: 2px; }
.card { transition: transform 0.2s ease; }
.card:hover { transform: translateY(-3px); }
.gap-3 { gap: 1rem; }
</style>
@endsection
