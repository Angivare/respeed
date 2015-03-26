-- phpMyAdmin SQL Dump
-- version 4.0.2
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 26 Mars 2015 à 21:13
-- Version du serveur: 5.6.11-log
-- Version de PHP: 5.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `respeed`
--
CREATE DATABASE IF NOT EXISTS `respeed` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `respeed`;

-- --------------------------------------------------------

--
-- Structure de la table `forums`
--

CREATE TABLE `forums` (
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` text NOT NULL,
  `fetched_at` timestamp NOT NULL,
  PRIMARY KEY (`forum_id`,`page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `topics`
--

CREATE TABLE `topics` (
  `topic_id` int(11) unsigned NOT NULL,
  `topic_mode` tinyint(4) unsigned NOT NULL,
  `forum_id` int(11) unsigned NOT NULL,
  `page` smallint(6) unsigned NOT NULL,
  `vars` text NOT NULL,
  `fetched_at` timestamp NOT NULL,
  PRIMARY KEY (`topic_id`,`topic_mode`,`forum_id`,`page`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
