-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: payment_systemdb
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
  `Name` varchar(19) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Phone_Number` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Link_ID` varchar(8) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`Account_ID`),
  UNIQUE KEY `Phone_Number` (`Phone_Number`),
  UNIQUE KEY `uniq_accounts_linkid` (`Link_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1077 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key_hash` char(64) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=988 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
  `Product` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Amount` decimal(12,2) NOT NULL,
  `status` varchar(19) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('withdraw','deposit','transfer_out','transfer_in') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Created_At` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `initiator_type` enum('System','User') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'System',
  `initiator_id` int DEFAULT NULL,
  `Transaction_ID` char(36) COLLATE utf8mb4_general_ci NOT NULL,
  `Idempotency_Key` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `from_card_id` int DEFAULT NULL,
  `to_card_id` int DEFAULT NULL,
  `transaction_group_id` char(36) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `initiator_reference` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payload_hash` char(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Idempotency_Key` (`Idempotency_Key`),
  UNIQUE KEY `Idempotency_Key_2` (`Idempotency_Key`),
  UNIQUE KEY `uniq_idem_key` (`Idempotency_Key`),
  KEY `fk_txn_card` (`Card_ID`),
  CONSTRAINT `fk_txn_card` FOREIGN KEY (`Card_ID`) REFERENCES `card` (`Card_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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

-- Dump completed on 2026-01-25 14:24:00
