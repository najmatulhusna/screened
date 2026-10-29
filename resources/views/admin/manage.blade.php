@extends('layouts.adminlte')
@section('title', 'Manage Items - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Manage Items</h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle"></i> Add New
        </button>
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

@include('admin._item_modals')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    .dataTables_wrapper { width: 100% !important; }
    table.dataTable thead th.sorting,
    table.dataTable thead th.sorting_asc,
    table.dataTable thead th.sorting_desc {
        background-image: none !important;
    }

    /* action buttons come from AdminController::apiIndex() */
    #manageTable td:last-child .btn-edit {
        display: inline-block;
        padding: .25rem .5rem;
        font-size: .75rem;
        line-height: 1.2;
        color: #fff;
        text-decoration: none;
        background-color: #0d6efd;
        border: 1px solid #0d6efd;
        border-radius: .25rem;
        cursor: pointer;
    }
    #manageTable td:last-child form button {
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
    function fillEditModal(d, id) {
        d = d || {};
        var form = document.getElementById('editItemForm');
        form.setAttribute('action', form.dataset.actionTemplate.replace('__ID__', id));

        $('#edit_id').val(id);
        $('#edit_title').val(d.title || '');
        $('#edit_type').val(d.type || 'Movie');
        $('#edit_release_year').val(d.release_year || '');
        $('#edit_genre').val(d.genre || '');
        $('#edit_country').val(d.country || '');
        $('#edit_director_creator').val(d.director_creator || '');
        $('#edit_cast_list').val(d.cast_list || '');
        $('#edit_duration').val(d.duration || '');
        $('#edit_total_episodes').val(d.total_episodes || '');
        $('#edit_synopsis').val(d.synopsis || '');
        $('#edit_status').val(d.status || 'Plan to Watch');
        $('#edit_episode_watched').val(d.episode_watched ?? 0);
        $('#edit_rating').val(d.review_rating ?? '');
        $('#edit_review_text').val(d.review_text || '');
        $('#edit_poster').val('');
        $('#edit_trailer_url').val(d.trailer_url || '');

        // stream links per episode (dari API admin, bentuk map {episode: url})
        var slBox = document.getElementById('edit_stream_links');
        if (slBox) {
            var slMap = {};
            if (Array.isArray(d.stream_links)) {
                d.stream_links.forEach(function (r) {
                    if (r) slMap[String(r.episode_number)] = r.url || '';
                });
            } else if (d.stream_links) {
                slMap = d.stream_links;
            }
            slBox.setAttribute('data-existing', JSON.stringify(slMap));
            if (window.renderStreamLinks) window.renderStreamLinks(slBox);
        }

        if (d.poster) {
            $('#edit_poster_preview').attr('src', d.poster).removeClass('d-none');
            $('#edit_poster_none').addClass('d-none');
        } else {
            $('#edit_poster_preview').addClass('d-none');
            $('#edit_poster_none').removeClass('d-none');
        }
    }

    $(document).ready(function() {
        var table = $('#manageTable').DataTable({
            ajax: '{{ route("admin.api.items") }}',
            ordering: true,
            order: [[1, 'asc']],
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: '' },
                { data: 'title', orderable: false },
                { data: 'type', orderable: false },
                { data: 'release_year', orderable: false },
                { data: 'genre', orderable: false },
                {
                    data: 'rating',
                    orderable: false,
                    render: function(data) {
                        return (data === null || data === '') ? '-' : data;
                    }
                },
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

        $('#manageTable tbody').on('click', '.btn-edit', function() {
            var row = table.row($(this).closest('tr')).data();
            fillEditModal(row, $(this).data('id'));
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
        });
    });

    @if($errors->any())
    (function() {
        var oldData = @json(old());
        var which = oldData._open_modal || 'add';

        if (which === 'edit' && oldData.id) {
            oldData.review_rating = oldData.rating ?? '';
            fillEditModal(oldData, oldData.id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal')).show();
        } else {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addModal')).show();
        }
    })();
    @endif
</script>

@endsection
