<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA GALERI DESA
// Halaman ini memungkinkan administrator untuk:
// - Mengunggah gambar baru ke galeri
// - Melihat daftar gambar yang sudah ada
// - Menghapus gambar dari galeri
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
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

$message = '';

// ==========================================================
// LOGIKA UNTUK MENAMBAH ATAU MENGHAPUS GAMBAR
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_galeri') {
        // Logika untuk Mengunggah Gambar Baru
        $judul = clean_input($_POST['judul']);
        $keterangan = clean_input($_POST['keterangan']);
        
        // Cek apakah file gambar diunggah
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_ext, $allowed_extensions)) {
                $unique_file_name = uniqid('galeri_', true) . '.' . $file_ext;
                $upload_path = 'assets/galeri/' . $unique_file_name;

                // Pindahkan file ke folder galeri
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Simpan data gambar ke database
                    $stmt = $conn->prepare("INSERT INTO galeri (judul, keterangan, file_path) VALUES (?, ?, ?)");
                    if ($stmt === false) {
                        $message = "<div class='alert alert-danger'>Gagal menyiapkan query INSERT: " . $conn->error . "</div>";
                    } else {
                        $stmt->bind_param("sss", $judul, $keterangan, $upload_path);
                        if ($stmt->execute()) {
                            $message = "<div class='alert alert-success'>Gambar berhasil diunggah dan ditambahkan ke galeri.</div>";
                        } else {
                            $message = "<div class='alert alert-danger'>Gagal menambahkan data gambar: " . $stmt->error . "</div>";
                        }
                        $stmt->close();
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Gagal memindahkan file yang diunggah.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Gagal mengunggah file. Pastikan Anda memilih gambar.</div>";
        }
    } elseif ($action === 'delete_galeri') {
        // Logika untuk Menghapus Gambar
        $id_to_delete = clean_input($_POST['id_galeri']);

        // Ambil file_path dari database
        $stmt_select = $conn->prepare("SELECT file_path FROM galeri WHERE id_galeri = ?");
        $stmt_select->bind_param("i", $id_to_delete);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'];
        $stmt_select->close();

        // Hapus data dari database
        $stmt_delete = $conn->prepare("DELETE FROM galeri WHERE id_galeri = ?");
        if ($stmt_delete === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query DELETE: " . $conn->error . "</div>";
        } else {
            $stmt_delete->bind_param("i", $id_to_delete);
            if ($stmt_delete->execute()) {
                // Hapus file gambar dari server
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $message = "<div class='alert alert-success'>Gambar berhasil dihapus dari galeri.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menghapus data gambar: " . $stmt_delete->error . "</div>";
            }
            $stmt_delete->close();
        }
    }
}

// ==========================================================
// MENGAMBIL SEMUA DATA GALERI DARI DATABASE UNTUK TAMPILAN
// ==========================================================
$galeri_list = [];
// Baris berikut diubah untuk menghindari error "Unknown column 'created_at'"
// Sekarang, data akan diurutkan berdasarkan judul secara ascending (A-Z).
$sql_galeri = "SELECT * FROM galeri ORDER BY judul ASC";
$result_galeri = $conn->query($sql_galeri);

if ($result_galeri === FALSE) {
    $message = "<div class='alert alert-danger'>Error saat menjalankan kueri data galeri: " . $conn->error . "</div>";
} else {
    if ($result_galeri->num_rows > 0) {
        while ($row = $result_galeri->fetch_assoc()) {
            $galeri_list[] = $row;
        }
    }
}
$conn->close();

// Ambil data pengguna dari sesi untuk sidebar
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';

// Dapatkan nama file halaman saat ini untuk penyorotan menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Galeri Desa</title>
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
        .galeri-item {
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .galeri-item:hover {
            transform: translateY(-5px);
        }
        .galeri-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .galeri-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 1rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .galeri-item:hover .galeri-overlay {
            transform: translateY(0);
        }
        .galeri-overlay h5 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .galeri-overlay p {
            font-size: 0.8rem;
            margin: 0;
        }
        .galeri-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .galeri-item:hover .galeri-actions {
            opacity: 1;
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
            <i class="fas fa-tools me-2"></i> Admin Panel
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
            <h1 class="h3 fw-bold">Manajemen Galeri Desa</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?= $message ?>
        
        <div class="d-flex justify-content-between mb-4 flex-wrap">
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#galeriModal">
                <i class="fas fa-plus-circle me-2"></i> Tambah Gambar
            </button>
        </div>

        <!-- Daftar Galeri -->
        <div class="row g-4">
            <?php if (count($galeri_list) > 0): ?>
                <?php foreach ($galeri_list as $gambar): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="galeri-item card">
                            <img src="<?= htmlspecialchars($gambar['file_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($gambar['judul']) ?>">
                            <div class="galeri-overlay">
                                <h5><?= htmlspecialchars($gambar['judul']) ?></h5>
                                <p><?= htmlspecialchars($gambar['keterangan']) ?></p>
                            </div>
                            <div class="galeri-actions">
                                <button type="button" class="btn btn-sm btn-danger rounded-circle" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= htmlspecialchars($gambar['id_galeri']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">Belum ada gambar di galeri. Silakan tambahkan gambar baru.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Tambah Gambar -->
    <div class="modal fade" id="galeriModal" tabindex="-1" aria-labelledby="galeriModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="galeriModalLabel">Tambah Gambar Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_galeri.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_galeri">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Gambar</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Pilih Gambar</label>
                            <input class="form-control" type="file" id="gambar" name="gambar" accept="image/*" required>
                            <div class="form-text">Format yang didukung: JPG, PNG, GIF</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i> Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Gambar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus gambar ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="delete-form" action="manage_galeri.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_galeri" id="delete-id-hidden">
                        <input type="hidden" name="action" value="delete_galeri">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Listener untuk mengatur ID gambar yang akan dihapus saat modal delete muncul
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const deleteIdHidden = document.getElementById('delete-id-hidden');
            deleteIdHidden.value = id;
        });
    </script>
</body>
</html>
