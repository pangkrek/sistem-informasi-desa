<?php
// Script untuk proses logout

// 1. Memulai sesi (session)
// Ini harus dipanggil di awal setiap halaman yang menggunakan sesi
session_start();

// 2. Menghapus semua variabel sesi
// Dengan mengosongkan array $_SESSION, kita menghapus semua data yang tersimpan
$_SESSION = array();

// 3. Menghancurkan sesi
// Fungsi ini secara permanen menghapus sesi dari server
session_destroy();

// 4. Mengarahkan kembali pengguna ke halaman login
// 'login.php' adalah halaman yang akan dituju setelah logout berhasil
header("Location: login.php");
exit; // Keluar dari script agar tidak ada kode lain yang dijalankan
?>