<?php
/**
 * File Koneksi Database
 */
/**
 * Load Environment Variables
 */
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$namaHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$namaPengguna = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root');
$kataSandi = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '');
$namaDatabase = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'db_bukutamu_digitalv2');

try {
    // Mengaktifkan mode exception untuk mysqli agar error bisa ditangkap try-catch
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $koneksi = new mysqli($namaHost, $namaPengguna, $kataSandi, $namaDatabase);
    
    if (!$koneksi->set_charset("utf8mb4")) {
        throw new Exception("Gagal mengatur charset utf8mb4: " . $koneksi->error);
    }
} catch (mysqli_sql_exception $e) {
    error_log("Koneksi Database Gagal: " . $e->getMessage());
    // Tampilkan pesan error yang jelas untuk debugging awal
    die("<h1>Gagal Terhubung ke Database</h1><p>Pastikan konfigurasi di file koneksi.php atau .env sudah benar.</p><p>Detail Error: " . htmlspecialchars($e->getMessage()) . "</p>");
} catch (Exception $e) {
    error_log("Error Umum: " . $e->getMessage());
    die("<h1>Terjadi Kesalahan Sistem</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
