<?php
// ==========================================================
// KODE INI ADALAH FILE DASHBOARD ADMIN DENGAN SIDEBAR DINAMIS
// DAN KOMPATIBEL DENGAN PHP VERSI LAMA
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

// Ambil data pengguna dari sesi
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$user_name = '';

// Ambil nama pengguna dari database berdasarkan perannya
if ($user_role === 'admin') {
    $stmt = $conn->prepare("SELECT email FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = isset($user['email']) ? $user['email'] : 'Admin';
    $stmt->close();
} elseif ($user_role === 'perangkat_desa') {
    $stmt = $conn->prepare("SELECT nama FROM perangkat_desa WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_name = isset($user['nama']) ? $user['nama'] : 'Perangkat Desa';
    $stmt->close();
}

// ==========================================================
// LOGIKA PENGOLAHAN FORM PENGATURAN DESA BARU
// ==========================================================
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $nama_desa = $_POST['nama_desa'];
        $alamat_desa = $_POST['alamat_desa'];
        $kontak_desa = $_POST['kontak_desa'];
        
        // Cek apakah data sudah ada, jika ada update, jika tidak insert
        $sql = "INSERT INTO settings (id, nama_desa, alamat_desa, kontak_desa) 
                VALUES (1, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                nama_desa = VALUES(nama_desa), 
                alamat_desa = VALUES(alamat_desa), 
                kontak_desa = VALUES(kontak_desa)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama_desa, $alamat_desa, $kontak_desa);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success' role='alert'>Pengaturan desa berhasil diperbarui!</div>";
        } else {
            $message = "<div class='alert alert-danger' role='alert'>Gagal memperbarui pengaturan: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } elseif (isset($_POST['add_slider'])) {
        $slider_image = $_POST['slider_image'];
        $slider_caption = $_POST['slider_caption'];
        
        $sql = "INSERT INTO sliders (image_url, caption) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $slider_image, $slider_caption);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success' role='alert'>Slider berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='alert alert-danger' role='alert'>Gagal menambahkan slider: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } elseif (isset($_POST['delete_slider'])) {
        $slider_id = $_POST['slider_id'];
        
        $sql = "DELETE FROM sliders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $slider_id);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success' role='alert'>Slider berhasil dihapus!</div>";
        } else {
            $message = "<div class='alert alert-danger' role='alert'>Gagal menghapus slider: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// ==========================================================
// MENGAMBIL DATA UNTUK HALAMAN DASHBOARD ATAU PENGATURAN
// ==========================================================

// Dapatkan nama halaman yang diminta dari URL, default ke 'dashboard'
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Mengambil data ringkasan untuk dashboard
$sql_berita = "SELECT COUNT(*) AS total FROM berita";
$result_berita = $conn->query($sql_berita);
$total_berita = ($result_berita && $result_berita->num_rows > 0) ? $result_berita->fetch_assoc()['total'] : 0;

$sql_perangkat = "SELECT COUNT(*) AS total FROM perangkat_desa";
$result_perangkat = $conn->query($sql_perangkat);
$total_perangkat = ($result_perangkat && $result_perangkat->num_rows > 0) ? $result_perangkat->fetch_assoc()['total'] : 0;

$sql_layanan_baru = "SELECT COUNT(*) AS total FROM permintaan_surat WHERE status = 'Diproses'";
$result_layanan_baru = $conn->query($sql_layanan_baru);
$total_layanan_baru = ($result_layanan_baru && $result_layanan_baru->num_rows > 0) ? $result_layanan_baru->fetch_assoc()['total'] : 0;

$sql_penduduk = "SELECT COUNT(*) AS total FROM penduduk";
$result_penduduk = $conn->query($sql_penduduk);
$total_penduduk = ($result_penduduk && $result_penduduk->num_rows > 0) ? $result_penduduk->fetch_assoc()['total'] : 0;

// Mengambil data pengaturan desa untuk ditampilkan di form
$nama_desa = '';
$alamat_desa = '';
$kontak_desa = '';
$sql_settings = "SELECT nama_desa, alamat_desa, kontak_desa FROM settings LIMIT 1";
$result_settings = $conn->query($sql_settings);
if ($result_settings && $result_settings->num_rows > 0) {
    $settings = $result_settings->fetch_assoc();
    $nama_desa = $settings['nama_desa'];
    $alamat_desa = $settings['alamat_desa'];
    $kontak_desa = $settings['kontak_desa'];
}

// Ambil data slider untuk ditampilkan
$sliders = [];
$sql_sliders = "SELECT id, image_url, caption FROM sliders ORDER BY id DESC";
$result_sliders = $conn->query($sql_sliders);
if ($result_sliders && $result_sliders->num_rows > 0) {
    while($row = $result_sliders->fetch_assoc()) {
        $sliders[] = $row;
    }
}

// Tutup koneksi database di akhir skrip
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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
                <!-- Gunakan parameter GET untuk menentukan menu aktif -->
                <a href="dashboard.php?page=dashboard" class="nav-link <?php if ($current_page == 'dashboard') echo 'active'; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <!-- KODE BARU: MENU PENGATURAN DESA -->
            <li class="nav-item mb-2">
                <a href="dashboard.php?page=settings" class="nav-link <?php if ($current_page == 'settings') echo 'active'; ?>">
                    <i class="fas fa-cogs me-2"></i> Pengaturan Desa
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_berita.php" class="nav-link">
                    <i class="fas fa-newspaper me-2"></i> Berita & Artikel
                </a>
            </li>
            <li class="nav-item mb-2">
                <a href="manage_layanan.php" class="nav-link">
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
                <a href="manage_umkm.php" class="nav-link">
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
            <h1 class="h3 fw-bold">
                <?php
                if ($current_page == 'dashboard') {
                    echo 'Dashboard';
                } elseif ($current_page == 'settings') {
                    echo 'Pengaturan Desa';
                }
                ?>
            </h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?php if ($current_page == 'dashboard'): ?>
            <!-- Widget Statistik -->
            <div class="row g-4 mb-5">
                <!-- Kartu Total Berita (dinamis) -->
                <div class="col-md-4">
                    <div class="info-card d-flex align-items-center">
                        <div class="me-4 text-primary card-icon"><i class="fas fa-newspaper"></i></div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold">Jumlah Berita</div>
                            <div class="h2 fw-bold mb-0"><?php echo number_format($total_berita); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Kartu Permintaan Layanan Baru (dinamis) -->
                <div class="col-md-4">
                    <div class="info-card d-flex align-items-center">
                        <div class="me-4 text-warning card-icon"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold">Permintaan Layanan Baru</div>
                            <div class="h2 fw-bold mb-0"><?php echo number_format($total_layanan_baru); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Kartu Total Perangkat Desa (dinamis) -->
                <div class="col-md-4">
                    <div class="info-card d-flex align-items-center">
                        <div class="me-4 text-success card-icon"><i class="fas fa-user-tie"></i></div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold">Total Perangkat Desa</div>
                            <div class="h2 fw-bold mb-0"><?php echo number_format($total_perangkat); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Kartu Total Penduduk (tambahan) -->
                <div class="col-md-4">
                    <div class="info-card d-flex align-items-center">
                        <div class="me-4 text-info card-icon"><i class="fas fa-id-card"></i></div>
                        <div>
                            <div class="text-muted text-uppercase fw-bold">Total Penduduk</div>
                            <div class="h2 fw-bold mb-0"><?php echo number_format($total_penduduk); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Aktivitas Terbaru -->
            <div class="card p-4">
                <h4 class="fw-bold mb-4">Aktivitas Terbaru</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Aktivitas</th>
                                <th scope="col">Waktu</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1</th>
                                <td>Pengajuan surat domisili dari Budi Santoso</td>
                                <td>2 jam yang lalu</td>
                                <td><span class="badge bg-warning text-dark">Menunggu</span></td>
                            </tr>
                            <tr>
                                <th scope="row">2</th>
                                <td>Pengaduan kerusakan jalan dari Siti Aminah</td>
                                <td>4 jam yang lalu</td>
                                <td><span class="badge bg-success">Selesai</span></td>
                            </tr>
                            <tr>
                                <th scope="row">3</th>
                                <td>Berita baru "Peringatan HUT Desa" telah dipublikasikan</td>
                                <td>1 hari yang lalu</td>
                                <td><span class="badge bg-primary">Dipublikasikan</span></td>
                            </tr>
                            <tr>
                                <th scope="row">4</th>
                                <td>Permintaan layanan oleh Agus Kurniawan</td>
                                <td>2 hari yang lalu</td>
                                <td><span class="badge bg-warning text-dark">Menunggu</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($current_page == 'settings'): ?>
            <!-- KODE BARU: TAMPILAN HALAMAN PENGATURAN DESA -->
            <?php echo $message; // Tampilkan pesan sukses/gagal ?>

            <div class='row'>
                <!-- Form Pengaturan Umum -->
                <div class='col-md-6 mb-4'>
                    <div class='card p-4 h-100'>
                        <h5 class='card-title'>Informasi Desa</h5>
                        <form method='POST'>
                            <div class='mb-3'>
                                <label for='nama_desa' class='form-label'>Nama Desa</label>
                                <input type='text' class='form-control' id='nama_desa' name='nama_desa' value='<?php echo htmlspecialchars($nama_desa); ?>' required>
                            </div>
                            <div class='mb-3'>
                                <label for='alamat_desa' class='form-label'>Alamat Desa</label>
                                <textarea class='form-control' id='alamat_desa' name='alamat_desa' rows='3' required><?php echo htmlspecialchars($alamat_desa); ?></textarea>
                            </div>
                            <div class='mb-3'>
                                <label for='kontak_desa' class='form-label'>Kontak Desa</label>
                                <input type='text' class='form-control' id='kontak_desa' name='kontak_desa' value='<?php echo htmlspecialchars($kontak_desa); ?>' required>
                            </div>
                            <button type='submit' name='update_settings' class='btn btn-primary'>Simpan Pengaturan</button>
                        </form>
                    </div>
                </div>
                
                <!-- Form Pengaturan Slider -->
                <div class='col-md-6 mb-4'>
                    <div class='card p-4 h-100'>
                        <h5 class='card-title'>Pengaturan Slider</h5>
                        <form method='POST'>
                            <div class='mb-3'>
                                <label for='slider_image' class='form-label'>URL Gambar Slider</label>
                                <input type='text' class='form-control' id='slider_image' name='slider_image' placeholder='http://example.com/image.jpg' required>
                            </div>
                            <div class='mb-3'>
                                <label for='slider_caption' class='form-label'>Caption/Teks Slider</label>
                                <textarea class='form-control' id='slider_caption' name='slider_caption' rows='2' required></textarea>
                            </div>
                            <button type='submit' name='add_slider' class='btn btn-success'>Tambah Slider Baru</button>
                        </form>
                        
                        <h6 class='mt-4'>Slider yang Ada</h6>
                        <?php if (!empty($sliders)): ?>
                            <ul class='list-group mt-2'>
                                <?php foreach ($sliders as $slider): ?>
                                    <li class='list-group-item d-flex justify-content-between align-items-center'>
                                        <div>
                                            <a href='<?php echo htmlspecialchars($slider['image_url']); ?>' target='_blank'>Gambar</a> - <?php echo htmlspecialchars($slider['caption']); ?>
                                        </div>
                                        <form method='POST' class='m-0 p-0' onsubmit='return confirm("Apakah Anda yakin ingin menghapus slider ini?");'>
                                            <input type='hidden' name='slider_id' value='<?php echo $slider['id']; ?>'>
                                            <button type='submit' name='delete_slider' class='btn btn-danger btn-sm'>Hapus</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class='alert alert-warning mt-2'>Belum ada slider yang ditambahkan.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
