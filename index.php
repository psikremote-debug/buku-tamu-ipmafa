<?php
declare(strict_types=1);
session_start();
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

// Pastikan file koneksi.php ada dan dapat diakses
$koneksiPath = __DIR__ . DIRECTORY_SEPARATOR . "koneksi" . DIRECTORY_SEPARATOR . "koneksi.php";
if (file_exists($koneksiPath)) {
    require_once $koneksiPath;
} else {
    die("File koneksi database tidak ditemukan. Harap periksa konfigurasi.");
}

// MEMPROSES FORM (Harus sebelum output HTML apapun untuk menghindari error header)
require_once __DIR__ . DIRECTORY_SEPARATOR . 'proses_form.php';

date_default_timezone_set('Asia/Jakarta');

$profile = null;
if (isset($koneksi) && $koneksi instanceof mysqli) { // Pastikan $koneksi adalah objek mysqli yang valid
    $result = $koneksi->query("SELECT * FROM tb_profile LIMIT 1");
    if ($result) {
        $profile = $result->fetch_assoc();
    } else {
        // Tetapkan nilai default jika query gagal
        $profile = ['nama_perusahaan' => 'Perusahaan Default', 'foto' => 'default-logo.png', 'foto2' => 'default-image.png'];
    }
} else {
    // Penanganan jika koneksi gagal atau $koneksi bukan objek mysqli
    $profile = ['nama_perusahaan' => 'Perusahaan Default (Koneksi Gagal)', 'foto' => 'default-logo.png', 'foto2' => 'default-image.png'];
}

// Load Master Data untuk Dropdown Form Tamu
$list_tujuan = [];
$list_keperluan = [];

if (isset($koneksi) && $koneksi instanceof mysqli) {
    // Fetch Tujuan
    $res_tujuan = $koneksi->query("SELECT nama_tujuan FROM tb_tujuan ORDER BY nama_tujuan ASC");
    if ($res_tujuan) {
        while ($row = $res_tujuan->fetch_assoc()) {
            $list_tujuan[] = $row['nama_tujuan'];
        }
    }
    // Fetch Keperluan
    $res_keperluan = $koneksi->query("SELECT nama_keperluan FROM tb_keperluan ORDER BY nama_keperluan ASC");
    if ($res_keperluan) {
        while ($row = $res_keperluan->fetch_assoc()) {
            $list_keperluan[] = $row['nama_keperluan'];
        }
    }
}


$currentDay = mktime(0, 0, 0, (int)date("n"), (int)date("j"), (int)date("Y"));

function tglIndonesia(string $str): string {
    $translations = [
        'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa',
        'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jum\'at',
        'Sat' => 'Sabtu', 'January' => 'Januari', 'February' => 'Februari',
        'March' => 'Maret', 'April' => 'April', 'May' => 'Mei',
        'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
        'September' => 'September', 'October' => 'Oktober',
        'November' => 'November', 'December' => 'Desember'
    ];
    return strtr(trim($str), $translations);
}

