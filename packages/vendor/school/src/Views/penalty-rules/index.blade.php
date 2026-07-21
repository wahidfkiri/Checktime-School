@extends('layouts.app')

@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Règles de pénalités</h3>
                        <p class="text-subtitle text-muted">Paramètres appliqués au calcul des vacations</p>
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
                                <li class="breadcrumb-item active">Règles de pénalités</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="card" style="max-width: 700px;">
                    <div class="card-header">
                        <h5 class="mb-0">Paramètres des pénalités</h5>
                    </div>
                    <div class="card-body">
                        <form id="penaltyRuleForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="absence_count" class="form-label">Seuil d'absences non justifiées</label>
                                        <input type="number" class="form-control" id="absence_count" name="absence_count"
                                               min="1" value="{{ $penaltyRule->absence_count }}" required>
                                        <div class="form-text">Nombre d'absences déclenchant une pénalité (ex: 1)</div>
                                        <div class="invalid-feedback" id="absence_count-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="absence_rate" class="form-label">Taux de pénalité par absence (%)</label>
                                        <input type="number" class="form-control" id="absence_rate" name="absence_rate"
                                               min="0" max="100" step="0.01" value="{{ $penaltyRule->absence_rate }}" required>
                                        <div class="form-text">% du montant total prélevé par seuil d'absence atteint</div>
                                        <div class="invalid-feedback" id="absence_rate-error"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="late_minutes" class="form-label">Palier de retard cumulé (minutes)</label>
                                        <input type="number" class="form-control" id="late_minutes" name="late_minutes"
                                               min="1" value="{{ $penaltyRule->late_minutes }}" required>
                                        <div class="form-text">Nombre de minutes de retard cumulé par mois déclenchant une pénalité (ex: 30)</div>
                                        <div class="invalid-feedback" id="late_minutes-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="late_rate" class="form-label">Taux de pénalité par palier de retard (%)</label>
                                        <input type="number" class="form-control" id="late_rate" name="late_rate"
                                               min="0" max="100" step="0.01" value="{{ $penaltyRule->late_rate }}" required>
                                        <div class="form-text">% du montant total prélevé par palier de retard atteint</div>
                                        <div class="invalid-feedback" id="late_rate-error"></div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submit-penalty-rule">
                                <span id="save-text">Enregistrer</span>
                                <span id="save-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
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
    $('#penaltyRuleForm').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            absence_count: $('#absence_count').val(),
            absence_rate: $('#absence_rate').val(),
            late_minutes: $('#late_minutes').val(),
            late_rate: $('#late_rate').val(),
            _token: "{{ csrf_token() }}"
        };

        $('#submit-penalty-rule').prop('disabled', true);
        $('#save-text').addClass('d-none');
        $('#save-spinner').removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ route('penalty-rules.update') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Règles mises à jour avec succès',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erreur', text: response.message });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        var input = $('#' + key);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(value[0]);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: xhr.responseJSON?.message || 'Une erreur est survenue.'
                    });
                }
            },
            complete: function() {
                $('#submit-penalty-rule').prop('disabled', false);
                $('#save-text').removeClass('d-none');
                $('#save-spinner').addClass('d-none');
            }
        });
    });
});
</script>
@endsection
