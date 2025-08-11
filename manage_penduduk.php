<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA DATA PENDUDUK
// SEMUA FUNGSI (CRUD, PENCARIAN, IMPOR, EKSPOR CSV, EKSPOR PDF)
// BERADA DALAM SATU FILE.
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

// ==========================================================
// LOGIKA EKSPOR PDF DAN CSV (HARUS DILAKUKAN SEBELUM HTML DIRENDER)
// ==========================================================
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'export_csv') {
        // Set header untuk mengunduh file CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="data_penduduk.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'NIK', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin', 'Alamat', 'Dusun', 'Pendidikan Terakhir', 'Pekerjaan', 'Status Perkawinan', 'Agama', 'Kewarganegaraan', 'PIN']);

        $sql_export = "SELECT id_penduduk, nik, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, dusun, pendidikan_terakhir, pekerjaan, status_perkawinan, agama, kewarganegaraan, pin FROM penduduk ORDER BY nama_lengkap ASC";
        $result_export = $conn->query($sql_export);

        if ($result_export && $result_export->num_rows > 0) {
            while ($row = $result_export->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    } elseif ($_GET['action'] === 'export_pdf') {
        // Logika untuk Ekspor ke PDF menggunakan FPDF
        require('fpdf\fpdf.php'); // Pastikan file fpdf.php berada di direktori yang sama

        // Buat objek FPDF baru
        $pdf = new FPDF('L', 'mm', 'A4'); // Orientasi Landscape
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Judul Laporan
        $pdf->Cell(280, 10, 'LAPORAN DATA PENDUDUK', 0, 1, 'C');
        $pdf->Cell(280, 10, '', 0, 1, 'C'); // Spasi kosong
        
        // Header Tabel
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(8, 7, 'No', 1, 0, 'C');
        $pdf->Cell(25, 7, 'NIK', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Nama Lengkap', 1, 0, 'C');
        $pdf->Cell(25, 7, 'Tempat Lahir', 1, 0, 'C');
        $pdf->Cell(25, 7, 'Tgl Lahir', 1, 0, 'C');
        $pdf->Cell(20, 7, 'J. Kelamin', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Alamat', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Dusun', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Pendidikan', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Pekerjaan', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Perkawinan', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Agama', 1, 1, 'C');

        // Data Tabel
        $sql_export = "SELECT nik, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, dusun, pendidikan_terakhir, pekerjaan, status_perkawinan, agama FROM penduduk ORDER BY nama_lengkap ASC";
        $result_export = $conn->query($sql_export);

        $pdf->SetFont('Arial', '', 8);
        $i = 1;
        if ($result_export && $result_export->num_rows > 0) {
            while ($row = $result_export->fetch_assoc()) {
                $pdf->Cell(8, 7, $i++, 1, 0, 'C');
                $pdf->Cell(25, 7, $row['nik'], 1, 0, 'L');
                $pdf->Cell(40, 7, $row['nama_lengkap'], 1, 0, 'L');
                $pdf->Cell(25, 7, $row['tempat_lahir'], 1, 0, 'L');
                $pdf->Cell(25, 7, date('d-m-Y', strtotime($row['tanggal_lahir'])), 1, 0, 'L');
                $pdf->Cell(20, 7, $row['jenis_kelamin'], 1, 0, 'L');
                $pdf->Cell(40, 7, $row['alamat'], 1, 0, 'L');
                $pdf->Cell(20, 7, $row['dusun'], 1, 0, 'L');
                $pdf->Cell(20, 7, $row['pendidikan_terakhir'], 1, 0, 'L');
                $pdf->Cell(20, 7, $row['pekerjaan'], 1, 0, 'L');
                $pdf->Cell(20, 7, $row['status_perkawinan'], 1, 0, 'L');
                $pdf->Cell(20, 7, $row['agama'], 1, 1, 'L');
            }
        }

        $pdf->Output('I', 'laporan_penduduk.pdf');
        exit;
    }
}

// ==========================================================
// LOGIKA UNTUK MENAMBAH, MENGEDIT, MENGHAPUS, ATAU IMPOR DATA
// ==========================================================
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_penduduk') {
        $nik = clean_input($_POST['nik']);
        $nama_lengkap = clean_input($_POST['nama_lengkap']);
        $tempat_lahir = clean_input($_POST['tempat_lahir']);
        $tanggal_lahir = clean_input($_POST['tanggal_lahir']);
        $jenis_kelamin = clean_input($_POST['jenis_kelamin']);
        $alamat = clean_input($_POST['alamat']);
        $dusun = clean_input($_POST['dusun']);
        $pendidikan_terakhir = clean_input($_POST['pendidikan_terakhir']);
        $pekerjaan = clean_input($_POST['pekerjaan']);
        $status_perkawinan = clean_input($_POST['status_perkawinan']);
        $agama = clean_input($_POST['agama']);
        $kewarganegaraan = clean_input($_POST['kewarganegaraan']);
        $pin = clean_input($_POST['pin']);

        $stmt = $conn->prepare("INSERT INTO penduduk (nik, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, dusun, pendidikan_terakhir, pekerjaan, status_perkawinan, agama, kewarganegaraan, pin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query INSERT: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("sssssssssssss", $nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $dusun, $pendidikan_terakhir, $pekerjaan, $status_perkawinan, $agama, $kewarganegaraan, $pin);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data penduduk baru berhasil ditambahkan.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menambahkan data penduduk: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }

    } elseif ($action === 'edit_penduduk') {
        $id_to_edit = clean_input($_POST['id_penduduk']);
        $nik = clean_input($_POST['nik']);
        $nama_lengkap = clean_input($_POST['nama_lengkap']);
        $tempat_lahir = clean_input($_POST['tempat_lahir']);
        $tanggal_lahir = clean_input($_POST['tanggal_lahir']);
        $jenis_kelamin = clean_input($_POST['jenis_kelamin']);
        $alamat = clean_input($_POST['alamat']);
        $dusun = clean_input($_POST['dusun']);
        $pendidikan_terakhir = clean_input($_POST['pendidikan_terakhir']);
        $pekerjaan = clean_input($_POST['pekerjaan']);
        $status_perkawinan = clean_input($_POST['status_perkawinan']);
        $agama = clean_input($_POST['agama']);
        $kewarganegaraan = clean_input($_POST['kewarganegaraan']);
        $pin = clean_input($_POST['pin']);

        $stmt = $conn->prepare("UPDATE penduduk SET nik=?, nama_lengkap=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, alamat=?, dusun=?, pendidikan_terakhir=?, pekerjaan=?, status_perkawinan=?, agama=?, kewarganegaraan=?, pin=? WHERE id_penduduk=?");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query UPDATE: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("sssssssssssssi", $nik, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $alamat, $dusun, $pendidikan_terakhir, $pekerjaan, $status_perkawinan, $agama, $kewarganegaraan, $pin, $id_to_edit);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data penduduk berhasil diubah.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal mengubah data penduduk: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }

    } elseif ($action === 'delete_penduduk') {
        $id_to_delete = clean_input($_POST['id_penduduk']);
        $stmt = $conn->prepare("DELETE FROM penduduk WHERE id_penduduk = ?");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query DELETE: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data penduduk berhasil dihapus.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menghapus data penduduk: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } elseif ($action === 'import_penduduk') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            $csv_file = $_FILES['csv_file']['tmp_name'];
            $file_handle = fopen($csv_file, "r");
            if ($file_handle === false) {
                $message = "<div class='alert alert-danger'>Gagal membuka file.</div>";
            } else {
                fgetcsv($file_handle);
                $imported_count = 0;
                $failed_rows = [];
                $stmt = $conn->prepare("INSERT INTO penduduk (nik, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, dusun, pendidikan_terakhir, pekerjaan, status_perkawinan, agama, kewarganegaraan, pin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                while (($data = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
                    if (count($data) >= 13) {
                        $stmt->bind_param("sssssssssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11], $data[12]);
                        if ($stmt->execute()) {
                            $imported_count++;
                        } else {
                            $failed_rows[] = implode(",", $data) . " (Error: " . $stmt->error . ")";
                        }
                    } else {
                        $failed_rows[] = implode(",", $data) . " (Error: Jumlah kolom tidak valid)";
                    }
                }
                fclose($file_handle);
                $message = "<div class='alert alert-success'>Berhasil mengimpor $imported_count data penduduk.</div>";
                if (!empty($failed_rows)) {
                    $message .= "<div class='alert alert-warning mt-2'>Gagal mengimpor data dari baris berikut:<br>" . implode("<br>", $failed_rows) . "</div>";
                }
                $stmt->close();
            }
        } else {
            $message = "<div class='alert alert-danger'>Gagal mengunggah file. Pastikan file berformat CSV.</div>";
        }
    }
}

