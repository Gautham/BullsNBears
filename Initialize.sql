-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 08, 2013 at 03:35 PM
-- Server version: 5.5.34-0ubuntu0.13.04.1
-- PHP Version: 5.4.9-4ubuntu2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `bought_stock`
--

CREATE TABLE IF NOT EXISTS `boughtStocks` (
  `id` varchar(30) NOT NULL,
  `symbol` varchar(20) NOT NULL DEFAULT '',
  `amount` int(11) NOT NULL,
  `avg` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`,`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE IF NOT EXISTS `history` (
  `id` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `symbol` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `skey` bigint(20) NOT NULL DEFAULT '-1',
  `amount` int(11) NOT NULL,
  `value` decimal(15,2) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE IF NOT EXISTS `player` (
  `id` varchar(30) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(64) NOT NULL,
  `liquidCash` int(11) NOT NULL,
  `marketValue` int(11) NOT NULL,
  `shortValue` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `dayWorth` int(11) NOT NULL,
  `weekWorth` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `skey` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id` varchar(30) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `type` varchar(2) NOT NULL,
  `scheduledPrice` decimal(15,2) NOT NULL,
  `amount` int(11) NOT NULL,
  `pendingAmount` int(11) NOT NULL,
  `flag` char(1) NOT NULL,
  PRIMARY KEY (`skey`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `shortedStocks` (
  `id` varchar(30) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `amount` int(11) NOT NULL,
  `value` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`,`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE IF NOT EXISTS `stocks` (
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL DEFAULT '',
  `value` decimal(15,2) DEFAULT NULL,
  `change` decimal(15,2) DEFAULT NULL,
  `dayLow` decimal(15,2) DEFAULT NULL,
  `dayHigh` decimal(15,2) DEFAULT NULL,
  `weekLow` decimal(15,2) DEFAULT NULL,
  `weekHigh` decimal(15,2) DEFAULT NULL,
  `changePerc` decimal(15,2) NOT NULL,
  PRIMARY KEY (`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
