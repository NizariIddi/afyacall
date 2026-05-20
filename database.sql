-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: afyacall
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `user_name` varchar(120) NOT NULL DEFAULT '',
  `user_role` varchar(20) NOT NULL DEFAULT '',
  `action` varchar(255) NOT NULL,
  `module` varchar(60) NOT NULL DEFAULT '',
  `record_id` int unsigned NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 11:57:20'),(2,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 12:58:45'),(3,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 13:31:42'),(4,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 13:39:11'),(5,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-18 21:51:14'),(6,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 06:00:10'),(7,1,'System Admin','admin','Added health tip: Ebola (general)','telecom',6,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 06:41:25'),(8,1,'System Admin','admin','Broadcast health tip: Ebola to 1 patients','telecom',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 06:48:09'),(9,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:13:28'),(10,1,'System Admin','admin','Created user: Blantina John (doctor)','admin',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:36:44'),(11,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:37:38'),(12,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:37:55'),(13,2,'Blantina John','doctor','Logged out','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:41:25'),(14,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:41:31'),(15,1,'System Admin','admin','Added shift for doctor #2 on 2026-05-19 (afternoon)','scheduling',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:47:34'),(16,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:54:08'),(17,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 07:54:14'),(18,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 08:07:36'),(19,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 08:10:58'),(20,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 08:35:25'),(21,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 09:06:34'),(22,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 09:28:47'),(23,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 09:29:34'),(24,2,'Blantina John','doctor','Logged out','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 09:39:39'),(25,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:34:41'),(26,2,'Blantina John','doctor','Broadcast health tip: Pumzika Vizuri to 1 patients','telecom',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:42:51'),(27,2,'Blantina John','doctor','Deleted health tip: Ebola','telecom',6,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:43:29'),(28,2,'Blantina John','doctor','Logged out','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:44:34'),(29,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:48:46'),(30,2,'Blantina John','doctor','Logged out','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:49:42'),(31,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:49:52'),(32,1,'System Admin','admin','Deactivated user: Blantina John','admin',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:51:09'),(33,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:51:13'),(34,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:51:32'),(35,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:51:39'),(36,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:51:43'),(37,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:52:13'),(38,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 10:52:36'),(39,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 19:57:19'),(40,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-19 21:02:06'),(41,1,'System Admin','admin','Activated user: Blantina John','admin',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 00:19:16'),(42,1,'System Admin','admin','Sent emergency alert: Cleaning Vercels (info) to 1 recipients','telecom',3,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 00:59:27'),(43,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 01:59:50'),(44,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 02:35:22'),(45,1,'System Admin','admin','Logged in','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 12:01:37'),(46,1,'System Admin','admin','Logged out','auth',1,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 12:24:44'),(47,2,'Blantina John','doctor','Logged in','auth',2,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','2026-05-20 12:25:03');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','nurse') DEFAULT 'nurse',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'System Admin','admin@afyacall.com','0712345678','$2a$12$yvGK26g0MQkvE87Ib1x9t.xO2r.Zq0GkQtS/pMh5bEbbWt1g0UYaq','admin','2026-05-17 22:42:49',1,'2026-05-20 12:01:37',NULL,NULL),(2,'Blantina John','john@afyacall.com',NULL,'$2y$12$foHxBYGc1oudCKcQamz6a.aRgzzOuFjpaSSljkU02qFaz2UCgWsMe','doctor','2026-05-19 04:36:43',1,'2026-05-20 12:25:03',NULL,NULL);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `appointment_date` datetime NOT NULL,
  `status` enum('Pending','Completed','Missed') DEFAULT 'Pending',
  `remarks` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,'2026-05-18 04:11:00','Completed','fast and quick','2026-05-17 23:11:21');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `broadcast_logs`
--

