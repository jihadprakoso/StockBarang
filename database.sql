-- ============================================================
-- StockBarang - Skema & data contoh
-- Database: stockbarang_local
-- Akun demo: admin@stockbarang.local / admin
-- ============================================================

CREATE DATABASE IF NOT EXISTS `stockbarang_local` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `stockbarang_local`;

-- Tabel akun pengguna
CREATE TABLE IF NOT EXISTS `login` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel master barang
CREATE TABLE IF NOT EXISTS `stock` (
  `idbarang` INT AUTO_INCREMENT PRIMARY KEY,
  `namabarang` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel transaksi barang masuk
CREATE TABLE IF NOT EXISTS `masuk` (
  `idmasuk` INT AUTO_INCREMENT PRIMARY KEY,
  `idbarang` INT NOT NULL,
  `keterangan` VARCHAR(150) DEFAULT NULL,
  `qty` INT NOT NULL,
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_masuk_barang` FOREIGN KEY (`idbarang`) REFERENCES `stock`(`idbarang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel transaksi barang keluar
CREATE TABLE IF NOT EXISTS `keluar` (
  `idkeluar` INT AUTO_INCREMENT PRIMARY KEY,
  `idbarang` INT NOT NULL,
  `penerima` VARCHAR(150) DEFAULT NULL,
  `qty` INT NOT NULL,
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_keluar_barang` FOREIGN KEY (`idbarang`) REFERENCES `stock`(`idbarang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Data contoh
-- ------------------------------------------------------------

-- Akun admin (password: admin, hash BCRYPT)
INSERT IGNORE INTO `login` (`email`, `password`) VALUES
('admin@stockbarang.local', '$2y$12$5w2Gv3fiJ2F4tLN9JJDN6ej.JSjHmD4Ymu0tyI80gZOcz/MlVLNzG');

-- Master barang
INSERT IGNORE INTO `stock` (`idbarang`, `namabarang`, `deskripsi`, `stock`) VALUES
(1, 'Kertas HVS A4 80gr', 'Kertas print A4 80gr isi 500 lembar per rim', 120),
(2, 'Tinta Printer Epson 003 Black', 'Tinta original Epson 003 warna hitam 65ml', 45),
(3, 'Kardus Packing 40x30x30', 'Kardus karton gelombang double wall ukuran 40x30x30 cm', 60),
(4, 'Lakban Bening 2 Inch', 'Lakban perekat bening lebar 2 inch panjang 100 yard', 80),
(5, 'Map Arsip Plastik', 'Map arsip plastik folio dengan daun penjepit', 150),
(6, 'Pulpen Gel Hitam', 'Pulpen gel hitam 0.5mm isi 12 pcs per box', 35);

-- Riwayat barang masuk
INSERT IGNORE INTO `masuk` (`idmasuk`, `idbarang`, `keterangan`, `qty`, `tanggal`) VALUES
(1, 1, 'PT Sumber Kertas Abadi', 100, NOW() - INTERVAL 12 DAY),
(2, 2, 'CV Tinta Nusantara', 40, NOW() - INTERVAL 9 DAY),
(3, 5, 'Toko ATK Sejahtera', 150, NOW() - INTERVAL 5 DAY);

-- Riwayat barang keluar
INSERT IGNORE INTO `keluar` (`idkeluar`, `idbarang`, `penerima`, `qty`, `tanggal`) VALUES
(1, 1, 'Divisi Operasional', 20, NOW() - INTERVAL 4 DAY),
(2, 2, 'Divisi Keuangan', 5, NOW() - INTERVAL 2 DAY);
