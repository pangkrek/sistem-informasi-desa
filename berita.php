<?php
// ==========================================================
// KODE INI ADALAH HALAMAN BERITA DINAMIS
// AKAN MENAMPILKAN DETAIL BERITA ATAU DAFTAR SEMUA BERITA
// ==========================================================

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
// Cek apakah ada ID berita di URL
// ==========================================================
$berita = null;
$berita_list = [];

if (isset($_GET['id'])) {
    // Jika ID ditemukan, ambil satu berita spesifik
    $id = clean_input($_GET['id']);
    
    // Gunakan Prepared Statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("SELECT * FROM berita WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $berita = $result->fetch_assoc();
    $stmt->close();

} else {
    // Jika tidak ada ID, ambil semua berita
    $sql = "SELECT id, judul, isi_singkat, gambar, tanggal, author FROM berita ORDER BY tanggal DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $berita_list[] = $row;
        }
    }
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_GET['id']) ? ($berita ? htmlspecialchars($berita['judul']) : 'Berita Tidak Ditemukan') : 'Semua Berita'; ?></title>
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
        .container {
            max-width: 800px;
        }
        .berita-image {
            width: 100%;
            height: auto;
            border-radius: 0.75rem;
            object-fit: cover;
        }
        .berita-content {
            background-color: #fff;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .card-berita {
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }
        .card-berita:hover {
            transform: translateY(-5px);
        }
        .card-berita .card-img-top {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .top-back-button {
            position: absolute;
            top: 20px;
            left: 20px;
        }
    </style>
</head>
<body>
    <div class="top-back-button">
        <a href="index.php" class="btn btn-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda</a>
    </div>

    <main class="container py-5">
        <?php if (isset($_GET['id'])): // Tampilkan detail satu berita ?>
            <?php if ($berita): ?>
                <div class="card p-4">
                    <img src="upload/image_berita/<?php echo htmlspecialchars($berita['gambar']); ?>" class="berita-image mb-4" alt="<?php echo htmlspecialchars($berita['judul']); ?>">
                    <div class="berita-content">
                        <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($berita['judul']); ?></h1>
                        <p class="text-muted small">
                            <i class="fas fa-user-edit me-1"></i> Penulis: <?php echo htmlspecialchars($berita['author']); ?> |
                            <i class="fas fa-calendar-alt me-1"></i> Tanggal: <?php echo date('d M Y', strtotime($berita['tanggal'])); ?>
                        </p>
                        <hr>
                        <p><?php echo nl2br(htmlspecialchars($berita['isi_lengkap'])); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    Berita tidak ditemukan.
                </div>
            <?php endif; ?>
        <?php else: // Tampilkan daftar semua berita ?>
            <h2 class="text-center fw-bold mb-4">Arsip Berita</h2>
            <div class="row g-4">
                <?php if (count($berita_list) > 0): ?>
                    <?php foreach ($berita_list as $berita_item): ?>
                        <div class="col-md-4">
                            <div class="card card-berita h-100">
                                <img src="upload/image_berita/<?php echo htmlspecialchars($berita_item['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($berita_item['judul']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($berita_item['judul']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <i class="fas fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($berita_item['tanggal'])); ?>
                                    </p>
                                    <p class="card-text"><?php echo htmlspecialchars($berita_item['isi_singkat']); ?></p>
                                    <a href="berita.php?id=<?php echo htmlspecialchars($berita_item['id']); ?>" class="btn btn-primary mt-2">Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">Belum ada berita yang tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> Desa Digital. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
