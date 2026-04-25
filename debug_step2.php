<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Step 2</h1>";

echo "<h2>1. Testing Session Start</h2>";
try {
    session_start();
    echo "<span style='color:green'>Session Started. ID: " . session_id() . "</span><br>";
} catch (Throwable $t) {
    die("<span style='color:red'>Session Error: " . $t->getMessage() . "</span>");
}

echo "<h2>2. Including Koneksi</h2>";
require_once __DIR__ . '/koneksi/koneksi.php';
echo "<span style='color:green'>Koneksi Loaded.</span><br>";

echo "<h2>3. Including Proses Form</h2>";
try {
    require_once __DIR__ . '/proses_form.php';
    echo "<span style='color:green'>Proses Form Loaded (Syntax OK).</span><br>";
} catch (Throwable $t) {
    die("<span style='color:red'>Proses Form Error: " . $t->getMessage() . "</span><pre>" . $t->getTraceAsString() . "</pre>");
}

echo "<h2>4. Testing Environment Variables</h2>";
echo "Profile Data Check: <br>";
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $result = $koneksi->query("SELECT * FROM tb_profile LIMIT 1");
    if ($result) {
        $data = $result->fetch_assoc();
        echo "<span style='color:green'>Data Profile: " . htmlspecialchars($data['nama_perusahaan'] ?? 'N/A') . "</span>";
    } else {
        echo "<span style='color:orange'>Query Failed: " . $koneksi->error . "</span>";
    }
}

echo "<h3>DONE. If you see this, core logic is safe. Problem might be in HTML/View.</h3>";
?>
