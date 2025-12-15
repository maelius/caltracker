-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 15. Dez 2025 um 22:53
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `caltrack`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `consumptions`
--

CREATE TABLE `consumptions` (
  `id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `servings` decimal(6,2) NOT NULL DEFAULT 1.00,
  `consumed_at` datetime NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `consumptions`
--

INSERT INTO `consumptions` (`id`, `meal_id`, `servings`, `consumed_at`, `notes`, `created_at`) VALUES
(2, 2, 1.00, '2025-12-15 12:00:00', NULL, '2025-12-15 21:39:17'),
(3, 2, 1.00, '2025-12-15 18:00:00', NULL, '2025-12-15 21:39:36');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `calories_per_100g` int(11) NOT NULL CHECK (`calories_per_100g` >= 0),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `foods`
--

INSERT INTO `foods` (`id`, `name`, `image_url`, `description`, `calories_per_100g`, `created_at`) VALUES
(8, 'Rindfleisch (mager)', '/uploads/5a21523b021b457d.jpg', NULL, 170, '2025-12-15 19:53:38'),
(9, 'Lachs', '/uploads/29edcd58a5b12e8e.webp', NULL, 208, '2025-12-15 19:53:53'),
(10, 'Thunfisch (Dose, Wasser)', '/uploads/fb4d797e3f0492de.webp', NULL, 116, '2025-12-15 19:54:32'),
(11, 'Ei', '/uploads/4912415e79f56161.png', NULL, 155, '2025-12-15 19:54:51'),
(12, 'Magerquark', '/uploads/30cfecad31cddc0c.webp', NULL, 67, '2025-12-15 19:55:13'),
(13, 'Skyr', '/uploads/ab25838ed0132285.jpg', NULL, 63, '2025-12-15 19:55:19'),
(14, 'Griechischer Joghurt (10 %)', '/uploads/9e9ac8433d023157.webp', NULL, 120, '2025-12-15 19:55:28'),
(15, 'Tofu (natur)', '/uploads/52a034d9c1aaecab.jpg', NULL, 76, '2025-12-15 19:55:34'),
(16, 'Linsen (gekocht)', '/uploads/e3e51b939445c616.jpg', NULL, 116, '2025-12-15 19:55:41'),
(17, 'Reis (gekocht)', '/uploads/97d7a702658d9351.jpg', NULL, 130, '2025-12-15 19:55:49'),
(18, 'Vollkornnudeln (gekocht)', '/uploads/68b403d376f33b74.jpg', NULL, 124, '2025-12-15 19:55:56'),
(19, 'Kartoffeln (gekocht)', '/uploads/f31bf48f7e641324.jpg', NULL, 77, '2025-12-15 19:56:02'),
(20, 'Haferflocken', '/uploads/732382382a3581ac.jpg', NULL, 389, '2025-12-15 19:56:24'),
(21, 'Vollkornbrot', '/uploads/a287a8d154d524ee.jpg', NULL, 220, '2025-12-15 19:56:44'),
(22, 'Weissbrot', '/uploads/0ef1b4b5ad717541.jpg', NULL, 265, '2025-12-15 19:56:53'),
(23, 'Couscous (gekocht)', '/uploads/07eb077bc4477e52.jpg', NULL, 112, '2025-12-15 19:56:58'),
(24, 'Quinoa (gekocht)', '/uploads/d9945370a2a83e9f.jpg', NULL, 120, '2025-12-15 19:57:06'),
(25, 'Olivenöl', '/uploads/15a5c907afd01771.jpg', NULL, 884, '2025-12-15 19:57:15'),
(26, 'Butter', '/uploads/d2b8638a6a4be83e.webp', NULL, 717, '2025-12-15 19:57:28'),
(27, 'Avocado', '/uploads/993c6c1dcee41d4b.jpg', NULL, 160, '2025-12-15 19:57:35'),
(28, 'Nüsse (gemischt)', '/uploads/01256ef098c6884a.webp', NULL, 600, '2025-12-15 19:57:41'),
(29, 'Erdnussbutter', '/uploads/6a7e9c64be47912a.jpg', NULL, 588, '2025-12-15 19:57:48'),
(30, 'Apfel', '/uploads/d6bd666db2d551b6.jpg', NULL, 52, '2025-12-15 19:57:56'),
(31, 'Banane', '/uploads/d9ae9fa0336f5295.jpg', NULL, 89, '2025-12-15 19:58:02'),
(32, 'Beeren (gemischt)', '/uploads/5b4d4f7c75e2c02f.jpg', NULL, 50, '2025-12-15 19:58:08'),
(33, 'Tomaten', '/uploads/241ef5d674a5a3f4.jpg', NULL, 18, '2025-12-15 19:58:14'),
(34, 'Brokkoli', '/uploads/9be44ece2b9032c1.png', NULL, 34, '2025-12-15 19:58:19'),
(35, 'Karotten', '/uploads/d06bd21aa6a0ce93.jpg', NULL, 41, '2025-12-15 19:58:27'),
(36, 'Schokolade (Milch)', '/uploads/0ace9bc8d1ebc736.jpg', NULL, 535, '2025-12-15 19:58:35'),
(37, 'Hähnchenbrust', '/uploads/9b44f934396bb5e9.jpg', NULL, 165, '2025-12-15 21:36:36');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `meals`
--

CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `meals`
--

INSERT INTO `meals` (`id`, `name`, `description`, `created_at`) VALUES
(2, 'Hähnchen mit Reis & Brokkoli', NULL, '2025-12-15 21:38:58');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `meal_items`
--

CREATE TABLE `meal_items` (
  `id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `quantity_grams` int(11) NOT NULL CHECK (`quantity_grams` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `meal_items`
--

INSERT INTO `meal_items` (`id`, `meal_id`, `food_id`, `quantity_grams`) VALUES
(3, 2, 34, 150),
(4, 2, 37, 180),
(5, 2, 17, 200);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `consumptions`
--
ALTER TABLE `consumptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indizes für die Tabelle `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `meal_items`
--
ALTER TABLE `meal_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_id` (`meal_id`),
  ADD KEY `food_id` (`food_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `consumptions`
--
ALTER TABLE `consumptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT für Tabelle `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `meal_items`
--
ALTER TABLE `meal_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `consumptions`
--
ALTER TABLE `consumptions`
  ADD CONSTRAINT `consumptions_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `meal_items`
--
ALTER TABLE `meal_items`
  ADD CONSTRAINT `meal_items_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meal_items_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `foods` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