$currentPage = $_GET['page'] ?? 'beranda';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - <?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Perusahaan') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>


    <style>
        :root {
            /* Palet Warna Gradasi Cerah Baru */
            --bright-gradient-start:rgb(121, 147, 241); /* Hijau Mint Cerah */
            --bright-gradient-end:rgb(61, 64, 153);   /* Biru Laut Dalam */
            --bright-gradient-alt-start: #FFAR70; /* Oranye Matahari Terbenam */
            --bright-gradient-alt-end: #FD5E53;   /* Merah Koral */

            --primary-color: #0E5CAD; /* Biru sebagai warna primer solid */
            --accent-color: #FFAR70;  /* Oranye sebagai aksen */

            /* Warna Netral Modern */
            --neutral-lightest: #F8F9FA; /* Sangat terang, hampir putih */
            --neutral-lighter: #E9ECEF;  /* Abu-abu terang */
            --neutral-light: #DEE2E6;   /* Abu-abu sedikit lebih gelap */
            --neutral-medium: #CED4DA;  /* Abu-abu sedang */
            --neutral-dark: #495057;     /* Abu-abu gelap untuk teks */
            --neutral-darker: #343A40;   /* Abu-abu sangat gelap */
            --neutral-darkest: #212529;  /* Hampir hitam */

            /* Gradien Utama */
            --gradient-main: linear-gradient(135deg, var(--bright-gradient-start) 0%, var(--bright-gradient-end) 100%);
            --gradient-accent: linear-gradient(135deg, var(--bright-gradient-alt-start) 0%, var(--bright-gradient-alt-end) 100%);
            --gradient-border: linear-gradient(90deg, transparent, var(--primary-color), transparent);
            --primary-gradient: var(--gradient-main);

            /* Bayangan yang Ditingkatkan */
            --shadow-soft: 0 4px 15px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 12px 35px rgba(0, 0, 0, 0.12);

            /* Radius Sudut yang Konsisten */
            --radius-sm: 0.375rem; /* 6px */
            --radius-md: 0.75rem;  /* 12px */
            --radius-lg: 1.25rem;  /* 20px */
            --radius-xl: 2rem;    /* 32px */
            --radius-full: 9999px;

            /* Transisi */
            --transition-fast: all 0.2s ease-in-out;
            --transition-medium: all 0.35s ease-in-out;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--neutral-lightest);
            color: var(--neutral-darker);
            line-height: 1.7;
            font-weight: 400;
        }

        /* Struktur Layout */
        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden; /* Mencegah scroll horizontal yang tidak diinginkan */
        }

        /* Header Modern */
        .app-header {
            background: rgba(255, 255, 255, 0.8); /* Efek glassmorphism tipis */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-soft);
            padding: 1rem 0;
            border-bottom: 1px solid var(--neutral-light);
            position: sticky;
            top: 0;
            z-index: 1000;
            overflow: hidden;
        }
        .app-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(78, 115, 223, 0.12), rgba(14, 92, 173, 0));
            opacity: 0.8;
            pointer-events: none;
        }
        .app-header::after {
            content: '';
            position: absolute;
            left: -20%;
            bottom: 0;
            width: 140%;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(78, 115, 223, 0.8), transparent);
            animation: headerGlow 4s ease-in-out infinite;
        }
        @keyframes headerGlow {
            0%, 100% { opacity: 0.5; transform: translateX(-10%); }
            50% { opacity: 1; transform: translateX(10%); }
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem; /* Mengurangi gap sedikit */
        }

        .company-logo {
            width: 50px; /* Sedikit lebih kecil */
            height: 50px;
            object-fit: contain;
            border-radius: var(--radius-md);
            border: 2px solid var(--primary-color);
            box-shadow: var(--shadow-soft);
            transition: var(--transition-medium);
        }
        .company-logo:hover {
            transform: scale(1.1) rotate(-3deg);
            box-shadow: var(--shadow-medium);
        }
        .logo-badge {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(78, 115, 223, 0.12);
            border: 1px solid rgba(78, 115, 223, 0.25);
            animation: floatBadge 5s ease-in-out infinite;
        }
        @keyframes floatBadge {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .company-title h1 {
            font-size: 1.25rem; /* Menyesuaikan ukuran font */
            font-weight: 700;
            color: var(--primary-color);
        }
        .company-title small {
            font-size: 0.8rem;
            color: var(--neutral-dark);
            font-weight: 500;
        }

        .header-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(78, 115, 223, 0.12);
            color: #1e3a8a;
            font-size: 0.75rem;
            font-weight: 600;
        }


        /* Tombol Menu Canggih */
        .menu-actions .btn {
            border-radius: var(--radius-full);
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition-medium);
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .menu-actions .btn-gradient {
            background: var(--gradient-main);
            color: white;
            border: none;
        }
        .menu-actions .btn-gradient:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: var(--shadow-medium);
            filter: brightness(1.1);
        }
        .menu-actions .btn-outline-dynamic {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        .menu-actions .btn-outline-dynamic:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px) scale(1.03);
            box-shadow: var(--shadow-medium);
        }


        /* Konten Utama */
        .main-content {
            flex: 1;
            padding: 2.5rem 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem; /* Sedikit lebih banyak ruang */
        }

        @media (min-width: 992px) {
            .content-grid {
                grid-template-columns: 2fr 3fr; /* Proporsi baru, form lebih lebar */
            }
        }

        /* Bagian Selamat Datang yang Ditingkatkan */
        .welcome-section {
            background: var(--gradient-main);
            color: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-strong);
            transform: translateY(0);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        .welcome-section:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
        }
        .welcome-section::before { /* Efek latar belakang halus */
            content: '';
            position: absolute;
            top: -20%; right: -20%;
            width: 150%; height: 150%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.07'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: bgPatternScroll 45s linear infinite;
            z-index: 0;
        }
        .welcome-section::after {
            content: '';
            position: absolute;
            inset: 16px;
            border-radius: calc(var(--radius-lg) - 8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            z-index: 0;
            pointer-events: none;
        }

        .welcome-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(2px);
            opacity: 0.25;
            z-index: 0;
            animation: floatSoft 12s ease-in-out infinite;
        }
        .welcome-orb.orb-1 {
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, 0.35);
            top: -30px;
            left: -30px;
        }
        .welcome-orb.orb-2 {
            width: 110px;
            height: 110px;
            background: rgba(255, 255, 255, 0.28);
            bottom: 20px;
            right: -20px;
            animation-delay: -3s;
        }
        .welcome-orb.orb-3 {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.22);
            top: 45%;
            right: 15%;
            animation-delay: -6s;
        }
        @keyframes floatSoft {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        @keyframes bgPatternScroll {
            0% {transform: translate(0, 0);}
            100% {transform: translate(-50px, -50px);}
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .welcome-content .badge {
            padding: 0.5em 1em;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .welcome-content .display-5 { font-weight: 700; }
        .welcome-content p { opacity: 0.9; font-weight: 300;}

        .modern-divider {
            height: 2px;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.5) 50%, rgba(255,255,255,0) 100%);
            margin: 2rem 0;
            border: none;
        }

        .company-info-welcome {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 1.25rem;
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .company-info-welcome h3 { font-weight: 600; margin-bottom: 0.75rem;}
        .company-info-welcome p { margin-bottom: 0.5rem; }
        .company-info-welcome .text-muted { color: rgba(255, 255, 255, 0.75) !important; }

        .company-info-list {
            display: grid;
            gap: 0.6rem;
            margin-top: 0.75rem;
        }
        .company-info-list .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
        }
        .company-info-list .info-item i {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            margin-top: 0.15rem;
        }
        .company-info-list .info-item a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }
        .company-info-list .info-item a:hover {
            text-decoration: underline;
        }

        .welcome-hero {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            letter-spacing: 0.4px;
        }


        /* Bagian Formulir yang Ditingkatkan */
        .form-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--neutral-lighter);
            transition: var(--transition-medium);
        }
        .form-section:hover {
            box-shadow: var(--shadow-strong);
        }

        .section-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem; /* Lebih banyak spasi */
        }
        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px; /* Lebih panjang */
            height: 4px; /* Lebih tebal */
            background: var(--gradient-accent);
            border-radius: var(--radius-full);
        }
        .section-header h2 {
            font-weight: 700;
            color: var(--primary-color); /* Menggunakan warna primer */
        }

        /* Elemen Formulir Modern */
        .form-control, .form-select, .select2-container--bootstrap-5 .select2-selection {
            border-radius: var(--radius-sm);
            padding: 0.85rem 1.1rem; /* Sedikit lebih besar */
            border: 1px solid var(--neutral-light);
            transition: var(--transition-fast);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            background-color: var(--neutral-lightest); /* Latar belakang yang sangat terang */
        }
        .form-control:focus, .form-select:focus,
        .select2-container--bootstrap-5 .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(var(--rgb-primary-color, 14, 92, 173), 0.25); /* Menggunakan RGB untuk shadow */
            background-color: white;
        }
        /* Untuk Select2 agar placeholder terlihat */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--neutral-dark);
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--neutral-light);
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--primary-color) !important; /* gradien bisa terlalu ramai disini */
            color: white !important;
        }

        /* Tombol Utama yang Ditingkatkan */
        .btn-submit-modern {
            background: var(--gradient-main); /* Gradien cerah utama */
            border: none;
            border-radius: var(--radius-md); /* Sudut lebih bulat */
            padding: 0.85rem 2rem;
            font-weight: 600;
            letter-spacing: 0.8px;
            color: white;
            transition: var(--transition-medium);
            box-shadow: var(--shadow-soft);
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }
        .btn-submit-modern:before { /* Efek kilau saat hover */
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.6s ease;
        }
        .btn-submit-modern:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-strong);
            filter: brightness(1.15);
        }
        .btn-submit-modern:hover:before {
            left: 100%;
        }
        .btn-submit-modern:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: var(--shadow-soft);
        }


        /* Tampilan Jam yang Ditingkatkan */
        .clock-info {
            text-align: right;
        }
        .date-display {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--neutral-dark);
            margin-bottom: 0.25rem;
        }
        .clock-display {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(var(--rgb-primary-color, 14, 92, 173), 0.1);
            padding: 0.6rem 1rem;
            border-radius: var(--radius-sm);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--primary-color);
            border: 1px solid rgba(var(--rgb-primary-color, 14, 92, 173), 0.2);
        }
        .clock-display .bi-clock { font-size: 1.1rem; }
        .clock-display .time { font-size: 1rem; letter-spacing: 0.5px;}


        /* Animasi */
        .animate-float-enhanced {
            animation: floatEnhanced 8s ease-in-out infinite;
        }
        @keyframes floatEnhanced {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px) rotate(2deg); }
            100% { transform: translateY(0px); }
        }

        /* Penyesuaian Responsif */
        @media (max-width: 991.98px) {
            .content-grid {
                grid-template-columns: 1fr; /* Kembali ke satu kolom di tablet */
            }
            .welcome-section {
                min-height: auto; /* Hapus tinggi minimum agar konten bisa menentukan tinggi */
                padding: 2rem;
            }
            .form-section {
                padding: 2rem;
            }
             .welcome-content .display-5 { font-size: 2.25rem; }
        }

        @media (max-width: 767.98px) {
            .app-header {
                position: relative; /* Matikan sticky di mobile */
            }
            .header-content {
                flex-direction: column;
                align-items: center; /* Pusatkan item di mobile */
                gap: 1rem;
            }
            .logo-container {
                flex-direction: column;
                text-align: center;
            }
            .company-title h1 { font-size: 1.1rem; }
            .company-title small { font-size: 0.75rem; }

            .clock-info { text-align: center; margin-top: 0.5rem; }

            .main-content { padding: 1.5rem 0; }
            .welcome-section, .form-section { padding: 1.5rem; }
            .section-header::after { width: 50px; height: 3px; }
            .btn-submit-modern, .menu-actions .btn { padding: 0.7rem 1.5rem; font-size: 0.9rem;}
        }

        /* Footer Modern */
        .app-footer {
            padding: 2.25rem 0 2rem;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            font-size: 0.875rem;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
        }
        .app-footer .footer-inner {
            display: grid;
            gap: 1.5rem;
            align-items: center;
        }
        .app-footer .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }
        .app-footer .footer-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.08);
            padding: 6px;
        }
        .app-footer .footer-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.15rem;
            color: #ffffff;
        }
        .app-footer .footer-subtitle {
            color: rgba(226, 232, 240, 0.7);
            font-size: 0.8rem;
        }
        .app-footer .footer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .app-footer .footer-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            color: rgba(226, 232, 240, 0.8);
            font-size: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .app-footer a {
            color: #7dd3fc;
            text-decoration: none;
            font-weight: 600;
        }
        .app-footer a:hover {
            color: #bae6fd;
            text-decoration: underline;
        }
        .app-footer .footer-bottom {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid rgba(148, 163, 184, 0.15);
            padding-top: 1rem;
            color: rgba(226, 232, 240, 0.65);
            font-size: 0.75rem;
        }
        @media (min-width: 768px) {
            .app-footer .footer-inner {
                grid-template-columns: 1.3fr 1fr;
            }
        }

        /* SweetAlert Button Visibility */
        .swal2-popup .swal2-actions .swal2-confirm,
        .swal2-popup .swal2-actions .swal2-cancel {
            color: #ffffff !important;
            font-weight: 600;
            min-width: 96px;
            border: none !important;
        }
        .swal2-popup .swal2-actions .swal2-confirm {
            background-color: var(--primary-color) !important;
            box-shadow: 0 6px 16px rgba(14, 92, 173, 0.25) !important;
        }
        .swal2-popup .swal2-actions .swal2-cancel {
            background-color: #e74c3c !important;
            box-shadow: 0 6px 16px rgba(231, 76, 60, 0.2) !important;
        }
    </style>
