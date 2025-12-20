@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                    <div class="breadcrumb-action justify-content-center flex-wrap">
                        <a href="{{ route('clinic.users.create', app()->getLocale()) }}" class="btn btn-primary">
                            <i class="uil uil-plus me-1"></i>Tambah User
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="uil uil-users-alt me-2"></i>Daftar User</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-circle avatar-sm bg-opacity-primary me-2">
                                                        <span class="avatar-letter">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                    <span class="fw-500">{{ $user->name }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @switch($user->role ?? 'admin')
                                                    @case('admin')
                                                        <span class="badge bg-primary">Administrator</span>
                                                        @break
                                                    @case('doctor')
                                                        <span class="badge bg-info">Dokter</span>
                                                        @break
                                                    @case('cashier')
                                                        <span class="badge bg-warning text-dark">Kasir</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">Staff</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($user->is_active ?? true)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('clinic.users.edit', [app()->getLocale(), $user->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="uil uil-edit"></i>
                                                </a>
                                                @if(auth()->id() !== $user->id)
                                                    <form action="{{ route('clinic.users.destroy', [app()->getLocale(), $user->id]) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Yakin hapus user ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                            <i class="uil uil-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                Belum ada user terdaftar
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
