<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA DATA PERANGKAT DESA
// FILE INI DIRANCANG UNTUK BERFUNGSI SEBAGAI HALAMAN MANDIRI
// DI DALAM STRUKTUR DASHBOARD ADMIN
// ==========================================================

// Memulai sesi untuk memeriksa status login
session_start();

// Periksa apakah pengguna sudah login DAN memiliki peran 'admin'.
// Jika tidak, arahkan ke halaman login.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Jika pengguna sudah login tapi bukan admin, arahkan ke dashboard penduduk atau halaman lain yang sesuai
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard_penduduk.php"); // Atau ke halaman error/akses ditolak
    } else {
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
// LOGIKA UNTUK MENAMBAH, MENGEDIT, DAN MENGHAPUS DATA PERANGKAT DESA
// ==========================================================
$message = '';

// Logika Tambah/Edit Perangkat Desa (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = clean_input($_POST['action']);
    $nama = clean_input($_POST['nama']);
    $jabatan = clean_input($_POST['jabatan']);
    $email = clean_input($_POST['email']);

    if ($action == 'add') {
        $password = password_hash(clean_input($_POST['password']), PASSWORD_DEFAULT); // Hash password
        $stmt = $conn->prepare("INSERT INTO perangkat_desa (nama, jabatan, email, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt === false) {
            $message = "<div class='alert alert-danger'>Gagal menyiapkan query ADD: " . $conn->error . "</div>";
        } else {
            $stmt->bind_param("ssss", $nama, $jabatan, $email, $password);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Data perangkat desa berhasil ditambahkan.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Gagal menambahkan data perangkat desa: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } elseif ($action == 'edit') {
        $id_perangkat = clean_input($_POST['id_perangkat']);
        $sql_update = "UPDATE perangkat_desa SET nama = ?, jabatan = ?, email = ? WHERE id = ?";
        
        // Periksa apakah password baru diisi
        if (!empty($_POST['password'])) {
            $password = password_hash(clean_input($_POST['password']), PASSWORD_DEFAULT);
            $sql_update = "UPDATE perangkat_desa SET nama = ?, jabatan = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            if ($stmt === false) {
                $message = "<div class='alert alert-danger'>Gagal menyiapkan query EDIT (dengan password): " . $conn->error . "</div>";
            } else {
                $stmt->bind_param("ssssi", $nama, $jabatan, $email, $password, $id_perangkat);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Data perangkat desa berhasil diperbarui.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Gagal memperbarui data perangkat desa: " . $stmt->error . "</div>";
                }
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare($sql_update);
            if ($stmt === false) {
                $message = "<div class='alert alert-danger'>Gagal menyiapkan query EDIT (tanpa password): " . $conn->error . "</div>";
            } else {
                $stmt->bind_param("sssi", $nama, $jabatan, $email, $id_perangkat);
                if ($stmt->execute()) {
                    $message = "<div class='alert alert-success'>Data perangkat desa berhasil diperbarui.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Gagal memperbarui data perangkat desa: " . $stmt->error . "</div>";
                }
                $stmt->close();
            }
        }
    }
}

// Logika Hapus Perangkat Desa (GET Request)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_perangkat = clean_input($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM perangkat_desa WHERE id = ?");
    if ($stmt === false) {
        $message = "<div class='alert alert-danger'>Gagal menyiapkan query DELETE: " . $conn->error . "</div>";
    } else {
        $stmt->bind_param("i", $id_perangkat);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Data perangkat desa berhasil dihapus.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menghapus data perangkat desa: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
    // Redirect untuk menghindari resubmission form pada refresh
    header("Location: manage_perangkat_desa.php");
    exit;
}

// ==========================================================
// MENGAMBIL SEMUA DATA PERANGKAT DESA DARI DATABASE
// ==========================================================
$perangkat_desa_list = [];
$sql = "SELECT id, nama, jabatan, email FROM perangkat_desa ORDER BY nama ASC";
$result = $conn->query($sql);
if ($result === FALSE) {
    $message = "<div class='alert alert-danger'>Error saat menjalankan kueri SELECT: " . $conn->error . "</div>";
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $perangkat_desa_list[] = $row;
        }
    }
}
$conn->close();

