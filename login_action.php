<?php
session_start();

// Ganti detail koneksi di bawah ini dengan kredensial database Anda
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "sistem_desa"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Pastikan data NIK dan PIN dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nik']) && isset($_POST['pin'])) {
    
    // Ambil dan bersihkan data input dari form
    $nik = htmlspecialchars(trim($_POST['nik']));
    $pin = htmlspecialchars(trim($_POST['pin']));

    // Gunakan prepared statement untuk mencegah SQL injection
    // CATATAN PENTING: Jika ada error, pastikan nama kolom 'nama'
    // atau 'nama_lengkap' sesuai dengan nama kolom di tabel 'penduduk' database Anda.
    $stmt = $conn->prepare("SELECT nik, nama_lengkap FROM penduduk WHERE nik = ? AND pin = ?");
    
    if ($stmt === false) {
        // Jika prepare() gagal, tampilkan pesan error database yang spesifik
        die("Kesalahan saat menyiapkan query: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $nik, $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Login berhasil
        $user_data = $result->fetch_assoc();
        
        // Simpan data pengguna ke dalam session
        // Perbarui nama session jika nama kolom di database juga berbeda
        $_SESSION['nik'] = $user_data['nik'];
        $_SESSION['nama'] = $user_data['nama_lengkap']; // Menggunakan nama_lengkap
        
        // Arahkan ke dashboard_penduduk.php
        header("Location: dashboard_penduduk.php");
        exit();
    } else {
        // Login gagal
        // Arahkan kembali ke halaman utama dengan pesan error
        header("Location: index.php?error=login_failed");
        exit();
    }

    $stmt->close();
} else {
    // Jika tidak ada data POST, arahkan kembali ke halaman utama
    header("Location: index.php");
    exit();
}

$conn->close();
?>
