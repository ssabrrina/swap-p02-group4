-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 04:44 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swap_assignment_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `CATEGORY_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`CATEGORY_ID`, `NAME`) VALUES
(1, 'Electronics'),
(2, 'Home Appliances'),
(3, 'Clothing'),
(4, 'Furniture'),
(5, 'Food and beverages '),
(6, 'Beauty'),
(7, 'Toys');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `DEPARTMENT_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`DEPARTMENT_ID`, `NAME`) VALUES
(1, 'HR'),
(2, 'Management');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `ITEM_ID` int(11) NOT NULL,
  `NAME` varchar(50) NOT NULL,
  `SKU` varchar(20) NOT NULL,
  `PRICE` decimal(10,2) NOT NULL,
  `CATEGORY_ID` int(11) NOT NULL,
  `DESCRIPTION` varchar(1000) DEFAULT NULL,
  `QUANTITY` int(11) NOT NULL DEFAULT 0,
  `STOCK` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`ITEM_ID`, `NAME`, `SKU`, `PRICE`, `CATEGORY_ID`, `DESCRIPTION`, `QUANTITY`, `STOCK`) VALUES
(21, 'Paper Clips', 'SKU123', 0.99, 1, 'For everyday organizing', 190, 190),
(22, 'Gaming chair', 'SKU124', 679.00, 4, 'Comfortable and affordable', 290, 290),
(23, 'Tennis Racket', 'SKU125', 300.90, 1, 'For beginners', 12, 12);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ORDER_ID` int(11) NOT NULL,
  `VENDOR_ID` int(11) DEFAULT NULL,
  `PROCUREMENT_ID` int(11) DEFAULT NULL,
  `STATUS` enum('PENDING','COMPLETED','APPROVED','') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_terms`
--

