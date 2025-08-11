<?php
// ==========================================================
// KODE INI UNTUK MENGELOLA DATA UMKM DENGAN UNGGAH FOTO
// DAN NOMOR WHATSAPP.
// Telah diperbarui untuk alur kerja yang lebih baik dan
// konfirmasi penghapusan dengan modal.
// ==========================================================

// Memulai sesi untuk memeriksa status login
session_start();

// Periksa apakah pengguna sudah login. Jika tidak, arahkan ke halaman login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Sertakan file koneksi database.
// Asumsi 'db.php' berisi logika koneksi ke database.
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
// LOGIKA UNTUK MENAMBAH, MENGEDIT, DAN MENGHAPUS UMKM
// ==========================================================
$message = '';
$message_type = '';

// Tentukan direktori untuk menyimpan gambar yang diunggah
$upload_dir = 'upload/umkm/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Memeriksa apakah action ada
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Logika untuk Menambah dan Mengedit
        if ($action === 'add' || $action === 'edit') {
            // Menggunakan isset() dan htmlspecialchars() untuk keamanan dan menghindari Notice: Undefined index
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $nama_umkm = isset($_POST['nama_umkm']) ? htmlspecialchars($_POST['nama_umkm']) : '';
            $pemilik = isset($_POST['pemilik']) ? htmlspecialchars($_POST['pemilik']) : '';
            $jenis_usaha = isset($_POST['jenis_usaha']) ? htmlspecialchars($_POST['jenis_usaha']) : '';
            $deskripsi = isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '';
            $no_wa = isset($_POST['no_wa']) ? htmlspecialchars($_POST['no_wa']) : '';
            $gambar_lama = isset($_POST['gambar_lama']) ? $_POST['gambar_lama'] : '';
            $gambar_path = $gambar_lama;
            $upload_success = true;

            // Handle unggahan gambar baru
            if (isset($_FILES['gambar_umkm']) && $_FILES['gambar_umkm']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['gambar_umkm']['tmp_name'];
                $file_name = uniqid() . '-' . basename($_FILES['gambar_umkm']['name']);
                $target_file = $upload_dir . $file_name;

                // Hapus gambar lama jika ada saat mode edit
                if ($action === 'edit' && !empty($gambar_lama) && file_exists($gambar_lama)) {
                    unlink($gambar_lama);
                }

                if (move_uploaded_file($file_tmp, $target_file)) {
                    $gambar_path = $target_file;
                } else {
                    $message = "Gagal mengunggah gambar.";
                    $message_type = 'danger';
                    $upload_success = false;
                }
            }

            // Hanya lanjutkan operasi database jika unggahan berhasil atau tidak ada file baru
            if ($upload_success) {
                if ($action === 'add') {
                    // Pastikan kolom 'no_wa' ada di tabel 'umkm'
                    $sql = "INSERT INTO umkm (nama_umkm, pemilik, jenis_usaha, deskripsi, gambar_path, no_wa) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                } else { // action === 'edit'
                    $sql = "UPDATE umkm SET nama_umkm = ?, pemilik = ?, jenis_usaha = ?, deskripsi = ?, gambar_path = ?, no_wa = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                }

                // Pengecekan error setelah prepare(). Ini akan mengatasi Fatal Error.
                if ($stmt === false) {
                    $message = "Error preparing the statement: " . $conn->error;
                    $message_type = 'danger';
                } else {
                    if ($action === 'add') {
                        $stmt->bind_param("ssssss", $nama_umkm, $pemilik, $jenis_usaha, $deskripsi, $gambar_path, $no_wa);
                    } else { // action === 'edit'
                        $stmt->bind_param("ssssssi", $nama_umkm, $pemilik, $jenis_usaha, $deskripsi, $gambar_path, $no_wa, $id);
                    }

                    if ($stmt->execute()) {
                        $message = "Data UMKM berhasil " . ($action === 'add' ? "ditambahkan" : "diperbarui") . ".";
                        $message_type = 'success';
                    } else {
                        $message = "Gagal " . ($action === 'add' ? "menambahkan" : "memperbarui") . " data UMKM: " . $stmt->error;
                        $message_type = 'danger';
                    }
                    $stmt->close();
                }
            }
        } elseif ($action === 'delete') {
            if (isset($_POST['id'])) {
                $id = (int)$_POST['id'];

                // Ambil path gambar sebelum menghapus data
                $sql_get_image = "SELECT gambar_path FROM umkm WHERE id = ?";
                $stmt_get_image = $conn->prepare($sql_get_image);
                if ($stmt_get_image === false) {
                    $message = "Error preparing statement for image retrieval: " . $conn->error;
                    $message_type = 'danger';
                } else {
                    $stmt_get_image->bind_param("i", $id);
                    $stmt_get_image->execute();
                    $result_image = $stmt_get_image->get_result();
                    $umkm_to_delete = $result_image->fetch_assoc();
                    $gambar_path_to_delete = $umkm_to_delete ? $umkm_to_delete['gambar_path'] : null;
                    $stmt_get_image->close();

                    $sql = "DELETE FROM umkm WHERE id = ?";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        $message = "Error preparing the statement: " . $conn->error;
                        $message_type = 'danger';
                    } else {
                        $stmt->bind_param("i", $id);

                        if ($stmt->execute()) {
                            // Hapus gambar dari server jika ada
                            if ($gambar_path_to_delete && file_exists($gambar_path_to_delete)) {
                                unlink($gambar_path_to_delete);
                            }
                            $message = "Data UMKM berhasil dihapus.";
                            $message_type = 'success';
                        } else {
                            $message = "Gagal menghapus data UMKM: " . $stmt->error;
                            $message_type = 'danger';
                        }
                        $stmt->close();
                    }
                }
            } else {
                $message = "ID UMKM tidak ditemukan.";
                $message_type = 'danger';
            }
        }
    }
}

