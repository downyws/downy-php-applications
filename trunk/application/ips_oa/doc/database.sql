-- Information Push System Database
-- 
-- Host: localhost	Database: ips
-- Date: 2012-08-01 20:09:38
-- ------------------------------------------------------
-- MySql version	5.5.21

-- ----------------------------
-- Table structure for `ips_channel`
-- ----------------------------
DROP TABLE IF EXISTS `ips_channel`;
CREATE TABLE `ips_channel` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `last_run` int(10) unsigned NOT NULL DEFAULT '0',
  `is_disable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_log`
-- ----------------------------
DROP TABLE IF EXISTS `ips_log`;
CREATE TABLE `ips_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `data_id` int(10) unsigned NOT NULL,
  `data_table` tinyint(3) unsigned NOT NULL,
  `operation_type` tinyint(3) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_send_list`
-- ----------------------------
DROP TABLE IF EXISTS `ips_send_list`;
CREATE TABLE `ips_send_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_id` int(10) unsigned NOT NULL,
  `task_id` int(10) unsigned NOT NULL,
  `send_time` int(10) unsigned NOT NULL DEFAULT '0',
  `send_state` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `data` text,
  `page_view` smallint(5) unsigned NOT NULL DEFAULT '0',
  `first_read_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_read_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_short_uri`
-- ----------------------------
DROP TABLE IF EXISTS `ips_short_uri`;
CREATE TABLE `ips_short_uri` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(10) unsigned NOT NULL DEFAULT '0',
  `key` varchar(8) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `uri` text NOT NULL,
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  `is_disable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_target`
-- ----------------------------
DROP TABLE IF EXISTS `ips_target`;
CREATE TABLE `ips_target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact` varchar(100) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_disable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact` (`contact`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_task_multi`
-- ----------------------------
DROP TABLE IF EXISTS `ips_task_multi`;
CREATE TABLE `ips_task_multi` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `channel_id` smallint(5) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `check_user_id` int(10) unsigned NOT NULL,
  `check_time` int(10) unsigned NOT NULL,
  `plan_send_time` int(10) unsigned NOT NULL,
  `send_count` mediumint(8) unsigned NOT NULL,
  `accept_rate` mediumint(8) unsigned NOT NULL,
  `send_rate` mediumint(8) unsigned NOT NULL,
  `send_state` tinyint(3) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_task_single`
-- ----------------------------
DROP TABLE IF EXISTS `ips_task_single`;
CREATE TABLE `ips_task_single` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `channel_id` smallint(5) unsigned NOT NULL,
  `plan_send_time` int(10) unsigned NOT NULL,
  `send_state` tinyint(3) unsigned NOT NULL,
  `send_time` int(10) unsigned NOT NULL DEFAULT '0',
  `page_view` smallint(5) unsigned NOT NULL DEFAULT '0',
  `first_read_time` int(10) unsigned NOT NULL DEFAULT '0',
  `last_read_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ips_user`
-- ----------------------------
DROP TABLE IF EXISTS `ips_user`;
CREATE TABLE `ips_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `power` text,
  `channel` text,
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `is_disable` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `tasksingle_limit_day` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of `ips_user`
-- ----------------------------
INSERT INTO ips_user VALUES ('1', 'admin', '202cb962ac59075b964b07152d234b70', 'USER:READ;USER:EDIT;', null, '0', '0', '0');

