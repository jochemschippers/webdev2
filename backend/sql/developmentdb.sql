-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Jun 22, 2025 at 09:50 PM
-- Server version: 11.8.2-MariaDB-ubu2404
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `developmentdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `manufacturer_id`, `created_at`, `updated_at`) VALUES
(1, 'ASUS', 1, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(2, 'MSI', 1, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(3, 'Gigabyte', 1, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(4, 'EVGA', 1, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(5, 'Sapphire', 2, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(6, 'PowerColor', 2, '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(7, 'XFX', 2, '2025-06-19 18:50:05', '2025-06-19 18:50:05');

-- --------------------------------------------------------

--
-- Table structure for table `graphic_cards`
--

CREATE TABLE `graphic_cards` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `gpu_model` varchar(255) NOT NULL,
  `vram_gb` int(11) NOT NULL,
  `interface` varchar(50) NOT NULL,
  `boost_clock_mhz` int(11) DEFAULT NULL,
  `cuda_cores` int(11) DEFAULT NULL,
  `stream_processors` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `graphic_cards`
--

INSERT INTO `graphic_cards` (`id`, `name`, `brand_id`, `gpu_model`, `vram_gb`, `interface`, `boost_clock_mhz`, `cuda_cores`, `stream_processors`, `price`, `stock`, `description`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'ASUS ROG Strix GeForce RTX 4090 OC', 1, 'RTX 4090', 24, 'PCIe 4.0 x16', 2640, 16384, NULL, 1999.99, 61, 'The flagship gaming GPU from ASUS, powered by NVIDIA Ada Lovelace architecture, featuring advanced cooling and extreme performance.', '/images/4090.png', '2025-06-19 18:50:05', '2025-06-22 21:36:37'),
(2, 'MSI Gaming X Trio GeForce RTX 4070 Ti SUPER', 2, 'RTX 4070 Ti SUPER', 16, 'PCIe 4.0 x16', 2670, 8448, NULL, 799.99, 10, 'MSI\'s high-performance RTX 4070 Ti SUPER, ideal for 1440p gaming with excellent cooling and quiet operation.', '/images/4070.png', '2025-06-19 18:50:05', '2025-06-22 13:26:28'),
(3, 'Sapphire PULSE Radeon RX 7900 XT', 5, 'RX 7900 XT', 20, 'PCIe 4.0 x16', 2450, NULL, 5376, 749.99, 8, 'A powerful AMD RDNA 3 GPU from Sapphire, offering incredible performance and stunning visuals for enthusiast gamers.', '/images/7900.png', '2025-06-19 18:50:05', '2025-06-22 13:26:40'),
(4, 'Gigabyte GeForce RTX 3060 GAMING OC', 3, 'RTX 3060', 12, 'PCIe 4.0 x16', 1837, 3584, NULL, 299.99, 64, 'A mid-range NVIDIA Ampere GPU from Gigabyte, perfect for 1080p gaming and content creation.', '/images/3060.png', '2025-06-19 18:50:05', '2025-06-22 21:45:17'),
(5, 'PowerColor Fighter AMD Radeon RX 6600', 6, 'RX 6600', 8, 'PCIe 4.0 x8', 2491, NULL, 1792, 189.99, 15, 'An affordable AMD RDNA 2 GPU from PowerColor, providing solid 1080p gaming performance.', '/images/6600.png', '2025-06-19 18:50:05', '2025-06-22 13:26:56'),
(6, 'XFX Speedster SWFT210 Radeon RX 6700', 7, 'RX 6700', 10, 'PCIe 4.0 x16', 2450, NULL, 2304, 329.99, 12, 'An excellent choice for 1440p gaming, balancing performance and value. From XFX\'s SWFT series.', '/images/6700.png', '2025-06-19 18:50:05', '2025-06-22 13:27:01'),
(7, 'Intel Arc A770 Photon', 3, 'Arc A770', 16, 'PCIe 4.0 x16', 2100, NULL, NULL, 349.99, 20, 'Intel\'s flagship Arc GPU, offering ray tracing and XeSS upscaling for modern gaming experiences.', '/images/a770.png', '2025-06-19 18:50:05', '2025-06-22 21:49:27');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `manufacturers`
--

INSERT INTO `manufacturers` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'NVIDIA', '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(2, 'AMD', '2025-06-19 18:50:05', '2025-06-19 18:50:05'),
(3, 'Intel', '2025-06-19 18:50:05', '2025-06-19 18:50:05');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `order_date`, `updated_at`) VALUES
(107, 19, 299.99, 'pending', '2025-06-22 20:45:41', '2025-06-22 20:45:41'),
(108, 19, 1999.99, 'pending', '2025-06-22 20:47:06', '2025-06-22 20:47:06'),
(109, 19, 299.99, 'pending', '2025-06-22 20:49:15', '2025-06-22 20:49:15'),
(110, 19, 349.99, 'pending', '2025-06-22 20:49:46', '2025-06-22 20:49:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `graphic_card_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `graphic_card_id`, `quantity`, `price_at_purchase`) VALUES
(118, 107, 4, 1, 299.99),
(119, 108, 1, 1, 1999.99),
(120, 109, 4, 1, 299.99),
(121, 110, 7, 1, 349.99);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(8, 'admin', 'admin@gmail.com', '$2y$12$1/bGNmdtVACdz/ouJH7iMOzPuRANrSle5KWReQu3zqTAdR6NB0QGi', 'admin', '2025-06-21 11:46:28', '2025-06-21 11:46:38'),
(19, 'jochemschippers', 'jochemschippers@gmail.com', '$2y$12$KD9pJH6CauMSvFVy7mP6u.wFhPg/TOQxcEk7Oadpq9TCvV12w84kq', 'customer', '2025-06-22 11:30:40', '2025-06-22 11:30:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `manufacturer_id` (`manufacturer_id`);

--
-- Indexes for table `graphic_cards`
--
ALTER TABLE `graphic_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `graphic_card_id` (`graphic_card_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `graphic_cards`
--
ALTER TABLE `graphic_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `graphic_cards`
--
ALTER TABLE `graphic_cards`
  ADD CONSTRAINT `graphic_cards_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`graphic_card_id`) REFERENCES `graphic_cards` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
