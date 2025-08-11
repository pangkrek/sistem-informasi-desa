<?php
// ==========================================================
// KODE INI ADALAH HALAMAN UNTUK MENGELOLA BERITA DAN ARTIKEL
// DENGAN FITUR UNGGAH GAMBAR
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

// Fungsi untuk menangani unggah gambar
function handle_upload($file) {
    $target_dir = "upload/image_berita/";
    // Pastikan direktori ada, jika tidak, buatlah
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    // Validasi tipe file
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan."];
    }

    // Buat nama file unik untuk mencegah duplikasi
    $new_file_name = uniqid('berita_', true) . '.' . $imageFileType;
    $target_file = $target_dir . $new_file_name;

    // Pindahkan file dari lokasi sementara ke direktori target
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_file_name];
    } else {
        return ['success' => false, 'message' => "Terjadi kesalahan saat mengunggah file."];
    }
}

// ==========================================================
// LOGIKA UNTUK MENAMBAH, MENGEDIT, DAN MENGHAPUS DATA
// ==========================================================
$message = '';

// Logika Tambah Berita
if (isset($_POST['add_berita'])) {
    $judul = clean_input($_POST['judul']);
    $isi_singkat = clean_input($_POST['isi_singkat']);
    $isi_lengkap = clean_input($_POST['isi_lengkap']);
    $author = clean_input($_POST['author']);
    $tanggal = date('Y-m-d');

    // Cek apakah ada file gambar yang diunggah
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handle_upload($_FILES['gambar']);
        if ($upload_result['success']) {
            $gambar_filename = $upload_result['filename'];

            $stmt = $conn->prepare("INSERT INTO berita (judul, isi_singkat, isi_lengkap, gambar, tanggal, author) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $judul, $isi_singkat, $isi_lengkap, $gambar_filename, $tanggal, $author);

            if ($stmt->execute()) {
                $message = "Berita baru berhasil ditambahkan.";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = $upload_result['message'];
        }
    } else {
        $message = "Error: Gambar belum diunggah.";
    }
}

// Logika Edit Berita
if (isset($_POST['edit_berita'])) {
    $id = clean_input($_POST['id']);
    $judul = clean_input($_POST['judul']);
    $isi_singkat = clean_input($_POST['isi_singkat']);
    $isi_lengkap = clean_input($_POST['isi_lengkap']);
    $author = clean_input($_POST['author']);
    $gambar_filename = $_POST['gambar_lama']; // Nama file gambar lama

    // Cek apakah ada file gambar baru yang diunggah
    if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] == UPLOAD_ERR_OK) {
        $upload_result = handle_upload($_FILES['gambar_baru']);
        if ($upload_result['success']) {
            // Hapus gambar lama jika ada
            if (!empty($gambar_filename) && file_exists("upload/image_berita/" . $gambar_filename)) {
                unlink("upload/image_berita/" . $gambar_filename);
            }
            $gambar_filename = $upload_result['filename'];
        } else {
            $message = $upload_result['message'];
        }
    }

    $stmt = $conn->prepare("UPDATE berita SET judul = ?, isi_singkat = ?, isi_lengkap = ?, gambar = ?, author = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $judul, $isi_singkat, $isi_lengkap, $gambar_filename, $author, $id);

    if ($stmt->execute()) {
        $message = "Berita berhasil diperbarui.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Logika Hapus Berita
if (isset($_GET['delete_id'])) {
    $id = clean_input($_GET['delete_id']);

    // Ambil nama file gambar dari database sebelum menghapus record
    $stmt_select = $conn->prepare("SELECT gambar FROM berita WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    $berita_to_delete = $result_select->fetch_assoc();
    $stmt_select->close();

    if ($berita_to_delete) {
        $gambar_filename = $berita_to_delete['gambar'];
        // Hapus file gambar dari server
        if (!empty($gambar_filename) && file_exists("upload/image_berita/" . $gambar_filename)) {
            unlink("upload/image_berita/" . $gambar_filename);
        }
    }

    // Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM berita WHERE id = ?");
    $stmt_delete->bind_param("i", $id);

    if ($stmt_delete->execute()) {
        $message = "Berita berhasil dihapus.";
    } else {
        $message = "Error: " . $stmt_delete->error;
    }
    $stmt_delete->close();
}

// ==========================================================
// MENGAMBIL SEMUA DATA BERITA UNTUK DITAMPILKAN
// ==========================================================
$sql = "SELECT * FROM berita ORDER BY tanggal DESC";
$result = $conn->query($sql);
$berita_list = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $berita_list[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita & Artikel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
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
        .sidebar .nav-link {
            color: #adb5bd;
            font-weight: 500;
            transition: all 0.2s ease;
            padding: 1rem 1.5rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
            border-left: 4px solid #0d6efd;
            border-radius: 0;
        }
        .main-content {
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
        .form-control[type="file"] {
            padding: 0.75rem 0.5rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column p-3">
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
                <a href="manage_berita.php" class="nav-link active">
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
    <div class="main-content">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 fw-bold">Kelola Berita & Artikel</h1>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-2"></i> Tambah Berita Baru
            </button>
        </header>

        <!-- Pesan Sukses/Error -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tabel Daftar Berita -->
        <div class="card p-4">
            <h4 class="fw-bold mb-4">Daftar Berita</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Gambar</th>
                            <th scope="col">Author</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($berita_list) > 0): ?>
                            <?php foreach ($berita_list as $index => $berita): ?>
                                <tr>
                                    <th scope="row"><?php echo $index + 1; ?></th>
                                    <td><?php echo htmlspecialchars($berita['judul']); ?></td>
                                    <td>
                                        <img src="upload/image_berita/<?php echo htmlspecialchars($berita['gambar']); ?>" alt="Gambar Berita" style="width: 80px; height: auto; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($berita['author']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($berita['tanggal'])); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?php echo $berita['id']; ?>"
                                                data-judul="<?php echo htmlspecialchars($berita['judul']); ?>"
                                                data-isi-singkat="<?php echo htmlspecialchars($berita['isi_singkat']); ?>"
                                                data-isi-lengkap="<?php echo htmlspecialchars($berita['isi_lengkap']); ?>"
                                                data-gambar="<?php echo htmlspecialchars($berita['gambar']); ?>"
                                                data-author="<?php echo htmlspecialchars($berita['author']); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="manage_berita.php?delete_id=<?php echo $berita['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus berita ini?');">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada berita.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Berita -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_berita.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Tambah Berita Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="add_berita" value="1">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="isi_singkat" class="form-label">Isi Singkat</label>
                            <textarea class="form-control" id="isi_singkat" name="isi_singkat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="isi_lengkap" class="form-label">Isi Lengkap</label>
                            <textarea class="form-control" id="isi_lengkap" name="isi_lengkap" rows="6" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="gambar" class="form-label">Unggah Gambar</label>
                            <input type="file" class="form-control" id="gambar" name="gambar" required>
                        </div>
                        <div class="mb-3">
                            <label for="author" class="form-label">Penulis</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Berita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Berita -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_berita.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Berita</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_berita" value="1">
                        <input type="hidden" id="edit_id" name="id">
                        <input type="hidden" id="gambar_lama_input" name="gambar_lama">
                        <div class="mb-3">
                            <label for="edit_judul" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="edit_judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_isi_singkat" class="form-label">Isi Singkat</label>
                            <textarea class="form-control" id="edit_isi_singkat" name="isi_singkat" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_isi_lengkap" class="form-label">Isi Lengkap</label>
                            <textarea class="form-control" id="edit_isi_lengkap" name="isi_lengkap" rows="6" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gambar Saat Ini</label>
                            <div class="mb-2">
                                <img id="gambar_lama_preview" src="" alt="Gambar Saat Ini" style="max-width: 150px; border-radius: 4px;">
                            </div>
                            <label for="gambar_baru" class="form-label">Unggah Gambar Baru (Opsional)</label>
                            <input type="file" class="form-control" id="gambar_baru" name="gambar_baru">
                        </div>
                        <div class="mb-3">
                            <label for="edit_author" class="form-label">Penulis</label>
                            <input type="text" class="form-control" id="edit_author" name="author" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Mengisi data modal edit secara dinamis
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var judul = button.getAttribute('data-judul');
                var isiSingkat = button.getAttribute('data-isi-singkat');
                var isiLengkap = button.getAttribute('data-isi-lengkap');
                var gambar = button.getAttribute('data-gambar');
                var author = button.getAttribute('data-author');

                var modalBodyInputId = editModal.querySelector('#edit_id');
                var modalBodyInputJudul = editModal.querySelector('#edit_judul');
                var modalBodyInputIsiSingkat = editModal.querySelector('#edit_isi_singkat');
                var modalBodyInputIsiLengkap = editModal.querySelector('#edit_isi_lengkap');
                var modalBodyInputAuthor = editModal.querySelector('#edit_author');
                var modalBodyInputGambarLama = editModal.querySelector('#gambar_lama_input');
                var modalBodyPreviewGambar = editModal.querySelector('#gambar_lama_preview');

                modalBodyInputId.value = id;
                modalBodyInputJudul.value = judul;
                modalBodyInputIsiSingkat.value = isiSingkat;
                modalBodyInputIsiLengkap.value = isiLengkap;
                modalBodyInputAuthor.value = author;
                modalBodyInputGambarLama.value = gambar;
                modalBodyPreviewGambar.src = "upload/image_berita/" + gambar;
            });
        });
    </script>
</body>
</html>
