-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 03:40 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_desa`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `email`, `password`, `role`) VALUES
(1, 'admin@desa.com', '$2y$10$7VWGCuGtcoVVmZjnKk22uezZUyCritglTl3Po8Fnf.KrhQGNt6.am', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `anggaran`
--

CREATE TABLE `anggaran` (
  `id` int(11) NOT NULL,
  `bidang` varchar(255) NOT NULL,
  `rencana_anggaran` bigint(20) NOT NULL,
  `realisasi_anggaran` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggaran`
--

INSERT INTO `anggaran` (`id`, `bidang`, `rencana_anggaran`, `realisasi_anggaran`) VALUES
(1, 'Bidang Penyelenggaraan Pemerintahan Desa', 500000000, 325000000),
(2, 'Bidang Pembangunan Desa', 750000000, 525000000),
(3, 'Bidang Pembinaan Kemasyarakatan', 250000000, 212500000),
(4, 'Bidang Pemberdayaan Masyarakat', 150000000, 75000000),
(5, 'jalan desa rusak parah', 2000000000, 100000000);

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi_singkat` text NOT NULL,
  `isi_lengkap` longtext NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `author` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `berita`
--

INSERT INTO `berita` (`id`, `judul`, `isi_singkat`, `isi_lengkap`, `gambar`, `tanggal`, `author`) VALUES
(1, 'Pembangunan Jalan Desa Selesai Tepat Waktu', 'Warga desa kini dapat menikmati akses jalan yang lebih baik setelah proyek pembangunan jalan selesai. Proyek ini memakan waktu 3 bulan dan didanai oleh dana desa.', 'Warga desa kini dapat menikmati akses jalan yang lebih baik setelah proyek pembangunan jalan selesai. Proyek ini memakan waktu 3 bulan dan didanai oleh dana desa. Dengan adanya jalan baru ini, diharapkan mobilitas warga dan distribusi hasil pertanian menjadi lebih lancar.', 'berita_6890220bc80d24.02634098.jpg', '2024-05-20', 'Admin Desa'),
(2, 'Festival Seni dan Budaya Desa Maju Jaya Meriah', 'Festival tahunan ini menampilkan berbagai kesenian tradisional, kuliner khas, dan kerajinan tangan dari warga desa.', 'Festival tahunan ini menampilkan berbagai kesenian tradisional, kuliner khas, dan kerajinan tangan dari warga desa. Acara ini dihadiri oleh ribuan pengunjung dari dalam dan luar desa, menunjukkan kekayaan budaya yang dimiliki Desa Maju Jaya.', 'berita_68902236b41f19.31356627.jpg', '2024-05-15', 'Admin Desa'),
(3, 'Pelatihan UMKM untuk Ibu-Ibu PKK Sukses', 'Kegiatan pelatihan yang diadakan oleh pemerintah desa bertujuan untuk meningkatkan keterampilan ibu-ibu dalam membuat produk olahan makanan dan memasarkannya secara online.', 'Kegiatan pelatihan yang diadakan oleh pemerintah desa bertujuan untuk meningkatkan keterampilan ibu-ibu dalam membuat produk olahan makanan dan memasarkannya secara online. Diharapkan pelatihan ini dapat meningkatkan perekonomian keluarga dan mendorong pertumbuhan UMKM di desa.', 'berita_68902241822310.40086736.png', '2024-05-10', 'Admin Desa'),
(5, 'ayo hut ri', 'awfcsfs', 'sdsdgdfg', 'berita_68902b4255e9e7.03009065.png', '2025-08-04', 'Admin Desa');

-- --------------------------------------------------------

--
-- Table structure for table `desa`
--

CREATE TABLE `desa` (
  `id` int(11) NOT NULL,
  `nama_desa` varchar(100) NOT NULL,
  `sejarah` text DEFAULT NULL,
  `visi_misi` text DEFAULT NULL,
  `deskripsi_geografis` text DEFAULT NULL,
  `data_demografi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data_demografi`)),
  `peta_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `media_url` varchar(255) NOT NULL,
  `tipe_media` varchar(20) NOT NULL,
  `tanggal_unggah` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `judul`, `keterangan`, `file_path`, `deskripsi`, `media_url`, `tipe_media`, `tanggal_unggah`, `created_at`) VALUES
