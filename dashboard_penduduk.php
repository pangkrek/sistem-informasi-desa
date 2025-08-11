<?php 
session_start(); 

// ==========================================================
// PENGAMANAN HALAMAN
// Periksa apakah pengguna sudah login. Jika tidak, arahkan kembali ke halaman login.
// ==========================================================
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
} 

// Sertakan file koneksi database
require_once 'db.php'; 

// ==========================================================
// PENGAMBILAN DATA PENGGUNA DARI SESSION & DATABASE
// ==========================================================
$nik_user = isset($_SESSION['nik']) ? $_SESSION['nik'] : 'N/A'; 

// --- PERUBAHAN DI SINI ---
// Query untuk mengambil nama lengkap dari tabel penduduk
$sql_nama_lengkap = "SELECT nama_lengkap FROM penduduk WHERE nik = ?"; 
$stmt_nama_lengkap = $conn->prepare($sql_nama_lengkap); 
if ($stmt_nama_lengkap === false) { 
    // Tangani kesalahan jika query gagal
    die("Kesalahan saat menyiapkan query nama lengkap: " . $conn->error); 
} 
$stmt_nama_lengkap->bind_param("s", $nik_user); 
$stmt_nama_lengkap->execute(); 
$result_nama_lengkap = $stmt_nama_lengkap->get_result(); 

$nama_user = 'Pengguna'; // Nilai default jika nama tidak ditemukan
if ($result_nama_lengkap->num_rows > 0) { 
    $row_nama_lengkap = $result_nama_lengkap->fetch_assoc(); 
    $nama_user = htmlspecialchars($row_nama_lengkap['nama_lengkap']); 
} 
$stmt_nama_lengkap->close(); 
// --- AKHIR PERUBAHAN ---

// ==========================================================
// Kueri Data Riwayat Pengajuan Surat
// Kueri ini MENGAMBIL DATA DARI TABEL `permintaan_surat`
// ==========================================================
$sql_riwayat = "SELECT id, jenis_surat, tanggal_pengajuan, status FROM permintaan_surat WHERE nik = ? ORDER BY tanggal_pengajuan DESC"; 

$stmt_riwayat = $conn->prepare($sql_riwayat); 
if ($stmt_riwayat === false) { 
    die("Kesalahan saat menyiapkan query riwayat: " . $conn->error); 
} 
$stmt_riwayat->bind_param("s", $nik_user); 
$stmt_riwayat->execute(); 
$result_riwayat = $stmt_riwayat->get_result(); 

$riwayat_pengajuan = []; 
if ($result_riwayat->num_rows > 0) { 
    while($row = $result_riwayat->fetch_assoc()) { 
        $riwayat_pengajuan[] = $row; 
    } 
} 
$stmt_riwayat->close(); 

// ==========================================================
// Kueri Data Statistik Pengajuan
// Kueri ini MENGAMBIL DATA DARI TABEL `permintaan_surat`
// ==========================================================
$sql_stats_total = "SELECT COUNT(*) as total FROM permintaan_surat WHERE nik = ?"; 
$stmt_stats_total = $conn->prepare($sql_stats_total); 
if ($stmt_stats_total === false) { 
    die("Kesalahan saat menyiapkan query statistik total: " . $conn->error); 
} 
$stmt_stats_total->bind_param("s", $nik_user); 
$stmt_stats_total->execute(); 
$result_stats_total = $stmt_stats_total->get_result(); 
$stats_total = $result_stats_total->fetch_assoc()['total']; 
$stmt_stats_total->close(); 

$sql_stats_diproses = "SELECT COUNT(*) as diproses FROM permintaan_surat WHERE nik = ? AND status = 'Diproses'"; 
$stmt_stats_diproses = $conn->prepare($sql_stats_diproses); 
if ($stmt_stats_diproses === false) { 
    die("Kesalahan saat menyiapkan query statistik diproses: " . $conn->error); 
} 
$stmt_stats_diproses->bind_param("s", $nik_user); 
$stmt_stats_diproses->execute(); 
$result_stats_diproses = $stmt_stats_diproses->get_result(); 
$stats_diproses = $result_stats_diproses->fetch_assoc()['diproses']; 
$stmt_stats_diproses->close(); 

