-- MySQL dump 10.14  Distrib 5.5.52-MariaDB, for Linux (x86_64)
--
-- Host: 192.168.0.24    Database: ebh2
-- ------------------------------------------------------
-- Server version	5.6.23-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ebh_user_blacklists`
--

DROP TABLE IF EXISTS `ebh_user_blacklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ebh_user_blacklist` (
  `uid` int(11) unsigned NOT NULL COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户帐号',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加日期',
  `remark` varchar(1024) NOT NULL DEFAULT '' COMMENT '备注',
  `operid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加用户ID',
  `crid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '网校ID',
  PRIMARY KEY (`uid`,`username`,`crid`),
  KEY `idx_crid` (`crid`) USING BTREE,
  KEY `idx_no` (`dateline`) USING BTREE COMMENT '时间戳，用以排序'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐号黑名单';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-24 10:24:47