(1, 'Pembangunan Jalan Desa Selesai Tepat Waktu', 'tes', 'assets/galeri/galeri_68959b9bd0e504.64787513.jpg', NULL, '', '', '0000-00-00 00:00:00', '2025-08-08 06:39:23');

-- --------------------------------------------------------

--
-- Table structure for table `layanan_request`
--

CREATE TABLE `layanan_request` (
  `id` int(11) NOT NULL,
  `nama_pemohon` varchar(100) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `jenis_layanan` varchar(100) NOT NULL,
  `deskripsi_permohonan` text DEFAULT NULL,
  `tanggal_pengajuan` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `penduduk`
--

CREATE TABLE `penduduk` (
  `id_penduduk` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `pin` varchar(6) DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
  `agama` varchar(50) NOT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `status_perkawinan` enum('Belum Kawin','Kawin','Cerai Hidup','Cerai Mati') NOT NULL,
  `status_hubungan_keluarga` varchar(50) DEFAULT NULL,
  `alamat` text NOT NULL,
  `dusun` varchar(100) NOT NULL,
  `rt` varchar(3) NOT NULL,
  `rw` varchar(3) NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `kewarganegaraan` varchar(50) DEFAULT 'WNI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penduduk`
--

INSERT INTO `penduduk` (`id_penduduk`, `nik`, `pin`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `pendidikan_terakhir`, `pekerjaan`, `status_perkawinan`, `status_hubungan_keluarga`, `alamat`, `dusun`, `rt`, `rw`, `tanggal_masuk`, `keterangan`, `kewarganegaraan`) VALUES
(1, '3301011203850001', '12345', 'Budi Santoso', 'Purwokerto', '1985-03-12', 'Laki-laki', 'Islam', 'S1', 'Wiraswasta', 'Kawin', 'Kepala Keluarga', 'Jl. Kenanga No. 10', 'Dusun 1', '001', '001', '2000-01-01', 'Penduduk tetap', 'WNI'),
(2, '3301012508880002', NULL, 'Dewi Lestari', 'Bandung', '1988-08-25', 'Perempuan', 'Islam', 'SMA', 'Ibu Rumah Tangga', 'Kawin', 'Istri', 'Jl. Kenanga No. 10', 'Dusun 1', '001', '001', '2000-01-01', 'Penduduk tetap', 'WNI'),
(3, '3301010505950003', NULL, 'Rudi Haryanto', 'Jakarta', '1995-05-05', 'Laki-laki', 'Kristen', 'S1', 'Pegawai Swasta', 'Belum Kawin', 'Kepala Keluarga', 'Jl. Mawar No. 5', 'Dusun 2', '002', '002', '2015-06-15', 'Penduduk pendatang', 'WNI'),
(4, '3301011007920004', NULL, 'Siti Aminah', 'Surabaya', '1992-07-10', 'Perempuan', 'Islam', 'S1', 'KARYAWAN', 'Cerai Hidup', 'Kepala Keluarga', 'Jl. Anggrek No. 20', 'Dusun 3', '003', '003', '2010-02-20', 'Penduduk tetap', 'WNI'),
(6, '1234', '123', 'tes', 'per', '1111-12-12', 'Laki-laki', 'Islam', 'S3', 'swasta', 'Belum Kawin', NULL, 'as', 'Dusun 4', '', '', '0000-00-00', NULL, 'WNI'),
(8, '123', '123', 'wer', 'per', '1999-12-12', 'Laki-laki', 'Islam', 'S4', 'PNS', 'Belum Kawin', NULL, 'Pandansari', 'Dusun 5', '', '', '0000-00-00', NULL, 'WNI');

-- --------------------------------------------------------

--
-- Table structure for table `perangkat_desa`
--

CREATE TABLE `perangkat_desa` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_url` varchar(255) DEFAULT NULL,
  `deskripsi_singkat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perangkat_desa`
--

INSERT INTO `perangkat_desa` (`id`, `nama`, `jabatan`, `email`, `password`, `foto_url`, `deskripsi_singkat`, `no_telp`) VALUES
(1, 'Budi Santoso', 'Kepala Desa', 'budi.santoso@desa.com', '$2y$10$7VWGCuGtcoVVmZjnKk22uezZUyCritglTl3Po8Fnf.KrhQGNt6.am', 'path/to/budi.jpg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_surat`
--

CREATE TABLE `permintaan_surat` (
  `id` int(11) NOT NULL,
  `id_penduduk` int(11) DEFAULT NULL,
  `nik` varchar(16) NOT NULL,
  `jenis_surat` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permintaan_surat`
--

INSERT INTO `permintaan_surat` (`id`, `id_penduduk`, `nik`, `jenis_surat`, `keterangan`, `tanggal_pengajuan`, `status`) VALUES
(1, NULL, '3301011203850001', 'Surat Pengantar Desa', NULL, '2025-08-04 02:50:00', 'Selesai'),
(2, NULL, '3301011203850001', 'Pengajuan Kartu Keluarga', NULL, '2025-08-04 03:38:18', 'Diproses'),
(3, NULL, '3301011203850001', 'Surat Pengantar Desa', NULL, '2025-08-04 03:41:00', 'Diproses'),
(4, NULL, '3301011203850001', 'Surat Pengantar Desa', NULL, '2025-08-04 04:07:48', 'Diproses'),
(5, NULL, '3301011203850001', 'Pengajuan Kartu Keluarga', NULL, '2025-08-04 04:14:45', 'Diproses'),
(6, NULL, '3301011203850001', 'Pengurusan Akta Kelahiran', NULL, '2025-08-04 04:17:23', 'Diproses'),
(7, NULL, '3301011203850001', 'Surat Pengantar Desa', NULL, '2025-08-04 04:33:56', 'Diproses');

-- --------------------------------------------------------

--
-- Table structure for table `potensi`
--

CREATE TABLE `potensi` (
  `id` int(11) NOT NULL,
  `jenis_potensi` varchar(50) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `nama_desa` varchar(255) NOT NULL,
  `alamat_desa` text NOT NULL,
  `kontak_desa` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `nama_desa`, `alamat_desa`, `kontak_desa`) VALUES
(1, 'maju makmur', 'sasa', '2112');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `umkm`
--

CREATE TABLE `umkm` (
  `id` int(11) NOT NULL,
  `nama_umkm` varchar(255) NOT NULL,
  `pemilik` varchar(255) NOT NULL,
  `jenis_usaha` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar_path` varchar(255) DEFAULT 'default_umkm.png',
  `tanggal_daftar` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `umkm`
--

INSERT INTO `umkm` (`id`, `nama_umkm`, `pemilik`, `jenis_usaha`, `alamat`, `telepon`, `no_wa`, `deskripsi`, `gambar_path`, `tanggal_daftar`) VALUES
(1, 'soto', 'misro', 'soto', NULL, NULL, '08462842323', 'tes', 'upload/umkm/6895bafc230e2-aparaturdesa_1734410456.jpg', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `nik`, `alamat`, `created_at`) VALUES
(1, 'Siti Aminah', 'siti.aminah@desa.com', '$2y$10$vU8uX3hY.qj9b4A.H6B0O.V45i1F2gE8H9W5hF8H6Y.F6V4Q2zC3o.', NULL, NULL, '2025-08-02 02:14:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `anggaran`
--
ALTER TABLE `anggaran`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `desa`
--
ALTER TABLE `desa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layanan_request`
--
ALTER TABLE `layanan_request`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penduduk`
--
ALTER TABLE `penduduk`
  ADD PRIMARY KEY (`id_penduduk`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `perangkat_desa`
--
ALTER TABLE `perangkat_desa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `permintaan_surat`
--
ALTER TABLE `permintaan_surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potensi`
--
ALTER TABLE `potensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `umkm`
--
ALTER TABLE `umkm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `anggaran`
--
ALTER TABLE `anggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `desa`
--
ALTER TABLE `desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `layanan_request`
--
ALTER TABLE `layanan_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penduduk`
--
ALTER TABLE `penduduk`
  MODIFY `id_penduduk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `perangkat_desa`
--
ALTER TABLE `perangkat_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permintaan_surat`
--
ALTER TABLE `permintaan_surat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `potensi`
--
ALTER TABLE `potensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `umkm`
--
ALTER TABLE `umkm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
