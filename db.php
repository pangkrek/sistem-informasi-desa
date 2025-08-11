<?php
// Konfigurasi koneksi ke database MySQL
$servername = "localhost"; // Nama server database, biasanya "localhost"
$username = "root";        // Nama pengguna database (user), ganti sesuai milik Anda
$password = "";            // Kata sandi database, ganti sesuai milik Anda
$dbname = "sistem_desa";   // Nama database yang telah kita buat

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Jika koneksi berhasil, tidak ada pesan yang akan ditampilkan.
// Anda bisa menambahkan pesan di bawah ini untuk tujuan debugging jika diperlukan:
// echo "Koneksi ke database berhasil.";
?>
