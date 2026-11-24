/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.16-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: hashmi_agriculture
-- ------------------------------------------------------
-- Server version	10.11.16-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_table` varchar(40) NOT NULL,
  `owner_id` bigint(20) unsigned NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_created_by_foreign` (`created_by`),
  KEY `attachments_owner_table_owner_id_index` (`owner_table`,`owner_id`),
  CONSTRAINT `attachments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(60) NOT NULL,
  `record_id` bigint(20) unsigned NOT NULL,
  `action` enum('insert','update','delete') NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `changed_at` timestamp NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  PRIMARY KEY (`id`),
  KEY `audit_log_changed_by_foreign` (`changed_by`),
  KEY `audit_log_table_name_record_id_index` (`table_name`,`record_id`),
  CONSTRAINT `audit_log_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=465 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_log`
--

LOCK TABLES `audit_log` WRITE;
/*!40000 ALTER TABLE `audit_log` DISABLE KEYS */;
INSERT INTO `audit_log` VALUES
(1,'stage_templates',1,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"code\":\"LAND_PREP\",\"name\":\"Land Preparation\",\"sequence\":1,\"is_system\":true,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":1}'),
(2,'stage_templates',2,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"code\":\"SEED_MAT\",\"name\":\"Seed & Material Purchase\",\"sequence\":2,\"is_system\":true,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":2}'),
(3,'stage_templates',3,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"code\":\"GROWING\",\"name\":\"Crop Growing & Maintenance\",\"sequence\":3,\"is_system\":true,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":3}'),
(4,'stage_templates',4,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"code\":\"HARVEST\",\"name\":\"Harvesting\",\"sequence\":4,\"is_system\":true,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":4}'),
(5,'stage_templates',5,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"code\":\"SILAGE\",\"name\":\"Silage Production\",\"sequence\":5,\"is_system\":true,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":5}'),
(6,'crops',1,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"name\":\"Maize\",\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":1}'),
(7,'crops',2,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"name\":\"Sorghum\",\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":2}'),
(8,'crops',3,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"name\":\"Oats\",\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":3}'),
(9,'crops',4,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"name\":\"Millet\",\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":4}'),
(10,'expense_categories',1,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Fuel\",\"cost_type\":\"fuel\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":1}'),
(11,'expense_categories',2,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":1,\"name\":\"Diesel\",\"cost_type\":\"fuel\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":2}'),
(12,'expense_categories',3,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":1,\"name\":\"Petrol\",\"cost_type\":\"fuel\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":3}'),
(13,'expense_categories',4,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":1,\"name\":\"Engine oil\",\"cost_type\":\"fuel\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":4}'),
(14,'expense_categories',5,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Labour\",\"cost_type\":\"labour\",\"default_unit\":\"day\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":5}'),
(15,'expense_categories',6,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":5,\"name\":\"Daily wages\",\"cost_type\":\"labour\",\"default_unit\":\"day\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":6}'),
(16,'expense_categories',7,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":5,\"name\":\"Contract labour\",\"cost_type\":\"labour\",\"default_unit\":\"acre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":7}'),
(17,'expense_categories',8,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":5,\"name\":\"Permanent staff\",\"cost_type\":\"labour\",\"default_unit\":\"month\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":8}'),
(18,'expense_categories',9,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":5,\"name\":\"Compaction labour\",\"cost_type\":\"labour\",\"default_unit\":\"day\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":9}'),
(19,'expense_categories',10,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Machinery hire\",\"cost_type\":\"machinery\",\"default_unit\":\"hour\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":10}'),
(20,'expense_categories',11,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":10,\"name\":\"Tractor rent\",\"cost_type\":\"machinery\",\"default_unit\":\"hour\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":11}'),
(21,'expense_categories',12,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":10,\"name\":\"Harvester \\/ chopper rent\",\"cost_type\":\"machinery\",\"default_unit\":\"acre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":12}'),
(22,'expense_categories',13,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":10,\"name\":\"Rotavator & disc\",\"cost_type\":\"machinery\",\"default_unit\":\"acre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":13}'),
(23,'expense_categories',14,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":10,\"name\":\"Planter\",\"cost_type\":\"machinery\",\"default_unit\":\"acre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":14}'),
(24,'expense_categories',15,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Maintenance\",\"cost_type\":\"maintenance\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":15}'),
(25,'expense_categories',16,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":15,\"name\":\"Tractor repair\",\"cost_type\":\"maintenance\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":16}'),
(26,'expense_categories',17,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":15,\"name\":\"Tyres & battery\",\"cost_type\":\"maintenance\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":17}'),
(27,'expense_categories',18,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":15,\"name\":\"Servicing\",\"cost_type\":\"maintenance\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":18}'),
(28,'expense_categories',19,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Seed\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":19}'),
(29,'expense_categories',20,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":19,\"name\":\"Hybrid maize seed\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":20}'),
(30,'expense_categories',21,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":19,\"name\":\"Sorghum seed\",\"cost_type\":\"material\",\"default_unit\":\"kg\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":21}'),
(31,'expense_categories',22,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":19,\"name\":\"Oats seed\",\"cost_type\":\"material\",\"default_unit\":\"kg\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":22}'),
(32,'expense_categories',23,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Fertilizer\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":23}'),
(33,'expense_categories',24,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":23,\"name\":\"Urea\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":24}'),
(34,'expense_categories',25,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":23,\"name\":\"DAP\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":25}'),
(35,'expense_categories',26,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":23,\"name\":\"Potash (SOP)\",\"cost_type\":\"material\",\"default_unit\":\"bag\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":26}'),
(36,'expense_categories',27,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":23,\"name\":\"Farmyard manure\",\"cost_type\":\"material\",\"default_unit\":\"trolley\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":27}'),
(37,'expense_categories',28,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Crop protection\",\"cost_type\":\"material\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":28}'),
(38,'expense_categories',29,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":28,\"name\":\"Herbicide\",\"cost_type\":\"material\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":29}'),
(39,'expense_categories',30,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":28,\"name\":\"Insecticide\",\"cost_type\":\"material\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":30}'),
(40,'expense_categories',31,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":28,\"name\":\"Fungicide\",\"cost_type\":\"material\",\"default_unit\":\"litre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":31}'),
(41,'expense_categories',32,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Silage materials\",\"cost_type\":\"material\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":32}'),
(42,'expense_categories',33,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":32,\"name\":\"Plastic sheet\",\"cost_type\":\"material\",\"default_unit\":\"roll\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":33}'),
(43,'expense_categories',34,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":32,\"name\":\"Silage additive \\/ inoculant\",\"cost_type\":\"material\",\"default_unit\":\"kg\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":34}'),
(44,'expense_categories',35,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":32,\"name\":\"Tyres & weights\",\"cost_type\":\"material\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":35}'),
(45,'expense_categories',36,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Irrigation\",\"cost_type\":\"utility\",\"default_unit\":\"month\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":36}'),
(46,'expense_categories',37,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":36,\"name\":\"Tubewell electricity\",\"cost_type\":\"utility\",\"default_unit\":\"month\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":37}'),
(47,'expense_categories',38,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":36,\"name\":\"Canal water (abiana)\",\"cost_type\":\"utility\",\"default_unit\":\"acre\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":38}'),
(48,'expense_categories',39,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Transport\",\"cost_type\":\"transport\",\"default_unit\":\"trip\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":39}'),
(49,'expense_categories',40,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":39,\"name\":\"Trolley hire\",\"cost_type\":\"transport\",\"default_unit\":\"trip\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":40}'),
(50,'expense_categories',41,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":39,\"name\":\"Loading & unloading\",\"cost_type\":\"transport\",\"default_unit\":\"trip\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":41}'),
(51,'expense_categories',42,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Services\",\"cost_type\":\"service\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":42}'),
(52,'expense_categories',43,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":42,\"name\":\"Soil testing\",\"cost_type\":\"service\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":43}'),
(53,'expense_categories',44,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":42,\"name\":\"Agronomist visit\",\"cost_type\":\"service\",\"default_unit\":\"unit\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":44}'),
(54,'expense_categories',45,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":null,\"name\":\"Other\",\"cost_type\":\"other\",\"default_unit\":null,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":45}'),
(55,'expense_categories',46,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":45,\"name\":\"Miscellaneous\",\"cost_type\":\"other\",\"default_unit\":null,\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":46}'),
(56,'expense_categories',47,'insert',NULL,'2026-11-12 04:04:26',NULL,'{\"farm_id\":null,\"parent_id\":10,\"name\":\"Own machine use (notional)\",\"cost_type\":\"machinery\",\"default_unit\":\"hour\",\"is_active\":true,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:26\",\"id\":47}'),
(57,'farms',1,'insert',NULL,'2026-11-12 04:04:27',NULL,'{\"name\":\"Hashmi Agriculture Farm\",\"location\":\"Renala Khurd, District Okara\",\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:27\",\"id\":1}'),
(58,'users',1,'insert',NULL,'2026-11-12 04:04:27',NULL,'{\"is_active\":true,\"locale\":\"en\",\"name\":\"Imran Hashmi\",\"phone\":\"03001234567\",\"role\":\"owner\",\"farm_id\":1,\"created_by\":null,\"updated_at\":\"2026-11-12 09:04:27\",\"id\":1}'),
(59,'users',2,'insert',1,'2026-11-12 04:04:28',NULL,'{\"is_active\":true,\"locale\":\"en\",\"name\":\"Tariq Mehmood\",\"phone\":\"03011234567\",\"role\":\"manager\",\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:28\",\"id\":2}'),
(60,'users',3,'insert',1,'2026-11-12 04:04:28',NULL,'{\"is_active\":true,\"locale\":\"en\",\"name\":\"Usman Ghani\",\"phone\":\"03021234567\",\"role\":\"data_entry\",\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:28\",\"id\":3}'),
(61,'users',4,'insert',1,'2026-11-12 04:04:28',NULL,'{\"is_active\":true,\"locale\":\"en\",\"name\":\"Ayesha Siddiqui\",\"phone\":\"03031234567\",\"role\":\"viewer\",\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:28\",\"id\":4}'),
(62,'fields',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"North Block A-1\",\"area_acres\":10,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(63,'fields',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"North Block A-2\",\"area_acres\":12,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(64,'fields',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Tubewell Square B-1\",\"area_acres\":8,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(65,'fields',4,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"River Bank R-1\",\"area_acres\":15,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":4}'),
(66,'vendors',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Kissan Seed Centre\",\"phone\":\"03214567890\",\"type\":\"supplier\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(67,'vendors',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"PSO Renala Filling Station\",\"phone\":\"0442630011\",\"type\":\"supplier\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(68,'vendors',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Rana Tractor Services\",\"phone\":\"03334567812\",\"type\":\"rental\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(69,'vendors',4,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Ghulam Rasool (labour contractor)\",\"phone\":\"03004561230\",\"type\":\"labour\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":4}'),
(70,'vendors',5,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"LESCO\",\"phone\":null,\"type\":\"utility\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":5}'),
(71,'vendors',6,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Okara Agri Traders\",\"phone\":\"03124567711\",\"type\":\"supplier\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":6}'),
(72,'resources',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Massey Ferguson 385\",\"type\":\"tractor\",\"ownership\":\"owned\",\"internal_rate_per_hour\":1500,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(73,'resources',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Rented forage chopper\",\"type\":\"chopper\",\"ownership\":\"rented\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(74,'resources',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"Tubewell pump B-1\",\"type\":\"pump\",\"ownership\":\"owned\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(75,'crop_seasons',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"March 2026\",\"crop_id\":1,\"start_date\":\"2026-03-02 00:00:00\",\"expected_end_date\":\"2026-06-30 00:00:00\",\"notes\":\"Spring maize for silage, hybrid DK-6789.\",\"status\":\"planned\",\"code\":\"CS-2026-03\",\"total_area_acres\":10,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(76,'season_stages',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":1,\"season_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(77,'season_stages',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":2,\"season_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(78,'season_stages',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":3,\"season_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(79,'season_stages',4,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":4,\"season_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":4}'),
(80,'season_stages',5,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":5,\"season_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":5}'),
(81,'expenses',1,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":1,\"category_id\":2,\"expense_date\":\"2026-03-02 00:00:00\",\"description\":\"Diesel for ploughing and discing\",\"quantity\":100,\"unit_code\":\"litre\",\"unit_price\":250,\"amount\":\"25000.00\",\"vendor_id\":2,\"resource_id\":1,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":1}'),
(82,'season_stages',1,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-03-02 00:00:00\"}'),
(83,'crop_seasons',1,'update',1,'2026-11-12 04:04:29','{\"status\":\"planned\"}','{\"status\":\"active\"}'),
(84,'expenses',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":1,\"category_id\":13,\"expense_date\":\"2026-03-03 00:00:00\",\"description\":\"Rotavator, 10 acres\",\"quantity\":10,\"unit_code\":\"acre\",\"unit_price\":1000,\"amount\":\"10000.00\",\"vendor_id\":3,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(85,'expenses',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":1,\"category_id\":6,\"expense_date\":\"2026-03-04 00:00:00\",\"description\":\"Levelling and ridging labour\",\"quantity\":5,\"unit_code\":\"day\",\"unit_price\":1000,\"amount\":\"5000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(86,'season_stages',1,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-03-04 00:00:00\"}'),
(87,'expenses',4,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":2,\"category_id\":20,\"expense_date\":\"2026-03-06 00:00:00\",\"description\":\"DK-6789 hybrid seed, 10 bags\",\"quantity\":10,\"unit_code\":\"bag\",\"unit_price\":4000,\"amount\":\"40000.00\",\"vendor_id\":1,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":4}'),
(88,'season_stages',2,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-03-06 00:00:00\"}'),
(89,'expenses',5,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":2,\"category_id\":25,\"expense_date\":\"2026-03-06 00:00:00\",\"description\":\"DAP at sowing\",\"quantity\":1.25,\"unit_code\":\"bag\",\"unit_price\":12000,\"amount\":\"15000.00\",\"vendor_id\":6,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":5}'),
(90,'season_stages',2,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-03-06 00:00:00\"}'),
(91,'expenses',6,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":3,\"category_id\":37,\"expense_date\":\"2026-04-30 00:00:00\",\"description\":\"Tubewell electricity, Mar\\u2013Apr\",\"quantity\":null,\"unit_code\":null,\"unit_price\":null,\"amount\":\"20000.00\",\"vendor_id\":5,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":6}'),
(92,'season_stages',3,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-04-30 00:00:00\"}'),
(93,'expenses',7,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":3,\"category_id\":24,\"expense_date\":\"2026-04-12 00:00:00\",\"description\":\"Urea top dressing\",\"quantity\":4,\"unit_code\":\"bag\",\"unit_price\":4500,\"amount\":\"18000.00\",\"vendor_id\":6,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":7}'),
(94,'season_stages',3,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-04-12 00:00:00\"}'),
(95,'expenses',8,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":4,\"category_id\":12,\"expense_date\":\"2026-06-14 00:00:00\",\"description\":\"Forage chopper hire, 10 acres\",\"quantity\":10,\"unit_code\":\"acre\",\"unit_price\":3000,\"amount\":\"30000.00\",\"vendor_id\":3,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":8}'),
(96,'season_stages',4,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-06-14 00:00:00\"}'),
(97,'expenses',9,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":4,\"category_id\":40,\"expense_date\":\"2026-06-14 00:00:00\",\"description\":\"Trolleys to silage pit\",\"quantity\":24,\"unit_code\":\"trip\",\"unit_price\":500,\"amount\":\"12000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":9}'),
(98,'season_stages',4,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-06-14 00:00:00\"}'),
(99,'expenses',10,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":5,\"category_id\":33,\"expense_date\":\"2026-06-15 00:00:00\",\"description\":\"Plastic sheet and inoculant\",\"quantity\":null,\"unit_code\":null,\"unit_price\":null,\"amount\":\"25000.00\",\"vendor_id\":6,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":10}'),
(100,'season_stages',5,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-06-15 00:00:00\"}'),
(101,'expenses',11,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":1,\"season_stage_id\":5,\"category_id\":9,\"expense_date\":\"2026-06-16 00:00:00\",\"description\":\"Compaction and sealing labour\",\"quantity\":20,\"unit_code\":\"day\",\"unit_price\":1000,\"amount\":\"20000.00\",\"vendor_id\":4,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":11}'),
(102,'season_stages',5,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-06-16 00:00:00\"}'),
(103,'crop_seasons',1,'update',1,'2026-11-12 04:04:29','{\"status\":\"planned\"}','{\"status\":\"ensiled\"}'),
(104,'crop_seasons',2,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"August 2026\",\"crop_id\":1,\"start_date\":\"2026-08-03 00:00:00\",\"expected_end_date\":\"2026-11-15 00:00:00\",\"notes\":\"Autumn maize across two fields.\",\"status\":\"planned\",\"code\":\"CS-2026-08\",\"total_area_acres\":27,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":2}'),
(105,'season_stages',6,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":1,\"season_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":6}'),
(106,'season_stages',7,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":2,\"season_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":7}'),
(107,'season_stages',8,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":3,\"season_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":8}'),
(108,'season_stages',9,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":4,\"season_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":9}'),
(109,'season_stages',10,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":5,\"season_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":10}'),
(110,'expenses',12,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":6,\"category_id\":2,\"expense_date\":\"2026-08-03 00:00:00\",\"description\":\"Diesel for ploughing, 27 acres\",\"quantity\":260,\"unit_code\":\"litre\",\"unit_price\":262,\"amount\":\"68120.00\",\"vendor_id\":2,\"resource_id\":1,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":12}'),
(111,'season_stages',6,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-08-03 00:00:00\"}'),
(112,'crop_seasons',2,'update',1,'2026-11-12 04:04:29','{\"status\":\"planned\"}','{\"status\":\"active\"}'),
(113,'expenses',13,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":6,\"category_id\":13,\"expense_date\":\"2026-08-05 00:00:00\",\"description\":\"Rotavator, 27 acres\",\"quantity\":27,\"unit_code\":\"acre\",\"unit_price\":1100,\"amount\":\"29700.00\",\"vendor_id\":3,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":13}'),
(114,'season_stages',6,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-08-06 00:00:00\"}'),
(115,'expenses',14,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":7,\"category_id\":20,\"expense_date\":\"2026-08-07 00:00:00\",\"description\":\"Pioneer 30Y87, 27 bags\",\"quantity\":27,\"unit_code\":\"bag\",\"unit_price\":4300,\"amount\":\"116100.00\",\"vendor_id\":1,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":14}'),
(116,'season_stages',7,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-08-07 00:00:00\"}'),
(117,'expenses',15,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":7,\"category_id\":25,\"expense_date\":\"2026-08-07 00:00:00\",\"description\":\"DAP at sowing\",\"quantity\":27,\"unit_code\":\"bag\",\"unit_price\":12400,\"amount\":\"334800.00\",\"vendor_id\":6,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":15}'),
(118,'season_stages',7,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-08-08 00:00:00\"}'),
(119,'expenses',16,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":8,\"category_id\":37,\"expense_date\":\"2026-08-31 00:00:00\",\"description\":\"Tubewell electricity, August\",\"quantity\":null,\"unit_code\":null,\"unit_price\":null,\"amount\":\"31500.00\",\"vendor_id\":5,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":16}'),
(120,'season_stages',8,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-08-31 00:00:00\"}'),
(121,'expenses',17,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":8,\"category_id\":24,\"expense_date\":\"2026-09-01 00:00:00\",\"description\":\"Urea, 1st split\",\"quantity\":27,\"unit_code\":\"bag\",\"unit_price\":4650,\"amount\":\"125550.00\",\"vendor_id\":6,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":17}'),
(122,'expenses',18,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":8,\"category_id\":37,\"expense_date\":\"2026-09-30 00:00:00\",\"description\":\"Tubewell electricity, September\",\"quantity\":null,\"unit_code\":null,\"unit_price\":null,\"amount\":\"29800.00\",\"vendor_id\":5,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":18}'),
(123,'expenses',19,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":8,\"category_id\":29,\"expense_date\":\"2026-09-10 00:00:00\",\"description\":\"Atrazine + S-metolachlor\",\"quantity\":27,\"unit_code\":\"litre\",\"unit_price\":1450,\"amount\":\"39150.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":19}'),
(124,'season_stages',8,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"end_date\":null}','{\"status\":\"completed\",\"end_date\":\"2026-10-15 00:00:00\"}'),
(125,'expenses',20,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":2,\"season_stage_id\":9,\"category_id\":12,\"expense_date\":\"2026-10-21 00:00:00\",\"description\":\"Forage chopper hire, first 12 acres\",\"quantity\":12,\"unit_code\":\"acre\",\"unit_price\":3200,\"amount\":\"38400.00\",\"vendor_id\":3,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":20}'),
(126,'season_stages',9,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-10-21 00:00:00\"}'),
(127,'crop_seasons',2,'update',1,'2026-11-12 04:04:29','{\"status\":\"planned\"}','{\"status\":\"active\"}'),
(128,'crop_seasons',3,'insert',1,'2026-11-12 04:04:29',NULL,'{\"name\":\"October 2026\",\"crop_id\":3,\"start_date\":\"2026-10-15 00:00:00\",\"expected_end_date\":\"2027-01-31 00:00:00\",\"notes\":\"Winter oats for fodder and silage.\",\"status\":\"planned\",\"code\":\"CS-2026-10\",\"total_area_acres\":8,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":3}'),
(129,'season_stages',11,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":1,\"season_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":11}'),
(130,'season_stages',12,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":2,\"season_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":12}'),
(131,'season_stages',13,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":3,\"season_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":13}'),
(132,'season_stages',14,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":4,\"season_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":14}'),
(133,'season_stages',15,'insert',1,'2026-11-12 04:04:29',NULL,'{\"stage_template_id\":5,\"season_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":15}'),
(134,'expenses',21,'insert',1,'2026-11-12 04:04:29',NULL,'{\"season_id\":3,\"season_stage_id\":11,\"category_id\":2,\"expense_date\":\"2026-10-16 00:00:00\",\"description\":\"Diesel for land preparation\",\"quantity\":70,\"unit_code\":\"litre\",\"unit_price\":265,\"amount\":\"18550.00\",\"vendor_id\":2,\"resource_id\":1,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:29\",\"id\":21}'),
(135,'season_stages',11,'update',1,'2026-11-12 04:04:29','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-10-16 00:00:00\"}'),
(136,'crop_seasons',3,'update',1,'2026-11-12 04:04:30','{\"status\":\"planned\"}','{\"status\":\"active\"}'),
(137,'expenses',22,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_id\":3,\"season_stage_id\":11,\"category_id\":27,\"expense_date\":\"2026-10-18 00:00:00\",\"description\":\"Farmyard manure, 6 trolleys\",\"quantity\":6,\"unit_code\":\"trolley\",\"unit_price\":3500,\"amount\":\"21000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":22}'),
(138,'storage_units',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"name\":\"Pit 1\",\"type\":\"pit\",\"capacity_tonnes\":200,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(139,'storage_units',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"name\":\"Bunker 2\",\"type\":\"bunker\",\"capacity_tonnes\":400,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(140,'storage_units',3,'insert',1,'2026-11-12 04:04:30',NULL,'{\"name\":\"Pit 3\",\"type\":\"pit\",\"capacity_tonnes\":150,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":3}'),
(141,'harvest_records',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_id\":1,\"harvest_date\":\"2026-06-14 00:00:00\",\"field_id\":1,\"method\":\"weighbridge\",\"quantity_kg\":200000,\"moisture_pct\":67,\"trolley_count\":null,\"avg_trolley_kg\":null,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(142,'silage_batches',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_id\":1,\"status\":\"sealed\",\"storage_unit_id\":1,\"fill_start\":\"2026-06-14 00:00:00\",\"sealed_date\":\"2026-06-17 00:00:00\",\"fresh_weight_kg\":190000,\"method\":\"weighbridge\",\"expected_loss_pct\":10,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(143,'silage_batches',1,'update',1,'2026-11-12 04:04:30','{\"status\":\"sealed\"}','{\"status\":\"feeding\",\"opened_date\":\"2026-08-01 00:00:00\"}'),
(144,'payments',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":2,\"payment_date\":\"2026-03-09 00:00:00\",\"amount\":25000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(145,'payment_allocations',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":1,\"amount\":25000,\"payment_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(146,'payments',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":3,\"payment_date\":\"2026-03-10 00:00:00\",\"amount\":10000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(147,'payment_allocations',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":2,\"amount\":10000,\"payment_id\":2,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(148,'payments',3,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-03-11 00:00:00\",\"amount\":5000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":3}'),
(149,'payment_allocations',3,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":3,\"amount\":5000,\"payment_id\":3,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":3}'),
(150,'payments',4,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":1,\"payment_date\":\"2026-03-13 00:00:00\",\"amount\":40000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":4}'),
(151,'payment_allocations',4,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":4,\"amount\":40000,\"payment_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":4}'),
(152,'payments',5,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":6,\"payment_date\":\"2026-03-13 00:00:00\",\"amount\":15000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":5}'),
(153,'payment_allocations',5,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":5,\"amount\":15000,\"payment_id\":5,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":5}'),
(154,'payments',6,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":5,\"payment_date\":\"2026-05-07 00:00:00\",\"amount\":20000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":6}'),
(155,'payment_allocations',6,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":6,\"amount\":20000,\"payment_id\":6,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":6}'),
(156,'payments',7,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":6,\"payment_date\":\"2026-04-19 00:00:00\",\"amount\":18000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":7}'),
(157,'payment_allocations',7,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":7,\"amount\":18000,\"payment_id\":7,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":7}'),
(158,'payments',8,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":3,\"payment_date\":\"2026-06-21 00:00:00\",\"amount\":30000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":8}'),
(159,'payment_allocations',8,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":8,\"amount\":30000,\"payment_id\":8,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":8}'),
(160,'payments',9,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-06-21 00:00:00\",\"amount\":12000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":9}'),
(161,'payment_allocations',9,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":9,\"amount\":12000,\"payment_id\":9,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":9}'),
(162,'payments',10,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":6,\"payment_date\":\"2026-06-22 00:00:00\",\"amount\":25000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":10}'),
(163,'payment_allocations',10,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":10,\"amount\":25000,\"payment_id\":10,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":10}'),
(164,'payments',11,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":4,\"payment_date\":\"2026-06-23 00:00:00\",\"amount\":20000,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":11}'),
(165,'payment_allocations',11,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":11,\"amount\":20000,\"payment_id\":11,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":11}'),
(166,'crop_seasons',1,'update',1,'2026-11-12 04:04:30','{\"status\":\"ensiled\"}','{\"status\":\"closed\",\"closed_at\":\"2026-11-12 00:00:00\"}'),
(167,'silage_batches',1,'update',1,'2026-11-12 04:04:30','{\"cost_per_kg\":null}','{\"cost_per_kg\":1.2865}'),
(168,'crop_seasons',1,'update',1,'2026-11-12 04:04:30','{\"closed_at\":\"2026-11-12T00:00:00.000000Z\"}','{\"closed_at\":\"2026-07-05 00:00:00\"}'),
(169,'harvest_records',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_id\":2,\"harvest_date\":\"2026-10-21 00:00:00\",\"field_id\":2,\"method\":\"trolley_count\",\"trolley_count\":48,\"avg_trolley_kg\":4500,\"moisture_pct\":68,\"quantity_kg\":216000,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(170,'crop_seasons',2,'update',1,'2026-11-12 04:04:30','{\"status\":\"active\"}','{\"status\":\"harvested\"}'),
(171,'silage_batches',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_id\":2,\"status\":\"filling\",\"storage_unit_id\":2,\"fill_start\":\"2026-10-21 00:00:00\",\"fresh_weight_kg\":205000,\"method\":\"trolley_count\",\"expected_loss_pct\":10,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(172,'crop_seasons',2,'update',1,'2026-11-12 04:04:30','{\"status\":\"harvested\"}','{\"status\":\"ensiled\"}'),
(173,'payments',12,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":6,\"payment_date\":\"2026-09-05 00:00:00\",\"amount\":400000,\"method\":\"bank\",\"reference\":\"HBL-552190\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":12}'),
(174,'payment_allocations',12,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":15,\"amount\":334800,\"payment_id\":12,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":12}'),
(175,'payment_allocations',13,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":17,\"amount\":65200,\"payment_id\":12,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":13}'),
(176,'payments',13,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":2,\"payment_date\":\"2026-08-06 00:00:00\",\"amount\":68120,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":13}'),
(177,'payment_allocations',14,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":12,\"amount\":68120,\"payment_id\":13,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":14}'),
(178,'payments',14,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":3,\"payment_date\":\"2026-08-08 00:00:00\",\"amount\":29700,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":14}'),
(179,'payment_allocations',15,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":13,\"amount\":29700,\"payment_id\":14,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":15}'),
(180,'payments',15,'insert',1,'2026-11-12 04:04:30',NULL,'{\"vendor_id\":1,\"payment_date\":\"2026-08-10 00:00:00\",\"amount\":116100,\"method\":\"cash\",\"reference\":null,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":15}'),
(181,'payment_allocations',16,'insert',1,'2026-11-12 04:04:30',NULL,'{\"expense_id\":14,\"amount\":116100,\"payment_id\":15,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":16}'),
(182,'resource_usage',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"resource_id\":1,\"season_stage_id\":6,\"usage_date\":\"2026-08-03 00:00:00\",\"hours\":24,\"acres\":27,\"fuel_litres\":260,\"operator\":\"Rashid Ali\",\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(183,'resource_usage',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"resource_id\":1,\"season_stage_id\":9,\"usage_date\":\"2026-10-21 00:00:00\",\"hours\":9,\"acres\":12,\"fuel_litres\":70,\"operator\":\"Rashid Ali\",\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(184,'resource_usage',3,'insert',1,'2026-11-12 04:04:30',NULL,'{\"resource_id\":1,\"season_stage_id\":11,\"usage_date\":\"2026-10-16 00:00:00\",\"hours\":16,\"acres\":8,\"fuel_litres\":70,\"operator\":\"Rashid Ali\",\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":3}'),
(185,'shared_costs',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"category_id\":16,\"resource_id\":1,\"vendor_id\":3,\"cost_date\":\"2026-10-22 00:00:00\",\"description\":\"MF-385 hydraulic pump repair\",\"total_amount\":12000,\"basis\":\"hours\",\"status\":\"draft\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(186,'recurring_rules',1,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_stage_id\":13,\"category_id\":37,\"vendor_id\":5,\"description\":\"Tubewell electricity\",\"amount\":14500,\"frequency\":\"monthly\",\"start_date\":\"2026-10-20 00:00:00\",\"next_run_date\":\"2026-10-20 00:00:00\",\"end_date\":\"2027-01-31 00:00:00\",\"is_active\":true,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":1}'),
(187,'recurring_rules',2,'insert',1,'2026-11-12 04:04:30',NULL,'{\"season_stage_id\":9,\"category_id\":6,\"vendor_id\":4,\"description\":\"Harvest crew weekly wages\",\"amount\":21000,\"frequency\":\"weekly\",\"start_date\":\"2026-10-19 00:00:00\",\"next_run_date\":\"2026-10-26 00:00:00\",\"end_date\":\"2026-11-15 00:00:00\",\"is_active\":true,\"created_by\":1,\"updated_at\":\"2026-11-12 09:04:30\",\"id\":2}'),
(188,'expense_categories',48,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":5,\"name\":\"Sowing & planting labour\",\"cost_type\":\"labour\",\"default_unit\":\"lumpsum\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":48}'),
(189,'expense_categories',49,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":28,\"name\":\"Pesticide (unclassified)\",\"cost_type\":\"material\",\"default_unit\":\"bottle\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":49}'),
(190,'expense_categories',50,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":28,\"name\":\"Spraying charges & fuel\",\"cost_type\":\"service\",\"default_unit\":\"litre\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":50}'),
(191,'expense_categories',51,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":5,\"name\":\"Labour food\",\"cost_type\":\"labour\",\"default_unit\":\"lumpsum\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":51}'),
(192,'expense_categories',52,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":10,\"name\":\"Harvesting, chopping & ensiling\",\"cost_type\":\"machinery\",\"default_unit\":\"acre\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":52}'),
(193,'expense_categories',53,'insert',1,'2026-11-16 07:14:23',NULL,'{\"parent_id\":39,\"name\":\"Input transportation\",\"cost_type\":\"transport\",\"default_unit\":\"lumpsum\",\"is_active\":true,\"farm_id\":1,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":53}'),
(194,'fields',5,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L01 \\u2013 Parcel 01\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":5}'),
(195,'fields',6,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L02 \\u2013 Parcel 02\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":6}'),
(196,'fields',7,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L03 \\u2013 Parcel 03\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":7}'),
(197,'fields',8,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L04 \\u2013 Parcel 04\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":8}'),
(198,'fields',9,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L05 \\u2013 Parcel 05\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":9}'),
(199,'fields',10,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L06 \\u2013 Parcel 06\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":10}'),
(200,'fields',11,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L07 \\u2013 Parcel 07\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":11}'),
(201,'fields',12,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L08 \\u2013 Parcel 08\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":12}'),
(202,'fields',13,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L09 \\u2013 Parcel 09\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":13}'),
(203,'fields',14,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L10 \\u2013 Parcel 10\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":14}'),
(204,'fields',15,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L11 \\u2013 Parcel 11\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":15}'),
(205,'fields',16,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L12 \\u2013 Parcel 12\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":16}'),
(206,'fields',17,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L13 \\u2013 Parcel 13\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":17}'),
(207,'fields',18,'insert',1,'2026-11-16 07:14:23',NULL,'{\"name\":\"HAL L14 \\u2013 Parcel 14\",\"area_acres\":1,\"is_active\":true,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:23\",\"id\":18}'),
(208,'crop_seasons',4,'insert',1,'2026-11-16 07:14:24',NULL,'{\"name\":\"Maize 2026 \\u2013 Faisalabad (HAL)\",\"crop_id\":1,\"start_date\":\"2026-06-30 00:00:00\",\"expected_end_date\":null,\"notes\":\"Imported from the HAL Maize Crop 2026 Cost Accounting Dashboard (Rev1). Faisalabad, 14 acres, costs recorded whole-crop (no field-level split); parcels L01\\u2013L14 are provisional 1-acre units. Each expense description starts with its workbook Txn ID. Production is provisional at the 500 maund\\/acre benchmark (280,000 kg) until measured.\",\"status\":\"planned\",\"code\":\"CS-2026-06\",\"total_area_acres\":14,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":4}'),
(209,'season_stages',16,'insert',1,'2026-11-16 07:14:24',NULL,'{\"stage_template_id\":1,\"season_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":16}'),
(210,'season_stages',17,'insert',1,'2026-11-16 07:14:24',NULL,'{\"stage_template_id\":2,\"season_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":17}'),
(211,'season_stages',18,'insert',1,'2026-11-16 07:14:24',NULL,'{\"stage_template_id\":3,\"season_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":18}'),
(212,'season_stages',19,'insert',1,'2026-11-16 07:14:24',NULL,'{\"stage_template_id\":4,\"season_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":19}'),
(213,'season_stages',20,'insert',1,'2026-11-16 07:14:24',NULL,'{\"stage_template_id\":5,\"season_id\":4,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":20}'),
(214,'expenses',23,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":27,\"expense_date\":\"2026-06-30 00:00:00\",\"description\":\"TX0001 \\u2013 Manure for land prepration (1.5 Acres)\",\"quantity\":6,\"unit_code\":\"trolley\",\"unit_price\":6000,\"amount\":\"36000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":23}'),
(215,'season_stages',16,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-06-30 00:00:00\"}'),
(216,'crop_seasons',4,'update',1,'2026-11-16 07:14:24','{\"status\":\"planned\"}','{\"status\":\"active\"}'),
(217,'expenses',24,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-02 00:00:00\",\"description\":\"TX0002 \\u2013 Tractor Diesel\",\"quantity\":40.01,\"unit_code\":\"litre\",\"unit_price\":394.68,\"amount\":\"15791.15\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":24}'),
(218,'expenses',25,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-02 00:00:00\",\"description\":\"TX0003 \\u2013 Tractor Diesel\",\"quantity\":9.7,\"unit_code\":\"litre\",\"unit_price\":394.68,\"amount\":\"3828.40\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":25}'),
(219,'expenses',26,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":16,\"expense_date\":\"2026-08-02 00:00:00\",\"description\":\"TX0004 \\u2013 Diesel pipe change tractor\",\"quantity\":1,\"unit_code\":\"no\",\"unit_price\":1670,\"amount\":\"1670.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":26}'),
(220,'expenses',27,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":16,\"expense_date\":\"2026-08-02 00:00:00\",\"description\":\"TX0005 \\u2013 Tractor genrator bearing\",\"quantity\":1,\"unit_code\":\"no\",\"unit_price\":1500,\"amount\":\"1500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":27}'),
(221,'expenses',28,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-03 00:00:00\",\"description\":\"TX0006 \\u2013 Tractor diesel\",\"quantity\":102.97,\"unit_code\":\"litre\",\"unit_price\":388.97,\"amount\":\"40052.24\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":28}'),
(222,'expenses',29,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-05 00:00:00\",\"description\":\"TX0007 \\u2013 Tractor diesel\",\"quantity\":50,\"unit_code\":\"litre\",\"unit_price\":387.19,\"amount\":\"19359.50\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":29}'),
(223,'expenses',30,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-06 00:00:00\",\"description\":\"TX0008 \\u2013 Tractor diesel\",\"quantity\":30.02,\"unit_code\":\"litre\",\"unit_price\":386.52,\"amount\":\"11603.33\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":30}'),
(224,'expenses',31,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-07 00:00:00\",\"description\":\"TX0009 \\u2013 Tractor diesel\",\"quantity\":96.01,\"unit_code\":\"litre\",\"unit_price\":386.52,\"amount\":\"37109.79\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":31}'),
(225,'expenses',32,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-07 00:00:00\",\"description\":\"TX0010 \\u2013 Tractor diesel\",\"quantity\":10,\"unit_code\":\"litre\",\"unit_price\":386.52,\"amount\":\"3865.20\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":32}'),
(226,'expenses',33,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":51,\"expense_date\":\"2026-08-12 00:00:00\",\"description\":\"TX0011 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":140,\"amount\":\"140.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":33}'),
(227,'expenses',34,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":16,\"expense_date\":\"2026-08-13 00:00:00\",\"description\":\"TX0012 \\u2013 Ridger repair \\u00b7 Partial record \\u00b7 Machinery repair - lump sum\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":1000,\"amount\":\"1000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":34}'),
(228,'expenses',35,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":51,\"expense_date\":\"2026-08-13 00:00:00\",\"description\":\"TX0013 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":140,\"amount\":\"140.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":35}'),
(229,'expenses',36,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":16,\"category_id\":2,\"expense_date\":\"2026-08-29 00:00:00\",\"description\":\"TX0014 \\u2013 Tractor Diesel (Re-prep) \\u00b7 Re-work \\u00b7 Re-preparation of re-worked land\",\"quantity\":110,\"unit_code\":\"litre\",\"unit_price\":374.09,\"amount\":\"41149.90\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":36}'),
(230,'expenses',37,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":48,\"expense_date\":\"2026-08-10 00:00:00\",\"description\":\"TX0015 \\u2013 Seed plantation labor \\u00b7 Partial record \\u00b7 Planting labour - lump sum\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":49000,\"amount\":\"49000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":37}'),
(231,'season_stages',17,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-08-10 00:00:00\"}'),
(232,'expenses',38,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":20,\"expense_date\":\"2026-08-12 00:00:00\",\"description\":\"TX0016 \\u2013 Seeds procurement (Code 3875) \\u00b7 Partial record \\u00b7 Bag count not itemised; batch code 3875\",\"quantity\":1,\"unit_code\":\"bag\",\"unit_price\":124000,\"amount\":\"124000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":38}'),
(233,'expenses',39,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":20,\"expense_date\":\"2026-08-12 00:00:00\",\"description\":\"TX0017 \\u2013 Seeds procurement (Code 4040) \\u00b7 Partial record \\u00b7 Bag count not itemised; batch code 4040\",\"quantity\":1,\"unit_code\":\"bag\",\"unit_price\":60000,\"amount\":\"60000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":39}'),
(234,'expenses',40,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":53,\"expense_date\":\"2026-08-12 00:00:00\",\"description\":\"TX0018 \\u2013 Seed transportation charges (from Sudhar)\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":1500,\"amount\":\"1500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":40}'),
(235,'expenses',41,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":48,\"expense_date\":\"2026-09-08 00:00:00\",\"description\":\"TX0019 \\u2013 Seed plantation labor (Re-planting) \\u00b7 Re-work \\u00b7 Partial record \\u00b7 Re-planting labour\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":22500,\"amount\":\"22500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":41}'),
(236,'expenses',42,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":2,\"expense_date\":\"2026-09-08 00:00:00\",\"description\":\"TX0020 \\u2013 Tractor Diesel (Re-panting) \\u00b7 Re-work \\u00b7 Re-planting diesel\",\"quantity\":60,\"unit_code\":\"litre\",\"unit_price\":379.5,\"amount\":\"22770.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":42}'),
(237,'expenses',43,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":2,\"expense_date\":\"2026-09-08 00:00:00\",\"description\":\"TX0021 \\u2013 Tractor Diesel (Replanting) \\u00b7 Re-work \\u00b7 Re-planting diesel\",\"quantity\":31,\"unit_code\":\"litre\",\"unit_price\":376.13,\"amount\":\"11660.03\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":43}'),
(238,'expenses',44,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":20,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0022 \\u2013 Seeds procurement \\u00b7 Partial record \\u00b7 Possible re-planting seed - tag if applicable\",\"quantity\":1,\"unit_code\":\"bag\",\"unit_price\":50000,\"amount\":\"50000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":44}'),
(239,'expenses',45,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":17,\"category_id\":20,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0023 \\u2013 Seeds procurement \\u00b7 Partial record \\u00b7 Possible re-planting seed - tag if applicable\",\"quantity\":1,\"unit_code\":\"bag\",\"unit_price\":5000,\"amount\":\"5000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":45}'),
(240,'expenses',46,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-08-21 00:00:00\",\"description\":\"TX0024 \\u2013 Medicine\\/Pesticide after 24 hour \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":40800,\"amount\":\"40800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":46}'),
(241,'season_stages',18,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-08-21 00:00:00\"}'),
(242,'expenses',47,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":24,\"expense_date\":\"2026-08-21 00:00:00\",\"description\":\"TX0025 \\u2013 Babbarsher Urea \\u00b7 Partial record \\u00b7 Unit \\\"No.s\\\" - likely bags; brand noted\",\"quantity\":4,\"unit_code\":\"no\",\"unit_price\":16100,\"amount\":\"64400.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":47}'),
(243,'expenses',48,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-08-21 00:00:00\",\"description\":\"TX0026 \\u2013 Spray \\u00b7 Partial record \\u00b7 Generic spray - product not named\",\"quantity\":7,\"unit_code\":\"bottle\",\"unit_price\":500,\"amount\":\"3500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":48}'),
(244,'expenses',49,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-08-21 00:00:00\",\"description\":\"TX0027 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":560,\"amount\":\"560.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":49}'),
(245,'expenses',50,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":2,\"expense_date\":\"2026-08-21 00:00:00\",\"description\":\"TX0028 \\u2013 Tractor Diesel\",\"quantity\":35,\"unit_code\":\"litre\",\"unit_price\":365.71,\"amount\":\"12799.85\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":50}'),
(246,'expenses',51,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-08-29 00:00:00\",\"description\":\"TX0029 \\u2013 Petrol for spray machine\",\"quantity\":4,\"unit_code\":\"litre\",\"unit_price\":360,\"amount\":\"1440.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":51}'),
(247,'expenses',52,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-08-29 00:00:00\",\"description\":\"TX0030 \\u2013 Labor food (3 times)\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":1000,\"amount\":\"1000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":52}'),
(248,'expenses',53,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":2,\"expense_date\":\"2026-08-29 00:00:00\",\"description\":\"TX0031 \\u2013 Tractor Diesel \\u00b7 Re-work \\u00b7 Re-preparation of re-worked land\",\"quantity\":110,\"unit_code\":\"litre\",\"unit_price\":374.09,\"amount\":\"41149.90\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":53}'),
(249,'expenses',54,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-09-08 00:00:00\",\"description\":\"TX0032 \\u2013 Petrol for spray machine (Re-planted) \\u00b7 Re-work \\u00b7 Re-planting spray fuel\",\"quantity\":2,\"unit_code\":\"litre\",\"unit_price\":360,\"amount\":\"720.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":54}'),
(250,'expenses',55,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-09-15 00:00:00\",\"description\":\"TX0033 \\u2013 Labor charges for medicine \\u00b7 Pesticide application labour\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":13500,\"amount\":\"13500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":55}'),
(251,'expenses',56,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":53,\"expense_date\":\"2026-09-15 00:00:00\",\"description\":\"TX0034 \\u2013 Transportation charges for fertilizer\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":1500,\"amount\":\"1500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":56}'),
(252,'expenses',57,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-15 00:00:00\",\"description\":\"TX0035 \\u2013 Pesticide for intruders \\u00b7 Partial record \\u00b7 Pest\\/intruder deterrent - not named\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":200,\"amount\":\"200.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":57}'),
(253,'expenses',58,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-09-15 00:00:00\",\"description\":\"TX0036 \\u2013 Petrol for spray machine\",\"quantity\":2.5,\"unit_code\":\"litre\",\"unit_price\":320,\"amount\":\"800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":58}'),
(254,'expenses',59,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0037 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":15200,\"amount\":\"15200.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":59}'),
(255,'expenses',60,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0038 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":33000,\"amount\":\"33000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":60}'),
(256,'expenses',61,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0039 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":7200,\"amount\":\"7200.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":61}'),
(257,'expenses',62,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0040 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":500,\"amount\":\"500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":62}'),
(258,'expenses',63,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0041 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":29250,\"amount\":\"29250.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":63}'),
(259,'expenses',64,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0042 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":4500,\"amount\":\"4500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":64}'),
(260,'expenses',65,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":24,\"expense_date\":\"2026-09-16 00:00:00\",\"description\":\"TX0043 \\u2013 Urea fertilizer\",\"quantity\":14,\"unit_code\":\"bag\",\"unit_price\":4700,\"amount\":\"65800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":65}'),
(261,'expenses',66,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-18 00:00:00\",\"description\":\"TX0044 \\u2013 Medicine\\/Pesticide (Gangvi) \\u00b7 Partial record \\u00b7 Brand noted; product class not confirmed\",\"quantity\":3,\"unit_code\":\"litre\",\"unit_price\":1400,\"amount\":\"4200.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":66}'),
(262,'expenses',67,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-18 00:00:00\",\"description\":\"TX0045 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":2600,\"amount\":\"2600.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":67}'),
(263,'expenses',68,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-09-18 00:00:00\",\"description\":\"TX0046 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":100,\"amount\":\"100.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":68}'),
(264,'expenses',69,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":2,\"expense_date\":\"2026-09-21 00:00:00\",\"description\":\"TX0047 \\u2013 Tractor Diesel\",\"quantity\":50,\"unit_code\":\"litre\",\"unit_price\":425.46,\"amount\":\"21273.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":69}'),
(265,'expenses',70,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":24,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0048 \\u2013 Urea fertilizer\",\"quantity\":14,\"unit_code\":\"bag\",\"unit_price\":4642.86,\"amount\":\"65000.04\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":70}'),
(266,'expenses',71,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0049 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":7500,\"amount\":\"7500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":71}'),
(267,'expenses',72,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0050 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":1800,\"amount\":\"1800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":72}'),
(268,'expenses',73,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0051 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":4200,\"amount\":\"4200.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":73}'),
(269,'expenses',74,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":26,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0052 \\u2013 Fertilizer (FMC SOP)\",\"quantity\":3,\"unit_code\":\"bag\",\"unit_price\":10500,\"amount\":\"31500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":74}'),
(270,'expenses',75,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":53,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0053 \\u2013 Transportation charges for pesticides and fertilizers\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":500,\"amount\":\"500.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":75}'),
(271,'expenses',76,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-09-29 00:00:00\",\"description\":\"TX0054 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":100,\"amount\":\"100.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":76}'),
(272,'expenses',77,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-06 00:00:00\",\"description\":\"TX0055 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":6700,\"amount\":\"6700.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":77}'),
(273,'expenses',78,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-10-06 00:00:00\",\"description\":\"TX0056 \\u2013 Petrol for spray machine\",\"quantity\":1,\"unit_code\":\"litre\",\"unit_price\":430,\"amount\":\"430.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":78}'),
(274,'expenses',79,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":2,\"expense_date\":\"2026-10-06 00:00:00\",\"description\":\"TX0057 \\u2013 Tractor Diesel\",\"quantity\":71.37,\"unit_code\":\"litre\",\"unit_price\":397.76,\"amount\":\"28388.13\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":79}'),
(275,'expenses',80,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-10-07 00:00:00\",\"description\":\"TX0058 \\u2013 Petrol for spray machine\",\"quantity\":1,\"unit_code\":\"litre\",\"unit_price\":430,\"amount\":\"430.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":80}'),
(276,'expenses',81,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-10-07 00:00:00\",\"description\":\"TX0059 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":100,\"amount\":\"100.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":81}'),
(277,'expenses',82,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-10-07 00:00:00\",\"description\":\"TX0060 \\u2013 Labor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":240,\"amount\":\"240.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":82}'),
(278,'expenses',83,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-07 00:00:00\",\"description\":\"TX0061 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":1,\"unit_code\":\"bottle\",\"unit_price\":7000,\"amount\":\"7000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":83}'),
(279,'expenses',84,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":27,\"expense_date\":\"2026-10-07 00:00:00\",\"description\":\"TX0062 \\u2013 Manure Fetilizer\",\"quantity\":20,\"unit_code\":\"trolley\",\"unit_price\":5000,\"amount\":\"100000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":84}'),
(280,'expenses',85,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-16 00:00:00\",\"description\":\"TX0063 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":5,\"unit_code\":\"bottle\",\"unit_price\":1400,\"amount\":\"7000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":85}'),
(281,'expenses',86,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":24,\"expense_date\":\"2026-10-16 00:00:00\",\"description\":\"TX0064 \\u2013 Urea fertilizer\",\"quantity\":14,\"unit_code\":\"bag\",\"unit_price\":4700,\"amount\":\"65800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":86}'),
(282,'expenses',87,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":53,\"expense_date\":\"2026-10-16 00:00:00\",\"description\":\"TX0065 \\u2013 Transportation charges urea fertilizer\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":800,\"amount\":\"800.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":87}'),
(283,'expenses',88,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-17 00:00:00\",\"description\":\"TX0066 \\u2013 Medicine\\/Pesticide \\u00b7 Incomplete record \\u00b7 Product name & quantity not in record\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":8000,\"amount\":\"8000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":88}'),
(284,'expenses',89,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":50,\"expense_date\":\"2026-10-17 00:00:00\",\"description\":\"TX0067 \\u2013 Petrol for spray machine\",\"quantity\":2,\"unit_code\":\"litre\",\"unit_price\":430,\"amount\":\"860.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":89}'),
(285,'expenses',90,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":16,\"expense_date\":\"2026-10-17 00:00:00\",\"description\":\"TX0068 \\u2013 Tractor repair (2 Pipe with clips)\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":300,\"amount\":\"300.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":90}'),
(286,'expenses',91,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":16,\"expense_date\":\"2026-10-17 00:00:00\",\"description\":\"TX0069 \\u2013 Tractor repair (Sulf repair)\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":4400,\"amount\":\"4400.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":91}'),
(287,'expenses',92,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":16,\"expense_date\":\"2026-10-17 00:00:00\",\"description\":\"TX0070 \\u2013 Tracktor repair (Water tank)\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":430,\"amount\":\"430.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":92}'),
(288,'expenses',93,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-20 00:00:00\",\"description\":\"TX0071 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":5,\"unit_code\":\"packet\",\"unit_price\":5000,\"amount\":\"25000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":93}'),
(289,'expenses',94,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":53,\"expense_date\":\"2026-10-20 00:00:00\",\"description\":\"TX0072 \\u2013 Transportation medicine\\/pesticide\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":400,\"amount\":\"400.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":94}'),
(290,'expenses',95,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-20 00:00:00\",\"description\":\"TX0073 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":3,\"unit_code\":\"packet\",\"unit_price\":1000,\"amount\":\"3000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":95}'),
(291,'expenses',96,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":16,\"expense_date\":\"2026-10-20 00:00:00\",\"description\":\"TX0074 \\u2013 Tractor repair nuts and bolts\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":240,\"amount\":\"240.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":96}'),
(292,'expenses',97,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":2,\"expense_date\":\"2026-10-22 00:00:00\",\"description\":\"TX0075 \\u2013 Tractor Diesel\",\"quantity\":60,\"unit_code\":\"litre\",\"unit_price\":391.67,\"amount\":\"23500.20\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":97}'),
(293,'expenses',98,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":49,\"expense_date\":\"2026-10-25 00:00:00\",\"description\":\"TX0076 \\u2013 Medicine\\/Pesticide \\u00b7 Partial record \\u00b7 Product not named\",\"quantity\":2,\"unit_code\":\"packet\",\"unit_price\":1000,\"amount\":\"2000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":98}'),
(294,'expenses',99,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":18,\"category_id\":51,\"expense_date\":\"2026-10-25 00:00:00\",\"description\":\"TX0077 \\u2013 Lobor food\",\"quantity\":1,\"unit_code\":\"lumpsum\",\"unit_price\":140,\"amount\":\"140.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":99}'),
(295,'expenses',100,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":20,\"category_id\":33,\"expense_date\":\"2026-11-01 00:00:00\",\"description\":\"TX0078 \\u2013 Plastic covers for silage \\u00b7 Booked \\/ accrued\",\"quantity\":2,\"unit_code\":\"roll\",\"unit_price\":16000,\"amount\":\"32000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":100}'),
(296,'season_stages',20,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-11-01 00:00:00\"}'),
(297,'expenses',101,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":19,\"category_id\":52,\"expense_date\":\"2026-11-03 00:00:00\",\"description\":\"TX0079 \\u2013 Silage harvesting and production cost \\u00b7 Booked \\/ accrued; combined harvest+chop+ensile; production ~1 week\",\"quantity\":9,\"unit_code\":\"acre\",\"unit_price\":50000,\"amount\":\"450000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":101}'),
(298,'season_stages',19,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-11-03 00:00:00\"}'),
(299,'expenses',102,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"season_stage_id\":19,\"category_id\":52,\"expense_date\":\"2026-11-04 00:00:00\",\"description\":\"TX0080 \\u2013 Silage harvesting and production cost \\u00b7 Booked \\/ accrued; combined harvest+chop+ensile; production ~1 week\",\"quantity\":5,\"unit_code\":\"acre\",\"unit_price\":50000,\"amount\":\"250000.00\",\"vendor_id\":null,\"resource_id\":null,\"field_id\":null,\"is_notional\":false,\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":102}'),
(300,'season_stages',16,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null,\"end_date\":null,\"notes\":null}','{\"status\":\"completed\",\"start_date\":\"2026-06-30 00:00:00\",\"end_date\":\"2026-08-29 00:00:00\",\"notes\":\"Workbook activities: Land Preparation.\"}'),
(301,'season_stages',17,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null,\"end_date\":null,\"notes\":null}','{\"status\":\"completed\",\"start_date\":\"2026-08-10 00:00:00\",\"end_date\":\"2026-09-16 00:00:00\",\"notes\":\"Workbook activities: Sowing & Planting, Seed Procurement & Treatment.\"}'),
(302,'season_stages',18,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null,\"end_date\":null,\"notes\":null}','{\"status\":\"completed\",\"start_date\":\"2026-08-21 00:00:00\",\"end_date\":\"2026-10-25 00:00:00\",\"notes\":\"Workbook activities: Weed & Crop Protection, Fertiliser Application, Crop Maintenance & Supervision, Land Preparation.\"}'),
(303,'season_stages',19,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null,\"end_date\":null,\"notes\":null}','{\"status\":\"completed\",\"start_date\":\"2026-11-03 00:00:00\",\"end_date\":\"2026-11-04 00:00:00\",\"notes\":\"Workbook activities: Silage Preparation & Ensiling.\"}'),
(304,'season_stages',20,'update',1,'2026-11-16 07:14:24','{\"status\":\"not_started\",\"start_date\":null,\"notes\":null}','{\"status\":\"in_progress\",\"start_date\":\"2026-11-01 00:00:00\",\"notes\":\"Workbook activities: Silage Preparation & Ensiling.\"}'),
(305,'harvest_records',3,'insert',1,'2026-11-16 07:14:24',NULL,'{\"season_id\":4,\"harvest_date\":\"2026-11-04 00:00:00\",\"field_id\":null,\"method\":\"estimate\",\"quantity_kg\":280000,\"trolley_count\":null,\"avg_trolley_kg\":null,\"created_by\":1,\"updated_at\":\"2026-11-16 12:14:24\",\"id\":3}'),
(306,'crop_seasons',4,'update',1,'2026-11-16 07:14:24','{\"status\":\"planned\"}','{\"status\":\"harvested\"}'),
(307,'payments',16,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-06-30 00:00:00\",\"amount\":36000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":16}'),
(308,'payment_allocations',17,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":23,\"amount\":36000,\"payment_id\":16,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":17}'),
(309,'payments',17,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-02 00:00:00\",\"amount\":15791.15,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":17}'),
(310,'payment_allocations',18,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":24,\"amount\":15791.15,\"payment_id\":17,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":18}'),
(311,'payments',18,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-02 00:00:00\",\"amount\":3828.4,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":18}'),
(312,'payment_allocations',19,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":25,\"amount\":3828.4,\"payment_id\":18,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":19}'),
(313,'payments',19,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-02 00:00:00\",\"amount\":1670,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":19}'),
(314,'payment_allocations',20,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":26,\"amount\":1670,\"payment_id\":19,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":20}'),
(315,'payments',20,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-02 00:00:00\",\"amount\":1500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":20}'),
(316,'payment_allocations',21,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":27,\"amount\":1500,\"payment_id\":20,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":21}'),
(317,'payments',21,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-03 00:00:00\",\"amount\":40052.24,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":21}'),
(318,'payment_allocations',22,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":28,\"amount\":40052.24,\"payment_id\":21,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":22}'),
(319,'payments',22,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-05 00:00:00\",\"amount\":19359.5,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":22}'),
(320,'payment_allocations',23,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":29,\"amount\":19359.5,\"payment_id\":22,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":23}'),
(321,'payments',23,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-06 00:00:00\",\"amount\":11603.33,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":23}'),
(322,'payment_allocations',24,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":30,\"amount\":11603.33,\"payment_id\":23,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":24}'),
(323,'payments',24,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-07 00:00:00\",\"amount\":37109.79,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":24}'),
(324,'payment_allocations',25,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":31,\"amount\":37109.79,\"payment_id\":24,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":25}'),
(325,'payments',25,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-07 00:00:00\",\"amount\":3865.2,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":25}'),
(326,'payment_allocations',26,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":32,\"amount\":3865.2,\"payment_id\":25,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":26}'),
(327,'payments',26,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-10 00:00:00\",\"amount\":49000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":26}'),
(328,'payment_allocations',27,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":37,\"amount\":49000,\"payment_id\":26,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":27}'),
(329,'payments',27,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-12 00:00:00\",\"amount\":140,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":27}'),
(330,'payment_allocations',28,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":33,\"amount\":140,\"payment_id\":27,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":28}'),
(331,'payments',28,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-12 00:00:00\",\"amount\":124000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":28}'),
(332,'payment_allocations',29,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":38,\"amount\":124000,\"payment_id\":28,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":29}'),
(333,'payments',29,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-12 00:00:00\",\"amount\":60000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":29}'),
(334,'payment_allocations',30,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":39,\"amount\":60000,\"payment_id\":29,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":30}'),
(335,'payments',30,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-12 00:00:00\",\"amount\":1500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":30}'),
(336,'payment_allocations',31,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":40,\"amount\":1500,\"payment_id\":30,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":31}'),
(337,'payments',31,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-13 00:00:00\",\"amount\":1000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":31}'),
(338,'payment_allocations',32,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":34,\"amount\":1000,\"payment_id\":31,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":32}'),
(339,'payments',32,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-13 00:00:00\",\"amount\":140,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":32}'),
(340,'payment_allocations',33,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":35,\"amount\":140,\"payment_id\":32,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":33}'),
(341,'payments',33,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-21 00:00:00\",\"amount\":40800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":33}'),
(342,'payment_allocations',34,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":46,\"amount\":40800,\"payment_id\":33,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":34}'),
(343,'payments',34,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-21 00:00:00\",\"amount\":64400,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":34}'),
(344,'payment_allocations',35,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":47,\"amount\":64400,\"payment_id\":34,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":35}'),
(345,'payments',35,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-21 00:00:00\",\"amount\":3500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":35}'),
(346,'payment_allocations',36,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":48,\"amount\":3500,\"payment_id\":35,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":36}'),
(347,'payments',36,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-21 00:00:00\",\"amount\":560,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":36}'),
(348,'payment_allocations',37,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":49,\"amount\":560,\"payment_id\":36,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":37}'),
(349,'payments',37,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-21 00:00:00\",\"amount\":12799.85,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":37}'),
(350,'payment_allocations',38,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":50,\"amount\":12799.85,\"payment_id\":37,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":38}'),
(351,'payments',38,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-29 00:00:00\",\"amount\":41149.9,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":38}'),
(352,'payment_allocations',39,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":36,\"amount\":41149.9,\"payment_id\":38,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":39}'),
(353,'payments',39,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-29 00:00:00\",\"amount\":1440,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":39}'),
(354,'payment_allocations',40,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":51,\"amount\":1440,\"payment_id\":39,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":40}'),
(355,'payments',40,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-29 00:00:00\",\"amount\":1000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":40}'),
(356,'payment_allocations',41,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":52,\"amount\":1000,\"payment_id\":40,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":41}'),
(357,'payments',41,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-08-29 00:00:00\",\"amount\":41149.9,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":41}'),
(358,'payment_allocations',42,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":53,\"amount\":41149.9,\"payment_id\":41,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":42}'),
(359,'payments',42,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-08 00:00:00\",\"amount\":22500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":42}'),
(360,'payment_allocations',43,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":41,\"amount\":22500,\"payment_id\":42,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":43}'),
(361,'payments',43,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-08 00:00:00\",\"amount\":22770,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":43}'),
(362,'payment_allocations',44,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":42,\"amount\":22770,\"payment_id\":43,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":44}'),
(363,'payments',44,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-08 00:00:00\",\"amount\":11660.03,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":44}'),
(364,'payment_allocations',45,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":43,\"amount\":11660.03,\"payment_id\":44,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":45}'),
(365,'payments',45,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-08 00:00:00\",\"amount\":720,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":45}'),
(366,'payment_allocations',46,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":54,\"amount\":720,\"payment_id\":45,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":46}'),
(367,'payments',46,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-15 00:00:00\",\"amount\":13500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":46}'),
(368,'payment_allocations',47,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":55,\"amount\":13500,\"payment_id\":46,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":47}'),
(369,'payments',47,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-15 00:00:00\",\"amount\":1500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":47}'),
(370,'payment_allocations',48,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":56,\"amount\":1500,\"payment_id\":47,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":48}'),
(371,'payments',48,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-15 00:00:00\",\"amount\":200,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":48}'),
(372,'payment_allocations',49,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":57,\"amount\":200,\"payment_id\":48,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":49}'),
(373,'payments',49,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-15 00:00:00\",\"amount\":800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":49}'),
(374,'payment_allocations',50,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":58,\"amount\":800,\"payment_id\":49,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":50}'),
(375,'payments',50,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":50000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":50}'),
(376,'payment_allocations',51,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":44,\"amount\":50000,\"payment_id\":50,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":51}'),
(377,'payments',51,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":5000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":51}'),
(378,'payment_allocations',52,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":45,\"amount\":5000,\"payment_id\":51,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":52}'),
(379,'payments',52,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":15200,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":52}'),
(380,'payment_allocations',53,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":59,\"amount\":15200,\"payment_id\":52,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":53}'),
(381,'payments',53,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":33000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":53}'),
(382,'payment_allocations',54,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":60,\"amount\":33000,\"payment_id\":53,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":54}'),
(383,'payments',54,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":7200,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":54}'),
(384,'payment_allocations',55,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":61,\"amount\":7200,\"payment_id\":54,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":55}'),
(385,'payments',55,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":55}'),
(386,'payment_allocations',56,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":62,\"amount\":500,\"payment_id\":55,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":56}'),
(387,'payments',56,'insert',1,'2026-11-23 04:13:29',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":29250,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":56}'),
(388,'payment_allocations',57,'insert',1,'2026-11-23 04:13:29',NULL,'{\"expense_id\":63,\"amount\":29250,\"payment_id\":56,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:29\",\"id\":57}'),
(389,'payments',57,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":4500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":57}'),
(390,'payment_allocations',58,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":64,\"amount\":4500,\"payment_id\":57,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":58}'),
(391,'payments',58,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-16 00:00:00\",\"amount\":65800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":58}'),
(392,'payment_allocations',59,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":65,\"amount\":65800,\"payment_id\":58,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":59}'),
(393,'payments',59,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-18 00:00:00\",\"amount\":4200,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":59}'),
(394,'payment_allocations',60,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":66,\"amount\":4200,\"payment_id\":59,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":60}'),
(395,'payments',60,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-18 00:00:00\",\"amount\":2600,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":60}'),
(396,'payment_allocations',61,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":67,\"amount\":2600,\"payment_id\":60,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":61}'),
(397,'payments',61,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-18 00:00:00\",\"amount\":100,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":61}'),
(398,'payment_allocations',62,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":68,\"amount\":100,\"payment_id\":61,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":62}'),
(399,'payments',62,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-21 00:00:00\",\"amount\":21273,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":62}'),
(400,'payment_allocations',63,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":69,\"amount\":21273,\"payment_id\":62,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":63}'),
(401,'payments',63,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":65000.04,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":63}'),
(402,'payment_allocations',64,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":70,\"amount\":65000.04,\"payment_id\":63,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":64}'),
(403,'payments',64,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":7500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":64}'),
(404,'payment_allocations',65,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":71,\"amount\":7500,\"payment_id\":64,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":65}'),
(405,'payments',65,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":1800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":65}'),
(406,'payment_allocations',66,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":72,\"amount\":1800,\"payment_id\":65,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":66}'),
(407,'payments',66,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":4200,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":66}'),
(408,'payment_allocations',67,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":73,\"amount\":4200,\"payment_id\":66,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":67}'),
(409,'payments',67,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":31500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":67}'),
(410,'payment_allocations',68,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":74,\"amount\":31500,\"payment_id\":67,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":68}'),
(411,'payments',68,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":500,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":68}'),
(412,'payment_allocations',69,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":75,\"amount\":500,\"payment_id\":68,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":69}'),
(413,'payments',69,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-09-29 00:00:00\",\"amount\":100,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":69}'),
(414,'payment_allocations',70,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":76,\"amount\":100,\"payment_id\":69,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":70}'),
(415,'payments',70,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-06 00:00:00\",\"amount\":6700,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":70}'),
(416,'payment_allocations',71,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":77,\"amount\":6700,\"payment_id\":70,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":71}'),
(417,'payments',71,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-06 00:00:00\",\"amount\":430,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":71}'),
(418,'payment_allocations',72,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":78,\"amount\":430,\"payment_id\":71,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":72}'),
(419,'payments',72,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-06 00:00:00\",\"amount\":28388.13,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":72}'),
(420,'payment_allocations',73,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":79,\"amount\":28388.13,\"payment_id\":72,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":73}'),
(421,'payments',73,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-07 00:00:00\",\"amount\":430,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":73}'),
(422,'payment_allocations',74,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":80,\"amount\":430,\"payment_id\":73,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":74}'),
(423,'payments',74,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-07 00:00:00\",\"amount\":100,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":74}'),
(424,'payment_allocations',75,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":81,\"amount\":100,\"payment_id\":74,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":75}'),
(425,'payments',75,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-07 00:00:00\",\"amount\":240,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":75}'),
(426,'payment_allocations',76,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":82,\"amount\":240,\"payment_id\":75,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":76}'),
(427,'payments',76,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-07 00:00:00\",\"amount\":7000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":76}'),
(428,'payment_allocations',77,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":83,\"amount\":7000,\"payment_id\":76,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":77}'),
(429,'payments',77,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-07 00:00:00\",\"amount\":100000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":77}'),
(430,'payment_allocations',78,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":84,\"amount\":100000,\"payment_id\":77,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":78}'),
(431,'payments',78,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-16 00:00:00\",\"amount\":7000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":78}'),
(432,'payment_allocations',79,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":85,\"amount\":7000,\"payment_id\":78,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":79}'),
(433,'payments',79,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-16 00:00:00\",\"amount\":65800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":79}'),
(434,'payment_allocations',80,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":86,\"amount\":65800,\"payment_id\":79,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":80}'),
(435,'payments',80,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-16 00:00:00\",\"amount\":800,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":80}'),
(436,'payment_allocations',81,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":87,\"amount\":800,\"payment_id\":80,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":81}'),
(437,'payments',81,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-17 00:00:00\",\"amount\":8000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":81}'),
(438,'payment_allocations',82,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":88,\"amount\":8000,\"payment_id\":81,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":82}'),
(439,'payments',82,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-17 00:00:00\",\"amount\":860,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":82}'),
(440,'payment_allocations',83,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":89,\"amount\":860,\"payment_id\":82,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":83}'),
(441,'payments',83,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-17 00:00:00\",\"amount\":300,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":83}'),
(442,'payment_allocations',84,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":90,\"amount\":300,\"payment_id\":83,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":84}'),
(443,'payments',84,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-17 00:00:00\",\"amount\":4400,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":84}'),
(444,'payment_allocations',85,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":91,\"amount\":4400,\"payment_id\":84,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":85}'),
(445,'payments',85,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-17 00:00:00\",\"amount\":430,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":85}'),
(446,'payment_allocations',86,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":92,\"amount\":430,\"payment_id\":85,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":86}'),
(447,'payments',86,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-20 00:00:00\",\"amount\":25000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":86}'),
(448,'payment_allocations',87,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":93,\"amount\":25000,\"payment_id\":86,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":87}'),
(449,'payments',87,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-20 00:00:00\",\"amount\":400,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":87}'),
(450,'payment_allocations',88,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":94,\"amount\":400,\"payment_id\":87,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":88}'),
(451,'payments',88,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-20 00:00:00\",\"amount\":3000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":88}'),
(452,'payment_allocations',89,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":95,\"amount\":3000,\"payment_id\":88,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":89}'),
(453,'payments',89,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-20 00:00:00\",\"amount\":240,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":89}'),
(454,'payment_allocations',90,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":96,\"amount\":240,\"payment_id\":89,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":90}'),
(455,'payments',90,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-22 00:00:00\",\"amount\":23500.2,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":90}'),
(456,'payment_allocations',91,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":97,\"amount\":23500.2,\"payment_id\":90,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":91}'),
(457,'payments',91,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-25 00:00:00\",\"amount\":2000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":91}'),
(458,'payment_allocations',92,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":98,\"amount\":2000,\"payment_id\":91,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":92}'),
(459,'payments',92,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-10-25 00:00:00\",\"amount\":140,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":92}'),
(460,'payment_allocations',93,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":99,\"amount\":140,\"payment_id\":92,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":93}'),
(461,'payments',93,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-11-03 00:00:00\",\"amount\":450000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":93}'),
(462,'payment_allocations',94,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":101,\"amount\":450000,\"payment_id\":93,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":94}'),
(463,'payments',94,'insert',1,'2026-11-23 04:13:30',NULL,'{\"vendor_id\":null,\"payment_date\":\"2026-11-04 00:00:00\",\"amount\":250000,\"method\":\"cash\",\"reference\":\"Settled per workbook\",\"created_by\":1,\"farm_id\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":94}'),
(464,'payment_allocations',95,'insert',1,'2026-11-23 04:13:30',NULL,'{\"expense_id\":102,\"amount\":250000,\"payment_id\":94,\"created_by\":1,\"updated_at\":\"2026-11-23 09:13:30\",\"id\":95}');
/*!40000 ALTER TABLE `audit_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crop_seasons`
--

DROP TABLE IF EXISTS `crop_seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `crop_seasons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(80) NOT NULL,
  `crop_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `expected_end_date` date DEFAULT NULL,
  `closed_at` date DEFAULT NULL,
  `status` enum('planned','active','harvested','ensiled','closed') NOT NULL DEFAULT 'planned',
  `total_area_acres` decimal(8,2) NOT NULL DEFAULT 0.00,
  `benchmark_yield_maund` decimal(8,2) DEFAULT NULL,
  `packing_density_kg_m3` decimal(8,2) NOT NULL DEFAULT 650.00,
  `dry_matter_pct` decimal(5,2) NOT NULL DEFAULT 30.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crop_seasons_farm_id_code_unique` (`farm_id`,`code`),
  KEY `crop_seasons_crop_id_foreign` (`crop_id`),
  KEY `crop_seasons_created_by_foreign` (`created_by`),
  KEY `crop_seasons_farm_id_status_index` (`farm_id`,`status`),
  CONSTRAINT `crop_seasons_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `crop_seasons_crop_id_foreign` FOREIGN KEY (`crop_id`) REFERENCES `crops` (`id`),
  CONSTRAINT `crop_seasons_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crop_seasons`
