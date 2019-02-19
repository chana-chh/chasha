-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.33-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for kartoteka
DROP DATABASE IF EXISTS `kartoteka`;
CREATE DATABASE IF NOT EXISTS `kartoteka` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `kartoteka`;

-- Dumping structure for table kartoteka.dokumenta
DROP TABLE IF EXISTS `dokumenta`;
CREATE TABLE IF NOT EXISTS `dokumenta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `tip` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `opis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_dokumenta_kartoni` (`karton_id`),
  CONSTRAINT `FK_dokumenta_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.dokumenta: ~0 rows (approximately)
/*!40000 ALTER TABLE `dokumenta` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumenta` ENABLE KEYS */;

-- Dumping structure for table kartoteka.groblja
DROP TABLE IF EXISTS `groblja`;
CREATE TABLE IF NOT EXISTS `groblja` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `naziv` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `adresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `ptt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `naziv` (`naziv`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.groblja: ~0 rows (approximately)
/*!40000 ALTER TABLE `groblja` DISABLE KEYS */;
/*!40000 ALTER TABLE `groblja` ENABLE KEYS */;

-- Dumping structure for table kartoteka.kartoni
DROP TABLE IF EXISTS `kartoni`;
CREATE TABLE IF NOT EXISTS `kartoni` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groblje_id` int(10) unsigned NOT NULL,
  `parcela` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grobno_mesto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `broj_mesta` smallint(5) unsigned NOT NULL,
  `tip_goba` enum('Grobno mesto','Grobnica','Kapela') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktivan` tinyint(3) unsigned NOT NULL,
  `napomena` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`,`grobno_mesto`),
  CONSTRAINT `FK_kartoni_groblja` FOREIGN KEY (`groblje_id`) REFERENCES `groblja` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.kartoni: ~0 rows (approximately)
/*!40000 ALTER TABLE `kartoni` DISABLE KEYS */;
/*!40000 ALTER TABLE `kartoni` ENABLE KEYS */;

-- Dumping structure for table kartoteka.mape
DROP TABLE IF EXISTS `mape`;
CREATE TABLE IF NOT EXISTS `mape` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `groblje_id` int(10) unsigned NOT NULL,
  `parcela` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grobno_mesto` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `veza` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `x_pozicija` int(10) unsigned NOT NULL DEFAULT '0',
  `y_pozicija` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `groblje_id_parcela_grobno_mesto` (`groblje_id`,`parcela`,`grobno_mesto`),
  KEY `FK_mape_kartoni` (`karton_id`),
  CONSTRAINT `FK_mape_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.mape: ~0 rows (approximately)
/*!40000 ALTER TABLE `mape` DISABLE KEYS */;
/*!40000 ALTER TABLE `mape` ENABLE KEYS */;

-- Dumping structure for table kartoteka.staraoci
DROP TABLE IF EXISTS `staraoci`;
CREATE TABLE IF NOT EXISTS `staraoci` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `redni_broj` smallint(5) unsigned NOT NULL,
  `prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jmbg` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ulica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `broj` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ptt` int(10) unsigned DEFAULT NULL,
  `telefon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktivan` tinyint(3) unsigned NOT NULL,
  `prenos` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `jmbg` (`jmbg`),
  KEY `prezime_ime` (`prezime`,`ime`),
  CONSTRAINT `FK_staraoci_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.staraoci: ~0 rows (approximately)
/*!40000 ALTER TABLE `staraoci` DISABLE KEYS */;
/*!40000 ALTER TABLE `staraoci` ENABLE KEYS */;

-- Dumping structure for table kartoteka.tipovi_transakcija
DROP TABLE IF EXISTS `tipovi_transakcija`;
CREATE TABLE IF NOT EXISTS `tipovi_transakcija` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip` enum('uplata','zakup','taksa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `godina` smallint(6) NOT NULL,
  `iznos` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.tipovi_transakcija: ~0 rows (approximately)
/*!40000 ALTER TABLE `tipovi_transakcija` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipovi_transakcija` ENABLE KEYS */;

-- Dumping structure for table kartoteka.transakcije
DROP TABLE IF EXISTS `transakcije`;
CREATE TABLE IF NOT EXISTS `transakcije` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tip_transakcije_id` int(10) unsigned NOT NULL,
  `datum` date NOT NULL,
  `iznos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priznanica` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razduzeno` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.transakcije: ~0 rows (approximately)
/*!40000 ALTER TABLE `transakcije` DISABLE KEYS */;
/*!40000 ALTER TABLE `transakcije` ENABLE KEYS */;

-- Dumping structure for table kartoteka.umrli
DROP TABLE IF EXISTS `umrli`;
CREATE TABLE IF NOT EXISTS `umrli` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `karton_id` int(10) unsigned NOT NULL,
  `redni_broj` smallint(5) unsigned NOT NULL,
  `prezime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ime` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `srednje_ime` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jmbg` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mesto` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dupla_raka` tinyint(3) unsigned NOT NULL,
  `pozicija` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datum_rodjenja` date DEFAULT NULL,
  `datum_smrti` date DEFAULT NULL,
  `datum_sahrane` date DEFAULT NULL,
  `datum_ekshumacije` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `karton_id_redni_broj` (`karton_id`,`redni_broj`),
  KEY `prezime_ime` (`prezime`,`ime`),
  KEY `jmbg` (`jmbg`),
  CONSTRAINT `FK_umrli_kartoni` FOREIGN KEY (`karton_id`) REFERENCES `kartoni` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kartoteka.umrli: ~0 rows (approximately)
/*!40000 ALTER TABLE `umrli` DISABLE KEYS */;
/*!40000 ALTER TABLE `umrli` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
