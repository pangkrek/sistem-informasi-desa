<?php
// ==========================================================
// PENGAMBILAN DATA DARI DATABASE
// Ganti detail koneksi di bawah ini dengan kredensial database Anda
// ==========================================================

$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$dbname = "sistem_desa"; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi dan hentikan eksekusi jika gagal
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// ==========================================================
// Kueri Data Berita Terbaru
// Diasumsikan ada tabel 'berita' dengan kolom 'judul', 'isi_singkat', 'gambar', 'tanggal'
// ==========================================================
$sql_berita = "SELECT judul, isi_singkat, gambar, tanggal FROM berita ORDER BY tanggal DESC LIMIT 3";
$result_berita = $conn->query($sql_berita);

$berita_terbaru = [];
if ($result_berita && $result_berita->num_rows > 0) {
    while ($row = $result_berita->fetch_assoc()) {
        $berita_terbaru[] = $row;
    }
}

// ==========================================================
// Kueri Data Anggaran dari Tabel 'anggaran'
// Diasumsikan tabel 'anggaran' memiliki kolom 'bidang', 'rencana_anggaran', 'realisasi_anggaran'
// ==========================================================
$sql_anggaran = "SELECT bidang, rencana_anggaran, realisasi_anggaran FROM anggaran ORDER BY bidang";
$result_anggaran = $conn->query($sql_anggaran);

$data_anggaran = [];
if ($result_anggaran && $result_anggaran->num_rows > 0) {
    while($row = $result_anggaran->fetch_assoc()) {
        $data_anggaran[] = $row;
    }
}


// ==========================================================
// Kueri Data untuk Bagian Kependudukan Utama
// ==========================================================

// 1. Kueri untuk Data Penduduk per Dusun
$sql_dusun = "SELECT dusun, COUNT(*) AS jumlah FROM penduduk GROUP BY dusun ORDER BY dusun";
$result_dusun = $conn->query($sql_dusun);

$penduduk_dusun = [];
if ($result_dusun && $result_dusun->num_rows > 0) {
    while($row = $result_dusun->fetch_assoc()) {
        $penduduk_dusun[] = $row;
    }
}
$total_penduduk_dusun = array_sum(array_column($penduduk_dusun, 'jumlah'));

// 2. Kueri untuk Perbandingan Laki-laki dan Perempuan
$sql_kelamin = "SELECT jenis_kelamin, COUNT(*) AS jumlah FROM penduduk GROUP BY jenis_kelamin";
$result_kelamin = $conn->query($sql_kelamin);

$penduduk_kelamin = ['Laki-laki' => 0, 'Perempuan' => 0];
if ($result_kelamin && $result_kelamin->num_rows > 0) {
    while($row = $result_kelamin->fetch_assoc()) {
        $penduduk_kelamin[$row['jenis_kelamin']] = $row['jumlah'];
    }
}
$total_penduduk_kelamin = $penduduk_kelamin['Laki-laki'] + $penduduk_kelamin['Perempuan'];
$persen_laki_laki = ($total_penduduk_kelamin > 0) ? round(($penduduk_kelamin['Laki-laki'] / $total_penduduk_kelamin) * 100) : 0;
$persen_perempuan = ($total_penduduk_kelamin > 0) ? round(($penduduk_kelamin['Perempuan'] / $total_penduduk_kelamin) * 100) : 0;

// ==========================================================
// Kueri Data untuk Bagian Demografi Tambahan
// ==========================================================

// 3. Kueri untuk Pendidikan Terakhir
$sql_pendidikan = "SELECT pendidikan_terakhir, COUNT(*) AS jumlah FROM penduduk GROUP BY pendidikan_terakhir ORDER BY jumlah DESC";
$result_pendidikan = $conn->query($sql_pendidikan);

$penduduk_pendidikan = [];
if ($result_pendidikan && $result_pendidikan->num_rows > 0) {
    while($row = $result_pendidikan->fetch_assoc()) {
        $penduduk_pendidikan[] = $row;
    }
}
$total_penduduk_pendidikan = array_sum(array_column($penduduk_pendidikan, 'jumlah'));

// 4. Kueri untuk Jenis Pekerjaan
$sql_pekerjaan = "SELECT pekerjaan, COUNT(*) AS jumlah FROM penduduk GROUP BY pekerjaan ORDER BY jumlah DESC";
$result_pekerjaan = $conn->query($sql_pekerjaan);