// Ambil semua data UMKM dari database untuk ditampilkan di tabel
$umkm_list = [];
$sql_umkm = "SELECT id, nama_umkm, pemilik, jenis_usaha, deskripsi, gambar_path, no_wa FROM umkm ORDER BY id DESC";
$result_umkm = $conn->query($sql_umkm);
if ($result_umkm && $result_umkm->num_rows > 0) {
    while ($row = $result_umkm->fetch_assoc()) {
        $umkm_list[] = $row;
    }
}

// Tutup koneksi database
$conn->close();

// Dapatkan nama file halaman saat ini untuk penyorotan menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen UMKM</title>
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
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table-responsive {
            border-radius: 0.75rem;
            overflow: hidden;
        }
        .umkm-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        .current-image {
            max-width: 150px;
            height: auto;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
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
            <h1 class="h3 fw-bold">Manajemen UMKM</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo htmlspecialchars($user_name); ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <!-- Formulir Tambah UMKM -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                Tambah UMKM Baru
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <form action="manage_umkm.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="nama_umkm" class="form-label">Nama UMKM</label>
                        <input type="text" class="form-control" id="nama_umkm" name="nama_umkm" required>
                    </div>
                    <div class="mb-3">
                        <label for="pemilik" class="form-label">Nama Pemilik</label>
                        <input type="text" class="form-control" id="pemilik" name="pemilik" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_usaha" class="form-label">Jenis Usaha</label>
                        <input type="text" class="form-control" id="jenis_usaha" name="jenis_usaha" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <!-- INPUT BARU UNTUK NOMOR WHATSAPP -->
                    <div class="mb-3">
                        <label for="no_wa" class="form-label">Nomor WhatsApp</label>
                        <input type="text" class="form-control" id="no_wa" name="no_wa" required>
                    </div>
                    <div class="mb-3">
                        <label for="gambar_umkm" class="form-label">Unggah Foto UMKM</label>
                        <input type="file" class="form-control" id="gambar_umkm" name="gambar_umkm" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tambah UMKM</button>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar UMKM -->
        <div class="card p-4">
            <h4 class="fw-bold mb-4">Daftar UMKM</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Foto</th>
                            <th scope="col">Nama UMKM</th>
                            <th scope="col">Pemilik</th>
                            <th scope="col">Jenis Usaha</th>
                            <th scope="col">Deskripsi</th>
                            <!-- KOLOM BARU DI TABEL -->
                            <th scope="col">Nomor WA</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($umkm_list)): ?>
                        <?php $no = 1; foreach ($umkm_list as $umkm): ?>
                        <tr>
                            <th scope="row"><?php echo $no++; ?></th>
                            <td>
                                <?php if ($umkm['gambar_path'] && file_exists($umkm['gambar_path'])): ?>
                                <img src="<?php echo htmlspecialchars($umkm['gambar_path']); ?>" alt="<?php echo htmlspecialchars($umkm['nama_umkm']); ?>" class="umkm-image">
                                <?php else: ?>
                                <i class="fas fa-image fa-2x text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($umkm['nama_umkm']); ?></td>
                            <td><?php echo htmlspecialchars($umkm['pemilik']); ?></td>
                            <td><?php echo htmlspecialchars($umkm['jenis_usaha']); ?></td>
                            <td><?php echo htmlspecialchars($umkm['deskripsi']); ?></td>
                            <!-- MENAMPILKAN DATA NOMOR WHATSAPP -->
                            <td><?php echo htmlspecialchars($umkm['no_wa']); ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning text-white me-2" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="<?php echo $umkm['id']; ?>"
                                    data-nama="<?php echo htmlspecialchars($umkm['nama_umkm']); ?>"
                                    data-pemilik="<?php echo htmlspecialchars($umkm['pemilik']); ?>"
                                    data-jenis="<?php echo htmlspecialchars($umkm['jenis_usaha']); ?>"
                                    data-deskripsi="<?php echo htmlspecialchars($umkm['deskripsi']); ?>"
                                    data-gambar="<?php echo htmlspecialchars($umkm['gambar_path']); ?>"
                                    data-no_wa="<?php echo htmlspecialchars($umkm['no_wa']); ?>">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Tombol hapus yang akan memicu modal konfirmasi -->
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php echo $umkm['id']; ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data UMKM.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit UMKM -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="editModalLabel">Edit UMKM</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="manage_umkm.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit-id" name="id">
                        <input type="hidden" id="gambar-lama" name="gambar_lama">

                        <div class="mb-3">
                            <label for="edit-nama_umkm" class="form-label">Nama UMKM</label>
                            <input type="text" class="form-control" id="edit-nama_umkm" name="nama_umkm" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-pemilik" class="form-label">Nama Pemilik</label>
                            <input type="text" class="form-control" id="edit-pemilik" name="pemilik" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-jenis_usaha" class="form-label">Jenis Usaha</label>
                            <input type="text" class="form-control" id="edit-jenis_usaha" name="jenis_usaha" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="edit-deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        <!-- INPUT BARU UNTUK NOMOR WHATSAPP DI MODAL -->
                        <div class="mb-3">
                            <label for="edit-no_wa" class="form-label">Nomor WhatsApp</label>
                            <input type="text" class="form-control" id="edit-no_wa" name="no_wa" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-gambar_umkm" class="form-label">Unggah Foto Baru (Kosongkan jika tidak diubah)</label>
                            <input type="file" class="form-control" id="edit-gambar_umkm" name="gambar_umkm" accept="image/*">
                            <div class="mt-2" id="current-image-container">
                                <strong>Foto Saat Ini:</strong>
                                <br>
                                <img id="current-image" src="" alt="Foto UMKM Saat Ini" class="current-image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-warning text-white">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data UMKM ini? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" action="manage_umkm.php" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" id="delete-id" name="id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // JavaScript untuk mengisi data ke modal edit
        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            // Dapatkan tombol yang memicu modal
            var button = event.relatedTarget;
            // Ekstrak informasi dari atribut data-*
            var id = button.getAttribute('data-id');
            var nama = button.getAttribute('data-nama');
            var pemilik = button.getAttribute('data-pemilik');
            var jenis = button.getAttribute('data-jenis');
            var deskripsi = button.getAttribute('data-deskripsi');
            var gambar = button.getAttribute('data-gambar');
            var no_wa = button.getAttribute('data-no_wa');

            // Dapatkan elemen-elemen modal
            var modalTitle = editModal.querySelector('.modal-title');
            var modalBodyInputId = editModal.querySelector('#edit-id');
            var modalBodyInputNama = editModal.querySelector('#edit-nama_umkm');
            var modalBodyInputPemilik = editModal.querySelector('#edit-pemilik');
            var modalBodyInputJenis = editModal.querySelector('#edit-jenis_usaha');
            var modalBodyInputDeskripsi = editModal.querySelector('#edit-deskripsi');
            var modalBodyInputNoWa = editModal.querySelector('#edit-no_wa');
            var modalBodyInputGambarLama = editModal.querySelector('#gambar-lama');
            var currentImage = editModal.querySelector('#current-image');
            var currentImageContainer = editModal.querySelector('#current-image-container');

            // Perbarui konten modal
            modalTitle.textContent = 'Edit UMKM: ' + nama;
            modalBodyInputId.value = id;
            modalBodyInputNama.value = nama;
            modalBodyInputPemilik.value = pemilik;
            modalBodyInputJenis.value = jenis;
            modalBodyInputDeskripsi.value = deskripsi;
            modalBodyInputNoWa.value = no_wa;
            modalBodyInputGambarLama.value = gambar;
            
            // Tampilkan atau sembunyikan gambar saat ini
            if (gambar) {
                currentImage.src = gambar;
                currentImageContainer.style.display = 'block';
            } else {
                currentImage.src = '';
                currentImageContainer.style.display = 'none';
            }
        });

        // JavaScript untuk mengisi ID UMKM ke modal hapus
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            // Dapatkan tombol yang memicu modal
            var button = event.relatedTarget;
            // Ekstrak ID dari atribut data-*
            var id = button.getAttribute('data-id');
            // Dapatkan elemen input hidden di dalam form hapus
            var modalBodyInputId = deleteModal.querySelector('#delete-id');
            // Perbarui nilai input hidden
            modalBodyInputId.value = id;
        });
    </script>
</body>
</html>
