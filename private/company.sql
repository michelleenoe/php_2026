-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Generation Time: Nov 11, 2025 at 01:26 PM
-- Server version: 10.6.20-MariaDB-ubu2004
-- PHP Version: 8.3.26

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
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `like_user_fk` char(50) NOT NULL,
  `like_post_fk` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `person_pk` bigint(20) UNSIGNED NOT NULL,
  `person_username` varchar(20) NOT NULL,
  `person_first_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person`
--

INSERT INTO `person` (`person_pk`, `person_username`, `person_first_name`) VALUES
(1, 'andreahauberg', 'Andrea'),
(5, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_pk` char(50) NOT NULL,
  `post_message` varchar(200) NOT NULL,
  `post_image_path` varchar(100) NOT NULL,
  `post_user_fk` char(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_pk`, `post_message`, `post_image_path`, `post_user_fk`) VALUES
('1', 'Post one', 'https://picsum.photos/400/250', '1'),
('2', '<script>alert()</stript>', 'https://picsum.photos/400/250', '1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_pk` char(50) NOT NULL,
  `user_username` varchar(20) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_full_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_pk`, `user_username`, `user_email`, `user_password`, `user_full_name`) VALUES
('1', 'andreahauberggg', 'a@a.com', '$2y$10$FFupw1g6iLrsAG1rraCqjeS38je8PgdOxml9YVQdV8ln/hSgkm.4.', 'Andrea Hauberg'),
('5afc9c4fe287446fa12cad78655727d2', 'user1', 'b@b.com', '$2y$10$GheYC/X6jez8yeWkVpqgKuJ5q/miOZcAdWBB858OFojdL0JLZzyba', 'Bobo Bob'),
('1a3add65aab9406698beb1de16227e12', 'user2', 'c@c.com', '$2y$10$5Rt6kJtegTIh3mAfJk059OgsLIdLES8n/ldWgvRegunURAUCK.T0m', 'Clem Clemsen'),
('4feb86ecb46c473e0f6c01e97bc26086e7953b5f621c08b5a1', 'user4', 'j@j.com', '$2y$10$YRUnPlxARN5G7em/xySvRuJV/hhjpATtSGMFbQ.zUsdeWJhmKaPrm', 'Jean Jean');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`like_user_fk`,`like_post_fk`),
  ADD KEY `like_post_fk` (`like_post_fk`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_pk`),
  ADD UNIQUE KEY `person_pk` (`person_pk`),
  ADD UNIQUE KEY `person_username` (`person_username`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_pk`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `user_email` (`user_email`),
  ADD UNIQUE KEY `user_pk` (`user_pk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `person_pk` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`like_post_fk`) REFERENCES `posts` (`post_pk`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`like_user_fk`) REFERENCES `users` (`user_pk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
