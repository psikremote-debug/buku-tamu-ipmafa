<?php
// proses_form.php

// Pastikan koneksi tersedia
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    return; // Atau handle error
}

date_default_timezone_set('Asia/Jakarta');

// Fungsi Logging Sederhana (Global Scope)
if (!function_exists('catat_log')) {
    function catat_log($pesan) {
        $logFile = __DIR__ . '/debug_log.txt';
        $waktu = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$waktu] $pesan" . PHP_EOL, FILE_APPEND);
    }
}

// === AWAL BLOK LOGIKA FORM REGISTRASI TAMU ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 0. Cek apakan POST request kosong (biasanya karena ukuran file melebihi post_max_size)
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $_SESSION['gagal'] = "Ukuran data/foto terlalu besar! Maksimal upload diperbolehkan server: " . ini_get('post_max_size');
        header("Location: index.php");
        exit;
    }

    if (isset($_POST['nama_tamu'])) {
        catat_log("Mulai proses simpan tamu: " . $_POST['nama_tamu']); 
        // Ambil dan sanitasi data dari form
        $nama_tamu = htmlspecialchars(trim($_POST['nama_tamu'] ?? ''));
        $asal_instansi = htmlspecialchars(trim($_POST['asal_instansi'] ?? ''));
        $jabatan = htmlspecialchars(trim($_POST['jabatan'] ?? ''));
        $no_telepon = htmlspecialchars(trim($_POST['no_telepon'] ?? ''));
        
        // Validasi Telepon
        if (!empty($no_telepon)) {
            if (!preg_match('/^[0-9+]{10,15}$/', $no_telepon)) {
                $_SESSION['gagal'] = "Nomor telepon tidak valid. Gunakan angka, panjang 10-15 digit.";
                $validation_error = true;
                catat_log("Validasi telepon gagal: $no_telepon");
            }
        }
        
        
        $bertemu_dengan = htmlspecialchars(trim($_POST['bertemu_dengan'] ?? ''));
        $keperluan = htmlspecialchars(trim($_POST['keperluan'] ?? ''));
        $catatan_tambahan = htmlspecialchars(trim($_POST['catatan_tambahan'] ?? ''));

        $foto_tamu_filename = null;
        $foto_processing_error = false;
        
        // Proses Foto
        if (!empty($_POST['foto_tamu_data'])) {
            $raw_foto_input = $_POST['foto_tamu_data'];
            if (preg_match('/^data:image\/(\w+);base64,/', $raw_foto_input, $match)) {
                $mime_extension = strtolower($match[1]);
                $mime_extension = $mime_extension === 'jpeg' ? 'jpg' : $mime_extension;
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                if (!in_array($mime_extension, $allowed_extensions, true)) {
                    $_SESSION['gagal'] = "Format foto tidak didukung. Gunakan JPG atau PNG.";
                    $foto_processing_error = true;
                } else {
                    $base64_data = substr($raw_foto_input, strpos($raw_foto_input, ',') + 1);
                    $base64_data = str_replace(' ', '+', $base64_data);
                    $image_binary = base64_decode($base64_data, true);
                    if ($image_binary === false) {
                        $_SESSION['gagal'] = "Foto tidak dapat diproses. Silakan coba lagi.";
                        $foto_processing_error = true;
                    } else {
                        $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tamu';
                        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
                            $_SESSION['gagal'] = "Folder penyimpanan foto tidak dapat dibuat.";
                            $foto_processing_error = true;
                            catat_log("Gagal membuat folder uploads/tamu");
                        } else {
                            // Secure upload folder
                            $htaccess_path = $upload_dir . DIRECTORY_SEPARATOR . '.htaccess';
                            if (!file_exists($htaccess_path)) {
                                file_put_contents($htaccess_path, "php_flag engine off");
                            }

                            try {
                                $random_suffix = bin2hex(random_bytes(4));
                            } catch (\Exception $e) {
                                if (function_exists('openssl_random_pseudo_bytes')) {
                                    $random_suffix = bin2hex(openssl_random_pseudo_bytes(4));
                                } else {
                                    $random_suffix = sprintf('%08x', mt_rand());
                                }
                            }
                            $foto_tamu_filename = 'tamu_' . date('Ymd_His') . '_' . $random_suffix . '.' . $mime_extension;
                            $foto_path = $upload_dir . DIRECTORY_SEPARATOR . $foto_tamu_filename;
                            if (file_put_contents($foto_path, $image_binary) === false) {
                                $_SESSION['gagal'] = "Foto gagal disimpan. Silakan coba lagi.";
                                $foto_processing_error = true;
                                $foto_tamu_filename = null;
                                catat_log("Gagal menyimpan file foto ke: $foto_path");
                            } else {
                                catat_log("Foto berhasil disimpan: $foto_tamu_filename");
                            }
                        }
                    }
                }
            } elseif (trim($raw_foto_input) !== '') {
                $_SESSION['gagal'] = "Foto tidak dikenali. Silakan ambil ulang.";
                $foto_processing_error = true;
            }
        }

        $tanggal_kunjungan = date("Y-m-d");
        $waktu_masuk = date("H:i:s");

        if (!isset($validation_error)) $validation_error = false;

        if (
            empty($nama_tamu) ||
            empty($asal_instansi) ||
            empty($jabatan) ||
            empty($no_telepon) ||
            empty($bertemu_dengan)
        ) {
            $_SESSION['gagal'] = "Semua kolom wajib diisi kecuali Keperluan.";
            catat_log("Validasi gagal: Kolom wajib ada yang kosong.");
        } elseif ($foto_processing_error || $validation_error) {
            // Error handled above
        } else {
            $sql_tamu = "INSERT INTO tb_tamu (tanggal_kunjungan, waktu_masuk, nama_tamu, asal_instansi, jabatan, no_telepon, bertemu_dengan, keperluan, catatan_tambahan, foto_tamu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt_tamu = $koneksi->prepare($sql_tamu)) {
                $stmt_tamu->bind_param("ssssssssss",
                    $tanggal_kunjungan, $waktu_masuk, $nama_tamu, $asal_instansi, $jabatan, $no_telepon, $bertemu_dengan, $keperluan, $catatan_tambahan, $foto_tamu_filename
                );
                if ($stmt_tamu->execute()) {
                    $_SESSION['sukses'] = "Registrasi kunjungan berhasil disimpan. Terima kasih!";
                    catat_log("Sukses: Data tamu berhasil disimpan ke DB. ID Tamu baru: " . $stmt_tamu->insert_id);
                } else {
                    $_SESSION['gagal'] = "Gagal menyimpan data kunjungan: " . $stmt_tamu->error;
                    catat_log("Error SQL Execute: " . $stmt_tamu->error);
                }
                $stmt_tamu->close();
            } else {
                $_SESSION['gagal'] = "Gagal menyiapkan statement SQL tamu: " . $koneksi->error;
                catat_log("Error SQL Prepare: " . $koneksi->error);
            }
        }

    } // Tutup if isset post nama_tamu

    if (!empty($_SESSION['gagal'])) {
        $_SESSION['old_tamu'] = [
            'nama_tamu' => $nama_tamu,
            'asal_instansi' => $asal_instansi,
            'jabatan' => $jabatan,
            'no_telepon' => $no_telepon,
            'bertemu_dengan' => $bertemu_dengan,
            'keperluan' => $keperluan,
            'catatan_tambahan' => $catatan_tambahan
        ];
    } else {
        unset($_SESSION['old_tamu']);
    }

    if (!empty($_SESSION['sukses']) || !empty($_SESSION['gagal'])) {
        header("Location: index.php");
        exit;
    }

}
// === AKHIR BLOK LOGIKA FORM REGISTRASI TAMU ===


