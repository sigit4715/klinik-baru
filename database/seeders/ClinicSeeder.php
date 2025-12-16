<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Medicine;
use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        // Create 2 Doctors
        $doctor1 = Doctor::create([
            'sip_number' => 'SIP-001-2024',
            'name' => 'dr. Ahmad Wijaya, Sp.PD',
            'specialization' => 'Penyakit Dalam',
            'email' => 'ahmad.wijaya@klinik.com',
            'phone' => '081234567890',
            'address' => 'Jl. Dokter No. 1, Jakarta',
            'status' => 'active',
        ]);

        $doctor2 = Doctor::create([
            'sip_number' => 'SIP-002-2024',
            'name' => 'dr. Siti Rahayu',
            'specialization' => 'Umum',
            'email' => 'siti.rahayu@klinik.com',
            'phone' => '081234567891',
            'address' => 'Jl. Dokter No. 2, Jakarta',
            'status' => 'active',
        ]);

        // Create Services
        $services = [
            ['code' => 'SRV-0001', 'name' => 'Konsultasi Umum', 'description' => 'Konsultasi dengan dokter umum', 'price' => 50000, 'status' => 'active'],
            ['code' => 'SRV-0002', 'name' => 'Konsultasi Spesialis', 'description' => 'Konsultasi dengan dokter spesialis', 'price' => 150000, 'status' => 'active'],
            ['code' => 'SRV-0003', 'name' => 'Pemeriksaan Tekanan Darah', 'description' => 'Cek tensi darah', 'price' => 15000, 'status' => 'active'],
            ['code' => 'SRV-0004', 'name' => 'Pemeriksaan Gula Darah', 'description' => 'Cek kadar gula darah', 'price' => 25000, 'status' => 'active'],
            ['code' => 'SRV-0005', 'name' => 'Suntik Vitamin', 'description' => 'Injeksi vitamin B12/C', 'price' => 100000, 'status' => 'active'],
            ['code' => 'SRV-0006', 'name' => 'Nebulizer', 'description' => 'Terapi uap untuk pernapasan', 'price' => 75000, 'status' => 'active'],
            ['code' => 'SRV-0007', 'name' => 'Jahit Luka', 'description' => 'Penjahitan luka minor', 'price' => 150000, 'status' => 'active'],
            ['code' => 'SRV-0008', 'name' => 'Infus', 'description' => 'Pemasangan infus', 'price' => 200000, 'status' => 'active'],
            ['code' => 'SRV-0009', 'name' => 'EKG', 'description' => 'Pemeriksaan jantung', 'price' => 250000, 'status' => 'active'],
            ['code' => 'SRV-0010', 'name' => 'Tes Laboratorium Darah', 'description' => 'Pemeriksaan darah lengkap', 'price' => 350000, 'status' => 'active'],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        // Create Medicines
        $medicines = [
            ['code' => 'OBT-0001', 'name' => 'Paracetamol 500mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 200, 'min_stock' => 50, 'buy_price' => 3000, 'sell_price' => 5000, 'expired_date' => '2026-12-31', 'status' => 'active'],
            ['code' => 'OBT-0002', 'name' => 'Amoxicillin 500mg', 'category' => 'Kapsul', 'unit' => 'Strip', 'stock' => 150, 'min_stock' => 30, 'buy_price' => 8000, 'sell_price' => 12000, 'expired_date' => '2025-08-30', 'status' => 'active'],
            ['code' => 'OBT-0003', 'name' => 'Omeprazole 20mg', 'category' => 'Kapsul', 'unit' => 'Strip', 'stock' => 100, 'min_stock' => 20, 'buy_price' => 10000, 'sell_price' => 15000, 'expired_date' => '2025-10-31', 'status' => 'active'],
            ['code' => 'OBT-0004', 'name' => 'Antasida Doen', 'category' => 'Sirup', 'unit' => 'Botol', 'stock' => 80, 'min_stock' => 15, 'buy_price' => 12000, 'sell_price' => 18000, 'expired_date' => '2025-09-15', 'status' => 'active'],
            ['code' => 'OBT-0005', 'name' => 'Vitamin B Complex', 'category' => 'Tablet', 'unit' => 'Botol', 'stock' => 120, 'min_stock' => 25, 'buy_price' => 15000, 'sell_price' => 25000, 'expired_date' => '2026-06-20', 'status' => 'active'],
            ['code' => 'OBT-0006', 'name' => 'Ibuprofen 400mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 10, 'min_stock' => 30, 'buy_price' => 7000, 'sell_price' => 11000, 'expired_date' => '2025-11-30', 'status' => 'active'],
            ['code' => 'OBT-0007', 'name' => 'Cetirizine 10mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 90, 'min_stock' => 20, 'buy_price' => 6000, 'sell_price' => 10000, 'expired_date' => '2025-07-15', 'status' => 'active'],
            ['code' => 'OBT-0008', 'name' => 'Salbutamol Inhaler', 'category' => 'Inhaler', 'unit' => 'Pcs', 'stock' => 25, 'min_stock' => 10, 'buy_price' => 45000, 'sell_price' => 65000, 'expired_date' => '2026-03-01', 'status' => 'active'],
            ['code' => 'OBT-0009', 'name' => 'Metformin 500mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 180, 'min_stock' => 40, 'buy_price' => 5000, 'sell_price' => 8000, 'expired_date' => '2026-08-15', 'status' => 'active'],
            ['code' => 'OBT-0010', 'name' => 'Amlodipine 5mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 160, 'min_stock' => 35, 'buy_price' => 6000, 'sell_price' => 10000, 'expired_date' => '2026-05-20', 'status' => 'active'],
            ['code' => 'OBT-0011', 'name' => 'Ondansetron 4mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 60, 'min_stock' => 15, 'buy_price' => 12000, 'sell_price' => 18000, 'expired_date' => '2025-12-01', 'status' => 'active'],
            ['code' => 'OBT-0012', 'name' => 'Domperidone 10mg', 'category' => 'Tablet', 'unit' => 'Strip', 'stock' => 75, 'min_stock' => 20, 'buy_price' => 8000, 'sell_price' => 12000, 'expired_date' => '2026-02-28', 'status' => 'active'],
            ['code' => 'OBT-0013', 'name' => 'OBH Combi', 'category' => 'Sirup', 'unit' => 'Botol', 'stock' => 45, 'min_stock' => 15, 'buy_price' => 18000, 'sell_price' => 28000, 'expired_date' => '2025-10-15', 'status' => 'active'],
            ['code' => 'OBT-0014', 'name' => 'Betadine', 'category' => 'Cairan', 'unit' => 'Botol', 'stock' => 30, 'min_stock' => 10, 'buy_price' => 25000, 'sell_price' => 38000, 'expired_date' => '2026-04-30', 'status' => 'active'],
            ['code' => 'OBT-0015', 'name' => 'Kasa Steril', 'category' => 'Alkes', 'unit' => 'Pack', 'stock' => 100, 'min_stock' => 25, 'buy_price' => 8000, 'sell_price' => 12000, 'expired_date' => '2027-01-01', 'status' => 'active'],
        ];

        foreach ($medicines as $medicine) {
            Medicine::create($medicine);
        }

        // Indonesian names for patients
        $maleNames = ['Andi', 'Budi', 'Cahyo', 'Dedi', 'Eko', 'Fajar', 'Gilang', 'Hendra', 'Irfan', 'Joko', 'Kevin', 'Lukman'];
        $femaleNames = ['Ani', 'Bunga', 'Citra', 'Dewi', 'Eka', 'Fitri', 'Gita', 'Hana', 'Indah', 'Juli', 'Kartini', 'Lestari'];
        $lastNames = ['Pratama', 'Wijaya', 'Santoso', 'Kusuma', 'Saputra', 'Hidayat', 'Rahman', 'Putra', 'Sari', 'Wati', 'Ningrum', 'Rahayu'];
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta', 'Malang', 'Solo', 'Medan', 'Makassar', 'Palembang'];
        $bloodTypes = ['A', 'B', 'AB', 'O'];
        $complaints = [
            'Demam dan flu sudah 3 hari',
            'Batuk tidak sembuh-sembuh',
            'Sakit kepala dan pusing',
            'Nyeri perut dan mual',
            'Diare sudah 2 hari',
            'Tekanan darah tinggi',
            'Gula darah terasa tidak stabil',
            'Sesak napas',
            'Nyeri sendi dan otot',
            'Alergi kulit',
            'Radang tenggorokan',
            'Infeksi saluran kemih',
            'Kontrol rutin',
            'Minta surat keterangan sehat',
            'Cek kesehatan umum',
        ];

        $patients = [];
        $today = Carbon::today();

        // Create 25 patients with appointments for today
        for ($i = 1; $i <= 25; $i++) {
            $isMale = $i % 2 === 1;
            $firstName = $isMale ? $maleNames[array_rand($maleNames)] : $femaleNames[array_rand($femaleNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            $patient = Patient::create([
                'no_rm' => 'RM-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'nik' => '320101010101' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $firstName . ' ' . $lastName,
                'gender' => $isMale ? 'L' : 'P',
                'birth_place' => $cities[array_rand($cities)],
                'birth_date' => Carbon::now()->subYears(rand(20, 65))->subDays(rand(0, 365))->format('Y-m-d'),
                'address' => 'Jl. ' . $lastNames[array_rand($lastNames)] . ' No. ' . rand(1, 100) . ', ' . $cities[array_rand($cities)],
                'phone' => '08' . rand(1, 9) . rand(10000000, 99999999),
                'blood_type' => $bloodTypes[array_rand($bloodTypes)],
                'allergy' => $i % 5 === 0 ? 'Penisilin' : null,
                'emergency_contact' => $lastNames[array_rand($lastNames)],
                'emergency_phone' => '08' . rand(1, 9) . rand(10000000, 99999999),
            ]);

            $patients[] = $patient;

            // Create appointment for today (for patients 1-22)
            if ($i <= 22) {
                $doctor = $i % 2 === 0 ? $doctor1 : $doctor2;
                $status = 'waiting';
                if ($i <= 5)
                    $status = 'completed';
                elseif ($i <= 8)
                    $status = 'in_progress';

                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'appointment_date' => $today,
                    'appointment_time' => sprintf('%02d:%02d', 8 + floor($i / 3), ($i % 3) * 20),
                    'queue_number' => $today->format('Ymd') . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'complaint' => $complaints[array_rand($complaints)],
                    'status' => $status,
                ]);
            }
        }

        $this->command->info('Clinic seeder completed!');
        $this->command->info('Created: 2 Doctors, 25 Patients, 10 Services, 15 Medicines, 22 Appointments for today');
    }
}
