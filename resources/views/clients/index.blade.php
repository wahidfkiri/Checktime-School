@extends('layouts.app')

@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Gestion des écoles</h3>
                        <p class="text-subtitle text-muted">
                            Provisionnement et gestion des écoles clientes du système CheckTime
                        </p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('super-admin.dashboard') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Écoles</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <section class="section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="card-title mb-0">Liste des écoles</h4>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary filter-btn active" data-status="all">
                                    Toutes {{ $all }}
                                </button>
                                <button type="button" class="btn btn-outline-success filter-btn" data-status="active">
                                    Actives {{ $actifs }}
                                </button>
                                <button type="button" class="btn btn-outline-danger filter-btn" data-status="inactive">
                                    Inactives {{ $inactifs }}
                                </button>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="sync-all-btn">
                                <i class="bi bi-arrow-repeat me-1"></i> Tout synchroniser
                            </button>
                            <button type="button" class="btn btn-primary" id="create-client-btn">
                                <i class="bi bi-plus-circle me-1"></i> Nouvelle école
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="clients-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Raison Sociale</th>
                                        <th>RCCM</th>
                                        <th>IFU</th>
                                        <th>Directeur</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Statut</th>
                                        <th>Date création</th>
                                        <th>Actions</th>
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
<div class="modal fade" id="createClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>

<!-- Modal d'édition -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>