$penduduk_pekerjaan = [];
if ($result_pekerjaan && $result_pekerjaan->num_rows > 0) {
    while($row = $result_pekerjaan->fetch_assoc()) {
        $penduduk_pekerjaan[] = $row;
    }
}
$total_penduduk_pekerjaan = array_sum(array_column($penduduk_pekerjaan, 'jumlah'));

// 5. Kueri untuk Status Perkawinan
$sql_perkawinan = "SELECT status_perkawinan, COUNT(*) AS jumlah FROM penduduk GROUP BY status_perkawinan ORDER BY jumlah DESC";
$result_perkawinan = $conn->query($sql_perkawinan);

$penduduk_perkawinan = [];
if ($result_perkawinan && $result_perkawinan->num_rows > 0) {
    while($row = $result_perkawinan->fetch_assoc()) {
        $penduduk_perkawinan[] = $row;
    }
}
$total_penduduk_perkawinan = array_sum(array_column($penduduk_perkawinan, 'jumlah'));

// ==========================================================
// Kueri Data Perangkat Desa
// Diasumsikan ada tabel 'perangkat_desa' dengan kolom 'nama', 'jabatan'
// ==========================================================
$sql_perangkat_desa = "SELECT nama, jabatan FROM perangkat_desa ORDER BY id ASC"; // Order by ID atau urutan yang logis
$result_perangkat_desa = $conn->query($sql_perangkat_desa);

$perangkat_desa = [];
if ($result_perangkat_desa && $result_perangkat_desa->num_rows > 0) {
    while ($row = $result_perangkat_desa->fetch_assoc()) {
        $perangkat_desa[] = $row;
    }
}