</head>
<body class="app-container">
    <header class="app-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo-container text-decoration-none">
                    <span class="logo-badge">
                        <img src="admin/images/<?= htmlspecialchars($profile['foto'] ?? 'default-logo.png') ?>"
                             alt="Company Logo" class="company-logo">
                    </span>
                    <div class="company-title">
                        <div class="header-chip mb-2">
                            <i class="bi bi-stars"></i>
                            Layanan Publik Digital
                        </div>
                        <h1 class="mb-0"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Perusahaan') ?></h1>
                    </div>
                </a>

                <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                    <div class="clock-info d-none d-md-block">
                        <div class="date-display"><?= tglIndonesia(date('D, d F Y', $currentDay)) ?></div>
                        <div id="clock" class="clock-display">
                            <i class="bi bi-clock"></i>
                            <span class="time"></span>
                        </div>
                    </div>

                    <div class="menu-actions">
                        <?php if ($currentPage === "spk"): ?>
                            <a href="index.php" class="btn btn-outline-dynamic">
                                <i class="bi bi-person-plus-fill"></i>
                                <span class="d-none d-sm-inline">Register Tamu</span>
                            </a>
                        <?php else: ?>
                            <a href="?page=spk" class="btn btn-gradient">
                                <i class="bi bi-star-fill"></i>
                                <span class="d-none d-sm-inline">Indeks Kepuasan</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
             <div class="clock-info d-block d-md-none mt-3">
                <div class="date-display"><?= tglIndonesia(date('D, d F Y', $currentDay)) ?></div>
                <div id="clock-mobile" class="clock-display justify-content-center">
                    <i class="bi bi-clock"></i>
                    <span class="time-mobile"></span>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <section class="welcome-section animate__animated animate__fadeInLeft">
                    <span class="welcome-orb orb-1"></span>
                    <span class="welcome-orb orb-2"></span>
                    <span class="welcome-orb orb-3"></span>
                    <div class="welcome-content">
                        <span class="welcome-hero align-self-start mb-3">
                            <i class="bi bi-stars"></i>
                            Selamat Datang!
                        </span>
                          <h2 class="display-5 mb-3">Buku Tamu <?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Instansi Kami') ?></h2>
                          <p class="lead mb-4 opacity-90"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Instansi Kami') ?> senang menyambut Anda. Silakan isi data diri Anda pada formulir di samping untuk keperluan dokumentasi dan pelayanan yang lebih baik.</p>

                        <hr class="modern-divider">

                          <div class="company-info-welcome">
                               <h3 class="h4 mb-3"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Instansi Kami') ?></h3>
                              <?php
                              $info_umum_display = trim($profile['info_umum'] ?? '');
                              if ($info_umum_display === '') {
                                  $info_umum_display = 'Diskominfo adalah dinas pemerintah yang mengelola teknologi informasi, internet, dan penyebaran informasi kepada masyarakat. Tujuannya untuk mendukung layanan publik yang lebih mudah dan cepat.';
                              }
                              ?>
                              <p class="mb-4"><?= htmlspecialchars($info_umum_display) ?></p>
                              <div class="company-info-list">
                                  <?php if (!empty($profile['alamat'])): ?>
                                  <div class="info-item">
                                      <i class="bi bi-geo-alt-fill"></i>
                                      <span><?= htmlspecialchars($profile['alamat']) ?></span>
                                  </div>
                                  <?php endif; ?>
                                  <?php if (!empty($profile['telepon'])): ?>
                                  <div class="info-item">
                                      <i class="bi bi-telephone-fill"></i>
                                      <span><?= htmlspecialchars($profile['telepon']) ?></span>
                                  </div>
                                  <?php endif; ?>
                                  <?php if (!empty($profile['website'])): ?>
                                  <div class="info-item">
                                      <i class="bi bi-globe2"></i>
                                      <a class="text-decoration-none" href="<?= htmlspecialchars($profile['website']) ?>" target="_blank" rel="noopener">
                                          <?= htmlspecialchars($profile['website']) ?>
                                      </a>
                                  </div>
                                  <?php endif; ?>
                              </div>
                        </div>

                        <div class="mt-auto text-center">
                            <img src="admin/images/<?= htmlspecialchars($profile['foto2'] ?? 'default-image.png') ?>"
                                 alt="Welcome Illustration" class="img-fluid animate-float-enhanced" style="max-height: 200px; object-fit:contain;">
                        </div>
                    </div>
                </section>

                <section class="form-section animate__animated animate__fadeInRight">
                    <div class="section-header">
                        <h2 class="h3 mb-0">
                            <?= $currentPage === "spk" ? 'Formulir Kepuasan Layanan' : 'Registrasi Kunjungan Tamu' ?>
                        </h2>
                    </div>

                    <?php
                    // Pastikan file isi.php ada
                    $isiPath = __DIR__ . DIRECTORY_SEPARATOR . "isi.php";
                    if (file_exists($isiPath)) {
                        include $isiPath;
                    } else {
                        echo '<div class="alert alert-warning" role="alert">Konten formulir (isi.php) tidak ditemukan.</div>';
                    }
                    ?>
                </section>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="container">
            <div class="footer-inner">
                <div class="footer-brand">
                    <img src="admin/images/<?= htmlspecialchars($profile['foto'] ?? 'default-logo.png') ?>" alt="Logo" class="footer-logo">
                    <div>
                        <div class="footer-title"><?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Buku Tamu Digital') ?></div>
                        <div class="footer-subtitle">Buku Tamu Digital • Layanan Publik Kampus</div>
                    </div>
                </div>
                <div class="footer-actions">
                    <span class="footer-pill"><i class="bi bi-shield-check"></i> Data Aman</span>
                    <span class="footer-pill"><i class="bi bi-clock-history"></i> Respons Cepat</span>
                    <span class="footer-pill"><i class="bi bi-people-fill"></i> Pelayanan Ramah</span>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; 2026 Pusat Sarana Informasi dan Komunikasi IPMAFA</span>
                <span>Powered by <a href="https://www.ipmafa.ac.id/" target="_blank" rel="noopener noreferrer">PSIK IPMAFA</a></span>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inisialisasi RGB color untuk CSS variable (digunakan di box-shadow focus)
        function hexToRgb(hex) {
            let r = 0, g = 0, b = 0;
            // 3 digits
            if (hex.length == 4) {
                r = "0x" + hex[1] + hex[1];
                g = "0x" + hex[2] + hex[2];
                b = "0x" + hex[3] + hex[3];
            // 6 digits
            } else if (hex.length == 7) {
                r = "0x" + hex[1] + hex[2];
                g = "0x" + hex[3] + hex[4];
                b = "0x" + hex[5] + hex[6];
            }
            return `${+r},${+g},${+b}`;
        }
        const primaryColorHex = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        document.documentElement.style.setProperty('--rgb-primary-color', hexToRgb(primaryColorHex));


        // Enhanced Digital Clock
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;

            const clockElement = document.querySelector('#clock .time');
            if (clockElement) clockElement.textContent = timeString;

            const clockMobileElement = document.querySelector('#clock-mobile .time-mobile');
            if (clockMobileElement) clockMobileElement.textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        $(document).ready(function() {
            // Initialize Select2 with modern styling
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih salah satu...',
                    // dropdownParent: $('.form-section') // Sesuaikan jika select2 ada di dalam modal atau elemen lain
                });
            }

            // Chain select functionality (jika masih digunakan)
            // Pastikan jquery.chained.js sudah dimuat dan elemennya ada
            if (typeof $.fn.chained !== 'undefined' && $("#pegawai").length && $("#unit_kerja").length) {
                $("#pegawai").chained("#unit_kerja");
            } else if ($("#pegawai").length && $("#unit_kerja").length) {
                console.warn("jquery.chained.js tidak dimuat atau elemen select tidak ditemukan.");
            }

            // SweetAlert untuk notifikasi (contoh)
            // Anda bisa trigger ini dari PHP setelah form submission
            // SweetAlert yang Lebih Keren
            <?php if (isset($_SESSION['sukses'])): ?>
                Swal.fire({
                    title: 'Berhasil Disimpan!',
                    text: '<?= htmlspecialchars($_SESSION['sukses']) ?>',
                    icon: 'success',
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    background: '#fff',
                    backdrop: `rgba(0,0,123,0.4) left top no-repeat`,
                    showClass: {
                        popup: 'animate__animated animate__zoomInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut'
                    }
                });
                <?php unset($_SESSION['sukses']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['gagal'])): ?>
                Swal.fire({
                    title: 'Gagal Menyimpan!',
                    text: '<?= htmlspecialchars($_SESSION['gagal']) ?>',
                    icon: 'error',
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#d33',
                    showClass: {
                        popup: 'animate__animated animate__shakeX'
                    }
                });
                <?php unset($_SESSION['gagal']); ?>
            <?php endif; ?>

            // Loading State saat Submit Form
            $('form').on('submit', function() {
                if(this.checkValidity()) {
                    Swal.fire({
                        title: 'Sedang Memproses...',
                        html: 'Mohon tunggu sebentar, data Anda sedang disimpan.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                }
            });

            // Tooltip Bootstrap (jika ada elemen dengan data-bs-toggle="tooltip")
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        });

        // Efek smooth scroll untuk anchor link jika ada
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.getAttribute('href').length > 1 && document.querySelector(this.getAttribute('href'))) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

    </script>
</body>
</html>
