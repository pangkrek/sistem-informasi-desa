<?php
// ==========================================================
// KODE INI ADALAH HALAMAN GALERI PUBLIK
// Halaman ini menampilkan semua gambar galeri yang diunggah
// oleh administrator.
// ==========================================================

// Sertakan file koneksi database
require_once 'db.php';

// Ambil semua data galeri dari database
$galeri_list = [];
$sql_galeri = "SELECT * FROM galeri ORDER BY created_at DESC";
$result_galeri = $conn->query($sql_galeri);

if ($result_galeri->num_rows > 0) {
    while ($row = $result_galeri->fetch_assoc()) {
        $galeri_list[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Desa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 40px;
        }
        .galeri-item {
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s ease;
        }
        .galeri-item:hover {
            transform: scale(1.05);
        }
        .galeri-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .galeri-item:hover img {
            transform: scale(1.1);
        }
        .galeri-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 1rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            text-align: center;
        }
        .galeri-item:hover .galeri-overlay {
            transform: translateY(0);
        }
        .galeri-overlay h5 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        .galeri-overlay p {
            font-size: 0.9rem;
            margin: 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1 class="text-center fw-bold mb-5">Galeri Desa</h1>

        <?php if (empty($galeri_list)): ?>
            <div class="alert alert-info text-center">Belum ada gambar yang tersedia di galeri.</div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($galeri_list as $gambar): ?>
                    <div class="col">
                        <div class="galeri-item">
                            <img src="<?= htmlspecialchars($gambar['file_path']) ?>" alt="<?= htmlspecialchars($gambar['judul']) ?>">
                            <div class="galeri-overlay">
                                <h5><?= htmlspecialchars($gambar['judul']) ?></h5>
                                <p><?= htmlspecialchars($gambar['keterangan']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
