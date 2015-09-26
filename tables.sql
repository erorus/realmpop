SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `realmpop`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblCharacter`
--

CREATE TABLE IF NOT EXISTS `tblCharacter` (
  `name` char(12) COLLATE utf8_unicode_ci NOT NULL,
  `realm` smallint(5) unsigned NOT NULL,
  `guild` int(10) unsigned DEFAULT NULL,
  `scanned` timestamp NULL DEFAULT NULL,
  `race` enum('Human','Orc','Dwarf','Night Elf','Undead','Tauren','Gnome','Troll','Goblin','Blood Elf','Draenei','Fel Orc','Naga','Broken','Skeleton','Vrykul','Tuskarr','Forest Troll','Taunka','Northrend Skeleton','Ice Troll','Worgen','Gilnean','Pandaren','PandarenA','PandarenH') COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` enum('Warrior','Paladin','Hunter','Rogue','Priest','Death Knight','Shaman','Mage','Warlock','Monk','Druid') COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female') COLLATE utf8_unicode_ci DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`realm`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblGuild`
--

CREATE TABLE IF NOT EXISTS `tblGuild` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `realm` smallint(5) unsigned NOT NULL,
  `name` char(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `scanned` timestamp NULL DEFAULT NULL,
  `side` enum('Alliance','Horde') COLLATE utf8_unicode_ci DEFAULT NULL,
  `members` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `realm-name` (`realm`,`name`(8))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1050790 ;

-- --------------------------------------------------------

--
-- Table structure for table `tblRealm`
--

CREATE TABLE IF NOT EXISTS `tblRealm` (
  `id` smallint(5) unsigned NOT NULL,
  `region` enum('US','EU') COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `locale` char(5) COLLATE utf8_unicode_ci NOT NULL,
  `pvp` tinyint(3) unsigned NOT NULL,
  `rp` tinyint(3) unsigned NOT NULL,
  `timezone` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `battlegroup` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `population` enum('low','medium','high','full') COLLATE utf8_unicode_ci DEFAULT NULL,
  `house` smallint(5) unsigned DEFAULT NULL,
  `canonical` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ownerrealm` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastfetch` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realmset` (`region`,`slug`),
  UNIQUE KEY `region` (`region`,`name`),
  KEY `house` (`house`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table structure for table `tblSide`
--

CREATE TABLE IF NOT EXISTS `tblSide` (
  `race` enum('Human','Orc','Dwarf','Night Elf','Undead','Tauren','Gnome','Troll','Goblin','Blood Elf','Draenei','Fel Orc','Naga','Broken','Skeleton','Vrykul','Tuskarr','Forest Troll','Taunka','Northrend Skeleton','Ice Troll','Worgen','Gilnean','Pandaren','PandarenA','PandarenH') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Human',
  `side` enum('Alliance','Horde','Neutral') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`race`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tblSide`
--

INSERT INTO `tblSide` (`race`, `side`) VALUES
('Human', 'Alliance'),
('Orc', 'Horde'),
('Dwarf', 'Alliance'),
('Night Elf', 'Alliance'),
('Undead', 'Horde'),
('Tauren', 'Horde'),
('Gnome', 'Alliance'),
('Troll', 'Horde'),
('Goblin', 'Horde'),
('Blood Elf', 'Horde'),
('Draenei', 'Alliance'),
('Fel Orc', 'Neutral'),
('Naga', 'Neutral'),
('Broken', 'Neutral'),
('Skeleton', 'Neutral'),
('Vrykul', 'Neutral'),
('Tuskarr', 'Neutral'),
('Forest Troll', 'Neutral'),
('Taunka', 'Neutral'),
('Northrend Skeleton', 'Neutral'),
('Ice Troll', 'Neutral'),
('Worgen', 'Alliance'),
('Gilnean', 'Neutral'),
('Pandaren', 'Neutral'),
('PandarenA', 'Alliance'),
('PandarenH', 'Horde');
