<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA DATA ANGGARAN DESA
// FILE INI DIRANCANG UNTUK BERFUNGSI SEBAGAI HALAMAN MANDIRI
// DI DALAM STRUKTUR DASHBOARD ADMIN/PERANGKAT DESA
// ==========================================================

// Memulai sesi untuk memeriksa status login
session_start();

// Periksa apakah pengguna sudah login dan memiliki peran yang diizinkan (admin atau perangkat_desa).
// Jika tidak, arahkan ke halaman login atau dashboard penduduk.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || 
    ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'perangkat_desa')) {
    
    // Jika pengguna sudah login tapi bukan admin/perangkat_desa, arahkan ke dashboard penduduk
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard_penduduk.php"); // Atau ke halaman error/akses ditolak
    } else {
        // Jika belum login sama sekali, arahkan ke halaman login admin
        header("Location: login.php");
    }
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
// LOGIKA UNTUK MENAMBAH, MENGEDIT, DAN MENGHAPUS DATA ANGGARAN
// ==========================================================
$message = '';

// Logika Tambah/Edit Anggaran (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = clean_input($_POST['action']);
    $bidang = clean_input($_POST['bidang']);
    $rencana_anggaran = (int)str_replace(['Rp', '.', ' '], '', clean_input($_POST['rencana_anggaran'])); // Bersihkan format Rupiah
    $realisasi_anggaran = (int)str_replace(['Rp', '.', ' '], '', clean_input($_POST['realisasi_anggaran'])); // Bersihkan format Rupiah

    if ($action == 'add') {
        $stmt = $conn->prepare("INSERT INTO anggaran (bidang, rencana_anggaran, realisasi_anggaran) VALUES (?, ?, ?)");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query ADD: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("sii", $bidang, $rencana_anggaran, $realisasi_anggaran);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data anggaran berhasil ditambahkan.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menambahkan data anggaran: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } elseif ($action == 'edit') {
        $id_anggaran = clean_input($_POST['id_anggaran']);
        $stmt = $conn->prepare("UPDATE anggaran SET bidang = ?, rencana_anggaran = ?, realisasi_anggaran = ? WHERE id = ?");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query EDIT: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("siii", $bidang, $rencana_anggaran, $realisasi_anggaran, $id_anggaran);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data anggaran berhasil diperbarui.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal memperbarui data anggaran: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Logika Hapus Anggaran (GET Request)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_anggaran = clean_input($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM anggaran WHERE id = ?");
    if ($stmt === false) {
        $message = "<div class='alert alert-danger'>Gagal menyiapkan query DELETE: " . $conn->error . "</div>";
    } else {
        $stmt->bind_param("i", $id_anggaran);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Data anggaran berhasil dihapus.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menghapus data anggaran: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    // Redirect untuk menghindari resubmission form pada refresh
    header("Location: manage_anggaran.php");
    exit;
}

// ==========================================================
// MENGAMBIL SEMUA DATA ANGGARAN DARI DATABASE
// ==========================================================
$anggaran_list = [];
$sql = "SELECT id, bidang, rencana_anggaran, realisasi_anggaran FROM anggaran ORDER BY bidang ASC";
$result = $conn->query($sql);
if ($result === FALSE) {
    $message = "<div class='alert alert-danger'>Error saat menjalankan kueri SELECT: " . $conn->error . "</div>";
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $anggaran_list[] = $row;
        }
    }
}
$conn->close();

// Ambil data pengguna dari sesi untuk sidebar
$user_display_name = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Admin/Petugas';