DROP TABLE IF EXISTS `broadcast_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `broadcast_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('alert','tip') NOT NULL DEFAULT 'alert',
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `severity` enum('info','warning','critical') NOT NULL DEFAULT 'info',
  `language` varchar(10) NOT NULL DEFAULT 'both',
  `target_audience` varchar(30) NOT NULL DEFAULT 'all',
  `recipients_count` int unsigned NOT NULL DEFAULT '0',
  `sent_by` int unsigned NOT NULL DEFAULT '0',
  `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `broadcast_logs`
--

LOCK TABLES `broadcast_logs` WRITE;
/*!40000 ALTER TABLE `broadcast_logs` DISABLE KEYS */;
INSERT INTO `broadcast_logs` VALUES (1,'tip','Ebola','sllsafalsk asdkfjlsa fsaf\n\n,asd.fmasdfsfd af','info','both','all',1,1,'2026-05-19 06:48:08'),(2,'tip','Pumzika Vizuri','Lala kwa saa 7-8 kila usiku. Usingizi mzuri husaidia mwili kupona na kuboresha afya ya akili.\n\nSleep 7-8 hours every night. Good sleep helps your body recover and improves mental health.','info','both','all',1,2,'2026-05-19 10:42:51'),(3,'alert','Cleaning Vercels','Wash any item you use for any meal.','info','both','all',1,1,'2026-05-20 00:59:27');
/*!40000 ALTER TABLE `broadcast_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_shifts`
--

DROP TABLE IF EXISTS `doctor_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_shifts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` int unsigned NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('morning','afternoon','night','custom') NOT NULL DEFAULT 'morning',
  `shift_start` time NOT NULL DEFAULT '07:00:00',
  `shift_end` time NOT NULL DEFAULT '14:00:00',
  `department` varchar(100) NOT NULL DEFAULT '',
  `notes` text,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doctor_date_type` (`doctor_id`,`shift_date`,`shift_type`),
  KEY `idx_doctor` (`doctor_id`),
  KEY `idx_shift_date` (`shift_date`),
  KEY `idx_dept` (`department`(20))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_shifts`
--

LOCK TABLES `doctor_shifts` WRITE;
/*!40000 ALTER TABLE `doctor_shifts` DISABLE KEYS */;
INSERT INTO `doctor_shifts` VALUES (1,2,'2026-05-19','afternoon','14:00:00','21:00:00','Dentists','',1,'2026-05-19 07:47:34');
/*!40000 ALTER TABLE `doctor_shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_reports`
--

DROP TABLE IF EXISTS `emergency_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_reports` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(30) NOT NULL DEFAULT '',
  `session_id` varchar(100) NOT NULL DEFAULT '',
  `resolved` tinyint(1) NOT NULL DEFAULT '0',
  `reported_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reported` (`reported_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_reports`
--

LOCK TABLES `emergency_reports` WRITE;
/*!40000 ALTER TABLE `emergency_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `emergency_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `health_tips`
--

DROP TABLE IF EXISTS `health_tips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `health_tips` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `body_sw` text NOT NULL,
  `body_en` text NOT NULL,
  `category` enum('nutrition','exercise','malaria','diabetes','hiv','hygiene','maternal','mental','general') NOT NULL DEFAULT 'general',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `broadcast_count` int unsigned NOT NULL DEFAULT '0',
  `last_broadcast` datetime DEFAULT NULL,
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_tips`
--

LOCK TABLES `health_tips` WRITE;
/*!40000 ALTER TABLE `health_tips` DISABLE KEYS */;
INSERT INTO `health_tips` VALUES (1,'Kunywa Maji Safi','Kunywa angalau lita 2 za maji safi kila siku. Maji husaidia mwili kufanya kazi vizuri na kuzuia magonjwa.','Drink at least 2 litres of clean water every day. Water helps your body function well and prevents disease.','nutrition',1,0,NULL,1,'2026-05-19 06:08:26'),(2,'Lala Kwenye Chandarua','Lala kwenye chandarua chenye dawa kila usiku. Chandarua kinakuwa na ufanisi zaidi dhidi ya malaria.','Sleep under a treated mosquito net every night. Nets are your best protection against malaria.','malaria',1,0,NULL,1,'2026-05-19 06:08:26'),(3,'Osha Mikono','Osha mikono yako kwa sabuni na maji kabla ya kula na baada ya kutoka chooni. Hii inazuia kuenea kwa magonjwa.','Wash your hands with soap and water before eating and after using the toilet. This prevents the spread of disease.','hygiene',1,0,NULL,1,'2026-05-19 06:08:26'),(4,'Pumzika Vizuri','Lala kwa saa 7-8 kila usiku. Usingizi mzuri husaidia mwili kupona na kuboresha afya ya akili.','Sleep 7-8 hours every night. Good sleep helps your body recover and improves mental health.','mental',1,1,'2026-05-19 10:42:51',1,'2026-05-19 06:08:26'),(5,'Pima Sukari Mara kwa Mara','Watu wenye kisukari wanapaswa kupima sukari yao kila siku. Dawa zako zinaweza kusaidia kudhibiti ugonjwa.','People with diabetes should monitor their blood sugar daily. Your medication can help control your condition.','diabetes',1,0,NULL,1,'2026-05-19 06:08:26');
/*!40000 ALTER TABLE `health_tips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `diagnosis` text NOT NULL,
  `medication` text,
  `allergies` text,
  `notes` text,
  `visit_date` date DEFAULT NULL,
  `next_appointment` date DEFAULT NULL,
  `recorded_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `recorded_by` (`recorded_by`),
  CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_records`
