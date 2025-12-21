<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class DummyPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            [
                'name' => 'Budi Santoso',
                'nik' => '3273011234560001',
                'gender' => 'L',
                'birth_date' => '1985-03-15',
                'birth_place' => 'Bandung',
                'phone' => '081234567890',
                'email' => 'budi.santoso@email.com',
                'address' => 'Jl. Merdeka No. 45 RT 02/RW 05, Kelurahan Sukajadi, Kec. Sukajadi, Kota Bandung, Jawa Barat',
                'blood_type' => 'O',
                'allergy' => 'Penisilin',
                'emergency_contact' => 'Siti Santoso',
                'emergency_phone' => '081234567891',
            ],
            [
                'name' => 'Dewi Lestari',
                'nik' => '3273025678900002',
                'gender' => 'P',
                'birth_date' => '1990-07-22',
                'birth_place' => 'Jakarta',
                'phone' => '082345678901',
                'email' => 'dewi.lestari@email.com',
                'address' => 'Jl. Sudirman No. 123 RT 01/RW 03, Kelurahan Menteng, Kec. Menteng, Kota Jakarta Pusat, DKI Jakarta',
                'blood_type' => 'A',
                'allergy' => null,
                'emergency_contact' => 'Andi Lestari',
                'emergency_phone' => '082345678902',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'nik' => '3506019876540003',
                'gender' => 'L',
                'birth_date' => '1978-11-08',
                'birth_place' => 'Surabaya',
                'phone' => '083456789012',
                'email' => 'ahmad.fauzi@email.com',
                'address' => 'Jl. Pahlawan No. 88 RT 04/RW 02, Kelurahan Bubutan, Kec. Bubutan, Kota Surabaya, Jawa Timur',
                'blood_type' => 'B',
                'allergy' => 'Seafood',
                'emergency_contact' => 'Fatimah Fauzi',
                'emergency_phone' => '083456789013',
            ],
            [
                'name' => 'Rina Wulandari',
                'nik' => '3374036543210004',
                'gender' => 'P',
                'birth_date' => '1995-02-28',
                'birth_place' => 'Semarang',
                'phone' => '084567890123',
                'email' => 'rina.wulandari@email.com',
                'address' => 'Jl. Pemuda No. 56 RT 03/RW 01, Kelurahan Sekayu, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah',
                'blood_type' => 'AB',
                'allergy' => null,
                'emergency_contact' => 'Joko Wulandari',
                'emergency_phone' => '084567890124',
            ],
            [
                'name' => 'Muhammad Rizky',
                'nik' => '1371014321090005',
                'gender' => 'L',
                'birth_date' => '2000-05-10',
                'birth_place' => 'Padang',
                'phone' => '085678901234',
                'email' => 'muhammad.rizky@email.com',
                'address' => 'Jl. Veteran No. 12 RT 01/RW 04, Kelurahan Padang Barat, Kec. Padang Barat, Kota Padang, Sumatera Barat',
                'blood_type' => 'O',
                'allergy' => 'Debu',
                'emergency_contact' => 'Nurul Hidayah',
                'emergency_phone' => '085678901235',
            ],
            [
                'name' => 'Sri Wahyuni',
                'nik' => '5171016789050006',
                'gender' => 'P',
                'birth_date' => '1988-09-14',
                'birth_place' => 'Denpasar',
                'phone' => '086789012345',
                'email' => 'sri.wahyuni@email.com',
                'address' => 'Jl. Raya Puputan No. 77 RT 02/RW 06, Kelurahan Renon, Kec. Denpasar Selatan, Kota Denpasar, Bali',
                'blood_type' => 'A',
                'allergy' => null,
                'emergency_contact' => 'Made Wahyuni',
                'emergency_phone' => '086789012346',
            ],
            [
                'name' => 'Hendra Gunawan',
                'nik' => '6471017654320007',
                'gender' => 'L',
                'birth_date' => '1982-12-25',
                'birth_place' => 'Balikpapan',
                'phone' => '087890123456',
                'email' => 'hendra.gunawan@email.com',
                'address' => 'Jl. Jendral Sudirman No. 200 RT 05/RW 03, Kelurahan Klandasan Ilir, Kec. Balikpapan Kota, Kota Balikpapan, Kalimantan Timur',
                'blood_type' => 'B',
                'allergy' => 'Telur',
                'emergency_contact' => 'Linda Gunawan',
                'emergency_phone' => '087890123457',
            ],
            [
                'name' => 'Putri Rahayu',
                'nik' => '7371018901230008',
                'gender' => 'P',
                'birth_date' => '1992-04-18',
                'birth_place' => 'Makassar',
                'phone' => '088901234567',
                'email' => 'putri.rahayu@email.com',
                'address' => 'Jl. A.P. Pettarani No. 45 RT 06/RW 02, Kelurahan Masale, Kec. Panakkukang, Kota Makassar, Sulawesi Selatan',
                'blood_type' => 'O',
                'allergy' => null,
                'emergency_contact' => 'Agus Rahayu',
                'emergency_phone' => '088901234568',
            ],
            [
                'name' => 'Irfan Hakim',
                'nik' => '1271019012340009',
                'gender' => 'L',
                'birth_date' => '1975-08-30',
                'birth_place' => 'Medan',
                'phone' => '089012345678',
                'email' => 'irfan.hakim@email.com',
                'address' => 'Jl. Gatot Subroto No. 150 RT 04/RW 05, Kelurahan Sei Sikambing B, Kec. Medan Sunggal, Kota Medan, Sumatera Utara',
                'blood_type' => 'AB',
                'allergy' => 'Kacang',
                'emergency_contact' => 'Yanti Hakim',
                'emergency_phone' => '089012345679',
            ],
            [
                'name' => 'Nurul Hidayati',
                'nik' => '3578010123450010',
                'gender' => 'P',
                'birth_date' => '1998-01-05',
                'birth_place' => 'Yogyakarta',
                'phone' => '081123456789',
                'email' => 'nurul.hidayati@email.com',
                'address' => 'Jl. Malioboro No. 99 RT 01/RW 01, Kelurahan Suryatmajan, Kec. Danurejan, Kota Yogyakarta, DI Yogyakarta',
                'blood_type' => 'A',
                'allergy' => null,
                'emergency_contact' => 'Bambang Hidayati',
                'emergency_phone' => '081123456790',
            ],
        ];

        foreach ($patients as $data) {
            // Check if NIK already exists
            if (!Patient::where('nik', $data['nik'])->exists()) {
                $patient = new Patient();
                $patient->no_rm = Patient::generateNoRM();
                $patient->name = $data['name'];
                $patient->nik = $data['nik'];
                $patient->gender = $data['gender'];
                $patient->birth_date = $data['birth_date'];
                $patient->birth_place = $data['birth_place'];
                $patient->phone = $data['phone'];
                $patient->email = $data['email'];
                $patient->address = $data['address'];
                $patient->blood_type = $data['blood_type'];
                $patient->allergy = $data['allergy'];
                $patient->emergency_contact = $data['emergency_contact'];
                $patient->emergency_phone = $data['emergency_phone'];
                $patient->save();
            }
        }
    }
}
