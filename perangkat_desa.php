<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struktur Perangkat Desa Maju Jaya</title>
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
        .navbar-brand {
            font-weight: 700;
        }
        .hero-title {
            padding-top: 6rem;
            padding-bottom: 2rem;
            background-color: #f0f2f5;
        }
        .member-card {
            text-align: center;
            padding: 1.5rem;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-radius: 0.75rem;
        }
        .member-card:hover {
            transform: translateY(-5px);
        }
        .member-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .footer {
            background-color: #212529;
            color: #ced4da;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fa-solid fa-village me-2"></i>Desa Maju Jaya
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="index.php">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Beranda
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Judul Halaman -->
    <section class="hero-title">
        <div class="container text-center">
            <h1 class="fw-bold">Struktur Perangkat Desa</h1>
            <p class="lead text-muted">Mengenal lebih dekat para pemimpin dan staf yang melayani masyarakat Desa Maju Jaya.</p>
        </div>
    </section>

    <!-- Detail Perangkat Desa -->
    <section id="perangkat-desa" class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Kepala Desa -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Agus+Setiawan" class="img-fluid" alt="Kepala Desa">
                        <h5 class="fw-bold mt-3">Bapak Agus Setiawan</h5>
                        <p class="text-muted">Kepala Desa</p>
                    </div>
                </div>
                <!-- Sekretaris Desa -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Rina+Wati" class="img-fluid" alt="Sekretaris Desa">
                        <h5 class="fw-bold mt-3">Ibu Rina Wati</h5>
                        <p class="text-muted">Sekretaris Desa</p>
                    </div>
                </div>
                <!-- Kaur Keuangan -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Budi+Santoso" class="img-fluid" alt="Kaur Keuangan">
                        <h5 class="fw-bold mt-3">Bapak Budi Santoso</h5>
                        <p class="text-muted">Kepala Urusan Keuangan</p>
                    </div>
                </div>
                <!-- Kaur Tata Usaha dan Umum -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Siti+Nurhayati" class="img-fluid" alt="Kaur Tata Usaha dan Umum">
                        <h5 class="fw-bold mt-3">Ibu Siti Nurhayati</h5>
                        <p class="text-muted">Kepala Urusan Tata Usaha dan Umum</p>
                    </div>
                </div>
                <!-- Kasi Pemerintahan -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Joko+Susanto" class="img-fluid" alt="Kasi Pemerintahan">
                        <h5 class="fw-bold mt-3">Bapak Joko Susanto</h5>
                        <p class="text-muted">Kepala Seksi Pemerintahan</p>
                    </div>
                </div>
                <!-- Kasi Kesejahteraan -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Ani+Prasetyo" class="img-fluid" alt="Kasi Kesejahteraan">
                        <h5 class="fw-bold mt-3">Ibu Ani Prasetyo</h5>
                        <p class="text-muted">Kepala Seksi Kesejahteraan</p>
                    </div>
                </div>
                <!-- Kasi Pelayanan -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Herman+Sudiro" class="img-fluid" alt="Kasi Pelayanan">
                        <h5 class="fw-bold mt-3">Bapak Herman Sudiro</h5>
                        <p class="text-muted">Kepala Seksi Pelayanan</p>
                    </div>
                </div>
                <!-- Kepala Dusun I -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Wawan+Kurniawan" class="img-fluid" alt="Kepala Dusun I">
                        <h5 class="fw-bold mt-3">Bapak Wawan Kurniawan</h5>
                        <p class="text-muted">Kepala Dusun I</p>
                    </div>
                </div>
                <!-- Kepala Dusun II -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="member-card">
                        <!-- Menggunakan placeholder foto spesifik -->
                        <img src="https://placehold.co/150x150/505050/ffffff?text=Sri+Handayani" class="img-fluid" alt="Kepala Dusun II">
                        <h5 class="fw-bold mt-3">Ibu Sri Handayani</h5>
                        <p class="text-muted">Kepala Dusun II</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0 text-center text-md-start">
                    &copy; 2024 Desa Maju Jaya. All Rights Reserved.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white mx-2"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white mx-2"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
