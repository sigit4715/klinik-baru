<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display Antrian - Klinik</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v3.0.0/css/line.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            overflow: hidden;
            color: #fff;
        }

        /* Animated background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .bg-animation .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            animation: float 20s infinite;
        }

        .bg-animation .circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -50px;
            left: -50px;
            animation-delay: 0s;
        }

        .bg-animation .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 50%;
            right: -50px;
            animation-delay: -5s;
        }

        .bg-animation .circle:nth-child(3) {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: 30%;
            animation-delay: -10s;
        }

        .bg-animation .circle:nth-child(4) {
            width: 150px;
            height: 150px;
            top: 20%;
            left: 60%;
            animation-delay: -15s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
                opacity: 0.5;
            }

            50% {
                transform: translateY(-30px) rotate(180deg);
                opacity: 0.8;
            }
        }

        .container {
            position: relative;
            z-index: 1;
            padding: 30px 40px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .logo-text h1 {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo-text p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .datetime {
            text-align: right;
        }

        .time {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .date {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.08);
        }

        .stat-card.waiting {
            border-left: 4px solid #ffc107;
        }

        .stat-card.current {
            border-left: 4px solid #00f5a0;
        }

        .stat-card.done {
            border-left: 4px solid #667eea;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            line-height: 1;
        }

        .stat-card.waiting .stat-number {
            color: #ffc107;
        }

        .stat-card.current .stat-number {
            color: #00f5a0;
        }

        .stat-card.done .stat-number {
            color: #667eea;
        }

        .stat-label {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 10px;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            flex: 1;
        }

        /* Current Queue */
        .current-queue {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .current-queue.calling {
            animation: pulse-border 1.5s ease-in-out infinite;
        }

        @keyframes pulse-border {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(0, 245, 160, 0.4);
            }

            50% {
                box-shadow: 0 0 0 20px rgba(0, 245, 160, 0);
            }
        }

        .current-label {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .current-number {
            font-size: 180px;
            font-weight: 900;
            background: linear-gradient(135deg, #00f5a0, #00d9f5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            text-shadow: 0 0 60px rgba(0, 245, 160, 0.3);
        }

        .current-patient {
            font-size: 36px;
            font-weight: 600;
            margin-top: 20px;
            color: #fff;
        }

        .current-doctor {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .no-queue {
            color: rgba(255, 255, 255, 0.5);
        }

        .no-queue i {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
        }

        /* Waiting List */
        .waiting-list {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .waiting-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 25px 30px;
        }

        .waiting-header h2 {
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .waiting-body {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .waiting-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .waiting-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }

        .waiting-item:last-child {
            margin-bottom: 0;
        }

        .waiting-num {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-right: 20px;
        }

        .waiting-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .waiting-info p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }

        .empty-list {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            padding: 40px;
        }

        .empty-list i {
            font-size: 60px;
            margin-bottom: 15px;
            color: #00f5a0;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 16px;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: slideIn 0.5s ease forwards;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body>
    <!-- Background Animation -->
    <div class="bg-animation">
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="uil uil-clinic-medical"></i>
                </div>
                <div class="logo-text">
                    <h1>Klinik</h1>
                    <p>Sistem Antrian Digital</p>
                </div>
            </div>
            <div class="datetime">
                <div class="time" id="liveClock">--:--:--</div>
                <div class="date" id="liveDate">--</div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card waiting">
                <div class="stat-number" id="waitingCount">{{ $waiting->count() }}</div>
                <div class="stat-label"><i class="uil uil-users-alt"></i> Menunggu</div>
            </div>
            <div class="stat-card current">
                <div class="stat-number" id="currentCount">{{ $current ? 1 : 0 }}</div>
                <div class="stat-label"><i class="uil uil-user-check"></i> Sedang Dipanggil</div>
            </div>
            <div class="stat-card done">
                <div class="stat-number" id="doneCount">{{ $completed }}</div>
                <div class="stat-label"><i class="uil uil-check-circle"></i> Selesai</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Current Queue -->
            <div class="current-queue" id="currentQueueCard">
                @if($current)
                    <div class="current-label">Nomor Antrian Saat Ini</div>
                    <div class="current-number" id="currentNumber">
                        {{ preg_replace('/^\d{8}-/', '', $current->queue_number) }}</div>
                    <div class="current-patient" id="currentPatient">{{ $current->patient->name }}</div>
                    <div class="current-doctor" id="currentDoctor">
                        <i class="uil uil-user-md"></i>
                        {{ $current->doctor->name ?? 'Dokter' }}
                    </div>
                @else
                    <div class="no-queue">
                        <i class="uil uil-clock-three"></i>
                        <div class="current-label">Belum Ada Antrian</div>
                        <p style="margin-top: 10px; font-size: 18px;">Silakan tunggu panggilan</p>
                    </div>
                @endif
            </div>

            <!-- Waiting List -->
            <div class="waiting-list">
                <div class="waiting-header">
                    <h2><i class="uil uil-list-ul"></i> Antrian Berikutnya</h2>
                </div>
                <div class="waiting-body" id="waitingList">
                    @forelse($waiting->take(8) as $index => $apt)
                        <div class="waiting-item animate-in" style="animation-delay: {{ $index * 0.1 }}s"
                            data-queue="{{ $apt->queue_number }}">
                            <div class="waiting-num">{{ preg_replace('/^\d{8}-/', '', $apt->queue_number) }}</div>
                            <div class="waiting-info">
                                <h3>{{ $apt->patient->name }}</h3>
                                <p><i class="uil uil-user-md"></i> {{ $apt->doctor->name ?? '' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="empty-list">
                            <i class="uil uil-check-circle"></i>
                            <p>Tidak ada antrian menunggu</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Silakan tunggu nomor antrian Anda dipanggil • Terima kasih atas kesabaran Anda</p>
        </div>
    </div>

    <script src="{{ asset('js/queue-voice.js') }}"></script>
    <script>
        // Initialize
        QueueVoice.init();

        // Live Clock
        function updateClock() {
            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const date = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('liveClock').textContent = time;
            document.getElementById('liveDate').textContent = date;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Track last queue
        let lastQueueId = {{ $current ? $current->id : 'null' }};

        // Check for updates
        function checkUpdates() {
            fetch('{{ route('clinic.appointments.waiting', app()->getLocale()) }}')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Update stats
                        document.getElementById('waitingCount').textContent = data.waiting_count;
                        document.getElementById('currentCount').textContent = data.in_progress_count;

                        // Check new call
                        if (data.in_progress && data.in_progress.length > 0) {
                            const current = data.in_progress[0];
                            if (lastQueueId !== current.id) {
                                lastQueueId = current.id;
                                updateCurrentDisplay(current);

                                // Add calling animation
                                const card = document.getElementById('currentQueueCard');
                                card.classList.add('calling');
                                setTimeout(() => card.classList.remove('calling'), 5000);

                                // Speak
                                QueueVoice.speak(
                                    current.queue_number,
                                    current.patient?.name || 'Pasien',
                                    current.doctor?.name || ''
                                );
                            }
                        }

                        // Update waiting list
                        updateWaitingList(data.waiting);
                    }
                })
                .catch(e => console.error('Error:', e));
        }

        // Names come from user input; escape before putting them into innerHTML
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        function updateCurrentDisplay(queue) {
            const queueNum = escapeHtml(queue.queue_number.replace(/^\d{8}-/, ''));
            document.getElementById('currentQueueCard').innerHTML = `
                <div class="current-label">Nomor Antrian Saat Ini</div>
                <div class="current-number" id="currentNumber">${queueNum}</div>
                <div class="current-patient" id="currentPatient">${escapeHtml(queue.patient?.name || 'Pasien')}</div>
                <div class="current-doctor" id="currentDoctor">
                    <i class="uil uil-user-md"></i>
                    ${escapeHtml(queue.doctor?.name || 'Dokter')}
                </div>
            `;
        }

        function updateWaitingList(waiting) {
            const list = document.getElementById('waitingList');
            if (!waiting || waiting.length === 0) {
                list.innerHTML = `
                    <div class="empty-list">
                        <i class="uil uil-check-circle"></i>
                        <p>Tidak ada antrian menunggu</p>
                    </div>
                `;
                return;
            }

            list.innerHTML = waiting.slice(0, 8).map((apt, i) => {
                const num = escapeHtml(apt.queue_number.replace(/^\d{8}-/, ''));
                return `
                    <div class="waiting-item animate-in" style="animation-delay: ${i * 0.1}s">
                        <div class="waiting-num">${num}</div>
                        <div class="waiting-info">
                            <h3>${escapeHtml(apt.patient?.name || 'Pasien')}</h3>
                            <p><i class="uil uil-user-md"></i> ${escapeHtml(apt.doctor?.name || '')}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Auto refresh every 5 seconds
        setInterval(checkUpdates, 5000);
    </script>
</body>

</html>