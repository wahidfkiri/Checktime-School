@extends('layouts.app')

@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Planning des vacations</h3>
                        <p class="text-subtitle text-muted">Emploi du temps hebdomadaire des enseignants</p>
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
                                <li class="breadcrumb-item active">Planning des vacations</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="employee_filter">Enseignant</label>
                                    <select class="form-control" id="employee_filter">
                                        <option value="">Tous</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="day_filter">Jour</label>
                                    <select class="form-control" id="day_filter">
                                        <option value="">Tous</option>
                                        <option value="1">Lundi</option>
                                        <option value="2">Mardi</option>
                                        <option value="3">Mercredi</option>
                                        <option value="4">Jeudi</option>
                                        <option value="5">Vendredi</option>
                                        <option value="6">Samedi</option>
                                        <option value="7">Dimanche</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group text-start">
                                    <label class="form-label d-block" style="margin-bottom:0px;">&nbsp;</label>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" id="create-vacation-button" data-bs-toggle="modal" data-bs-target="#createVacationModal">
                                            <i class="bi bi-plus-circle me-1"></i> Nouvelle vacation
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="reset_filters">
                                            <i class="bi bi-x-circle me-1"></i> Réinitialiser
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="vacations-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="20%">Enseignant</th>
                                        <th width="12%">Jour</th>
                                        <th width="10%">Début</th>
                                        <th width="10%">Fin</th>
                                        <th width="18%">Classe</th>
                                        <th width="15%">Matière</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Les données seront chargées via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Modal de création de vacation -->
