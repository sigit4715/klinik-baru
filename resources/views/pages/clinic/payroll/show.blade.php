@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php $rp = fn ($v) => \App\Models\Payslip::rupiah($v); @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Penggajian {{ $period->label() }}
                <span class="badge bg-{{ $period->statusBadge() }} ms-2">{{ $period->statusLabel() }}</span></h4>
            <div class="d-flex gap-2 flex-wrap">
                @if($period->isDraft())
                    <form method="POST" action="{{ route('clinic.payroll.recalculate', [app()->getLocale(), $period->id]) }}">
                        @csrf
                        <button class="btn btn-outline-primary btn-sm"><i class="uil uil-redo me-1"></i>Hitung Ulang</button>
                    </form>
                    <form method="POST" action="{{ route('clinic.payroll.status', [app()->getLocale(), $period->id]) }}"
                        onsubmit="return confirm('Kunci gaji bulan ini? Setelah dikunci, slip tidak berubah dan bisa dilihat karyawan.')">
                        @csrf <input type="hidden" name="action" value="lock">
                        <button class="btn btn-primary btn-sm"><i class="uil uil-lock me-1"></i>Kunci</button>
                    </form>
                @elseif($period->status === 'locked')
                    <form method="POST" action="{{ route('clinic.payroll.status', [app()->getLocale(), $period->id]) }}">
                        @csrf <input type="hidden" name="action" value="unlock">
                        <button class="btn btn-outline-secondary btn-sm"><i class="uil uil-unlock me-1"></i>Buka Kunci</button>
                    </form>
                    <form method="POST" action="{{ route('clinic.payroll.status', [app()->getLocale(), $period->id]) }}"
                        onsubmit="return confirm('Tandai gaji bulan ini sudah dibayar? Setelah ini data tidak bisa diubah.')">
                        @csrf <input type="hidden" name="action" value="paid">
                        <button class="btn btn-success btn-sm"><i class="uil uil-check-circle me-1"></i>Tandai Sudah Dibayar</button>
                    </form>
                @endif
                <a href="{{ route('clinic.payroll.export', [app()->getLocale(), $period->id]) }}" class="btn btn-outline-success btn-sm"><i class="uil uil-file-download me-1"></i>Export Excel (CSV)</a>
                <a href="{{ route('clinic.payroll.index', app()->getLocale()) }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <p class="small text-muted">
            Dihitung: {{ $period->calculated_at?->format('d/m/Y H:i') ?? '-' }}
            @if($period->locked_at) | Dikunci {{ $period->locked_at->format('d/m/Y H:i') }} oleh {{ $period->lockedBy?->name ?? '-' }} @endif
            @if($period->paid_at) | Dibayar {{ $period->paid_at->format('d/m/Y H:i') }} oleh {{ $period->paidBy?->name ?? '-' }} @endif
        </p>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Karyawan</th>
                                <th class="text-center">Hadir / Jadwal</th>
                                <th class="text-center">Alpa</th>
                                <th>Gaji Pokok</th>
                                <th>Uang Duduk</th>
                                <th>Fee Tindakan</th>
                                <th>Tunjangan + Bonus</th>
                                <th>Potongan</th>
                                <th>Gaji Bersih</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payslips as $p)
                                <tr>
                                    <td class="ps-3">{{ $p->employee_name }}<br><small class="text-muted">{{ $p->role_label }}</small></td>
                                    <td class="text-center">{{ $p->attended_shifts }} / {{ $p->scheduled_shifts }}</td>
                                    <td class="text-center">{{ $p->absent_shifts }}</td>
                                    <td>{{ $rp($p->base_salary) }}</td>
                                    <td>{{ $rp($p->sitting_fee_total) }}</td>
                                    <td>{{ $rp($p->service_fee_total) }} <small class="text-muted">({{ $p->service_count }})</small></td>
                                    <td>{{ $rp((float) $p->allowance_total + (float) $p->manual_additions) }}</td>
                                    <td class="text-danger">{{ $rp($p->deductionTotal()) }}</td>
                                    <td><strong>{{ $rp($p->net_total) }}</strong></td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('clinic.payslips.show', [app()->getLocale(), $p->id]) }}" class="btn btn-sm btn-outline-primary" title="Detail"><i class="uil uil-eye"></i></a>
                                        <a href="{{ route('clinic.payslips.print', [app()->getLocale(), $p->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Cetak"><i class="uil uil-print"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada slip. Atur gaji karyawan di Pengaturan Gaji, lalu Hitung Ulang.</td></tr>
                            @endforelse
                        </tbody>
                        @if($payslips->isNotEmpty())
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td class="ps-3" colspan="8">Total</td>
                                    <td>{{ $rp($payslips->sum('net_total')) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