$sql_stats_selesai = "SELECT COUNT(*) as selesai FROM permintaan_surat WHERE nik = ? AND status = 'Selesai'"; 
$stmt_stats_selesai = $conn->prepare($sql_stats_selesai); 
if ($stmt_stats_selesai === false) { 
    die("Kesalahan saat menyiapkan query statistik selesai: " . $conn->error); 
} 
$stmt_stats_selesai->bind_param("s", $nik_user); 
$stmt_stats_selesai->execute(); 
$result_stats_selesai = $stmt_stats_selesai->get_result(); 
$stats_selesai = $result_stats_selesai->fetch_assoc()['selesai']; 
$stmt_stats_selesai->close(); 

$conn->close(); 
?> 
<!DOCTYPE html> 
<html lang="id"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Dashboard Penduduk</title> 
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
        .sidebar { 
            width: 250px; 
            background-color: #343a40; 
            color: #fff; 
            height: 100vh; 
            position: fixed; 
            top: 0; 
            left: 0; 
            padding-top: 20px; 
            z-index: 1000; 
            overflow-y: auto; 
        } 
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block; 
            border-left: 5px solid transparent; 
        } 
        .sidebar a:hover, .sidebar a.active { 
            color: #fff; 
            background-color: #495057; 
            border-left: 5px solid #0d6efd; 
        } 
        .content { 
            margin-left: 250px; 
            padding: 20px; 
        } 
        .card { 
            border: none; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
            border-radius: 0.75rem; 
        } 
        .card-header { 
            background-color: #0d6efd; 
            color: #fff; 
            border-radius: 0.75rem 0.75rem 0 0; 
        } 
        .btn-logout { 
            width: 100%; 
        } 
        .modal-header { 
            background-color: #0d6efd; 
            color: #fff; 
            border-bottom: none; 
        } 
        .modal-header .btn-close { 
            filter: invert(1); 
        } 
    </style> 