--

LOCK TABLES `crop_seasons` WRITE;
/*!40000 ALTER TABLE `crop_seasons` DISABLE KEYS */;
INSERT INTO `crop_seasons` VALUES
(1,1,'CS-2026-03','March 2026',1,'2026-03-02','2026-06-30','2026-07-05','closed',10.00,NULL,650.00,30.00,'Spring maize for silage, hybrid DK-6789.',1,'2026-11-12 04:04:29','2026-11-12 04:04:30'),
(4,1,'CS-2026-06','Maize 2026 – Faisalabad (HAL)',1,'2026-06-30',NULL,NULL,'harvested',14.00,500.00,650.00,30.00,'Imported from the HAL Maize Crop 2026 Cost Accounting Dashboard (Rev1). Faisalabad, 14 acres, costs recorded whole-crop (no field-level split); parcels L01–L14 are provisional 1-acre units. Each expense description starts with its workbook Txn ID. Production is provisional at the 500 maund/acre benchmark (280,000 kg) until measured.',1,'2026-11-16 07:14:24','2026-11-16 07:14:24');
/*!40000 ALTER TABLE `crop_seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `crops`
--

DROP TABLE IF EXISTS `crops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `crops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crops_name_unique` (`name`),
  KEY `crops_created_by_foreign` (`created_by`),
  CONSTRAINT `crops_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `crops`
--

LOCK TABLES `crops` WRITE;
/*!40000 ALTER TABLE `crops` DISABLE KEYS */;
INSERT INTO `crops` VALUES
(1,'Maize',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(2,'Sorghum',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(3,'Oats',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(4,'Millet',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26');
/*!40000 ALTER TABLE `crops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned DEFAULT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(80) NOT NULL,
  `cost_type` enum('fuel','labour','machinery','maintenance','material','service','transport','utility','other') NOT NULL,
  `default_unit` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_categories_farm_id_foreign` (`farm_id`),
  KEY `expense_categories_parent_id_foreign` (`parent_id`),
  KEY `expense_categories_default_unit_foreign` (`default_unit`),
  KEY `expense_categories_created_by_foreign` (`created_by`),
  CONSTRAINT `expense_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expense_categories_default_unit_foreign` FOREIGN KEY (`default_unit`) REFERENCES `units` (`code`) ON DELETE SET NULL,
  CONSTRAINT `expense_categories_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `expense_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES
(1,NULL,NULL,'Fuel','fuel','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(2,NULL,1,'Diesel','fuel','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(3,NULL,1,'Petrol','fuel','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(4,NULL,1,'Engine oil','fuel','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(5,NULL,NULL,'Labour','labour','day',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(6,NULL,5,'Daily wages','labour','day',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(7,NULL,5,'Contract labour','labour','acre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(8,NULL,5,'Permanent staff','labour','month',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(9,NULL,5,'Compaction labour','labour','day',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(10,NULL,NULL,'Machinery hire','machinery','hour',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(11,NULL,10,'Tractor rent','machinery','hour',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(12,NULL,10,'Harvester / chopper rent','machinery','acre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(13,NULL,10,'Rotavator & disc','machinery','acre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(14,NULL,10,'Planter','machinery','acre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(15,NULL,NULL,'Maintenance','maintenance','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(16,NULL,15,'Tractor repair','maintenance','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(17,NULL,15,'Tyres & battery','maintenance','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(18,NULL,15,'Servicing','maintenance','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(19,NULL,NULL,'Seed','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(20,NULL,19,'Hybrid maize seed','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(21,NULL,19,'Sorghum seed','material','kg',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(22,NULL,19,'Oats seed','material','kg',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(23,NULL,NULL,'Fertilizer','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(24,NULL,23,'Urea','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(25,NULL,23,'DAP','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(26,NULL,23,'Potash (SOP)','material','bag',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(27,NULL,23,'Farmyard manure','material','trolley',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(28,NULL,NULL,'Crop protection','material','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(29,NULL,28,'Herbicide','material','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(30,NULL,28,'Insecticide','material','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(31,NULL,28,'Fungicide','material','litre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(32,NULL,NULL,'Silage materials','material','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(33,NULL,32,'Plastic sheet','material','roll',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(34,NULL,32,'Silage additive / inoculant','material','kg',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(35,NULL,32,'Tyres & weights','material','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(36,NULL,NULL,'Irrigation','utility','month',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(37,NULL,36,'Tubewell electricity','utility','month',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(38,NULL,36,'Canal water (abiana)','utility','acre',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(39,NULL,NULL,'Transport','transport','trip',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(40,NULL,39,'Trolley hire','transport','trip',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(41,NULL,39,'Loading & unloading','transport','trip',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(42,NULL,NULL,'Services','service','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(43,NULL,42,'Soil testing','service','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(44,NULL,42,'Agronomist visit','service','unit',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(45,NULL,NULL,'Other','other',NULL,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(46,NULL,45,'Miscellaneous','other',NULL,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(47,NULL,10,'Own machine use (notional)','machinery','hour',1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(48,1,5,'Sowing & planting labour','labour','lumpsum',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(49,1,28,'Pesticide (unclassified)','material','bottle',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(50,1,28,'Spraying charges & fuel','service','litre',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(51,1,5,'Labour food','labour','lumpsum',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(52,1,10,'Harvesting, chopping & ensiling','machinery','acre',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(53,1,39,'Input transportation','transport','lumpsum',1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23');
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_category_stage`
--

DROP TABLE IF EXISTS `expense_category_stage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_category_stage` (
  `expense_category_id` bigint(20) unsigned NOT NULL,
  `stage_template_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`expense_category_id`,`stage_template_id`),
  KEY `expense_category_stage_stage_template_id_foreign` (`stage_template_id`),
  CONSTRAINT `expense_category_stage_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_category_stage_stage_template_id_foreign` FOREIGN KEY (`stage_template_id`) REFERENCES `stage_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_category_stage`
--

LOCK TABLES `expense_category_stage` WRITE;
/*!40000 ALTER TABLE `expense_category_stage` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_category_stage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `season_id` bigint(20) unsigned NOT NULL,
  `season_stage_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(200) NOT NULL,
  `quantity` decimal(12,3) DEFAULT NULL,
  `unit_code` varchar(10) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `resource_id` bigint(20) unsigned DEFAULT NULL,
  `field_id` bigint(20) unsigned DEFAULT NULL,
  `shared_cost_id` bigint(20) unsigned DEFAULT NULL,
  `recurring_rule_id` bigint(20) unsigned DEFAULT NULL,
  `is_notional` tinyint(1) NOT NULL DEFAULT 0,
  `completeness` enum('complete','partial','incomplete') NOT NULL DEFAULT 'complete',
  `is_rework` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_farm_id_foreign` (`farm_id`),
  KEY `expenses_vendor_id_foreign` (`vendor_id`),
  KEY `expenses_resource_id_foreign` (`resource_id`),
  KEY `expenses_field_id_foreign` (`field_id`),
  KEY `expenses_shared_cost_id_foreign` (`shared_cost_id`),
  KEY `expenses_recurring_rule_id_foreign` (`recurring_rule_id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_unit_code_foreign` (`unit_code`),
  KEY `expenses_season_stage_id_season_id_foreign` (`season_stage_id`,`season_id`),
  KEY `expenses_season_id_season_stage_id_index` (`season_id`,`season_stage_id`),
  KEY `expenses_category_id_index` (`category_id`),
  KEY `expenses_expense_date_index` (`expense_date`),
  CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_recurring_rule_id_foreign` FOREIGN KEY (`recurring_rule_id`) REFERENCES `recurring_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `crop_seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_season_stage_id_season_id_foreign` FOREIGN KEY (`season_stage_id`, `season_id`) REFERENCES `season_stages` (`id`, `season_id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_shared_cost_id_foreign` FOREIGN KEY (`shared_cost_id`) REFERENCES `shared_costs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expenses_unit_code_foreign` FOREIGN KEY (`unit_code`) REFERENCES `units` (`code`) ON DELETE SET NULL,
  CONSTRAINT `expenses_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES
(1,1,1,1,2,'2026-03-02','Diesel for ploughing and discing',100.000,'litre',250.00,25000.00,2,1,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(2,1,1,1,13,'2026-03-03','Rotavator, 10 acres',10.000,'acre',1000.00,10000.00,3,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(3,1,1,1,6,'2026-03-04','Levelling and ridging labour',5.000,'day',1000.00,5000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(4,1,1,2,20,'2026-03-06','DK-6789 hybrid seed, 10 bags',10.000,'bag',4000.00,40000.00,1,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(5,1,1,2,25,'2026-03-06','DAP at sowing',1.250,'bag',12000.00,15000.00,6,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(6,1,1,3,37,'2026-04-30','Tubewell electricity, Mar–Apr',NULL,NULL,NULL,20000.00,5,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(7,1,1,3,24,'2026-04-12','Urea top dressing',4.000,'bag',4500.00,18000.00,6,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(8,1,1,4,12,'2026-06-14','Forage chopper hire, 10 acres',10.000,'acre',3000.00,30000.00,3,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(9,1,1,4,40,'2026-06-14','Trolleys to silage pit',24.000,'trip',500.00,12000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(10,1,1,4,33,'2026-06-15','Plastic sheet and inoculant',NULL,NULL,NULL,25000.00,6,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(11,1,1,4,9,'2026-06-16','Compaction and sealing labour',20.000,'day',1000.00,20000.00,4,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(23,1,4,16,27,'2026-06-30','TX0001 – Manure for land prepration (1.5 Acres)',6.000,'trolley',6000.00,36000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(24,1,4,16,2,'2026-08-02','TX0002 – Tractor Diesel',40.010,'litre',394.68,15791.15,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(25,1,4,16,2,'2026-08-02','TX0003 – Tractor Diesel',9.700,'litre',394.68,3828.40,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(26,1,4,16,16,'2026-08-02','TX0004 – Diesel pipe change tractor',1.000,'no',1670.00,1670.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(27,1,4,16,16,'2026-08-02','TX0005 – Tractor genrator bearing',1.000,'no',1500.00,1500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(28,1,4,16,2,'2026-08-03','TX0006 – Tractor diesel',102.970,'litre',388.97,40052.24,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(29,1,4,16,2,'2026-08-05','TX0007 – Tractor diesel',50.000,'litre',387.19,19359.50,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(30,1,4,16,2,'2026-08-06','TX0008 – Tractor diesel',30.020,'litre',386.52,11603.33,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(31,1,4,16,2,'2026-08-07','TX0009 – Tractor diesel',96.010,'litre',386.52,37109.79,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(32,1,4,16,2,'2026-08-07','TX0010 – Tractor diesel',10.000,'litre',386.52,3865.20,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(33,1,4,16,51,'2026-08-12','TX0011 – Labor food',1.000,'lumpsum',140.00,140.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(34,1,4,16,16,'2026-08-13','TX0012 – Ridger repair · Partial record · Machinery repair - lump sum',1.000,'lumpsum',1000.00,1000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(35,1,4,16,51,'2026-08-13','TX0013 – Labor food',1.000,'lumpsum',140.00,140.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(36,1,4,16,2,'2026-08-29','TX0014 – Tractor Diesel (Re-prep) · Re-work · Re-preparation of re-worked land',110.000,'litre',374.09,41149.90,NULL,NULL,NULL,NULL,NULL,0,'complete',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(37,1,4,17,48,'2026-08-10','TX0015 – Seed plantation labor · Partial record · Planting labour - lump sum',1.000,'lumpsum',49000.00,49000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(38,1,4,17,20,'2026-08-12','TX0016 – Seeds procurement (Code 3875) · Partial record · Bag count not itemised; batch code 3875',1.000,'bag',124000.00,124000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(39,1,4,17,20,'2026-08-12','TX0017 – Seeds procurement (Code 4040) · Partial record · Bag count not itemised; batch code 4040',1.000,'bag',60000.00,60000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(40,1,4,17,53,'2026-08-12','TX0018 – Seed transportation charges (from Sudhar)',1.000,'lumpsum',1500.00,1500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(41,1,4,17,48,'2026-09-08','TX0019 – Seed plantation labor (Re-planting) · Re-work · Partial record · Re-planting labour',1.000,'lumpsum',22500.00,22500.00,NULL,NULL,NULL,NULL,NULL,0,'partial',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(42,1,4,17,2,'2026-09-08','TX0020 – Tractor Diesel (Re-panting) · Re-work · Re-planting diesel',60.000,'litre',379.50,22770.00,NULL,NULL,NULL,NULL,NULL,0,'complete',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(43,1,4,17,2,'2026-09-08','TX0021 – Tractor Diesel (Replanting) · Re-work · Re-planting diesel',31.000,'litre',376.13,11660.03,NULL,NULL,NULL,NULL,NULL,0,'complete',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(44,1,4,17,20,'2026-09-16','TX0022 – Seeds procurement · Partial record · Possible re-planting seed - tag if applicable',1.000,'bag',50000.00,50000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(45,1,4,17,20,'2026-09-16','TX0023 – Seeds procurement · Partial record · Possible re-planting seed - tag if applicable',1.000,'bag',5000.00,5000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(46,1,4,18,49,'2026-08-21','TX0024 – Medicine/Pesticide after 24 hour · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',40800.00,40800.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(47,1,4,18,24,'2026-08-21','TX0025 – Babbarsher Urea · Partial record · Unit \"No.s\" - likely bags; brand noted',4.000,'no',16100.00,64400.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(48,1,4,18,49,'2026-08-21','TX0026 – Spray · Partial record · Generic spray - product not named',7.000,'bottle',500.00,3500.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(49,1,4,18,51,'2026-08-21','TX0027 – Labor food',1.000,'lumpsum',560.00,560.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(50,1,4,18,2,'2026-08-21','TX0028 – Tractor Diesel',35.000,'litre',365.71,12799.85,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(51,1,4,18,50,'2026-08-29','TX0029 – Petrol for spray machine',4.000,'litre',360.00,1440.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(52,1,4,18,51,'2026-08-29','TX0030 – Labor food (3 times)',1.000,'lumpsum',1000.00,1000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(53,1,4,18,2,'2026-08-29','TX0031 – Tractor Diesel · Re-work · Re-preparation of re-worked land',110.000,'litre',374.09,41149.90,NULL,NULL,NULL,NULL,NULL,0,'complete',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(54,1,4,18,50,'2026-09-08','TX0032 – Petrol for spray machine (Re-planted) · Re-work · Re-planting spray fuel',2.000,'litre',360.00,720.00,NULL,NULL,NULL,NULL,NULL,0,'complete',1,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(55,1,4,18,50,'2026-09-15','TX0033 – Labor charges for medicine · Pesticide application labour',1.000,'lumpsum',13500.00,13500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(56,1,4,18,53,'2026-09-15','TX0034 – Transportation charges for fertilizer',1.000,'lumpsum',1500.00,1500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(57,1,4,18,49,'2026-09-15','TX0035 – Pesticide for intruders · Partial record · Pest/intruder deterrent - not named',1.000,'lumpsum',200.00,200.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(58,1,4,18,50,'2026-09-15','TX0036 – Petrol for spray machine',2.500,'litre',320.00,800.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(59,1,4,18,49,'2026-09-16','TX0037 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',15200.00,15200.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(60,1,4,18,49,'2026-09-16','TX0038 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',33000.00,33000.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(61,1,4,18,49,'2026-09-16','TX0039 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',7200.00,7200.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(62,1,4,18,49,'2026-09-16','TX0040 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',500.00,500.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(63,1,4,18,49,'2026-09-16','TX0041 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',29250.00,29250.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(64,1,4,18,49,'2026-09-16','TX0042 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',4500.00,4500.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(65,1,4,18,24,'2026-09-16','TX0043 – Urea fertilizer',14.000,'bag',4700.00,65800.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(66,1,4,18,49,'2026-09-18','TX0044 – Medicine/Pesticide (Gangvi) · Partial record · Brand noted; product class not confirmed',3.000,'litre',1400.00,4200.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(67,1,4,18,49,'2026-09-18','TX0045 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',2600.00,2600.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(68,1,4,18,51,'2026-09-18','TX0046 – Labor food',1.000,'lumpsum',100.00,100.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(69,1,4,18,2,'2026-09-21','TX0047 – Tractor Diesel',50.000,'litre',425.46,21273.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(70,1,4,18,24,'2026-09-29','TX0048 – Urea fertilizer',14.000,'bag',4642.86,65000.04,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(71,1,4,18,49,'2026-09-29','TX0049 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',7500.00,7500.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(72,1,4,18,49,'2026-09-29','TX0050 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',1800.00,1800.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(73,1,4,18,49,'2026-09-29','TX0051 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',4200.00,4200.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(74,1,4,18,26,'2026-09-29','TX0052 – Fertilizer (FMC SOP)',3.000,'bag',10500.00,31500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(75,1,4,18,53,'2026-09-29','TX0053 – Transportation charges for pesticides and fertilizers',1.000,'lumpsum',500.00,500.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(76,1,4,18,51,'2026-09-29','TX0054 – Labor food',1.000,'lumpsum',100.00,100.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(77,1,4,18,49,'2026-10-06','TX0055 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',6700.00,6700.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(78,1,4,18,50,'2026-10-06','TX0056 – Petrol for spray machine',1.000,'litre',430.00,430.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(79,1,4,18,2,'2026-10-06','TX0057 – Tractor Diesel',71.370,'litre',397.76,28388.13,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(80,1,4,18,50,'2026-10-07','TX0058 – Petrol for spray machine',1.000,'litre',430.00,430.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(81,1,4,18,51,'2026-10-07','TX0059 – Labor food',1.000,'lumpsum',100.00,100.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(82,1,4,18,51,'2026-10-07','TX0060 – Labor food',1.000,'lumpsum',240.00,240.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(83,1,4,18,49,'2026-10-07','TX0061 – Medicine/Pesticide · Partial record · Product not named',1.000,'bottle',7000.00,7000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(84,1,4,18,27,'2026-10-07','TX0062 – Manure Fetilizer',20.000,'trolley',5000.00,100000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(85,1,4,18,49,'2026-10-16','TX0063 – Medicine/Pesticide · Partial record · Product not named',5.000,'bottle',1400.00,7000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(86,1,4,18,24,'2026-10-16','TX0064 – Urea fertilizer',14.000,'bag',4700.00,65800.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(87,1,4,18,53,'2026-10-16','TX0065 – Transportation charges urea fertilizer',1.000,'lumpsum',800.00,800.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(88,1,4,18,49,'2026-10-17','TX0066 – Medicine/Pesticide · Incomplete record · Product name & quantity not in record',1.000,'lumpsum',8000.00,8000.00,NULL,NULL,NULL,NULL,NULL,0,'incomplete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(89,1,4,18,50,'2026-10-17','TX0067 – Petrol for spray machine',2.000,'litre',430.00,860.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(90,1,4,18,16,'2026-10-17','TX0068 – Tractor repair (2 Pipe with clips)',1.000,'lumpsum',300.00,300.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(91,1,4,18,16,'2026-10-17','TX0069 – Tractor repair (Sulf repair)',1.000,'lumpsum',4400.00,4400.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(92,1,4,18,16,'2026-10-17','TX0070 – Tracktor repair (Water tank)',1.000,'lumpsum',430.00,430.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(93,1,4,18,49,'2026-10-20','TX0071 – Medicine/Pesticide · Partial record · Product not named',5.000,'packet',5000.00,25000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(94,1,4,18,53,'2026-10-20','TX0072 – Transportation medicine/pesticide',1.000,'lumpsum',400.00,400.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(95,1,4,18,49,'2026-10-20','TX0073 – Medicine/Pesticide · Partial record · Product not named',3.000,'packet',1000.00,3000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(96,1,4,18,16,'2026-10-20','TX0074 – Tractor repair nuts and bolts',1.000,'lumpsum',240.00,240.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(97,1,4,18,2,'2026-10-22','TX0075 – Tractor Diesel',60.000,'litre',391.67,23500.20,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(98,1,4,18,49,'2026-10-25','TX0076 – Medicine/Pesticide · Partial record · Product not named',2.000,'packet',1000.00,2000.00,NULL,NULL,NULL,NULL,NULL,0,'partial',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(99,1,4,18,51,'2026-10-25','TX0077 – Lobor food',1.000,'lumpsum',140.00,140.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(100,1,4,19,33,'2026-11-01','TX0078 – Plastic covers for silage · Booked / accrued',2.000,'roll',16000.00,32000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(101,1,4,19,52,'2026-11-03','TX0079 – Silage harvesting and production cost · Booked / accrued; combined harvest+chop+ensile; production ~1 week',9.000,'acre',50000.00,450000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(102,1,4,19,52,'2026-11-04','TX0080 – Silage harvesting and production cost · Booked / accrued; combined harvest+chop+ensile; production ~1 week',5.000,'acre',50000.00,250000.00,NULL,NULL,NULL,NULL,NULL,0,'complete',0,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `farms`
--

DROP TABLE IF EXISTS `farms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `farms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `farms_created_by_foreign` (`created_by`),
  CONSTRAINT `farms_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `farms`
--

LOCK TABLES `farms` WRITE;
/*!40000 ALTER TABLE `farms` DISABLE KEYS */;
INSERT INTO `farms` VALUES
(1,'Hashmi Agriculture Farm','Renala Khurd, District Okara','PKR',NULL,'2026-11-12 04:04:27','2026-11-12 04:04:27');
/*!40000 ALTER TABLE `farms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fields`
--

DROP TABLE IF EXISTS `fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `area_acres` decimal(8,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fields_farm_id_foreign` (`farm_id`),
  KEY `fields_created_by_foreign` (`created_by`),
  CONSTRAINT `fields_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fields_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fields`
--

LOCK TABLES `fields` WRITE;
/*!40000 ALTER TABLE `fields` DISABLE KEYS */;
INSERT INTO `fields` VALUES
(1,1,'North Block A-1',10.00,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(2,1,'North Block A-2',12.00,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(3,1,'Tubewell Square B-1',8.00,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(4,1,'River Bank R-1',15.00,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(5,1,'HAL L01 – Parcel 01',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(6,1,'HAL L02 – Parcel 02',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(7,1,'HAL L03 – Parcel 03',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(8,1,'HAL L04 – Parcel 04',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(9,1,'HAL L05 – Parcel 05',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(10,1,'HAL L06 – Parcel 06',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(11,1,'HAL L07 – Parcel 07',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(12,1,'HAL L08 – Parcel 08',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(13,1,'HAL L09 – Parcel 09',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(14,1,'HAL L10 – Parcel 10',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(15,1,'HAL L11 – Parcel 11',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(16,1,'HAL L12 – Parcel 12',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(17,1,'HAL L13 – Parcel 13',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
(18,1,'HAL L14 – Parcel 14',1.00,1,1,'2026-11-16 07:14:23','2026-11-16 07:14:23');
/*!40000 ALTER TABLE `fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `harvest_records`
--

DROP TABLE IF EXISTS `harvest_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `harvest_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned DEFAULT NULL,
  `harvest_date` date NOT NULL,
  `quantity_kg` decimal(12,3) NOT NULL,
  `method` enum('weighbridge','trolley_count','estimate') NOT NULL,
  `trolley_count` int(11) DEFAULT NULL,
  `avg_trolley_kg` decimal(10,2) DEFAULT NULL,
  `moisture_pct` decimal(5,2) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `harvest_records_field_id_foreign` (`field_id`),
  KEY `harvest_records_created_by_foreign` (`created_by`),
  KEY `harvest_records_season_id_index` (`season_id`),
  CONSTRAINT `harvest_records_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_records_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE SET NULL,
  CONSTRAINT `harvest_records_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `crop_seasons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `harvest_records`
--

LOCK TABLES `harvest_records` WRITE;
/*!40000 ALTER TABLE `harvest_records` DISABLE KEYS */;
INSERT INTO `harvest_records` VALUES
(3,4,NULL,'2026-11-04',280000.000,'estimate',NULL,NULL,NULL,1,'2026-11-16 07:14:24','2026-11-16 07:14:24');
/*!40000 ALTER TABLE `harvest_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_10_23_000001_create_core_tables',1),
(5,'2026_10_23_000002_create_crop_season_tables',1),
(6,'2026_10_23_000003_create_cost_views',1),
(7,'2026_10_24_000001_create_production_tables',1),
(8,'2026_11_23_000001_create_expense_category_stage_table',2),
(9,'2026_11_23_000002_merge_silage_stage_into_harvest',3),
(10,'2026_11_23_000003_add_quality_flags_to_expenses_table',4),
(11,'2026_11_23_000004_rename_stages_to_workbook_names',5),
(12,'2026_11_23_000005_add_bunker_estimator',6),
(13,'2026_11_23_000006_convert_pits_to_bunkers',7);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_allocations`
--

DROP TABLE IF EXISTS `payment_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_allocations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `expense_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_allocations_payment_id_foreign` (`payment_id`),
  KEY `payment_allocations_created_by_foreign` (`created_by`),
  KEY `payment_allocations_expense_id_index` (`expense_id`),
  CONSTRAINT `payment_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_allocations_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_allocations_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_allocations`
--

LOCK TABLES `payment_allocations` WRITE;
/*!40000 ALTER TABLE `payment_allocations` DISABLE KEYS */;
INSERT INTO `payment_allocations` VALUES
(1,1,1,25000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(2,2,2,10000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(3,3,3,5000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(4,4,4,40000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(5,5,5,15000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(6,6,6,20000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(7,7,7,18000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(8,8,8,30000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(9,9,9,12000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(10,10,10,25000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(11,11,11,20000.00,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(17,16,23,36000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(18,17,24,15791.15,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(19,18,25,3828.40,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(20,19,26,1670.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(21,20,27,1500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(22,21,28,40052.24,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(23,22,29,19359.50,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(24,23,30,11603.33,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(25,24,31,37109.79,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(26,25,32,3865.20,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(27,26,37,49000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(28,27,33,140.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(29,28,38,124000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(30,29,39,60000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(31,30,40,1500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(32,31,34,1000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(33,32,35,140.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(34,33,46,40800.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(35,34,47,64400.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(36,35,48,3500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(37,36,49,560.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(38,37,50,12799.85,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(39,38,36,41149.90,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(40,39,51,1440.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(41,40,52,1000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(42,41,53,41149.90,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(43,42,41,22500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(44,43,42,22770.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(45,44,43,11660.03,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(46,45,54,720.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(47,46,55,13500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(48,47,56,1500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(49,48,57,200.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(50,49,58,800.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(51,50,44,50000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(52,51,45,5000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(53,52,59,15200.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(54,53,60,33000.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(55,54,61,7200.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(56,55,62,500.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(57,56,63,29250.00,1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(58,57,64,4500.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(59,58,65,65800.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(60,59,66,4200.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(61,60,67,2600.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(62,61,68,100.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(63,62,69,21273.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(64,63,70,65000.04,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(65,64,71,7500.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(66,65,72,1800.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(67,66,73,4200.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(68,67,74,31500.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(69,68,75,500.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(70,69,76,100.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(71,70,77,6700.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(72,71,78,430.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(73,72,79,28388.13,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(74,73,80,430.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(75,74,81,100.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(76,75,82,240.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(77,76,83,7000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(78,77,84,100000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(79,78,85,7000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(80,79,86,65800.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(81,80,87,800.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(82,81,88,8000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(83,82,89,860.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(84,83,90,300.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(85,84,91,4400.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(86,85,92,430.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(87,86,93,25000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(88,87,94,400.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(89,88,95,3000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(90,89,96,240.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(91,90,97,23500.20,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(92,91,98,2000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(93,92,99,140.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(94,93,101,450000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(95,94,102,250000.00,1,'2026-11-23 04:13:30','2026-11-23 04:13:30');
/*!40000 ALTER TABLE `payment_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('cash','bank','cheque','jazzcash','easypaisa') NOT NULL DEFAULT 'cash',
  `reference` varchar(60) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_vendor_id_foreign` (`vendor_id`),
  KEY `payments_created_by_foreign` (`created_by`),
  KEY `payments_farm_id_payment_date_index` (`farm_id`,`payment_date`),
  CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(1,1,2,'2026-03-09',25000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(2,1,3,'2026-03-10',10000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(3,1,NULL,'2026-03-11',5000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(4,1,1,'2026-03-13',40000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(5,1,6,'2026-03-13',15000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(6,1,5,'2026-05-07',20000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(7,1,6,'2026-04-19',18000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(8,1,3,'2026-06-21',30000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(9,1,NULL,'2026-06-21',12000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(10,1,6,'2026-06-22',25000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(11,1,4,'2026-06-23',20000.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(12,1,6,'2026-09-05',400000.00,'bank','HBL-552190',1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(13,1,2,'2026-08-06',68120.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(14,1,3,'2026-08-08',29700.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(15,1,1,'2026-08-10',116100.00,'cash',NULL,1,'2026-11-12 04:04:30','2026-11-12 04:04:30'),
(16,1,NULL,'2026-06-30',36000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(17,1,NULL,'2026-08-02',15791.15,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(18,1,NULL,'2026-08-02',3828.40,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(19,1,NULL,'2026-08-02',1670.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(20,1,NULL,'2026-08-02',1500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(21,1,NULL,'2026-08-03',40052.24,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(22,1,NULL,'2026-08-05',19359.50,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(23,1,NULL,'2026-08-06',11603.33,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(24,1,NULL,'2026-08-07',37109.79,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(25,1,NULL,'2026-08-07',3865.20,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(26,1,NULL,'2026-08-10',49000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(27,1,NULL,'2026-08-12',140.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(28,1,NULL,'2026-08-12',124000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(29,1,NULL,'2026-08-12',60000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(30,1,NULL,'2026-08-12',1500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(31,1,NULL,'2026-08-13',1000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(32,1,NULL,'2026-08-13',140.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(33,1,NULL,'2026-08-21',40800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(34,1,NULL,'2026-08-21',64400.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(35,1,NULL,'2026-08-21',3500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(36,1,NULL,'2026-08-21',560.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(37,1,NULL,'2026-08-21',12799.85,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(38,1,NULL,'2026-08-29',41149.90,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(39,1,NULL,'2026-08-29',1440.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(40,1,NULL,'2026-08-29',1000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(41,1,NULL,'2026-08-29',41149.90,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(42,1,NULL,'2026-09-08',22500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(43,1,NULL,'2026-09-08',22770.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(44,1,NULL,'2026-09-08',11660.03,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(45,1,NULL,'2026-09-08',720.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(46,1,NULL,'2026-09-15',13500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(47,1,NULL,'2026-09-15',1500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(48,1,NULL,'2026-09-15',200.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(49,1,NULL,'2026-09-15',800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(50,1,NULL,'2026-09-16',50000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(51,1,NULL,'2026-09-16',5000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(52,1,NULL,'2026-09-16',15200.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(53,1,NULL,'2026-09-16',33000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(54,1,NULL,'2026-09-16',7200.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(55,1,NULL,'2026-09-16',500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(56,1,NULL,'2026-09-16',29250.00,'cash','Settled per workbook',1,'2026-11-23 04:13:29','2026-11-23 04:13:29'),
(57,1,NULL,'2026-09-16',4500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(58,1,NULL,'2026-09-16',65800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(59,1,NULL,'2026-09-18',4200.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(60,1,NULL,'2026-09-18',2600.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(61,1,NULL,'2026-09-18',100.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(62,1,NULL,'2026-09-21',21273.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(63,1,NULL,'2026-09-29',65000.04,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(64,1,NULL,'2026-09-29',7500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(65,1,NULL,'2026-09-29',1800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(66,1,NULL,'2026-09-29',4200.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(67,1,NULL,'2026-09-29',31500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(68,1,NULL,'2026-09-29',500.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(69,1,NULL,'2026-09-29',100.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(70,1,NULL,'2026-10-06',6700.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(71,1,NULL,'2026-10-06',430.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(72,1,NULL,'2026-10-06',28388.13,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(73,1,NULL,'2026-10-07',430.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(74,1,NULL,'2026-10-07',100.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(75,1,NULL,'2026-10-07',240.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(76,1,NULL,'2026-10-07',7000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(77,1,NULL,'2026-10-07',100000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(78,1,NULL,'2026-10-16',7000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(79,1,NULL,'2026-10-16',65800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(80,1,NULL,'2026-10-16',800.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(81,1,NULL,'2026-10-17',8000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(82,1,NULL,'2026-10-17',860.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(83,1,NULL,'2026-10-17',300.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(84,1,NULL,'2026-10-17',4400.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(85,1,NULL,'2026-10-17',430.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(86,1,NULL,'2026-10-20',25000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(87,1,NULL,'2026-10-20',400.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(88,1,NULL,'2026-10-20',3000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(89,1,NULL,'2026-10-20',240.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(90,1,NULL,'2026-10-22',23500.20,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(91,1,NULL,'2026-10-25',2000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(92,1,NULL,'2026-10-25',140.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(93,1,NULL,'2026-11-03',450000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30'),
(94,1,NULL,'2026-11-04',250000.00,'cash','Settled per workbook',1,'2026-11-23 04:13:30','2026-11-23 04:13:30');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_rules`
--

DROP TABLE IF EXISTS `recurring_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_stage_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(200) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `frequency` enum('weekly','monthly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `next_run_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recurring_rules_season_stage_id_foreign` (`season_stage_id`),
  KEY `recurring_rules_category_id_foreign` (`category_id`),
  KEY `recurring_rules_vendor_id_foreign` (`vendor_id`),
  KEY `recurring_rules_created_by_foreign` (`created_by`),
  CONSTRAINT `recurring_rules_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `recurring_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recurring_rules_season_stage_id_foreign` FOREIGN KEY (`season_stage_id`) REFERENCES `season_stages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recurring_rules_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_rules`
--

LOCK TABLES `recurring_rules` WRITE;
/*!40000 ALTER TABLE `recurring_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `recurring_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resource_usage`
--

DROP TABLE IF EXISTS `resource_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_usage` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` bigint(20) unsigned NOT NULL,
  `season_stage_id` bigint(20) unsigned NOT NULL,
  `usage_date` date NOT NULL,
  `hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `acres` decimal(8,2) DEFAULT NULL,
  `fuel_litres` decimal(8,2) DEFAULT NULL,
  `operator` varchar(80) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_usage_season_stage_id_foreign` (`season_stage_id`),
  KEY `resource_usage_created_by_foreign` (`created_by`),
  KEY `resource_usage_resource_id_season_stage_id_index` (`resource_id`,`season_stage_id`),
  CONSTRAINT `resource_usage_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resource_usage_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resource_usage_season_stage_id_foreign` FOREIGN KEY (`season_stage_id`) REFERENCES `season_stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resource_usage`
--

LOCK TABLES `resource_usage` WRITE;
/*!40000 ALTER TABLE `resource_usage` DISABLE KEYS */;
/*!40000 ALTER TABLE `resource_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `resources` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('tractor','harvester','chopper','pump','trolley','implement','vehicle') NOT NULL,
  `ownership` enum('owned','rented') NOT NULL DEFAULT 'owned',
  `internal_rate_per_hour` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resources_farm_id_foreign` (`farm_id`),
  KEY `resources_created_by_foreign` (`created_by`),
  CONSTRAINT `resources_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `resources_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources`
--

LOCK TABLES `resources` WRITE;
/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` VALUES
(1,1,'Massey Ferguson 385','tractor','owned',1500.00,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(2,1,'Rented forage chopper','chopper','rented',NULL,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(3,1,'Tubewell pump B-1','pump','owned',NULL,1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29');
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `season_fields`
--

DROP TABLE IF EXISTS `season_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_fields` (
  `season_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `area_acres` decimal(8,2) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`season_id`,`field_id`),
  KEY `season_fields_field_id_foreign` (`field_id`),
  KEY `season_fields_created_by_foreign` (`created_by`),
  CONSTRAINT `season_fields_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `season_fields_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`),
  CONSTRAINT `season_fields_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `crop_seasons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `season_fields`
--

LOCK TABLES `season_fields` WRITE;
/*!40000 ALTER TABLE `season_fields` DISABLE KEYS */;
INSERT INTO `season_fields` VALUES
(1,1,10.00,NULL,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(4,5,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,6,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,7,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,8,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,9,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,10,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,11,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,12,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,13,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,14,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,15,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,16,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,17,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(4,18,1.00,NULL,'2026-11-16 07:14:24','2026-11-16 07:14:24');
/*!40000 ALTER TABLE `season_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `season_stages`
--

DROP TABLE IF EXISTS `season_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` bigint(20) unsigned NOT NULL,
  `stage_template_id` bigint(20) unsigned NOT NULL,
  `status` enum('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `season_stages_season_id_stage_template_id_unique` (`season_id`,`stage_template_id`),
  UNIQUE KEY `season_stages_id_season_id_unique` (`id`,`season_id`),
  KEY `season_stages_stage_template_id_foreign` (`stage_template_id`),
  KEY `season_stages_created_by_foreign` (`created_by`),
  CONSTRAINT `season_stages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `season_stages_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `crop_seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `season_stages_stage_template_id_foreign` FOREIGN KEY (`stage_template_id`) REFERENCES `stage_templates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `season_stages`
--

LOCK TABLES `season_stages` WRITE;
/*!40000 ALTER TABLE `season_stages` DISABLE KEYS */;
INSERT INTO `season_stages` VALUES
(1,1,1,'completed','2026-03-02','2026-03-04',NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(2,1,2,'completed','2026-03-06','2026-03-06',NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(3,1,3,'completed','2026-04-30','2026-04-12',NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(4,1,4,'completed','2026-06-14','2026-06-16',NULL,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(16,4,1,'completed','2026-06-30','2026-08-29','Workbook activities: Land Preparation.',1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(17,4,2,'completed','2026-08-10','2026-09-16','Workbook activities: Sowing & Planting, Seed Procurement & Treatment.',1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(18,4,3,'completed','2026-08-21','2026-10-25','Workbook activities: Weed & Crop Protection, Fertiliser Application, Crop Maintenance & Supervision, Land Preparation.',1,'2026-11-16 07:14:24','2026-11-16 07:14:24'),
(19,4,4,'in_progress','2026-11-01',NULL,'Workbook activities: Silage Preparation & Ensiling.\nWorkbook activities: Silage Preparation & Ensiling.',1,'2026-11-16 07:14:24','2026-11-16 07:14:24');
/*!40000 ALTER TABLE `season_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('6MTwo2vE6qWa7rCNX8AfGMqqULPCpirdmV4ILe08',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ0F3RnM2WXQybDVmTE9pNlNlaWkxdHQ5eG54R3hHSktTM0xFWDAzUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMC9vZmZsaW5lIjtzOjU6InJvdXRlIjtzOjc6Im9mZmxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1795500583),
('GeSpCRTzUl2qPCgYjGD1zoLXaVyNIMgR1jK9ch8v',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTW9XRHBCWkFvdVYycjFyQkswekcwRWhsMmEyWDBZanBlVGhxb0l4SiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1795430300),
('tfh116V7A7GzLwmAuGUP7xmMSiBXcNboVV6N9ofj',1,'127.0.0.1','Symfony','YTo1OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJ0ZWVvWVJFMDJUTWl5ZkU1Um8xQ1pBQXU3WGRPekZ1UnVPbGhZVEJjIjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiM2RhOWIzN2Y0N2MzODg5M2IyYmNkOWU1MmI5M2VmNzdhOGE2YWFjNThjYTE0MDYzYTVmNmFhYTNkMThmMTEwMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHA6Ly9sb2NhbGhvc3QvcHJvZHVjdGlvbj9zZWFzb249Q1MtMjAyNi0wNiI7czo1OiJyb3V0ZSI7czoxNjoicHJvZHVjdGlvbi5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1795429583),
('UHVZUYS86drQnRzup2gUU03KcLXSVjt7m1xeZIYY',1,'127.0.0.1','Symfony','YTo1OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiI3YnRIbWtHTkd2WW1SdkdNMThkV3M2VTF0R2ZpNGF0aEdseEltcXN4IjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiM2RhOWIzN2Y0N2MzODg5M2IyYmNkOWU1MmI5M2VmNzdhOGE2YWFjNThjYTE0MDYzYTVmNmFhYTNkMThmMTEwMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3QvcmVwb3J0cy9zZWFzb25zL0NTLTIwMjYtMDYiO3M6NToicm91dGUiO3M6MTQ6InJlcG9ydHMuc2Vhc29uIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795429117),
('YkdMy5UZu6umSMWl3ZrUGZPDGYruDzwf5wK4tz1e',1,'127.0.0.1','Symfony','YTo1OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJDOTkybTF0QW9TbmcwWWRVU0VHbXVKcmRGNzBzVFl5VmNXZElXMHNGIjtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiM2RhOWIzN2Y0N2MzODg5M2IyYmNkOWU1MmI5M2VmNzdhOGE2YWFjNThjYTE0MDYzYTVmNmFhYTNkMThmMTEwMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDM6Imh0dHA6Ly9sb2NhbGhvc3QvcmVwb3J0cy9zZWFzb25zL0NTLTIwMjYtMDYiO3M6NToicm91dGUiO3M6MTQ6InJlcG9ydHMuc2Vhc29uIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1795427682);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shared_costs`
--

DROP TABLE IF EXISTS `shared_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shared_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `resource_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `cost_date` date NOT NULL,
  `description` varchar(200) NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `basis` enum('hours','acres','equal','manual') NOT NULL DEFAULT 'hours',
  `status` enum('draft','allocated') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shared_costs_farm_id_foreign` (`farm_id`),
  KEY `shared_costs_category_id_foreign` (`category_id`),
  KEY `shared_costs_resource_id_foreign` (`resource_id`),
  KEY `shared_costs_vendor_id_foreign` (`vendor_id`),
  KEY `shared_costs_created_by_foreign` (`created_by`),
  CONSTRAINT `shared_costs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `shared_costs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shared_costs_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shared_costs_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shared_costs_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shared_costs`
--

LOCK TABLES `shared_costs` WRITE;
/*!40000 ALTER TABLE `shared_costs` DISABLE KEYS */;
INSERT INTO `shared_costs` VALUES
(1,1,16,1,3,'2026-10-22','MF-385 hydraulic pump repair',12000.00,'hours','draft',1,'2026-11-12 04:04:30','2026-11-12 04:04:30');
/*!40000 ALTER TABLE `shared_costs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `silage_batches`
--

DROP TABLE IF EXISTS `silage_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `silage_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `season_id` bigint(20) unsigned NOT NULL,
  `storage_unit_id` bigint(20) unsigned NOT NULL,
  `fill_start` date NOT NULL,
  `sealed_date` date DEFAULT NULL,
  `fresh_weight_kg` decimal(12,3) NOT NULL,
  `method` enum('weighbridge','trolley_count','pit_volume') NOT NULL,
  `length_ft` decimal(8,2) DEFAULT NULL,
  `width_ft` decimal(8,2) DEFAULT NULL,
  `height_ft` decimal(8,2) DEFAULT NULL,
  `density_kg_m3` decimal(8,2) DEFAULT NULL,
  `expected_loss_pct` decimal(5,2) NOT NULL DEFAULT 10.00,
  `opened_date` date DEFAULT NULL,
  `cost_per_kg` decimal(10,4) DEFAULT NULL,
  `status` enum('filling','sealed','feeding','finished') NOT NULL DEFAULT 'filling',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `silage_batches_storage_unit_id_foreign` (`storage_unit_id`),
  KEY `silage_batches_created_by_foreign` (`created_by`),
  KEY `silage_batches_season_id_index` (`season_id`),
  CONSTRAINT `silage_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `silage_batches_season_id_foreign` FOREIGN KEY (`season_id`) REFERENCES `crop_seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `silage_batches_storage_unit_id_foreign` FOREIGN KEY (`storage_unit_id`) REFERENCES `storage_units` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `silage_batches`
--

LOCK TABLES `silage_batches` WRITE;
/*!40000 ALTER TABLE `silage_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `silage_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stage_templates`
--

DROP TABLE IF EXISTS `stage_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stage_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(60) NOT NULL,
  `sequence` smallint(6) NOT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stage_templates_code_unique` (`code`),
  KEY `stage_templates_created_by_foreign` (`created_by`),
  CONSTRAINT `stage_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stage_templates`
--

LOCK TABLES `stage_templates` WRITE;
/*!40000 ALTER TABLE `stage_templates` DISABLE KEYS */;
INSERT INTO `stage_templates` VALUES
(1,'LAND_PREP','Crop Land Preparation',1,1,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(2,'SEED_MAT','Crop Planting',2,1,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(3,'GROWING','Crop Care & Maintenance',3,1,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
(4,'HARVEST','Crop Harvesting & Production',4,1,1,NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26');
/*!40000 ALTER TABLE `stage_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storage_units`
--

DROP TABLE IF EXISTS `storage_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `storage_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` enum('pit','bunker','bag','bale_stack') NOT NULL,
  `capacity_tonnes` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `storage_units_farm_id_foreign` (`farm_id`),
  KEY `storage_units_created_by_foreign` (`created_by`),
  CONSTRAINT `storage_units_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `storage_units_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storage_units`
--

LOCK TABLES `storage_units` WRITE;
/*!40000 ALTER TABLE `storage_units` DISABLE KEYS */;
/*!40000 ALTER TABLE `storage_units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `code` varchar(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  `kind` enum('weight','volume','time','area','count','trip') NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `units_created_by_foreign` (`created_by`),
  CONSTRAINT `units_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES
('acre','Acre','area',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('bag','Bag','count',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('bottle','Bottle','count',1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
('day','Day','time',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('hour','Hour','time',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('kg','Kilogram','weight',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('litre','Litre','volume',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('lumpsum','Lump sum','count',1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
('month','Month','time',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('no','Number (No.)','count',1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
('packet','Packet','count',1,'2026-11-16 07:14:23','2026-11-16 07:14:23'),
('roll','Roll','count',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('tonne','Tonne','weight',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('trip','Trip','trip',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('trolley','Trolley','trip',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26'),
('unit','Unit','count',NULL,'2026-11-12 04:04:26','2026-11-12 04:04:26');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` enum('owner','manager','data_entry','viewer') NOT NULL DEFAULT 'data_entry',
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `locale` varchar(5) NOT NULL DEFAULT 'en',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_farm_id_foreign` (`farm_id`),
  KEY `users_created_by_foreign` (`created_by`),
  CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1,'Imran Hashmi','03001234567','owner','$2y$12$oAemefXP8lFzfwgKsUBKtO2dECdEjiL.6p13DIzgbDJ0prrm0taXK',1,'en',NULL,NULL,'2026-11-12 04:04:27','2026-11-12 04:04:27'),
(2,1,'Tariq Mehmood','03011234567','manager','$2y$12$jPzRctpuZXAR57L.P7cosOtNdfiwUInQ9dnnD1SyKbZoYMElFfBB.',1,'en',NULL,1,'2026-11-12 04:04:28','2026-11-12 04:04:28'),
(3,1,'Usman Ghani','03021234567','data_entry','$2y$12$4e0xfUDDsR9vEzrOE5jlTujwCeONgE1BH3AfGo8sgCYQA2bcu379u',1,'en',NULL,1,'2026-11-12 04:04:28','2026-11-12 04:04:28'),
(4,1,'Ayesha Siddiqui','03031234567','viewer','$2y$12$DsnZQaTC.rGz7NRhSiHc3e4dLK8KZDXo7weLoHx0tzx5lMqAyPIA6',1,'en',NULL,1,'2026-11-12 04:04:28','2026-11-12 04:04:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_category_breakdown`
--

DROP TABLE IF EXISTS `v_category_breakdown`;
/*!50001 DROP VIEW IF EXISTS `v_category_breakdown`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_category_breakdown` AS SELECT
 1 AS `season_id`,
  1 AS `category_id`,
  1 AS `parent_category_id`,
  1 AS `cost_type`,
  1 AS `total`,
  1 AS `notional_total`,
  1 AS `line_count` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_expense_status`
--

DROP TABLE IF EXISTS `v_expense_status`;
/*!50001 DROP VIEW IF EXISTS `v_expense_status`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_expense_status` AS SELECT
 1 AS `expense_id`,
  1 AS `farm_id`,
  1 AS `season_id`,
  1 AS `season_stage_id`,
  1 AS `category_id`,
  1 AS `vendor_id`,
  1 AS `expense_date`,
  1 AS `is_notional`,
  1 AS `amount`,
  1 AS `paid`,
  1 AS `balance`,
  1 AS `payment_status` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_season_summary`
--

DROP TABLE IF EXISTS `v_season_summary`;
/*!50001 DROP VIEW IF EXISTS `v_season_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_season_summary` AS SELECT
 1 AS `season_id`,
  1 AS `farm_id`,
  1 AS `total_area_acres`,
  1 AS `total_cost`,
  1 AS `economic_cost`,
  1 AS `paid`,
  1 AS `outstanding`,
  1 AS `cost_per_acre`,
  1 AS `harvested_kg`,
  1 AS `yield_kg_per_acre`,
  1 AS `cost_per_tonne_harvested`,
  1 AS `silage_fresh_kg`,
  1 AS `silage_usable_kg`,
  1 AS `silage_cost_per_kg`,
  1 AS `silage_cost_per_tonne` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_stage_totals`
--

DROP TABLE IF EXISTS `v_stage_totals`;
/*!50001 DROP VIEW IF EXISTS `v_stage_totals`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_stage_totals` AS SELECT
 1 AS `season_stage_id`,
  1 AS `season_id`,
  1 AS `total`,
  1 AS `notional_total`,
  1 AS `paid`,
  1 AS `balance`,
  1 AS `line_count` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `farm_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('supplier','contractor','labour','rental','utility','other') NOT NULL DEFAULT 'supplier',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendors_farm_id_foreign` (`farm_id`),
  KEY `vendors_created_by_foreign` (`created_by`),
  CONSTRAINT `vendors_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_farm_id_foreign` FOREIGN KEY (`farm_id`) REFERENCES `farms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES
(1,1,'Kissan Seed Centre','03214567890','supplier',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(2,1,'PSO Renala Filling Station','0442630011','supplier',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(3,1,'Rana Tractor Services','03334567812','rental',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(4,1,'Ghulam Rasool (labour contractor)','03004561230','labour',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(5,1,'LESCO',NULL,'utility',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29'),
(6,1,'Okara Agri Traders','03124567711','supplier',1,1,'2026-11-12 04:04:29','2026-11-12 04:04:29');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `v_category_breakdown`
--

/*!50001 DROP VIEW IF EXISTS `v_category_breakdown`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_category_breakdown` AS select `v`.`season_id` AS `season_id`,`v`.`category_id` AS `category_id`,`c`.`parent_id` AS `parent_category_id`,`c`.`cost_type` AS `cost_type`,sum(case when `v`.`is_notional` = 0 then `v`.`amount` else 0 end) AS `total`,sum(case when `v`.`is_notional` = 1 then `v`.`amount` else 0 end) AS `notional_total`,count(0) AS `line_count` from (`v_expense_status` `v` join `expense_categories` `c` on(`c`.`id` = `v`.`category_id`)) group by `v`.`season_id`,`v`.`category_id`,`c`.`parent_id`,`c`.`cost_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_expense_status`
--

/*!50001 DROP VIEW IF EXISTS `v_expense_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_expense_status` AS select `e`.`id` AS `expense_id`,`e`.`farm_id` AS `farm_id`,`e`.`season_id` AS `season_id`,`e`.`season_stage_id` AS `season_stage_id`,`e`.`category_id` AS `category_id`,`e`.`vendor_id` AS `vendor_id`,`e`.`expense_date` AS `expense_date`,`e`.`is_notional` AS `is_notional`,`e`.`amount` AS `amount`,coalesce(`pa`.`paid`,0) AS `paid`,case when `e`.`is_notional` = 1 then 0 else `e`.`amount` - coalesce(`pa`.`paid`,0) end AS `balance`,case when `e`.`is_notional` = 1 then 'notional' when coalesce(`pa`.`paid`,0) <= 0 then 'unpaid' when coalesce(`pa`.`paid`,0) < `e`.`amount` then 'partial' else 'paid' end AS `payment_status` from (`expenses` `e` left join (select `payment_allocations`.`expense_id` AS `expense_id`,sum(`payment_allocations`.`amount`) AS `paid` from `payment_allocations` group by `payment_allocations`.`expense_id`) `pa` on(`pa`.`expense_id` = `e`.`id`)) where `e`.`deleted_at` is null */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_season_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_season_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_season_summary` AS select `s`.`id` AS `season_id`,`s`.`farm_id` AS `farm_id`,`s`.`total_area_acres` AS `total_area_acres`,coalesce(`t`.`total`,0) AS `total_cost`,coalesce(`t`.`total`,0) + coalesce(`t`.`notional_total`,0) AS `economic_cost`,coalesce(`t`.`paid`,0) AS `paid`,coalesce(`t`.`balance`,0) AS `outstanding`,case when `s`.`total_area_acres` > 0 then coalesce(`t`.`total`,0) * 1.0 / `s`.`total_area_acres` end AS `cost_per_acre`,coalesce(`h`.`harvested_kg`,0) AS `harvested_kg`,case when `s`.`total_area_acres` > 0 then coalesce(`h`.`harvested_kg`,0) * 1.0 / `s`.`total_area_acres` end AS `yield_kg_per_acre`,case when `h`.`harvested_kg` > 0 then coalesce(`t`.`total`,0) * 1000.0 / `h`.`harvested_kg` end AS `cost_per_tonne_harvested`,coalesce(`b`.`fresh_kg`,0) AS `silage_fresh_kg`,coalesce(`b`.`usable_kg`,0) AS `silage_usable_kg`,case when `b`.`usable_kg` > 0 then coalesce(`t`.`total`,0) * 1.0 / `b`.`usable_kg` end AS `silage_cost_per_kg`,case when `b`.`usable_kg` > 0 then coalesce(`t`.`total`,0) * 1000.0 / `b`.`usable_kg` end AS `silage_cost_per_tonne` from (((`crop_seasons` `s` left join (select `v_stage_totals`.`season_id` AS `season_id`,sum(`v_stage_totals`.`total`) AS `total`,sum(`v_stage_totals`.`notional_total`) AS `notional_total`,sum(`v_stage_totals`.`paid`) AS `paid`,sum(`v_stage_totals`.`balance`) AS `balance` from `v_stage_totals` group by `v_stage_totals`.`season_id`) `t` on(`t`.`season_id` = `s`.`id`)) left join (select `harvest_records`.`season_id` AS `season_id`,sum(`harvest_records`.`quantity_kg`) AS `harvested_kg` from `harvest_records` group by `harvest_records`.`season_id`) `h` on(`h`.`season_id` = `s`.`id`)) left join (select `silage_batches`.`season_id` AS `season_id`,sum(`silage_batches`.`fresh_weight_kg`) AS `fresh_kg`,sum(`silage_batches`.`fresh_weight_kg` * (100 - `silage_batches`.`expected_loss_pct`) / 100.0) AS `usable_kg` from `silage_batches` group by `silage_batches`.`season_id`) `b` on(`b`.`season_id` = `s`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_stage_totals`
--

/*!50001 DROP VIEW IF EXISTS `v_stage_totals`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_stage_totals` AS select `ss`.`id` AS `season_stage_id`,`ss`.`season_id` AS `season_id`,coalesce(sum(case when `v`.`is_notional` = 0 then `v`.`amount` else 0 end),0) AS `total`,coalesce(sum(case when `v`.`is_notional` = 1 then `v`.`amount` else 0 end),0) AS `notional_total`,coalesce(sum(`v`.`paid`),0) AS `paid`,coalesce(sum(`v`.`balance`),0) AS `balance`,count(`v`.`expense_id`) AS `line_count` from (`season_stages` `ss` left join `v_expense_status` `v` on(`v`.`season_stage_id` = `ss`.`id`)) group by `ss`.`id`,`ss`.`season_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 12:24:57
