@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('css')
<style>
    .user-management-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 25px;
        color: white;
    }
    
    .user-management-header h4 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }
    
    .user-management-header p {
        margin: 8px 0 0;
        opacity: 0.85;
        font-size: 14px;
    }
    
    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 25px;
    }
    
    .stat-card {
        flex: 1;
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
    }
    
    .stat-card.total {
        border-color: #667eea;
    }
    
    .stat-card.active {
        border-color: #10b981;
    }
    
    .stat-card.inactive {
        border-color: #ef4444;
    }
    
    .stat-card .stat-number {
        font-size: 32px;
        font-weight: 700;
        line-height: 1;
    }
    
    .stat-card.total .stat-number { color: #667eea; }
    .stat-card.active .stat-number { color: #10b981; }
    .stat-card.inactive .stat-number { color: #ef4444; }
    
    .stat-card .stat-label {
        font-size: 14px;
        color: #6b7280;
        margin-top: 8px;
    }
    
    .user-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    
    .user-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 25px;
    }
    
    .user-card .card-header h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .user-table {
        margin: 0;
    }
    
    .user-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .user-table thead th {
        color: white !important;
        font-weight: 500;
        padding: 15px 20px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: none !important;
    }
    
    .user-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s;
    }
    
    .user-table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .user-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border: none !important;
    }
    
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: 600;
        color: white;
        margin-right: 12px;
    }
    
    .user-avatar.bg-1 { background: linear-gradient(135deg, #667eea, #764ba2); }
    .user-avatar.bg-2 { background: linear-gradient(135deg, #f093fb, #f5576c); }
    .user-avatar.bg-3 { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    .user-avatar.bg-4 { background: linear-gradient(135deg, #43e97b, #38f9d7); }
    
    .user-info .user-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 15px;
    }
    
    .user-info .user-email {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }
    
    .badge-role {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-role.admin {
        background: rgba(102, 126, 234, 0.15);
        color: #667eea;
    }
    
    .badge-role.doctor {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }
    
    .badge-role.cashier {
        background: rgba(245, 158, 11, 0.15);
        color: #d97706;
    }
    
    .badge-role.staff {
        background: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }
    
    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-status.active {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
    }
    
    .badge-status.inactive {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .action-btn.edit {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    .action-btn.edit:hover {
        background: #667eea;
        color: white;
    }
    
    .action-btn.delete {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    
    .action-btn.delete:hover {
        background: #ef4444;
        color: white;
    }
    
    .btn-add-user {
        background: white;
        color: #667eea;
        border: 2px solid rgba(255,255,255,0.3);
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-add-user:hover {
        background: rgba(255,255,255,0.2);
        color: white;
        border-color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    
    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 15px;
    }
    
    .empty-state p {
        color: #6b7280;
        font-size: 16px;
    }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="user-management-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4><i class="uil uil-users-alt me-2"></i>{{ $title }}</h4>
                    <p>Kelola semua pengguna sistem klinik</p>
                </div>
                <a href="{{ route('clinic.users.create', app()->getLocale()) }}" class="btn btn-add-user">
                    <i class="uil uil-plus me-1"></i>Tambah User
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card total">
                <div class="stat-number">{{ $users->count() }}</div>
                <div class="stat-label"><i class="uil uil-users-alt me-1"></i>Total User</div>
            </div>
            <div class="stat-card active">
                <div class="stat-number">{{ $users->where('is_active', true)->count() }}</div>
                <div class="stat-label"><i class="uil uil-check-circle me-1"></i>User Aktif</div>
            </div>
            <div class="stat-card inactive">
                <div class="stat-number">{{ $users->where('is_active', false)->count() }}</div>
                <div class="stat-label"><i class="uil uil-times-circle me-1"></i>User Nonaktif</div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="uil uil-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- User Table -->
        <div class="user-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6><i class="uil uil-list-ul me-2"></i>Daftar Pengguna</h6>
                <span class="text-muted" style="font-size: 13px;">{{ $users->count() }} pengguna terdaftar</span>
            </div>
            <div class="table-responsive">
                <table class="table user-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Pengguna</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td><strong>{{ $index + 1 }}</strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar bg-{{ ($index % 4) + 1 }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $user->name }}</div>
                                            <div class="user-email">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @switch($user->role ?? 'admin')
                                        @case('admin')
                                            <span class="badge-role admin"><i class="uil uil-shield me-1"></i>Administrator</span>
                                            @break
                                        @case('doctor')
                                            <span class="badge-role doctor"><i class="uil uil-stethoscope me-1"></i>Dokter</span>
                                            @break
                                        @case('cashier')
                                            <span class="badge-role cashier"><i class="uil uil-receipt me-1"></i>Kasir</span>
                                            @break
                                        @default
                                            <span class="badge-role staff"><i class="uil uil-user me-1"></i>Staff</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($user->is_active ?? true)
                                        <span class="badge-status active"><i class="uil uil-check me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge-status inactive"><i class="uil uil-times me-1"></i>Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: #6b7280; font-size: 14px;">
                                        <i class="uil uil-calendar-alt me-1"></i>{{ $user->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('clinic.users.edit', [app()->getLocale(), $user->id]) }}"
                                            class="action-btn edit" title="Edit User">
                                            <i class="uil uil-edit"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('clinic.users.destroy', [app()->getLocale(), $user->id]) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Yakin ingin menghapus user {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete" title="Hapus User">
                                                    <i class="uil uil-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="uil uil-users-alt"></i>
                                        <p>Belum ada pengguna terdaftar</p>
                                        <a href="{{ route('clinic.users.create', app()->getLocale()) }}" class="btn btn-primary mt-3">
                                            <i class="uil uil-plus me-1"></i>Tambah User Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
