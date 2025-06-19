-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Gegenereerd op: 2025-06-19 (Adjust date)
-- Serverversie: 10.6.4-MariaDB-1:10.6.4+maria~focal
-- PHP-versie: 7.4.25

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
-- Tabelstructuur voor tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `user` (`id`, `username`, `password`, `email`) VALUES
(1, 'username', '$2y$10$DQlV0u9mFmtOWsOdxXX9H.4kgzEB3E8o97s.S.Pdy4klUAdBvtVh.', 'username@password.com');


ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `category` (for Brands/Series if desired)
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Gegevens worden geëxporteerd voor tabel `category`
-- (Example categories for Graphic Cards - e.g., Brands or Chipset Families)
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'NVIDIA GeForce RTX'),
(2, 'AMD Radeon RX'),
(3, 'NVIDIA GeForce GTX');

ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `graphic_card`
-- (Renamed from `product` and adjusted fields)

CREATE TABLE `graphic_card` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,       -- e.g., ASUS, MSI, Gigabyte
  `gpu_model` varchar(255) NOT NULL,  -- e.g., RTX 4090, RX 7900 XTX
  `vram_gb` int(11) NOT NULL,         -- e.g., 24, 16, 8
  `price` decimal(10,2) NOT NULL,
  `description` varchar(8000) NOT NULL,
  `image` varchar(255) DEFAULT NULL,  -- Image URL, can be null
  `category_id` int(11) NOT NULL      -- Link to category (e.g., NVIDIA GeForce RTX)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `graphic_card`
-- (Example data for graphic cards)

INSERT INTO `graphic_card` (`id`, `name`, `brand`, `gpu_model`, `vram_gb`, `price`, `description`, `image`, `category_id`) VALUES
(1, 'ASUS ROG Strix GeForce RTX 4090 OC', 'ASUS', 'RTX 4090', 24, '1999.99', 'The ultimate gaming GPU, built with NVIDIA Ada Lovelace architecture. Features 24GB GDDR6X VRAM, advanced cooling, and extreme clock speeds.', 'https://example.com/rtx4090.jpg', 1),
(2, 'MSI Gaming X Trio Radeon RX 7900 XTX', 'MSI', 'RX 7900 XTX', 24, '999.99', 'Experience incredible performance, visuals, and efficiency at 4K and beyond. Powered by AMD RDNA 3 architecture.', 'https://example.com/rx7900xtx.jpg', 2),
(3, 'Gigabyte GeForce RTX 4070 Ti EAGLE OC', 'Gigabyte', 'RTX 4070 Ti', 12, '799.99', 'High-performance card for 1440p gaming, featuring WINDFORCE 3X Cooling System.', 'https://example.com/rtx4070ti.jpg', 1),
(4, 'Sapphire PULSE Radeon RX 7800 XT', 'Sapphire', 'RX 7800 XT', 16, '499.99', 'Excellent value for 1440p gaming with AMD FidelityFX Super Resolution technology.', 'https://example.com/rx7800xt.jpg', 2),
(5, 'ZOTAC Gaming GeForce RTX 3060 Twin Edge OC', 'ZOTAC', 'RTX 3060', 12, '289.99', 'A great card for 1080p and entry-level 1440p gaming. Compact design.', 'https://example.com/rtx3060.jpg', 1),
(6, 'PowerColor Hellhound AMD Radeon RX 6600 XT', 'PowerColor', 'RX 6600 XT', 8, '229.99', 'Reliable 1080p gaming performance with advanced cooling.', 'https://example.com/rx6600xt.jpg', 2),
(7, 'EVGA GeForce GTX 1660 SUPER SC Ultra', 'EVGA', 'GTX 1660 SUPER', 6, '189.99', 'Solid performance for esports and budget 1080p gaming.', 'https://example.com/gtx1660super.jpg', 3);


--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `graphic_card`
--
ALTER TABLE `graphic_card`
  ADD PRIMARY KEY (`id`),
  ADD KEY `graphic_card_category` (`category_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `graphic_card`
--
ALTER TABLE `graphic_card`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `graphic_card`
--
ALTER TABLE `graphic_card`
  ADD CONSTRAINT `graphic_card_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;