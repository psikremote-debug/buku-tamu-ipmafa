<?php
session_start();

require_once __DIR__ . '/../koneksi/koneksi.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit("Akses ditolak. Silakan login terlebih dahulu.");
}

// Variabel Filter dari URL
$filter_tipe = $_GET['filter'] ?? 'semua';
$start_date = $_GET['start'] ?? date('Y-m-d');
$end_date = $_GET['end'] ?? date('Y-m-d');

// Bangun kondisional query
$where_clause = "1=1";
$params = [];
$types = "";

if ($filter_tipe === 'hari_ini') {
    $where_clause = "DATE(tanggal_kunjungan) = CURDATE()";
} elseif ($filter_tipe === 'minggu_ini') {
    $where_clause = "YEARWEEK(tanggal_kunjungan, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter_tipe === 'bulan_ini') {
    $where_clause = "MONTH(tanggal_kunjungan) = MONTH(CURDATE()) AND YEAR(tanggal_kunjungan) = YEAR(CURDATE())";
} elseif ($filter_tipe === 'tahun_ini') {
    $where_clause = "YEAR(tanggal_kunjungan) = YEAR(CURDATE())";
} elseif ($filter_tipe === 'kustom' && !empty($start_date) && !empty($end_date)) {
    $where_clause = "tanggal_kunjungan BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

// Ambil data tamu
$sql_export_tamu = "SELECT 
                        id_tamu, 
                        tanggal_kunjungan, 
                        waktu_masuk, 
                        nama_tamu, 
                        asal_instansi, 
                        jabatan, 
                        no_telepon, 
                        bertemu_dengan, 
                        keperluan, 
                        catatan_tambahan, 
                        status_keluar, 
                        waktu_keluar,
                        created_at
                    FROM tb_tamu 
                    WHERE $where_clause
                    ORDER BY tanggal_kunjungan DESC, waktu_masuk DESC";

$stmt_export = $koneksi->prepare($sql_export_tamu);
if ($stmt_export) {
    if (!empty($params)) {
        $stmt_export->bind_param($types, ...$params);
    }
    $stmt_export->execute();
    $result_export = $stmt_export->get_result();
} else {
    $result_export = false;
}

if ($result_export === false) {
    error_log("Gagal query ekspor data tamu (PDF): " . $koneksi->error);
    exit("Terjadi kesalahan saat mengambil data tamu.");
}

$export_time = date('d-m-Y H:i:s');
$rows = [];
while ($row = $result_export->fetch_assoc()) {
    $rows[] = $row;
}
$result_export->free();
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $koneksi->close();
}

// Menentukan judul header tabel dinamis sesuai tipe filter
$tanggal_text = '';
if ($filter_tipe === 'hari_ini') {
    $tanggal_text = ' - Hari Ini (' . date('d M Y') . ')';
} elseif ($filter_tipe === 'minggu_ini') {
    $tanggal_text = ' - Minggu Ini';
} elseif ($filter_tipe === 'bulan_ini') {
    $tanggal_text = ' - Bulan Ini (' . date('M Y') . ')';
} elseif ($filter_tipe === 'tahun_ini') {
    $tanggal_text = ' - Tahun Ini (' . date('Y') . ')';
} elseif ($filter_tipe === 'kustom') {
    $tanggal_text = ' - Periode (' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)) . ')';
}

$html = '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ekspor PDF - Data Tamu</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: "Poppins", Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .export-header {
            border-bottom: 2px solid #0E5CAD;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .table-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
            margin: 10px 0 6px;
        }
        .export-title {
            font-size: 18px;
            font-weight: 700;
            color: #0E5CAD;
        }
        .export-subtitle {
            font-size: 11px;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        thead th {
            background: #0E5CAD;
            color: #fff;
            padding: 6px;
            text-align: left;
            border: 1px solid #0E5CAD;
            font-weight: 600;
            white-space: nowrap;
        }
        tbody td {
            border: 1px solid #d7e3f2;
            padding: 6px;
            vertical-align: top;
        }
        tbody tr:nth-child(even) td {
            background: #f4f7fb;
        }
        .text-center { text-align: center; }
        .status-masuk { color: #198754; font-weight: 600; }
        .status-keluar { color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
    <div class="export-header">
        <div class="export-title">Data Kunjungan Tamu' . $tanggal_text . '</div>
        <div class="export-subtitle">Diekspor pada: ' . htmlspecialchars($export_time) . '</div>
    </div>
    <div class="table-title">Tabel Data Kunjungan Tamu' . $tanggal_text . '</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">No</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Tanggal</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Waktu</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Nama</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Instansi</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Jabatan</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Telepon</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Bertemu</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Keperluan</th>
                <th style="background:#0E5CAD;color:#ffffff;border:1px solid #0E5CAD;font-weight:600;padding:6px;text-align:left;white-space:nowrap;">Status</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
foreach ($rows as $row) {
    $tanggal = $row['tanggal_kunjungan'] ? date('d-m-Y', strtotime($row['tanggal_kunjungan'])) : '';
    $waktu = $row['waktu_masuk'] ? substr($row['waktu_masuk'], 0, 5) : '';
    $statusClass = ($row['status_keluar'] === 'Keluar') ? 'status-keluar' : 'status-masuk';

    $html .= '<tr>
        <td class="text-center">' . $no++ . '</td>
        <td>' . htmlspecialchars($tanggal) . '</td>
        <td class="text-center">' . htmlspecialchars($waktu) . '</td>
        <td>' . htmlspecialchars($row['nama_tamu']) . '</td>
        <td>' . htmlspecialchars($row['asal_instansi']) . '</td>
        <td>' . htmlspecialchars($row['jabatan']) . '</td>
        <td>' . htmlspecialchars($row['no_telepon']) . '</td>
        <td>' . htmlspecialchars($row['bertemu_dengan']) . '</td>
        <td>' . htmlspecialchars($row['keperluan']) . '</td>
        <td class="' . $statusClass . '">' . htmlspecialchars($row['status_keluar']) . '</td>
    </tr>';
}

$html .= '</tbody></table></body></html>';

try {
    $mpdf = new Mpdf([
        'format' => 'A4-L',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 12,
        'margin_bottom' => 12
    ]);
    $mpdf->WriteHTML($html);

    $filename = 'daftar_tamu_' . date('Ymd_His') . '.pdf';
    $mpdf->Output($filename, 'D');
} catch (\Throwable $e) {
    error_log("Gagal membuat PDF: " . $e->getMessage());
    exit("Gagal membuat file PDF. Silakan coba lagi.");
}
?>