// Dapatkan nama file halaman saat ini untuk penyorotan menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Anggaran</title>
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
                <a href="dashboard.php" class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_berita.php" class="nav-link <?php if ($current_page == 'manage_berita.php') echo 'active'; ?>">
                    <i class="fas fa-newspaper me-2"></i> Berita & Artikel
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_layanan.php" class="nav-link <?php if ($current_page == 'manage_layanan.php') echo 'active'; ?>">
                    <i class="fas fa-file-alt me-2"></i> Permintaan Layanan
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_penduduk.php" class="nav-link <?php if ($current_page == 'manage_penduduk.php') echo 'active'; ?>">
                    <i class="fas fa-users me-2"></i> Data Penduduk
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_perangkat_desa.php" class="nav-link <?php if ($current_page == 'manage_perangkat_desa.php') echo 'active'; ?>">
                    <i class="fas fa-user-tie me-2"></i> Perangkat Desa
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_anggaran.php" class="nav-link <?php if ($current_page == 'manage_anggaran.php') echo 'active'; ?>">
                    <i class="fas fa-money-bill-wave me-2"></i> Anggaran
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_galeri.php" class="nav-link <?php if ($current_page == 'manage_galeri.php') echo 'active'; ?>">
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
            <h1 class="h3 fw-bold">Manajemen Data Anggaran</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo $user_display_name; ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?= $message ?>

        <!-- Tombol Tambah Anggaran -->
        <div class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAnggaranModal">
                <i class="fas fa-plus-circle me-2"></i> Tambah Data Anggaran
            </button>
        </div>

        <!-- Tabel Daftar Anggaran -->
        <div class="table-responsive card p-4">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Bidang</th>
                        <th scope="col">Rencana Anggaran</th>
                        <th scope="col">Realisasi Anggaran</th>
                        <th scope="col">Persentase Realisasi</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($anggaran_list) > 0): ?>
                        <?php $i = 1; foreach ($anggaran_list as $anggaran): ?>
                            <tr>
                                <th scope="row"><?= $i++; ?></th>
                                <td><?= htmlspecialchars($anggaran['bidang']) ?></td>
                                <td>Rp <?= number_format($anggaran['rencana_anggaran'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($anggaran['realisasi_anggaran'], 0, ',', '.') ?></td>
                                <td>
                                    <?php
                                        $persentase = ($anggaran['rencana_anggaran'] > 0) ? 
                                                      round(($anggaran['realisasi_anggaran'] / $anggaran['rencana_anggaran']) * 100) : 0;
                                        echo $persentase . '%';
                                    ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editAnggaranModal"
                                                data-id="<?= htmlspecialchars($anggaran['id']) ?>"
                                                data-bidang="<?= htmlspecialchars($anggaran['bidang']) ?>"
                                                data-rencana_anggaran="<?= htmlspecialchars($anggaran['rencana_anggaran']) ?>"
                                                data-realisasi_anggaran="<?= htmlspecialchars($anggaran['realisasi_anggaran']) ?>">
                                            Edit
                                        </button>
                                        <a href="manage_anggaran.php?action=delete&id=<?= htmlspecialchars($anggaran['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data anggaran ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data anggaran yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Anggaran -->
    <div class="modal fade" id="addAnggaranModal" tabindex="-1" aria-labelledby="addAnggaranModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addAnggaranModalLabel">Tambah Data Anggaran Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_anggaran.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="add_bidang" class="form-label">Bidang Anggaran</label>
                            <input type="text" class="form-control" id="add_bidang" name="bidang" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_rencana_anggaran" class="form-label">Rencana Anggaran (Rp)</label>
                            <input type="number" class="form-control" id="add_rencana_anggaran" name="rencana_anggaran" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="add_realisasi_anggaran" class="form-label">Realisasi Anggaran (Rp)</label>
                            <input type="number" class="form-control" id="add_realisasi_anggaran" name="realisasi_anggaran" required min="0">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Anggaran -->
    <div class="modal fade" id="editAnggaranModal" tabindex="-1" aria-labelledby="editAnggaranModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editAnggaranModalLabel">Edit Data Anggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_anggaran.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_anggaran" id="edit_id_anggaran">
                        <div class="mb-3">
                            <label for="edit_bidang" class="form-label">Bidang Anggaran</label>
                            <input type="text" class="form-control" id="edit_bidang" name="bidang" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_rencana_anggaran" class="form-label">Rencana Anggaran (Rp)</label>
                            <input type="number" class="form-control" id="edit_rencana_anggaran" name="rencana_anggaran" required min="0">
                        </div>
                        <div class="mb-3">
                            <label for="edit_realisasi_anggaran" class="form-label">Realisasi Anggaran (Rp)</label>
                            <input type="number" class="form-control" id="edit_realisasi_anggaran" name="realisasi_anggaran" required min="0">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Perbarui Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // JavaScript untuk mengisi data ke modal edit
        var editAnggaranModal = document.getElementById('editAnggaranModal');
        editAnggaranModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Tombol yang memicu modal
            
            // Ambil data dari atribut data-*
            var id_anggaran = button.getAttribute('data-id');
            var bidang = button.getAttribute('data-bidang');
            var rencana_anggaran = button.getAttribute('data-rencana_anggaran');
            var realisasi_anggaran = button.getAttribute('data-realisasi_anggaran');

            // Isi data ke dalam field modal
            var modalIdAnggaran = editAnggaranModal.querySelector('#edit_id_anggaran');
            var modalBidang = editAnggaranModal.querySelector('#edit_bidang');
            var modalRencanaAnggaran = editAnggaranModal.querySelector('#edit_rencana_anggaran');
            var modalRealisasiAnggaran = editAnggaranModal.querySelector('#edit_realisasi_anggaran');

            modalIdAnggaran.value = id_anggaran;
            modalBidang.value = bidang;
            modalRencanaAnggaran.value = rencana_anggaran;
            modalRealisasiAnggaran.value = realisasi_anggaran;
        });
    </script>
</body>
</html>
