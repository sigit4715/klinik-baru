<?php

/*
|--------------------------------------------------------------------------
| Hak akses (permission) per menu
|--------------------------------------------------------------------------
|
| Daftar semua hak akses yang bisa dicentang di halaman Manajemen Role.
| Kunci (mis. "patients.view") dipakai di route (middleware "permission:")
| dan di Blade (@can). Role "admin" (sistem) selalu punya semua akses.
|
| Untuk menambah menu baru: tambahkan kunci di sini, lindungi route-nya
| dengan middleware('permission:kunci'), lalu bungkus menu dengan @can.
|
*/

return [
    'Pasien' => [
        'patients.view' => 'Lihat daftar & detail pasien',
        'patients.manage' => 'Tambah & edit pasien',
        'patients.delete' => 'Hapus pasien',
    ],

    'Pendaftaran & Antrian' => [
        'appointments.view' => 'Lihat antrian & cetak struk antrian',
        'appointments.manage' => 'Daftarkan & edit antrian',
        'appointments.call' => 'Panggil antrian / ubah status antrian',
        'appointments.delete' => 'Hapus antrian',
        'queue.display' => 'Buka layar display antrian (TV)',
    ],

    'Pemeriksaan & Rekam Medis' => [
        'medical_records.view' => 'Lihat & cetak rekam medis',
        'medical_records.manage' => 'Isi pemeriksaan / buat rekam medis',
    ],

    'Dokter' => [
        'doctors.view' => 'Lihat daftar dokter',
        'doctors.manage' => 'Tambah & edit dokter',
        'doctors.delete' => 'Hapus dokter',
    ],

    'Kasir / Pembayaran' => [
        'payments.view' => 'Lihat pembayaran & cetak invoice/struk',
        'payments.manage' => 'Proses pembayaran',
        'payments.cancel' => 'Batalkan & hapus pembayaran yang belum lunas',
        'payments.refund' => 'Refund pembayaran (pengembalian dana)',
    ],

    'Apotek / Obat' => [
        'medicines.view' => 'Lihat daftar obat & kartu stok',
        'medicines.manage' => 'Tambah & edit data obat',
        'medicines.stock' => 'Stok masuk, retur, stok opname & obat dibuang',
        'medicines.delete' => 'Hapus (arsipkan) obat',
    ],

    'Layanan / Tindakan' => [
        'services.view' => 'Lihat daftar layanan',
        'services.manage' => 'Tambah & edit layanan',
        'services.delete' => 'Hapus layanan',
    ],

    'Laporan' => [
        'reports.finance' => 'Laporan keuangan & pendapatan (termasuk ringkasan di dashboard)',
        'reports.patients' => 'Laporan pasien (KB, imunisasi, ibu hamil, dll.)',
    ],

    'Pengaturan' => [
        'settings.manage' => 'Ubah profil klinik',
        'users.manage' => 'Manajemen user',
        'roles.manage' => 'Manajemen role & hak akses',
        'backup.manage' => 'Backup database',
        'archive.manage' => 'Arsip data: lihat & pulihkan data yang dihapus',
        'audit.view' => 'Lihat audit log (riwayat aktivitas)',
        'system.cache' => 'Bersihkan cache aplikasi',
    ],
];
