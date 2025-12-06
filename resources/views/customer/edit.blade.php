@extends('template.master')
@section('title', 'Edit Customer')
@section('content')
    <div class="row justify-content-md-center" style="background-color: #F7F3E4;">
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header" style="background-color: #F7F3E4; color: #50200C;">
                    <h2>Edit Pelanggan</h2>
                </div>
                <div class="card-body p-3" style="color: #50200C">
                    <form class="row g-3" method="POST"
                        action="{{ route('customer.update', ['customer' => $customer->id]) }}"  enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="col-md-12">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" style="color: #50200C" 
                            id="name"
                                name="name" value="{{ $customer->name }}">
                            @error('name')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" style="color: #50200C"
                            id=" email"
                                name="email" value="{{ $customer->user->email }}" disabled>
                            @error('email')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="birthdate" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('birthdate') is-invalid @enderror" style="color: #50200C"
                            id="birthdate"
                                name="birthdate" value="{{ $customer->birthdate }}">
                            @error('birthdate')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="gender" class="form-label">Jenis Kelamin</label>
                            <select style="color: #50200C" class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender"
                                aria-label="Default select example">
                                {{-- <option selected hidden>Select</option> --}}
                                <option value="Male">Laki-Laki</option>
                                <option value="Female">Wanita</option>
                            </select>
                            @error('gender')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="job" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control @error('job') is-invalid @enderror" style="color: #50200C"
                            id="job" name="job"
                                value="{{ $customer->job }}">
                            @error('job')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="address" class="form-label" style="color: #50200C">Alamat</label>
                            <textarea class="form-control" style="color: #50200C" id="address" name="address"
                                rows="3">{{ $customer->address }}</textarea>
                            @error('address')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-mg-12">
                            <label for="avatar" class="form-label" style="color: #50200C">Foto Profil</label>
                            <input class="form-control" type="file" style="color: #50200C" id="avatar" name="avatar" >
                            @error('avatar')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-modal-save shadow-sm border float-end">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
