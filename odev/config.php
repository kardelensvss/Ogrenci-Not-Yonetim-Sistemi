<?php
session_start();

$host = 'localhost';
$dbname = 'php_odev';
$username = 'root';
$password = '';

try {
    // Önce veritabanı adı belirtmeden bağlan
    $pdo_init = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo_init->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Veritabanını oluştur (yoksa)
    $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `e_okul_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo_init->exec("USE `e_okul_db`");

    $pdo_init->exec("CREATE TABLE IF NOT EXISTS `ogrenciler` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ogr_no` varchar(20) NOT NULL,
        `ad` varchar(50) NOT NULL,
        `soyad` varchar(50) NOT NULL,
        `sinif` varchar(20) NOT NULL,
        `sifre` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `ogr_no` (`ogr_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo_init->exec("CREATE TABLE IF NOT EXISTS `ogretmenler` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `kullanici_adi` varchar(50) NOT NULL,
        `ad` varchar(50) NOT NULL,
        `soyad` varchar(50) NOT NULL,
        `sifre` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `kullanici_adi` (`kullanici_adi`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo_init->exec("CREATE TABLE IF NOT EXISTS `dersler` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ders_adi` varchar(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Eski notlar tablosunu yeni şemaya geçir
    $pdo_init->exec("DROP TABLE IF EXISTS `notlar`");
    $pdo_init->exec("CREATE TABLE `notlar` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ogrenci_id` int(11) NOT NULL,
        `ders_id` int(11) NOT NULL,
        `vize1` decimal(5,2) DEFAULT NULL,
        `vize2` decimal(5,2) DEFAULT NULL,
        `vize3` decimal(5,2) DEFAULT NULL,
        `final` decimal(5,2) DEFAULT NULL,
        `ortalama` decimal(5,2) DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `ogrenci_id` (`ogrenci_id`),
        KEY `ders_id` (`ders_id`),
        CONSTRAINT `notlar_ibfk_1` FOREIGN KEY (`ogrenci_id`) REFERENCES `ogrenciler` (`id`) ON DELETE CASCADE,
        CONSTRAINT `notlar_ibfk_2` FOREIGN KEY (`ders_id`) REFERENCES `dersler` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo_init->exec("CREATE TABLE IF NOT EXISTS `devamsizlik` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ogrenci_id` int(11) NOT NULL,
        `tarih` date NOT NULL,
        `durum` enum('Tam','Yarim') NOT NULL DEFAULT 'Tam',
        PRIMARY KEY (`id`),
        KEY `ogrenci_id` (`ogrenci_id`),
        CONSTRAINT `devamsizlik_ibfk_1` FOREIGN KEY (`ogrenci_id`) REFERENCES `ogrenciler` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Örnek veriler (sadece boşsa ekle)
    $ogretmen_count = $pdo_init->query("SELECT COUNT(*) FROM ogretmenler")->fetchColumn();
    if ($ogretmen_count == 0) {
        $pdo_init->exec("INSERT INTO `ogretmenler` (`kullanici_adi`, `ad`, `soyad`, `sifre`) VALUES ('admin', 'Admin', 'Ogretmen', '123456')");
        $pdo_init->exec("INSERT INTO `dersler` (`ders_adi`) VALUES ('Matematik'), ('Turkce'), ('Fizik'), ('Kimya'), ('Internet Programciligi')");
        $pdo_init->exec("INSERT INTO `ogrenciler` (`ogr_no`, `ad`, `soyad`, `sinif`, `sifre`) VALUES ('101', 'Ahmet', 'Yilmaz', '11-A', '123456'), ('102', 'Ayse', 'Demir', '11-A', '123456')");
    }

    // Şimdi normal bağlantıyı kur
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

define('SITE_URL', 'http://localhost:8080');
?>
