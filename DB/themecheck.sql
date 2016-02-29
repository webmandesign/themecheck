-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Lun 29 Février 2016 à 14:39
-- Version du serveur: 5.5.44-0ubuntu0.14.04.1
-- Version de PHP: 5.5.9-1ubuntu4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `themecheck`
--

-- --------------------------------------------------------

--
-- Structure de la table `download`
--

DROP TABLE IF EXISTS `download`;
CREATE TABLE IF NOT EXISTS `download` (
  `user_ip` int(11) NOT NULL AUTO_INCREMENT,
  `date_download` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_ip`,`date_download`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2147483647 ;

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

DROP TABLE IF EXISTS `theme`;
CREATE TABLE IF NOT EXISTS `theme` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(25) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'hash code of archive',
  `hash_md5` binary(16) NOT NULL,
  `hash_sha1` binary(20) NOT NULL,
  `name` varchar(64) NOT NULL,
  `namesanitized` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `themedir` varchar(256) NOT NULL COMMENT 'name of theme directory in wordpress/joomla directories',
  `themetype` int(11) unsigned NOT NULL COMMENT '0:undefined, 1:wordpress, 2:joomla, 4:wordpress-child',
  `parentId` int(11) DEFAULT NULL,
  `cmsVersion` varchar(16) CHARACTER SET ascii NOT NULL,
  `score` float NOT NULL,
  `criticalCount` int(10) unsigned NOT NULL DEFAULT '0',
  `warningsCount` int(10) unsigned NOT NULL DEFAULT '0',
  `infoCount` int(10) unsigned NOT NULL DEFAULT '0',
  `zipfilename` varchar(260) NOT NULL,
  `zipmimetype` varchar(64) CHARACTER SET ascii NOT NULL,
  `zipfilesize` int(11) unsigned NOT NULL,
  `userIp` int(10) unsigned NOT NULL,
  `author` varchar(256) DEFAULT NULL,
  `description` text,
  `descriptionBB` text,
  `themeUri` varchar(260) DEFAULT NULL,
  `version` varchar(32) DEFAULT NULL,
  `authorUri` varchar(260) DEFAULT NULL,
  `authorMail` varchar(260) DEFAULT NULL,
  `tags` varchar(256) DEFAULT NULL,
  `layout` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '0 : undefined, 1 : fixed, 2 : fluid, 3 : responsive',
  `license` smallint(5) unsigned DEFAULT NULL,
  `licenseUri` varchar(260) DEFAULT NULL,
  `licenseText` varchar(2048) DEFAULT NULL,
  `copyright` varchar(256) DEFAULT NULL,
  `isThemeForest` tinyint(1) NOT NULL DEFAULT '0',
  `isTemplateMonster` tinyint(1) NOT NULL DEFAULT '0',
  `isCreativeMarket` tinyint(1) NOT NULL DEFAULT '0',
  `isPiqpaq` tinyint(1) NOT NULL DEFAULT '0',
  `isNsfw` tinyint(1) NOT NULL DEFAULT '0',
  `isOpenSource` tinyint(1) DEFAULT NULL,
  `filesIncluded` varchar(256) NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'first insertion in DB',
  `modificationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'last update of archive content',
  `validationDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'last validation date',
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_id` (`hash`),
  KEY `name` (`name`,`namesanitized`),
  KEY `zipfilesize` (`zipfilesize`),
  KEY `validationDate` (`validationDate`),
  KEY `score` (`score`),
  KEY `themetype` (`themetype`),
  KEY `creationDate` (`creationDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9910 ;

-- --------------------------------------------------------

--
-- Structure de la table `theme_wpvulnd`
--

DROP TABLE IF EXISTS `theme_wpvulnd`;
CREATE TABLE IF NOT EXISTS `theme_wpvulnd` (
  `theme_hash` char(25) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `vuln_id` int(11) NOT NULL,
  PRIMARY KEY (`theme_hash`,`vuln_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `wpvulndb_vulnerabilities`
--

DROP TABLE IF EXISTS `wpvulndb_vulnerabilities`;
CREATE TABLE IF NOT EXISTS `wpvulndb_vulnerabilities` (
  `id` int(11) NOT NULL,
  `title` varchar(260) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `published_date` datetime NOT NULL,
  `vuln_type` varchar(32) NOT NULL,
  `fixed_in` varchar(32) DEFAULT NULL,
  `refs` varchar(2048) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
