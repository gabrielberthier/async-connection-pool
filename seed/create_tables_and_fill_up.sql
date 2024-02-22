-- MariaDB dump 10.19  Distrib 10.11.6-MariaDB, for Linux (x86_64)
--
-- Host: mysql.hostinger.ro    Database: u574849695_11
-- ------------------------------------------------------
-- Server version	10.11.6-MariaDB-cll-lve

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
-- Table structure for table `authors`
--

DROP TABLE IF EXISTS `authors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  `added` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authors`
--

LOCK TABLES `authors` WRITE;
/*!40000 ALTER TABLE `authors` DISABLE KEYS */;
INSERT INTO `authors` VALUES
(1,'Angel','Volkman','skoelpin@example.com','1987-06-07','2010-09-06 17:11:19'),
(2,'Palma','Bergstrom','keeley82@example.com','1990-08-01','2020-02-25 20:21:18'),
(3,'Noemy','Heathcote','yost.gabriel@example.com','1982-04-24','1985-04-26 17:04:33'),
(4,'Augustine','Metz','willis20@example.com','2000-04-09','1979-03-01 14:19:24'),
(5,'Asia','Lynch','josiane28@example.org','2023-02-24','2006-09-29 16:14:51'),
(6,'Mina','Hansen','wisozk.jovani@example.com','1975-06-05','1992-06-18 03:06:45'),
(7,'Miller','Brakus','hilario23@example.com','2021-07-07','1980-11-13 00:13:53'),
(8,'Wallace','Jaskolski','kuhic.collin@example.org','2001-11-01','2007-08-05 14:39:17'),
(9,'Rocio','Fay','thackett@example.com','1973-10-13','2015-05-15 03:05:25'),
(10,'Megane','Gibson','gleichner.frances@example.net','2021-11-03','1981-05-03 11:40:23'),
(11,'Presley','Kunze','verlie74@example.net','1979-01-16','1987-09-24 21:26:03'),
(12,'Rosalee','Dibbert','angie23@example.org','1986-06-15','2002-02-18 02:22:15'),
(13,'Dulce','Abernathy','shaina.hilpert@example.net','1994-06-04','2014-04-02 21:14:35'),
(14,'Henry','Pagac','kristin.hilll@example.net','1989-07-22','1977-11-30 13:19:59'),
(15,'Maia','Schroeder','rlittle@example.com','1973-12-11','1979-08-08 03:01:05'),
(16,'Nadia','Veum','uhegmann@example.net','2015-07-24','1994-04-13 03:12:29'),
(17,'Jeffrey','Durgan','clare.kilback@example.org','1994-03-31','1973-10-15 12:07:06'),
(18,'Rashawn','Greenfelder','myra44@example.net','1996-12-05','1993-07-31 08:01:44'),
(19,'Peyton','Wiegand','harmon.kling@example.org','1982-05-29','2011-01-09 21:47:07'),
(20,'Germaine','Strosin','eveum@example.org','2006-06-04','2015-02-05 07:38:02'),
(21,'Casey','Rempel','henriette95@example.org','2011-06-04','2003-10-30 11:17:57'),
(22,'Caden','Lemke','arvilla.mitchell@example.com','1994-05-04','1973-01-01 05:11:19'),
(23,'Stephen','Fisher','heidi.swaniawski@example.org','2020-09-13','2010-09-28 23:56:42'),
(24,'Rory','Keeling','frances20@example.net','2012-09-08','1995-12-06 01:11:29'),
(25,'Cedrick','Gusikowski','jasper.gorczany@example.net','1999-01-22','2003-06-18 13:25:00'),
(26,'Anna','Ebert','schneider.flavie@example.com','1994-08-16','2009-07-12 08:43:22'),
(27,'Maximilian','Buckridge','ryan.carmella@example.org','2018-09-06','2024-01-08 06:51:34'),
(28,'Kelly','Mayer','kacey.schuster@example.org','2013-05-17','2001-11-10 13:12:10'),
(29,'Antone','Pacocha','clare.rempel@example.net','2009-04-11','1975-07-27 02:52:23'),
(30,'Emmanuelle','Ratke','littel.vivianne@example.org','1991-06-14','1996-11-12 08:21:12'),
(31,'Davon','O\'Hara','lynch.felipa@example.com','1989-12-29','1988-01-13 00:42:14'),
(32,'Green','Roberts','michaela48@example.net','1971-08-18','2001-05-11 05:41:01'),
(33,'Quinten','Larkin','langosh.thaddeus@example.net','2018-03-08','1972-01-08 07:31:38'),
(34,'Maiya','Lueilwitz','lglover@example.com','2005-02-07','1973-10-19 07:38:23'),
(35,'Brandon','Shanahan','tierra.borer@example.org','2020-08-29','2023-12-02 23:26:08'),
(36,'Alba','Green','jast.missouri@example.org','1977-06-19','1987-07-25 02:00:35'),
(37,'Lelia','Kunze','eldridge27@example.org','2020-11-17','1971-12-25 22:03:17'),
(38,'Verla','Nikolaus','jonathan.will@example.org','1998-12-10','2007-04-08 21:45:57'),
(39,'Vicenta','Bradtke','alanna35@example.net','2002-03-21','2007-11-07 14:37:23'),
(40,'Mara','Hintz','gaylord.lorine@example.com','2004-01-14','2002-07-31 13:12:57'),
(41,'Kadin','Lakin','ritchie.marcos@example.org','2006-06-08','1994-01-15 16:58:45'),
(42,'Kacie','Roob','leonie95@example.net','1990-12-21','1977-06-06 16:36:24'),
(43,'Jamir','Kris','mkuhic@example.org','2019-06-19','1976-06-12 08:12:00'),
(44,'Bruce','Reichert','lelia64@example.com','1998-08-14','1992-06-30 01:39:04'),
(45,'Fletcher','Fahey','muller.aaron@example.org','1992-01-31','2002-03-01 23:45:34'),
(46,'Sallie','Nikolaus','wpouros@example.net','2003-07-21','1982-06-12 09:08:05'),
(47,'Aubree','Cormier','gbogisich@example.net','1999-10-05','2007-02-14 15:23:05'),
(48,'Loma','Bailey','rickey88@example.org','1995-08-18','1984-01-07 10:38:07'),
(49,'General','Dicki','krajcik.alejandra@example.net','2004-06-27','2012-07-10 14:12:36'),
(50,'Grady','Wiegand','hreilly@example.com','1971-06-08','2018-12-08 18:11:22'),
(51,'Krista','Crooks','ncole@example.org','1998-12-03','1971-07-20 01:08:54'),
(52,'Antonina','Lang','pmarvin@example.org','2002-02-19','2022-11-24 20:01:01'),
(53,'Enos','Walter','xwhite@example.net','1994-08-08','2023-08-24 21:50:15'),
(54,'Josianne','Bergnaum','mckenzie.derrick@example.net','2008-10-24','2006-01-11 04:25:58'),
(55,'Nicola','Littel','thaddeus.langosh@example.org','2024-01-20','1993-10-02 13:34:23'),
(56,'Walton','Hegmann','lorna.schowalter@example.com','1970-09-26','2010-09-24 00:46:06'),
(57,'Lottie','Hilll','bnitzsche@example.org','2000-09-12','2017-11-17 07:37:41'),
(58,'Aditya','Dooley','mkeeling@example.net','1976-12-25','1989-03-08 16:07:59'),
(59,'Dax','Skiles','ritchie.wayne@example.net','1998-05-09','1999-07-22 01:39:09'),
(60,'Maggie','Rogahn','shaun.kirlin@example.net','1994-03-17','2003-04-09 10:01:23'),
(61,'Eldred','Padberg','jarvis97@example.org','1983-02-04','1996-08-21 10:27:31'),
(62,'Ocie','McLaughlin','destiney98@example.net','1995-11-11','2013-05-03 13:46:28'),
(63,'Orville','Metz','botsford.cullen@example.com','2016-02-27','2006-01-07 23:41:51'),
(64,'Travon','Nitzsche','willms.llewellyn@example.org','2007-12-12','2009-01-13 15:56:39'),
(65,'Silas','Armstrong','prohaska.hayley@example.com','1970-10-29','2023-05-02 05:19:14'),
(66,'Randall','Kertzmann','spencer.mireille@example.org','1983-07-18','2012-05-01 12:47:55'),
(67,'Ressie','Pfannerstill','conroy.arden@example.com','1974-11-27','2005-03-21 07:46:57'),
(68,'Brisa','Runolfsdottir','gerhard.leuschke@example.net','1986-05-27','1992-07-03 22:23:49'),
(69,'Orie','Medhurst','xwitting@example.com','1970-05-28','1982-10-03 22:56:15'),
(70,'Joanie','Hyatt','ghartmann@example.org','1983-03-05','1975-12-27 07:21:41'),
(71,'Emmet','Adams','stephany.abbott@example.net','1998-08-16','1982-06-15 02:49:00'),
(72,'Anika','Herzog','turner.jorge@example.com','1996-10-23','1984-12-04 16:55:27'),
(73,'Savion','Murphy','schuyler.haag@example.net','2002-09-19','2018-07-16 10:15:29'),
(74,'Jevon','Nader','joy40@example.org','1971-06-03','2021-01-24 14:24:24'),
(75,'Wallace','Fay','noble26@example.org','2017-05-27','1976-04-23 09:26:59'),
(76,'Nicklaus','Morissette','weimann.roger@example.org','2009-12-31','1989-02-27 17:45:44'),
(77,'Damaris','Lehner','angelo68@example.com','1984-04-07','1975-06-22 05:49:22'),
(78,'Arnulfo','Waelchi','lauriane.bruen@example.org','2014-08-01','1983-08-10 08:55:18'),
(79,'Teresa','Dickens','pwilderman@example.net','1984-11-17','1988-08-22 11:08:36'),
(80,'Adrian','Bartoletti','bruen.vicenta@example.org','1993-06-26','1979-04-08 17:53:03'),
(81,'Destiney','Kerluke','schamberger.roosevelt@example.com','1983-07-09','2017-07-26 00:25:05'),
(82,'Theodora','Kreiger','zgibson@example.com','2022-07-16','1992-03-09 15:53:45'),
(83,'Mckenna','Hirthe','emmet93@example.net','1971-10-03','2020-09-10 15:04:34'),
(84,'Tobin','Streich','arlie.volkman@example.net','1972-04-30','2021-11-30 10:19:07'),
(85,'Jon','Prohaska','fmckenzie@example.com','2008-03-18','1973-12-09 20:53:30'),
(86,'Raymond','Predovic','dandre51@example.net','1990-02-23','1991-07-22 03:55:29'),
(87,'Audreanne','Parisian','boyle.fern@example.com','1995-05-25','2008-07-11 19:12:17'),
(88,'Thora','Runte','samanta62@example.net','2022-06-24','1987-12-16 09:02:41'),
(89,'Rosario','Kutch','jeanne60@example.org','1999-09-28','2001-04-01 08:03:54'),
(90,'Guiseppe','Bailey','anne.murray@example.com','2023-04-22','1978-07-15 02:12:04'),
(91,'Rhiannon','Cremin','erich.prohaska@example.org','1983-10-14','2023-04-13 11:28:18'),
(92,'Leonora','Kilback','skyla.waters@example.org','1992-03-26','2007-05-18 08:27:52'),
(93,'Garret','Conn','bromaguera@example.net','1980-07-19','2011-04-04 12:21:08'),
(94,'Letitia','Rogahn','sanford.aaron@example.net','1986-03-21','1971-11-12 07:47:34'),
(95,'Victoria','Paucek','arowe@example.net','1974-05-24','1970-03-24 08:27:14'),
(96,'Bertrand','Glover','roberta25@example.net','2004-08-31','2019-03-29 10:46:37'),
(97,'Chasity','Swift','orin44@example.net','1989-11-02','1993-08-17 09:34:12'),
(98,'Mya','Schulist','cummings.karley@example.com','1972-06-11','2006-12-08 22:17:56'),
(99,'Derek','Osinski','valerie80@example.com','1997-12-10','1996-04-22 04:51:42'),
(100,'Cierra','Greenholt','hturcotte@example.org','1990-10-24','2016-12-22 20:48:15');
/*!40000 ALTER TABLE `authors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-02-21  0:16:00
