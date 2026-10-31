/**
 * Queue Voice Announcement System
 * Menggunakan Web Speech API (SpeechSynthesis) untuk memanggil nomor antrian dengan suara
 */

const QueueVoice = {
    // Konfigurasi suara
    config: {
        lang: 'id-ID',     // Bahasa Indonesia
        rate: 0.9,         // Kecepatan bicara (0.1 - 10)
        pitch: 1.0,        // Pitch suara (0 - 2)
        volume: 1.0        // Volume (0 - 1)
    },

    // Data panggilan terakhir untuk fitur panggil ulang
    lastCall: null,

    /**
     * Inisialisasi - cek apakah browser mendukung Speech Synthesis
     */
    init: function () {
        if (!('speechSynthesis' in window)) {
            console.warn('Browser tidak mendukung Web Speech API');
            return false;
        }

        // Preload voices
        speechSynthesis.getVoices();

        // Event listener untuk voices loaded
        speechSynthesis.onvoiceschanged = () => {
            this.getIndonesianVoice();
        };

        return true;
    },

    /**
     * Mendapatkan voice bahasa Indonesia jika tersedia
     */
    getIndonesianVoice: function () {
        const voices = speechSynthesis.getVoices();

        // Prioritas: Indonesian > Malay > default
        let voice = voices.find(v => v.lang.startsWith('id'));
        if (!voice) {
            voice = voices.find(v => v.lang.startsWith('ms')); // Malay sebagai fallback
        }
        if (!voice && voices.length > 0) {
            voice = voices[0]; // Default ke voice pertama
        }

        return voice;
    },

    /**
     * Memanggil antrian dengan suara
     * @param {string} queueNumber - Nomor antrian
     * @param {string} patientName - Nama pasien
     * @param {string} doctorName - Nama dokter (opsional)
     * @param {string} room - Ruangan (opsional)
     */
    speak: function (queueNumber, patientName, doctorName = '', room = '') {
        if (!('speechSynthesis' in window)) {
            alert('Maaf, browser Anda tidak mendukung fitur suara. Silakan gunakan Chrome, Firefox, atau Edge terbaru.');
            return false;
        }

        // Hentikan ucapan sebelumnya jika ada
        speechSynthesis.cancel();

        // Simpan data untuk panggil ulang
        this.lastCall = { queueNumber, patientName, doctorName, room };

        // Buat teks panggilan
        let text = `Nomor antrian ${this.formatQueueNumber(queueNumber)}, atas nama ${patientName}`;

        if (room) {
            // e.g. "Ruang Perawat" or "Poli Umum - dr. Siti"
            text += `, silakan menuju ${room.replace(/ - /g, ', ')}`;
        } else if (doctorName) {
            text += `, silakan menuju ruangan ${doctorName}`;
        } else {
            text += `, silakan ke meja pendaftaran`;
        }

        // Buat utterance
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = this.config.lang;
        utterance.rate = this.config.rate;
        utterance.pitch = this.config.pitch;
        utterance.volume = this.config.volume;

        // Set voice Indonesia jika tersedia
        const voice = this.getIndonesianVoice();
        if (voice) {
            utterance.voice = voice;
        }

        // Event handlers
        utterance.onstart = () => {
            console.log('Mulai memanggil: ' + text);
            this.triggerCallAnimation(queueNumber);
        };

        utterance.onend = () => {
            console.log('Selesai memanggil');
            this.endCallAnimation();
        };

        utterance.onerror = (event) => {
            console.error('Speech synthesis error:', event.error);
        };

        // Ucapkan
        speechSynthesis.speak(utterance);

        // Ulangi panggilan setelah jeda
        setTimeout(() => {
            if (!speechSynthesis.speaking) {
                const repeatUtterance = new SpeechSynthesisUtterance(text);
                repeatUtterance.lang = this.config.lang;
                repeatUtterance.rate = this.config.rate;
                repeatUtterance.pitch = this.config.pitch;
                repeatUtterance.volume = this.config.volume;
                if (voice) repeatUtterance.voice = voice;
                speechSynthesis.speak(repeatUtterance);
            }
        }, 3000);

        return true;
    },

    /**
     * Format nomor antrian agar dibaca dengan benar
     * Contoh: "20241221-001" -> "dua puluh empat dua belas dua puluh satu, nol nol satu"
     */
    formatQueueNumber: function (queueNumber) {
        // Jika format YYYYMMDD-XXX, ambil hanya bagian nomor urut
        if (queueNumber.includes('-')) {
            const parts = queueNumber.split('-');
            const number = parseInt(parts[parts.length - 1], 10);
            return number.toString();
        }
        return queueNumber;
    },

    /**
     * Panggil ulang antrian terakhir
     */
    repeatLast: function () {
        if (!this.lastCall) {
            alert('Belum ada panggilan antrian yang bisa diulang.');
            return false;
        }

        return this.speak(
            this.lastCall.queueNumber,
            this.lastCall.patientName,
            this.lastCall.doctorName,
            this.lastCall.room
        );
    },

    /**
     * Animasi saat memanggil antrian
     */
    triggerCallAnimation: function (queueNumber) {
        // Highlight baris yang dipanggil
        const row = document.querySelector(`[data-queue="${queueNumber}"]`);
        if (row) {
            row.classList.add('queue-calling');
        }

        // Tampilkan modal panggilan jika ada
        const callModal = document.getElementById('queueCallModal');
        if (callModal) {
            const modalQueue = callModal.querySelector('.modal-queue-number');
            const modalPatient = callModal.querySelector('.modal-patient-name');
            if (modalQueue) modalQueue.textContent = queueNumber;
            if (modalPatient && this.lastCall) modalPatient.textContent = this.lastCall.patientName;
            const modalDestination = callModal.querySelector('.modal-doctor-name');
            if (modalDestination && this.lastCall) modalDestination.textContent = this.lastCall.room || this.lastCall.doctorName || '';

            const bsModal = new bootstrap.Modal(callModal);
            bsModal.show();
        }
    },

    /**
     * Akhiri animasi panggilan
     */
    endCallAnimation: function () {
        // Hapus highlight
        const callingRows = document.querySelectorAll('.queue-calling');
        callingRows.forEach(row => row.classList.remove('queue-calling'));
    },

    /**
     * Panggil antrian berikutnya via API
     */
    callNext: async function (url, csrfToken, body = {}) {
        // Older callers passed a doctor id instead of a body object
        if (body === null || typeof body !== 'object') {
            body = body ? { doctor_id: body } : {};
        }
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                // stage: nurse|doctor, optional doctor_id / nurse_id filter
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (data.success && data.appointment) {
                const apt = data.appointment;
                this.speak(
                    apt.queue_number,
                    apt.patient?.name || 'Pasien',
                    apt.doctor?.name || '',
                    apt.destination || ''
                );

                // Reload halaman setelah beberapa saat untuk update status
                setTimeout(() => location.reload(), 5000);

                return true;
            } else {
                alert(data.message || 'Tidak ada antrian yang menunggu');
                return false;
            }
        } catch (error) {
            console.error('Error calling next queue:', error);
            alert('Terjadi kesalahan saat memanggil antrian');
            return false;
        }
    },

    /**
     * Panggil antrian spesifik via API  
     */
    callSpecific: async function (url, id, csrfToken, body = {}) {
        try {
            const response = await fetch(url.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body || {})
            });

            const data = await response.json();

            if (data.success && data.appointment) {
                const apt = data.appointment;
                this.speak(
                    apt.queue_number,
                    apt.patient?.name || 'Pasien',
                    apt.doctor?.name || '',
                    apt.destination || ''
                );

                // Reload halaman setelah beberapa saat untuk update status
                setTimeout(() => location.reload(), 5000);

                return true;
            } else {
                alert(data.message || 'Gagal memanggil antrian');
                return false;
            }
        } catch (error) {
            console.error('Error calling specific queue:', error);
            alert('Terjadi kesalahan saat memanggil antrian');
            return false;
        }
    },

    /**
     * Test suara
     */
    test: function () {
        this.speak('001', 'Ahmad Budiman', 'Dr. Siti');
    }
};

// Auto-initialize saat DOM siap
document.addEventListener('DOMContentLoaded', function () {
    QueueVoice.init();
});
