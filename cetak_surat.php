<?php
// ==========================================================
// FILE INI BERTANGGUNG JAWAB UNTUK MENCETAK SURAT
// ==========================================================

// Memulai sesi untuk keamanan
session_start();

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
// LOGIKA UNTUK MENGAMBIL DATA SURAT
// ==========================================================
$permintaan = null;
$penduduk = null;
$message = '';

// Periksa apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    $id_permintaan = clean_input($_GET['id']);
    
    // Pastikan koneksi tersedia
    if ($conn) {
        // Step 1: Ambil NIK dan jenis surat dari tabel permintaan_surat
        $stmt_permintaan = $conn->prepare("SELECT nik, jenis_surat FROM permintaan_surat WHERE id = ?");
        $stmt_permintaan->bind_param("i", $id_permintaan);
        $stmt_permintaan->execute();
        $result_permintaan = $stmt_permintaan->get_result();

        // Periksa apakah data permintaan ditemukan
        if ($result_permintaan->num_rows > 0) {
            $permintaan = $result_permintaan->fetch_assoc();
            $nik_pemohon = $permintaan['nik'];

            // Step 2: Ambil semua data penduduk berdasarkan NIK
            $stmt_penduduk = $conn->prepare("SELECT * FROM penduduk WHERE nik = ?");
            $stmt_penduduk->bind_param("s", $nik_pemohon);
            $stmt_penduduk->execute();
            $result_penduduk = $stmt_penduduk->get_result();

            // Periksa apakah data penduduk ditemukan
            if ($result_penduduk->num_rows > 0) {
                $penduduk = $result_penduduk->fetch_assoc();
            } else {
                $message = "Data penduduk dengan NIK: $nik_pemohon tidak ditemukan.";
            }
            
            $stmt_penduduk->close();
        } else {
            $message = "Data permintaan surat tidak ditemukan.";
        }
        
        $stmt_permintaan->close();
        $conn->close();
    } else {
        $message = "Koneksi database gagal.";
    }
} else {
    $message = "ID permintaan surat tidak valid.";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .surat-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .header-surat {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .header-surat h4 {
            margin: 0;
            font-weight: 700;
        }
        .body-surat {
            line-height: 1.8;
            font-size: 1rem;
        }
        .body-surat p {
            text-indent: 40px;
            margin-bottom: 10px;
        }
        .identitas-surat {
            margin: 20px 0;
        }
        .identitas-surat p {
            text-indent: 0;
            margin: 5px 0;
            padding-left: 40px;
        }
        .signature-section {
            margin-top: 50px;
            text-align: right;
        }
        .signature-section p {
            margin: 0;
        }
        .btn-print {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        /* Style khusus untuk cetak */
        @media print {
            body {
                background: none;
            }
            .surat-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
        .debug-box {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #f0f0f0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: monospace;
            z-index: 9999;
            max-width: 300px;
            white-space: pre-wrap;
            word-wrap: break-word;
            opacity: 0.8;
        }
        @media print {
            .debug-box {
                display: none;
            }
        }
    </style>
</head>
<body>
    
   

    <?php if ($permintaan && $penduduk): ?>
        <div class="surat-container">
            <div class="header-surat">
                <h4>PEMERINTAH DESA [NAMA DESA]</h4>
                <p>Kecamatan [NAMA KECAMATAN], Kabupaten [NAMA KABUPATEN]</p>
            </div>
            
            <div class="body-surat">
                <p class="text-center">
                    <u><b>SURAT KETERANGAN <?= strtoupper($permintaan['jenis_surat']) ?></b></u><br>
                    Nomor: ................................
                </p>
                <p>
                    Yang bertanda tangan di bawah ini Kepala Desa [NAMA DESA] Kecamatan [NAMA KECAMATAN] Kabupaten [NAMA KABUPATEN], menerangkan dengan sesungguhnya bahwa:
                </p>
                
                <div class="identitas-surat">
                    <!-- Pastikan nama kolom sesuai dengan yang ditampilkan di debug-box -->
                    <p>Nama &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= isset($penduduk['nama']) ? htmlspecialchars($penduduk['nama']) : '-' ?></p>
                    <p>NIK &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= isset($penduduk['nik']) ? htmlspecialchars($penduduk['nik']) : '-' ?></p>
                    <p>Tempat/Tanggal Lahir &nbsp;: <?= isset($penduduk['tempat_lahir']) ? htmlspecialchars($penduduk['tempat_lahir']) : '-' ?>, <?= isset($penduduk['tanggal_lahir']) ? date('d-m-Y', strtotime($penduduk['tanggal_lahir'])) : '-' ?></p>
                    <p>Jenis Kelamin &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= isset($penduduk['jenis_kelamin']) ? htmlspecialchars($penduduk['jenis_kelamin']) : '-' ?></p>
                    <p>Alamat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?= isset($penduduk['alamat']) ? htmlspecialchars($penduduk['alamat']) : '-' ?></p>
                </div>

                <p>
                    Berdasarkan data yang ada pada kami, bahwa nama tersebut di atas adalah benar-benar warga desa kami dan sedang mengajukan permohonan <?= htmlspecialchars($permintaan['jenis_surat']) ?>.
                </p>

                <p>
                    Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
                </p>

                <div class="signature-section">
                    <p>Dibuat di : [NAMA DESA]</p>
                    <p>Pada Tanggal : <?= date('d M Y') ?></p>
                    <br>
                    <p>Kepala Desa [NAMA DESA]</p>
                    <br>
                    <br>
                    <br>
                    <p><u>[NAMA KEPALA DESA]</u></p>
                </div>
            </div>
        </div>

        <button class="btn btn-primary btn-print" onclick="window.print()">
            <i class="fas fa-print me-2"></i> Cetak Dokumen
        </button>

    <?php else: ?>
        <div class="container text-center mt-5">
            <div class="alert alert-danger" role="alert">
                <?= $message ?>
            </div>
            <a href="manage_layanan.php" class="btn btn-secondary mt-3">Kembali ke Manajemen Layanan</a>
        </div>
    <?php endif; ?>

    <!-- Font Awesome JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>
</html>
