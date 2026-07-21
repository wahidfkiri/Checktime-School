@extends('layouts.app')

@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Gestion des classes</h3>
                        <p class="text-subtitle text-muted">Niveaux, classes et taux horaire de vacation</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Classes</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="level_filter">Niveau</label>
                                    <select class="form-control" id="level_filter">
                                        <option value="">Tous</option>
                                        <option value="Maternel">Maternel</option>
                                        <option value="Primaire">Primaire</option>
                                        <option value="Secondaire">Secondaire</option>
                                        <option value="Universitaire">Universitaire</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name_filter">Classe</label>
                                    <input type="text" class="form-control" id="name_filter" placeholder="Rechercher par nom...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="status_filter">Statut</label>
                                    <select class="form-control" id="status_filter">
                                        <option value="">Tous</option>
                                        <option value="1">Actif</option>
                                        <option value="0">Inactif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group text-start">
                                    <label class="form-label d-block" style="margin-bottom:0px;">&nbsp;</label>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" id="create-class-button" data-bs-toggle="modal" data-bs-target="#createClassModal">
                                            <i class="bi bi-plus-circle me-1"></i> Nouvelle classe
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
                            <table id="classes-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="20%">Niveau</th>
                                        <th width="25%">Classe</th>
                                        <th width="20%">Taux horaire (F CFA)</th>
                                        <th width="15%">Statut</th>
                                        <th width="20%">Actions</th>
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

<!-- Modal de création de classe -->
<div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createClassModalLabel">Créer une nouvelle classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createClassForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="class_level" class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select class="form-control" id="class_level" name="level" required>
                            <option value="">Sélectionner...</option>
                            <option value="Maternel">Maternel</option>
                            <option value="Primaire">Primaire</option>
                            <option value="Secondaire">Secondaire</option>
                            <option value="Universitaire">Universitaire</option>
                        </select>
                        <div class="invalid-feedback" id="level-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="class_name" class="form-label">Classe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="class_name" name="name" required maxlength="100" placeholder="ex: CM1, 6e A, Tle D">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="class_hourly_rate" class="form-label">Taux horaire (F CFA) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="class_hourly_rate" name="hourly_rate" required min="0" step="1" placeholder="ex: 1700">
                        <div class="invalid-feedback" id="hourly_rate-error"></div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="class_status" name="status" value="1" checked>
                        <label class="form-check-label" for="class_status">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-create-class">
                        <span id="create-class-text">Créer</span>
                        <span id="create-class-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition de classe -->
<div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClassModalLabel">Modifier la classe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClassForm">
                <input type="hidden" id="edit_class_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_class_level" class="form-label">Niveau <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_class_level" name="level" required>
                            <option value="Maternel">Maternel</option>
                            <option value="Primaire">Primaire</option>
                            <option value="Secondaire">Secondaire</option>
                            <option value="Universitaire">Universitaire</option>
                        </select>
                        <div class="invalid-feedback" id="edit-level-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_class_name" class="form-label">Classe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_class_name" name="name" required maxlength="100">
                        <div class="invalid-feedback" id="edit-name-error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_class_hourly_rate" class="form-label">Taux horaire (F CFA) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit_class_hourly_rate" name="hourly_rate" required min="0" step="1">
                        <div class="invalid-feedback" id="edit-hourly_rate-error"></div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_class_status" name="status" value="1">
                        <label class="form-check-label" for="edit_class_status">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-edit-class">
                        <span id="edit-class-text">Enregistrer</span>
                        <span id="edit-class-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression de classe -->
