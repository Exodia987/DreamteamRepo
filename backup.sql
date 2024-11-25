-- MySQL dump 10.13  Distrib 8.0.40, for Linux (x86_64)
--
-- Host: localhost    Database: user_auth
-- ------------------------------------------------------
-- Server version	8.0.40-0ubuntu0.22.04.1

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
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,3,'products/SteelSeries_Rival_3/buyimg_rival3_003.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(2,3,'products/SteelSeries_Rival_3/buyimg_rival3_004.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(3,3,'products/SteelSeries_Rival_3/buyimg_rival3_005.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(4,3,'products/SteelSeries_Rival_3/buyimg_rival3_006.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(5,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_2.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(6,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_3.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(7,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_4.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(8,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_5.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(9,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_6.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(10,5,'products/Arctis_7P__Vezet__kn__lk__li_Feh__r/imgbuy_arctis_7p_white_7.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(11,6,'products/Logitech_G213_Prodigy/1071268_thumb674.jpg'),(12,6,'products/Logitech_G213_Prodigy/1071268-1_thumb674.jpg'),(13,6,'products/Logitech_G213_Prodigy/1071268-2_thumb674.jpg'),(14,6,'products/Logitech_G213_Prodigy/1071268-3_thumb674.jpg'),(15,6,'products/Logitech_G213_Prodigy/1071268-4_thumb674.jpg'),(18,8,'products/SteelSeries_QCK_Mini_Eg__rpad/1200x_buy_qck_s_02.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(19,8,'products/SteelSeries_QCK_Mini_Eg__rpad/1200x_buy_qck_s_03.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(20,9,'products/SteelSeries_QCK_Medium_Eg__rpad/1200x_buy_qck_m_03.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(21,9,'products/SteelSeries_QCK_Medium_Eg__rpad/1200x_buy_qck_s_02.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(22,10,'products/SteelSeries_QCK_Large_Eg__rpad/1200x_buy_qck_l_03.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(23,10,'products/SteelSeries_QCK_Large_Eg__rpad/1200x_buy_qck_s_02.png__1920x1080_crop-fit_optimize_subsampling-2.png'),(32,12,'products/27__Odyssey_G5_G55T_QHD_144_Hz_gaming_monitor_-_LC27G55TQBUXEN/1_SAMSUNG_LC27G55TQBUXEN-i1203207.png'),(33,12,'products/27__Odyssey_G5_G55T_QHD_144_Hz_gaming_monitor_-_LC27G55TQBUXEN/SAMSUNG_LC27G55TQBUXEN-i1203144.png'),(34,12,'products/27__Odyssey_G5_G55T_QHD_144_Hz_gaming_monitor_-_LC27G55TQBUXEN/SAMSUNG_LC27G55TQBUXEN-i1203165.png'),(35,12,'products/27__Odyssey_G5_G55T_QHD_144_Hz_gaming_monitor_-_LC27G55TQBUXEN/SAMSUNG_LC27G55TQBUXEN-i1203172.png'),(36,12,'products/27__Odyssey_G5_G55T_QHD_144_Hz_gaming_monitor_-_LC27G55TQBUXEN/SAMSUNG_LC27G55TQBUXEN-i1203200.png'),(37,13,'products/ASUS_ROG_Strix_G10DK-R5600X156W__90PF02S2-M00MW0__/977032899.asus-rog-strix-g10dk-r5600x156w-90pf02s2-m00mw0.jpg'),(38,14,'products/Sony_PlayStation_5_Slim_Lemezes_V__ltozat__PS5/28954.jpg'),(39,14,'products/Sony_PlayStation_5_Slim_Lemezes_V__ltozat__PS5/28955.jpg'),(40,14,'products/Sony_PlayStation_5_Slim_Lemezes_V__ltozat__PS5/28956.jpg'),(41,15,'products/SONY_PlayStation_5_DualSense_Wireless_Controller_-_feh__r/01575088_004.97ffc10d.jpg'),(42,15,'products/SONY_PlayStation_5_DualSense_Wireless_Controller_-_feh__r/controller.png'),(43,15,'products/SONY_PlayStation_5_DualSense_Wireless_Controller_-_feh__r/HPNG2_AV3.jpg'),(44,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/1.png'),(45,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/35397.png'),(46,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/35398.png'),(47,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/35396.png'),(48,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/35400.png'),(49,16,'products/ACER_PREDATOR_X34VBMIIPHUZX_OLED_FREESYNC_MONITOR_34_/35401.png');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (3,'SteelSeries Rival 3','**Érzékelő SteelSeries TrueMove Core\r\n\r\n**Érzékelő típusa Optikai\r\n\r\n**CPI 200–8 500, 100 CPI-s lépésekben\r\n\r\n**IPS 300, SteelSeries QcK felületeken\r\n\r\n**Gyorsulás 35G\r\n\r\n**Mintavételezési arány (Polling Rate) 1000Hz (1 ms)\r\n\r\n**Hardveres gyorsítás Nincs (Nulla hardveres gyorsítás)',11990.00,'Egerek',NULL,4),(5,'Arctis 7P+ Vezetéknélküli Fehér','**Kifejezetten PlayStation 5-höz tervezve*, de kompatibilis PlayStation 4-gyel, PC-vel, Androiddal, Switchel, USB-C iPadekkel és további eszközökkel.\r\n**A többplatformos USB-C dongle lehetővé teszi az egyszerű váltást a rendszerek között, és alacsony késleltetésű, játékra tervezett 2,4 GHz-es vezeték nélküli kapcsolatot biztosít.\r\n**A legújabb USB-C töltési technológia maximális rugalmasságot kínál, 15 perces gyorstöltéssel 3 órás használathoz.\r\n**A továbbfejlesztett, 30 órás akkumulátor-élettartam még a leghosszabb játékmeneteidet is bírja.\r\n**A Discord által tanúsított ClearCast kétirányú zajszűrő mikrofon.\r\n**Tapasztald meg a lenyűgöző részleteket az összes következő generációs játékban a díjnyertes Arctis hangzással.\r\n\r\n***Teljesen kompatibilis a PlayStation 5 Tempest 3D AudioTech technológiájával.',42490.00,'Fejhallgatók',NULL,10),(6,'Logitech G213 Prodigy','Tartós, és emelt szintű játékteljesítményt nyújt. A tapintható mechanikus dómbillentyű-kapcsolók cseppállók. Testreszabható LIGHTSYNC RGB-megvilágítás. Integrált kéztámasz és állítható lábak. Külön médiavezérlők.\r\n**Méretek\r\nMagasság: 33 mm\r\nSzélesség: 452 mm\r\nMélység: 218 mm\r\nTömeg: 1000 g\r\nA csatlakozás típusa: USB 2.0\r\nUSB protokoll: USB 2.0\r\nUSB-sebesség: Teljes sebesség\r\nJelzőfények (LED): Igen\r\nHáttérvilágítás: RGB (5 zóna)\r\nKábelhossz (tápellátás / töltés): 1,8 m\r\n**Cseppállóság\r\n60 ml-rel tesztelve l Cseppállóság',19990.00,'Billentyűzetek',NULL,5),(8,'SteelSeries QCK Mini Egérpad','**Exkluzív QcK mikroszövött anyag a maximális irányítás érdekében.\r\n**Alacsony és magas CPI mozgásokhoz optimalizálva.\r\n**Tartós és mosható, egyszerű tisztítás céljából.\r\n**Több mint 15 éve az e-sport profik első számú választása.\r\n**Méret: 250 mm x 210 mm x 2 mm',3090.00,'Egérpadok',NULL,16),(9,'SteelSeries QCK Medium Egérpad','**Exkluzív QcK mikroszövött anyag a maximális irányítás érdekében.\r\n**Alacsony és magas CPI mozgásokhoz optimalizálva.\r\n**Tartós és mosható, egyszerű tisztítás céljából.\r\n**Több mint 15 éve az e-sport profik első számú választása.\r\n**Méret: 320 mm x 270 mm x 2 mm.',3790.00,'Egérpadok',NULL,15),(10,'SteelSeries QCK Large Egérpad','**Exkluzív QcK mikroszövött anyag a maximális irányítás érdekében.\r\n**Alacsony és magas CPI mozgásokhoz optimalizálva.\r\n**Tartós és mosható, egyszerű tisztítás céljából.\r\n**Több mint 15 éve az e-sport profik első számú választása.\r\n**Méret: 450 mm x 400 mm x 2 mm',8490.00,'Egérpadok',NULL,16),(12,'27\" Odyssey G5 G55T QHD 144 Hz gaming monitor - LC27G55TQBUXEN','##Adatok\r\n**Az 1000R ív illeszkedik az emberi szem körvonalaihoz, és elképzelhetetlen valósághűséget biztosít.\r\n**A WQHD felbontás rendkívül széles játékteret kínál élethű részletekkel.\r\n**A 144 Hz-es frissítési frekvencia kiküszöböli a laggolást a zökkenőmentes játék érdekében.\r\n##Specifikációk\r\n**Felbontás: QHD (2,560 x 1,440)\r\n**Képarány: 16:9\r\n**Ívelt képernyő: 1000R\r\n**Fényerő (tipikus): 300cd/㎡\r\n**Statikus kontraszt-arány: 2,500:1(Jell.)\r\n**Reakcióidő (mp): 1 (MPRT)',114990.00,'Monitorok',NULL,2),(13,'ASUS ROG Strix G10DK-R5600X156W (90PF02S2-M00MW0) ','**AMD Ryzen 5 5600X Processzor\r\n**6 mag 12 szál\r\n**3.7 GHz alap órajel 32MB L3 Cache\r\n**16GB DDR4 RAM\r\n**512GB M. 2 SSD\r\n**NVIDIA® GeForce RTX 3060 videokártya\r\n**Gigabit Ethernet\r\n**802.11ax WiFi + Bluetooth 5\r\n**2 HDMI 1 DisplayPort kimenet\r\n**Összesen 8 USB 3.1 Gen 1\r\n**7.1 Audio\r\n**500W 80 PLUS Gold tápegység',299000.00,'Gamer PC-k',NULL,1),(14,'Sony PlayStation 5 Slim Lemezes Változat (PS5','##Processzor: \r\n**8 magos @ 3.5 GHz egyedi Zen 2\r\n##Videokártya: \r\n**10.3 TFLOPS, 36 CU @ 2.23 GHz egyedi RDNA 2\r\n##Videó átvitel: \r\n**4K @ 120Hz\r\n**8K\r\n**VRR\r\n##Memória:\r\n**GDDR6 16GB (448GB/s)\r\n##Belső tárhely:\r\n**1 TB egyedi tervezésű SSD (5.5 GB/s (Raw), 8-9 GB/s (tömörítve))\r\n##Optikai meghajtó:\r\n**4K UHD Blu-Ray\r\n##Csatlakozás\r\n**1 x Ethernet\r\n**Wi-Fi (IEEE 802.11 a/b/g/n/ac/ax)\r\n**Bluetooth 5.1\r\n**3,5 mm Jack a kontrolleren\r\n**1 x USB-C\r\n**2 x USB-A\r\n**1 x HDMI 2.1\r\n##Méretek és súly:\r\n**358 x 80 x 216 mm / 2,6 kg\r\n##Csomag tartalma:\r\n**PlayStation 5 játékkonzol 1TB tárhellyel\r\n**1 db PlayStation 5 DualSense kontroller fekete-fehér színkombinációban\r\n**2 db vízszintes állvány\r\n**HDMI kábel\r\n**Tápkábel\r\n**USB kábel\r\n**Használati útmutató\r\n**ASTRO’s PLAYROOM (Előtelepítve)',209990.00,'Konzolok',NULL,6),(15,'SONY PlayStation 5 DualSense Wireless Controller - fehér','##Főbb jellemzők\r\n**Terméktípus	Vezeték: nélküli vezérlő\r\n**Kompatibilitás:	Playstation 5\r\n**Technológia:	Vezeték nélküli DualSense\r\n**Interfész: Jack 3.5 mm USB Type C\r\n**Tápellátás: Li-Ion\r\n**Elem/Akkumulátor típusa: Újratölthető Li-Ion, 1,560 mAh\r\n**Mikrofon: Beépített\r\n##Tulajdonságok & funkciók\r\n**Vibráció: Igen\r\n**Csatlakozók: Bluetooth 5.1\r\n##Technikai jellemzők\r\n**Szín: Fehér/Fekete\r\n**Méretek (mm): W x H x D: 160 x 66 x 106 mm\r\n**Súly: 280 g',24990.00,'Kontrollerek',NULL,7),(16,'ACER PREDATOR X34VBMIIPHUZX OLED FREESYNC MONITOR 34\"','**Termék család: Predator X\r\n**Kijelző mérete: 34\"\r\n**Kijelző technológia: OLED 1800R hajlított\r\n**Felbontás: 3440 x 1440\r\n**Képarány: 21:9\r\n**Képfrissítés:\r\n- HDMI: 100Hz\r\n- Displayport/Type-C: 175Hz\r\n**Válaszidő: 0,1 ms (G2G)\r\n**Kontrasztarány: 1 500 000:1 statikus kontraszt\r\n**Fényerő: 250nits / 1000nits Peak\r\n**Betekintési szög: 178/178°\r\n**Színek: 1.07 Billió (10Bit), 99% DCI-P3',299990.00,'Monitorok',NULL,7);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(64) DEFAULT NULL,
  `token_expiry` bigint DEFAULT NULL,
  `is_verified` int DEFAULT NULL,
  `verification_token` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (11,'shador','messihpps8@gmail.com','$2y$10$wREGWyZtaVxa6cDQ4si31ephnf98bdbOmZjwemgXle.HB9DDrTrPS','2024-11-17 10:01:11','1befb473914b23099cd746a16f443f6841339733b62cab7fcc304a89e3ead362',NULL,1,'8ecb839ee5497f0e02dcbefaa88e0cbe'),(12,'szar','shador1337@gmail.com','$2y$10$LezcXNElvt5k2uBiLeds/ujmjwRmk9g2tmZCi8OSXsNzczZ9mruFK','2024-11-20 20:09:39',NULL,NULL,1,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-25 23:20:08
