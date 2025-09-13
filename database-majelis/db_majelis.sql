-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 08, 2025 at 05:02 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_majelis`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id_booking` int NOT NULL,
  `id_user` int NOT NULL,
  `id_trip` int NOT NULL,
  `id_participant` int NOT NULL,
  `jumlah_orang` int NOT NULL,
  `total_harga` int NOT NULL,
  `tanggal_booking` date NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_trips`
--

CREATE TABLE `detail_trips` (
  `id_trip` int NOT NULL,
  `include` varchar(250) NOT NULL,
  `exclude` varchar(250) NOT NULL,
  `syaratKetentuan` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meeting-points`
--

CREATE TABLE `meeting-points` (
  `id_trip` int NOT NULL,
  `nama_lokasi` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `waktu_kumpul` time(6) NOT NULL,
  `link_map` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paket_trips`
--

CREATE TABLE `paket_trips` (
  `id_trip` int NOT NULL,
  `nama_gunung` varchar(30) NOT NULL,
  `jenis_trip` int NOT NULL,
  `tanggal` date NOT NULL,
  `durasi` varchar(15) NOT NULL,
  `via_gunung` varchar(30) NOT NULL,
  `slot` int NOT NULL,
  `status` varchar(15) NOT NULL,
  `gambar` mediumblob NOT NULL,
  `harga` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id_participant` int NOT NULL,
  `nama` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tempat_lahir` varchar(20) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `no_wa` varchar(15) NOT NULL,
  `no_wa_darurat` varchar(15) NOT NULL,
  `riwayat_penyakit` varchar(50) NOT NULL,
  `foto_ktp` mediumblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id_payment` int NOT NULL,
  `id_booking` int NOT NULL,
  `jumlah_bayar` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_pembayaran` varchar(20) NOT NULL,
  `metode` varchar(20) NOT NULL,
  `sisa_bayar` int NOT NULL,
  `status_pembayaran` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id_review` int NOT NULL,
  `id_trip` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal` date NOT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trip_galleries`
--

CREATE TABLE `trip_galleries` (
  `id_trip` int NOT NULL,
  `galery_name` varchar(50) NOT NULL,
  `gdrive_link` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `fk_trip_paket_trips` (`id_trip`),
  ADD KEY `fk_user_users` (`id_user`),
  ADD KEY `fk_participant_participants` (`id_participant`);

--
-- Indexes for table `detail_trips`
--
ALTER TABLE `detail_trips`
  ADD KEY `fk_detail_trip_idtrip` (`id_trip`);

--
-- Indexes for table `meeting-points`
--
ALTER TABLE `meeting-points`
  ADD KEY `fk_meeting_points_idtrip` (`id_trip`);

--
-- Indexes for table `paket_trips`
--
ALTER TABLE `paket_trips`
  ADD PRIMARY KEY (`id_trip`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id_participant`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `no_wa` (`no_wa`),
  ADD UNIQUE KEY `no_wa_darurat` (`no_wa_darurat`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id_payment`),
  ADD KEY `fk_bookings_id_booking` (`id_booking`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `fk_id_trip_paket_trips` (`id_trip`),
  ADD KEY `fk_id_user_users` (`id_user`);

--
-- Indexes for table `trip_galleries`
--
ALTER TABLE `trip_galleries`
  ADD KEY `fk_trip_galleries_idtrip` (`id_trip`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`,`no_wa`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id_booking` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paket_trips`
--
ALTER TABLE `paket_trips`
  MODIFY `id_trip` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants`
  MODIFY `id_participant` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id_payment` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id_review` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_participant_participants` FOREIGN KEY (`id_participant`) REFERENCES `participants` (`id_participant`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trip_paket_trips` FOREIGN KEY (`id_trip`) REFERENCES `paket_trips` (`id_trip`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_trips`
--
ALTER TABLE `detail_trips`
  ADD CONSTRAINT `fk_detail_trip_idtrip` FOREIGN KEY (`id_trip`) REFERENCES `paket_trips` (`id_trip`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `meeting-points`
--
ALTER TABLE `meeting-points`
  ADD CONSTRAINT `fk_meeting_points_idtrip` FOREIGN KEY (`id_trip`) REFERENCES `paket_trips` (`id_trip`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_bookings_id_booking` FOREIGN KEY (`id_booking`) REFERENCES `bookings` (`id_booking`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_id_trip_paket_trips` FOREIGN KEY (`id_trip`) REFERENCES `paket_trips` (`id_trip`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_id_user_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `trip_galleries`
--
ALTER TABLE `trip_galleries`
  ADD CONSTRAINT `fk_trip_galleries_idtrip` FOREIGN KEY (`id_trip`) REFERENCES `paket_trips` (`id_trip`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
