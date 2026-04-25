<?php
require_once __DIR__ . '/../koneksi/koneksi.php';

try {
    echo "Memulai migrasi database...\n";

    // 1. Hapus kolom email_tamu dari tb_tamu (JIKA ADA)
    $check_email = $koneksi->query("SHOW COLUMNS FROM tb_tamu LIKE 'email_tamu'");
    if ($check_email->num_rows > 0) {
        if ($koneksi->query("ALTER TABLE tb_tamu DROP COLUMN email_tamu")) {
             echo "- Berhasil menghapus kolom email_tamu.\n";
        } else {
             echo "- Gagal menghapus kolom email_tamu: " . $koneksi->error . "\n";
        }
    } else {
         echo "- Kolom email_tamu sudah tidak ada.\n";
    }

    // 2. Buat tabel tb_tujuan
    $sql_tujuan = "CREATE TABLE IF NOT EXISTS tb_tujuan (
        id_tujuan INT AUTO_INCREMENT PRIMARY KEY,
        nama_tujuan VARCHAR(255) NOT NULL,
        keterangan TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($koneksi->query($sql_tujuan)) {
        echo "- Berhasil membuat/memeriksa tabel tb_tujuan.\n";
    } else {
        echo "- Gagal membuat tabel tb_tujuan: " . $koneksi->error . "\n";
    }

    // Insert dummy data JIKA KOSONG
    $check_tujuan = $koneksi->query("SELECT id_tujuan FROM tb_tujuan LIMIT 1");
    if ($check_tujuan->num_rows == 0) {
        $koneksi->query("INSERT INTO tb_tujuan (nama_tujuan, keterangan) VALUES 
            ('Kepala Dinas', 'Pimpinan'),
            ('Sekretaris Dinas', 'Sekretariat'),
            ('Bidang E-Government', 'Urusan SPBE, Aplikasi'),
            ('Bidang Aptika', 'Persandian, Statistik'),
            ('Umum', 'Keperluan Umum / Resepsionis')
        ");
        echo "  > Data awal tb_tujuan disisipkan.\n";
    }


    // 3. Buat tabel tb_keperluan
    $sql_keperluan = "CREATE TABLE IF NOT EXISTS tb_keperluan (
        id_keperluan INT AUTO_INCREMENT PRIMARY KEY,
        nama_keperluan VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    if ($koneksi->query($sql_keperluan)) {
        echo "- Berhasil membuat/memeriksa tabel tb_keperluan.\n";
    } else {
        echo "- Gagal membuat tabel tb_keperluan: " . $koneksi->error . "\n";
    }

    // Insert dummy data JIKA KOSONG
    $check_kep = $koneksi->query("SELECT id_keperluan FROM tb_keperluan LIMIT 1");
    if ($check_kep->num_rows == 0) {
        $koneksi->query("INSERT INTO tb_keperluan (nama_keperluan) VALUES 
            ('Konsultasi / Koordinasi'),
            ('Rapat'),
            ('Penawaran Produk/Kerjasama'),
            ('Kunjungan Kerja / Studi Banding'),
            ('Laporan Pengaduan'),
            ('Lainnya')
        ");
        echo "  > Data awal tb_keperluan disisipkan.\n";
    }

    echo "Migrasi database selesai.\n";

} catch (Exception $e) {
    echo "Terjadi kesalahan: " . $e->getMessage() . "\n";
}
