{{--
    Racikan (compounded medicine) editor. Expects $racikanLines (array), $medicines, $defaultFee.
    Inputs: racikan[i][name|qty|dosage|notes|fee], racikan[i][components][j][medicine_id|qty]
--}}
<h6 class="mt-4">Obat Racikan <small class="text-muted fw-normal">(puyer, kapsul racik, salep campur)</small></h6>
<div id="racikanList">
    @foreach($racikanLines as $i => $line)
        @php $components = ($line['components'] ?? []) ?: [['medicine_id' => '', 'qty' => '']]; @endphp
        <div class="border rounded p-2 mb-2 racikan-block" data-index="{{ $i }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4"><label class="form-label small mb-0">Nama Racikan</label>
                    <input type="text" name="racikan[{{ $i }}][name]" value="{{ $line['name'] ?? '' }}" class="form-control form-control-sm" placeholder="Puyer batuk anak" maxlength="100"></div>
                <div class="col-md-2"><label class="form-label small mb-0">Jumlah</label>
                    <input type="number" name="racikan[{{ $i }}][qty]" value="{{ $line['qty'] ?? 10 }}" class="form-control form-control-sm" min="1" title="bungkus/kapsul"></div>
                <div class="col-md-2"><label class="form-label small mb-0">Aturan Pakai</label>
                    <input type="text" name="racikan[{{ $i }}][dosage]" value="{{ $line['dosage'] ?? '' }}" class="form-control form-control-sm" placeholder="3x1"></div>
                <div class="col-md-2"><label class="form-label small mb-0">Biaya Racik (Rp)</label>
                    <input type="number" name="racikan[{{ $i }}][fee]" value="{{ $line['fee'] ?? $defaultFee }}" class="form-control form-control-sm" min="0" step="1"></div>
                <div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-racikan">Hapus racikan</button></div>
                <div class="col-12"><input type="text" name="racikan[{{ $i }}][notes]" value="{{ $line['notes'] ?? '' }}" class="form-control form-control-sm" placeholder="Keterangan (mis. dibagi 10 bungkus, sesudah makan)"></div>
            </div>
            <div class="small text-muted mt-2">Komponen (jumlah total untuk seluruh racikan):</div>
            <div class="racikan-components">
                @foreach($components as $j => $component)
                    <div class="row g-2 mb-1 component-row">
                        <div class="col-md-8"><select name="racikan[{{ $i }}][components][{{ $j }}][medicine_id]" class="form-select form-select-sm select2-medicine">
                            <option value="">Pilih Obat...</option>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}" @selected(($component['medicine_id'] ?? null) == $med->id)>{{ $med->name }} (Stok: {{ $med->stock }})</option>
                            @endforeach
                        </select></div>
                        <div class="col-md-3"><input type="number" name="racikan[{{ $i }}][components][{{ $j }}][qty]" value="{{ $component['qty'] ?? '' }}" class="form-control form-control-sm" min="1" placeholder="Qty"></div>
                        <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-component">&times;</button></div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-link px-0 btn-add-component"><i class="uil uil-plus"></i> Tambah komponen</button>
        </div>
    @endforeach
</div>
<button type="button" class="btn btn-sm btn-outline-primary mb-3" id="btnAddRacikan"><i class="uil uil-plus"></i> Tambah Racikan</button>

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('racikanList');
            const options = @js('<option value="">Pilih Obat...</option>' . $medicines->filter(fn ($m) => ! $m->trashed() && $m->status === 'active' && $m->stock > 0)
                ->map(fn ($m) => '<option value="' . $m->id . '">' . e($m->name) . ' (Stok: ' . $m->stock . ')</option>')->implode(''));
            const defaultFee = @js((float) $defaultFee);
            let blockIndex = {{ count($racikanLines) }};

            const componentRow = (i, j) => `<div class="row g-2 mb-1 component-row">
                <div class="col-md-8"><select name="racikan[${i}][components][${j}][medicine_id]" class="form-select form-select-sm select2-medicine">${options}</select></div>
                <div class="col-md-3"><input type="number" name="racikan[${i}][components][${j}][qty]" class="form-control form-control-sm" min="1" placeholder="Qty"></div>
                <div class="col-md-1"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-component">&times;</button></div>
            </div>`;

            document.getElementById('btnAddRacikan').addEventListener('click', () => {
                const i = blockIndex++;
                list.insertAdjacentHTML('beforeend', `<div class="border rounded p-2 mb-2 racikan-block" data-index="${i}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4"><label class="form-label small mb-0">Nama Racikan</label><input type="text" name="racikan[${i}][name]" class="form-control form-control-sm" placeholder="Puyer batuk anak" maxlength="100"></div>
                        <div class="col-md-2"><label class="form-label small mb-0">Jumlah</label><input type="number" name="racikan[${i}][qty]" value="10" class="form-control form-control-sm" min="1"></div>
                        <div class="col-md-2"><label class="form-label small mb-0">Aturan Pakai</label><input type="text" name="racikan[${i}][dosage]" class="form-control form-control-sm" placeholder="3x1"></div>
                        <div class="col-md-2"><label class="form-label small mb-0">Biaya Racik (Rp)</label><input type="number" name="racikan[${i}][fee]" value="${defaultFee}" class="form-control form-control-sm" min="0" step="1"></div>
                        <div class="col-md-2 text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-racikan">Hapus racikan</button></div>
                        <div class="col-12"><input type="text" name="racikan[${i}][notes]" class="form-control form-control-sm" placeholder="Keterangan"></div>
                    </div>
                    <div class="small text-muted mt-2">Komponen (jumlah total untuk seluruh racikan):</div>
                    <div class="racikan-components">${componentRow(i, 0)}</div>
                    <button type="button" class="btn btn-sm btn-link px-0 btn-add-component"><i class="uil uil-plus"></i> Tambah komponen</button>
                </div>`);
                if (typeof initSelect2 === 'function') initSelect2();
            });

            list.addEventListener('click', e => {
                const block = e.target.closest('.racikan-block');
                if (e.target.closest('.btn-remove-racikan')) {
                    block.remove();
                } else if (e.target.closest('.btn-remove-component')) {
                    e.target.closest('.component-row').remove();
                } else if (e.target.closest('.btn-add-component')) {
                    const box = block.querySelector('.racikan-components');
                    box.dataset.next = box.dataset.next || box.querySelectorAll('.component-row').length + 10;
                    box.insertAdjacentHTML('beforeend', componentRow(block.dataset.index, box.dataset.next++));
                    if (typeof initSelect2 === 'function') initSelect2();
                }
            });
        })();
    </script>
@endpush
