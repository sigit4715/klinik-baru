@section('title', $title)
@section('description', $description)
@extends('layout.app')

@push('styles')
    <style>
        .queue-display-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .current-queue-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }

            50% {
                box-shadow: 0 25px 60px -12px rgba(102, 126, 234, 0.5);
            }
        }

        .queue-number-display {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }

        .patient-name-display {
            font-size: 2.5rem;
            color: #374151;
            font-weight: 600;
        }

        .doctor-name-display {
            font-size: 1.5rem;
            color: #6b7280;
        }

        .waiting-list-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }

        .waiting-item {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .waiting-item:last-child {
            border-bottom: none;
        }

        .waiting-item:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        .waiting-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            width: 80px;
        }

        .waiting-name {
            font-size: 1.2rem;
            color: #374151;
        }

        .clock-display {
            font-size: 3rem;
            font-weight: 300;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .date-display {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            padding: 20px;
            text-align: center;
            color: white;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .no-queue {
            color: rgba(255, 255, 255, 0.8);
        }

        .calling-animation {
            animation: calling 0.5s ease-in-out infinite;
        }

        @keyframes calling {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }
        }
    </style>
@endpush

@section('content')
    <div class="queue-display-container p-4">
        <div class="container-fluid">
            <!-- Header with Clock -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h1 class="text-white mb-0" style="font-size: 2.5rem; font-weight: 600;">
                        <i class="uil uil-clinic-medical me-2"></i>Antrian Pasien
                    </h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="clock-display" id="liveClock">--:--:--</div>
                    <div class="date-display" id="liveDate">--</div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">{{ $waiting->count() }}</div>
                        <div class="stats-label"><i class="uil uil-users-alt me-1"></i>Menunggu</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">{{ $current ? 1 : 0 }}</div>
                        <div class="stats-label"><i class="uil uil-user-check me-1"></i>Sedang Dipanggil</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">{{ $completed }}</div>
                        <div class="stats-label"><i class="uil uil-check-circle me-1"></i>Selesai</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Current Queue -->
                <div class="col-lg-7 mb-4">
                    <div class="current-queue-card p-5 text-center h-100 d-flex flex-column justify-content-center"
                        id="currentQueueCard">
                        @if($current)
                            <div>
                                <p class="text-muted mb-2" style="font-size: 1.2rem;">Nomor Antrian Saat Ini</p>
                                <div class="queue-number-display mb-3" id="currentQueueNumber">
                                    {{ preg_replace('/^\d{8}-/', '', $current->queue_number) }}
                                </div>
                                <div class="patient-name-display mb-2" id="currentPatientName">
                                    {{ $current->patient->name }}
                                </div>
                                <div class="doctor-name-display">
                                    <i class="uil uil-user-md me-1"></i>{{ $current->doctor->name ?? 'Dokter' }}
                                </div>
                            </div>
                        @else
                            <div class="no-queue py-5">
                                <i class="uil uil-clock-three" style="font-size: 5rem; color: #9ca3af;"></i>
                                <p class="mt-3" style="font-size: 1.5rem; color: #6b7280;">Belum ada antrian yang dipanggil</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Waiting List -->
                <div class="col-lg-5 mb-4">
                    <div class="waiting-list-card h-100" style="max-height: 500px; overflow: hidden;">
                        <div class="p-4 border-bottom"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px 20px 0 0;">
                            <h4 class="text-white mb-0">
                                <i class="uil uil-list-ul me-2"></i>Antrian Berikutnya
                            </h4>
                        </div>
                        <div style="max-height: 400px; overflow-y: auto;" id="waitingList">
                            @forelse($waiting->take(10) as $index => $apt)
                                <div class="waiting-item d-flex align-items-center" data-queue="{{ $apt->queue_number }}">
                                    <span class="waiting-number">{{ preg_replace('/^\d{8}-/', '', $apt->queue_number) }}</span>
                                    <div>
                                        <div class="waiting-name">{{ $apt->patient->name }}</div>
                                        <small class="text-muted">{{ $apt->doctor->name ?? '' }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="uil uil-check-circle" style="font-size: 3rem; color: #10b981;"></i>
                                    <p class="text-muted mt-2">Tidak ada antrian menunggu</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-white mt-3">
                <p style="opacity: 0.8;">Silakan tunggu nomor antrian Anda dipanggil</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/queue-voice.js') }}"></script>
    <script>
        // Initialize Queue Voice
        QueueVoice.init();

        // Live clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            document.getElementById('liveClock').textContent = timeStr;
            document.getElementById('liveDate').textContent = dateStr;
        }

        updateClock();
        setInterval(updateClock, 1000);

        // Store last known queue for comparison
        let lastQueueId = {{ $current ? $current->id : 'null' }};

        // Auto-refresh and check for new calls
        function checkForUpdates() {
            fetch('{{ route('clinic.appointments.waiting', app()->getLocale()) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Check if there's a new call
                        if (data.in_progress && data.in_progress.length > 0) {
                            const currentQueue = data.in_progress[0];
                            if (lastQueueId !== currentQueue.id) {
                                // New queue called! Speak it
                                QueueVoice.speak(
                                    currentQueue.queue_number,
                                    currentQueue.patient?.name || 'Pasien',
                                    currentQueue.doctor?.name || ''
                                );

                                // Update display
                                updateCurrentQueue(currentQueue);
                                lastQueueId = currentQueue.id;

                                // Add calling animation
                                document.getElementById('currentQueueCard').classList.add('calling-animation');
                                setTimeout(() => {
                                    document.getElementById('currentQueueCard').classList.remove('calling-animation');
                                }, 5000);
                            }
                        }

                        // Update waiting list
                        updateWaitingList(data.waiting);
                    }
                })
                .catch(error => console.error('Error checking updates:', error));
        }

        function updateCurrentQueue(queue) {
            const card = document.getElementById('currentQueueCard');
            const queueNum = queue.queue_number.replace(/^\d{8}-/, '');

            card.innerHTML = `
                <div>
                    <p class="text-muted mb-2" style="font-size: 1.2rem;">Nomor Antrian Saat Ini</p>
                    <div class="queue-number-display mb-3" id="currentQueueNumber">${queueNum}</div>
                    <div class="patient-name-display mb-2" id="currentPatientName">${queue.patient?.name || 'Pasien'}</div>
                    <div class="doctor-name-display">
                        <i class="uil uil-user-md me-1"></i>${queue.doctor?.name || 'Dokter'}
                    </div>
                </div>
            `;
        }

        function updateWaitingList(waiting) {
            const list = document.getElementById('waitingList');
            if (waiting.length === 0) {
                list.innerHTML = `
                    <div class="text-center py-5">
                        <i class="uil uil-check-circle" style="font-size: 3rem; color: #10b981;"></i>
                        <p class="text-muted mt-2">Tidak ada antrian menunggu</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = waiting.slice(0, 10).map(apt => {
                const queueNum = apt.queue_number.replace(/^\d{8}-/, '');
                return `
                    <div class="waiting-item d-flex align-items-center" data-queue="${apt.queue_number}">
                        <span class="waiting-number">${queueNum}</span>
                        <div>
                            <div class="waiting-name">${apt.patient?.name || 'Pasien'}</div>
                            <small class="text-muted">${apt.doctor?.name || ''}</small>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Check for updates every 5 seconds
        setInterval(checkForUpdates, 5000);
    </script>
@endpush