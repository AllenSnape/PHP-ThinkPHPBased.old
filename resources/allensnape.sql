CREATE DATABASE  IF NOT EXISTS `allensnape` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `allensnape`;
-- MySQL dump 10.13  Distrib 5.7.17, for Win64 (x86_64)
--
-- Host: localhost    Database: allensnape
-- ------------------------------------------------------
-- Server version	5.7.16

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
-- Table structure for table `as_menu`
--

DROP TABLE IF EXISTS `as_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_menu` (
  `id` varchar(64) NOT NULL,
  `pid` varchar(64) DEFAULT NULL COMMENT '父菜单id',
  `name` varchar(255) DEFAULT '' COMMENT '菜单名称',
  `href` varchar(3072) DEFAULT NULL COMMENT '菜单跳转地址',
  `sort` decimal(10,0) DEFAULT '1' COMMENT '排序字段, 默认1; 越小越靠前',
  `icon` varchar(3072) DEFAULT NULL COMMENT '菜单图标',
  `hidden` decimal(1,0) DEFAULT '0' COMMENT '是否隐藏; 0: 显示, 1: 隐藏',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `permission` varchar(255) DEFAULT '' COMMENT '权限标识符, 预留字段',
  `create_by` varchar(64) NOT NULL,
  `create_time` decimal(10,0) DEFAULT '0',
  `update_by` varchar(64) NOT NULL,
  `update_time` decimal(10,0) DEFAULT '0',
  `disabled` decimal(1,0) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_menu`
--

LOCK TABLES `as_menu` WRITE;
/*!40000 ALTER TABLE `as_menu` DISABLE KEYS */;
INSERT INTO `as_menu` VALUES ('0634f5b9e89c679d08123eb685a709ed','ede5aeef8a4746ccae74d963efa6ae8e','管理员列表','../user/listPage.html?porder=create_time&amp;psort=desc',1,'',0,'','admin:user:listPage','admin123',1514917421,'admin123',1514917717,0),('11c2b9cb835260c03ffc6b430a5fdea9','ede5aeef8a4746ccae74d963efa6ae8e','添加管理员','',0,'',1,'','admin:user:add','admin123',1514917936,'admin123',1514917945,0),('21da9c485ae1699809405a76035dadef','ede5aeef8a4746ccae74d963efa6ae8e','设置管理员角色','',0,'',1,'','admin:user:setUserRoles','admin123',1514918035,'admin123',1514918035,0),('2ac8626f2582f285ba9c9ccf8f4961ec','ede5aeef8a4746ccae74d963efa6ae8e','角色列表','../role/listPage.html?porder=create_time&amp;psort=desc',2,'',0,'','admin:role:listPage','admin123',1514917473,'admin123',1514917729,0),('4272db96a651d093ecb90e449d39e39a','ede5aeef8a4746ccae74d963efa6ae8e','获取管理员列表','',0,'',1,'','admin:user:jsonlist','admin123',1514917897,'admin123',1514917897,0),('4e55efd1a8c6f6bc81ccc44eae25f3b1','ede5aeef8a4746ccae74d963efa6ae8e','管理员访问记录','../user/loglistPage.html?porder=create_time&psort=desc',4,'',0,'','admin:user:loglistPage','admin123',1514917565,'admin123',1514917753,0),('6062c334c1f3bcd3d9b00b18fae445b6','c969f9e7eb43cd4332783e360e68d53c','检查是否登录并返回管理员信息','',0,'',1,'','admin:user:logined','admin123',1514918115,'admin123',1514918115,0),('6481f40af97e5998c807957a338a7fec','ede5aeef8a4746ccae74d963efa6ae8e','添加菜单','',0,'',1,'','admin:menu:add','admin123',1514918312,'admin123',1514918312,0),('65920324a3bf06d6b08bb2438f8c6324','ede5aeef8a4746ccae74d963efa6ae8e','添加角色','',0,'',1,'','admin:role:add','admin123',1514918418,'admin123',1514918418,0),('67f4abc8a67c68992b93a3966e823520','ede5aeef8a4746ccae74d963efa6ae8e','修改菜单','',0,'',1,'','admin:menu:edit','admin123',1514918332,'admin123',1514918332,0),('6e69c3c46059533fc63b1859d4fd06d3','ede5aeef8a4746ccae74d963efa6ae8e','操作角色','',0,'',1,'','admin:role:dis','admin123',1514918445,'admin123',1514918445,0),('728543500c6c336b6a215146d876d897','ede5aeef8a4746ccae74d963efa6ae8e','修改管理员','',0,'',1,'','admin:user:edit','admin123',1514917967,'admin123',1514917967,0),('74db9b1990b67823f9e3dc9d8fb48a96','ede5aeef8a4746ccae74d963efa6ae8e','菜单json列表','',0,'',1,'','admin:menu:listJson','admin123',1514918295,'admin123',1514918295,0),('77b74d67f1177706402ae1c1e6ac73d3','c969f9e7eb43cd4332783e360e68d53c','管理员修改信息','',0,'',1,'','admin:user:editinfo','admin123',1514918150,'admin123',1514918150,0),('8cfba1305fb4f97543bc518bc02e650d','c969f9e7eb43cd4332783e360e68d53c','管理员首页页面跳转','',0,'',1,'','admin:home:homePage','admin123',1514918248,'admin123',1514918248,0),('955382f9edc1b97ed340a328f6982913','c969f9e7eb43cd4332783e360e68d53c','启动欢迎界面','',0,'',1,'','admin:home:welcomePage','admin123',1514918231,'admin123',1514918231,0),('9cf4e7b537a659064899e0f2936078c2','ede5aeef8a4746ccae74d963efa6ae8e','修改角色','',0,'',1,'','admin:role:edit','admin123',1514918432,'admin123',1514918432,0),('b132161cadc9efe79874066ab366d5e5','ede5aeef8a4746ccae74d963efa6ae8e','操作菜单','',0,'',1,'','admin:menu:dis','admin123',1514918348,'admin123',1514918348,0),('b3c12a1322777132295cc47e5dee4a4c','ede5aeef8a4746ccae74d963efa6ae8e','查询对应管理员的角色授权列表','',0,'',1,'','admin:user:userRoleListJson','admin123',1514918017,'admin123',1514918017,0),('c95094b42adac7ce5b75aa51c5574a86','ede5aeef8a4746ccae74d963efa6ae8e','角色菜单关联数据','',0,'',1,'','admin:role:roleMenuListJson','admin123',1514918386,'admin123',1514918386,0),('c969f9e7eb43cd4332783e360e68d53c','','首页','',0,'',1,'','','admin123',1514918080,'admin123',1514918089,0),('d04bc981dc9459c29358db48824e0921','ede5aeef8a4746ccae74d963efa6ae8e','菜单列表','../menu/listPage.html?porder=create_time&amp;psort=desc&amp;disabled=0',3,'',0,'','admin:menu:listPage','admin123',1514917490,'admin123',1514917736,0),('d8164873458c217f00852fbfd9e9f96a','ede5aeef8a4746ccae74d963efa6ae8e','设置角色菜单','',0,'',1,'','admin:role:setRoleMenus','admin123',1514918403,'admin123',1514918403,0),('d863d995e8102d063a5d94d2bc0c7b9f','ede5aeef8a4746ccae74d963efa6ae8e','操作管理员','',0,'',1,'','admin:user:dis','admin123',1514917984,'admin123',1514917984,0),('ede5aeef8a4746ccae74d963efa6ae8e','','系统管理','',1,'',0,'',NULL,'admin123',1514917358,'admin123',1514917358,0),('f12e738c0b4ca2c7b2f34a2d0daf9fe4','c969f9e7eb43cd4332783e360e68d53c','管理员登出','',0,'',1,'','admin:user:logout','admin123',1514918135,'admin123',1514918135,0);
/*!40000 ALTER TABLE `as_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `as_role`
--

DROP TABLE IF EXISTS `as_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_role` (
  `id` varchar(64) NOT NULL,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `create_by` varchar(64) NOT NULL,
  `create_time` decimal(10,0) DEFAULT '0',
  `update_by` varchar(64) NOT NULL,
  `update_time` decimal(10,0) DEFAULT '0',
  `disabled` decimal(1,0) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_role`
--

LOCK TABLES `as_role` WRITE;
/*!40000 ALTER TABLE `as_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `as_role_menu`
--

DROP TABLE IF EXISTS `as_role_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_role_menu` (
  `role_id` varchar(64) NOT NULL,
  `menu_id` varchar(64) NOT NULL,
  PRIMARY KEY (`role_id`,`menu_id`),
  KEY `fk_as_role_menu_menu_id_idx` (`menu_id`),
  CONSTRAINT `fk_as_role_menu_menu_id` FOREIGN KEY (`menu_id`) REFERENCES `as_menu` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_as_role_menu_role_id` FOREIGN KEY (`role_id`) REFERENCES `as_role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色与菜单关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_role_menu`
--

LOCK TABLES `as_role_menu` WRITE;
/*!40000 ALTER TABLE `as_role_menu` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `as_user`
--

DROP TABLE IF EXISTS `as_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_user` (
  `id` varchar(64) NOT NULL COMMENT 'id',
  `username` varchar(64) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL COMMENT '密码',
  `status` varchar(3072) DEFAULT NULL COMMENT '状态\n0/正常, 1/冻结, 2/保留用户',
  `name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `mobile` varchar(20) DEFAULT NULL COMMENT '管理员手机号',
  `remark` varchar(255) DEFAULT '',
  `create_by` varchar(64) NOT NULL,
  `create_time` decimal(10,0) DEFAULT '0',
  `update_by` varchar(64) NOT NULL,
  `update_time` decimal(10,0) DEFAULT '0',
  `disabled` decimal(1,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员列表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_user`
--

LOCK TABLES `as_user` WRITE;
/*!40000 ALTER TABLE `as_user` DISABLE KEYS */;
INSERT INTO `as_user` VALUES ('admin123','admin123','eabc71bd3e5eeb7cbb72da1dcf4210407c43b9545bcf318264445fba3a5db155','admin123','18583838383','1','123','123',1506399918,'admin123',1514836452,0),('aeda5fde4f46e2a143a72d43962dc211','admin1234','036c44dc467bc4cc391f2eb610d2b82e7d3284347f83acb9062f2398c7d8992e',NULL,'admin1234','','','admin123',1514336474,'admin123',1514835444,0);
/*!40000 ALTER TABLE `as_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `as_user_log`
--

DROP TABLE IF EXISTS `as_user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_user_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(64) NOT NULL,
  `type` varchar(20) DEFAULT NULL COMMENT '操作类型',
  `title` varchar(64) DEFAULT NULL COMMENT '标题',
  `content` text COMMENT '请求携带的数据',
  `remote_ip` varchar(64) DEFAULT NULL COMMENT '请求的ip',
  `request_uri` varchar(255) DEFAULT NULL COMMENT '请求的地址',
  `user_agent` text,
  `method` varchar(64) DEFAULT NULL COMMENT '请求的方式',
  `exception` text COMMENT '请求造成的异常',
  `create_time` decimal(10,0) DEFAULT '0' COMMENT '请求时间',
  PRIMARY KEY (`id`),
  KEY `fk_as_user_log_user_id_idx` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=774 DEFAULT CHARSET=utf8 COMMENT='管理员访问记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_user_log`
--

LOCK TABLES `as_user_log` WRITE;
/*!40000 ALTER TABLE `as_user_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_user_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `as_user_role`
--

DROP TABLE IF EXISTS `as_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `as_user_role` (
  `user_id` varchar(64) NOT NULL,
  `role_id` varchar(64) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_as_user_role_role_id_idx` (`role_id`),
  CONSTRAINT `fk_as_user_role_role_id` FOREIGN KEY (`role_id`) REFERENCES `as_role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_as_user_role_user_id` FOREIGN KEY (`user_id`) REFERENCES `as_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员与角色关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `as_user_role`
--

LOCK TABLES `as_user_role` WRITE;
/*!40000 ALTER TABLE `as_user_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `as_user_role` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-01-03  2:44:31