// Tutup koneksi database
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Desa</title>
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
            scroll-behavior: smooth;
        }
        .navbar-brand {
            font-weight: 700;
        }
        /* Style untuk Carousel */
        .carousel-item {
            height: 100vh;
            min-height: 350px;
            background: no-repeat center center scroll;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            position: relative;
        }
        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 2rem;
            border-radius: 0.75rem;
            bottom: 20%;
        }
        .section-title {
            font-weight: 700;
            margin-bottom: 3rem;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-radius: 0.75rem;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .accordion-item {
            border: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .footer {
            background-color: #212529;
            color: #ced4da;
        }
        section {
            padding: 5rem 0;
        }
        .info-card {
            background-color: #fff;
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .info-card h4 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        /* Style untuk chart kependudukan */
        .chart-label {
            display: flex;
            justify-content: space-between;
            font-weight: 500;
        }
        .gender-progress .progress-bar-male {
            background-color: #0d6efd;
        }
        .gender-progress .progress-bar-female {
            background-color: #dc3545;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-home me-2"></i> Desa Maju jaya
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#profil">Profil Desa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#berita_terbaru">Berita</a>
                    </li>
                    <!-- Tambahan menu Galeri -->
                    <li class="nav-item">
                        <a class="nav-link" href="galeri.php">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pemerintahan">Pemerintahan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#potensi">Potensi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#anggaran">Anggaran</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kependudukan">Kependudukan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#layanan">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#kontak">Kontak</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-light" href="login.php">Login Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section dengan Carousel -->
    <header id="beranda">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <!-- Slide 1 -->
                <div class="carousel-item active" style="background-image: url('https://placehold.co/1920x1080/4F46E5/ffffff?text=Pemandangan+Desa')">
                    <div class="carousel-caption d-none d-md-block text-center">
                        <h1 class="display-3 fw-bold">Selamat Datang di Desa Maju Jaya</h1>
                        <p class="lead mt-3">Membangun desa yang makmur, sejahtera, dan berbudaya.</p>
                        <a href="#profil" class="btn btn-lg btn-primary mt-4 rounded-pill">Pelajari Lebih Lanjut</a>
                    </div>
                </div>
                <!-- Slide 2 -->
                <div class="carousel-item" style="background-image: url('https://placehold.co/1920x1080/465EE5/ffffff?text=Kegiatan+Masyarakat')">
                    <div class="carousel-caption d-none d-md-block text-center">
                        <h1 class="display-3 fw-bold">Potensi Alam yang Menawan</h1>
                        <p class="lead mt-3">Sawah yang subur dan keindahan alam yang tak ternilai.</p>
                        <a href="#potensi" class="btn btn-lg btn-success mt-4 rounded-pill">Jelajahi Potensi</a>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="carousel-item" style="background-image: url('https://placehold.co/1920x1080/E54655/ffffff?text=Kerja+Bakti')">
                    <div class="carousel-caption d-none d-md-block text-center">
                        <h1 class="display-3 fw-bold">Gotong Royong dalam Harmoni</h1>
                        <p class="lead mt-3">Kebersamaan adalah kunci kemajuan desa kami.</p>
                        <a href="#profil" class="btn btn-lg btn-warning mt-4 rounded-pill">Tentang Kami</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </header>

    <!-- Profil Desa Section -->
    <section id="profil" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center">Profil Desa</h2>
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="https://placehold.co/600x400/ced4da/212529?text=Pemandangan+Desa" class="img-fluid rounded-4 shadow" alt="Pemandangan Desa">
                </div>
                <div class="col-md-6">
                    <h3 class="fw-bold">Sejarah dan Visi Misi</h3>
                    <p>Desa Maju Jaya didirikan pada tahun 1985 dan sejak saat itu terus berkembang menjadi desa yang mandiri. Visi kami adalah menciptakan masyarakat yang berdaya saing tinggi, didukung oleh nilai-nilai kearifan lokal. Kami berkomitmen untuk meningkatkan kualitas hidup warga melalui pembangunan yang berkelanjutan.</p>
                    <p>Misi kami meliputi peningkatan infrastruktur, pemberdayaan ekonomi masyarakat, dan pelestarian lingkungan serta budaya.</p>
                    <a href="#" class="btn btn-outline-dark mt-3">Lihat Selengkapnya</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Berita Terbaru Section -->
    <section id="berita_terbaru">
        <div class="container">
            <h2 class="section-title text-center">Berita Terbaru</h2>
            <div class="row g-4">
                <?php if (!empty($berita_terbaru)) { ?>
                    <?php foreach ($berita_terbaru as $berita) { ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($berita['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($berita['judul']); ?>" onerror="this.src='https://placehold.co/600x400/ced4da/212529?text=Gambar+Tidak+Tersedia'">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($berita['judul']); ?></h5>
                                    <p class="card-text text-muted small"><i class="fa-solid fa-calendar me-1"></i><?php echo htmlspecialchars($berita['tanggal']); ?></p>
                                    <p class="card-text"><?php echo htmlspecialchars($berita['isi_singkat']); ?></p>
                                    <a href="#" class="btn btn-sm btn-primary mt-2">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-muted text-center col-12">Belum ada berita terbaru saat ini.</p>
                <?php } ?>
            </div>
            <div class="text-center mt-5">
                <a href="berita.php" class="btn btn-outline-primary">Lihat Semua Berita</a>
            </div>
        </div>
    </section>

    <!-- Pemerintahan Section -->
    <section id="pemerintahan">
        <div class="container">
            <h2 class="section-title text-center">Struktur Pemerintahan Desa</h2>
            <div class="text-center mb-5">
                <!-- Gambar diagram struktur bisa tetap statis atau dinamis jika ada data di DB -->
                <img src="https://placehold.co/1000x300/4F46E5/ffffff?text=Diagram+Struktur+Pemerintahan+Desa" class="img-fluid rounded-4 shadow" alt="Diagram Struktur Pemerintahan Desa">
            </div>
            <div class="row justify-content-center">
                <?php if (!empty($perangkat_desa)) { ?>
                    <?php foreach ($perangkat_desa as $perangkat) { ?>
                        <div class="col-md-4 text-center mb-4">
                            <!-- Placeholder untuk foto perangkat desa. Anda bisa menambahkan kolom 'foto' di tabel perangkat_desa jika diperlukan. -->
                            <img src="https://placehold.co/150x150/505050/ffffff?text=<?= urlencode(substr($perangkat['nama'], 0, 1)) ?>" class="rounded-circle mb-3 shadow-sm" alt="<?= htmlspecialchars($perangkat['nama']) ?>">
                            <h4 class="fw-bold"><?= htmlspecialchars($perangkat['nama']) ?></h4>
                            <p class="text-muted"><?= htmlspecialchars($perangkat['jabatan']) ?></p>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p class="text-muted text-center col-12">Data perangkat desa tidak tersedia.</p>
                <?php } ?>
            </div>
            <div class="text-center mt-4">
                <a href="perangkat_desa.php" class="btn btn-outline-primary">Lihat Seluruh Perangkat Desa</a>
            </div>
        </div>
    </section>

    <!-- Potensi Desa Section -->
    <section id="potensi" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center">Potensi Desa</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <i class="fa-solid fa-leaf text-success fa-3x mb-3"></i>
                            <h5 class="card-title fw-bold">Potensi Alam</h5>
                            <p class="card-text">Desa kami dikelilingi oleh sawah yang subur dan perkebunan teh yang indah. Potensi agrowisata sangat besar.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <i class="fa-solid fa-masks-theater text-warning fa-3x mb-3"></i>
                            <h5 class="card-title fw-bold">Potensi Budaya</h5>
                            <p class="card-text">Kami memiliki berbagai tradisi dan kesenian lokal yang masih terjaga, seperti tari-tarian dan kerajinan tangan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center h-100 p-4">
                        <div class="card-body">
                            <i class="fa-solid fa-store text-info fa-3x mb-3"></i>
                            <h5 class="card-title fw-bold">Potensi UMKM</h5>
                            <p class="card-text">Produk unggulan desa kami antara lain keripik singkong, gula aren, dan kain tenun tradisional.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Anggaran Transparansi Desa Section -->
    <section id="anggaran">
        <div class="container">
            <h2 class="section-title text-center">Anggaran Transparansi Desa & Dana Desa</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-success">Rencana Anggaran dan Biaya (RAB) Tahun 2024</h4>
                        <p class="text-muted">Pembangunan desa yang transparan dan akuntabel.</p>
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($data_anggaran)) { ?>
                                <?php foreach ($data_anggaran as $data) { ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($data['bidang']); ?>
                                        <span class="badge bg-primary rounded-pill">Rp <?php echo number_format($data['rencana_anggaran']); ?></span>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <li class="list-group-item">Data rencana anggaran tidak tersedia.</li>
                            <?php } ?>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-outline-success">Lihat Rincian Lengkap</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-info">Realisasi Anggaran Terkini</h4>
                        <p class="text-muted">Data realisasi anggaran hingga triwulan terakhir.</p>
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($data_anggaran)) { ?>
                                <?php foreach ($data_anggaran as $data) { 
                                    $rencana = $data['rencana_anggaran'];
                                    $realisasi = $data['realisasi_anggaran'];
                                    $persentase = ($rencana > 0) ? round(($realisasi / $rencana) * 100) : 0;
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($data['bidang']); ?>
                                        <span class="badge bg-secondary rounded-pill"><?php echo $persentase; ?>%</span>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <li class="list-group-item">Data realisasi anggaran tidak tersedia.</li>
                            <?php } ?>
                        </ul>
                        <div class="text-center mt-4">
                            <a href="#" class="btn btn-outline-info">Lihat Realisasi Lengkap</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Data Kependudukan Section -->
    <section id="kependudukan" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center">Data Demografi Kependudukan</h2>
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-primary">Jumlah Penduduk per Dusun</h4>
                        <!-- Diagram Penduduk per Dusun (dinamis dari database) -->
                        <?php if (!empty($penduduk_dusun)) { ?>
                            <?php foreach ($penduduk_dusun as $data) { ?>
                                <?php $persentase = ($total_penduduk_dusun > 0) ? round(($data['jumlah'] / $total_penduduk_dusun) * 100) : 0; ?>
                                <div class="mb-3">
                                    <div class="chart-label">
                                        <span><?php echo htmlspecialchars($data['dusun']); ?></span>
                                        <span><?php echo number_format($data['jumlah']); ?> orang (<?php echo $persentase; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted text-center">Data penduduk per dusun tidak tersedia.</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-primary">Perbandingan Laki-laki dan Perempuan</h4>
                        <!-- Diagram Perbandingan Laki-laki dan Perempuan (dinamis dari database) -->
                        <?php if ($total_penduduk_kelamin > 0) { ?>
                            <div class="row text-center mb-3">
                                <div class="col">
                                    <i class="fas fa-male fa-2x text-primary d-block mb-2"></i>
                                    <p class="fw-bold mb-0">Laki-laki</p>
                                    <span><?php echo number_format($penduduk_kelamin['Laki-laki']); ?> orang (<?php echo $persen_laki_laki; ?>%)</span>
                                </div>
                                <div class="col">
                                    <i class="fas fa-female fa-2x text-danger d-block mb-2"></i>
                                    <p class="fw-bold mb-0">Perempuan</p>
                                    <span><?php echo number_format($penduduk_kelamin['Perempuan']); ?> orang (<?php echo $persen_perempuan; ?>%)</span>
                                </div>
                            </div>
                            <div class="progress gender-progress" style="height: 30px;">
                                <div class="progress-bar progress-bar-male" role="progressbar" style="width: <?php echo $persen_laki_laki; ?>%;" aria-valuenow="<?php echo $persen_laki_laki; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                <div class="progress-bar progress-bar-female" role="progressbar" style="width: <?php echo $persen_perempuan; ?>%;" aria-valuenow="<?php echo $persen_perempuan; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        <?php } else { ?>
                            <p class="text-muted text-center">Data perbandingan jenis kelamin tidak tersedia.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="info-card h-100">
                        <h4 class="text-success">Berdasarkan Pendidikan Terakhir</h4>
                        <?php if (!empty($penduduk_pendidikan)) { ?>
                            <?php foreach ($penduduk_pendidikan as $data) { 
                                $persentase = ($total_penduduk_pendidikan > 0) ? round(($data['jumlah'] / $total_penduduk_pendidikan) * 100) : 0; ?>
                                <div class="mb-3">
                                    <div class="chart-label">
                                        <span><?php echo htmlspecialchars($data['pendidikan_terakhir']); ?></span>
                                        <span><?php echo number_format($data['jumlah']); ?> (<?php echo $persentase; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted text-center">Data pendidikan tidak tersedia.</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card h-100">
                        <h4 class="text-warning">Berdasarkan Jenis Pekerjaan</h4>
                        <?php if (!empty($penduduk_pekerjaan)) { ?>
                            <?php foreach ($penduduk_pekerjaan as $data) { 
                                $persentase = ($total_penduduk_pekerjaan > 0) ? round(($data['jumlah'] / $total_penduduk_pekerjaan) * 100) : 0; ?>
                                <div class="mb-3">
                                    <div class="chart-label">
                                        <span><?php echo htmlspecialchars($data['pekerjaan']); ?></span>
                                        <span><?php echo number_format($data['jumlah']); ?> (<?php echo $persentase; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted text-center">Data pekerjaan tidak tersedia.</p>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-card h-100">
                        <h4 class="text-info">Berdasarkan Status Perkawinan</h4>
                        <?php if (!empty($penduduk_perkawinan)) { ?>
                            <?php foreach ($penduduk_perkawinan as $data) { 
                                $persentase = ($total_penduduk_perkawinan > 0) ? round(($data['jumlah'] / $total_penduduk_perkawinan) * 100) : 0; ?>
                                <div class="mb-3">
                                    <div class="chart-label">
                                        <span><?php echo htmlspecialchars($data['status_perkawinan']); ?></span>
                                        <span><?php echo number_format($data['jumlah']); ?> (<?php echo $persentase; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 15px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-muted text-center">Data status perkawinan tidak tersedia.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Layanan Section -->
    <section id="layanan">
        <div class="container">
            <h2 class="section-title text-center">Layanan Online</h2>
            <div class="accordion" id="layananAccordion">
                <!-- Tambahan: Layanan Mandiri -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingLayananMandiri">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLayananMandiri" aria-expanded="false" aria-controls="collapseLayananMandiri">
                            <i class="fas fa-user-circle me-2 text-primary"></i> Layanan Mandiri
                        </button>
                    </h2>
                    <div id="collapseLayananMandiri" class="accordion-collapse collapse" aria-labelledby="headingLayananMandiri" data-bs-parent="#layananAccordion">
                        <div class="accordion-body">
                            <p>Masuk ke akun layanan mandiri Anda untuk mengajukan berbagai surat dan melihat data pribadi.</p>
                            <!-- Pesan Error Login -->
                            <?php if (isset($_GET['error']) && $_GET['error'] == 'login_failed') { ?>
                                <div class="alert alert-danger" role="alert">
                                    NIK atau PIN salah. Silakan coba lagi.
                                </div>
                            <?php } ?>
                            <!-- FORM DIARAHKAN KE proses_login_penduduk.php -->
                            <form action="login_action.php" method="POST">
                                <div class="mb-3">
                                    <label for="nik" class="form-label">Nomor Induk Kependudukan (NIK)</label>
                                    <input type="text" class="form-control" id="nik" name="nik" placeholder="Masukkan NIK" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pin" class="form-label">PIN</label>
                                    <input type="password" class="form-control" id="pin" name="pin" placeholder="Masukkan PIN" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Accordion Item 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <i class="fas fa-file-invoice me-2 text-primary"></i> Surat Keterangan Usaha
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#layananAccordion">
                        <div class="accordion-body">
                            <p>Layanan untuk membuat surat keterangan yang menyatakan bahwa seseorang memiliki usaha di wilayah desa. Dokumen yang diperlukan: KTP, KK, dan surat pengantar RT/RW.</p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Ajukan Sekarang</a>
                        </div>
                    </div>
                </div>
                <!-- Accordion Item 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="fas fa-handshake me-2 text-success"></i> Surat Pengantar Nikah
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#layananAccordion">
                        <div class="accordion-body">
                            <p>Layanan untuk mendapatkan surat pengantar yang diperlukan untuk mengurus pernikahan di Kantor Urusan Agama (KUA). Dokumen yang diperlukan: KTP, KK, dan formulir N1-N4 dari desa.</p>
                            <a href="#" class="btn btn-outline-success btn-sm">Ajukan Sekarang</a>
                        </div>
                    </div>
                </div>
                <!-- Accordion Item 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="fas fa-user-times me-2 text-danger"></i> Surat Keterangan Domisili
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#layananAccordion">
                        <div class="accordion-body">
                            <p>Layanan untuk mendapatkan surat keterangan domisili bagi penduduk baru atau yang membutuhkan. Dokumen yang diperlukan: KTP, KK, dan surat pindah (jika ada).</p>
                            <a href="#" class="btn btn-outline-danger btn-sm">Ajukan Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Kontak Section -->
    <section id="kontak" class="bg-light">
        <div class="container">
            <h2 class="section-title text-center">Hubungi Kami</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-dark">Kantor Desa</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <strong>Alamat:</strong> Jalan Pembangunan No. 123, Desa Maju Jaya</li>
                            <li class="mb-2"><i class="fas fa-envelope me-2 text-success"></i> <strong>Email:</strong> desamajujaya@gmail.com</li>
                            <li class="mb-2"><i class="fas fa-phone me-2 text-info"></i> <strong>Telepon:</strong> (021) 12345678</li>
                        </ul>
                        <h4 class="text-dark mt-4">Jam Pelayanan</h4>
                        <ul class="list-unstyled">
                            <li>Senin - Jumat: 08.00 - 16.00 WIB</li>
                            <li>Sabtu, Minggu, Hari Libur: Tutup</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-card h-100">
                        <h4 class="text-dark">Lokasi Kami</h4>
                        <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.9100412354784!2d107.01894081534063!3d-6.903848195155702!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69315d16e872d5%3A0xe96503b8e4e9f783!2sKantor%20Desa%20Maju%20Jaya!5e0!3m2!1sid!2sid!4v1625471206161!5m2!1sid!2sid" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-6 text-center text-lg-start mb-3 mb-lg-0">
                    <p class="mb-0">&copy; 2024 Desa Maju Jaya. All Rights Reserved.</p>
                </div>
                <div class="col-lg-6 text-center text-lg-end">
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-twitter fa-2x"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="fab fa-instagram fa-2x"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" xintegrity="sha384-I7E8VVD/ismYTF4y9l7Jq6gI6M55PqR+W+LgC9pY2gXy1Z28f2A/s" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" xintegrity="sha384-B4gt1jrGC7Jh4AgBf3J8T5O2C9o2a+4T7y7I2z7T+M3E8h8+H4gJ7E" crossorigin="anonymous"></script>

</body>
</html>
