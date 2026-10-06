@extends('layouts.adminlte')
@section('title', 'Manage Items - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Manage Items</h1>
        <a href="{{ route('admin.add') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add New
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-table"></i> Daftar Item</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="manageTable" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Year</th>
                            <th>Genre</th>
                            <th>Rating</th>
                            <th style="width: 160px;">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper { width: 100% !important; }
    table.dataTable thead th.sorting,
    table.dataTable thead th.sorting_asc,
    table.dataTable thead th.sorting_desc {
        background-image: none !important;
    }

    /* action buttons come from AdminController::apiIndex() */
    #manageTable td:last-child a {
        display: inline-block;
        padding: .25rem .5rem;
        font-size: .75rem;
        line-height: 1.2;
        color: #fff;
        text-decoration: none;
        background-color: #0d6efd;
        border: 1px solid #0d6efd;
        border-radius: .25rem;
    }
    #manageTable td:last-child button {
        padding: .25rem .5rem;
        font-size: .75rem;
        line-height: 1.2;
        color: #fff;
        background-color: #dc3545;
        border: 1px solid #dc3545;
        border-radius: .25rem;
    }
    #manageTable td:last-child form {
        display: inline;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#manageTable').DataTable({
            ajax: '{{ route("admin.api.items") }}',
            ordering: true,
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: '' },
                { data: 'title', orderable: false },
                { data: 'type', orderable: false },
                { data: 'release_year', orderable: false },
                { data: 'genre', orderable: false },
                { data: 'rating', orderable: false },
                { data: 'action', orderable: false, searchable: false }
            ],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            drawCallback: function() {
                var start = this.api().page.info().start;
                $('#manageTable tbody tr').each(function(i) {
                    $('td:eq(0)', this).text(start + i + 1);
                });
            },
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ items",
                processing: "Loading...",
                emptyTable: "No data available",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });
</script>

@endsection
