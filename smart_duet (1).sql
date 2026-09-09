-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2026 at 07:03 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_duet`
--
CREATE DATABASE IF NOT EXISTS `smart_duet` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_duet`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `full_name`, `email`, `mobile`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'admin', 'admin@gmail.com', '01737267889', '$2y$10$LCzHayRjcMq4fKhjMhwXqu2gP3ygrndMsAFR1zbtx4IF0JkPPMeqS', 'active', '2026-09-06 12:13:17', '2026-09-06 12:13:17');

-- --------------------------------------------------------

--
-- Table structure for table `admit_cards`
--

CREATE TABLE `admit_cards` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `admit_card_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_date` date DEFAULT NULL,
  `exam_shift` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exam_center` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seat_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('not_published','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admit_cards`
--

INSERT INTO `admit_cards` (`id`, `application_id`, `admit_card_no`, `exam_date`, `exam_shift`, `exam_center`, `seat_number`, `pdf_file`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'DUET-2026-000001', '2026-09-29', 'Morning — 10:00 AM - 12:00 PM', 'DUET Main Campus, Gazipur', 'A-101', NULL, 'published', '2026-09-06 12:56:42', '2026-09-06 12:56:42');

-- --------------------------------------------------------

--
-- Table structure for table `admit_card_schedules`
--

CREATE TABLE `admit_card_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `admission_year` year(4) NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `exam_date` date NOT NULL,
  `exam_shift` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exam_center` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admit_card_schedules`
--

INSERT INTO `admit_card_schedules` (`id`, `admission_year`, `department_id`, `exam_date`, `exam_shift`, `exam_center`, `status`, `created_at`, `updated_at`) VALUES
(1, 2026, 10, '2026-09-30', 'Morning — 10:00 AM - 12:00 PM', 'DUET Main Campus, Gazipur', 'published', '2026-09-06 13:24:54', '2026-09-06 13:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `admit_card_settings`
--

CREATE TABLE `admit_card_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `authorization_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admit_card_settings`
--

INSERT INTO `admit_card_settings` (`id`, `authorization_signature`, `updated_at`) VALUES
(1, 'authorization_signature_20260906154756_a38c5b023b.png', '2026-09-06 13:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `father_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mother_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `user_id`, `full_name`, `father_name`, `mother_name`, `date_of_birth`, `gender`, `identity_number`, `created_at`, `updated_at`) VALUES
(1, 1, 'Shibly', 'Rafiqul', 'Shilpy', '2026-09-16', 'Male', '4210325869', '2026-09-06 10:22:01', '2026-09-06 10:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_no` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applicant_id` int(10) UNSIGNED NOT NULL,
  `admission_year` year(4) NOT NULL,
  `ssc_gpa` decimal(3,2) NOT NULL,
  `diploma_cgpa` decimal(3,2) NOT NULL,
  `diploma_passing_year` year(4) NOT NULL,
  `technology_id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `quota` enum('None','Freedom Fighter''s Son/Daughter','Tribal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
  `status` enum('draft','payment_pending','submitted','under_review','accepted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `submitted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `application_no`, `applicant_id`, `admission_year`, `ssc_gpa`, `diploma_cgpa`, `diploma_passing_year`, `technology_id`, `department_id`, `quota`, `status`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 'SDUET-2026-000001', 1, 2026, '5.00', '4.00', 2025, 30, 10, 'None', 'accepted', '2026-09-06 16:57:04', '2026-09-06 10:57:04', '2026-09-06 16:02:52');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `faculty` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'B.Sc. Engineering',
  `duration` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '4 Years',
  `seats` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `code`, `name`, `faculty`, `degree`, `duration`, `seats`, `is_active`, `created_at`) VALUES