</head> 
<body> 

    <!-- Sidebar --> 
    <div class="sidebar"> 
        <h3 class="text-center text-white mb-4">Dashboard</h3> 
        <div class="text-center mb-4"> 
            <img src="https://placehold.co/80x80/6c757d/ffffff?text=<?php echo substr($nama_user, 0, 1); ?>" class="rounded-circle mb-2" alt="Avatar"> 
            <!-- NAMA LENGKAP -->
            <h5 class="text-white"><?php echo $nama_user; ?></h5> 
            <!-- NIK -->
            <p class="text-muted small"><?php echo $nik_user; ?></p> 
        </div> 
        <ul class="nav flex-column"> 
            <li class="nav-item"> 
                <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-2"></i> Beranda</a> 
            </li> 
            <li class="nav-item"> 
                <a class="nav-link" href="#layanan-surat"><i class="fas fa-file-alt me-2"></i> Layanan Surat</a> 
            </li> 
            <li class="nav-item"> 
                <a class="nav-link" href="#riwayat-pengajuan"><i class="fas fa-history me-2"></i> Riwayat Pengajuan</a> 
            </li> 
        </ul> 
        <div class="p-3"> 
            <a href="logout.php" class="btn btn-outline-light btn-logout"><i class="fas fa-sign-out-alt me-2"></i> Keluar</a> 
        </div> 
    </div> 

    <!-- Main Content --> 
    <div class="content"> 
        <!-- Header --> 
        <div class="d-flex justify-content-between align-items-center mb-4"> 
            <h1 class="fw-bold">Selamat Datang, <?php echo $nama_user; ?>!</h1> 
        </div> 
        
        <!-- Pesan Sukses atau Error --> 
        <?php if (isset($_GET['success']) && $_GET['success'] == 'submitted'): ?> 
            <div class="alert alert-success alert-dismissible fade show" role="alert"> 
                Pengajuan surat berhasil dikirim! Silakan tunggu prosesnya. 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> 
            </div> 
        <?php elseif (isset($_GET['error'])): ?> 
            <div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                Pengajuan surat gagal. Mohon coba lagi atau hubungi admin. 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> 
            </div> 
        <?php endif; ?> 

        <!-- Statistik Singkat --> 
        <div class="row mb-5"> 
            <div class="col-md-4"> 
                <div class="card p-3 text-center bg-primary text-white"> 
                    <div class="card-body"> 
                        <i class="fas fa-file-upload fa-3x mb-3"></i> 
                        <h5 class="card-title">Total Pengajuan</h5> 
                        <p class="card-text fs-2 fw-bold"><?php echo number_format($stats_total); ?></p> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card p-3 text-center bg-warning text-dark"> 
                    <div class="card-body"> 
                        <i class="fas fa-hourglass-half fa-3x mb-3"></i> 
                        <h5 class="card-title">Pengajuan Diproses</h5> 
                        <p class="card-text fs-2 fw-bold"><?php echo number_format($stats_diproses); ?></p> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card p-3 text-center bg-success text-white"> 
                    <div class="card-body"> 
                        <i class="fas fa-check-circle fa-3x mb-3"></i> 
                        <h5 class="card-title">Pengajuan Selesai</h5> 
                        <p class="card-text fs-2 fw-bold"><?php echo number_format($stats_selesai); ?></p> 
                    </div> 
                </div> 
            </div> 
        </div> 

        <!-- Layanan Pengajuan Surat --> 
        <h2 class="fw-bold mb-4" id="layanan-surat">Layanan Pengajuan Surat</h2> 
        <div class="row g-4 mb-5"> 
            <!-- Layanan yang Ada --> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-file-invoice fa-3x text-primary mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Keterangan Usaha</h5> 
                        <p class="card-text text-muted">Ajukan surat keterangan untuk kepemilikan usaha.</p> 
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalSuratUsaha">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-handshake fa-3x text-success mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Pengantar Nikah</h5> 
                        <p class="card-text text-muted">Dapatkan surat pengantar untuk keperluan pernikahan.</p> 
                        <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#modalSuratNikah">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-home fa-3x text-info mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Keterangan Domisili</h5> 
                        <p class="card-text text-muted">Ajukan surat domisili untuk keperluan administrasi.</p> 
                        <button class="btn btn-info text-white mt-3" data-bs-toggle="modal" data-bs-target="#modalSuratDomisili">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            
            <!-- Tambahan Layanan Baru --> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-users fa-3x text-danger mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Keterangan Kematian</h5> 
                        <p class="card-text text-muted">Ajukan surat untuk pengurusan administrasi kematian.</p> 
                        <button class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#modalSuratKematian">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-dollar-sign fa-3x text-secondary mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Keterangan Tidak Mampu (SKTM)</h5> 
                        <p class="card-text text-muted">Dapatkan surat untuk bantuan sosial atau pendidikan.</p> 
                        <button class="btn btn-secondary mt-3" data-bs-toggle="modal" data-bs-target="#modalSKTM">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-volume-up fa-3x text-warning mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Izin Keramaian</h5> 
                        <p class="card-text text-muted">Ajukan perizinan untuk acara yang mengundang banyak orang.</p> 
                        <button class="btn btn-warning text-dark mt-3" data-bs-toggle="modal" data-bs-target="#modalIzinKeramaian">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-id-card fa-3x text-primary mb-3"></i> 
                        <h5 class="card-title fw-bold">Pengajuan Kartu Keluarga</h5> 
                        <p class="card-text text-muted">Ajukan pembuatan kartu keluarga baru atau perubahan data.</p> 
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalKartuKeluarga">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-envelope-open-text fa-3x text-info mb-3"></i> 
                        <h5 class="card-title fw-bold">Surat Pengantar Desa</h5> 
                        <p class="card-text text-muted">Dapatkan surat pengantar untuk berbagai keperluan.</p> 
                        <button class="btn btn-info text-white mt-3" data-bs-toggle="modal" data-bs-target="#modalSuratPengantarDesa">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
            <div class="col-md-4"> 
                <div class="card h-100"> 
                    <div class="card-body text-center"> 
                        <i class="fas fa-baby fa-3x text-success mb-3"></i> 
                        <h5 class="card-title fw-bold">Pengurusan Akta Kelahiran</h5> 
                        <p class="card-text text-muted">Ajukan permohonan untuk akta kelahiran anak.</p> 
                        <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#modalAktaKelahiran">Ajukan Sekarang</button> 
                    </div> 
                </div> 
            </div> 
        </div> 

        <!-- Riwayat Pengajuan --> 
        <h2 class="fw-bold mb-4" id="riwayat-pengajuan">Riwayat Pengajuan Surat</h2> 
        <div class="card"> 
            <div class="card-header fw-bold"> 
                Daftar Pengajuan Terakhir 
            </div> 
            <div class="card-body"> 
                <div class="table-responsive"> 
                    <table class="table table-hover align-middle"> 
                        <thead> 
                            <tr> 
                                <th scope="col">#</th> 
                                <th scope="col">Jenis Surat</th> 
                                <th scope="col">Tanggal Pengajuan</th> 
                                <th scope="col">Status</th> 
                            </tr> 
                        </thead> 
                        <tbody> 
                            <?php if (!empty($riwayat_pengajuan)) { ?> 
                                <?php $i = 1; ?> 
                                <?php foreach ($riwayat_pengajuan as $row) { ?> 
                                    <tr> 
                                        <th scope="row"><?php echo $i++; ?></th> 
                                        <td><?php echo htmlspecialchars($row['jenis_surat']); ?></td> 
                                        <td><?php echo htmlspecialchars($row['tanggal_pengajuan']); ?></td> 
                                        <td> 
                                            <?php 
                                                $badge_class = ''; 
                                                if ($row['status'] == 'Selesai') { 
                                                    $badge_class = 'bg-success'; 
                                                } elseif ($row['status'] == 'Diproses') { 
                                                    $badge_class = 'bg-warning'; 
                                                } else { 
                                                    $badge_class = 'bg-danger'; 
                                                } 
                                            ?> 
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span> 
                                        </td> 
                                    </tr> 
                                <?php } ?> 
                            <?php } else { ?> 
                                <tr> 
                                    <td colspan="4" class="text-center text-muted">Belum ada riwayat pengajuan surat.</td> 
                                </tr> 
                            <?php } ?> 
                        </tbody> 
                    </table> 
                </div> 
            </div> 
        </div> 
    </div> 

    <!-- Modals untuk Pengajuan Surat --> 
    <!-- Modal Surat Keterangan Usaha --> 
    <div class="modal fade" id="modalSuratUsaha" tabindex="-1" aria-labelledby="modalSuratUsahaLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSuratUsahaLabel">Form Pengajuan Surat Keterangan Usaha</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Keterangan Usaha"> 
                <p>Formulir pengajuan akan ditampilkan di sini. Anda dapat menambahkan input seperti jenis usaha, lokasi, dan deskripsi.</p> 
                <!-- Contoh: Input jenis usaha --> 
                <div class="mb-3"> 
                    <label for="jenis_usaha" class="form-label">Jenis Usaha</label> 
                    <input type="text" class="form-control" id="jenis_usaha" name="jenis_usaha" required> 
                </div> 
                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Pengantar Nikah --> 
    <div class="modal fade" id="modalSuratNikah" tabindex="-1" aria-labelledby="modalSuratNikahLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSuratNikahLabel">Form Pengajuan Surat Pengantar Nikah</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Pengantar Nikah"> 
                <p>Formulir pengajuan untuk surat pengantar nikah akan ditampilkan di sini.</p> 
                <!-- Contoh: Input nama pasangan --> 
                <div class="mb-3"> 
                    <label for="nama_pasangan" class="form-label">Nama Calon Pasangan</label> 
                    <input type="text" class="form-control" id="nama_pasangan" name="nama_pasangan" required> 
                </div> 
                <button type="submit" class="btn btn-success">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Keterangan Domisili --> 
    <div class="modal fade" id="modalSuratDomisili" tabindex="-1" aria-labelledby="modalSuratDomisiliLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSuratDomisiliLabel">Form Pengajuan Surat Keterangan Domisili</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Keterangan Domisili"> 
                <p>Formulir pengajuan untuk surat domisili akan ditampilkan di sini.</p> 
                <!-- Contoh: Input alasan pengajuan --> 
                <div class="mb-3"> 
                    <label for="alasan_domisili" class="form-label">Alasan Pengajuan</label> 
                    <textarea class="form-control" id="alasan_domisili" name="alasan_domisili" rows="3" required></textarea> 
                </div> 
                <button type="submit" class="btn btn-info text-white">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Keterangan Kematian --> 
    <div class="modal fade" id="modalSuratKematian" tabindex="-1" aria-labelledby="modalSuratKematianLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSuratKematianLabel">Form Pengajuan Surat Keterangan Kematian</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Keterangan Kematian"> 
                <p>Formulir pengajuan surat keterangan kematian akan ditampilkan di sini.</p> 
                <!-- Contoh: Input nama almarhum --> 
                <div class="mb-3"> 
                    <label for="nama_almarhum" class="form-label">Nama Almarhum/Almarhumah</label> 
                    <input type="text" class="form-control" id="nama_almarhum" name="nama_almarhum" required> 
                </div> 
                <button type="submit" class="btn btn-danger">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Keterangan Tidak Mampu (SKTM) --> 
    <div class="modal fade" id="modalSKTM" tabindex="-1" aria-labelledby="modalSKTMLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSKTMLabel">Form Pengajuan SKTM</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="SKTM"> 
                <p>Formulir pengajuan SKTM akan ditampilkan di sini.</p> 
                <!-- Contoh: Input alasan pengajuan --> 
                <div class="mb-3"> 
                    <label for="alasan_sktm" class="form-label">Alasan Pengajuan</label> 
                    <textarea class="form-control" id="alasan_sktm" name="alasan_sktm" rows="3" required></textarea> 
                </div> 
                <button type="submit" class="btn btn-secondary">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Izin Keramaian --> 
    <div class="modal fade" id="modalIzinKeramaian" tabindex="-1" aria-labelledby="modalIzinKeramaianLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalIzinKeramaianLabel">Form Pengajuan Surat Izin Keramaian</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Izin Keramaian"> 
                <p>Formulir pengajuan surat izin keramaian akan ditampilkan di sini.</p> 
                <!-- Contoh: Input nama acara --> 
                <div class="mb-3"> 
                    <label for="nama_acara" class="form-label">Nama Acara</label> 
                    <input type="text" class="form-control" id="nama_acara" name="nama_acara" required> 
                </div> 
                <button type="submit" class="btn btn-warning text-dark">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Pengajuan Kartu Keluarga Baru --> 
    <div class="modal fade" id="modalKartuKeluarga" tabindex="-1" aria-labelledby="modalKartuKeluargaLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalKartuKeluargaLabel">Form Pengajuan Kartu Keluarga Baru</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Pengajuan Kartu Keluarga"> 
                <p>Formulir pengajuan untuk pembuatan kartu keluarga baru akan ditampilkan di sini.</p> 
                <!-- Contoh: Input alasan pengajuan --> 
                <div class="mb-3"> 
                    <label for="alasan_kk" class="form-label">Alasan Pengajuan</label> 
                    <textarea class="form-control" id="alasan_kk" name="alasan_kk" rows="3" required></textarea> 
                </div> 
                <button type="submit" class="btn btn-primary">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Surat Pengantar Desa --> 
    <div class="modal fade" id="modalSuratPengantarDesa" tabindex="-1" aria-labelledby="modalSuratPengantarDesaLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalSuratPengantarDesaLabel">Form Pengajuan Surat Pengantar Desa</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Surat Pengantar Desa"> 
                <p>Formulir pengajuan surat pengantar desa akan ditampilkan di sini.</p> 
                <div class="mb-3"> 
                    <label for="keperluan_surat" class="form-label">Keperluan Surat</label> 
                    <input type="text" class="form-control" id="keperluan_surat" name="keperluan_surat" required> 
                </div> 
                <button type="submit" class="btn btn-info text-white">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 
    <!-- Modal Pengurusan Akta Kelahiran --> 
    <div class="modal fade" id="modalAktaKelahiran" tabindex="-1" aria-labelledby="modalAktaKelahiranLabel" aria-hidden="true"> 
      <div class="modal-dialog"> 
        <div class="modal-content"> 
          <div class="modal-header"> 
            <h5 class="modal-title" id="modalAktaKelahiranLabel">Form Pengurusan Akta Kelahiran</h5> 
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
          </div> 
          <div class="modal-body"> 
            <form action="pengajuan_surat_action.php" method="POST"> 
                <input type="hidden" name="jenis_surat" value="Pengurusan Akta Kelahiran"> 
                <p>Formulir untuk pengurusan akta kelahiran akan ditampilkan di sini.</p> 
                <div class="mb-3"> 
                    <label for="nama_bayi" class="form-label">Nama Bayi</label> 
                    <input type="text" class="form-control" id="nama_bayi" name="nama_bayi" required> 
                </div> 
                <button type="submit" class="btn btn-success">Kirim Pengajuan</button> 
            </form> 
          </div> 
        </div> 
      </div> 
    </div> 

    <!-- Bootstrap 5 JS --> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script> 

</body> 
</html>
