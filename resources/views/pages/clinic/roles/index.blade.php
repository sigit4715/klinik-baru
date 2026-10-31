@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('clinic.users.index', app()->getLocale()) }}" class="btn btn-light">
                            <i class="uil uil-users-alt me-1"></i>Manajemen User
                        </a>
                        <a href="{{ route('clinic.roles.create', app()->getLocale()) }}" class="btn btn-primary">
                            <i class="uil uil-plus me-1"></i>Tambah Role
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="alert alert-info">
            <i class="uil uil-info-circle me-1"></i>
            Setiap user memiliki satu role. Untuk menggabungkan tugas (misalnya <strong>Kasir + Apoteker</strong>),
            buat role baru lalu centang hak akses keduanya, atau gunakan tombol <em>Gabung</em> di bawah.
            Menu di sidebar otomatis menyesuaikan hak akses role.
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('clinic.roles.create', app()->getLocale()) }}" id="combineForm"></form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;" title="Pilih untuk digabung"><i class="uil uil-layer-group"></i></th>
                                <th>Role</th>
                                <th>Kode</th>
                                <th>Hak Akses</th>
                                <th>User</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>
                                        @unless($role->is_system)
                                            <input type="checkbox" class="form-check-input" name="copy[]" value="{{ $role->id }}"
                                                form="combineForm" title="Pilih untuk digabung">
                                        @endunless
                                    </td>
                                    <td>
                                        <strong>{{ $role->label }}</strong>
                                        @if($role->is_system)
                                            <span class="badge bg-primary ms-1">Sistem</span>
                                        @endif
                                        @if($role->description)
                                            <div class="text-muted small">{{ $role->description }}</div>
                                        @endif
                                    </td>
                                    <td><code>{{ $role->name }}</code></td>
                                    <td>
                                        @if($role->is_system)
                                            <span class="text-success">Semua akses</span>
                                        @else
                                            {{ count($role->effectivePermissions()) }} / {{ $totalPermissions }}
                                        @endif
                                    </td>
                                    <td>{{ $role->users_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('clinic.roles.edit', [app()->getLocale(), $role->id]) }}"
                                            class="btn btn-sm btn-outline-primary" title="Edit"><i class="uil uil-edit"></i></a>
                                        @unless($role->is_system)
                                            <a href="{{ route('clinic.roles.create', [app()->getLocale(), 'copy' => [$role->id]]) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Duplikat"><i class="uil uil-copy"></i></a>
                                            <form action="{{ route('clinic.roles.destroy', [app()->getLocale(), $role->id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm(@js('Hapus role ' . $role->label . '?'))">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"
                                                    @disabled($role->users_count > 0)><i class="uil uil-trash-alt"></i></button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" form="combineForm" class="btn btn-outline-primary btn-sm">
                        <i class="uil uil-layer-group me-1"></i>Gabung role yang dicentang menjadi role baru
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
