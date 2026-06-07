-- E-Okul Benzeri Sistem Veritabanı Yapısı
CREATE DATABASE IF NOT EXISTS `e_okul_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `e_okul_db`;

-- Öğrenciler Tablosu
CREATE TABLE `ogrenciler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ogr_no` varchar(20) NOT NULL,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `sinif` varchar(20) NOT NULL,
  `sifre` varchar(255) NOT NULL, -- Gerçek projelerde hashlenmeli (password_hash)
  PRIMARY KEY (`id`),
  UNIQUE KEY `ogr_no` (`ogr_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Öğretmenler Tablosu
CREATE TABLE `ogretmenler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kullanici_adi` varchar(50) NOT NULL,
  `ad` varchar(50) NOT NULL,
  `soyad` varchar(50) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kullanici_adi` (`kullanici_adi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dersler Tablosu
CREATE TABLE `dersler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ders_adi` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notlar Tablosu
CREATE TABLE `notlar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ogrenci_id` int(11) NOT NULL,
  `ders_id` int(11) NOT NULL,
  `sinav1` int(11) DEFAULT NULL,
  `sinav2` int(11) DEFAULT NULL,
  `sozlu` int(11) DEFAULT NULL,
  `ortalama` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ogrenci_id` (`ogrenci_id`),
  KEY `ders_id` (`ders_id`),
  CONSTRAINT `notlar_ibfk_1` FOREIGN KEY (`ogrenci_id`) REFERENCES `ogrenciler` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notlar_ibfk_2` FOREIGN KEY (`ders_id`) REFERENCES `dersler` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Devamsızlık Tablosu
CREATE TABLE `devamsizlik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ogrenci_id` int(11) NOT NULL,
  `tarih` date NOT NULL,
  `durum` enum('Tam','Yarım') NOT NULL DEFAULT 'Tam',
  PRIMARY KEY (`id`),
  KEY `ogrenci_id` (`ogrenci_id`),
  CONSTRAINT `devamsizlik_ibfk_1` FOREIGN KEY (`ogrenci_id`) REFERENCES `ogrenciler` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek Veriler (Opsiyonel Başlangıç Verileri)
INSERT INTO `ogretmenler` (`kullanici_adi`, `ad`, `soyad`, `sifre`) VALUES
('admin', 'Admin', 'Öğretmen', '123456');

INSERT INTO `dersler` (`ders_adi`) VALUES
('Matematik'), ('Türkçe'), ('Fizik'), ('Kimya'), ('İnternet Programcılığı');

INSERT INTO `ogrenciler` (`ogr_no`, `ad`, `soyad`, `sinif`, `sifre`) VALUES
('101', 'Ahmet', 'Yılmaz', '11-A', '123456'),
('102', 'Ayşe', 'Demir', '11-A', '123456');
