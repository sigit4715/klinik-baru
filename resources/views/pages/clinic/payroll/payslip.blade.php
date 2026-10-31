@section('title', $title)
@section('description', $description)
@extends('layout.app')
@section('content')
    @php
        $draft = $payslip->period->isDraft();
        $lines = old('adjustments', $payslip->adjustments ?? []);
        $lines = array_values($lines);
        // Always offer at least one empty row (and three in total)
        $target = max(3, count($lines) + 1);
        while (count($lines) < $target) {
            $lines[] = ['type' => 'add', 'label' => '', 'amount' => ''];
        }
    @endphp
    <div class="container-fluid">
        <div class="breadcrumb-main d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h4 class="breadcrumb-title mb-0">Slip Gaji {{ $payslip->employee_name }} <small class="text-muted">{{ $payslip->period->label() }}</small></h4>
            <div class="d-flex gap-2">
                <a href="{{ route('clinic.payslips.print', [app()->getLocale(), $payslip->id]) }}" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="uil uil-print me-1"></i>Cetak</a>
                <a href="{{ route('clinic.payroll.show', [app()->getLocale(), $payslip->payroll_period_id]) }}" class="btn btn-light btn-sm">Kembali</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted small mb-2">{{ $payslip->role_label }} · status periode: {{ $payslip->period->statusLabel() }}</p>
                        @include('pages.clinic.payroll._slip')
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">Bonus / Potongan Manual</div>
                    <div class="card-body">
                        @if($draft)
                            <form method="POST" action="{{ route('clinic.payslips.adjust', [app()->getLocale(), $payslip->id]) }}">
                                @csrf
                                @foreach($lines as $i => $line)
                                    <div class="row g-2 mb-2">
                                        <div class="col-4">
                                            <select name="adjustments[{{ $i }}][type]" class="form-select form-select-sm">
                                                <option value="add" @selected(($line['type'] ?? 'add') === 'add')>+ Bonus</option>
                                                <option value="deduct" @selected(($line['type'] ?? '') === 'deduct')>- Potongan</option>
                                            </select>
                                        </div>
                                        <div class="col-4"><input type="text" name="adjustments[{{ $i }}][label]" value="{{ $line['label'] ?? '' }}" class="form-control form-control-sm" placeholder="Keterangan" maxlength="100"></div>
                                        <div class="col-4"><input type="number" step="1" min="0" name="adjustments[{{ $i }}][amount]" value="{{ $line['amount'] ?? '' }}" class="form-control form-control-sm" placeholder="Rp"></div>
                                    </div>
                                @endforeach
                                <p class="small text-muted">Contoh: lembur, bonus, kasbon. Baris kosong diabaikan. Simpan dulu untuk menambah baris lagi.</p>
                                <button class="btn btn-primary btn-sm"><i class="uil uil-check me-1"></i>Simpan</button>
                            </form>
                        @else
                            <p class="text-muted mb-0">Periode sudah dikunci. Buka kunci di halaman penggajian untuk mengubah.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
