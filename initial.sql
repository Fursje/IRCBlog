-- phpMyAdmin SQL Dump
-- version 3.4.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 24, 2016 at 04:42 PM
-- Server version: 5.1.61
-- PHP Version: 5.4.6--pl0-gentoo

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ircblog`
--

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(32) NOT NULL,
  `pass` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `apiLink`
--

CREATE TABLE IF NOT EXISTS `apiLink` (
  `api_id` int(11) NOT NULL,
  `bot_id` int(11) NOT NULL,
  UNIQUE KEY `api_id` (`api_id`,`bot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bot`
--

CREATE TABLE IF NOT EXISTS `bot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(64) NOT NULL,
  `network` int(11) NOT NULL,
  `authNick` varchar(32) NOT NULL,
  `authPass` varchar(32) NOT NULL,
  `authEnabled` tinyint(1) NOT NULL,
  `authType` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bot` (`nick`,`network`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `channel`
--

CREATE TABLE IF NOT EXISTS `channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `secret` tinyint(4) NOT NULL,
  `password` varchar(32) NOT NULL,
  `network_id` int(11) NOT NULL,
  `bot_id` int(11) NOT NULL,
  `requester` varchar(64) NOT NULL,
  `ignoreUsers` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel` (`name`,`network_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `url_hash` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(11) NOT NULL,
  `channel` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `chan_id` int(11) NOT NULL,
  `poster` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `context` text COLLATE utf8_unicode_ci NOT NULL,
  `hide` tinyint(4) NOT NULL DEFAULT '0',
  `hide_reason` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thumb_name` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  `image_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `img_host` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `post_type` enum('irc','mms','mail') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'irc',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_hash` (`url_hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_link`
--

CREATE TABLE IF NOT EXISTS `mail_link` (
  `channel_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  UNIQUE KEY `channel_id` (`channel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network`
--

CREATE TABLE IF NOT EXISTS `network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `server` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

