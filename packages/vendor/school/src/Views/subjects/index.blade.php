@extends('layouts.app')

@section('content')
@php
    $renderChecks = function ($items, $type, $prefix) {
        $html = '';
        foreach ($items as $it) {
            if ($type === 'class') {
                $label = e($it->level . ' · ' . $it->name);
            } else {
                $name = trim($it->first_name . ' ' . ($it->last_name ?? ''));
                $label = e($name) . ($it->emp_code ? ' <small class="text-muted">(' . e($it->emp_code) . ')</small>' : '');
            }
            $cid = $prefix . '_' . $type . '_' . $it->id;
            $field = $type === 'class' ? 'class_ids[]' : 'teacher_ids[]';
            $html .= '<div class="form-check">'
                . '<input class="form-check-input ' . $prefix . '-' . $type . '-check" type="checkbox" '
                . 'name="' . $field . '" value="' . $it->id . '" id="' . $cid . '">'
                . '<label class="form-check-label" for="' . $cid . '">' . $label . '</label>'
                . '</div>';
        }
        return $html ?: '<p class="text-muted mb-0"><small>Aucun élément disponible.</small></p>';
    };
@endphp
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Gestion des matières</h3>
                        <p class="text-subtitle text-muted">Matières enseignées, liées aux classes et aux enseignants</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Matières</li>
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
                                    <label for="name_filter">Matière</label>
                                    <input type="text" class="form-control" id="name_filter" placeholder="Rechercher par nom ou code...">
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
                            <div class="col-md-6">
                                <div class="form-group text-start">
                                    <label class="form-label d-block" style="margin-bottom:0px;">&nbsp;</label>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" id="create-subject-button" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                            <i class="bi bi-plus-circle me-1"></i> Nouvelle matière
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
                            <table id="subjects-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th width="30%">Matière</th>
                                        <th width="15%">Code</th>
                                        <th width="17%">Classes</th>
                                        <th width="18%">Enseignants</th>
                                        <th width="10%">Statut</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Modal de création -->
