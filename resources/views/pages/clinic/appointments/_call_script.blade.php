{{--
    Call buttons shared by Antrian Hari Ini, Pemeriksaan Perawat and Pasien Menunggu (dokter).
    - #btnCallNext[data-stage] (+ optional data-filter-select="#id" whose value is sent as data-filter-name)
    - .btn-call-specific[data-id][data-stage]
    - #btnRepeat
--}}
<script src="{{ asset('js/queue-voice.js') }}?v=2"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const callNextUrl = @js(route('clinic.appointments.call-next', app()->getLocale()));
        const callSpecificUrl = @js(route('clinic.appointments.call-specific', [app()->getLocale(), '__ID__']));
        const btnRepeat = document.getElementById('btnRepeat');

        const btnNext = document.getElementById('btnCallNext');
        btnNext?.addEventListener('click', async function () {
            const label = this.innerHTML;
            const body = { stage: this.dataset.stage || 'nurse' };
            if (this.dataset.filterSelect) {
                const value = document.querySelector(this.dataset.filterSelect)?.value;
                if (value) body[this.dataset.filterName] = value;
            }

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memanggil...';
            const success = await QueueVoice.callNext(callNextUrl, csrfToken, body);
            if (success && btnRepeat) btnRepeat.style.display = 'inline-block';
            setTimeout(() => { this.disabled = false; this.innerHTML = label; }, 3000);
        });

        btnRepeat?.addEventListener('click', () => QueueVoice.repeatLast());

        document.querySelectorAll('.btn-call-specific').forEach(btn => {
            btn.addEventListener('click', async function () {
                const label = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                const success = await QueueVoice.callSpecific(callSpecificUrl, this.dataset.id, csrfToken, { stage: this.dataset.stage || 'nurse' });
                if (success && btnRepeat) btnRepeat.style.display = 'inline-block';
                setTimeout(() => { this.disabled = false; this.innerHTML = label; }, 3000);
            });
        });
    });
</script>
