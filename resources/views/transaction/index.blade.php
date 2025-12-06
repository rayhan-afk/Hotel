@extends('template.master')
@section('title', 'Reservation')
@section('content')
    <div class="row mt-2 mb-2">
        <div class="col-lg-6 mb-2">
            <div class="d-grid gap-2 d-md-block">
                <span data-bs-toggle="tooltip" data-bs-placement="right" title="Add Room Reservation">
                    <button type="button" class="btn btn-sm shadow-sm myBtn border rounded" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="fas fa-plus"></i>
                    </button>
                </span>
                {{-- Tombol History Payment DIHAPUS --}}
            </div>
        </div>
        <div class="col-lg-6 mb-2">
            <form class="d-flex" method="GET" action="{{ route('transaction.index') }}">
                <input class="form-control me-2" type="search" placeholder="Search by ID" aria-label="Search"
                    id="search-user" name="search" value="{{ request()->input('search') }}">
                <button class="btn btn-outline-dark" type="submit">Cari</button>
            </form>
        </div>
    </div>
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12">
            <h5>Tamu Aktif: </h5>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card p-0">
                <div class="card-body">
                    <div class="table-responsive" style="max-width: calc(100vw - 50px)">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Pengguna</th>
                                    <th>Kamar</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Hari</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <th>{{ ($transactions->currentpage() - 1) * $transactions->perpage() + $loop->index + 1 }}
                                        </th>
                                        <td>{{ $transaction->id }}</td>
                                        <td>{{ $transaction->customer->name }}</td>
                                        <td>{{ $transaction->room->number }}</td>
                                        <td>{{ Helper::dateFormat($transaction->check_in) }}</td>
                                        <td>{{ Helper::dateFormat($transaction->check_out) }}</td>
                                        <td>{{ $transaction->getDateDifferenceWithPlural($transaction->check_in, $transaction->check_out) }}
                                        </td>
                                        <td>{{ Helper::convertToRupiah($transaction->getTotalPrice()) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-success">Lunas (Paid)</span>
                                        </td>
                                        <td>
                                            {{-- Tombol Pay DIHAPUS/DISABLED --}}
                                            <button class="btn btn-light btn-sm rounded shadow-sm border p-1 m-0 disabled"
                                                disabled
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Already Paid">
                                                <i class="fas fa-check-circle text-success"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" class="text-center">
                                            Tidak ada data di tabel ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $transactions->onEachSide(2)->links('template.paginationlinks') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-2 mt-4 ms-1">
        <div class="col-lg-12">
            <h5>Kadaluarsa / Selesai: </h5>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card p-0">
                <div class="card-body">
                    <div class="table-responsive" style="max-width: calc(100vw - 50px)">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ID</th>
                                    <th>Pengguna</th>
                                    <th>Kamar</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Hari</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactionsExpired as $transaction)
                                <tr>
                                    <th>{{ ($transactions->currentpage() - 1) * $transactions->perpage() + $loop->index + 1 }}
                                    </th>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->customer->name }}</td>
                                    <td>{{ $transaction->room->number }}</td>
                                    <td>{{ Helper::dateFormat($transaction->check_in) }}</td>
                                    <td>{{ Helper::dateFormat($transaction->check_out) }}</td>
                                    <td>{{ $transaction->getDateDifferenceWithPlural($transaction->check_in, $transaction->check_out) }}
                                    </td>
                                    <td>{{ Helper::convertToRupiah($transaction->getTotalPrice()) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Selesai</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-light btn-sm rounded shadow-sm border p-1 m-0 disabled"
                                            disabled>
                                            <i class="fas fa-check-circle text-success"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center">
                                        Tidak ada data di tabel ini
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                        {{ $transactions->onEachSide(2)->links('template.paginationlinks') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Sudah Punya Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <a class="btn btn-sm btn-primary m-1"
                            href="{{ route('transaction.reservation.createIdentity') }}">Belum, buat akun baru!</a>
                        <a class="btn btn-sm btn-success m-1"
                            href="{{ route('transaction.reservation.pickFromCustomer') }}">Ya, gunakan akun mereka!</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection