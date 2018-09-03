SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `realmpop`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblCharacter`
--

CREATE TABLE IF NOT EXISTS `tblCharacter` (
  `name` char(12) CHARACTER SET utf8mb4 NOT NULL,
  `realm` smallint(5) UNSIGNED NOT NULL,
  `guild` int(10) UNSIGNED DEFAULT NULL,
  `scanned` timestamp NULL DEFAULT NULL,
  `race` tinyint(3) UNSIGNED DEFAULT NULL,
  `class` tinyint(3) UNSIGNED DEFAULT NULL,
  `gender` tinyint(3) UNSIGNED DEFAULT NULL,
  `level` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`realm`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblGuild`
--

CREATE TABLE IF NOT EXISTS `tblGuild` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `realm` smallint(5) UNSIGNED NOT NULL,
  `name` char(24) CHARACTER SET utf8mb4 DEFAULT NULL,
  `scanned` timestamp NULL DEFAULT NULL,
  `side` tinyint(3) UNSIGNED DEFAULT NULL,
  `members` smallint(5) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `realm-name` (`realm`,`name`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblRealm`
--

CREATE TABLE IF NOT EXISTS `tblRealm` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `region` enum('US','EU') CHARACTER SET utf8mb4 NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `locale` char(5) CHARACTER SET utf8mb4 NOT NULL,
  `rp` tinyint(3) UNSIGNED NOT NULL,
  `timezone` varchar(60) CHARACTER SET utf8mb4 DEFAULT NULL,
  `population` enum('new players','low','medium','high','full') CHARACTER SET utf8mb4 DEFAULT NULL,
  `house` smallint(5) UNSIGNED DEFAULT NULL,
  `canonical` varchar(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ownerrealm` varchar(100) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realmset` (`region`,`slug`),
  UNIQUE KEY `region` (`region`,`name`),
  KEY `house` (`house`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