<div class="modal fade" id="createSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer une nouvelle matière</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSubjectForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="subject_name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject_name" name="name" required maxlength="100" placeholder="ex: Mathématiques">
                            <div class="invalid-feedback" id="name-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="subject_code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="subject_code" name="code" maxlength="50" placeholder="ex: MATHS">
                            <div class="invalid-feedback" id="code-error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject_description" class="form-label">Description</label>
                        <textarea class="form-control" id="subject_description" name="description" rows="2" maxlength="1000"></textarea>
                        <div class="invalid-feedback" id="description-error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Classes concernées</label>
                            <div class="border rounded p-2 check-panel">
                                {!! $renderChecks($classes, 'class', 'create') !!}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enseignants</label>
                            <div class="border rounded p-2 check-panel">
                                {!! $renderChecks($teachers, 'teacher', 'create') !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="subject_status" name="status" value="1" checked>
                        <label class="form-check-label" for="subject_status">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-create-subject">
                        <span id="create-subject-text">Créer</span>
                        <span id="create-subject-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier la matière</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubjectForm">
                <input type="hidden" id="edit_subject_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_subject_name" class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_subject_name" name="name" required maxlength="100">
                            <div class="invalid-feedback" id="edit-name-error"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_subject_code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="edit_subject_code" name="code" maxlength="50">
                            <div class="invalid-feedback" id="edit-code-error"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_subject_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_subject_description" name="description" rows="2" maxlength="1000"></textarea>
                        <div class="invalid-feedback" id="edit-description-error"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Classes concernées</label>
                            <div class="border rounded p-2 check-panel">
                                {!! $renderChecks($classes, 'class', 'edit') !!}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enseignants</label>
                            <div class="border rounded p-2 check-panel">
                                {!! $renderChecks($teachers, 'teacher', 'edit') !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_subject_status" name="status" value="1">
                        <label class="form-check-label" for="edit_subject_status">Actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submit-edit-subject">
                        <span id="edit-subject-text">Enregistrer</span>
                        <span id="edit-subject-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cette matière ?</p>
                <p><strong>Matière :</strong> <span id="delete-subject-name"></span></p>
                <p class="text-danger"><small>Cette action est irréversible.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-subject">
                    <span id="delete-subject-text">Supprimer</span>
                    <span id="delete-subject-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let subjectToDelete = null;

    var table = $('#subjects-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('subjects.local') }}",
            data: function (d) {
                d.name_filter = $('#name_filter').val();
                d.status_filter = $('#status_filter').val();
            }
        },
        columns: [
            { data: 'name', name: 'name', width: '30%' },
            { data: 'code', name: 'code', width: '15%', render: function(d) { return d ? d : '<span class="text-muted">-</span>'; } },
            { data: 'classes_count', name: 'classes_count', searchable: false, width: '17%',
              render: function(d) { return '<span class="badge bg-light text-dark">' + (d || 0) + ' classe(s)</span>'; } },
            { data: 'teachers_count', name: 'teachers_count', searchable: false, width: '18%',
              render: function(d) { return '<span class="badge bg-light text-dark">' + (d || 0) + ' ens.</span>'; } },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, width: '10%' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '10%' }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/French.json" },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
        order: [[0, 'asc']],
        responsive: true,
        drawCallback: function() { attachActionButtons(); }
    });

    function attachActionButtons() {
        $('.edit-subject-btn').off('click').on('click', function() {
            openEditModal($(this).data('id'));
        });
        $('.delete-subject-btn').off('click').on('click', function() {
            openDeleteModal($(this).data('id'), $(this).data('name'));
        });
    }

    function openEditModal(id) {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('.edit-class-check, .edit-teacher-check').prop('checked', false);

        $.ajax({
            url: "{{ url('subjects') }}/" + id,
            type: 'GET',
            success: function(response) {
                if (!response.success) {
                    showSweetAlert('error', 'Erreur', response.message || 'Matière introuvable');
                    return;
                }
                var s = response.subject;
                $('#edit_subject_id').val(s.id);
                $('#edit_subject_name').val(s.name);
                $('#edit_subject_code').val(s.code || '');
                $('#edit_subject_description').val(s.description || '');
                $('#edit_subject_status').prop('checked', s.status == 1);
                (s.class_ids || []).forEach(function(cid) {
                    $('#edit_class_' + cid).prop('checked', true);
                });
                (s.teacher_ids || []).forEach(function(tid) {
                    $('#edit_teacher_' + tid).prop('checked', true);
                });
                $('#editSubjectModal').modal('show');
            },
            error: function() {
                showSweetAlert('error', 'Erreur', 'Impossible de charger la matière.');
            }
        });
    }

    function openDeleteModal(id, name) {
        subjectToDelete = id;
        $('#delete-subject-name').text(name);
        $('#deleteSubjectModal').modal('show');
    }

    function checkedValues(selector) {
        return $(selector).map(function() { return this.value; }).get();
    }

    function applyFilters() { table.ajax.reload(); }
    $('#status_filter').on('change', applyFilters);
    $('#name_filter').on('change keyup', applyFilters);
    $('#reset_filters').on('click', function() {
        $('#status_filter').val('');
        $('#name_filter').val('');
        applyFilters();
    });

    function showSweetAlert(icon, title, text, timer = null) {
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: timer || 5000, timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({ icon: icon, title: title, text: text });
    }

    function showFieldErrors(errors, prefix) {
        $.each(errors, function(key, value) {
            var base = key.replace('_ids', '').replace('.', '-');
            var input = $('#' + prefix + base.replace('-', '_'));
            input.addClass('is-invalid');
            $('#' + (prefix === 'edit_subject_' ? 'edit-' : '') + base + '-error').text(value[0]);
        });
    }

    $('#createSubjectForm').on('submit', function(e) {
        e.preventDefault();
        var formData = {
            name: $('#subject_name').val(),
            code: $('#subject_code').val(),
            description: $('#subject_description').val(),
            status: $('#subject_status').is(':checked') ? 1 : 0,
            class_ids: checkedValues('.create-class-check:checked'),
            teacher_ids: checkedValues('.create-teacher-check:checked'),
            _token: "{{ csrf_token() }}"
        };

        $('#submit-create-subject').prop('disabled', true);
        $('#create-subject-text').addClass('d-none');
        $('#create-subject-spinner').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ route('subjects.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#createSubjectModal').modal('hide');
                    $('#createSubjectForm')[0].reset();
                    $('#subject_status').prop('checked', true);
                    $('.create-class-check, .create-teacher-check').prop('checked', false);
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message, timer: 3000, showConfirmButton: false });
                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    showFieldErrors(xhr.responseJSON.errors, 'subject_');
                } else {
                    showSweetAlert('error', 'Erreur', 'Une erreur est survenue. ' + (xhr.responseJSON?.message || ''));
                }
            },
            complete: function() {
                $('#submit-create-subject').prop('disabled', false);
                $('#create-subject-text').removeClass('d-none');
                $('#create-subject-spinner').addClass('d-none');
            }
        });
    });

    $('#editSubjectForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_subject_id').val();
        var formData = {
            name: $('#edit_subject_name').val(),
            code: $('#edit_subject_code').val(),
            description: $('#edit_subject_description').val(),
            status: $('#edit_subject_status').is(':checked') ? 1 : 0,
            class_ids: checkedValues('.edit-class-check:checked'),
            teacher_ids: checkedValues('.edit-teacher-check:checked'),
            _token: "{{ csrf_token() }}"
        };

        $('#submit-edit-subject').prop('disabled', true);
        $('#edit-subject-text').addClass('d-none');
        $('#edit-subject-spinner').removeClass('d-none');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: "{{ url('subjects') }}/" + id,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editSubjectModal').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message, timer: 3000, showConfirmButton: false });
                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    showFieldErrors(xhr.responseJSON.errors, 'edit_subject_');
                } else {
                    showSweetAlert('error', 'Erreur', 'Une erreur est survenue. ' + (xhr.responseJSON?.message || ''));
                }
            },
            complete: function() {
                $('#submit-edit-subject').prop('disabled', false);
                $('#edit-subject-text').removeClass('d-none');
                $('#edit-subject-spinner').addClass('d-none');
            }
        });
    });

    $('#confirm-delete-subject').on('click', function() {
        if (!subjectToDelete) return;
        $(this).prop('disabled', true);
        $('#delete-subject-text').addClass('d-none');
        $('#delete-subject-spinner').removeClass('d-none');

        $.ajax({
            url: "{{ url('subjects') }}/" + subjectToDelete,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(response) {
                $('#deleteSubjectModal').modal('hide');
                if (response.success) {
                    subjectToDelete = null;
                    Swal.fire({ icon: 'success', title: 'Succès', text: response.message, timer: 3000, showConfirmButton: false });
                    table.ajax.reload();
                } else {
                    showSweetAlert('error', 'Erreur', response.message);
                }
            },
            error: function(xhr) {
                $('#deleteSubjectModal').modal('hide');
                showSweetAlert('error', 'Erreur', (xhr.responseJSON?.message || 'Erreur lors de la suppression.'));
            },
            complete: function() {
                $('#confirm-delete-subject').prop('disabled', false);
                $('#delete-subject-text').removeClass('d-none');
                $('#delete-subject-spinner').addClass('d-none');
            }
        });
    });

    $('#createSubjectModal').on('hidden.bs.modal', function() {
        $('#createSubjectForm')[0].reset();
        $('#subject_status').prop('checked', true);
        $('.create-class-check, .create-teacher-check').prop('checked', false);
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    });
});
</script>
<style>
    .dataTables_wrapper { padding: 10px 0; }
    .dataTables_length, .dataTables_filter { margin-bottom: 15px; }
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
    .check-panel { max-height: 180px; overflow-y: auto; background:#fff; }
</style>
@endsection
