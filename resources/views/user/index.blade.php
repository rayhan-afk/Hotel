@extends('template.master')
@section('title', 'User')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="row mt-2 mb-2">
                <div class="col-lg-6 mb-2">
                    <div class="d-grid gap-2 d-md-block">
                        <a href="{{ route('user.create') }}" class="btn btn-search-custom shadow-sm myBtn border rounded"
                            data-bs-toggle="tooltip" data-bs-placement="right" title="Add User">
                            <svg width="25" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 mb-2">
                    <form class="d-flex" method="GET" action="{{ route('user.index') }}">
                        <input class="form-control me-2" type="search" placeholder="Cari User Hotel" aria-label="Search"
                            id="search-user" name="qu" value="{{ request()->input('qu') }}">
                        <button class="btn btn-search-custom" type="submit">Cari</button>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm border">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-custom">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">User</th>
                                            <th scope="col">Kontak</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Bergabung</th>
                                            <th scope="col">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users as $user)
                                            <tr>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    {{ ($users->currentpage() - 1) * $users->perpage() + $loop->index + 1 }}
                                                </td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    <div class="d-flex align-items-center">
                                                        {{-- Avatar Placeholder dari Inisial Nama --}}
                                                        <div class="rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold shadow-sm" 
                                                             style="width: 40px; height: 40px; background-color: #F7F3E4; color: #50200C; border: 1px solid #ddd;">
                                                            {{ substr($user->name, 0, 2) }}
                                                        </div>
                                                        <span class="fw-bold">{{ $user->name }}</span>
                                                    </div>
                                                </td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">{{ $user->email }}</td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    <span class="badge rounded-pill" style="background-color: #50200C; color: #fff; font-weight: normal;">
                                                        {{ $user->role }}
                                                    </span>
                                                </td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    {{-- Simulasi Status Active --}}
                                                    <span class="badge rounded-pill bg-success" style="font-weight: normal;">
                                                        Active
                                                    </span>
                                                </td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    {{-- Format Tanggal: 31 Dec 2025 --}}
                                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                                                </td>
                                                <td class="align-middle" style="background-color: #F7F3E4;">
                                                    <a class="btn btn-sm rounded shadow-sm border p-0 m-0"
                                                        href="{{ route('user.edit', ['user' => $user->id]) }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User"
                                                        style="background-color: #F7F3E4; border-color: #F7F3E4;">
                                                        <i class="fas fa-edit p-1" style="font-size: 16px; color: #50200C;"></i>
                                                    </a>
                                                    
                                                    <form class="d-inline" method="POST"
                                                        id="delete-post-form-{{ $user->id }}"
                                                        action="{{ route('user.destroy', ['user' => $user->id]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm rounded shadow-sm border p-0 m-0 delete"
                                                            user-id="{{ $user->id }}" 
                                                            user-name="{{ $user->name }}"
                                                            user-role="{{ $user->role }}"
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top"
                                                            title="Delete User"
                                                            style="background-color: #F7F3E4; border-color: #F7F3E4;">
                                                            <i class="fas fa-trash p-1" style="font-size: 16px; color: #50200C;"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-sm rounded shadow-sm border p-0 m-0"
                                                        href="{{ route('user.show', ['user' => $user->id]) }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Detail User"
                                                        style="background-color: #F7F3E4; border-color: #F7F3E4;">
                                                        <i class="fas fa-info-circle p-1" style="font-size: 16px; color: #50200C;"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center" style="color: #50200C">
                                                    Tidak ada data di tabel ini
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer" style="background-color: #F7F3E4; color: #50200C;">
                            <h3 class="mb-0 fs-5 fw-bold">User Hotel Management</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-md-center mt-3">
                <div class="col-sm-10 d-flex mx-auto justify-content-md-center">
                    <div class="pagination-block">
                        {{ $users->onEachSide(1)->appends(['qc' => request()->input('qc')])->links('template.paginationlinks') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        $('.delete').click(function() {
            var user_id = $(this).attr('user-id');
            var user_name = $(this).attr('user-name');
            var user_role = $(this).attr('user-role');
            
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            Swal.fire({
                title: "Yakin ingin menghapus?",
                text: "Data " + user_name + " tidak bisa dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#F2C2B8",
                cancelButtonColor: "#8FB8E1",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                customClass: {
                    confirmButton: "text-50200C",
                    cancelButton: "text-50200C",
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    id = '#delete-post-form-' + user_id
                    $(id).submit();
                }
            })
        });

        // --- TAMBAHAN: POP UP SUKSES ---
        // Cek apakah ada session 'success' dari Controller
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                // Styling agar sesuai tema hotel Anda
                background: '#F7F3E4',
                color: '#50200C',
                confirmButtonColor: '#50200C',
                iconColor: '#28a745',
                timer: 2500, // Pop up hilang otomatis setelah 2.5 detik
                showConfirmButton: false
            });
        @endif

        // --- TAMBAHAN: POP UP GAGAL (Opsional) ---
        @if(session('failed'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('failed') }}',
                background: '#F7F3E4',
                color: '#50200C',
                confirmButtonColor: '#50200C',
            });
        @endif
    </script>
@endsection