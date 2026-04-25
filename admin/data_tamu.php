<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Sesuaikan path jika berbeda
require_once __DIR__ . '/../koneksi/csrf.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Data Kunjungan Tamu";
$message = $_SESSION['message'] ?? ''; // Ambil pesan dari session
$message_type = $_SESSION['message_type'] ?? ''; // Ambil tipe pesan
unset($_SESSION['message'], $_SESSION['message_type']); // Hapus pesan setelah ditampilkan

$csrf_delete_tamu_token = csrf_generate_token('delete_tamu');

// Handle Aksi Hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'delete_tamu')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }

    $id_tamu_to_delete = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id_tamu_to_delete) {
        $sql_delete = "DELETE FROM tb_tamu WHERE id_tamu = ?";
        if ($stmt_delete = $koneksi->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $id_tamu_to_delete);
            if ($stmt_delete->execute()) {
                $_SESSION['message'] = "Data tamu berhasil dihapus.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal menghapus data tamu: " . $stmt_delete->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt_delete->close();
        } else {
            $_SESSION['message'] = "Gagal menyiapkan statement hapus: " . $koneksi->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: data_tamu.php"); // Redirect untuk refresh dan menghilangkan parameter GET
        exit;
    } else {
        $_SESSION['message'] = "ID tamu tidak valid untuk dihapus.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }
}


// Variabel Filter
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

// Fetch semua data tamu sesuai filter
$tamu_list = [];
$sql_select_tamu = "SELECT id_tamu, tanggal_kunjungan, waktu_masuk, nama_tamu, asal_instansi, bertemu_dengan, keperluan, waktu_keluar 
                    FROM tb_tamu 
                    WHERE $where_clause 
                    ORDER BY tanggal_kunjungan DESC, waktu_masuk DESC";

if ($stmt_tamu = $koneksi->prepare($sql_select_tamu)) {
    if (!empty($params)) {
        $stmt_tamu->bind_param($types, ...$params);
    }
    $stmt_tamu->execute();
    $result_tamu = $stmt_tamu->get_result();
    if ($result_tamu && $result_tamu->num_rows > 0) {
        while ($row = $result_tamu->fetch_assoc()) {
            $tamu_list[] = $row;
        }
    }
    $stmt_tamu->close();
}

// $koneksi->close(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Buku Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
    <style>
        /* Table Styling */
        .table-responsive { margin-top: 0; }
        .table thead th {
            background-color: #f8f9fa; /* Light Gray Header */
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
            padding: 1rem 0.75rem;
        }
        .table tbody tr td {
            vertical-align: middle;
            color: #555;
            font-size: 0.95rem;
            padding: 1rem 0.75rem;
            border-bottom-color: #f0f2f5;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* DataTables Customization */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 6px;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 6px;
            padding: 0.375rem 0.75rem;
        }
        .page-item.active .page-link {
            background-color: #3498db;
            border-color: #3498db;
        }
        
        .action-buttons .btn { border-radius: 6px; padding: 0.4rem 0.6rem; }

        /* Soft Buttons */
        .btn-soft-primary { background-color: rgba(52, 152, 219, 0.1); color: #3498db; border: none; transition: all 0.2s; }
        .btn-soft-primary:hover { background-color: #3498db; color: #fff; }
        
        .btn-soft-warning { background-color: rgba(241, 196, 15, 0.1); color: #f1c40f; border: none; transition: all 0.2s; }
        .btn-soft-warning:hover { background-color: #f1c40f; color: #fff; }
        
        .btn-soft-danger { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; border: none; transition: all 0.2s; }
        .btn-soft-danger:hover { background-color: #e74c3c; color: #fff; }
        
        .action-buttons .btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin: 0 2px; }
    </style>
</head>
<body>
    <?php
    if (file_exists(__DIR__ . '/_partials/navbar.php')) { include_once __DIR__ . '/_partials/navbar.php'; }
    if (file_exists(__DIR__ . '/_partials/sidebar.php')) { include_once __DIR__ . '/_partials/sidebar.php'; }
    ?>

    <main class="main-content">
        <div class="container-fluid px-0">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-4 mb-4 border-bottom-0">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1"><?php echo htmlspecialchars($page_title); ?></h1>
                    <p class="text-muted mb-0">Kelola informasi dan riwayat kunjungan tamu.</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center bg-white shadow-sm border" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="bi bi-funnel me-2"></i> Filter Data
                    </button>
                    <a href="export_tamu_pdf.php?filter=<?php echo urlencode($filter_tipe); ?>&start=<?php echo urlencode($start_date); ?>&end=<?php echo urlencode($end_date); ?>" target="_blank" class="btn btn-danger text-white shadow-sm border-0 d-flex align-items-center" style="background: linear-gradient(135deg, #e74c3c, #c0392b); border-radius: 10px;">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i> Ekspor PDF
                    </a>
                    <a href="export_tamu.php?filter=<?php echo urlencode($filter_tipe); ?>&start=<?php echo urlencode($start_date); ?>&end=<?php echo urlencode($end_date); ?>" class="btn btn-primary text-white shadow-sm border-0 d-flex align-items-center" style="background: linear-gradient(135deg, #4e73df, #224abe); border-radius: 10px;">
                        <i class="bi bi-file-earmark-excel-fill me-2"></i> Ekspor ke Excel
                    </a>
                </div>
            </div>

            <!-- Form Filter Collapse -->
            <div class="collapse mb-4" id="filterCollapse">
                <div class="card card-body shadow-sm border-0 rounded-3">
                    <form method="GET" action="data_tamu.php" class="row g-3 align-items-end" id="formFilter">
                        <div class="col-md-3">
                            <label for="filter" class="form-label text-muted small fw-bold text-uppercase">Tampilkan Data</label>
                            <select class="form-select border-0 bg-light" id="filter" name="filter">
                                <option value="semua" <?php echo $filter_tipe === 'semua' ? 'selected' : ''; ?>>Semua Waktu</option>
                                <option value="hari_ini" <?php echo $filter_tipe === 'hari_ini' ? 'selected' : ''; ?>>Hari Ini</option>
                                <option value="minggu_ini" <?php echo $filter_tipe === 'minggu_ini' ? 'selected' : ''; ?>>Minggu Ini</option>
                                <option value="bulan_ini" <?php echo $filter_tipe === 'bulan_ini' ? 'selected' : ''; ?>>Bulan Ini</option>
                                <option value="tahun_ini" <?php echo $filter_tipe === 'tahun_ini' ? 'selected' : ''; ?>>Tahun Ini</option>
                                <option value="kustom" <?php echo $filter_tipe === 'kustom' ? 'selected' : ''; ?>>Pilih Tanggal Tertentu</option>
                            </select>
                        </div>
                        
                        <div class="col-md-5 d-none" id="kustomDateRange">
                            <label class="form-label text-muted small fw-bold text-uppercase">Rentang Tanggal</label>
                            <div class="input-group border-0 bg-transparent rounded-3 overflow-hidden">
                                <input type="date" class="form-control border-0 bg-light" name="start" value="<?php echo htmlspecialchars($start_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                                <span class="input-group-text bg-white border-0 text-muted">s/d</span>
                                <input type="date" class="form-control border-0 bg-light" name="end" value="<?php echo htmlspecialchars($end_date); ?>" max="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100 shadow-sm"><i class="bi bi-search me-1"></i> Terapkan</button>
                        </div>
                        <div class="col-md-2">
                            <a href="data_tamu.php" class="btn btn-light w-100 border text-muted"><i class="bi bi-arrow-clockwise me-1"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'info'; ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'info-circle'; ?>-fill fs-4 me-3"></i>
                    <div><?php echo htmlspecialchars($message); ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 pt-4 px-4">
                    <h5 class="mb-0 fw-bold text-secondary">Semua Tamu</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (!empty($tamu_list)): ?>
                    <div class="table-responsive">
                        <table id="tabelDataTamu" class="table table-hover align-middle w-100" style="border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr>
                                    <th class="border-top-0 rounded-start-2">No.</th>
                                    <th class="border-top-0">Tanggal</th>
                                    <th class="border-top-0">Waktu</th>
                                    <th class="border-top-0">Nama Tamu</th>
                                    <th class="border-top-0">Instansi</th>
                                    <th class="border-top-0">Bertemu</th>
                                    <th class="border-top-0">Keperluan</th>
                                    <th class="border-top-0 rounded-end-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $nomor = 1; foreach ($tamu_list as $tamu): ?>
                                <tr>
                                    <td class="ps-3"><?php echo $nomor++; ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium text-dark"><?php echo htmlspecialchars(date('d M Y', strtotime($tamu['tanggal_kunjungan']))); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars(date('l', strtotime($tamu['tanggal_kunjungan']))); ?></small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars(substr($tamu['waktu_masuk'], 0, 5)); ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, #667eea, #764ba2) !important;">
                                                <?php echo strtoupper(substr($tamu['nama_tamu'], 0, 1)); ?>
                                            </div>
                                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($tamu['nama_tamu']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($tamu['asal_instansi']); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['bertemu_dengan']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($tamu['keperluan'], 0, 25, "...")); ?></td>
                                    <td class="text-center action-buttons">
                                        <a href="detail_tamu.php?id=<?php echo $tamu['id_tamu']; ?>" class="btn btn-soft-primary" data-bs-toggle="tooltip" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit_tamu.php?id=<?php echo $tamu['id_tamu']; ?>" class="btn btn-soft-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-soft-danger" title="Hapus" 
                                                onclick="confirmDelete(<?php echo (int) $tamu['id_tamu']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        
                                        <!-- Hidden Form for Delete -->
                                        <form id="deleteForm-<?php echo (int) $tamu['id_tamu']; ?>" method="POST" class="d-none">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $tamu['id_tamu']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_delete_tamu_token, ENT_QUOTES, 'UTF-8'); ?>">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" alt="Empty Data" style="max-width: 200px; opacity: 0.8;">
                        <h6 class="mt-3 text-muted">Belum ada data kunjungan tamu.</h6>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#tabelDataTamu').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                    "search": "",
                    "searchPlaceholder": "Cari tamu..." 
                },
                "dom": '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "pageLength": 10,
                "createdRow": function( row, data, dataIndex ) {
                    $(row).addClass('align-middle');
                }
            });
            
            // Initialize Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data tamu ini? Data yang dihapus tidak dapat dikembalikan.')) {
                document.getElementById('deleteForm-' + id).submit();
            }
        }

        // Script untuk toggle sidebar di mobile
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const adminSidebar = document.getElementById('adminSidebar');
        if (sidebarToggleBtn && adminSidebar) {
            sidebarToggleBtn.addEventListener('click', function() {
                adminSidebar.classList.toggle('active');
            });
        }

        // Logic toggle form range date dropdown
        document.getElementById('filter').addEventListener('change', function() {
            var kustomRange = document.getElementById('kustomDateRange');
            if(this.value === 'kustom') {
                kustomRange.classList.remove('d-none');
            } else {
                kustomRange.classList.add('d-none');
            }
        });

        // Buka panel filter jika ada filter aktif di URL
        window.addEventListener('load', function() {
            if (new URLSearchParams(window.location.search).has('filter') && new URLSearchParams(window.location.search).get('filter') !== 'semua') {
                 var myCollapse = new bootstrap.Collapse(document.getElementById('filterCollapse'), {
                    toggle: false
                });
                myCollapse.show();
            }
        });
    </script>
</body>
</html>