CREATE TABLE `payment_terms` (
  `PAYMENT_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_terms`
--

INSERT INTO `payment_terms` (`PAYMENT_ID`, `NAME`) VALUES
(1, 'PayLah!'),
(2, 'PayNow'),
(3, 'VISA'),
(4, 'MasterCard');

-- --------------------------------------------------------

--
-- Table structure for table `procurement`
--

CREATE TABLE `procurement` (
  `PROCUREMENT_ID` int(11) NOT NULL,
  `ITEM_ID` int(11) DEFAULT NULL,
  `QUANTITY` int(11) DEFAULT NULL,
  `DEPARTMENT_ID` int(11) DEFAULT NULL,
  `PRIORITY_LEVEL` enum('Low','Medium','High','') DEFAULT NULL,
  `STATUS` enum('PENDING','APPROVED','COMPLETED','') DEFAULT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `DATE_REQUESTED` datetime(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement`
--

INSERT INTO `procurement` (`PROCUREMENT_ID`, `ITEM_ID`, `QUANTITY`, `DEPARTMENT_ID`, `PRIORITY_LEVEL`, `STATUS`, `USER_ID`, `DATE_REQUESTED`) VALUES
(1, 1, 3, 1, 'Medium', 'APPROVED', 1, '2025-01-09 22:30:48.000000'),
(3, 1, 3, 2, 'Medium', 'COMPLETED', 6, '2025-01-19 22:30:48.000000'),
(4, 1, 3, 2, 'Medium', 'PENDING', 9, '2025-01-11 22:30:48.000000'),
(5, 3, 2, 2, 'Medium', 'APPROVED', 3, '2025-02-01 21:21:57.000000');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

CREATE TABLE `report` (
  `REPORT_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) DEFAULT NULL,
  `ORDER_HISTORY` datetime(6) DEFAULT NULL,
  `VENDOR_ID` int(11) DEFAULT NULL,
  `PERFORMANCE` varchar(200) DEFAULT NULL,
  `ITEM_ID` int(11) DEFAULT NULL,
  `STOCK` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_type`
--

CREATE TABLE `service_type` (
  `SERVICE_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_type`
--

INSERT INTO `service_type` (`SERVICE_ID`, `NAME`) VALUES
(1, 'Food'),
(2, 'Clothing'),
(3, 'Electronics'),
(4, 'Household');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` int(11) NOT NULL,
  `USERNAME` varchar(20) DEFAULT NULL,
  `PASSWORD` varchar(64) DEFAULT NULL,
  `EMAIL` varchar(100) DEFAULT NULL,
  `ROLE_ID` int(11) DEFAULT NULL,
  `needs_password_reset` tinyint(1) NOT NULL DEFAULT 1,
  `token` varchar(255) DEFAULT NULL,
  `token_expires` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USERNAME`, `PASSWORD`, `EMAIL`, `ROLE_ID`, `needs_password_reset`, `token`, `token_expires`) VALUES
(1, 'Jaimie1', '$2y$10$XwFPmGTzWyFOM6xq9.8ukOcYZAprTejeXBUk1FNlP5xvRg8unnmkW', 'limpeh12345678910@gmail.com', 1, 1, NULL, NULL),
(2, 'Jaimie2', '$2y$10$XwFPmGTzWyFOM6xq9.8ukOcYZAprTejeXBUk1FNlP5xvRg8unnmkW', '2303934J@student.tp.edu.sg', 2, 1, NULL, NULL),
(3, 'Angelica1', '$2y$10$Eyp39tvQfen0krAdYMXDdu5D6mNKLfNMQA.4a/kZouSpy5WHafTU6', '2304293J@student.tp.edu.sg', 1, 0, NULL, NULL),
(4, 'Angelica2', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2304293J@student.tp.edu.sg', 2, 1, NULL, NULL),
(5, 'Angelica3', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2304293J@student.ep.edu.sg', 3, 1, NULL, NULL),
(6, 'Sabrina1', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2302560D@student.ep.edu.sg', 1, 1, NULL, NULL),
(7, 'Sabrina2', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2302560D@student.ep.edu.sg', 2, 1, NULL, NULL),
(8, 'Sabrina3', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2302560D@student.ep.edu.sg', 3, 1, NULL, NULL),
(9, 'Zarah1', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2300166B@student.tp.edu.sg', 1, 1, NULL, NULL),
(10, 'Zarah2', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2300166B@student.tp.edu.sg', 2, 1, NULL, NULL),
(11, 'Zarah3', '$2y$10$Eo9XxfiGrqTt12stwHtvKeKXzNpErmUuqfbjNfj6rZ5oJsjYkqUKW', '2300166B@student.tp.edu.sg', 3, 1, NULL, NULL),
(12, 'Jaimie3', '$2y$10$XwFPmGTzWyFOM6xq9.8ukOcYZAprTejeXBUk1FNlP5xvRg8unnmkW', 'jaimiepehhx@gmail.com', 3, 1, NULL, NULL),
(13, 'Group1', '$2y$10$T2TLPoO7PWV8t49K2Ks2GOd96rYE2n7GFkyfZgCKPG4fGgrvEXyCm', 'jaimiepehhx@gmail.com', 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `ROLE_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`ROLE_ID`, `NAME`) VALUES
(1, 'Admin'),
(2, 'Department Head'),
(3, 'Procurement Officer');

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `VENDOR_ID` int(11) NOT NULL,
  `NAME` varchar(50) DEFAULT NULL,
  `EMAIL` varchar(50) DEFAULT NULL,
  `TELEPHONE_NUMBER` varchar(20) DEFAULT NULL,
  `SERVICE_ID` int(11) DEFAULT NULL,
  `PAYMENT_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendor`
--

INSERT INTO `vendor` (`VENDOR_ID`, `NAME`, `EMAIL`, `TELEPHONE_NUMBER`, `SERVICE_ID`, `PAYMENT_ID`) VALUES
(2, 'Beyond The Vines', 'btv@gmail.com', '44445555', 1, 3),
(4, 'Tefal', 'tefal@gmail.com', '11112222', 4, 2),
(5, 'Hello Panda', 'hellopanda@gmail.com', '97850484', 2, 4),
(32, 'Sanrio', 'sanrio@gmail.com', '98897885', 1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`CATEGORY_ID`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`DEPARTMENT_ID`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`ITEM_ID`),
  ADD UNIQUE KEY `SKU` (`SKU`),
  ADD KEY `CATEGORY_ID` (`CATEGORY_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ORDER_ID`),
  ADD KEY `VENDOR_ID` (`VENDOR_ID`),
  ADD KEY `PROCUREMENT_ID` (`PROCUREMENT_ID`);

--
-- Indexes for table `payment_terms`
--
ALTER TABLE `payment_terms`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `procurement`
--
ALTER TABLE `procurement`
  ADD PRIMARY KEY (`PROCUREMENT_ID`),
  ADD KEY `ITEM_ID` (`ITEM_ID`),
  ADD KEY `DEPARTMENT_ID` (`DEPARTMENT_ID`),
  ADD KEY `user_id` (`USER_ID`);

--
-- Indexes for table `report`
--
ALTER TABLE `report`
  ADD PRIMARY KEY (`REPORT_ID`),
  ADD KEY `VENDOR_ID` (`VENDOR_ID`),
  ADD KEY `ORDER_ID` (`ORDER_ID`),
  ADD KEY `ITEM_ID` (`ITEM_ID`);

--
-- Indexes for table `service_type`
--
ALTER TABLE `service_type`
  ADD PRIMARY KEY (`SERVICE_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `ROLE_ID` (`ROLE_ID`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`ROLE_ID`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`VENDOR_ID`),
  ADD KEY `SERVICE_ID` (`SERVICE_ID`),
  ADD KEY `PAYMENT_ID` (`PAYMENT_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `ITEM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ORDER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `procurement`
--
ALTER TABLE `procurement`
  MODIFY `PROCUREMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `report`
--
ALTER TABLE `report`
  MODIFY `REPORT_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `VENDOR_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `CATEGORY_ID` FOREIGN KEY (`CATEGORY_ID`) REFERENCES `category` (`CATEGORY_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `PROCUREMENT_ID` FOREIGN KEY (`PROCUREMENT_ID`) REFERENCES `procurement` (`PROCUREMENT_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `VENDOR_ID` FOREIGN KEY (`VENDOR_ID`) REFERENCES `vendor` (`VENDOR_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `procurement`
--
ALTER TABLE `procurement`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `ROLE_ID` FOREIGN KEY (`ROLE_ID`) REFERENCES `user_roles` (`ROLE_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vendor`
--
ALTER TABLE `vendor`
  ADD CONSTRAINT `PAYMENT_ID` FOREIGN KEY (`PAYMENT_ID`) REFERENCES `payment_terms` (`PAYMENT_ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SERVICE_ID` FOREIGN KEY (`SERVICE_ID`) REFERENCES `service_type` (`SERVICE_ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
