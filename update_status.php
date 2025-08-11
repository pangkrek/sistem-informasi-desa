<?php
session_start();

// ==========================================================
// PENGAMANAN HALAMAN ADMIN
// Periksa apakah pengguna sudah login dan memiliki peran 'admin'.
// ==========================================================
if (!isset($_SESSION['nik']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// ==========================================================
// KONEKSI KE DATABASE
// ==========================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistem_desa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Periksa apakah parameter 'id' dikirimkan melalui URL
if (isset($_GET['id'])) {
    $id_pengajuan = $_GET['id'];
    $status_baru = 'Selesai';
    
    // Gunakan prepared statement untuk memperbarui status
    $stmt = $conn->prepare("UPDATE pengajuan_surat SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status_baru, $id_pengajuan);
    
    if ($stmt->execute()) {
        // Jika berhasil, arahkan kembali ke dashboard admin dengan pesan sukses
        header("Location: manage_layanan.php?success=status_updated");
        exit();
    } else {
        // Jika gagal, arahkan kembali dengan pesan error
        header("Location: manage_layanan.php?error=update_failed");
        exit();
    }

    $stmt->close();
} else {
    // Jika 'id' tidak ada, arahkan kembali ke dashboard admin
    header("Location: manage_layanan.php");
    exit();
}

$conn->close();
?>
