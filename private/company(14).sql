-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Vært: mariadb
-- Genereringstid: 19. 11 2025 kl. 19:54:47
-- Serverversion: 10.6.20-MariaDB-ubu2004
-- PHP-version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `company`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `follows`
--

CREATE TABLE `follows` (
  `follower_user_fk` char(50) NOT NULL,
  `follow_user_fk` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `follows`
--

INSERT INTO `follows` (`follower_user_fk`, `follow_user_fk`) VALUES
('1', '4feb86ecb46c473e0f6c01e97bc26086e7953b5f621c08b5a1'),
('1', '5afc9c4fe287446fa12cad78655727d2'),
('1a3add65aab9406698beb1de16227e12', '1'),
('1a3add65aab9406698beb1de16227e12', '4feb86ecb46c473e0f6c01e97bc26086e7953b5f621c08b5a1'),
('1a3add65aab9406698beb1de16227e12', '5afc9c4fe287446fa12cad78655727d2');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `likes`
--

CREATE TABLE `likes` (
  `like_user_fk` char(50) NOT NULL,
  `like_post_fk` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `likes`
--

INSERT INTO `likes` (`like_user_fk`, `like_post_fk`) VALUES
('1', '03db19c699667926a214eb06280ca360cc5015e610ee583195'),
('1a3add65aab9406698beb1de16227e12', '03db19c699667926a214eb06280ca360cc5015e610ee583195'),
('1a3add65aab9406698beb1de16227e12', '42098f86770b40120733b6e76f73af6b84f1ea4ecc04517c98'),
('5afc9c4fe287446fa12cad78655727d2', '03db19c699667926a214eb06280ca360cc5015e610ee583195');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `people`
--

CREATE TABLE `people` (
  `person_pk` bigint(20) UNSIGNED NOT NULL,
  `person_username` varchar(20) NOT NULL,
  `person_first_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `people`
--

INSERT INTO `people` (`person_pk`, `person_username`, `person_first_name`) VALUES
(1, 'andreahauberg', 'Andrea'),
(5, '', '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `person`
--

CREATE TABLE `person` (
  `person_pk` bigint(20) UNSIGNED NOT NULL,
  `person_username` varchar(20) NOT NULL,
  `person_first_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `person`
--

INSERT INTO `person` (`person_pk`, `person_username`, `person_first_name`) VALUES
(1, 'andreahauberg', 'Andrea'),
(5, '', '');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `posts`
--

CREATE TABLE `posts` (
  `post_pk` char(50) NOT NULL,
  `post_message` varchar(200) NOT NULL,
  `post_image_path` varchar(100) NOT NULL,
  `post_user_fk` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `posts`
--

INSERT INTO `posts` (`post_pk`, `post_message`, `post_image_path`, `post_user_fk`) VALUES
('03db19c699667926a214eb06280ca360cc5015e610ee583195', 'dsffdsdsfsdfdsfsdfdsf', 'https://picsum.photos/400/250', '1'),
('1', 'Post one', 'https://picsum.photos/400/250', '1'),
('2', '<script>alert()</stript>', 'https://picsum.photos/400/250', '1'),
('42098f86770b40120733b6e76f73af6b84f1ea4ecc04517c98', 'Hej med jer her er mit første post', 'https://picsum.photos/400/250', '1a3add65aab9406698beb1de16227e12'),
('a3c0a09879aa6209bdb58574dd18a8954b0f2d1f56f06bb813', 'new post', 'https://picsum.photos/400/250', '1'),
('a7b0b388995525ccaeb6674f2752bedc02e13e682db2a71631', 'assdasadsdadsa', 'https://picsum.photos/400/250', '5afc9c4fe287446fa12cad78655727d2'),
('db517c70eae4766a085fe030638734d286203fc9bbc85becd5', 'fdsdsfdsfdfsdfs', 'https://picsum.photos/400/250', '1'),
('ea9a680f2ca31d68950d90b96fb30839574031664cd62a7a76', 'dsfsdfkkgfdlkjfgdsjklsfdgjksgflk', 'https://picsum.photos/400/250', '1');

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `users`
--

CREATE TABLE `users` (
  `user_pk` char(50) NOT NULL,
  `user_username` varchar(20) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_full_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data dump for tabellen `users`
--

INSERT INTO `users` (`user_pk`, `user_username`, `user_email`, `user_password`, `user_full_name`) VALUES
('1', 'andreahauberg', 'a@a.com', '$2y$10$FFupw1g6iLrsAG1rraCqjeS38je8PgdOxml9YVQdV8ln/hSgkm.4.', 'Andrea Hauberg'),
('5afc9c4fe287446fa12cad78655727d2', 'user1', 'b@b.com', '$2y$10$GheYC/X6jez8yeWkVpqgKuJ5q/miOZcAdWBB858OFojdL0JLZzyba', 'Bobo Bob'),
('1a3add65aab9406698beb1de16227e12', 'user2', 'c@c.com', '$2y$10$5Rt6kJtegTIh3mAfJk059OgsLIdLES8n/ldWgvRegunURAUCK.T0m', 'Clem Clemsen'),
('4feb86ecb46c473e0f6c01e97bc26086e7953b5f621c08b5a1', 'user4', 'j@j.com', '$2y$10$YRUnPlxARN5G7em/xySvRuJV/hhjpATtSGMFbQ.zUsdeWJhmKaPrm', 'Jean Jean');

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`follower_user_fk`,`follow_user_fk`),
  ADD KEY `follow_user_fk` (`follow_user_fk`);

--
-- Indeks for tabel `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_user_fk`,`like_post_fk`),
  ADD KEY `like_post_fk` (`like_post_fk`);

--
-- Indeks for tabel `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`person_pk`),
  ADD UNIQUE KEY `person_pk` (`person_pk`),
  ADD UNIQUE KEY `person_username` (`person_username`);

--
-- Indeks for tabel `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_pk`),
  ADD UNIQUE KEY `person_pk` (`person_pk`),
  ADD UNIQUE KEY `person_username` (`person_username`);

--
-- Indeks for tabel `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_pk`);

--
-- Indeks for tabel `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD UNIQUE KEY `user_pk` (`user_pk`);

--
-- Brug ikke AUTO_INCREMENT for slettede tabeller
--

--
-- Tilføj AUTO_INCREMENT i tabel `people`
--
ALTER TABLE `people`
  MODIFY `person_pk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tilføj AUTO_INCREMENT i tabel `person`
--
ALTER TABLE `person`
  MODIFY `person_pk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_user_fk`) REFERENCES `users` (`user_pk`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`follow_user_fk`) REFERENCES `users` (`user_pk`) ON DELETE CASCADE;

--
-- Begrænsninger for tabel `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`like_post_fk`) REFERENCES `posts` (`post_pk`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`like_user_fk`) REFERENCES `users` (`user_pk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