// Ambil data pengguna dari sesi untuk sidebar
$user_display_name = isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perangkat Desa</title>
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
            <h1 class="h3 fw-bold">Manajemen Data Perangkat Desa</h1>
            <div class="d-flex align-items-center">
                <span class="me-3 d-none d-md-block text-muted">Selamat datang, <?php echo $user_display_name; ?>!</span>
                <a href="logout.php" class="btn btn-danger d-md-none"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <?= $message ?>

        <!-- Tombol Tambah Perangkat Desa -->
        <div class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPerangkatModal">
                <i class="fas fa-plus-circle me-2"></i> Tambah Perangkat Desa Baru
            </button>
        </div>

        <!-- Tabel Daftar Perangkat Desa -->
        <div class="table-responsive card p-4">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Jabatan</th>
                        <th scope="col">Email</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($perangkat_desa_list) > 0): ?>
                        <?php $i = 1; foreach ($perangkat_desa_list as $perangkat): ?>
                            <tr>
                                <th scope="row"><?= $i++; ?></th>
                                <td><?= htmlspecialchars($perangkat['nama']) ?></td>
                                <td><?= htmlspecialchars($perangkat['jabatan']) ?></td>
                                <td><?= htmlspecialchars($perangkat['email']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-warning edit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editPerangkatModal"
                                                data-id="<?= htmlspecialchars($perangkat['id']) ?>"
                                                data-nama="<?= htmlspecialchars($perangkat['nama']) ?>"
                                                data-jabatan="<?= htmlspecialchars($perangkat['jabatan']) ?>"
                                                data-email="<?= htmlspecialchars($perangkat['email']) ?>">
                                            Edit
                                        </button>
                                        <a href="manage_perangkat_desa.php?action=delete&id=<?= htmlspecialchars($perangkat['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data perangkat desa ini?');">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data perangkat desa yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah Perangkat Desa -->
    <div class="modal fade" id="addPerangkatModal" tabindex="-1" aria-labelledby="addPerangkatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addPerangkatModalLabel">Tambah Perangkat Desa Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_perangkat_desa.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="add_nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="add_nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="add_jabatan" name="jabatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="add_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="add_password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="add_password" name="password" required>
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

    <!-- Modal Edit Perangkat Desa -->
    <div class="modal fade" id="editPerangkatModal" tabindex="-1" aria-labelledby="editPerangkatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editPerangkatModalLabel">Edit Data Perangkat Desa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="manage_perangkat_desa.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_perangkat" id="edit_id_perangkat">
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="edit_jabatan" name="jabatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
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
        var editPerangkatModal = document.getElementById('editPerangkatModal');
        editPerangkatModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Tombol yang memicu modal
            
            // Ambil data dari atribut data-*
            var id_perangkat = button.getAttribute('data-id');
            var nama = button.getAttribute('data-nama');
            var jabatan = button.getAttribute('data-jabatan');
            var email = button.getAttribute('data-email');

            // Isi data ke dalam field modal
            var modalIdPerangkat = editPerangkatModal.querySelector('#edit_id_perangkat');
            var modalNama = editPerangkatModal.querySelector('#edit_nama');
            var modalJabatan = editPerangkatModal.querySelector('#edit_jabatan');
            var modalEmail = editPerangkatModal.querySelector('#edit_email');
            var modalPassword = editPerangkatModal.querySelector('#edit_password'); // Input password

            modalIdPerangkat.value = id_perangkat;
            modalNama.value = nama;
            modalJabatan.value = jabatan;
            modalEmail.value = email;
            modalPassword.value = ''; // Kosongkan password saat modal dibuka untuk keamanan
        });
    </script>
</body>
</html>
