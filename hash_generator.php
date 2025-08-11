<?php
// Script ini digunakan untuk membuat hash dari kata sandi
// Jalankan file ini melalui browser, misalnya: http://localhost/sistem_desa/hash_generator.php

// Kata sandi yang ingin Anda gunakan
$password_to_hash = "123456"; // Ganti dengan kata sandi yang Anda inginkan

// Membuat hash menggunakan algoritma yang aman
$hashed_password = password_hash($password_to_hash, PASSWORD_DEFAULT);

// Menampilkan hasil hash
echo "Kata Sandi Asli: " . htmlspecialchars($password_to_hash) . "<br>";
echo "Hash Kata Sandi: <strong>" . htmlspecialchars($hashed_password) . "</strong><br><br>";
echo "Salin hash di atas dan paste-kan ke database Anda.";
?>
