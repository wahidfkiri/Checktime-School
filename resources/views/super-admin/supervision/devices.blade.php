@extends('layouts.app')
@section('content')
<div id="main" class="layout-navbar navbar-fixed">
    <x-nav-bar />
    <div id="main-content">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>Supervision — Appareils</h3>
                        <p class="text-subtitle text-muted">Toutes les pointeuses biométriques, toutes écoles confondues</p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('super-admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Appareils</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <section class="section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h4 class="card-title mb-0">Appareils</h4>
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
                                        <th>N° série</th>
                                        <th>Alias</th>
                                        <th>Terminal</th>
                                        <th>Zone</th>
                                        <th>IP</th>
                                        <th>Dernière synchro</th>
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
            url: "{{ route('super-admin.supervision.devices.data') }}",
            data: function(d) { d.client_id = $('#school-filter').val(); }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '3%' },
            { data: 'ecole', name: 'ecole', orderable: false, searchable: false },
            { data: 'device_sn', name: 'device_sn' },
            { data: 'alias', name: 'alias', orderable: false, searchable: false, defaultContent: '-' },
            { data: 'terminal_name', name: 'terminal_name', orderable: false, searchable: false, defaultContent: '-' },
            { data: 'area_name', name: 'area_name', orderable: false, searchable: false, defaultContent: '-' },
            { data: 'ip', name: 'ip', orderable: false, searchable: false, defaultContent: '-' },
            { data: 'last_synced_at', name: 'last_synced_at', orderable: false, searchable: false }
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