// ==========================================================
// MENGAMBIL SEMUA DATA PENDUDUK DARI DATABASE UNTUK TAMPILAN
// ==========================================================
$penduduk_list = [];
$search_query = isset($_GET['search_query']) ? clean_input($_GET['search_query']) : '';
$sql_penduduk = "SELECT * FROM penduduk";
$where_clause = "";

if (!empty($search_query)) {
    $where_clause = " WHERE nama_lengkap LIKE '%$search_query%' OR nik LIKE '%$search_query%' OR dusun LIKE '%$search_query%'";
}

$sql_penduduk .= $where_clause . " ORDER BY nama_lengkap ASC";
$result_penduduk = $conn->query($sql_penduduk);

if ($result_penduduk === FALSE) {
    $message = "<div class='alert alert-danger'>Error saat menjalankan kueri data penduduk: " . $conn->error . "</div>";
} else {
    if ($result_penduduk->num_rows > 0) {
        while ($row = $result_penduduk->fetch_assoc()) {
            $penduduk_list[] = $row;
        }
    }
}
$conn->close();

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Data Penduduk</title>
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
        .table-container {
            max-height: 400px;
            overflow-y: auto;
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
            <h1 class="h3 fw-bold">Manajemen Data Penduduk</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?= $message ?>
        
        <div class="d-flex justify-content-between mb-4 flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <!-- Tombol Tambah dan Impor -->
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#pendudukModal" onclick="resetForm()">
                    <i class="fas fa-plus-circle me-2"></i> Tambah
                </button>
                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                    <i class="fas fa-file-upload me-2"></i> Impor
                </button>
                <!-- Dropdown Ekspor -->
                <div class="dropdown">
                    <button class="btn btn-info text-white dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-download me-2"></i> Ekspor
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="manage_penduduk.php?action=export_csv">Ekspor ke CSV</a></li>
                        <li><a class="dropdown-item" href="manage_penduduk.php?action=export_pdf">Ekspor ke PDF</a></li>
                    </ul>
                </div>
            </div>

            <!-- Form Pencarian -->
            <form action="manage_penduduk.php" method="GET" class="d-flex mb-2 mb-md-0">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Cari penduduk..." name="search_query" value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>


        <!-- Tabel Daftar Penduduk -->
        <div class="card p-4">
            <h4 class="fw-bold mb-4">Daftar Penduduk</h4>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">NIK</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Dusun</th>
                                <th scope="col">Pendidikan</th>
                                <th scope="col">Pekerjaan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($penduduk_list) > 0): ?>
                                <?php $i = 1; ?>
                                <?php foreach ($penduduk_list as $penduduk): ?>
                                    <tr>
                                        <th scope="row"><?= $i++; ?></th>
                                        <td><?= htmlspecialchars($penduduk['nik']) ?></td>
                                        <td><?= htmlspecialchars($penduduk['nama_lengkap']) ?></td>
                                        <td><?= htmlspecialchars($penduduk['dusun']) ?></td>
                                        <td><?= htmlspecialchars($penduduk['pendidikan_terakhir']) ?></td>
                                        <td><?= htmlspecialchars($penduduk['pekerjaan']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Tombol Edit yang membuka modal dan mengisi data -->
                                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#pendudukModal" onclick='editPenduduk(<?= json_encode($penduduk) ?>)'>
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <!-- Tombol Hapus yang membuka modal konfirmasi -->
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?= htmlspecialchars($penduduk['id_penduduk']) ?>">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data penduduk yang tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Penduduk -->
    <div class="modal fade" id="pendudukModal" tabindex="-1" aria-labelledby="pendudukModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="pendudukModalLabel">Tambah Penduduk Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_penduduk.php" method="POST" id="penduduk-form">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="action_input" value="add_penduduk">
                        <input type="hidden" name="id_penduduk" id="id_penduduk">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control" id="nik" name="nik" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih...</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="alamat" class="form-label">Alamat</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dusun" class="form-label">Dusun</label>
                                <select class="form-select" id="dusun" name="dusun" required>
                                    <option value="">Pilih...</option>
                                    <option value="Dusun 1">Dusun 1</option>
                                    <option value="Dusun 2">Dusun 2</option>
                                    <option value="Dusun 3">Dusun 3</option>
                                    <option value="Dusun 4">Dusun 4</option>
                                    <option value="Dusun 5">Dusun 5</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="pendidikan_terakhir" class="form-label">Pendidikan Terakhir</label>
                                <select class="form-select" id="pendidikan_terakhir" name="pendidikan_terakhir" required>
                                    <option value="Belum Sekolah">Belum Sekolah</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA">SMA</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                    <option value="S4">S4</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status_perkawinan" class="form-label">Status Perkawinan</label>
                                <select class="form-select" id="status_perkawinan" name="status_perkawinan" required>
                                    <option value="">Pilih...</option>
                                    <option value="Belum Kawin">Belum Kawin</option>
                                    <option value="Kawin">Kawin</option>
                                    <option value="Cerai Hidup">Cerai Hidup</option>
                                    <option value="Cerai Mati">Cerai Mati</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="agama" class="form-label">Agama</label>
                                <select class="form-select" id="agama" name="agama" required>
                                    <option value="">Pilih...</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen Protestan">Kristen Protestan</option>
                                    <option value="Kristen Katolik">Kristen Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="kewarganegaraan" class="form-label">Kewarganegaraan</label>
                                <input type="text" class="form-control" id="kewarganegaraan" name="kewarganegaraan" value="WNI" required>
                            </div>
                            <div class="col-md-6">
                                <label for="pin" class="form-label">PIN (untuk Layanan Mandiri)</label>
                                <input type="password" class="form-control" id="pin" name="pin" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" id="submit-button" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i> Tambah Penduduk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Impor Data -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Impor Data Penduduk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_penduduk.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="import_penduduk">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Pilih File CSV</label>
                            <input class="form-control" type="file" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">Pastikan file CSV memiliki header yang sesuai dengan kolom tabel.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload me-2"></i> Impor</button>
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
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data penduduk ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form id="delete-form" action="manage_penduduk.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_penduduk" id="delete-id-hidden">
                        <input type="hidden" name="action" value="delete_penduduk">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Fungsi untuk mengisi form saat tombol "Edit" diklik
        function editPenduduk(penduduk) {
            document.getElementById('pendudukModalLabel').innerText = 'Edit Data Penduduk';
            document.getElementById('action_input').value = 'edit_penduduk';
            document.getElementById('submit-button').innerHTML = '<i class="fas fa-save me-2"></i> Perbarui Data';
            document.getElementById('submit-button').classList.remove('btn-primary');
            document.getElementById('submit-button').classList.add('btn-warning');

            document.getElementById('id_penduduk').value = penduduk.id_penduduk;
            document.getElementById('nik').value = penduduk.nik;
            document.getElementById('nama_lengkap').value = penduduk.nama_lengkap;
            document.getElementById('tempat_lahir').value = penduduk.tempat_lahir;
            document.getElementById('tanggal_lahir').value = penduduk.tanggal_lahir;
            document.getElementById('jenis_kelamin').value = penduduk.jenis_kelamin;
            document.getElementById('alamat').value = penduduk.alamat;
            document.getElementById('dusun').value = penduduk.dusun;
            document.getElementById('pendidikan_terakhir').value = penduduk.pendidikan_terakhir;
            document.getElementById('pekerjaan').value = penduduk.pekerjaan;
            document.getElementById('status_perkawinan').value = penduduk.status_perkawinan;
            document.getElementById('agama').value = penduduk.agama;
            document.getElementById('kewarganegaraan').value = penduduk.kewarganegaraan;
            document.getElementById('pin').value = penduduk.pin;
        }

        // Fungsi untuk mereset form ke kondisi awal (tambah data)
        function resetForm() {
            document.getElementById('pendudukModalLabel').innerText = 'Tambah Penduduk Baru';
            document.getElementById('action_input').value = 'add_penduduk';
            document.getElementById('submit-button').innerHTML = '<i class="fas fa-plus-circle me-2"></i> Tambah Penduduk';
            document.getElementById('submit-button').classList.remove('btn-warning');
            document.getElementById('submit-button').classList.add('btn-primary');
            document.getElementById('penduduk-form').reset();
        }

        // Listener untuk mengatur ID data yang akan dihapus saat modal delete muncul
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
