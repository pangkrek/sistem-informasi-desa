<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA PERMINTAAN SURAT
// FILE INI DIRANCANG UNTUK BERFUNGSI SEBAGAI HALAMAN MANDIRI
// DI DALAM STRUKTUR DASHBOARD
// ==========================================================

// Memulai sesi untuk memeriksa status login
session_start();

// Periksa apakah pengguna sudah login. Jika tidak, arahkan ke halaman login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Sertakan file koneksi database
require_once 'db.php';

// Fungsi untuk membersihkan input dari user
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ==========================================================
// LOGIKA UNTUK UPDATE STATUS ATAU HAPUS DATA
// ==========================================================
$message = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = clean_input($_GET['id']);
    
    // Pastikan koneksi masih terbuka sebelum melakukan operasi
    if ($conn) {
        if ($_GET['action'] == 'update_status' && isset($_GET['status'])) {
            $status = clean_input($_GET['status']);
            $stmt = $conn->prepare("UPDATE permintaan_surat SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Status permintaan berhasil diperbarui.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal memperbarui status: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } elseif ($_GET['action'] == 'delete') {
            $stmt = $conn->prepare("DELETE FROM permintaan_surat WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Permintaan berhasil dihapus.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menghapus permintaan: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }

    // Arahkan kembali ke halaman ini untuk menghilangkan parameter GET dari URL
    header("Location: manage_layanan.php");
    exit;
}

// ==========================================================
// MENGAMBIL SEMUA DATA PERMINTAAN SURAT DARI DATABASE
// ==========================================================
$permintaan_list = [];
if ($conn) {
    // Kueri asli yang sudah benar untuk mengambil semua data dari tabel permintaan_surat
    $sql = "SELECT * FROM permintaan_surat ORDER BY tanggal_pengajuan DESC";
    $result = $conn->query($sql);

    // Periksa apakah kueri berhasil dijalankan
    if ($result === FALSE) {
        $message = "<div class='alert alert-danger'>Error saat menjalankan kueri: " . $conn->error . "</div>";
    } else {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $permintaan_list[] = $row;
            }
        }
    }
    $conn->close();
}


// Ambil data pengguna dari sesi untuk sidebar
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengajuan Surat</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        #sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            transition: all 0.3s;
            z-index: 1000;
        }
        #sidebar .nav-link {
            color: #adb5bd;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 1rem 1.5rem;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
            border-left: 4px solid #0d6efd;
            border-radius: 0;
        }
        #main-content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .info-card {
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
            transition: transform 0.2s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .info-card .card-icon {
            font-size: 3rem;
            opacity: 0.3;
        }
        .table-responsive {
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        @media (max-width: 768px) {
            #sidebar {
                width: 0;
                overflow: hidden;
            }
            #main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column p-3">
        <a href="dashboard.php" class="navbar-brand text-light text-center mb-4">
            <i class="fas fa-tools me-2"></i>Admin Panel
        </a>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-2">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_berita.php" class="nav-link">
                    <i class="fas fa-newspaper me-2"></i> Berita & Artikel
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_layanan.php" class="nav-link active">
                    <i class="fas fa-file-alt me-2"></i> Permintaan Layanan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_penduduk.php" class="nav-link">
                    <i class="fas fa-users me-2"></i> Data Penduduk
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_perangkat_desa.php" class="nav-link">
                    <i class="fas fa-user-tie me-2"></i> Perangkat Desa
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_anggaran.php" class="nav-link">
                    <i class="fas fa-money-bill-wave me-2"></i> Anggaran
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_galeri.php" class="nav-link">
                    <i class="fas fa-images me-2"></i> Galeri Desa
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_umkm.php" class="nav-link <?php if ($current_page == 'manage_umkm.php') echo 'active'; ?>">
                    <i class="fas fa-store me-2"></i> UMKM
                </a>
            </li>
        </ul>
        <div class="mt-auto text-center py-3">
             <a href="logout.php" class="btn btn-outline-light rounded-pill px-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="main-content">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">Manajemen Pengajuan Surat</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?= $message ?>

        <!-- Tabel Daftar Permintaan Surat -->
        <div class="table-responsive card p-4">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama Pemohon</th>
                        <th scope="col">NIK</th>
                        <th scope="col">Jenis Surat</th>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($permintaan_list) > 0): ?>
                        <?php foreach ($permintaan_list as $permintaan): ?>
                            <tr>
                                <th scope="row"><?= htmlspecialchars($permintaan['id']) ?></th>
                                <td><?= htmlspecialchars($permintaan['nama_pemohon']) ?></td>
                                <td><?= htmlspecialchars($permintaan['nik']) ?></td>
                                <td><?= htmlspecialchars($permintaan['jenis_surat']) ?></td>
                                <td><?= date('d M Y', strtotime($permintaan['tanggal_pengajuan'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= ($permintaan['status'] == 'Selesai') ? 'success' : (($permintaan['status'] == 'Diproses') ? 'warning' : 'secondary') ?>">
                                        <?= htmlspecialchars($permintaan['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="manage_layanan.php?action=update_status&id=<?= htmlspecialchars($permintaan['id']) ?>&status=Diproses" class="btn btn-sm btn-warning">Proses</a>
                                        <a href="manage_layanan.php?action=update_status&id=<?= htmlspecialchars($permintaan['id']) ?>&status=Selesai" class="btn btn-sm btn-success">Selesai</a>
                                        <!-- Tombol Cetak Surat -->
                                        <a href="cetak_surat.php?id=<?= htmlspecialchars($permintaan['id']) ?>" class="btn btn-sm btn-info" target="_blank">Cetak Surat</a>
                                        <a href="manage_layanan.php?action=delete&id=<?= htmlspecialchars($permintaan['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus permintaan ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada permintaan surat yang masuk saat ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
