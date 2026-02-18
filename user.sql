-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2025 at 04:30 PM
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
-- Database: `user`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `Id` int(11) NOT NULL,
  `A_Email` varchar(255) NOT NULL,
  `A_Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`Id`, `A_Email`, `A_Password`) VALUES
(4, 'admin1@kmail.com', 'admin@111'),
(5, 'admin2@gmail.com', 'admin@222');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `booking_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `vehicle_no` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_id` varchar(255) NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `slot_id`, `seat_number`, `user_name`, `booking_time`, `vehicle_no`, `amount_paid`, `payment_id`, `end_time`, `city`, `area`, `location`) VALUES
(143, 16, 15, 'Preet', '2025-03-06 13:20:17', 'GJ05MH2222', 2000.00, 'pay_Q3WEr3RacUc3G9', '2025-03-06 20:50:17', 'Ahemdabad', 'Thaltej Road', 'Palladium Mall'),
(145, 53, 2, 'Preet', '2025-03-06 13:22:15', 'GJ05MH2222', 6000.00, 'pay_Q3WGtk7SRBlWBN', '2025-03-06 20:52:15', 'Banglore', 'Sampige Road', 'Mantri Sqare Mall'),
(146, 49, 9, 'Preet', '2025-03-06 14:28:59', 'GJ05MH2222', 5000.00, 'pay_Q3XPPaqeIQHfXU', '2025-03-06 21:58:59', 'Surat', 'Mota Varachha', 'AR Mall'),
(147, 49, 17, 'Preet', '2025-03-06 14:29:44', 'GJ05MH2222', 5000.00, 'pay_Q3XQ5yAnEUWSc1', '2025-03-06 21:59:44', 'Surat', 'Mota Varachha', 'AR Mall');

-- --------------------------------------------------------

--
-- Table structure for table `slots`
--

CREATE TABLE `slots` (
  `id` int(11) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `area` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_slots` int(11) DEFAULT 10,
  `available_slots` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slots`
--

INSERT INTO `slots` (`id`, `city`, `area`, `location`, `price`, `total_slots`, `available_slots`) VALUES
(16, 'Ahemdabad', 'Thaltej Road', 'Palladium Mall', 20.00, 30, 29),
(49, 'Surat', 'Mota Varachha', 'AR Mall', 50.00, 20, 18),
(50, 'Banglore', 'WhiteField Road', 'Phoenix Mall', 50.00, 20, 20),
(53, 'Banglore', 'Sampige Road', 'Mantri Sqare Mall', 60.00, 15, 14),
(55, 'Banglore', 'Dr Rajkumar Road', 'Orion Mall', 80.00, 20, 20),
(56, 'Kolkata', 'Anwar Raod', 'South City Mall', 60.00, 15, 15);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `U_Name` varchar(255) NOT NULL,
  `U_Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Phone_number` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`Id`, `U_Name`, `U_Email`, `Password`, `Phone_number`) VALUES
(7, 'Purvil', 'Purvil@gmail.com', '$2y$10$KzNvr404YqyaG4BlzoJu9uLu.UD5QBGGi125G9OFvF01uoYq4u9Ya', '9898989898'),
(8, 'Gyani', 'gyanibaba534@gmail.com', '$2y$10$ZchkTcXm7q2QXh2ZxUsmCucpZGAyBjAT1t8CltLlaXynboiUNk8SK', '9696969696'),
(9, 'Preet', 'preetk02700270@gmail.com', '$2y$10$MHSrH/Y/GXwOwCLOd57lmuRtkA5y3GYEv3kec1GgiK5svb/JFnpZ2', '6351581680'),
(10, 'Kishan', 'kishanlathiya029@gmail.com', '$2y$10$Wax2hfJi7CiIpPgzZxrf/OpTxfnOWCrYxNRglYoZWyK4a.Isb5Fzu', '6363636363'),
(11, 'sanskruti', 'sanskrutiramani@gmail.com', '$2y$10$oV6IIERs7wuRLa9Orol8heDsQxk1FNRpGfea5szmo43Y9dhvPgfre', '9016419482');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `email`, `password`, `phone`, `city`, `area`, `location`) VALUES
(16, 'Mehul', 'Mehul@gmail.com', '$2y$10$QjDRZe.YRxBdnTahUgtAvOdZhKnz3PS84hx9krt3bwHhqiyEdil2K', '6363636363', 'Ahemdabad', 'Thaltej Road', 'Palladium Mall'),
(49, 'Preet', 'preetk02700270@gmail.com', '$2y$10$oQwS0JKPWe.SGE0NGY81k.re.ANu7LXHfCj83sG1yjuLL39JauG4q', '6363636363', 'Surat', 'Mota Varachha', 'AR Mall'),
(50, 'Kishan', 'kishanlathiya029@gmail.com', '$2y$10$hz80VvFLo3wjct6GdFhp9eeLLk6Lq2AWI38qQGYL6br9ULYern0GW', '7878788877', 'Banglore', 'WhiteField Road', 'Phoenix Mall'),
(53, 'Chirag', 'kananichirag444@gmail.com', '$2y$10$hIqUTXVn.bd5nMKYjAcrbuiLarVIe6677SGbfVX0MMOZW.VxfkbvO', '7046035712', 'Banglore', 'Sampige Road', 'Mantri Sqare Mall'),
(55, 'Meji', 'mehij84329@egvoo.com', '$2y$10$OIV2ABSJTGPC3oaqTvqHqedkVBpK1HTufg8V4vwXBZxwnPaBiSEE2', '8845566211', 'Banglore', 'Dr Rajkumar Road', 'Orion Mall'),
(56, 'Preetam', 'Preetum84329@egvoo.com', '$2y$10$BuFPua4zQZBy8okImqYJXur1Lk4fJ67Hq9x/WnE79078Fd3b5A0TW', '9859856986', 'Kolkata', 'Anwar Raod', 'South City Mall'),
(58, 'Karan', 'Karan@gmail.com', '$2y$10$GaSxSy1dTp5pL0lIphKHUubAJ.i4dvcVxAATA7kTBz1OPgw0AFJoW', '6363636363', 'Surat', 'Vesu', 'VR Mall');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slot_id` (`slot_id`);

--
-- Indexes for table `slots`
--
ALTER TABLE `slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `slots`
--
ALTER TABLE `slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
