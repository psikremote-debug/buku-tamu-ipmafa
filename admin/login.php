<?php
session_start(); // Mulai session di awal

// Jika admin sudah login, redirect ke dashboard admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

// Memanggil file koneksi dari parent directory
// Pastikan path ini benar sesuai struktur folder Anda
require_once __DIR__ . '/../koneksi/koneksi.php';

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        $login_error = "Username dan Password wajib diisi.";
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]); // Password plain text dari form

        $sql = "SELECT id_admin, username, password_hash, nama_lengkap, role FROM tb_admin WHERE username = ?";

        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) { // Username ditemukan
                    $stmt->bind_result($id_admin, $db_username, $db_password_hash, $nama_lengkap, $role);
                    if ($stmt->fetch()) {
                        // Verifikasi password plain text dengan hash dari database
                        if (password_verify($password, $db_password_hash)) {
                            // Password benar, mulai session baru
                            session_regenerate_id(true); // Mencegah pembajakan session ID lama
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $id_admin;
                            $_SESSION['admin_username'] = $db_username;
                            $_SESSION['admin_nama_lengkap'] = $nama_lengkap;
                            $_SESSION['admin_role'] = $role;

                            // Update last_login (opsional)
                            $update_sql = "UPDATE tb_admin SET last_login = CURRENT_TIMESTAMP WHERE id_admin = ?";
                            if ($update_stmt = $koneksi->prepare($update_sql)) {
                                $update_stmt->bind_param("i", $id_admin);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }

                            header("Location: index.php"); // Redirect ke dashboard admin
                            exit;
                        } else {
                            $login_error = "Password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    $login_error = "Username tidak ditemukan.";
                }
            } else {
                $login_error = "Oops! Terjadi kesalahan saat eksekusi. Silakan coba lagi nanti.";
                error_log("Admin login execute error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $login_error = "Oops! Gagal menyiapkan statement. Silakan coba lagi nanti.";
            error_log("Admin login prepare error: " . $koneksi->error);
        }
    }
    // $koneksi->close(); // Sebaiknya tidak ditutup di sini jika halaman masih membutuhkan koneksi setelahnya,
                       // tapi karena ini hanya untuk login, bisa saja ditutup. PHP akan menutup otomatis di akhir skrip.
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Buku Tamu Digital</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            --body-bg: #F5F7FA;
            --card-bg: #ffffff;
            --text-color: #495057;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            /* Soft gradient pattern */
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 20px 20px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-card {
            background: var(--card-bg);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08); /* Soft diffuse shadow */
            padding: 3rem;
            width: 90%; /* Mobile friendly */
            max-width: 450px;
            transition: transform 0.3s ease;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 1.5rem;
                width: 92%;
            }
        }

        .login-card:hover {
            transform: translateY(-5px); /* Subtle lift */
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-gradient);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.5rem;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #6c757d;
        }

        .form-control {
            border: 1px solid #ced4da;
            border-left: none;
            border-radius: 0 10px 10px 0;
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            font-size: 0.95rem;
            background-color: #f8f9fa;
            transition: all 0.2s;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #4e73df;
            box-shadow: none; /* No default glow, custom border color */
        }
        
        .input-group:focus-within .input-group-text {
            background-color: #fff;
            border-color: #4e73df;
        }

        .btn-primary-gradient {
            background: var(--primary-gradient);
            border: none;
            border-radius: 10px;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            color: white;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.2s;
        }

        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
            color: white;
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #4e73df;
        }
        
        .alert-soft {
            border: none;
            border-radius: 10px;
        }
        .alert-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }

    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-logo shadow-sm">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Admin Login</h4>
            <p class="text-muted small">Masuk untuk mengelola Buku Tamu Digital</p>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="alert alert-soft-danger d-flex align-items-center mb-4 p-3" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                <div class="small fw-medium">
                    <?php echo htmlspecialchars($login_error); ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person text-secondary"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username admin" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key text-secondary"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password Anda" required>
                </div>
            </div>
            
            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary-gradient">
                    Masuk Sekarang <i class="bi bi-arrow-right-short"></i>
                </button>
            </div>
        </form>
        
        <div class="text-center border-top pt-3">
            <a href="../index.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Kembali ke Halaman Utama
            </a>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>