<?php
// ==========================================================
// KODE INI ADALAH FILE LOGIN UNTUK PENDUDUK
// MENGGUNAKAN NIK DAN PIN
// ==========================================================

// Memulai sesi untuk menyimpan status login
session_start();

// Sertakan file koneksi database
require_once 'db.php';

$error_message = '';

// Periksa jika formulir telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil input dari formulir dan bersihkan
    $nik = trim($_POST['nik']);
    $pin = trim($_POST['pin']);

    // Validasi input sederhana
    if (empty($nik) || empty($pin)) {
        $error_message = 'NIK dan PIN tidak boleh kosong.';
    } else {
        // Gunakan Prepared Statement untuk mencegah SQL Injection
        $stmt = $conn->prepare("SELECT id, nama FROM penduduk WHERE nik = ? AND pin = ?");
        $stmt->bind_param("ss", $nik, $pin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Login berhasil
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nik'] = $nik;
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_role'] = 'penduduk'; // Tetapkan peran sebagai penduduk

            // Redirect ke halaman dashboard penduduk
            header("Location: dashboard_penduduk.php");
            exit;
        } else {
            // Login gagal
            $error_message = 'NIK atau PIN salah.';
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Penduduk</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="login-container">
            <h2 class="text-center fw-bold mb-4">Login Penduduk</h2>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <form action="login_penduduk.php" method="POST">
                <div class="mb-3">
                    <label for="nik" class="form-label">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" class="form-control" id="nik" name="nik" required>
                </div>
                <div class="mb-3">
                    <label for="pin" class="form-label">PIN</label>
                    <input type="password" class="form-control" id="pin" name="pin" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