--

LOCK TABLES `medical_records` WRITE;
/*!40000 ALTER TABLE `medical_records` DISABLE KEYS */;
INSERT INTO `medical_records` VALUES (1,1,'Nothing found on the diognosis done today','Patient should take only paracetamol for handiling pain.','None','This is good for him make sure that he comes back letter','2026-05-21','2026-05-29',1,'2026-05-17 23:43:55');
/*!40000 ALTER TABLE `medical_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `message` text NOT NULL,
  `type` enum('SMS','USSD','Reminder','Emergency') DEFAULT NULL,
  `status` enum('Sent','Pending','Failed') DEFAULT 'Pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_code` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `blood_group` varchar(5) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `registered_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_code` (`patient_code`),
  UNIQUE KEY `phone` (`phone`),
  KEY `registered_by` (`registered_by`),
  KEY `idx_patient_code` (`patient_code`),
  KEY `idx_phone` (`phone`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`registered_by`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,'AC-62501','Nizari Iddi','Male','2026-05-21','0750761558','Dodoma Arusha','B+','0750761558',1,'2026-05-17 23:10:37');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'hospital_name','AfyaCall Health Center'),(2,'welcome_message','Welcome to AfyaCall'),(3,'default_language','Swahili'),(4,'ussd_shortcode','*384*123#');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ussd_logs`
--

DROP TABLE IF EXISTS `ussd_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ussd_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(20) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `service_code` varchar(20) DEFAULT NULL,
  `user_input` text,
  `language` enum('Swahili','English') DEFAULT NULL,
  `response` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ussd_logs`
--

LOCK TABLES `ussd_logs` WRITE;
/*!40000 ALTER TABLE `ussd_logs` DISABLE KEYS */;
INSERT INTO `ussd_logs` VALUES (1,'+255700000000','SIM-1779159637348','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:00:37'),(2,'+255700000000','SIM-1779160272014','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:11:12'),(3,'+255700000000','SIM-1779160272014','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:11:16'),(4,'+255700000000','SIM-1779160272014','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:11:27'),(5,'+255700000000','SIM-1779160272014','*384*7#','1*3*4','Swahili','END Punguza sukari na wanga. Pima sukari yako mara kwa mara. Dawa zako zinaweza kusaidia kudhibiti ugonjwa wa kisukari.','2026-05-19 03:11:39'),(6,'+255700000000','SIM-1779160802317','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:20:02'),(7,'+255700000000','SIM-1779160802317','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 03:20:10'),(8,'+255700000000','SIM-1779160802317','*384*7#','2*2','English','CON Enter Patient Code','2026-05-19 03:20:18'),(9,'+255700000000','SIM-1779160827735','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:20:27'),(10,'+255700000000','SIM-1779160827735','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:20:32'),(11,'+255700000000','SIM-1779160827735','*384*7#','1*1','Swahili','CON Weka Namba ya Mgonjwa (mfano: AC-12345)','2026-05-19 03:20:42'),(12,'+255700000000','SIM-1779160827735','*384*7#','1*1*AC-62501','Swahili','END Mgonjwa: Nizari Iddi\nTatizo: Nothing found on the diognosis done today\nDawa: Patient should take only paracetamol for handiling pain.\nMiadi ijayo: 2026-05-29','2026-05-19 03:20:49'),(13,'+255700000000','SIM-1779161114412','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:25:14'),(14,'+255700000000','SIM-1779161114412','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 03:26:58'),(15,'+255700000000','SIM-1779161114412','*384*7#','2*3','English','CON Health Tips\n1. Nutrition\n2. Exercise\n3. Malaria\n4. Diabetes\n5. HIV/AIDS','2026-05-19 03:27:04'),(16,'+255700000000','SIM-1779161114412','*384*7#','2*3*2','English','END Exercise for 30 minutes daily. Walking, running or playing sports can significantly improve your health and wellbeing.','2026-05-19 03:27:08'),(17,'+255700000000','SIM-1779161345452','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:29:05'),(18,'+255700000000','SIM-1779161360747','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:29:20'),(19,'+255700000000','SIM-1779161360747','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:29:30'),(20,'+255700000000','SIM-1779161576164','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:32:56'),(21,'+255700000000','SIM-1779161775866','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:36:15'),(22,'+255700000000','SIM-1779161801330','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:36:41'),(23,'+255700000000','SIM-1779161801330','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:36:46'),(24,'+255700000000','SIM-1779161801330','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:37:00'),(25,'+255700000000','SIM-1779161801330','*384*7#','1*3*1','Swahili','END Kula matunda, mboga na nafaka kila siku. Epuka sukari nyingi na mafuta. Kunywa maji safi angalau lita 2 kwa siku.','2026-05-19 03:37:09'),(26,'+255700000000','SIM-1779161838764','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:37:18'),(27,'+255700000000','SIM-1779161838764','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:37:21'),(28,'+255700000000','SIM-1779161838764','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:37:24'),(29,'+255700000000','SIM-1779161838764','*384*7#','1*3*1','Swahili','END Kula matunda, mboga na nafaka kila siku. Epuka sukari nyingi na mafuta. Kunywa maji safi angalau lita 2 kwa siku.','2026-05-19 03:37:26'),(30,'+255700000000','SIM-1779161949161','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:39:09'),(31,'+255700000000','SIM-1779161951037','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:39:11'),(32,'+255700000000','SIM-1779161955966','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:39:15'),(33,'+255700000000','SIM-1779161955966','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:39:19'),(34,'+255700000000','SIM-1779161955966','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:39:20'),(35,'+255700000000','SIM-1779161955966','*384*7#','1*3*3','Swahili','END Lala kwenye chandarua chenye dawa. Angalia maeneo ya maji yaliyotuama. Tafuta matibabu mapema ukipata dalili za malaria.','2026-05-19 03:39:22'),(36,'+255700000000','SIM-1779162010918','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:40:10'),(37,'+255700000000','SIM-1779162010918','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 03:40:15'),(38,'+255700000000','SIM-1779162020015','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:40:23'),(39,'+255700000000','SIM-1779162020015','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:40:28'),(40,'+255700000000','SIM-1779162093589','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:41:33'),(41,'+255700000000','SIM-1779162100666','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:41:40'),(42,'+255700000000','SIM-1779162100666','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:41:43'),(43,'+255700000000','SIM-1779162100666','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:41:49'),(44,'+255700000000','SIM-1779162126329','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:42:08'),(45,'+255700000000','SIM-1779162126329','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:42:12'),(46,'+255700000000','SIM-1779162126329','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:42:21'),(47,'+255700000000','SIM-1779162197382','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:43:17'),(48,'+255700000000','SIM-1779162197382','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:43:19'),(49,'+255700000000','SIM-1779162197382','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:43:27'),(50,'+255700000000','SIM-1779162254527','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:44:14'),(51,'+255700000000','SIM-1779162254527','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:44:17'),(52,'+255700000000','SIM-1779162254527','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:44:20'),(53,'+255700000000','SIM-1779162254527','*384*7#','1*3*3','Swahili','END Lala kwenye chandarua chenye dawa. Angalia maeneo ya maji yaliyotuama. Tafuta matibabu mapema ukipata dalili za malaria.','2026-05-19 03:44:23'),(54,'+255700000000','SIM-1779162305409','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:45:05'),(55,'+255700000000','SIM-1779162380610','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:46:20'),(56,'+255700000000','SIM-1779162380610','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:47:50'),(57,'+255700000000','SIM-1779162380610','*384*7#','1*4','Swahili','CON Taarifa za Dharura\n1. Nambari za dharura\n2. Hospitali zilizo karibu\n3. Ripoti dharura','2026-05-19 03:47:52'),(58,'+255700000000','SIM-1779162495684','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:48:15'),(59,'+255700000000','SIM-1779162495684','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:48:17'),(60,'+255700000000','SIM-1779162495684','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:48:20'),(61,'+255700000000','SIM-1779162505281','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:48:25'),(62,'+255700000000','SIM-1779162505281','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 03:48:27'),(63,'+255700000000','SIM-1779162505281','*384*7#','1*3','Swahili','CON Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI','2026-05-19 03:48:30'),(64,'+255700000000','SIM-1779162651744','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:50:51'),(65,'+255700000000','SIM-1779162663245','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:51:03'),(66,'+255700000000','SIM-1779162936390','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 03:55:36'),(67,'+255700000000','SIM-1779163614476','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 04:06:54'),(68,'+255700000000','SIM-1779171402330','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 06:16:42'),(69,'+255700000000','SIM-1779171402330','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 06:17:32'),(70,'+255700000000','SIM-1779172203305','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 06:30:03'),(71,'+255700000000','SIM-1779172284986','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 06:31:24'),(72,'+255700000000','SIM-1779176147166','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:35:47'),(73,'+255700000000','SIM-1779176154462','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:35:54'),(74,'+255700000000','SIM-1779176156409','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:36:06'),(75,'+255700000000','SIM-1779176156409','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 07:36:11'),(76,'+255700000000','SIM-1779176156409','*384*7#','1*1','Swahili','CON Weka Namba ya Mgonjwa (mfano: AC-12345)','2026-05-19 07:36:23'),(77,'+255700000000','SIM-1779176193264','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:36:33'),(78,'+255700000000','SIM-1779176193264','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 07:36:35'),(79,'+255700000000','SIM-1779176193264','*384*7#','1*1','Swahili','CON Weka Namba ya Mgonjwa (mfano: AC-12345)','2026-05-19 07:36:37'),(80,'+255700000000','SIM-1779176193264','*384*7#','1*1*AC-62501','Swahili','END Mgonjwa: Nizari Iddi\nTatizo: Nothing found on the diognosis done today\nDawa: Patient should take only paracetamol for handiling pain.\nMiadi ijayo: 2026-05-29','2026-05-19 07:36:41'),(81,'+255700000000','SIM-1779176577921','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:42:57'),(82,'+255700000000','SIM-1779176602090','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 07:43:22'),(83,'+255700000000','SIM-1779210715417','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 17:11:55'),(84,'+255700000000','SIM-1779211772808','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 17:29:32'),(85,'+255700000000','SIM-1779225511467','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:18:31'),(86,'+255700000000','SIM-1779226699871','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:38:19'),(87,'+255700000000','SIM-1779226699871','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 21:38:23'),(88,'+255700000000','SIM-1779226699871','*384*7#','1*2','Swahili','CON Weka Namba ya Mgonjwa','2026-05-19 21:38:28'),(89,'+255700000000','SIM-1779226733011','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:38:53'),(90,'+255700000000','SIM-1779226733011','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 21:38:56'),(91,'+255700000000','SIM-1779226733011','*384*7#','1*2','Swahili','CON Weka Namba ya Mgonjwa','2026-05-19 21:39:49'),(92,'+255700000000','SIM-1779226733011','*384*7#','1*2*AC-62501','Swahili','END Mgonjwa: Nizari Iddi\nTarehe: 2026-05-18 04:11:00\nHali: Completed\nMaelezo: fast and quick','2026-05-19 21:39:58'),(93,'+255700000000','SIM-1779226809356','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:40:10'),(94,'+255700000000','SIM-1779226809356','*384*7#','1','Swahili','CON Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura','2026-05-19 21:40:16'),(95,'+255700000000','SIM-1779226809356','*384*7#','1*4','Swahili','CON Taarifa za Dharura\n1. Nambari za dharura\n2. Hospitali zilizo karibu\n3. Ripoti dharura','2026-05-19 21:40:31'),(96,'+255700000000','SIM-1779226809356','*384*7#','1*4*1','Swahili','END Nambari za Dharura:\nPolisi: 112\nZima moto: 115\nAfya: 0800 110 084\nMadawa ya kulevya: 116','2026-05-19 21:40:41'),(97,'+255700000000','SIM-1779226918913','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:41:58'),(98,'+255700000000','SIM-1779226918913','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 21:42:03'),(99,'+255700000000','SIM-1779226918913','*384*7#','2*3','English','CON Health Tips\n1. Nutrition\n2. Exercise\n3. Malaria\n4. Diabetes\n5. HIV/AIDS','2026-05-19 21:42:20'),(100,'+255700000000','SIM-1779226918913','*384*7#','2*3*5','English','END Get tested for HIV regularly. Always use condoms. People living with HIV can live healthy lives with proper ARV treatment.','2026-05-19 21:42:26'),(101,'+255700000000','SIM-1779227982243','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 21:59:42'),(102,'+255700000000','SIM-1779228071011','*384*7#','','Swahili','CON Karibu AfyaCall\n1. Kiswahili\n2. English','2026-05-19 22:01:11'),(103,'+255700000000','SIM-1779228071011','*384*7#','2','English','CON Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts','2026-05-19 22:01:13'),(104,'+255700000000','SIM-1779228071011','*384*7#','2*2','English','CON Enter Patient Code','2026-05-19 22:01:16'),(105,'+255700000000','SIM-1779228071011','*384*7#','2*2*AC-62501','English','END Patient: Nizari Iddi\nDate: 2026-05-18 04:11:00\nStatus: Completed\nRemarks: fast and quick','2026-05-19 22:01:24');
/*!40000 ALTER TABLE `ussd_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ussd_menus`
--

DROP TABLE IF EXISTS `ussd_menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ussd_menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menu_path` varchar(60) NOT NULL,
  `menu_text` text NOT NULL,
  `language` enum('sw','en','both') NOT NULL DEFAULT 'both',
  `is_terminal` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL DEFAULT '',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_path_lang` (`menu_path`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ussd_menus`
--

LOCK TABLES `ussd_menus` WRITE;
/*!40000 ALTER TABLE `ussd_menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `ussd_menus` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-20 13:07:39
