<?php
session_start();

// Periksa apakah pengguna sudah login, jika tidak, arahkan kembali ke halaman login.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['nik'])) {
    header("Location: login.php");
    exit();
}

// Sertakan file koneksi database
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data NIK dari sesi, bukan dari POST
    $nik_pengaju = $_SESSION['nik'];

    // Ambil data jenis surat dari form
    $jenis_surat = isset($_POST['jenis_surat']) ? $_POST['jenis_surat'] : '';
    $tanggal_pengajuan = date("Y-m-d H:i:s");
    $status = "Diproses"; // Status awal pengajuan

    // Pastikan jenis_surat tidak kosong
    if (!empty($jenis_surat)) {
        // Gunakan prepared statement untuk memasukkan data dengan aman
        $sql = "INSERT INTO permintaan_surat (nik, jenis_surat, tanggal_pengajuan, status) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $nik_pengaju, $jenis_surat, $tanggal_pengajuan, $status);
            
            // Eksekusi query
            if ($stmt->execute()) {
                // Jika berhasil, arahkan kembali ke dashboard dengan pesan sukses
                header("Location: dashboard_penduduk.php?success=submitted");
                exit();
            } else {
                // Jika gagal, log error dan arahkan kembali dengan pesan error
                error_log("Error saat memasukkan data permintaan surat: " . $stmt->error);
                header("Location: dashboard_penduduk.php?error=db_error");
                exit();
            }
            $stmt->close();
        } else {
            // Jika persiapan statement gagal, log error
            error_log("Gagal menyiapkan statement: " . $conn->error);
            header("Location: dashboard_penduduk.php?error=prepare_error");
            exit();
        }
    } else {
        // Jika jenis surat tidak ada, arahkan kembali dengan pesan error
        header("Location: dashboard_penduduk.php?error=invalid_request");
        exit();
    }
} else {
    // Jika bukan metode POST, tolak permintaan
    header("Location: dashboard_penduduk.php");
    exit();
}

$conn->close();
?>