<style>
    .filter-btn.active { font-weight: 600; border-width: 2px; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // =================== DATATABLE ===================
        var table = $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('clients.datatable') }}",
                data: function(d) {
                    d.status = $('.filter-btn.active').data('status');
                },
                error: function(xhr) {
                    showSweetAlert('error', 'Erreur de chargement',
                        'Impossible de charger les écoles. ' +
                        (xhr.responseJSON?.error || 'Veuillez réessayer.'));
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '3%', className: 'text-center' },
                { data: 'nom_complet', name: 'raison_sociale', render: function(data) { return `<strong class="text-primary">${data}</strong>`; } },
                { data: 'rccm', name: 'rccm', className: 'text-uppercase' },
                { data: 'ifu', name: 'ifu', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'directeur', name: 'directeur', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'email_linked', name: 'email' },
                { data: 'telephone_formatted', name: 'telephone', defaultContent: '<span class="text-muted">-</span>' },
                { data: 'status_badge', name: 'is_active', orderable: false, searchable: false, className: 'text-center' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, width: '15%', className: 'text-center' }
            ],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
            responsive: true,
            order: [[1, 'asc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]]
        });

        // =================== FILTRES ===================
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            table.ajax.reload();
        });

        // =================== CRÉATION ===================
        $('#create-client-btn').click(function() {
            $.ajax({
                url: "{{ route('clients.create') }}",
                method: 'GET',
                beforeSend: function() {
                    $('#createClientModal .modal-content').html('<div class="modal-body text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Chargement...</p></div>');
                },
                success: function(response) {
                    $('#createClientModal .modal-content').html(response);
                    $('#createClientModal').modal('show');
                },
                error: function(xhr) {
                    showSweetAlert('error', 'Erreur', 'Impossible de charger le formulaire. ' + (xhr.responseJSON?.error || ''));
                }
            });
        });

        $(document).on('submit', '#create-client-form', function(e) {
            e.preventDefault();
            var form = $(this)[0];
            var formData = new FormData(form);
            var submitBtn = $(form).find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Enregistrement...');

            $.ajax({
                url: $(form).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        $('#createClientModal').modal('hide');
                        table.ajax.reload(null, false);
                        showSweetAlert('success', 'Succès', response.message, 3000);
                        form.reset();
                    } else {
                        displayFormErrors($(form), response.errors);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        displayFormErrors($(form), xhr.responseJSON.errors);
                    } else {
                        showSweetAlert('error', 'Erreur', xhr.responseJSON?.message || 'Une erreur est survenue.');
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // =================== ÉDITION ===================
        $(document).on('click', '.edit-client-btn', function() {
            var clientId = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true);
            $.ajax({
                url: "/clients/" + clientId + "/edit",
                method: 'GET',
                beforeSend: function() {
                    $('#editClientModal .modal-content').html('<div class="modal-body text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Chargement...</p></div>');
                },
                success: function(response) {
                    $('#editClientModal .modal-content').html(response);
                    $('#editClientModal').modal('show');
                },
                error: function(xhr) {
                    showSweetAlert('error', 'Erreur', 'Impossible de charger le formulaire. ' + (xhr.responseJSON?.error || ''));
                },
                complete: function() { btn.prop('disabled', false); }
            });
        });

        $(document).on('submit', '#edit-client-form', function(e) {
            e.preventDefault();
            var form = $(this)[0];
            var formData = new FormData(form);
            var clientId = $(form).data('client-id');
            var submitBtn = $(form).find('button[type="submit"]');
            var originalText = submitBtn.html();
            formData.append('_method', 'PUT');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Mise à jour...');

            $.ajax({
                url: "/clients/" + clientId,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        $('#editClientModal').modal('hide');
                        table.ajax.reload(null, false);
                        showSweetAlert('success', 'Succès', response.message, 3000);
                    } else {
                        displayFormErrors($(form), response.errors);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        displayFormErrors($(form), xhr.responseJSON.errors);
                    } else {
                        showSweetAlert('error', 'Erreur', xhr.responseJSON?.message || 'Une erreur est survenue.');
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // =================== SUPPRESSION ===================
        $(document).on('click', '.btn-delete-client', function(e) {
            e.preventDefault();
            var clientId = $(this).data('id');
            var clientName = $(this).data('name');
            if (!clientId) { return; }

            Swal.fire({
                title: 'Supprimer l\'école ?',
                html: `Êtes-vous sûr de vouloir supprimer <strong>"${clientName}"</strong> ?<br><br><span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Cette action est irréversible !</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#B4483D',
                cancelButtonColor: '#5B665F',
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: `/clients/${clientId}`,
                            type: 'DELETE',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) { resolve(response); },
                            error: function(xhr) { reject(xhr.responseJSON?.message || 'Erreur lors de la suppression'); }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Supprimée !', text: result.value.message, timer: 3000, showConfirmButton: false });
                }
            }).catch((error) => {
                Swal.fire({ icon: 'error', title: 'Erreur', text: error });
            });
        });

        // =================== CHANGEMENT DE STATUT ===================
        $(document).on('click', '.toggle-status-btn', function() {
            var clientId = $(this).data('id');
            var action = $(this).data('action');
            var actionText = action === 'activate' ? 'activation' : 'désactivation';

            Swal.fire({
                title: 'Confirmer l\'' + actionText,
                text: action === 'activate' ? 'Voulez-vous vraiment activer cette école ?' : 'Voulez-vous vraiment désactiver cette école ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2F6F62',
                cancelButtonColor: '#B4483D',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "/clients/" + clientId + "/toggle-status",
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}", action: action },
                            success: function(response) { resolve(response); },
                            error: function(xhr) { reject(xhr.responseJSON?.message || 'Erreur lors du changement de statut.'); }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    table.ajax.reload(null, false);
                    showSweetAlert('success', 'Succès', action === 'activate' ? 'École activée avec succès.' : 'École désactivée avec succès.', 3000);
                }
            }).catch((error) => { showSweetAlert('error', 'Erreur', error); });
        });

        // =================== SYNCHRO BIOMÉTRIE (une école) ===================
        $(document).on('click', '.sync-client-btn', function() {
            var clientId = $(this).data('id');
            var clientName = $(this).data('name');
            Swal.fire({
                title: 'Synchroniser « ' + clientName + ' » ?',
                text: 'Récupère enseignants, zones, départements et appareils depuis l\'API biométrique.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2F6F62',
                cancelButtonColor: '#5B665F',
                confirmButtonText: 'Synchroniser',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "/super-admin/schools/" + clientId + "/sync",
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(r) { resolve(r); },
                            error: function(xhr) { reject(xhr.responseJSON?.message || 'Erreur de synchronisation.'); }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    var c = result.value.counts || {};
                    var fmt = function(v) { return v === null ? '<span class="text-danger">erreur</span>' : v; };
                    table.ajax.reload(null, false);
                    Swal.fire({
                        icon: 'success',
                        title: 'Synchronisation terminée',
                        html: 'Enseignants : <strong>' + fmt(c.employees) + '</strong><br>' +
                              'Zones : <strong>' + fmt(c.zones) + '</strong><br>' +
                              'Départements : <strong>' + fmt(c.departments) + '</strong><br>' +
                              'Appareils : <strong>' + fmt(c.devices) + '</strong>'
                    });
                }
            }).catch((error) => { showSweetAlert('error', 'Erreur', error); });
        });

        // =================== TOUT SYNCHRONISER ===================
        $('#sync-all-btn').click(function() {
            Swal.fire({
                title: 'Tout synchroniser ?',
                text: 'Lance la synchro biométrique de toutes les écoles actives. Cela peut prendre du temps.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2F6F62',
                cancelButtonColor: '#5B665F',
                confirmButtonText: 'Lancer',
                cancelButtonText: 'Annuler',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading(),
                preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "{{ route('super-admin.schools.sync-all') }}",
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(r) { resolve(r); },
                            error: function(xhr) { reject(xhr.responseJSON?.message || 'Erreur de synchronisation globale.'); }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed && result.value && result.value.success) {
                    table.ajax.reload(null, false);
                    showSweetAlert('success', 'Terminé', result.value.message, 6000);
                }
            }).catch((error) => { showSweetAlert('error', 'Erreur', error); });
        });

        // =================== VÉRIFICATION RCCM ===================
        $(document).on('blur', '#rccm', function() {
            var rccm = $(this).val();
            var clientId = $(this).data('client-id');
            if (rccm && rccm.length > 0) {
                $.ajax({
                    url: "{{ route('clients.check-rccm') }}",
                    method: 'GET',
                    data: { rccm: rccm, client_id: clientId || '' },
                    success: function(response) {
                        var input = $('#rccm');
                        var feedback = $('#rccm-feedback');
                        if (response.exists) {
                            input.addClass('is-invalid');
                            feedback.text(response.message).addClass('text-danger');
                        } else {
                            input.removeClass('is-invalid');
                            feedback.text('RCCM disponible').removeClass('text-danger').addClass('text-success');
                            setTimeout(function() { feedback.text(''); }, 3000);
                        }
                    }
                });
            }
        });

        // =================== UTILITAIRES ===================
        function displayFormErrors(form, errors) {
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('');
            if (errors && Object.keys(errors).length > 0) {
                $.each(errors, function(field, messages) {
                    var input = form.find('[name="' + field + '"]');
                    if (input.length > 0) {
                        input.addClass('is-invalid');
                        var feedback = input.next('.invalid-feedback');
                        if (feedback.length === 0) {
                            input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                        } else {
                            feedback.text(messages[0]);
                        }
                    }
                });
                showSweetAlert('warning', 'Formulaire invalide', 'Veuillez corriger les erreurs du formulaire.');
            }
        }

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
        window.showSweetAlert = showSweetAlert;
        window.displayFormErrors = displayFormErrors;

        $(document).on('hidden.bs.modal', '.modal', function() {
            $(this).find('.is-invalid').removeClass('is-invalid');
            $(this).find('.invalid-feedback').remove();
            setTimeout(() => {
                if (!$(this).hasClass('show')) { $(this).find('.modal-content').html(''); }
            }, 300);
        });
    });
</script>
@endsection
