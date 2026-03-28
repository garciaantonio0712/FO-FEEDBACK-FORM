-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 11:31 AM
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
-- Database: `frontoffice_feedbackforms`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password (use password_hash())',
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `role` enum('superadmin','manager','staff') DEFAULT 'manager',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1 = active, 0 = disabled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `last_login`, `is_active`) VALUES
(2, 'FO', '$2y$10$v4T4pWFVvlRo4yxxK1GYYuq/WHv5hE5rUzDWZHrXfYsvTCqE6pEyi', 'FO', 'FO@example.com', 'manager', '2026-01-17 00:12:50', '2026-02-23 10:19:58', 1),
(5, 'IT', '$2y$10$l8FTa8Ua5n41Xwuto1J0Cu8364U1SFJXetb2anzFY4dCTDSTRCiUW', 'IT', 'IT@support.com', 'superadmin', '2026-02-23 10:26:47', NULL, 1),
(6, 'Staff', '$2y$10$25TncMYyG96dekYTHpDWO.8RuvFEhuEH6RZ3iVj0OQvlpyqFKhz1.', 'Staff', 'staff@gmail.com', 'staff', '2026-02-23 10:27:17', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `guest_feedbacks`
--

CREATE TABLE `guest_feedbacks` (
  `id` int(11) NOT NULL,
  `frontdesk` tinyint(4) DEFAULT NULL COMMENT '1=Poor, 2=Good, 3=Excellent',
  `reservations` tinyint(4) DEFAULT NULL,
  `telephone_operator` tinyint(4) DEFAULT NULL,
  `valet` tinyint(4) DEFAULT NULL,
  `housekeeping` tinyint(4) DEFAULT NULL,
  `accommodation` tinyint(4) DEFAULT NULL,
  `safety` tinyint(4) DEFAULT NULL,
  `security` tinyint(4) DEFAULT NULL,
  `overall_service` tinyint(4) DEFAULT NULL,
  `overall_rating` tinyint(4) NOT NULL,
  `frontdesk_comments` text DEFAULT NULL COMMENT 'Comments & Suggestions - Front of House',
  `food_quality` tinyint(4) DEFAULT NULL,
  `serving_time` tinyint(4) DEFAULT NULL,
  `wait_staff` tinyint(4) DEFAULT NULL,
  `grooming` tinyint(4) DEFAULT NULL,
  `behavior` tinyint(4) DEFAULT NULL,
  `fnb_service` tinyint(4) DEFAULT NULL COMMENT 'Service (Food & Beverage)',
  `bar` tinyint(4) DEFAULT NULL,
  `bartender` tinyint(4) DEFAULT NULL,
  `fnb_comments` text DEFAULT NULL COMMENT 'Comments & Suggestions - Food & Beverage',
  `helpful_staff_names` text DEFAULT NULL COMMENT 'Names of especially helpful staff',
  `suggestions_future` text DEFAULT NULL COMMENT 'Suggestions to make next visit more enjoyable',
  `other_comments` text DEFAULT NULL COMMENT 'Any other general comments or suggestions',
  `guest_name` varchar(120) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `room_no` varchar(20) DEFAULT NULL,
  `date_of_stay` varchar(100) DEFAULT NULL COMMENT 'Flexible format: single date or range',
  `first_stay` enum('Yes','No') DEFAULT NULL,
  `purpose_of_stay` varchar(150) DEFAULT NULL COMMENT 'e.g. Leisure, Business, Event, Wedding...',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Optional - for basic spam tracking'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `guest_feedbacks`
--
ALTER TABLE `guest_feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `guest_feedbacks`
--
ALTER TABLE `guest_feedbacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
