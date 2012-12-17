-- Pass Database
-- 
-- Host: localhost	Database: pass
-- Date: 2012-12-03 17:15:17
-- ------------------------------------------------------
-- MySql version	5.5.28

-- ----------------------------
-- Table structure for `pass_appliction`
-- ----------------------------
DROP TABLE IF EXISTS `pass_appliction`;
CREATE TABLE `pass_appliction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `key` varchar(50) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_check`
-- ----------------------------
DROP TABLE IF EXISTS `pass_check`;
CREATE TABLE `pass_check` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL,
  `data_id` int(10) unsigned NOT NULL,
  `check_time` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_connect`
-- ----------------------------
DROP TABLE IF EXISTS `pass_connect`;
CREATE TABLE `pass_connect` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `key` varchar(50) NOT NULL,
  `platform_url` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `sort_order` tinyint(3) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_inout`
-- ----------------------------
DROP TABLE IF EXISTS `pass_inout`;
CREATE TABLE `pass_inout` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `ip` int(11) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `session_key` varchar(50) NOT NULL,
  `inout_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pass_logs`;
CREATE TABLE `pass_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `ip` int(11) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `request_url` text NOT NULL,
  `request_data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member`;
CREATE TABLE `pass_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sex` tinyint(3) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `point_coin` int(11) NOT NULL,
  `point_level` int(11) NOT NULL,
  `verify_info` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_connect`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_connect`;
CREATE TABLE `pass_member_connect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `outer_id` varchar(255) NOT NULL,
  `connect_id` smallint(5) unsigned NOT NULL,
  `data` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_info`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_info`;
CREATE TABLE `pass_member_info` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `real_name` varchar(20) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `birthday` int(10) unsigned NOT NULL,
  `birth_region_id` int(10) unsigned NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `address` varchar(255) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `blood` tinyint(3) unsigned NOT NULL,
  `sign` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_login`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_login`;
CREATE TABLE `pass_member_login` (
  `id` int(10) unsigned NOT NULL,
  `login_count` mediumint(8) unsigned NOT NULL,
  `online_long` int(10) unsigned NOT NULL,
  `inout_first_id` bigint(20) unsigned NOT NULL,
  `inout_last_id` bigint(20) unsigned NOT NULL,
  `auto_login_key` varchar(50) NOT NULL,
  `auto_login_ip` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_qanda`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_qanda`;
CREATE TABLE `pass_member_qanda` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `pass_point_logs`
-- ----------------------------
DROP TABLE IF EXISTS `pass_point_logs`;
CREATE TABLE `pass_point_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `point_type` varchar(20) NOT NULL,
  `value` int(11) NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `appliction_id` int(10) unsigned NOT NULL,
  `operation_id` int(10) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

