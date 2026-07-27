@extends('layouts.app')
@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Supervision — Enseignants</h3>
                        <p class="text-subtitle text-muted">Tous les enseignants, toutes écoles confondues</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Enseignants</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <section class="section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="card-title mb-0">Enseignants</h4>
                        <div style="min-width: 260px;">
                            <select id="school-filter" class="form-select form-select-sm">
                                <option value="">Toutes les écoles</option>
                                @foreach($schools as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="sup-table" class="table table-striped table-hover" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>École</th>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        <th>Téléphone</th>
                                        <th>Email</th>
                                        <th>Statut</th>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#sup-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('super-admin.supervision.teachers.data') }}",
            data: function(d) { d.client_id = $('#school-filter').val(); }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '3%' },
            { data: 'ecole', name: 'ecole', orderable: false, searchable: false },
            { data: 'emp_code', name: 'emp_code' },
            { data: 'nom', name: 'nom', orderable: false, searchable: false },
            { data: 'phone', name: 'phone', defaultContent: '-' },
            { data: 'email', name: 'email', defaultContent: '-' },
            { data: 'status', name: 'status', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[2, 'asc']],
        language: { url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json" },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
    $('#school-filter').on('change', function() { table.ajax.reload(); });
});
</script>
@endsection