(1, 'CE', 'Civil Engineering', 'Faculty of Civil Engineering', 'B.Sc. Engineering', '4 Years', 120, 1, '2026-09-06 10:00:23'),
(2, 'EEE', 'Electrical and Electronic Engineering', 'Faculty of Electrical and Electronic Engineering', 'B.Sc. Engineering', '4 Years', 120, 1, '2026-09-06 10:00:23'),
(3, 'CSE', 'Computer Science and Engineering', 'Faculty of Electrical and Electronic Engineering', 'B.Sc. Engineering', '4 Years', 120, 1, '2026-09-06 10:00:23'),
(4, 'ME', 'Mechanical Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 120, 1, '2026-09-06 10:00:23'),
(5, 'TE', 'Textile Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 120, 1, '2026-09-06 10:00:23'),
(6, 'Arch', 'Architecture', 'Faculty of Civil Engineering', 'Bachelor of Architecture', '5 Years', 30, 1, '2026-09-06 10:00:23'),
(7, 'IPE', 'Industrial and Production Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 30, 1, '2026-09-06 10:00:23'),
(8, 'MME', 'Materials and Metallurgical Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 30, 1, '2026-09-06 10:00:23'),
(9, 'CHE', 'Chemical Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 30, 1, '2026-09-06 10:00:23'),
(10, 'FE', 'Food Engineering', 'Faculty of Mechanical Engineering', 'B.Sc. Engineering', '4 Years', 30, 1, '2026-09-06 10:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `diploma_technologies`
--

CREATE TABLE `diploma_technologies` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diploma_technologies`
--

INSERT INTO `diploma_technologies` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Civil Technology', 1, '2026-09-06 10:00:23'),
(2, 'Surveying Technology', 1, '2026-09-06 10:00:23'),
(3, 'Environmental Technology', 1, '2026-09-06 10:00:23'),
(4, 'Electrical Technology', 1, '2026-09-06 10:00:23'),
(5, 'Electromedical Technology', 1, '2026-09-06 10:00:23'),
(6, 'Electronics Technology', 1, '2026-09-06 10:00:23'),
(7, 'Instrumentation and Process Control Technology', 1, '2026-09-06 10:00:23'),
(8, 'Telecommunication Technology', 1, '2026-09-06 10:00:23'),
(9, 'Mechanical Technology', 1, '2026-09-06 10:00:23'),
(10, 'Power Technology', 1, '2026-09-06 10:00:23'),
(11, 'Refrigeration and Air Conditioning (RAC) Technology', 1, '2026-09-06 10:00:23'),
(12, 'Automobile Technology', 1, '2026-09-06 10:00:23'),
(13, 'Mechatronics Technology', 1, '2026-09-06 10:00:23'),
(14, 'Ceramic Technology', 1, '2026-09-06 10:00:23'),
(15, 'Glass Technology', 1, '2026-09-06 10:00:23'),
(16, 'Ship Building Technology', 1, '2026-09-06 10:00:23'),
(17, 'Marine Technology', 1, '2026-09-06 10:00:23'),
(18, 'Mining and Mine Survey Technology', 1, '2026-09-06 10:00:23'),
(19, 'Computer Science and Technology', 1, '2026-09-06 10:00:23'),
(20, 'Computer Technology', 1, '2026-09-06 10:00:23'),
(21, 'Data Telecommunication and Networking Technology', 1, '2026-09-06 10:00:23'),
(22, 'Graphics Design Technology', 1, '2026-09-06 10:00:23'),
(23, 'Printing Technology', 1, '2026-09-06 10:00:23'),
(24, 'Textile Technology', 1, '2026-09-06 10:00:23'),
(25, 'Jute Technology', 1, '2026-09-06 10:00:23'),
(26, 'Garments and Pattern Making Technology', 1, '2026-09-06 10:00:23'),
(27, 'Architecture Technology', 1, '2026-09-06 10:00:23'),
(28, 'Architecture and Interior Design Technology', 1, '2026-09-06 10:00:23'),
(29, 'Chemical Technology', 1, '2026-09-06 10:00:23'),
(30, 'Food Technology', 1, '2026-09-06 10:00:23'),
(31, 'Agriculture Technology', 1, '2026-09-06 10:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `applicant_photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_document` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quota_document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `application_id`, `applicant_photo`, `signature`, `identity_document`, `quota_document`, `created_at`, `updated_at`) VALUES
(1, 1, 'photo_6a9d4700033961.53135134.png', 'signature_6a9d4700033a35.72934659.png', 'identity_6a9d4700033a49.38374760.png', NULL, '2026-09-06 10:57:04', '2026-09-06 10:57:04');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admission_year` year(4) DEFAULT NULL,
  `published_date` date NOT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `description`, `pdf_file`, `admission_year`, `published_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admission Notice', 'Admission Noitce has been published', 'notice_20260906182842_b03011dee5.pdf', 2026, '2026-09-06', 'published', '2026-09-06 16:28:42', '2026-09-06 16:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `payment_method` enum('Rocket','bKash','Agrani Education Fee Pay') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 1500.00,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `application_id`, `payment_method`, `amount`, `transaction_id`, `payment_status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agrani Education Fee Pay', '1500.00', '535', 'paid', '2026-09-06 18:45:53', '2026-09-06 10:57:04', '2026-09-06 12:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `merit_position` int(10) UNSIGNED DEFAULT NULL,
  `result_status` enum('pending','selected','waiting','not_selected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `published_date` date DEFAULT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `results`
--

INSERT INTO `results` (`id`, `application_id`, `merit_position`, `result_status`, `published_date`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'waiting', '2026-09-06', '', '2026-09-06 16:45:36', '2026-09-06 16:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `technology_department_eligibility`
--

CREATE TABLE `technology_department_eligibility` (
  `id` int(10) UNSIGNED NOT NULL,
  `technology_id` int(10) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `technology_department_eligibility`
--

INSERT INTO `technology_department_eligibility` (`id`, `technology_id`, `department_id`) VALUES
(1, 1, 1),
(3, 2, 1),
(2, 3, 1),
(72, 3, 9),
(4, 4, 2),
(5, 5, 2),
(6, 6, 2),
(29, 6, 3),
(7, 7, 2),
(14, 7, 4),
(42, 7, 7),
(74, 7, 9),
(8, 8, 2),
(16, 9, 4),
(44, 9, 7),
(58, 9, 8),
(76, 9, 9),
(19, 10, 4),
(46, 10, 7),
(60, 10, 8),
(79, 10, 9),
(20, 11, 4),
(47, 11, 7),
(61, 11, 8),
(80, 11, 9),
(86, 11, 10),
(11, 12, 4),
(39, 12, 7),
(54, 12, 8),
(69, 12, 9),
(17, 13, 4),
(45, 13, 7),
(77, 13, 9),
(12, 14, 4),
(55, 14, 8),
(70, 14, 9),
(13, 15, 4),
(57, 15, 8),
(73, 15, 9),
(21, 16, 4),
(48, 16, 7),
(62, 16, 8),
(81, 16, 9),
(15, 17, 4),
(43, 17, 7),
(75, 17, 9),
(18, 18, 4),
(59, 18, 8),
(78, 18, 9),
(26, 19, 3),
(27, 20, 3),
(28, 21, 3),
(30, 22, 3),
(31, 23, 3),
(35, 24, 5),
(34, 25, 5),
(33, 26, 5),
(37, 27, 6),
(36, 28, 6),
(40, 29, 7),
(56, 29, 8),
(71, 29, 9),
(41, 30, 7),
(85, 30, 10),
(84, 31, 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('applicant','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'applicant',
  `status` enum('active','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `mobile`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'shafiq@gmail.com', '01737267889', '$2y$10$TQWrPQN8/fRrdI5upNLQWe5rwX4afsGnUYbzVQr4IqWGgr57a.qYe', 'applicant', 'active', '2026-09-06 10:21:18', '2026-09-06 10:21:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `admit_cards`
--
ALTER TABLE `admit_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`),
  ADD UNIQUE KEY `admit_card_no` (`admit_card_no`);

--
-- Indexes for table `admit_card_schedules`
--
ALTER TABLE `admit_card_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_department_year` (`admission_year`,`department_id`),
  ADD KEY `fk_schedule_department` (`department_id`);

--
-- Indexes for table `admit_card_settings`
--
ALTER TABLE `admit_card_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_applicant_user` (`user_id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_no` (`application_no`),
  ADD KEY `fk_application_technology` (`technology_id`),
  ADD KEY `idx_application_applicant` (`applicant_id`),
  ADD KEY `idx_application_year` (`admission_year`),
  ADD KEY `idx_application_department` (`department_id`),
  ADD KEY `idx_application_status` (`status`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `diploma_technologies`
--
ALTER TABLE `diploma_technologies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notice_year` (`admission_year`),
  ADD KEY `idx_notice_date` (`published_date`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_id` (`application_id`);

--
-- Indexes for table `technology_department_eligibility`
--
ALTER TABLE `technology_department_eligibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_technology_department` (`technology_id`,`department_id`),
  ADD KEY `fk_tde_department` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admit_cards`
--
ALTER TABLE `admit_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admit_card_schedules`
--
ALTER TABLE `admit_card_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admit_card_settings`
--
ALTER TABLE `admit_card_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `diploma_technologies`
--
ALTER TABLE `diploma_technologies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `technology_department_eligibility`
--
ALTER TABLE `technology_department_eligibility`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admit_cards`
--
ALTER TABLE `admit_cards`
  ADD CONSTRAINT `fk_admit_card_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admit_card_schedules`
--
ALTER TABLE `admit_card_schedules`
  ADD CONSTRAINT `fk_schedule_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `fk_applicant_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_application_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_application_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_application_technology` FOREIGN KEY (`technology_id`) REFERENCES `diploma_technologies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_document_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `results`
--
ALTER TABLE `results`
  ADD CONSTRAINT `fk_result_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `technology_department_eligibility`
--
ALTER TABLE `technology_department_eligibility`
  ADD CONSTRAINT `fk_tde_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tde_technology` FOREIGN KEY (`technology_id`) REFERENCES `diploma_technologies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
