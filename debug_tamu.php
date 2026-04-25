<?php
// debug_tamu.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$koneksiPath = __DIR__ . '/koneksi/koneksi.php';
if (!file_exists($koneksiPath)) {
    die("File koneksi.php not found at $koneksiPath");
}
require $koneksiPath;

if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

echo "Database Connected Successfully.<br>";

// Check table structure
$tableName = 'tb_tamu';
$sql = "DESCRIBE $tableName";
$result = $koneksi->query($sql);

if ($result) {
    echo "<h2>Structure of table '$tableName':</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing table: " . $koneksi->error;
}

// Check recent inserts
echo "<h2>Recent Inserts (Last 5):</h2>";
$sql2 = "SELECT * FROM $tableName ORDER BY id_tamu DESC LIMIT 5"; // Assuming id_tamu is the PK
$result2 = $koneksi->query($sql2);

if ($result2) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    // header
    $fields = $result2->fetch_fields();
    echo "<tr>";
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";
    
    // data
    while ($row = $result2->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
} else {
     echo "Error selecting recent data: " . $koneksi->error; 
}
?>
