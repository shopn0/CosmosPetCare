-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 11:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vet_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `vet_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `pet_id`, `vet_id`, `date`, `status`, `reason`, `diagnosis`, `prescription`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2025-05-05 10:00:00', 'confirmed', 'Annual checkup', NULL, NULL, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(2, 2, 3, '2025-05-06 11:30:00', 'pending', 'Not eating properly', NULL, NULL, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(3, 3, 2, '2025-05-04 14:00:00', 'completed', 'Skin infection', 'Mild skin infection due to allergies', 'Apply Betadine solution twice daily. Cetrizine 5mg once daily for 7 days.', '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(4, 4, 3, '2025-05-07 09:30:00', 'pending', 'Wing injury', NULL, NULL, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(5, 5, 2, '2025-05-08 10:00:00', 'pending', 'Vaccination', NULL, NULL, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(6, 6, 3, '2025-05-09 11:00:00', 'pending', 'Routine checkup', NULL, NULL, '2025-04-29 21:52:26', '2025-04-29 21:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','refunded') DEFAULT 'pending',
  `method` enum('card') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `amount`, `status`, `method`, `transaction_id`, `payment_date`, `updated_at`) VALUES
(1, 1, 1500.00, 'pending', 'card', NULL, '2025-04-30 03:52:26', '2025-04-29 21:52:26'),
(2, 2, 1500.00, 'pending', 'card', NULL, '2025-04-30 03:52:26', '2025-04-29 21:52:26'),
(3, 3, 2200.00, 'completed', 'card', NULL, '2025-04-30 03:52:26', '2025-04-29 21:52:26'),
(4, 4, 1000.00, 'pending', 'card', 'NG123456789', '2025-04-30 03:52:26', '2025-04-29 21:52:26'),
(5, 5, 1200.00, 'pending', 'card', NULL, '2025-04-30 03:52:26', '2025-04-29 21:52:26'),
(6, 6, 1800.00, 'pending', 'card', NULL, '2025-04-30 03:52:26', '2025-04-29 21:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `breed` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','unknown') DEFAULT 'unknown',
  `color` varchar(50) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `name`, `type`, `breed`, `age`, `gender`, `color`, `owner_id`, `created_at`, `updated_at`) VALUES
(1, 'Moti', 'Dog', 'Local', 3, 'male', 'Brown', 4, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(2, 'Bilu', 'Cat', 'Persian', 2, 'female', 'White', 4, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(3, 'Tommy', 'Dog', 'German Shepherd', 4, 'male', 'Black and Tan', 5, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(4, 'Moyna', 'Bird', 'Parrot', 1, 'female', 'Green', 6, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(5, 'Shuvo', 'Dog', 'Labrador', 5, 'male', 'Golden', 7, '2025-04-29 21:52:26', '2025-04-29 21:52:26'),
(6, 'Mimi', 'Cat', 'Bengal', 3, 'female', 'Brown', 8, '2025-04-29 21:52:26', '2025-04-29 21:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer','vet') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Rahim Ahmed', 'admin@vetcare.bd', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'admin', '01712345678', 'House 10, Road 5, Dhanmondi, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(2, 'Dr. Farida Rahman', 'farida@vetcare.bd', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'vet', '01812345678', 'Uttara, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(3, 'Dr. Kamal Hossain', 'kamal@vetcare.bd', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'vet', '01912345678', 'Gulshan, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(4, 'Nasreen Begum', 'nasreen@gmail.com', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'customer', '01612345678', 'Mirpur, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(5, 'Md. Anwar Hossain', 'anwar@gmail.com', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'customer', '01512345678', 'Mohammadpur, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(6, 'Sadia Islam', 'sadia@gmail.com', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'customer', '01412345678', 'Banani, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(7, 'Ayesha Akter', 'ayesha@gmail.com', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'customer', '01312345678', 'Bashundhara, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22'),
(8, 'Tanvir Rahman', 'tanvir@gmail.com', '$2y$10$dlipvGn6hxeVsRk3S2881.ndMigOmBg9UK61SCsWcyKXaqnN3C.My', 'customer', '01212345678', 'Shantinagar, Dhaka', '2025-04-29 21:52:26', '2025-04-29 21:54:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`vet_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
