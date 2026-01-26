-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: payment_test
-- ------------------------------------------------------
-- Server version	8.0.44-0ubuntu0.24.04.2

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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `Account_ID` int NOT NULL AUTO_INCREMENT,
  `Name` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Phone_Number` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Link_ID` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`Account_ID`),
  UNIQUE KEY `Phone_Number` (`Phone_Number`),
  UNIQUE KEY `uniq_accounts_linkid` (`Link_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1091 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1077,'Test User','9272106537','user_ebecac26@example.com','930'),(1078,'Test User','9968368696','user_b89a2803@example.com','981'),(1079,'Test User','9844741886','user_ea33d4da@example.com',NULL),(1080,'Test User','9950549791','user_a8852018@example.com','928'),(1081,'Test User','9467923784','user_e692fca7@example.com',NULL),(1082,'Test User','9797980681','user_6a98af7c@example.com',NULL),(1083,'Test User','9419799434','user_5bf3076f@example.com',NULL),(1084,'Test User','9206201333','user_91a50fd8@example.com','913'),(1085,'Test User','9756856400','user_c22aa9ca@example.com',NULL),(1086,'Test User','9147165879','user_a6f2e151@example.com',NULL),(1087,'Test User','9429586405','user_4481a8c1@example.com',NULL),(1088,'Test User','9341206951','user_fd5fc625@example.com','960'),(1089,'Test User','9775343098','user_ad11c9ab@example.com',NULL),(1090,'Test User','9261100609','user_e6a21d7e@example.com',NULL);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_keys`
--

LOCK TABLES `api_keys` WRITE;
/*!40000 ALTER TABLE `api_keys` DISABLE KEYS */;
INSERT INTO `api_keys` VALUES (4,'493b61f0c3132ed0a6d7f42c867a2cfff9e2315d8aa774399eb2e531c5adce33','test-key',1,'2026-01-25 15:38:24',NULL);
/*!40000 ALTER TABLE `api_keys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_rate_limits`
--

DROP TABLE IF EXISTS `api_rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_rate_limits` (
  `api_key_id` int NOT NULL,
  `window_start` datetime NOT NULL,
  `request_count` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`api_key_id`,`window_start`),
  CONSTRAINT `fk_rate_limit_api_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_rate_limits`
--

LOCK TABLES `api_rate_limits` WRITE;
/*!40000 ALTER TABLE `api_rate_limits` DISABLE KEYS */;
INSERT INTO `api_rate_limits` VALUES (4,'2026-01-25 16:39:00',1),(4,'2026-01-25 16:45:00',1),(4,'2026-01-25 17:05:00',1),(4,'2026-01-25 17:08:00',70),(4,'2026-01-25 17:10:00',1),(4,'2026-01-25 17:18:00',78),(4,'2026-01-25 17:20:00',1),(4,'2026-01-25 17:24:00',65),(4,'2026-01-25 17:25:00',41),(4,'2026-01-25 18:09:00',1);
/*!40000 ALTER TABLE `api_rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card`
--

DROP TABLE IF EXISTS `card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `card` (
  `Card_ID` int NOT NULL AUTO_INCREMENT,
  `Account_ID` int DEFAULT NULL,
  `Balance` decimal(12,2) DEFAULT NULL,
  `card_number` varchar(16) DEFAULT NULL,
  `iban` varchar(34) NOT NULL,
  PRIMARY KEY (`Card_ID`),
  UNIQUE KEY `iban` (`iban`),
  UNIQUE KEY `iban_2` (`iban`),
  UNIQUE KEY `card_number` (`card_number`),
  KEY `Account_ID` (`Account_ID`),
  CONSTRAINT `card_ibfk_1` FOREIGN KEY (`Account_ID`) REFERENCES `accounts` (`Account_ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card`
--

LOCK TABLES `card` WRITE;
/*!40000 ALTER TABLE `card` DISABLE KEYS */;
INSERT INTO `card` VALUES (988,1077,93.00,'6397554063608454','FAKEec96cd4efa0411f0'),(989,1078,103.00,'8347811119023534','FAKEa0f0f6d9fa0711f0'),(990,1079,21.00,'3755113526950696','FAKE1b96121bfa0811f0'),(991,1080,100.00,'1422409429608294','FAKE1c0cd761fa0811f0'),(992,1081,20.00,'9689216743865252','FAKE1f6e6c9bfa0811f0'),(993,1082,20.00,'6962171840446229','FAKE200bb3f6fa0811f0'),(994,1083,20.00,'4268969066691556','FAKE7e5b8eadfa0911f0'),(995,1084,100.00,'4815382086499950','FAKE7ecddf41fa0911f0'),(996,1085,20.00,'6772577905815632','FAKE822d896efa0911f0'),(997,1086,20.00,'3950121610378959','FAKE82d1c0fbfa0911f0'),(998,1087,20.00,'3127317024513443','FAKE5ee0abb6fa0a11f0'),(999,1088,100.00,'1635334466244405','FAKE5f52f3dffa0a11f0'),(1000,1089,20.00,'1047610748603577','FAKE62b46d29fa0a11f0'),(1001,1090,20.00,'8363714363764705','FAKE6351bf89fa0a11f0');
/*!40000 ALTER TABLE `card` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `IBAN_GEN` BEFORE INSERT ON `card` FOR EACH ROW BEGIN
    IF NEW.iban IS NULL OR NEW.iban = '' THEN
        SET NEW.iban = CONCAT(
            'FAKE',
            SUBSTRING(
                REPLACE(UUID(), '-', ''),
                1,
                16
            )
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`payuser`@`localhost`*/ /*!50003 TRIGGER `bi_card_gentate_number` BEFORE INSERT ON `card` FOR EACH ROW SET NEW.card_number =
CONCAT(
    FLOOR(1 + RAND() * 9),
    LPAD(FLOOR(RAND() * 1000000000000000), 15, '0')
) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction` (
  `ID` int NOT NULL AUTO_INCREMENT,
  `Card_ID` int NOT NULL,
  `Balance_After` decimal(12,2) NOT NULL,
  `Product` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Amount` decimal(12,2) NOT NULL,
  `status` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('withdraw','deposit','transfer_out','transfer_in') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Created_At` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `initiator_type` enum('System','User') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'System',
  `initiator_id` int DEFAULT NULL,
  `Transaction_ID` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Idempotency_Key` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `from_card_id` int DEFAULT NULL,
  `to_card_id` int DEFAULT NULL,
  `transaction_group_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `initiator_reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payload_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Idempotency_Key` (`Idempotency_Key`),
  UNIQUE KEY `Idempotency_Key_2` (`Idempotency_Key`),
  UNIQUE KEY `uniq_idem_key` (`Idempotency_Key`),
  KEY `fk_txn_card` (`Card_ID`),
  CONSTRAINT `fk_txn_card` FOREIGN KEY (`Card_ID`) REFERENCES `card` (`Card_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
INSERT INTO `transaction` VALUES (187,990,25.00,'TEST deposit',5.00,'success','deposit','2026-01-25 19:18:43','System',NULL,'84866376-fa09-11f0-8e39-080027397b89','deposit-success-5053d761-d024-43dc-812c-c9794f61c9ec',NULL,NULL,NULL,NULL,'c4bd0dff3a4e2114d14b2148a6cce1f54d86cfc50290d836f8986a296c595231'),(188,990,26.00,'Deposit test',1.00,'success','deposit','2026-01-25 19:18:46','System',NULL,'867834a5-fa09-11f0-8e39-080027397b89','deposit-8a6e4991-2fd9-4650-b7f5-ed5725202156',NULL,NULL,NULL,NULL,'17d3ccc697f75aaab6359aee85aef23f59eff3ca9357874b0d62620fb19ff7c1'),(189,990,27.00,'test-Deposit-new-idempotency_key',1.00,'success','deposit','2026-01-25 19:18:47','System',NULL,'87029e3e-fa09-11f0-8e39-080027397b89','2791cad0-cabe-40f5-a63e-7df8d5ade91c',NULL,NULL,NULL,NULL,'376487732449e2dc6edbfe46000b89da832280292334dc664e40e39e697c4518'),(190,990,26.00,'TEST Withdrawal',1.00,'success','withdraw','2026-01-25 19:18:48','System',NULL,'87d90c67-fa09-11f0-8e39-080027397b89','withdrawal-success-19a00c0b-20a0-42f4-b927-6e10e6a9f6d8',NULL,NULL,NULL,NULL,'5c9464a3e716602db0340e914b259200a5ae7bd320b9ac415427d2a1adf91379'),(191,990,16.00,'TEST Withdrawal',10.00,'success','withdraw','2026-01-25 19:18:49','System',NULL,'882eb313-fa09-11f0-8e39-080027397b89','withdraw-card-not-found43edda3c-92ae-4ffd-bd8e-5f252e5b3f8d',NULL,NULL,NULL,NULL,'83dbaefb09b0ec228a86616e354f825bb4dc7d250cba560b8620fa5292a88cba'),(192,988,99.00,NULL,1.00,'success','transfer_out','2026-01-25 19:18:54','User',988,'8b3d0fce-fa09-11f0-8e39-080027397b89','transfer:0c5ec25b22b72261e6c9dff7f671e6fd:out',988,989,'0c5ec25b22b72261e6c9dff7f671e6fd','transfer',NULL),(193,989,101.00,NULL,1.00,'success','transfer_in','2026-01-25 19:18:54','User',988,'8b417876-fa09-11f0-8e39-080027397b89','transfer:0c5ec25b22b72261e6c9dff7f671e6fd:in',988,989,'0c5ec25b22b72261e6c9dff7f671e6fd','transfer',NULL),(194,990,15.00,'TEST Withdrawal',1.00,'success','withdraw','2026-01-25 19:24:15','System',NULL,'4a68890c-fa0a-11f0-8e39-080027397b89','withdrawal-success-6f61493d-455b-4ea9-8e58-634d3a7fe58d',NULL,NULL,NULL,NULL,'5c9464a3e716602db0340e914b259200a5ae7bd320b9ac415427d2a1adf91379'),(195,988,98.00,'ATM Withdrawal',1.00,'success','withdraw','2026-01-25 19:24:18','System',NULL,'4c5a9cf2-fa0a-11f0-8e39-080027397b89','withdrawal-1649c4ab-7300-44ee-84d6-78a21211cd3f',NULL,NULL,NULL,NULL,'3a164183a32038f756abe486ec3fe0f6ab8a7a55e8afcdb0606d9780d1ab8d12'),(196,988,97.00,'test-Withdrawal-new-idempotency_key',1.00,'success','withdraw','2026-01-25 19:24:19','System',NULL,'4ce4ee5b-fa0a-11f0-8e39-080027397b89','test-b7872ae3-54e3-42b7-a732-afff469316be',NULL,NULL,NULL,NULL,'1e44a2cdcb131edfba40cfe7611e6344ede6a82c478e491e1debe5c98216e5f8'),(197,988,96.00,NULL,1.00,'success','transfer_out','2026-01-25 19:24:41','User',988,'59d959a8-fa0a-11f0-8e39-080027397b89','transfer:5c828f1416504169b28947db076a4275:out',988,989,'5c828f1416504169b28947db076a4275','transfer',NULL),(198,989,102.00,NULL,1.00,'success','transfer_in','2026-01-25 19:24:41','User',988,'59de0639-fa0a-11f0-8e39-080027397b89','transfer:5c828f1416504169b28947db076a4275:in',988,989,'5c828f1416504169b28947db076a4275','transfer',NULL),(199,990,20.00,'TEST deposit',5.00,'success','deposit','2026-01-25 19:24:59','System',NULL,'64f7c28f-fa0a-11f0-8e39-080027397b89','deposit-success-a1be63ab-1afe-4d03-bd48-02d1ddb68f5d',NULL,NULL,NULL,NULL,'c4bd0dff3a4e2114d14b2148a6cce1f54d86cfc50290d836f8986a296c595231'),(200,990,21.00,'Deposit test',1.00,'success','deposit','2026-01-25 19:25:03','System',NULL,'66ea1872-fa0a-11f0-8e39-080027397b89','deposit-91660e74-5733-41ea-af1d-b223822faa7a',NULL,NULL,NULL,NULL,'17d3ccc697f75aaab6359aee85aef23f59eff3ca9357874b0d62620fb19ff7c1'),(201,990,22.00,'test-Deposit-new-idempotency_key',1.00,'success','deposit','2026-01-25 19:25:04','System',NULL,'67744969-fa0a-11f0-8e39-080027397b89','bc744dcd-0a64-4dea-b42f-fc3e2b63951e',NULL,NULL,NULL,NULL,'376487732449e2dc6edbfe46000b89da832280292334dc664e40e39e697c4518'),(202,990,21.00,'TEST Withdrawal',1.00,'success','withdraw','2026-01-25 19:25:05','System',NULL,'684b0cbb-fa0a-11f0-8e39-080027397b89','withdrawal-success-8be103ea-9491-4241-9b66-5ead4d83c9fb',NULL,NULL,NULL,NULL,'5c9464a3e716602db0340e914b259200a5ae7bd320b9ac415427d2a1adf91379'),(203,988,95.00,'ATM Withdrawal',1.00,'success','withdraw','2026-01-25 19:25:08','System',NULL,'6a3f9997-fa0a-11f0-8e39-080027397b89','withdrawal-b02448a0-4f4c-4b38-8d02-df0298dac337',NULL,NULL,NULL,NULL,'3a164183a32038f756abe486ec3fe0f6ab8a7a55e8afcdb0606d9780d1ab8d12'),(204,988,94.00,'test-Withdrawal-new-idempotency_key',1.00,'success','withdraw','2026-01-25 19:25:09','System',NULL,'6aca0b1b-fa0a-11f0-8e39-080027397b89','test-919b7e5d-ac6b-4acb-89c5-3cd8b81dc390',NULL,NULL,NULL,NULL,'1e44a2cdcb131edfba40cfe7611e6344ede6a82c478e491e1debe5c98216e5f8'),(205,988,93.00,NULL,1.00,'success','transfer_out','2026-01-25 19:25:11','User',988,'6b97aec6-fa0a-11f0-8e39-080027397b89','transfer:7b898228d3a62225d9c69df3885766ed:out',988,989,'7b898228d3a62225d9c69df3885766ed','transfer',NULL),(206,989,103.00,NULL,1.00,'success','transfer_in','2026-01-25 19:25:11','User',988,'6b9c058d-fa0a-11f0-8e39-080027397b89','transfer:7b898228d3a62225d9c69df3885766ed:in',988,989,'7b898228d3a62225d9c69df3885766ed','transfer',NULL);
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`payuser`@`localhost`*/ /*!50003 TRIGGER `transaction_balance` BEFORE INSERT ON `transaction` FOR EACH ROW BEGIN
    DECLARE current_balance DECIMAL(12,2);

    IF NEW.Transaction_ID IS NULL OR NEW.Transaction_ID = '' THEN
        SET NEW.Transaction_ID = UUID();
    END IF;

    IF NEW.type IN ('transfer_out', 'transfer_in') THEN
        IF NEW.transaction_group_id IS NULL OR NEW.transaction_group_id = '' THEN
            SET NEW.transaction_group_id = UUID();
        END IF;
    END IF;

    IF NEW.type = 'transfer_out' AND NEW.to_card_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'transfer_out must have to_card_id';
    END IF;

    SELECT Balance
    INTO current_balance
    FROM card
    WHERE Card_ID = NEW.Card_ID
    FOR UPDATE;

    SET current_balance = IFNULL(current_balance, 0);
    SET NEW.Amount = IFNULL(NEW.Amount, 0);

    IF NEW.type IN ('withdraw', 'transfer_out') THEN

        IF current_balance < NEW.Amount THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Insufficient funds';

        ELSE
            SET NEW.Balance_After = current_balance - NEW.Amount;
            SET NEW.status = 'success';

            UPDATE card
            SET Balance = NEW.Balance_After
            WHERE Card_ID = NEW.Card_ID;
        END IF;

    ELSEIF NEW.type IN ('deposit', 'transfer_in') THEN

        SET NEW.Balance_After = current_balance + NEW.Amount;
        SET NEW.status = 'success';

        UPDATE card
        SET Balance = NEW.Balance_After
        WHERE Card_ID = NEW.Card_ID;

    ELSE
        SET NEW.Balance_After = current_balance;
        SET NEW.status = 'failed';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-25 17:44:48
