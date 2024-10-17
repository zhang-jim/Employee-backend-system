-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-10-17 19:48:11
-- 伺服器版本： 10.4.28-MariaDB
-- PHP 版本： 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `backend_system`
--

-- --------------------------------------------------------

--
-- 資料表結構 `all_leave`
--

CREATE TABLE `all_leave` (
  `Leave_id` int(6) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `Start_date` timestamp NULL DEFAULT NULL,
  `Finish_date` timestamp NULL DEFAULT NULL,
  `Date_of_filing` date DEFAULT NULL,
  `Status` varchar(20) NOT NULL DEFAULT 'undetermined',
  `Leave_category` varchar(20) NOT NULL,
  `Reason` varchar(100) NOT NULL,
  `illustrate` varchar(100) DEFAULT NULL,
  `Sick_img` varchar(80) DEFAULT NULL,
  `Days` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `annual_leave_message`
--

CREATE TABLE `annual_leave_message` (
  `Id` int(6) NOT NULL,
  `Name` varchar(20) NOT NULL,
  `Date_of_filing` date DEFAULT NULL,
  `Text` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `category`
--

CREATE TABLE `category` (
  `id` int(5) NOT NULL,
  `category_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `classdata`
--

CREATE TABLE `classdata` (
  `id` int(5) NOT NULL,
  `class` varchar(20) NOT NULL,
  `startWork` time NOT NULL,
  `ontime` time NOT NULL,
  `getOffWork` time NOT NULL,
  `lastclock` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `latetimetable`
--

CREATE TABLE `latetimetable` (
  `id` int(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `year` year(4) NOT NULL,
  `month` varchar(2) NOT NULL,
  `latetime` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `memberdata`
--

CREATE TABLE `memberdata` (
  `id` int(5) NOT NULL,
  `name` varchar(30) NOT NULL,
  `rule` varchar(5) NOT NULL DEFAULT '3',
  `account` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `category` varchar(20) NOT NULL,
  `class` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `EntryDate` date NOT NULL,
  `Annual_leave` int(3) DEFAULT 0,
  `Remaining_annual_leave` int(3) DEFAULT 0,
  `Prompting_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `menstruation_leave`
--

CREATE TABLE `menstruation_leave` (
  `Id` int(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `date` varchar(7) NOT NULL,
  `Menstruation_days` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `time`
--

CREATE TABLE `time` (
  `id` int(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `date` date NOT NULL,
  `onlineTime` timestamp NULL DEFAULT NULL,
  `offlineTime` timestamp NULL DEFAULT NULL,
  `workstatus` varchar(30) NOT NULL DEFAULT '',
  `statusCategory` varchar(20) NOT NULL DEFAULT '',
  `latetime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `all_leave`
--
ALTER TABLE `all_leave`
  ADD PRIMARY KEY (`Leave_id`),
  ADD KEY `ask_for_leave_name_foreign` (`Name`);

--
-- 資料表索引 `annual_leave_message`
--
ALTER TABLE `annual_leave_message`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `AL_message_foreign` (`Name`);

--
-- 資料表索引 `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_name`);

--
-- 資料表索引 `classdata`
--
ALTER TABLE `classdata`
  ADD PRIMARY KEY (`class`);

--
-- 資料表索引 `latetimetable`
--
ALTER TABLE `latetimetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_foreign` (`name`);

--
-- 資料表索引 `memberdata`
--
ALTER TABLE `memberdata`
  ADD PRIMARY KEY (`name`),
  ADD KEY `categoryForeign` (`category`),
  ADD KEY `class_foreign` (`class`);

--
-- 資料表索引 `menstruation_leave`
--
ALTER TABLE `menstruation_leave`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `menstruation_user_foreign` (`name`);

--
-- 資料表索引 `time`
--
ALTER TABLE `time`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name_foreign` (`name`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `all_leave`
--
ALTER TABLE `all_leave`
  MODIFY `Leave_id` int(6) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `annual_leave_message`
--
ALTER TABLE `annual_leave_message`
  MODIFY `Id` int(6) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `latetimetable`
--
ALTER TABLE `latetimetable`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `menstruation_leave`
--
ALTER TABLE `menstruation_leave`
  MODIFY `Id` int(10) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `time`
--
ALTER TABLE `time`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `all_leave`
--
ALTER TABLE `all_leave`
  ADD CONSTRAINT `ask_for_leave_name_foreign` FOREIGN KEY (`Name`) REFERENCES `memberdata` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `annual_leave_message`
--
ALTER TABLE `annual_leave_message`
  ADD CONSTRAINT `AL_message_foreign` FOREIGN KEY (`Name`) REFERENCES `memberdata` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `latetimetable`
--
ALTER TABLE `latetimetable`
  ADD CONSTRAINT `user_foreign` FOREIGN KEY (`name`) REFERENCES `memberdata` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `memberdata`
--
ALTER TABLE `memberdata`
  ADD CONSTRAINT `category_foreign` FOREIGN KEY (`category`) REFERENCES `category` (`category_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `class_foreign` FOREIGN KEY (`class`) REFERENCES `classdata` (`class`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `menstruation_leave`
--
ALTER TABLE `menstruation_leave`
  ADD CONSTRAINT `menstruation_user_foreign` FOREIGN KEY (`name`) REFERENCES `memberdata` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `time`
--
ALTER TABLE `time`
  ADD CONSTRAINT `nameForeign` FOREIGN KEY (`name`) REFERENCES `memberdata` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
