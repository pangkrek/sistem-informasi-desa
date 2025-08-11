<?php
// Konfigurasi koneksi database
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = "";     // Ganti dengan password database Anda
$dbname = "sistem_desa"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
  http_response_code(500);
  die(json_encode(["error" => "Koneksi database gagal: " . $conn->connect_error]));
}

// Set header untuk mengembalikan JSON
header('Content-Type: application/json');

// Ambil data anggaran dari database
$sql = "SELECT * FROM anggaran ORDER BY tahun DESC, jenis ASC, bidang ASC";
$result = $conn->query($sql);

$data = [
    'rencana' => [],
    'realisasi' => []
];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['jenis'] === 'rencana') {
            $data['rencana'][] = $row;
        } else if ($row['jenis'] === 'realisasi') {
            $data['realisasi'][] = $row;
        }
    }
}

// Kembalikan data dalam format JSON
echo json_encode($data);

$conn->close();
?>