// === AWAL BLOK LOGIKA FORM KEPUASAN (PRESERVED) ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_kepuasan'])) { 
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $nama_responden = htmlspecialchars(trim($_POST['nama_responden'] ?? '')); 
        $id_tamu_fk_input = filter_input(INPUT_POST, 'id_tamu_fk', FILTER_VALIDATE_INT);
        $id_tamu_fk = $id_tamu_fk_input ?: null;

        $nilai_pelayanan = filter_input(INPUT_POST, 'nilai_pelayanan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_fasilitas = filter_input(INPUT_POST, 'nilai_fasilitas', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_keramahan = filter_input(INPUT_POST, 'nilai_keramahan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_kecepatan = filter_input(INPUT_POST, 'nilai_kecepatan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $saran_masukan = htmlspecialchars(trim($_POST['saran_masukan'] ?? ''));

        $tanggal_survei = date("Y-m-d");
        $waktu_survei = date("H:i:s");

        if ($nilai_pelayanan === false || $nilai_fasilitas === false || $nilai_keramahan === false || $nilai_kecepatan === false) {
            $_SESSION['gagal'] = "Semua pertanyaan penilaian wajib diisi dengan benar (skala 1-5).";
        } else {
            $sql_kepuasan = "INSERT INTO tb_kepuasan (id_tamu_fk, nama_responden, tanggal_survei, waktu_survei, nilai_pelayanan, nilai_fasilitas, nilai_keramahan, nilai_kecepatan, saran_masukan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_kepuasan = $koneksi->prepare($sql_kepuasan)) {
                $stmt_kepuasan->bind_param("isssiiiis", $id_tamu_fk, $nama_responden, $tanggal_survei, $waktu_survei, $nilai_pelayanan, $nilai_fasilitas, $nilai_keramahan, $nilai_kecepatan, $saran_masukan);
                if ($stmt_kepuasan->execute()) {
                    $_SESSION['sukses'] = "Survei kepuasan Anda berhasil dikirim. Terima kasih atas partisipasinya!";
                } else {
                    $_SESSION['gagal'] = "Gagal menyimpan data survei: " . $stmt_kepuasan->error;
                }
                $stmt_kepuasan->close();
            } else {
                $_SESSION['gagal'] = "Gagal menyiapkan statement SQL survei: " . $koneksi->error;
            }
        }
    } else {
        $_SESSION['gagal'] = "Koneksi database tidak tersedia. Tidak dapat menyimpan data survei.";
    }
}
// === AKHIR BLOK LOGIKA FORM KEPUASAN ===
?>
