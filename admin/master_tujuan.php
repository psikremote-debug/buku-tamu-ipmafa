<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';
require_once __DIR__ . '/../koneksi/csrf.php';

// Cek autentiaksi
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Master Data: Tujuan (Bertemu Siapa)";
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

$csrf_token = csrf_generate_token('master_tujuan');

// Handle aksi tambah, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'master_tujuan')) {
        $_SESSION['message'] = "Token keamanan tidak valid.";
        $_SESSION['message_type'] = "danger";
        header("Location: master_tujuan.php");
        exit;
    }

    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $nama_tujuan = htmlspecialchars(trim($_POST['nama_tujuan'] ?? ''));
        $keterangan = htmlspecialchars(trim($_POST['keterangan'] ?? ''));
        
        if (!empty($nama_tujuan)) {
            $stmt = $koneksi->prepare("INSERT INTO tb_tujuan (nama_tujuan, keterangan) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_tujuan, $keterangan);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Data tujuan berhasil ditambahkan.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal menambah data.";
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Nama tujuan wajib diisi.";
            $_SESSION['message_type'] = "danger";
        }
    } elseif ($action === 'edit') {
        $id_tujuan = filter_var($_POST['id_tujuan'], FILTER_VALIDATE_INT);
        $nama_tujuan = htmlspecialchars(trim($_POST['nama_tujuan'] ?? ''));
        $keterangan = htmlspecialchars(trim($_POST['keterangan'] ?? ''));
        
        if ($id_tujuan && !empty($nama_tujuan)) {
            $stmt = $koneksi->prepare("UPDATE tb_tujuan SET nama_tujuan = ?, keterangan = ? WHERE id_tujuan = ?");
            $stmt->bind_param("ssi", $nama_tujuan, $keterangan, $id_tujuan);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Data tujuan berhasil diubah.";
                $_SESSION['message_type'] = "success";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id_tujuan = filter_var($_POST['id_tujuan'], FILTER_VALIDATE_INT);
        if ($id_tujuan) {
            $stmt = $koneksi->prepare("DELETE FROM tb_tujuan WHERE id_tujuan = ?");
            $stmt->bind_param("i", $id_tujuan);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Data tujuan berhasil dihapus.";
                $_SESSION['message_type'] = "success";
            }
            $stmt->close();
        }
    }
    
    header("Location: master_tujuan.php");
    exit;
}

// Fetch Data
$tujuan_list = [];
$res = $koneksi->query("SELECT * FROM tb_tujuan ORDER BY nama_tujuan ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $tujuan_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Buku Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
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
                    <p class="text-muted mb-0">Kelola daftar opsi untuk isian "Bertemu Dengan" di form pengunjung.</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Tujuan Baru
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Tujuan</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($tujuan_list)): ?>
                                    <tr><td colspan="4" class="text-center">Belum ada data.</td></tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($tujuan_list as $row): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['nama_tujuan']); ?></td>
                                        <td><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                                        <td class="text-center">
                                            <!-- Edit Button -->
                                            <button type="button" class="btn btn-sm btn-outline-warning me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEdit<?php echo $row['id_tujuan']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <!-- Delete Button -->
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                <input type="hidden" name="id_tujuan" value="<?php echo $row['id_tujuan']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                            <!-- Modal Edit -->
                                            <div class="modal fade" id="modalEdit<?php echo $row['id_tujuan']; ?>" tabindex="-1" aria-hidden="true" style="text-align: left;">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Tujuan Mengubah</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="action" value="edit">
                                                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                                <input type="hidden" name="id_tujuan" value="<?php echo $row['id_tujuan']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Nama Tujuan</label>
                                                                    <input type="text" name="nama_tujuan" class="form-control" value="<?php echo htmlspecialchars($row['nama_tujuan']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Keterangan</label>
                                                                    <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($row['keterangan'] ?? ''); ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Tujuan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Tujuan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_tujuan" class="form-control" required placeholder="Contoh: Kepala Dinas / Keuangan">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Deskripsi divisi atau orang..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const adminSidebar = document.getElementById('adminSidebar');
        if (sidebarToggleBtn && adminSidebar) {
            sidebarToggleBtn.addEventListener('click', function() {
                adminSidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