<div class="modal fade" id="createVacationModal" tabindex="-1" aria-labelledby="createVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createVacationModalLabel">Planifier une nouvelle vacation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createVacationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="vacation_employee_id" class="form-label">Enseignant <span class="text-danger">*</span></label>
                        <select class="form-control" id="vacation_employee_id" name="employee_id" required>
                            <option value="">Sélectionner...</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="employee_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vacation_day_of_week" class="form-label">Jour <span class="text-danger">*</span></label>
                        <select class="form-control" id="vacation_day_of_week" name="day_of_week" required>
                            <option value="">Sélectionner...</option>
                            <option value="1">Lundi</option>
                            <option value="2">Mardi</option>
                            <option value="3">Mercredi</option>
                            <option value="4">Jeudi</option>
                            <option value="5">Vendredi</option>
                            <option value="6">Samedi</option>
                            <option value="7">Dimanche</option>
                        </select>
                        <div class="invalid-feedback" id="day_of_week-error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vacation_start_time" class="form-label">Début <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="vacation_start_time" name="start_time" required>
                                <div class="invalid-feedback" id="start_time-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vacation_end_time" class="form-label">Fin <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="vacation_end_time" name="end_time" required>
                                <div class="invalid-feedback" id="end_time-error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="vacation_class_id" class="form-label">Classe <span class="text-danger">*</span></label>
                        <select class="form-control" id="vacation_class_id" name="class_id" required>
                            <option value="">Sélectionner...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->level }} - {{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="class_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="vacation_subject" class="form-label">Matière</label>
                        <input type="text" class="form-control" id="vacation_subject" name="subject" maxlength="100" placeholder="ex: Maths, SPCT">
                        <div class="invalid-feedback" id="subject-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-create-vacation">
                        <span id="create-vacation-text">Planifier</span>
                        <span id="create-vacation-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition de vacation -->
<div class="modal fade" id="editVacationModal" tabindex="-1" aria-labelledby="editVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editVacationModalLabel">Modifier la vacation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editVacationForm">
                <input type="hidden" id="edit_vacation_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_vacation_employee_id" class="form-label">Enseignant <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_vacation_employee_id" name="employee_id" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="edit-employee_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vacation_day_of_week" class="form-label">Jour <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_vacation_day_of_week" name="day_of_week" required>
                            <option value="1">Lundi</option>
                            <option value="2">Mardi</option>
                            <option value="3">Mercredi</option>
                            <option value="4">Jeudi</option>
                            <option value="5">Vendredi</option>
                            <option value="6">Samedi</option>
                            <option value="7">Dimanche</option>
                        </select>
                        <div class="invalid-feedback" id="edit-day_of_week-error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vacation_start_time" class="form-label">Début <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit_vacation_start_time" name="start_time" required>
                                <div class="invalid-feedback" id="edit-start_time-error"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_vacation_end_time" class="form-label">Fin <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="edit_vacation_end_time" name="end_time" required>
                                <div class="invalid-feedback" id="edit-end_time-error"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vacation_class_id" class="form-label">Classe <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_vacation_class_id" name="class_id" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->level }} - {{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="edit-class_id-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_vacation_subject" class="form-label">Matière</label>
                        <input type="text" class="form-control" id="edit_vacation_subject" name="subject" maxlength="100">
                        <div class="invalid-feedback" id="edit-subject-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-edit-vacation">
                        <span id="edit-vacation-text">Enregistrer</span>
                        <span id="edit-vacation-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression de vacation -->
<div class="modal fade" id="deleteVacationModal" tabindex="-1" aria-labelledby="deleteVacationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVacationModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette vacation planifiée ?</p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-vacation">
                    <span id="delete-vacation-text">Supprimer</span>
                    <span id="delete-vacation-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let vacationToDelete = null;

    var table = $('#vacations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('vacation-schedules.local') }}",
            data: function (d) {
                d.employee_id = $('#employee_filter').val();
                d.day_of_week = $('#day_filter').val();
            }
        },
        columns: [
            { data: 'employee_name', name: 'employee_name', width: '20%' },
            { data: 'day_name', name: 'day_name', width: '12%' },
            { data: 'start_time_formatted', name: 'start_time_formatted', orderable: false, searchable: false, width: '10%' },
            { data: 'end_time_formatted', name: 'end_time_formatted', orderable: false, searchable: false, width: '10%' },
            { data: 'class_name', name: 'class_name', width: '18%' },
            { data: 'subject', name: 'subject', width: '15%' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '15%' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
        order: [[0, 'asc'], [1, 'asc']],
        responsive: true,
        drawCallback: function() {
            attachActionButtons();
        }
    });

    function attachActionButtons() {
        $('.edit-vacation-btn').off('click').on('click', function() {
            openEditModal(
                $(this).data('id'),
                $(this).data('employee_id'),
                $(this).data('day_of_week'),
                $(this).data('start_time'),
                $(this).data('end_time'),
                $(this).data('class_id'),
                $(this).data('subject')
            );
        });

        $('.delete-vacation-btn').off('click').on('click', function() {
            vacationToDelete = $(this).data('id');
            $('#deleteVacationModal').modal('show');
        });
    }

    function openEditModal(id, employeeId, dayOfWeek, startTime, endTime, classId, subject) {
        $('#edit_vacation_id').val(id);
        $('#edit_vacation_employee_id').val(employeeId);
        $('#edit_vacation_day_of_week').val(dayOfWeek);
        $('#edit_vacation_start_time').val(startTime);
        $('#edit_vacation_end_time').val(endTime);
        $('#edit_vacation_class_id').val(classId);
        $('#edit_vacation_subject').val(subject);

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $('#editVacationModal').modal('show');
    }

    function applyFilters() {
        table.ajax.reload();
    }

    $('#employee_filter, #day_filter').on('change', applyFilters);

    $('#reset_filters').on('click', function() {
        $('#employee_filter, #day_filter').val('');
        applyFilters();
    });

    function showSweetAlert(icon, title, text) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({ icon: icon, title: title, text: text });
    }

    $('#createVacationForm').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            employee_id: $('#vacation_employee_id').val(),
            day_of_week: $('#vacation_day_of_week').val(),
            start_time: $('#vacation_start_time').val(),
            end_time: $('#vacation_end_time').val(),
            class_id: $('#vacation_class_id').val(),
            subject: $('#vacation_subject').val(),
            _token: "{{ csrf_token() }}"
        };

        $('#submit-create-vacation').prop('disabled', true);
        $('#create-vacation-text').addClass('d-none');
        $('#create-vacation-spinner').removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ route('vacation-schedules.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#createVacationModal').modal('hide');
                    $('#createVacationForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Vacation planifiée avec succès',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            var input = $('#vacation_' + key);
                            input.addClass('is-invalid');
                            input.next('.invalid-feedback').text(value[0]);
                        });
                    } else {
                        showSweetAlert('error', 'Erreur', xhr.responseJSON.message);
                    }
                } else {
                    showSweetAlert('error', 'Erreur',
                        'Une erreur est survenue lors de la création. ' +
                        (xhr.responseJSON?.message || 'Veuillez réessayer.')
                    );
                }
            },
            complete: function() {
                $('#submit-create-vacation').prop('disabled', false);
                $('#create-vacation-text').removeClass('d-none');
                $('#create-vacation-spinner').addClass('d-none');
            }
        });
    });

    $('#editVacationForm').on('submit', function(e) {
        e.preventDefault();

        const vacationId = $('#edit_vacation_id').val();

        var formData = {
            employee_id: $('#edit_vacation_employee_id').val(),
            day_of_week: $('#edit_vacation_day_of_week').val(),
            start_time: $('#edit_vacation_start_time').val(),
            end_time: $('#edit_vacation_end_time').val(),
            class_id: $('#edit_vacation_class_id').val(),
            subject: $('#edit_vacation_subject').val(),
            _token: "{{ csrf_token() }}"
        };

        $('#submit-edit-vacation').prop('disabled', true);
        $('#edit-vacation-text').addClass('d-none');
        $('#edit-vacation-spinner').removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ url('vacation-schedules') }}/" + vacationId,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editVacationModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Vacation modifiée avec succès',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    if (errors) {
                        $.each(errors, function(key, value) {
                            var input = $('#edit_vacation_' + key);
                            input.addClass('is-invalid');
                            input.next('.invalid-feedback').text(value[0]);
                        });
                    } else {
                        showSweetAlert('error', 'Erreur', xhr.responseJSON.message);
                    }
                } else {
                    showSweetAlert('error', 'Erreur',
                        'Une erreur est survenue lors de la modification. ' +
                        (xhr.responseJSON?.message || 'Veuillez réessayer.')
                    );
                }
            },
            complete: function() {
                $('#submit-edit-vacation').prop('disabled', false);
                $('#edit-vacation-text').removeClass('d-none');
                $('#edit-vacation-spinner').addClass('d-none');
            }
        });
    });

    $('#confirm-delete-vacation').on('click', function() {
        if (!vacationToDelete) return;

        $(this).prop('disabled', true);
        $('#delete-vacation-text').addClass('d-none');
        $('#delete-vacation-spinner').removeClass('d-none');

        $.ajax({
            url: "{{ url('vacation-schedules') }}/" + vacationToDelete,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.success) {
                    $('#deleteVacationModal').modal('hide');
                    vacationToDelete = null;

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Vacation supprimée avec succès',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                    $('#deleteVacationModal').modal('hide');
                }
            },
            error: function(xhr) {
                showSweetAlert('error', 'Erreur',
                    'Une erreur est survenue lors de la suppression. ' +
                    (xhr.responseJSON?.message || 'Veuillez réessayer.')
                );
                $('#deleteVacationModal').modal('hide');
            },
            complete: function() {
                $('#confirm-delete-vacation').prop('disabled', false);
                $('#delete-vacation-text').removeClass('d-none');
                $('#delete-vacation-spinner').addClass('d-none');
            }
        });
    });

    $('#createVacationModal').on('hidden.bs.modal', function() {
        $('#createVacationForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#submit-create-vacation').prop('disabled', false);
        $('#create-vacation-text').removeClass('d-none');
        $('#create-vacation-spinner').addClass('d-none');
    });

    $('#editVacationModal').on('hidden.bs.modal', function() {
        $('#submit-edit-vacation').prop('disabled', false);
        $('#edit-vacation-text').removeClass('d-none');
        $('#edit-vacation-spinner').addClass('d-none');
    });

    $('#deleteVacationModal').on('hidden.bs.modal', function() {
        vacationToDelete = null;
        $('#confirm-delete-vacation').prop('disabled', false);
        $('#delete-vacation-text').removeClass('d-none');
        $('#delete-vacation-spinner').addClass('d-none');
    });
});
</script>
<style>
    .dataTables_wrapper { padding: 10px 0; }
    .dataTables_length, .dataTables_filter { margin-bottom: 15px; }
    .dataTables_filter input { margin-left: 10px; }
    .dt-responsive { width: 100% !important; }
    .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    .btn-group .btn { margin-right: 5px; border-radius: 4px !important; }
    .modal-content { border-radius: 10px; }
    .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; border-radius: 10px 10px 0 0; }
    .modal-title { color: #333; font-weight: 600; }
    .btn-group .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
</style>
@endsection
