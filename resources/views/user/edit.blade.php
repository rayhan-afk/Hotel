@extends('template.master')
@section('title', 'Edit User')
@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row justify-content-md-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header" style="background-color: #F7F3E4; color: #50200C;">
                    <h2>Edit Pengguna</h2>
                </div>
                <div class="card-body p-3" style="color: #50200C">
                    <form class="row g-3" method="POST" action="{{ route('user.update', ['user' => $user->id]) }}">
                        @method('PUT')
                        @csrf
                        <div class="col-md-12">
                            <label for="name" class="form-label">Nama</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" style="color: #50200C"
                            id="name" name="name" value="{{ $user->name }}">
                            @error('name')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" style="color: #50200C"
                            id=" email" name="email" value="{{ $user->email }}">
                            @error('email')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class=" col-md-12">
                            <label for="role" class="form-label">Peran</label>
                            <select style="color: #50200C" id="role" name="role" class="form-select @error('password') is-invalid @enderror">
                                <option selected disabled hidden>Choose...</option>
                                @if (in_array($user->role, ['Super', 'Admin']))
                                    <option value="Super" @if ($user->role == 'Super') selected @endif>Super</option>
                                    <option value="Admin" @if ($user->role == 'Admin') selected @endif>Admin</option>
                                    <option value="Manager" @if (old('role') == 'Manager') selected @endif>Manager</option>
                                    <option value="Dapur" @if (old('role') == 'Dapur') selected @endif>Dapur</option>
                                @endif
                                @if ($user->role == 'Customer')
                                    <option value="Customer" @if ($user->role == 'Customer') selected @endif>Pengguna</option>
                                @endif
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