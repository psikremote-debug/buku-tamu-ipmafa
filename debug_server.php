<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Server Start</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

echo "<h2>Step 1: Check Local File</h2>";
$koneksiPath = __DIR__ . "/koneksi/koneksi.php";

if (!file_exists($koneksiPath)) {
    die("<span style='color:red'>Critical Error: File koneksi.php NOT FOUND at $koneksiPath</span>");
}
echo "<span style='color:green'>File koneksi.php ditemukan.</span><br>";

echo "<h2>Step 2: Try Include Koneksi</h2>";
try {
    require_once $koneksiPath;
    echo "<span style='color:green'>Include Berhasil (Sintaks Aman).</span><br>";
} catch (Throwable $t) {
    echo "<span style='color:red'>Captured Error: " . $t->getMessage() . "</span><br>";
    echo "<pre>" . $t->getTraceAsString() . "</pre>";
}

echo "<h2>Step 3: Database Connection Status</h2>";
if (isset($koneksi) && $koneksi instanceof mysqli) {
    echo "<span style='color:green'>BERHASIL TERHUBUNG!</span><br>";
    echo "Host info: " . $koneksi->host_info;
} else {
    echo "<span style='color:red'>GAGAL! Variabel \$koneksi tidak tersedia atau invalid.</span>";
}
?>
