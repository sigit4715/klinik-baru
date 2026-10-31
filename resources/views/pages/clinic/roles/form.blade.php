@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-main">
                    <h4 class="text-capitalize breadcrumb-title">{{ $title }}</h4>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST"
            action="{{ $role->exists ? route('clinic.roles.update', [app()->getLocale(), $role->id]) : route('clinic.roles.store', app()->getLocale()) }}">
            @csrf
            @if($role->exists)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header"><h6><i class="uil uil-shield me-2"></i>Data Role</h6></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Role <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                    value="{{ old('label', $role->label) }}" placeholder="Contoh: Kasir & Apoteker" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kode Role <span class="text-danger">*</span></label>
                                @if($role->exists)
                                    <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                                    <small class="text-muted">Kode tidak bisa diubah karena dipakai oleh user.</small>
                                @else
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" placeholder="kasir_apoteker" pattern="[a-z0-9_]+" required>
                                    <small class="text-muted">Huruf kecil, angka, dan garis bawah.</small>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="description" class="form-control"
                                    value="{{ old('description', $role->description) }}">
                            </div>

                            @if(! $role->is_system && $otherRoles->isNotEmpty())
                                <div class="mb-3">
                                    <label class="form-label">Tambahkan akses dari role lain</label>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($otherRoles as $other)
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-copy-role"
                                                data-permissions='@json($other->effectivePermissions())'>
                                                + {{ $other->label }}
                                            </button>
                                        @endforeach
                                    </div>
                                    <small class="text-muted">Mencentang semua hak akses role tersebut (bisa dikombinasikan).</small>
                                </div>
                            @endif

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="uil uil-check me-1"></i>Simpan</button>
                                <a href="{{ route('clinic.roles.index', app()->getLocale()) }}" class="btn btn-light">Batal</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6><i class="uil uil-check-square me-2"></i>Hak Akses Menu</h6>
                            @unless($role->is_system)
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-light" id="checkAll">Centang semua</button>
                                    <button type="button" class="btn btn-sm btn-light" id="uncheckAll">Kosongkan</button>
                                </div>
                            @endunless
                        </div>
                        <div class="card-body">
                            @if($role->is_system)
                                <div class="alert alert-info mb-3">
                                    Role sistem <strong>{{ $role->label }}</strong> selalu memiliki semua hak akses.
                                </div>
                            @endif

                            @php $checked = old('permissions', $selected); @endphp
                            <div class="row">
                                @foreach($groups as $groupLabel => $permissions)
                                    <div class="col-md-6 mb-4 permission-group">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                                            <strong>{{ $groupLabel }}</strong>
                                            @unless($role->is_system)
                                                <a href="#" class="small js-toggle-group">pilih semua</a>
                                            @endunless
                                        </div>
                                        @foreach($permissions as $key => $permissionLabel)
                                            @php $canGrant = in_array($key, $grantable, true); @endphp
                                            <div class="form-check mb-1">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]"
                                                    value="{{ $key }}" id="perm-{{ $key }}"
                                                    @checked($role->is_system || in_array($key, $checked ?? [], true))
                                                    @disabled($role->is_system || ! $canGrant)>
                                                <label class="form-check-label" for="perm-{{ $key }}">
                                                    {{ $permissionLabel }}
                                                    @unless($canGrant || $role->is_system)
                                                        <small class="text-muted">(hanya Administrator)</small>
                                                    @endunless
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const boxes = () => document.querySelectorAll('.permission-checkbox:not(:disabled)');

            document.getElementById('checkAll')?.addEventListener('click', () => boxes().forEach(b => b.checked = true));
            document.getElementById('uncheckAll')?.addEventListener('click', () => boxes().forEach(b => b.checked = false));

            document.querySelectorAll('.js-toggle-group').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const group = this.closest('.permission-group').querySelectorAll('.permission-checkbox:not(:disabled)');
                    const allChecked = Array.from(group).every(b => b.checked);
                    group.forEach(b => b.checked = !allChecked);
                });
            });

            document.querySelectorAll('.js-copy-role').forEach(btn => {
                btn.addEventListener('click', function () {
                    const keys = JSON.parse(this.dataset.permissions || '[]');
                    boxes().forEach(b => { if (keys.includes(b.value)) b.checked = true; });
                });
            });
        });
    </script>
@endsection
