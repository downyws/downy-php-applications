-- Pass Database
-- 
-- Host: localhost	Database: pass
-- Date: 2013-03-31 20:57:19
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
  `home_page` varchar(255) NOT NULL,
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
  `cate_id` mediumint(8) unsigned NOT NULL,
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
-- Records of pass_connect
-- ----------------------------
INSERT INTO pass_connect VALUES ('1', '支付宝', 'alipay', 'https://open.alipay.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('2', '百度', 'baidu', 'http://open.baidu.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('3', '豆瓣', 'douban', 'http://developers.douban.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('4', '天翼189', 'esurfing', 'http://open.189.cn/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('5', 'Facebook', 'facebook', 'http://developers.facebook.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('6', '飞信', 'fetion', 'http://i2.feixin.10086.cn/open/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('7', '谷歌', 'google', 'https://developers.google.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('8', '京东', 'jd360', 'http://jos.360buy.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('9', '开心网', 'kaixin001', 'http://open.kaixin001.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('10', 'MSN', 'msn', 'http://dev.live.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('11', '网易通行证', 'netease', 'http://reg.163.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('12', '网易微博', 'neteasewb', 'http://open.t.163.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('13', '奇虎360', 'qihu360', 'http://dev.app.360.cn/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('14', '人人网', 'renren', 'http://dev.renren.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('15', '盛大', 'sdo', 'http://open.sdo.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('16', '搜狐微博', 'sohu', 'https://open.sohu.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('17', '淘宝', 'taobao', 'http://open.taobao.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('18', '腾讯互联', 'tencent', 'http://open.qq.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('19', '腾讯微博', 'tencentwb', 'http://dev.t.qq.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('20', '天涯社区', 'tianya', 'http://open.tianya.cn/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('21', '土豆', 'tudou', 'http://dev.tudou.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('22', '推特', 'twitter', 'https://dev.twitter.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('23', '凡客', 'vancl', 'http://open.vancl.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('24', '新浪微博', 'weibo', 'http://open.weibo.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('25', '雅虎', 'yahoo', 'http://developer.yahoo.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('26', '优酷', 'youku', 'http://open.youku.com/', '', '1', '0', '0', '0');
INSERT INTO pass_connect VALUES ('27', 'YouTube', 'youtube', 'http://youtube.com/dev', '', '2', '0', '0', '0');

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
  `create_time` int(10) unsigned NOT NULL,
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
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `portrait` varchar(255) NOT NULL,
  `sex` tinyint(3) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `point_coin` int(11) NOT NULL,
  `point_level` int(11) NOT NULL,
  `online_long` int(10) unsigned NOT NULL,
  `auto_login_ip` int(11) NOT NULL,
  `auto_login_key` varchar(50) NOT NULL,
  `first_inout_id` bigint(20) unsigned NOT NULL,
  `last_inout_id` bigint(20) unsigned NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_application`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_application`;
CREATE TABLE `pass_member_application` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `last_use_time` int(10) unsigned NOT NULL,
  `application_id` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
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
  `update_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_info`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_info`;
CREATE TABLE `pass_member_info` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `birthday` int(11) NOT NULL,
  `blood` tinyint(3) unsigned NOT NULL,
  `sign` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_member_privacy`
-- ----------------------------
DROP TABLE IF EXISTS `pass_member_privacy`;
CREATE TABLE `pass_member_privacy` (
  `member_id` int(10) unsigned NOT NULL,
  `field` varchar(50) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`member_id`,`field`)
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `pass_support`
-- ----------------------------
DROP TABLE IF EXISTS `pass_support`;
CREATE TABLE `pass_support` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` mediumint(9) NOT NULL,
  `style` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `contact` varchar(50) NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `is_dispose` tinyint(4) NOT NULL,
  `ip` int(11) NOT NULL,
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
