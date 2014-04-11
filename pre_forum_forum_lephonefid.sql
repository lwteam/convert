/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50524
Source Host           : localhost:3306
Source Database       : discuzx31

Target Server Type    : MYSQL
Target Server Version : 50524
File Encoding         : 65001

Date: 2014-04-12 02:46:17
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pre_forum_forum_lephonefid
-- ----------------------------
DROP TABLE IF EXISTS `pre_forum_forum_lephonefid`;
CREATE TABLE `pre_forum_forum_lephonefid` (
  `fid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lephonefid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fid`),
  UNIQUE KEY `lephonefid` (`lephonefid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
