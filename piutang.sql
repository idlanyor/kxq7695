-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 05:24 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.0.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `piutang`
--

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `kode_pelanggan` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` varchar(200) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `tagihan` int(11) NOT NULL,
  `keterangan` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `kode_pelanggan`, `nama_pelanggan`, `alamat`, `no_hp`, `tagihan`, `keterangan`) VALUES
(2, 'PTN0002', 'BU TITI', 'PURBALINGGA', '085123456712', 1500000, 'LAMA'),
(3, 'PTN0003', 'ISMA', 'BOJANEGARA', '085123456711', 0, 'LAMA'),
(7, 'PTN0005', 'LINDA', 'BABAKAN', '0855', 500000, 'BARU'),
(8, 'PTN0004', 'YENI', 'PURBALINGGA', '085123456789', 4000000, 'LAMA'),
(9, 'PTN0001', 'BU ANI', 'PURBALINGGA', '085123456789', 0, 'LAMA'),
(10, 'PTN0006', 'ISMI', 'BOJANEGARA', '085123456712', 0, 'BARU'),
(11, 'PTN0007', 'DEDI', 'PURBALINGGA', '085743532795', 3500000, 'BARU'),
(12, 'PTN0008', 'LELI', 'PURBALINGGA', '085123456711', 0, 'BARU'),
(13, 'PTN0009', 'ARRA', 'KUTASARI', '085123456789', 500000, 'BARU'),
(14, 'PTN0010', 'UNI', 'KEMANGKON', '085123456711', 3000000, 'LAMA'),
(15, 'PTN0011', 'DINDA', 'BANCAR', '085123456789', 400000, 'LAMA');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `nama_toko` varchar(255) DEFAULT NULL,
  `alamat_toko` text,
  `stok_minimal_default` int(11) DEFAULT NULL,
  `logo_toko` varchar(255) DEFAULT NULL,
  `format_tanggal` varchar(10) DEFAULT NULL,
  `tema` enum('light','dark') DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tagihan`
--

CREATE TABLE `tagihan` (
  `id` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `tgl_transaksi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaksi` enum('debit','kredit') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(100) NOT NULL,
  `tgl_jt` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tagihan`
--

INSERT INTO `tagihan` (`id`, `id_pelanggan`, `tgl_transaksi`, `transaksi`, `jumlah`, `keterangan`, `tgl_jt`) VALUES
(3, 7, '2025-05-11 17:19:57', 'debit', 5000000, 'VIVO 2', '2025-08-11'),
(4, 7, '2025-05-11 17:20:58', 'kredit', 500000, '-', NULL),
(5, 1, '2025-05-11 17:24:24', 'debit', 5000000, 'VIVO y15', '2025-10-15'),
(6, 1, '2025-05-11 17:24:41', 'kredit', 1000000, '-', NULL),
(7, 2, '2025-05-11 17:25:59', 'debit', 3000000, 'VIVO 2', '2025-05-11'),
(8, 3, '2025-05-11 17:26:21', 'debit', 3000000, 'VIVO y15', '2025-05-13'),
(9, 2, '2025-05-11 17:26:38', 'kredit', 1500000, '-', NULL),
(10, 8, '2025-05-12 22:09:35', 'debit', 4000000, 'VIVO y15', '2025-05-13'),
(11, 15, '2025-05-12 22:10:02', 'debit', 400000, 'VIVO y15', '2025-05-15'),
(12, 11, '2025-05-12 22:10:23', 'debit', 5000000, 'VIVO y15', '2025-05-17'),
(13, 14, '2025-05-12 22:10:43', 'debit', 3000000, 'VIVO y15', '2025-05-20'),
(14, 11, '2025-05-12 22:11:18', 'debit', 4000000, 'VIVO y15', '2025-05-18'),
(15, 7, '2025-05-12 22:12:47', 'kredit', 4000000, '-', NULL),
(16, 13, '2025-05-13 09:39:43', 'debit', 500000, 'VIVO y15', '2025-05-13'),
(17, 11, '2025-05-13 20:03:01', 'kredit', 5500000, '-', NULL),
(18, 3, '2025-05-13 20:05:07', 'kredit', 3000000, '-', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(6, 'admin', 'admin123'),
(11, 'admin1', '$2y$10$gN3OVDh.93licVzl7VG82eo847h4I33DlU0jXm8jPD0FUPhO0JitG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQUE` (`kode_pelanggan`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
