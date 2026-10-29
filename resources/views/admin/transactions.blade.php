@extends('layouts.adminlte')

@section('title', 'Kelola VIP - SCREENED')

@section('content')
<div class="container-fluid">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0">Kelola VIP</h1>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTxModal">
            <i class="bi bi-plus-circle"></i> Tambah VIP
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ============ RINGKASAN ============ --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="bi bi-cash-stack"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendapatan VIP</span>
                    <span class="info-box-number">Rp {{ number_format($stats['success_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="bi bi-check2-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Berhasil</span>
                    <span class="info-box-number">{{ $stats['success_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="bi bi-hourglass-split"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $stats['pending_count'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="bi bi-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Transaksi</span>
                    <span class="info-box-number">{{ $stats['total_count'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ PAKET VIP (harga + masa berlaku, bisa ditambah & dihapus) ============ --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header py-2">
            <h3 class="card-title h6 mb-0"><i class="bi bi-gem"></i> Paket VIP — tambah &amp; hapus paket</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Paket</th>
                                    <th>Harga</th>
                                    <th>Berlaku</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $package)
                                    <tr>
                                        <td class="fw-semibold">{{ $package->name }}</td>
                                        <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                                        <td><span class="badge text-bg-dark">{{ $package->duration_days }} hari</span></td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.packages.destroy', $package->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    data-confirm
                                                    data-title="Hapus paket ini?"
                                                    data-text="Paket &quot;{{ $package->name }}&quot; akan dihapus dari daftar pembelian."
                                                    data-submit="Ya, Hapus">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-body-secondary py-3">
                                            Belum ada paket VIP. Tambahkan paket di samping —
                                            halaman VIP memakai paket yang dibuat di sini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-5">
                    <form method="POST" action="{{ route('admin.packages.store') }}" class="border rounded p-3 h-100 bg-body-secondary">
                        @csrf
                        <h4 class="h6 fw-semibold mb-3"><i class="bi bi-plus-square"></i> Tambah Paket</h4>

                        <div class="mb-2">
                            <label class="form-label" for="pkg_name">Nama paket</label>
                            <input type="text" name="name" id="pkg_name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" maxlength="60" placeholder="VIP Bulanan" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" for="pkg_price">Harga (Rp)</label>
                                <input type="number" name="price" id="pkg_price" class="form-control @error('price') is-invalid @enderror"
                                    value="{{ old('price') }}" min="1000" step="1000" placeholder="30000" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="pkg_days">Berlaku (hari)</label>
                                <input type="number" name="duration_days" id="pkg_days" class="form-control @error('duration_days') is-invalid @enderror"
                                    value="{{ old('duration_days') }}" min="1" max="3650" placeholder="30" required>
                                @error('duration_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm w-100 mt-3">
                            <i class="bi bi-plus-lg"></i> Tambahkan Paket
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ DATA BERLANGGANAN & TRANSAKSI (AJAX DataTables) ============ --}}
    <div class="card shadow-sm">
        <div class="card-header py-2">
            <h3 class="card-title h6 mb-0"><i class="bi bi-people"></i> Data Berlangganan &amp; Transaksi</h3>
            <div class="card-tools">
                <select id="txStatusFilter" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">Semua Status</option>
                    @foreach(['pending', 'success', 'failed', 'expired'] as $statusOption)
                        <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="txTable" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>User</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Berlaku</th>
                            <th>Status</th>
                            <th>Metode</th>
                            <th>Order ID</th>
                            <th>Dibuat</th>
                            <th style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ============ MODAL TAMBAH TRANSAKSI MANUAL ============ --}}
<div class="modal fade" id="addTxModal" tabindex="-1" aria-labelledby="addTxLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.transactions.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5" id="addTxLabel"><i class="bi bi-crown"></i> Tambah VIP (Pembayaran Manual)</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p class="small text-body-secondary mb-3">
                    Untuk pembayaran di luar Midtrans (tunai / transfer manual). Status
                    <b>success</b> langsung menambah masa aktif VIP user.
                </p>

                <div class="mb-2">
                    <label class="form-label" for="tx_user">User</label>
                    <select name="users_id" id="tx_user" class="form-select @error('users_id') is-invalid @enderror" required>
                        <option value="">— pilih user —</option>
                        @foreach($users as $userOption)
                            <option value="{{ $userOption->id }}" @selected((string) old('users_id') === (string) $userOption->id)>
                                {{ $userOption->username }} ({{ $userOption->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('users_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" for="tx_amount">Harga (Rp)</label>
                        <input type="number" name="amount" id="tx_amount" class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}" min="1" placeholder="30000" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="tx_days">Berlaku (hari)</label>
                        <input type="number" name="duration_days" id="tx_days" class="form-control @error('duration_days') is-invalid @enderror"
                            value="{{ old('duration_days', 30) }}" min="1" max="3650" required>
                        @error('duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-2 mt-0">
                    <div class="col-6">
                        <label class="form-label" for="tx_status">Status</label>
                        <select name="status" id="tx_status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach(['success', 'pending', 'failed', 'expired'] as $statusOption)
                                <option value="{{ $statusOption }}" @selected(old('status', 'success') === $statusOption)>
                                    {{ ucfirst($statusOption) }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="tx_method">Metode</label>
                        <input type="text" name="payment_method" id="tx_method" class="form-control @error('payment_method') is-invalid @enderror"
                            value="{{ old('payment_method', 'Manual') }}" maxlength="50" placeholder="Tunai / Transfer">
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save"></i> Simpan Transaksi</button>
            </div>
        </form>
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
    #txTable td:last-child form { display: inline; }
    #txTable td:last-child .btn {
        padding: .25rem .5rem;
        font-size: .75rem;
        line-height: 1.2;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
    (function () {
        function esc(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function rp(n) {
            return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }

        var statusColor = { success: 'success', pending: 'warning', failed: 'danger', expired: 'secondary' };

        var table = $('#txTable').DataTable({
            ajax: {
                url: @json(route('admin.api.transactions')),
                data: function (d) {
                    d.status = document.getElementById('txStatusFilter').value || '';
                }
            },
            order: [[8, 'desc']],
            columns: [
                { data: null, orderable: false, searchable: false, defaultContent: '' },
                {
                    data: 'username',
                    orderable: false,
                    render: function (data, type, row) {
                        var html = '<span class="fw-semibold">' + esc(row.username) + '</span>' +
                            '<br><small class="text-body-secondary">' + esc(row.email) + '</small>';
                        if (row.vip_until) {
                            html += '<br><small class="text-warning">VIP s/d ' + esc(row.vip_until) + '</small>';
                        }
                        return html;
                    }
                },
                { data: 'type', orderable: false, render: function (d) { return '<span class="badge text-bg-dark">' + esc(d) + '</span>'; } },
                { data: 'amount', render: function (d) { return rp(d); } },
                {
                    data: 'duration_days',
                    render: function (d) {
                        return d ? '<span class="badge text-bg-secondary">' + d + ' hari</span>' : '-';
                    }
                },
                {
                    data: 'status',
                    orderable: false,
                    render: function (d) {
                        return '<span class="badge text-bg-' + (statusColor[d] || 'secondary') + '">' + esc(d) + '</span>';
                    }
                },
                { data: 'payment_method', orderable: false, render: function (d) { return esc(d); } },
                { data: 'midtrans_order_id', orderable: false, render: function (d) { return '<code class="small">' + esc(d) + '</code>'; } },
                {
                    data: 'created_at',
                    render: function (d, type, row) {
                        var html = '<small>' + esc(d || '-') + '</small>';
                        if (row.paid_at) {
                            html += '<br><small class="text-success">dibayar ' + esc(row.paid_at) + '</small>';
                        }
                        return html;
                    }
                },
                { data: 'action', orderable: false, searchable: false }
            ],
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            drawCallback: function () {
                var start = this.api().page.info().start;
                $('#txTable tbody tr').each(function (i) {
                    $('td:eq(0)', this).text(start + i + 1);
                });
            },
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ transaksi",
                processing: "Memuat...",
                emptyTable: "Belum ada transaksi.",
                zeroRecords: "Transaksi tidak ditemukan.",
                paginate: {
                    first: "Pertama", last: "Terakhir",
                    next: "Berikutnya", previous: "Sebelumnya"
                }
            }
        });

        // filter status via AJAX
        document.getElementById('txStatusFilter').addEventListener('change', function () {
            table.ajax.reload();
        });

        // kalau ada error validasi, buka lagi modal tambah transaksi
        @if($errors->any())
        (function () {
            var el = document.getElementById('addTxModal');
            if (el && window.bootstrap) bootstrap.Modal.getOrCreateInstance(el).show();
        })();
        @endif
    })();
</script>

@endsection
