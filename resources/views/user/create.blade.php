@extends('template.master')
@section('title', 'Add User')
@section('content')
    <div class="row justify-content-md-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header" style="background-color: #F7F3E4; color: #50200C;">
                    <h2>Tambah User</h2>
                </div>
                <div class="card-body p-3">
                    <form class="row g-3" method="POST" action="{{ route('user.store') }}">
                        @csrf
                        <div class="col-md-12" style="color: #50200C">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                style="color: #50200C" name="name" value="{{ old('name') }}">
                            @error('name')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-6" style="color: #50200C">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" " id=" email"
                                style="color: #50200C" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class=" col-md-6" style="color: #50200C">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" style="color: #50200C"
                            id="password" name="password" value="{{ old('password') }}">
                            @error('password')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class=" col-md-12" style="color: #50200C">
                            <label for="role" class="form-label">Peran</label>
                            <select style="color: #50200C" id="role" name="role" class="form-select @error('password') is-invalid @enderror">
                                <option selected disabled hidden>Pilih..</option>
                                <option value="Manager" @if (old('role') == 'Super') selected @endif>Super</option>
                                <option value="Admin" @if (old('role') == 'Admin') selected @endif>Admin</option>
                                <option value="Manager" @if (old('role') == 'Manager') selected @endif>Manager</option>
                                <option value="Dapur" @if (old('role') == 'Dapur') selected @endif>Dapur</option>
                                <option value="Housekeeping" {{ old('role') == 'Housekeeping' ? 'selected' : '' }}>Housekeeping</option>
                            </select>
                            @error('role')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-modal-save shadow-sm border float-end">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
