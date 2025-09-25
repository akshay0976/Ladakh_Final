-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 09:19 AM
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
-- Database: `mizoram`
--

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `uid` varchar(100) NOT NULL,
  `verified` varchar(10) NOT NULL DEFAULT '0',
  `role` varchar(255) NOT NULL DEFAULT 'admin',
  `token` varchar(255) NOT NULL,
  `lastlogin` varchar(255) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `permission` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`username`, `password`, `uid`, `verified`, `role`, `token`, `lastlogin`, `count`, `permission`) VALUES
('admin@pds', '$2y$10$.HIRS7ZF3NrDoUqgzOcsA.3LfUSNkksCBgF3jSFM4nb4zGGEIVZuW', '67163a9d4e46e', '1', 'admin', 'a13f3a4dc43ad56847757f3365509be7', '2025-09-19 12:48:24', 141, ''),
('Aizawl@pds', '$2y$10$6C7w/uMx7EjArGrZe7Uc1uJUfAjAtFGKddFu4XQiw2I81guHrMkeq', '68baf301d67e9', '1', 'aizawl', 'e834b975a45a47e40303585c030a0302', '2025-09-05 19:59:05', 1, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
