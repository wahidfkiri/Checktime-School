@extends('layouts.app')

@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Rapports de vacation</h3>
                        <p class="text-subtitle text-muted">Fiche de présence & ponctualité, fiche des heures de vacation</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('classes.index') }}">Gestion Scolaire</a>
                                </li>
                                <li class="breadcrumb-item active">Rapports</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="card" style="max-width: 700px;">
                    <div class="card-header">
                        <h5 class="mb-0">Sélection de la période</h5>
                    </div>
                    <div class="card-body">
                        <form id="vacationReportForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="report_month" class="form-label">Mois <span class="text-danger">*</span></label>
                                        <input type="month" class="form-control" id="report_month" name="month"
                                               value="{{ now()->format('Y-m') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="report_employee_id" class="form-label">Enseignant</label>
                                        <select class="form-control" id="report_employee_id" name="employee_id">
                                            <option value="all">Tous les enseignants</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" id="btn-presence-pdf">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Fiche présence & ponctualité
                                </button>
                                <button type="button" class="btn btn-success" id="btn-payment-pdf">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Fiche des heures de vacation
                                </button>
                                <button type="button" class="btn btn-secondary" id="btn-attendance-summary-pdf">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Point d'assiduité (Direction)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function buildQuery() {
        return $.param({
            month: $('#report_month').val(),
            employee_id: $('#report_employee_id').val()
        });
    }

    $('#btn-presence-pdf').on('click', function() {
        if (!$('#report_month').val()) {
            Swal.fire({ icon: 'warning', title: 'Mois requis', text: 'Veuillez sélectionner un mois.' });
            return;
        }
        window.open("{{ route('vacation-reports.presence-pdf') }}?" + buildQuery(), '_blank');
    });

    $('#btn-payment-pdf').on('click', function() {
        if (!$('#report_month').val()) {
            Swal.fire({ icon: 'warning', title: 'Mois requis', text: 'Veuillez sélectionner un mois.' });
            return;
        }
        window.open("{{ route('vacation-reports.payment-pdf') }}?" + buildQuery(), '_blank');
    });

    $('#btn-attendance-summary-pdf').on('click', function() {
        if (!$('#report_month').val()) {
            Swal.fire({ icon: 'warning', title: 'Mois requis', text: 'Veuillez sélectionner un mois.' });
            return;
        }
        window.open("{{ route('vacation-reports.attendance-summary-pdf') }}?month=" + encodeURIComponent($('#report_month').val()), '_blank');
    });
});
</script>
@endsection