<div class="modal fade" id="deleteClassModal" tabindex="-1" aria-labelledby="deleteClassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteClassModalLabel">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette classe ?</p>
                <p><strong>Classe:</strong> <span id="delete-class-name"></span></p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-class">
                    <span id="delete-class-text">Supprimer</span>
                    <span id="delete-class-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let classToDelete = null;

    var table = $('#classes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('classes.local') }}",
            data: function (d) {
                d.level_filter = $('#level_filter').val();
                d.name_filter = $('#name_filter').val();
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'level', name: 'level', width: '20%' },
            { data: 'name', name: 'name', width: '25%' },
            {
                data: 'hourly_rate',
                name: 'hourly_rate',
                width: '20%',
                render: function(data) {
                    return Number(data).toLocaleString('fr-FR');
                }
            },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '15%' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '20%' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json"
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
        order: [[0, 'asc']],
        responsive: true,
        drawCallback: function() {
            attachActionButtons();
        }
    });

    function attachActionButtons() {
        $('.edit-class-btn').off('click').on('click', function() {
            openEditModal(
                $(this).data('id'),
                $(this).data('level'),
                $(this).data('name'),
                $(this).data('hourly_rate'),
                $(this).data('status')
            );
        });

        $('.delete-class-btn').off('click').on('click', function() {
            openDeleteModal($(this).data('id'), $(this).data('name'));
        });
    }

    function openEditModal(id, level, name, hourlyRate, status) {
        $('#edit_class_id').val(id);
        $('#edit_class_level').val(level);
        $('#edit_class_name').val(name);
        $('#edit_class_hourly_rate').val(hourlyRate);
        $('#edit_class_status').prop('checked', status == 1);

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $('#editClassModal').modal('show');
    }

    function openDeleteModal(id, name) {
        classToDelete = id;
        $('#delete-class-name').text(name);
        $('#deleteClassModal').modal('show');
    }

    function applyFilters() {
        table.ajax.reload();
    }

    $('#level_filter, #status_filter').on('change', applyFilters);
    $('#name_filter').on('change keyup', applyFilters);

    $('#reset_filters').on('click', function() {
        $('#level_filter, #status_filter').val('');
        $('#name_filter').val('');
        applyFilters();
    });

    function showSweetAlert(icon, title, text, timer = null) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: timer || 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({ icon: icon, title: title, text: text });
    }

    $('#createClassForm').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            level: $('#class_level').val(),
            name: $('#class_name').val(),
            hourly_rate: $('#class_hourly_rate').val(),
            status: $('#class_status').is(':checked') ? 1 : 0,
            _token: "{{ csrf_token() }}"
        };

        $('#submit-create-class').prop('disabled', true);
        $('#create-class-text').addClass('d-none');
        $('#create-class-spinner').removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ route('classes.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#createClassModal').modal('hide');
                    $('#createClassForm')[0].reset();
                    $('#class_status').prop('checked', true);

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Classe créée avec succès',
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
                    $.each(errors, function(key, value) {
                        var input = $('#class_' + key);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(value[0]);
                    });
                } else {
                    showSweetAlert('error', 'Erreur',
                        'Une erreur est survenue lors de la création. ' +
                        (xhr.responseJSON?.message || 'Veuillez réessayer.')
                    );
                }
            },
            complete: function() {
                $('#submit-create-class').prop('disabled', false);
                $('#create-class-text').removeClass('d-none');
                $('#create-class-spinner').addClass('d-none');
            }
        });
    });

    $('#editClassForm').on('submit', function(e) {
        e.preventDefault();

        const classId = $('#edit_class_id').val();

        var formData = {
            level: $('#edit_class_level').val(),
            name: $('#edit_class_name').val(),
            hourly_rate: $('#edit_class_hourly_rate').val(),
            status: $('#edit_class_status').is(':checked') ? 1 : 0,
            _token: "{{ csrf_token() }}"
        };

        $('#submit-edit-class').prop('disabled', true);
        $('#edit-class-text').addClass('d-none');
        $('#edit-class-spinner').removeClass('d-none');

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ url('classes') }}/" + classId,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editClassModal').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Classe modifiée avec succès',
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
                    $.each(errors, function(key, value) {
                        var input = $('#edit_class_' + key);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(value[0]);
                    });
                } else {
                    showSweetAlert('error', 'Erreur',
                        'Une erreur est survenue lors de la modification. ' +
                        (xhr.responseJSON?.message || 'Veuillez réessayer.')
                    );
                }
            },
            complete: function() {
                $('#submit-edit-class').prop('disabled', false);
                $('#edit-class-text').removeClass('d-none');
                $('#edit-class-spinner').addClass('d-none');
            }
        });
    });

    $('#confirm-delete-class').on('click', function() {
        if (!classToDelete) return;

        $(this).prop('disabled', true);
        $('#delete-class-text').addClass('d-none');
        $('#delete-class-spinner').removeClass('d-none');

        $.ajax({
            url: "{{ url('classes') }}/" + classToDelete,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                if (response.success) {
                    $('#deleteClassModal').modal('hide');
                    classToDelete = null;

                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message || 'Classe supprimée avec succès',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                    $('#deleteClassModal').modal('hide');
                }
            },
            error: function(xhr) {
                showSweetAlert('error', 'Erreur',
                    'Une erreur est survenue lors de la suppression. ' +
                    (xhr.responseJSON?.message || 'Veuillez réessayer.')
                );
                $('#deleteClassModal').modal('hide');
            },
            complete: function() {
                $('#confirm-delete-class').prop('disabled', false);
                $('#delete-class-text').removeClass('d-none');
                $('#delete-class-spinner').addClass('d-none');
            }
        });
    });

    $('#createClassModal').on('hidden.bs.modal', function() {
        $('#createClassForm')[0].reset();
        $('#class_status').prop('checked', true);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#submit-create-class').prop('disabled', false);
        $('#create-class-text').removeClass('d-none');
        $('#create-class-spinner').addClass('d-none');
    });

    $('#editClassModal').on('hidden.bs.modal', function() {
        $('#submit-edit-class').prop('disabled', false);
        $('#edit-class-text').removeClass('d-none');
        $('#edit-class-spinner').addClass('d-none');
    });

    $('#deleteClassModal').on('hidden.bs.modal', function() {
        classToDelete = null;
        $('#confirm-delete-class').prop('disabled', false);
        $('#delete-class-text').removeClass('d-none');
        $('#delete-class-spinner').addClass('d-none');
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
    .badge { font-size: 0.75em; padding: 0.35em 0.65em; }
    .modal-content { border-radius: 10px; }
    .modal-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; border-radius: 10px 10px 0 0; }
    .modal-title { color: #333; font-weight: 600; }
    .btn-group .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .form-check.form-switch { padding-left: 3.5em; }
    .form-check-input:checked { background-color: #198754; border-color: #198754; }
</style>
@endsection
