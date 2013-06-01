-- ro2l10n Database
-- 
-- Host: localhost	Database: ro2l10n
-- Date: 2013-05-08 16:09:14
-- ------------------------------------------------------
-- MySql version	5.5.28

-- ----------------------------
-- Table structure for `l10n_dict`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_dict`;
CREATE TABLE `l10n_dict` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `en` varchar(255) NOT NULL,
  `cn` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `en` (`en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `l10n_file`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_file`;
CREATE TABLE `l10n_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(50) NOT NULL,
  `file_name_short` varchar(10) NOT NULL,
  `desc` varchar(255) NOT NULL DEFAULT '',
  `detail` text NOT NULL,
  `wait_t_count` int(10) unsigned NOT NULL,
  `wait_p_count` int(10) unsigned NOT NULL,
  `wait_a_count` int(10) unsigned NOT NULL,
  `data_count` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  `name_key` varchar(20) NOT NULL,
  `name_val` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file_name` (`file_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `l10n_mapping`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_mapping`;
CREATE TABLE `l10n_mapping` (
  `key` varchar(20) NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  `t_user_id` int(10) unsigned NOT NULL,
  `p_user_id` int(10) unsigned NOT NULL,
  `a_user_id` int(10) unsigned NOT NULL,
  `occ_user_id` int(10) unsigned NOT NULL,
  `occ_time` int(10) unsigned NOT NULL,
  `state` tinyint(3) unsigned NOT NULL,
  `content_old_en` text NOT NULL,
  `content_old_cn` text NOT NULL,
  `content_new_en` text NOT NULL,
  `content_new_cn` text NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cate` (`file_id`),
  KEY `user` (`occ_user_id`),
  KEY `state` (`state`),
  KEY `key` (`key`),
  KEY `fstate` (`file_id`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `l10n_mapping_temp`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_mapping_temp`;
CREATE TABLE `l10n_mapping_temp` (
  `key` varchar(20) NOT NULL,
  `content_new_en` text NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `l10n_notice`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_notice`;
CREATE TABLE `l10n_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `style` varchar(100) NOT NULL,
  `sort` tinyint(4) NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `l10n_notice` (`id`, `content`, `style`, `sort`, `start_time`, `end_time`) VALUES (0, '', '', 0, 0, 0);

-- ----------------------------
-- Table structure for `l10n_user`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_user`;
CREATE TABLE `l10n_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `individuation` text NOT NULL,
  `state` tinyint(3) unsigned NOT NULL,
  `task_max_count` int(10) unsigned NOT NULL,
  `task_occ_count` int(10) unsigned NOT NULL,
  `dedicate_t_count` int(10) unsigned NOT NULL,
  `dedicate_p_count` int(10) unsigned NOT NULL,
  `dedicate_a_count` int(10) unsigned NOT NULL,
  `super` tinyint(3) unsigned NOT NULL,
  `dict` tinyint(3) unsigned NOT NULL,
  `translation` tinyint(3) unsigned NOT NULL,
  `proof` tinyint(3) unsigned NOT NULL,
  `audit` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `l10n_user` (`id`, `nick`, `email`, `password`, `individuation`, `state`, `task_max_count`, `task_occ_count`, `dedicate_t_count`, `dedicate_p_count`, `dedicate_a_count`, `super`, `dict`, `translation`, `proof`, `audit`) VALUES (0, 'Wing075', 'wing075@gmail.com', 'd41d8cd98f00b204e9800998ecf8427e', '', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0);

-- ----------------------------
-- Table structure for `l10n_auto_update`
-- ----------------------------
DROP TABLE IF EXISTS `l10n_auto_update`;
CREATE TABLE `l10n_auto_update` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(20) NOT NULL,
  `path` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
