-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2025 at 08:56 AM
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
-- Database: `erp_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `company_id`, `user_id`, `user_role`, `module`, `action`, `description`, `ip_address`, `user_agent`, `timestamp`) VALUES
(1, 7, 25, 'head_hr', 'hr', 'add_employee', 'Added employee ID: 010, Name: john', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 05:15:13'),
(2, 7, 23, 'admin', 'pm', 'update_task_status', 'Updated task ID: 5 status to done', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 16:26:35'),
(3, 7, 23, 'admin', 'pm', 'update_task_status', 'Updated task ID: 2 status to done', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 16:26:36'),
(4, 7, 23, 'admin', 'pm', 'update_task_status', 'Updated task ID: 1 status to done', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 16:26:37'),
(5, 7, 23, 'admin', 'pm', 'update_task_status', 'Updated task ID: 3 status to done', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 16:26:37'),
(6, 7, 23, 'admin', 'pm', 'save_project_budget', 'Set budget for project ID: 2 to 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 16:26:53'),
(7, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:46:19'),
(8, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:47:45'),
(9, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:53:18'),
(10, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:53:36'),
(11, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:02:11'),
(12, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:06:17'),
(13, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked asd', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:06:24'),
(14, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked juju', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:16:59'),
(15, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked juju', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:24:11'),
(16, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked juju', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:30:54'),
(17, 7, 23, 'admin', 'manage_account', 'lock_user', 'Manually locked juju', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 18:37:11'),
(18, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 18:59:42'),
(19, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:30:24'),
(20, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:39:44'),
(21, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:48:53'),
(22, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (123 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:49:20'),
(23, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item carrot chips (Qty: 100).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:52:48'),
(24, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (123 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:53:14'),
(25, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item carrot chips (123 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:53:37'),
(26, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item kangkong chips (Qty: 123).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:53:43'),
(27, 7, 23, 'admin', 'sales', 'delete_sale', 'Deleted sale ID: 17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:54:01'),
(28, 7, 23, 'admin', 'hr', 'delete_employee', 'Deleted employee ID: 18', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:54:05'),
(29, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item carrot chips (123 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:55:47'),
(30, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (123 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 19:56:02'),
(31, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Kuyukot (123 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-24 20:04:51'),
(32, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:52:47'),
(33, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item carrot chips (Qty: 123).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:52:58'),
(34, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Kuyukot (123 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:55:01'),
(35, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item axfg (123 liter) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:55:15'),
(36, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Kuyukot (Qty: 123).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:55:32'),
(37, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item axfg (123 liter) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:56:22'),
(38, 7, 23, 'admin', 'inventory', 'update_item', 'Increased axfg stock by 123. New total: 246.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:57:17'),
(39, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (123 kg) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:57:36'),
(40, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item axfg (Qty: 246).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:57:40'),
(41, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:59:48'),
(42, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (123 kg) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 00:59:57'),
(43, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item carrot chips (123 g) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:10:39'),
(44, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item kangkong chips (Qty: 121).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:10:41'),
(45, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item carrot chips (123 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:11:10'),
(46, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item ballpe (125 kg) in School Supplies.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:12:25'),
(47, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:18:22'),
(48, 7, 30, 'head_inventory', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:18:34'),
(49, 7, 30, 'head_inventory', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:18:52'),
(50, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:19:03'),
(51, 7, 42, 'head_finance', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:19:16'),
(52, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:19:22'),
(53, 7, 29, 'head_sales', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:19:39'),
(54, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:19:54'),
(55, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item carrot chips (Qty: 123).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:20:07'),
(56, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item ballpe (125 pcs) in School Supplies.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:20:35'),
(57, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (555 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:20:53'),
(58, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item ballpe (Qty: 115).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:45:15'),
(59, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item kangkong chips (Qty: 115).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 01:59:15'),
(60, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item andoks (111 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:01:11'),
(61, 7, 23, 'admin', 'inventory', 'mark_defective_partial', 'Moved 1 qty of andoks to defective inventory.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:01:16'),
(62, 7, 23, 'admin', 'inventory', 'mark_defective_partial', 'Moved 12 qty of andoks to defective inventory.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:01:25'),
(63, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item chiicken (100 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:14:09'),
(64, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Kuyukot (200 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:19:34'),
(65, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item andoks (Qty: 94).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:22:22'),
(66, 7, 23, 'admin', 'inventory', 'mark_defective_partial', 'Moved 1 qty of chiicken to defective inventory.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:22:27'),
(67, 7, 23, 'admin', 'inventory', 'restore_defective', 'Restored 1 qty of chiicken back to active inventory.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:22:31'),
(68, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:53:56'),
(69, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:55:00'),
(70, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:55:11'),
(71, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 02:55:24'),
(72, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:31:04'),
(73, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:35:23'),
(74, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:36:16'),
(75, 7, 23, 'admin', 'inventory', 'mark_defective_all', 'Marked chiicken (Qty: 0) as fully defective.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:39:16'),
(76, 7, 23, 'admin', 'inventory', 'restore_defective', 'Restored 0 qty of chiicken back to active inventory.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:39:19'),
(77, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item chiicken (Qty: 0).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:41:55'),
(78, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item kangkong chips (222 box) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:42:22'),
(79, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:42:29'),
(80, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:43:13'),
(81, 7, 23, 'admin', 'inventory', 'import_inventory', 'Imported 4 inventory item(s) via inventory_report.csv.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:45:26'),
(82, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item 1 (Qty: 0).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:45:45'),
(83, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item 12 (Qty: 0).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:45:47'),
(84, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item 216 (Qty: 0).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:45:48'),
(85, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item 191 (Qty: 0).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 03:45:50'),
(86, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 04:02:32'),
(87, 7, 23, 'admin', 'inventory', 'delete_defective', 'Deleted defective inventory item andoks (Qty: 12).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 04:55:32'),
(88, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 05:20:45'),
(89, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 05:20:58'),
(90, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item kangkong chips (Qty: 216).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 05:34:25'),
(91, 7, 23, 'admin', 'inventory', 'add_item', 'Added new inventory item kangkong chips (SKU: 4124, Qty: 222).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 05:34:37'),
(92, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 08:06:41'),
(93, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 08:07:22'),
(94, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-25 08:38:22'),
(95, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:53:18'),
(96, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Choco (50 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:57:18'),
(97, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:58:28'),
(98, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:58:56'),
(99, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 1 qty to Kuyukot. New total: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 09:35:11'),
(100, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 4 qty to Kuyukot. New total: 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 09:36:06'),
(101, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 1 qty to Kuyukot. New total: 11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 09:36:11'),
(102, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 2 qty to Kuyukot. New total: 11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 09:51:14'),
(103, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Butter (20 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:32:05'),
(104, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 6 qty to Kuyukot. New total: 12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:33:10'),
(105, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:53:12'),
(106, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Peanut (100 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 10:57:25'),
(107, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Donut (100 pcs) in food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 11:38:25'),
(108, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 18:52:35'),
(109, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 18:52:46'),
(110, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 18:55:36'),
(111, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:06:00'),
(112, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:06:42'),
(113, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:09:41'),
(114, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Kuyukot (Qty: 9).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:40:43'),
(115, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item kangkong chips (Qty: 222).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:40:45'),
(116, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Choco (Qty: 39).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:40:46'),
(117, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Butter (Qty: 15).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:40:48'),
(118, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Chicken (100 kg) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:42:18'),
(119, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 12 qty to Peanut. New total: 107', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:43:22'),
(120, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 1000 qty to Peanut. New total: 1107', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:43:29'),
(121, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 11 of Peanut as defective. Remaining stock: 1096.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:51:54'),
(122, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:57:50'),
(123, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 19:58:19'),
(124, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:49:04'),
(125, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:51:27'),
(126, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:57:16'),
(127, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:58:11'),
(128, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 23:03:37'),
(129, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 01:05:12'),
(130, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 30 qty to Chicken. New total: 101', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 01:06:04'),
(131, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 01:06:39'),
(132, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 01:07:51'),
(133, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 10 of Chicken as defective. Remaining stock: 78.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 01:08:44'),
(134, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:13:53'),
(135, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:14:32'),
(136, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:14:50'),
(137, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:23:00'),
(138, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:25:30'),
(139, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:26:13'),
(140, 7, 42, 'head_finance', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:29:01'),
(141, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:29:27'),
(142, 7, 42, 'head_finance', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:41:55'),
(143, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 09:42:08'),
(144, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 00:56:51'),
(145, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 00:57:15'),
(146, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 1 of Chicken as defective. Remaining stock: 77.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:06:48'),
(147, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Bread (100 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:09:25'),
(148, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Hotdog (100 pack) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:10:55'),
(149, 7, 23, 'admin', 'pm', 'create_project', 'Created project: Hotdog Sandwich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:19:55'),
(150, 7, 23, 'admin', 'pm', 'assign_resource', 'Assigned resource \"Daniel\" to task \"BLE\" in project \"GOO\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:22:14'),
(151, 7, 23, 'admin', 'pm', 'add_cost', 'Added ₱100.00 expense cost for task \"PM\" / project \"Finals\" dated 2025-11-27.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:23:37'),
(152, 7, 23, 'admin', 'pm', 'bulk_assign_resource', 'Bulk assigned resource \"Laynes\" to \"BLE\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:28:03'),
(153, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:37:24'),
(154, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:37:25'),
(155, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:38:03'),
(156, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Cocoa (2 pack) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:39:17'),
(157, 7, 23, 'admin', 'pm', 'create_project', 'Created project: Chocolate', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:40:14'),
(158, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 2 qty to Cocoa. New total: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:48:09'),
(159, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 2 qty to Cocoa. New total: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:48:13'),
(160, 7, 23, 'admin', 'pm', 'create_project', 'Created project: Chocolate', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:48:58'),
(161, 7, 23, 'admin', 'pm', 'save_project_budget', 'Set budget for project \"Chocolate\" to ₱1,000.00 with alerts at 50% utilization.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:49:56'),
(162, 7, 23, 'admin', 'pm', 'save_project_reminder', 'Set deadline reminder for project \"Chocolate\" to 2 day(s) before due date.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:50:05'),
(163, 7, 23, 'admin', 'pm', 'save_project_reminder', 'Set deadline reminder for project \"Hotdog Sandwich\" to 1 day(s) before due date.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:50:24'),
(164, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"test 1\" for project \"Hotdog Sandwich\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:54:06'),
(165, 7, 23, 'admin', 'pm', 'add_cost', 'Added ₱500.00 expense cost for task \"test 1\" / project \"Hotdog Sandwich\" dated 2025-11-27.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:54:48'),
(166, 7, 23, 'admin', 'pm', 'save_project_budget', 'Set budget for project \"Hotdog Sandwich\" to ₱100.00 with alerts at 50% utilization.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:57:47'),
(167, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"Chocolate\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:58:59'),
(168, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"Chocolate\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 01:59:03'),
(169, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"test 2\" for project \"Hotdog Sandwich\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:01:53'),
(170, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"hi\" for project \"test 1\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:02:35'),
(171, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:10:10'),
(172, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"part 1\" for project \"test 2\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:10:37'),
(173, 7, 23, 'admin', 'pm', 'save_project_reminder', 'Set deadline reminder for project \"test 2\" to 1 day(s) before due date.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:10:48'),
(174, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"Hotdog Sandwich\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 02:21:32'),
(175, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 1 of Hotdog as defective. Remaining stock: 88.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 03:00:55'),
(176, 7, 23, 'admin', 'inventory', 'restore_defective', 'Restored 1 qty of andoks (ID: 2) from defective status.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 03:07:08'),
(177, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item andoks (Qty: 1).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 03:07:15'),
(178, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 1 of Bread as defective. Remaining stock: 85.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 03:07:28'),
(179, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 1 of Bread as defective. Remaining stock: 84.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 03:07:42'),
(180, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"hi\" for project \"test 1\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:14:32'),
(181, 7, 23, 'admin', 'pm', 'assign_resource', 'Assigned resource \"Daniel\" to task \"hi\" in project \"test 1\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:38:54'),
(182, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"hi\" in project \"test 1\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:42:13'),
(183, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"hi\" in project \"test 1\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:42:16'),
(184, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"test 1\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:44:32'),
(185, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"Finals\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:44:39'),
(186, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"test 2\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:44:52'),
(187, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:46:22'),
(188, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:46:27'),
(189, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:46:29'),
(190, 7, 23, 'admin', 'pm', 'create_project', 'Created project: Hotdog Sandwich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:48:18'),
(191, 7, 23, 'admin', 'pm', 'create_task', 'Created task \"Creation\" for project \"Hotdog Sandwich\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:48:50'),
(192, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:49:02'),
(193, 7, 23, 'admin', 'pm', 'assign_resource', 'Assigned resource \"Daniel\" to task \"BLE\" in project \"GOO\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:49:08'),
(194, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:49:10'),
(195, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"Creation\" in project \"Hotdog Sandwich\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:49:15'),
(196, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"Creation\" in project \"Hotdog Sandwich\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:49:18'),
(197, 7, 23, 'admin', 'pm', 'save_project_reminder', 'Set deadline reminder for project \"Hotdog Sandwich\" to 1 day(s) before due date.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:56:19'),
(198, 7, 23, 'admin', 'pm', 'save_project_budget', 'Set budget for project \"Hotdog Sandwich\" to ₱100.00 with alerts at 50% utilization.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:56:37'),
(199, 7, 23, 'admin', 'pm', 'add_cost', 'Added ₱60.00 material cost for task \"Creation\" / project \"Hotdog Sandwich\" dated 2025-11-27.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 04:56:49'),
(200, 7, 23, 'admin', 'hr', 'edit_employee', 'Edited employee ID: 005, Name: Johnny', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:02:06'),
(201, 7, 23, 'admin', 'hr', 'delete_employee', 'Deleted employee ID: 15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:02:11');
INSERT INTO `activity_logs` (`id`, `company_id`, `user_id`, `user_role`, `module`, `action`, `description`, `ip_address`, `user_agent`, `timestamp`) VALUES
(202, 7, 23, 'admin', 'inventory', 'mark_defective_part', 'Marked 10 of Peanut as defective. Remaining stock: 1085.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:02:32'),
(203, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:07:15'),
(204, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:07:36'),
(205, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:07:48'),
(206, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 2 qty to Hotdog. New total: 88', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:38:46'),
(207, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511270653130023 | Items: 1 | Total: 250.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:53:13'),
(208, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:56:36'),
(209, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511270656430023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:56:43'),
(210, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:57:10'),
(211, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:57:11'),
(212, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:57:11'),
(213, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:57:59'),
(214, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511270658110023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 05:58:11'),
(215, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 06:01:16'),
(216, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511270701200023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 06:01:20'),
(217, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 12:01:46'),
(218, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 12:02:05'),
(219, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 12:02:05'),
(220, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:05:18'),
(221, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:05:36'),
(222, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271405490023 | Items: 1 | Total: 250.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:05:49'),
(223, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:10:14'),
(224, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:10:15'),
(225, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:10:15'),
(226, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:10:15'),
(227, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:10:16'),
(228, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411020023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:02'),
(229, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411060023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:06'),
(230, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411100023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:10'),
(231, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411140023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:14'),
(232, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411170023 | Items: 2 | Total: 270.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:17'),
(233, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:37'),
(234, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271411490023 | Items: 1 | Total: 40.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:49'),
(235, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 13:11:53'),
(236, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:36:08'),
(237, 7, 23, 'admin', 'inventory', 'add_vendor', 'Added vendor Bossing (ID: 12, Supplier ID: 123).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:38:53'),
(238, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 2 qty to Cocoa. New total: 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:40:03'),
(239, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:40:13'),
(240, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:40:13'),
(241, 7, 50, 'head_pos', 'pos', 'process_sale', 'Receipt RC202511271540280050 | Items: 1 | Total: 100.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:40:28'),
(242, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:46:21'),
(243, 7, 23, 'admin', 'inventory', 'add_bom', 'Created BOM test 1 (ID: 3, Output Qty: 1) with 1 component(s).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:47:27'),
(244, 7, 23, 'admin', 'inventory', 'add_vendor', 'Added vendor John (ID: 13, Supplier ID: 35).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 14:56:06'),
(245, 7, 23, 'admin', 'pm', 'save_project_budget', 'Set budget for project \"SAD\" to ₱50,000.00 with alerts at 50% utilization.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:01:41'),
(246, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:10:45'),
(247, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:15:33'),
(248, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:22:43'),
(249, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:22:43'),
(250, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:22:57'),
(251, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Choco Choco (10 pack) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:26:10'),
(252, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:27:08'),
(253, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271627550023 | Items: 1 | Total: 100.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:27:55'),
(254, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Peanut (Qty: 1084).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:29:54'),
(255, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:29:59'),
(256, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271630040023 | Items: 1 | Total: 100.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:30:04'),
(257, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271636210023 | Items: 1 | Total: 100.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:36:21'),
(258, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271637450023 | Items: 5 | Total: 470.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:37:45'),
(259, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:17'),
(260, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:21'),
(261, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:21'),
(262, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271639260023 | Items: 1 | Total: 300.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:26'),
(263, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271639290023 | Items: 1 | Total: 300.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:29'),
(264, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:39:39'),
(265, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 100 qty to Choco Choco. New total: 100', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:40:03'),
(266, 7, 23, 'admin', 'inventory', 'update_vendor', 'Updated vendor John (ID: 13, Supplier ID: 35).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:40:36'),
(267, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:41:02'),
(268, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271641120023 | Items: 1 | Total: 1000.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:41:12'),
(269, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271641180023 | Items: 1 | Total: 1000.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:41:18'),
(270, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:41:22'),
(271, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271643110023 | Items: 5 | Total: 470.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:43:11'),
(272, 7, 23, 'admin', 'manage_account', 'edit_user', 'Edited user: Big Boy', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:47:24'),
(273, 7, 23, 'admin', 'manage_account', 'edit_user', 'Edited user: Big Boy (employee ID 000 → 00)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:52:29'),
(274, 7, 23, 'admin', 'manage_account', 'add_user', 'Added user: Boy (employee ID: 000, role: head_hr)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:52:47'),
(275, 7, 23, 'admin', 'finance', 'delete_transaction', 'Deleted expense transaction of ₱10,000.00 dated 2025-10-07.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 15:53:06'),
(276, 7, 23, 'admin', 'finance', 'delete_transaction', 'Deleted income transaction of ₱20,000.00 dated 2025-09-07.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:04:29'),
(277, 7, 23, 'admin', 'inventory', 'delete_bom', 'Deleted BOM test 1 (ID: 3).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:06:37'),
(278, 7, 23, 'admin', 'inventory', 'update_vendor', 'Updated vendor MCDO Supplier (ID: 11, Supplier ID: 8).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:06:56'),
(279, 7, 23, 'admin', 'hr', 'delete_employee', 'Deleted employee ID: 20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:11:51'),
(280, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:13:26'),
(281, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:14:40'),
(282, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:14:48'),
(283, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:15:17'),
(284, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:15:44'),
(285, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:16:08'),
(286, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:17:14'),
(287, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:17:21'),
(288, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:19:35'),
(289, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:20:31'),
(290, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:21:08'),
(291, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:22:50'),
(292, 7, NULL, 'admin', 'hr', 'add_payroll', 'Payroll for roven (002): 0.02 hrs @ 120.00/hr. Net PHP 2.40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:34:03'),
(293, 7, NULL, 'admin', 'hr', 'add_payroll', 'Payroll for roven (002): 0.02 hrs @ 10.00/hr. Net PHP 0.20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:35:08'),
(294, 7, 23, 'admin', 'pm', 'create_project', 'Created project: Hotdog Sandwich', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:36:12'),
(295, 7, 23, 'admin', 'pm', 'bulk_assign_resource', 'Bulk assigned resource \"Laynes\" to \"BLE\", \"Creation\".', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:40:08'),
(296, 7, 23, 'admin', 'pm', 'log_time', 'Logged 10.00h on task \"BLE\" in project \"GOO\" for resource \"Laynes\" dated 2025-11-27.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:41:01'),
(297, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as In Progress.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:41:14'),
(298, 7, 23, 'admin', 'pm', 'update_task_status', 'Marked task \"BLE\" in project \"GOO\" as Done.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:41:22'),
(299, 7, 23, 'admin', 'pm', 'add_cost', 'Added ₱100.00 expense cost for task \"Creation\" / project \"Hotdog Sandwich\" dated 2025-11-27.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:41:54'),
(300, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:45:04'),
(301, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:45:04'),
(302, 7, 50, 'head_pos', 'pos', 'process_sale', 'Receipt RC202511271745090050 | Items: 5 | Total: 470.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:45:09'),
(303, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:45:27'),
(304, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:45:38'),
(305, 7, 23, 'admin', 'inventory', 'add_vendor', 'Added vendor Sef (ID: 14, Supplier ID: 10).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:46:20'),
(306, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Potato (100 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:46:53'),
(307, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:46:59'),
(308, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511271747090023 | Items: 1 | Total: 500.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:47:09'),
(309, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:47:15'),
(310, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:47:52'),
(311, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:57:12'),
(312, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 16:57:58'),
(313, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 17:08:32'),
(314, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 17:08:51'),
(315, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 17:10:09'),
(316, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:09:51'),
(317, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:10:33'),
(318, 7, 25, 'head_hr', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:13:57'),
(319, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:41:44'),
(320, 7, 23, 'admin', 'pm', 'add_resource', 'Added resource: Mark', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:43:08'),
(321, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 10 qty to Cocoa. New total: 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:44:38'),
(322, 7, 23, 'admin', 'inventory', 'add_bom', 'Created BOM test 1 (ID: 3, Output Qty: 1) with 1 component(s).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:45:47'),
(323, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:46:13'),
(324, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 100 qty to Choco Choco. New total: 100', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-27 23:57:23'),
(325, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 00:10:25'),
(326, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"test 1\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 00:10:45'),
(327, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 200 qty to Choco Choco. New total: 200', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:23:06'),
(328, 7, 23, 'admin', 'pm', 'reallocate_materials', 'Adjusted allocations for project #11 | Items: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:23:20'),
(329, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:35:35'),
(330, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Sugar (100 g) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:52:14'),
(331, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 1 qty to Choco Choco. New total: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:52:27'),
(332, 7, 50, 'head_pos', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:52:43'),
(333, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:52:43'),
(334, 7, 50, 'head_pos', 'pos', 'process_sale', 'Receipt RC202511280252560050 | Items: 2 | Total: 150.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:52:56'),
(335, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:53:28'),
(336, 7, 50, 'head_pos', 'pos', 'process_sale', 'Receipt RC202511280254060050 | Items: 1 | Total: 1750.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 01:54:06'),
(337, 7, 50, 'head_pos', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:00:57'),
(338, 7, 50, 'head_pos', 'pos', 'process_sale', 'Receipt RC202511280301090050 | Items: 1 | Total: 50.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:01:09'),
(339, 7, 23, 'admin', 'login', 'login_success', 'User authenticated via admin/head portal.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:03:52'),
(340, 7, 23, 'admin', 'inventory', 'adjust_quantity', 'Added 100 qty to Choco Choco. New total: 100', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:12:20'),
(341, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:12:23'),
(342, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511280312330023 | Items: 2 | Total: 325.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:12:33'),
(343, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"test 1\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:21:29'),
(344, 7, 23, 'admin', 'pm', 'delete_project', 'Deleted project \"test 2\" and related records.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:21:31'),
(345, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:21:39'),
(346, 7, 23, 'admin', 'inventory', 'delete_bom', 'Deleted BOM Sandwich (ID: 1).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:24:32'),
(347, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Bread (Qty: 66).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:24:42'),
(348, 7, 23, 'admin', 'inventory', 'add_vendor', 'Added vendor Daniel (ID: 15, Supplier ID: 1).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:26:44'),
(349, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Bread (50 pcs) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:27:50'),
(350, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:28:44'),
(351, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511280328530023 | Items: 2 | Total: 45.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:28:54'),
(352, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Salt (100 set) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:42:14'),
(353, 7, 23, 'admin', 'inventory', 'delete_item', 'Deleted inventory item Salt (Qty: 100).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:46:59'),
(354, 7, 23, 'admin', 'inventory', 'add_item', 'Added inventory item Salt (100 set) in Food.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:47:33'),
(355, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:53:19'),
(356, 7, 23, 'admin', 'pos', 'process_sale', 'Receipt RC202511280353260023 | Items: 2 | Total: 75.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:53:26'),
(357, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 02:53:58'),
(358, 7, 23, 'admin', 'pos', 'view_pos_system', 'Accessed POS System interface', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:09:47'),
(359, 7, 23, 'admin', 'inventory', 'add_bom', 'Created BOM test 3 (ID: 4, Output Qty: 1) with 1 component(s).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:10:29'),
(360, 7, 23, 'admin', 'pm', 'create_project', 'Created project: test 2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `employee_type` enum('head','general') DEFAULT 'head',
  `employee_name` varchar(100) DEFAULT NULL,
  `company_id` int(11) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `date` date NOT NULL,
  `scheduled_start_time` time DEFAULT NULL,
  `scheduled_end_time` time DEFAULT NULL,
  `is_late` tinyint(1) NOT NULL DEFAULT 0,
  `late_minutes` int(11) NOT NULL DEFAULT 0,
  `is_ot_without_pay` tinyint(1) NOT NULL DEFAULT 0,
  `overtime_is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `ot_minutes` int(11) NOT NULL DEFAULT 0,
  `is_early_clockout` tinyint(1) NOT NULL DEFAULT 0,
  `early_minutes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `employee_id`, `employee_type`, `employee_name`, `company_id`, `time_in`, `time_out`, `date`, `scheduled_start_time`, `scheduled_end_time`, `is_late`, `late_minutes`, `is_ot_without_pay`, `overtime_is_paid`, `ot_minutes`, `is_early_clockout`, `early_minutes`) VALUES
(4, 13, '222', 'head', 'Luke', 5, '2025-10-04 19:56:29', '2025-10-04 19:56:34', '2025-10-04', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(5, 19, '11023', 'head', 'hr', 6, '2025-10-05 13:18:52', '2025-10-05 13:19:01', '2025-10-05', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(6, 20, '11024', 'head', 'f', 6, '2025-10-06 21:18:58', '2025-10-06 21:43:02', '2025-10-06', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(9, -1, '5', 'general', 'rrrr', 6, '2025-10-07 03:32:08', '2025-10-07 03:41:04', '2025-10-06', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(12, 0, '6', 'general', NULL, 6, '2025-10-07 04:04:07', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(13, 0, '5', 'head', NULL, 6, '2025-10-07 04:04:53', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(16, 0, '4', NULL, 'fasd', 6, '2025-10-07 04:17:30', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(18, 0, '8', 'general', 'dfad', 6, '2025-10-07 04:23:37', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(19, 0, '8', 'general', 'dfad', 6, '2025-10-07 04:23:56', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(20, 0, '8', 'general', 'dfad', 6, '2025-10-07 04:24:21', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(25, -17, '0123', 'general', 'sya', 7, '2025-10-07 20:32:22', '2025-10-07 20:37:10', '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(27, -18, '0234', 'general', 'Daniel', 7, '2025-10-07 23:01:58', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(28, -19, '0321', 'general', 'L', 7, '2025-10-07 23:02:44', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(29, -21, '1', 'general', 'Josef', 8, '2025-10-08 00:08:31', NULL, '2025-10-07', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(30, -21, '1', 'general', 'Josef', 8, '2025-10-08 12:11:42', NULL, '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(31, -22, '2', 'general', 'Romnick', 8, '2025-10-08 12:17:04', '2025-10-08 12:17:21', '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(32, -23, '3', 'general', 'Kim', 8, '2025-10-08 12:23:06', '2025-10-08 12:29:19', '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(33, -26, '9', 'general', 'jej', 8, '2025-10-08 12:36:57', NULL, '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(34, -27, '12345', 'general', 'Jerem', 8, '2025-10-08 23:35:55', '2025-10-08 23:36:08', '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(35, -33, '12345', 'general', 'roven', 10, '2025-10-09 02:46:17', NULL, '2025-10-08', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(37, -18, '000', 'general', 'Dan', 7, '2025-10-21 00:50:46', NULL, '2025-10-20', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(38, -19, '001', 'general', 'Loki', 7, '2025-10-21 00:56:11', NULL, '2025-10-20', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(39, -10, '002', 'general', 'roven', 7, '2025-10-21 01:02:31', '2025-10-21 01:02:48', '2025-10-20', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(40, -18, '000', 'general', 'Dan', 7, '2025-10-21 19:39:54', '2025-10-21 19:44:40', '2025-10-21', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(41, -19, '001', 'general', 'Loki', 7, '2025-10-21 19:45:11', '2025-10-21 19:45:21', '2025-10-21', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(42, -10, '002', 'general', 'roven', 7, '2025-10-21 22:32:06', '2025-10-21 22:34:32', '2025-10-21', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(43, -17, '009', 'general', 'sya', 7, '2025-10-21 22:35:32', '2025-10-21 22:35:42', '2025-10-21', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(44, -18, '000', 'general', 'Dan', 7, '2025-10-28 19:11:45', '2025-10-28 19:11:56', '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(45, -12, '005', 'general', 'John', 7, '2025-10-28 19:13:04', '2025-10-28 19:13:11', '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(46, -19, '001', 'general', 'Loki', 7, '2025-10-28 20:23:21', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(47, 25, '001', '', 'j', 7, '2025-10-28 20:28:25', '2025-10-28 20:29:15', '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(48, -10, '002', 'general', 'roven', 7, '2025-10-28 20:28:52', '2025-10-28 20:29:20', '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(49, 29, '003', '', 'j2', 7, '2025-10-28 20:28:57', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(50, 42, '9999', '', 'asd', 7, '2025-10-28 20:44:10', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(51, 43, '789', '', 'jojo', 7, '2025-10-28 21:26:28', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(52, 44, '852', '', 'juju', 7, '2025-10-28 21:30:37', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(53, 45, '6767', 'head', 'ualatjr', 7, '2025-10-28 21:40:02', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(54, 46, '676767', 'head', 'nickjr', 7, '2025-10-28 21:41:50', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(55, 40, '2', 'head', 'finance', 7, '2025-10-28 21:57:05', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(56, -17, '009', 'general', 'sya', 7, '2025-10-28 21:57:23', NULL, '2025-10-28', NULL, NULL, 0, 0, 0, 0, 0, 0, 0),
(57, -18, '000', 'general', 'Dan', 7, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-11-11', '09:00:00', '18:00:00', 1, 706, 0, 0, 0, 0, 0),
(58, 25, '001', 'head', 'j', 7, '2025-11-11 22:25:05', '2025-11-11 22:25:55', '2025-11-11', '09:00:00', '18:00:00', 1, 806, 1, 0, 266, 0, 0),
(59, -37, '010', 'general', 'john', 7, '2025-11-26 17:22:35', '2025-11-26 17:24:32', '2025-11-26', '09:00:00', '18:00:00', 1, 503, 0, 0, 0, 1, 36),
(60, -10, '002', 'general', 'roven', 7, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-11-27', '09:00:00', '18:00:00', 1, 248, 0, 0, 0, 0, 0),
(61, 25, '001', 'head', 'j', 7, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2025-11-27', '09:00:00', '18:00:00', 1, 851, 0, 0, 0, 0, 0),
(65, -12, '005', 'general', 'Johnny', 7, '2025-11-28 01:08:18', '2025-11-28 01:10:25', '2025-11-28', '09:00:00', '18:00:00', 0, 0, 0, 0, 0, 1, 1010),
(66, 29, '003', 'head', 'j2', 7, '2025-11-28 07:12:58', NULL, '2025-11-28', '09:00:00', '18:00:00', 0, 0, 0, 0, 0, 0, 0),
(67, -37, '010', 'general', 'john', 7, '2025-11-28 07:16:45', '2025-11-28 07:19:20', '2025-11-28', '09:00:00', '18:00:00', 0, 0, 0, 0, 0, 1, 641),
(68, -10, '002', 'general', 'roven', 7, '2025-11-28 07:18:09', '2025-11-28 07:18:25', '2025-11-28', '09:00:00', '18:00:00', 0, 0, 0, 0, 0, 1, 642);

-- --------------------------------------------------------

--
-- Table structure for table `business_intelligence`
--

CREATE TABLE `business_intelligence` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `metric` varchar(100) NOT NULL,
  `value` decimal(12,2) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `code`, `name`, `email`) VALUES
(4, '123', 'Inasal', 'inasal@gmail.com'),
(5, '234', 'Mcdo', 'mcdo@gmail.com'),
(6, '1102', 'Funeral Services', 'fs@gmail.com'),
(7, '1111', 'Jollimcdo', 'Jollimcdo@gmail.com'),
(8, '1010', 'QCU', 'qcu@gmail.com'),
(9, '321', 'pup', 'pup@gmail.com'),
(10, '0101', 'www', 'www@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `finance`
--

CREATE TABLE `finance` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` enum('income','expense') NOT NULL DEFAULT 'expense'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `finance`
--

INSERT INTO `finance` (`id`, `company_id`, `amount`, `description`, `date`, `type`) VALUES
(14, 6, 10000.00, 'hatdog', '2025-10-07', 'income'),
(15, 6, 5000.00, 'hatdog', '2025-10-07', 'expense'),
(16, 6, 5000.00, 'hatdog', '2025-11-21', 'income'),
(30, 7, 10000.00, 'Romnick', '2025-09-09', 'expense'),
(33, 8, 1000.00, 'natalo sa scatter', '2025-10-08', 'income'),
(34, 10, 10000.00, 'natalo sa scatter', '2025-10-09', 'income'),
(35, 7, 6.00, 'Sale: Gummy (Qty: 3)', '2025-10-20', 'income'),
(36, 7, 500.00, 'Sale: Choco Cake (Qty: 5)', '2025-10-21', 'income'),
(37, 7, 246.00, 'POS Sale - Receipt: RC202511250209532803', '2025-11-25', 'income'),
(38, 7, 1110.00, 'POS Sale - Receipt: RC202511250230282303', '2025-11-25', 'income'),
(39, 7, 1665.00, 'POS Sale - Receipt: RC202511250230472775', '2025-11-25', 'income'),
(40, 7, 2775.00, 'POS Sale - Receipt: RC202511250231142924', '2025-11-25', 'income'),
(41, 7, 1710.00, 'POS Sale - Receipt: RC202511250314475032', '2025-11-25', 'income'),
(42, 7, 3510.00, 'POS Sale - Receipt: RC202511250316131395', '2025-11-25', 'income'),
(43, 7, 600.00, 'POS Sale - Receipt: RC202511250434386016', '2025-11-25', 'income'),
(44, 7, 27000.00, 'POS Sale - Receipt: RC202511250435562608', '2025-11-25', 'income'),
(45, 7, 17226.00, 'POS Sale - Receipt: RC202511250443072986', '2025-11-25', 'income'),
(46, 7, 55800.00, 'POS Sale - Receipt: RC202511250847568590', '2025-11-25', 'income'),
(47, 7, 2200.00, 'POS Sale - Receipt: RC202511250958447790', '2025-11-25', 'income'),
(48, 7, 600.00, 'POS Sale - Receipt: RC202511251036267668', '2025-11-25', 'income'),
(49, 7, 900.00, 'POS Sale - Receipt: RC202511251054037616', '2025-11-25', 'income'),
(50, 7, 600.00, 'POS Sale - Receipt: RC202511251110048932', '2025-11-25', 'income'),
(51, 7, 750.00, 'POS Sale - Receipt: RC202511251132265338', '2025-11-25', 'income'),
(52, 7, 900.00, 'POS Sale - Receipt: RC202511251133281566', '2025-11-25', 'income'),
(53, 7, 1000.00, 'POS Sale - Receipt: RC202511251157533949', '2025-11-25', 'income'),
(54, 7, 375.00, 'POS Sale - Receipt: RC202511251249225896', '2025-11-25', 'income'),
(55, 7, 2500.00, 'POS Sale - Receipt: POS-20251125210554-23', '2025-11-25', 'income'),
(56, 7, 2500.00, 'POS Sale - Receipt: POS-20251125210557-23', '2025-11-25', 'income'),
(57, 7, 1250.00, 'POS Sale - Receipt: POS-20251125235141-50', '2025-11-25', 'income'),
(58, 7, 1500.00, 'POS Sale - Receipt: RC202511252357270050', '2025-11-25', 'income'),
(59, 7, 1250.00, 'POS Sale - Receipt: RC202511252357490050', '2025-11-25', 'income'),
(60, 7, 250.00, 'POS Sale - Receipt: RC202511252359540023', '2025-11-25', 'income'),
(61, 7, 1000.00, 'POS Sale - Receipt: RC202511260002480023', '2025-11-26', 'income'),
(62, 7, 3250.00, 'POS Sale - Receipt: RC202511260206560050', '2025-11-26', 'income'),
(63, 7, 250.00, 'POS Sale - Receipt: RC202511270157060050', '2025-11-27', 'income'),
(64, 7, 250.00, 'POS Sale - Receipt: RC202511270208020023', '2025-11-27', 'income'),
(65, 7, 100.00, 'PM Expense cost - task \"PM\" / project \"Finals\"', '2025-11-27', 'expense'),
(66, 7, 500.00, 'PM Expense cost - task \"test 1\" / project \"Hotdog Sandwich\"', '2025-11-27', 'expense'),
(67, 7, 250.00, 'POS Sale - Receipt: RC202511270336200023', '2025-11-27', 'income'),
(68, 7, 20.00, 'POS Sale - Receipt: RC202511270400010023', '2025-11-27', 'income'),
(69, 7, 20.00, 'POS Sale - Receipt: RC202511270400380023', '2025-11-27', 'income'),
(70, 7, 20.00, 'POS Sale - Receipt: RC202511270405480023', '2025-11-27', 'income'),
(71, 7, 60.00, 'PM Material cost - task \"Creation\" / project \"Hotdog Sandwich\"', '2025-11-27', 'expense'),
(72, 7, 200.00, 'POS Sale - Receipt: RC202511270639590023', '2025-11-27', 'income'),
(73, 7, 250.00, 'POS Sale - Receipt: RC202511270653130023', '2025-11-27', 'income'),
(74, 7, 270.00, 'POS Sale - Receipt: RC202511270656430023', '2025-11-27', 'income'),
(75, 7, 270.00, 'POS Sale - Receipt: RC202511270657140023', '2025-11-27', 'income'),
(76, 7, 270.00, 'POS Sale - Receipt: RC202511270658110023', '2025-11-27', 'income'),
(77, 7, 270.00, 'POS Sale - Receipt: RC202511270701200023', '2025-11-27', 'income'),
(78, 7, 250.00, 'POS Sale - Receipt: RC202511271405490023', '2025-11-27', 'income'),
(79, 7, 270.00, 'POS Sale - Receipt: RC202511271411020023', '2025-11-27', 'income'),
(80, 7, 270.00, 'POS Sale - Receipt: RC202511271411060023', '2025-11-27', 'income'),
(81, 7, 270.00, 'POS Sale - Receipt: RC202511271411100023', '2025-11-27', 'income'),
(82, 7, 270.00, 'POS Sale - Receipt: RC202511271411140023', '2025-11-27', 'income'),
(83, 7, 270.00, 'POS Sale - Receipt: RC202511271411170023', '2025-11-27', 'income'),
(84, 7, 40.00, 'POS Sale - Receipt: RC202511271411490023', '2025-11-27', 'income'),
(85, 7, 100.00, 'POS Sale - Receipt: RC202511271540280050', '2025-11-27', 'income'),
(86, 7, 100.00, 'POS Sale - Receipt: RC202511271627550023', '2025-11-27', 'income'),
(87, 7, 100.00, 'POS Sale - Receipt: RC202511271630040023', '2025-11-27', 'income'),
(88, 7, 100.00, 'POS Sale - Receipt: RC202511271636210023', '2025-11-27', 'income'),
(89, 7, 470.00, 'POS Sale - Receipt: RC202511271637450023', '2025-11-27', 'income'),
(90, 7, 300.00, 'POS Sale - Receipt: RC202511271639260023', '2025-11-27', 'income'),
(91, 7, 300.00, 'POS Sale - Receipt: RC202511271639290023', '2025-11-27', 'income'),
(92, 7, 1000.00, 'POS Sale - Receipt: RC202511271641120023', '2025-11-27', 'income'),
(93, 7, 1000.00, 'POS Sale - Receipt: RC202511271641180023', '2025-11-27', 'income'),
(94, 7, 470.00, 'POS Sale - Receipt: RC202511271643110023', '2025-11-27', 'income'),
(95, 7, 2.40, 'Payroll: roven (002) • 2025-11-27 to 2025-11-28 • 0.02 hrs @ PHP 120.00/hr', '2025-11-28', 'expense'),
(96, 7, 0.20, 'Payroll: roven (002) • 2025-11-27 to 2025-11-29 • 0.02 hrs @ PHP 10.00/hr', '2025-11-29', 'expense'),
(97, 7, 100.00, 'PM Expense cost - task \"Creation\" / project \"Hotdog Sandwich\"', '2025-11-27', 'expense'),
(98, 7, 470.00, 'POS Sale - Receipt: RC202511271745090050', '2025-11-27', 'income'),
(99, 7, 500.00, 'POS Sale - Receipt: RC202511271747090023', '2025-11-27', 'income'),
(100, 7, 150.00, 'POS Sale - Receipt: RC202511280252560050', '2025-11-28', 'income'),
(101, 7, 1750.00, 'POS Sale - Receipt: RC202511280254060050', '2025-11-28', 'income'),
(102, 7, 50.00, 'POS Sale - Receipt: RC202511280301090050', '2025-11-28', 'income'),
(103, 7, 325.00, 'POS Sale - Receipt: RC202511280312330023', '2025-11-28', 'income'),
(104, 7, 45.00, 'POS Sale - Receipt: RC202511280328530023', '2025-11-28', 'income'),
(105, 7, 40000.00, 'Salt from Unknown supplier - PHP 40,000.00', '2025-11-28', 'expense'),
(106, 7, 2000.00, 'Salt from Daniel - PHP 2,000.00', '2025-11-28', 'expense'),
(107, 7, 75.00, 'POS Sale - Receipt: RC202511280353260023', '2025-11-28', 'income');

-- --------------------------------------------------------

--
-- Table structure for table `hr`
--

CREATE TABLE `hr` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `date_hired` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hr`
--

INSERT INTO `hr` (`id`, `company_id`, `employee_id`, `name`, `date_hired`, `created_at`) VALUES
(4, 6, '4', 'fasd', '2025-10-02', '2025-10-06 20:16:27'),
(6, 6, '10', 'asd', '2025-10-07', '2025-10-06 20:40:09'),
(7, 6, '69', 'jojo', '2025-10-07', '2025-10-06 20:45:54'),
(10, 7, '002', 'roven', '2025-10-07', '2025-10-06 21:59:39'),
(12, 7, '005', 'Johnny', '2025-10-07', '2025-10-07 00:27:17'),
(17, 7, '009', 'sya', '2025-10-07', '2025-10-07 12:31:47'),
(19, 7, '008', 'Loki', '2025-10-07', '2025-10-07 15:00:59'),
(21, 8, '1', 'Josef', '2025-10-08', '2025-10-07 16:08:04'),
(22, 8, '2', 'Romnick', '2025-11-08', '2025-10-08 02:35:49'),
(23, 8, '3', 'Kim', '2025-10-08', '2025-10-08 04:22:51'),
(25, 8, '5', 'Nick', '2025-10-08', '2025-10-08 04:35:15'),
(26, 8, '9', 'jej', '2025-10-08', '2025-10-08 04:36:30'),
(27, 8, '12345', 'Jerem', '2025-10-08', '2025-10-08 15:35:16'),
(31, 10, 'jej', '1', '2025-10-09', '2025-10-08 17:01:57'),
(32, 10, 'nick', '2', '2025-11-09', '2025-10-08 17:02:08'),
(33, 10, '12345', 'roven', '2025-10-09', '2025-10-08 18:46:05'),
(34, 8, '01', 'Sya', '2025-10-09', '2025-10-09 03:39:38'),
(35, 8, '02', 'Sha', '2025-10-09', '2025-10-09 04:02:24'),
(36, 8, '03', 'jowow', '2025-10-09', '2025-10-09 04:48:10'),
(37, 7, '010', 'john', '2025-11-17', '2025-11-17 05:15:13');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reorder_level` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `remarks` text DEFAULT NULL,
  `date_added` date DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `is_defective` tinyint(1) NOT NULL DEFAULT 0,
  `defective_reason` varchar(255) DEFAULT NULL,
  `defective_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `company_id`, `item_name`, `sku`, `quantity`, `reorder_level`, `unit`, `cost_price`, `selling_price`, `category`, `supplier_id`, `status`, `remarks`, `date_added`, `updated_at`, `is_defective`, `defective_reason`, `defective_at`) VALUES
(9, 7, 'Donut', '678', 51, 99, 'pcs', 50.00, 75.00, 'food', 71, 'Active', NULL, '2025-11-25', '2025-11-28 00:45:09', 0, NULL, NULL),
(10, 7, 'Chicken', '11000', 61, 95, 'kg', 150.00, 250.00, 'Food', 8, 'Active', NULL, '2025-11-25', '2025-11-28 00:45:09', 0, NULL, NULL),
(12, 7, 'Hotdog', '2', 83, 2, 'pack', 500.00, 25.00, 'Food', 123, 'Active', NULL, '2025-11-27', '2025-11-28 00:45:09', 0, NULL, NULL),
(13, 7, 'Cocoa', '3', 10, 12, 'pack', 100.00, 50.00, 'Food', 123, 'Active', NULL, '2025-11-27', '2025-11-27 22:40:28', 0, NULL, NULL),
(14, 7, 'Bread', '1', 1, 3, 'pcs', 500.00, 20.00, 'Food', 12, 'Active', NULL, '2025-11-27', NULL, 1, 'hi', '2025-11-27 11:07:28'),
(15, 7, 'Bread', '1', 1, 3, 'pcs', 500.00, 20.00, 'Food', 12, 'Active', NULL, '2025-11-27', NULL, 1, 'Marked as defective', '2025-11-27 11:07:42'),
(16, 7, 'Peanut', '3535', 10, 99, 'pcs', 150.00, 200.00, 'Food', 35, 'Active', NULL, '2025-11-25', NULL, 1, 'Marked as defective', '2025-11-27 13:02:32'),
(17, 7, 'Choco Choco', '4', 0, 100, 'pack', 50.00, 100.00, 'Food', 35, 'Active', NULL, '2025-11-27', '2025-11-28 10:12:33', 0, NULL, NULL),
(18, 7, 'Potato', '5', 95, 100, 'pcs', 100.00, 100.00, 'Food', 10, 'Active', NULL, '2025-11-27', '2025-11-28 00:47:09', 0, NULL, NULL),
(19, 7, 'Sugar', '6', 0, 100, 'g', 1000.00, 25.00, 'Food', 35, 'Active', NULL, '2025-11-28', '2025-11-28 10:53:26', 0, NULL, NULL),
(20, 7, 'Bread', '7', 49, 100, 'pcs', 100.00, 20.00, 'Food', 1, 'Active', NULL, '2025-11-28', '2025-11-28 10:28:53', 0, NULL, NULL),
(21, 7, 'Salt', '8', 99, 100, 'set', 20.00, 50.00, 'Food', 1, 'Active', NULL, '2025-11-28', '2025-11-28 10:53:26', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_bom`
--

CREATE TABLE `inventory_bom` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `output_qty` decimal(12,2) NOT NULL DEFAULT 1.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_bom`
--

INSERT INTO `inventory_bom` (`id`, `company_id`, `name`, `output_qty`, `created_at`) VALUES
(2, 7, 'test 2', 1.00, '2025-11-27 05:39:17'),
(3, 7, 'test 1', 1.00, '2025-11-27 23:45:47'),
(4, 7, 'test 3', 1.00, '2025-11-28 03:10:29');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_bom_items`
--

CREATE TABLE `inventory_bom_items` (
  `id` int(11) NOT NULL,
  `bom_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity_required` decimal(12,4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_bom_items`
--

INSERT INTO `inventory_bom_items` (`id`, `bom_id`, `inventory_id`, `quantity_required`, `created_at`) VALUES
(0, 0, 11, 2.0000, '2025-11-27 01:18:28'),
(0, 0, 12, 2.0000, '2025-11-27 01:18:28'),
(0, 0, 13, 2.0000, '2025-11-27 01:39:39'),
(0, 0, 13, 2.0000, '2025-11-27 01:42:37'),
(0, 2, 9, 1.0000, '2025-11-27 05:39:17'),
(0, 3, 17, 78.0000, '2025-11-27 23:45:47'),
(0, 4, 19, 23.0000, '2025-11-28 03:10:29');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint(20) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `change_qty` int(11) NOT NULL,
  `movement_type` enum('sale','refund','adjustment','production','transfer') NOT NULL,
  `ref_type` varchar(50) DEFAULT NULL,
  `ref_id` bigint(20) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_purchase_requests`
--

CREATE TABLE `inventory_purchase_requests` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `inventory_id` int(11) NOT NULL,
  `required_qty` decimal(12,4) NOT NULL,
  `request_type` varchar(50) NOT NULL DEFAULT 'shortage',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `note` varchar(255) DEFAULT NULL,
  `approval_token` varchar(64) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_purchase_requests`
--

INSERT INTO `inventory_purchase_requests` (`id`, `company_id`, `project_id`, `inventory_id`, `required_qty`, `request_type`, `status`, `note`, `approval_token`, `approved_at`, `created_at`) VALUES
(1, 7, 7, 13, 2.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 01:48:58'),
(2, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(3, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(4, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(5, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(6, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(7, 7, 8, 13, 1.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 02:10:10'),
(8, 7, 11, 17, 78.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-27 23:46:13'),
(9, 7, 12, 17, 56.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-28 00:10:25'),
(10, 7, 13, 17, 34.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-28 01:35:35'),
(11, 7, 14, 17, 59.0000, 'shortage', 'pending', 'Auto-generated from project allocation', NULL, NULL, '2025-11-28 02:21:39'),
(12, 7, 15, 19, 23.0000, 'Material Shortage', 'approved', 'Auto-generated from project allocation', '14b0b7af98320d1f45215e87811c4ba2', '2025-11-28 03:11:22', '2025-11-28 03:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `company_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `employee_id`, `company_id`, `pay_period_start`, `pay_period_end`, `base_salary`, `overtime_pay`, `allowances`, `deductions`, `net_salary`, `created_at`, `updated_at`) VALUES
(1, '1', 8, '2025-10-08', '2025-11-08', 10000.00, 3000.00, 0.00, 1.00, 12999.00, '2025-10-08 03:24:11', '2025-10-08 03:24:11'),
(2, '5', 8, '2025-10-08', '2025-10-08', 10000.00, 0.00, 0.00, 0.00, 10000.00, '2025-10-08 04:35:34', '2025-10-08 04:35:34'),
(3, '9', 8, '2025-10-08', '2025-10-08', 15000.00, 0.00, 0.00, 0.00, 15000.00, '2025-10-08 04:36:44', '2025-10-08 04:36:44'),
(6, '002', 7, '2025-11-27', '2025-11-28', 2.40, 0.00, 0.00, 0.00, 2.40, '2025-11-27 16:34:03', '2025-11-27 16:34:03'),
(7, '002', 7, '2025-11-27', '2025-11-29', 0.20, 0.00, 0.00, 0.00, 0.20, '2025-11-27 16:35:08', '2025-11-27 16:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `pm_assignments`
--

CREATE TABLE `pm_assignments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `allocation_percent` tinyint(3) UNSIGNED DEFAULT 100,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role_note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_assignments`
--

INSERT INTO `pm_assignments` (`id`, `task_id`, `resource_id`, `allocation_percent`, `assigned_at`, `role_note`) VALUES
(1, 1, 1, 100, '2025-10-21 17:58:22', '100'),
(3, 3, 3, 100, '2025-11-27 01:22:14', ''),
(4, 3, 1, 100, '2025-11-27 01:28:03', 'Team Leader'),
(6, 2, 3, 100, '2025-11-27 04:49:08', ''),
(7, 2, 1, 100, '2025-11-27 16:40:08', 'Team Leader'),
(8, 11, 1, 100, '2025-11-27 16:40:08', 'Team Leader');

-- --------------------------------------------------------

--
-- Table structure for table `pm_costs`
--

CREATE TABLE `pm_costs` (
  `id` int(11) NOT NULL,
  `task_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `cost_type` enum('labor','expense','material','other') DEFAULT 'other',
  `cost_date` date DEFAULT curdate(),
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_costs`
--

INSERT INTO `pm_costs` (`id`, `task_id`, `project_id`, `amount`, `cost_type`, `cost_date`, `note`, `created_at`) VALUES
(1, 1, 1, 0.00, 'labor', '2025-10-22', 'Finish', '2025-10-21 17:33:20'),
(2, 1, 1, 100.00, 'material', '2025-10-22', 'Finish', '2025-10-21 17:34:07'),
(3, 1, 1, 55.00, 'expense', '2025-10-21', 'INC', '2025-10-21 17:57:22'),
(4, 1, 1, 23423.00, 'labor', '2025-10-28', '23445', '2025-10-28 11:03:05'),
(5, 2, 1, 2323.00, 'material', '2025-10-28', '2323', '2025-10-28 14:00:34'),
(6, 2, 2, 123213.00, 'expense', '2025-10-28', 'CUTIEEE', '2025-10-28 14:09:59'),
(9, 3, 2, 150000.00, 'expense', '2025-11-17', '', '2025-11-16 17:54:20'),
(12, 11, 9, 60.00, 'material', '2025-11-27', '', '2025-11-27 04:56:49'),
(13, 11, 9, 100.00, 'expense', '2025-11-27', '', '2025-11-27 16:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `pm_project_materials`
--

CREATE TABLE `pm_project_materials` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `bom_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `required_qty` decimal(12,4) NOT NULL,
  `allocated_qty` decimal(12,4) NOT NULL,
  `shortage_qty` decimal(12,4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_project_materials`
--

INSERT INTO `pm_project_materials` (`id`, `company_id`, `project_id`, `bom_id`, `inventory_id`, `required_qty`, `allocated_qty`, `shortage_qty`, `created_at`) VALUES
(22, 7, 9, 1, 11, 2.0000, 2.0000, 0.0000, '2025-11-27 04:48:18'),
(23, 7, 9, 1, 12, 2.0000, 2.0000, 0.0000, '2025-11-27 04:48:18'),
(24, 7, 10, 1, 11, 2.0000, 2.0000, 0.0000, '2025-11-27 16:36:12'),
(25, 7, 10, 1, 12, 2.0000, 2.0000, 0.0000, '2025-11-27 16:36:12'),
(29, 7, 14, 3, 17, 156.0000, 97.0000, 59.0000, '2025-11-28 02:21:39'),
(30, 7, 15, 4, 19, 46.0000, 23.0000, 23.0000, '2025-11-28 03:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `pm_project_meta`
--

CREATE TABLE `pm_project_meta` (
  `project_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `planned_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `budget_threshold` tinyint(3) UNSIGNED NOT NULL DEFAULT 80,
  `deadline_buffer_days` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `auto_completion` decimal(5,2) NOT NULL DEFAULT 0.00,
  `manual_completion` decimal(5,2) DEFAULT NULL,
  `reminder_opt_in` tinyint(1) NOT NULL DEFAULT 1,
  `last_auto_sync` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_project_meta`
--

INSERT INTO `pm_project_meta` (`project_id`, `company_id`, `planned_budget`, `budget_threshold`, `deadline_buffer_days`, `auto_completion`, `manual_completion`, `reminder_opt_in`, `last_auto_sync`, `created_at`, `updated_at`) VALUES
(1, 7, 50000.00, 50, 3, 100.00, NULL, 1, NULL, '2025-11-16 15:28:13', '2025-11-27 15:01:41'),
(2, 7, 0.00, 80, 3, 50.00, NULL, 1, NULL, '2025-11-16 15:28:13', '2025-11-27 16:41:22'),
(9, 7, 100.00, 50, 1, 0.00, NULL, 1, NULL, '2025-11-27 04:48:19', '2025-11-27 04:56:37'),
(10, 7, 0.00, 80, 3, 0.00, NULL, 1, NULL, '2025-11-27 16:36:12', '2025-11-27 16:36:12'),
(14, 7, 0.00, 80, 3, 0.00, NULL, 1, NULL, '2025-11-28 02:21:43', '2025-11-28 02:21:43'),
(15, 7, 0.00, 80, 3, 0.00, NULL, 1, NULL, '2025-11-28 03:10:43', '2025-11-28 03:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `pm_resources`
--

CREATE TABLE `pm_resources` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_resources`
--

INSERT INTO `pm_resources` (`id`, `company_id`, `name`, `role`, `hourly_rate`, `created_at`) VALUES
(1, 7, 'Laynes', 'PM', 100.00, '2025-10-21 17:34:29'),
(2, 7, 'Romnick', 'Head', 23.00, '2025-10-28 14:00:59'),
(3, 7, 'Daniel', 'Secretary', 20.00, '2025-11-16 15:45:43'),
(4, 7, 'Mark', 'Designer', 100.00, '2025-11-27 23:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `pm_tasks`
--

CREATE TABLE `pm_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `estimated_hours` decimal(8,2) DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_tasks`
--

INSERT INTO `pm_tasks` (`id`, `project_id`, `title`, `description`, `status`, `priority`, `estimated_hours`, `due_date`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'System Title', 'System for SAD', 'done', 'medium', 22.00, '2025-10-22', 23, '2025-10-21 17:32:01', '2025-11-17 16:26:37'),
(2, 2, 'BLE', 'ASDASD', 'in_progress', 'low', 23.00, '2025-10-28', 23, '2025-10-28 14:00:04', '2025-11-27 04:49:10'),
(3, 2, 'BLE', 'QWEASDGWQE', 'done', 'medium', 234.00, '2025-10-08', 23, '2025-10-28 14:10:22', '2025-11-27 16:41:22'),
(11, 9, 'Creation', '', 'in_progress', 'low', 10.00, '2025-11-29', 23, '2025-11-27 04:48:50', '2025-11-27 04:49:18');

-- --------------------------------------------------------

--
-- Table structure for table `pm_time_entries`
--

CREATE TABLE `pm_time_entries` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `hours` decimal(6,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pm_time_entries`
--

INSERT INTO `pm_time_entries` (`id`, `task_id`, `resource_id`, `entry_date`, `hours`, `notes`, `created_at`) VALUES
(1, 2, 1, '2025-10-28', 23.00, 'asdfasfd', '2025-10-28 14:00:15'),
(2, 1, 2, '2025-11-16', 5.00, '', '2025-11-16 15:35:08'),
(5, 3, 1, '2025-11-27', 10.00, '', '2025-11-27 16:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `pos_counters`
--

CREATE TABLE `pos_counters` (
  `company_id` int(11) NOT NULL,
  `last_no` bigint(20) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_items`
--

CREATE TABLE `pos_items` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `qty` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_payments`
--

CREATE TABLE `pos_payments` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `method` varchar(64) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `details` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_sales`
--

CREATE TABLE `pos_sales` (
  `id` bigint(20) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `terminal_id` varchar(50) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `discount` decimal(14,2) DEFAULT 0.00,
  `tax` decimal(14,2) DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL,
  `status` enum('completed','pending','voided','refunded') DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_sales_items`
--

CREATE TABLE `pos_sales_items` (
  `id` bigint(20) NOT NULL,
  `pos_sale_id` bigint(20) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(14,2) NOT NULL,
  `discount` decimal(14,2) DEFAULT 0.00,
  `line_total` decimal(14,2) NOT NULL,
  `cost` decimal(14,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_transactions`
--

CREATE TABLE `pos_transactions` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(64) NOT NULL,
  `company_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(32) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planned','active','completed','on_hold','cancelled') DEFAULT 'planned',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `company_id`, `name`, `description`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, 'SAD', '', '2025-10-22', '2025-10-30', 'planned', 23, '2025-10-21 17:30:07', '2025-10-21 17:30:07'),
(2, 7, 'GOO', '', '2025-10-28', '2025-10-29', 'planned', 23, '2025-10-28 13:59:09', '2025-10-28 13:59:09'),
(9, 7, 'Hotdog Sandwich', '', '2025-11-27', '2025-11-28', 'planned', 23, '2025-11-27 04:48:18', '2025-11-27 04:48:18'),
(10, 7, 'Hotdog Sandwich', '', NULL, NULL, 'planned', 23, '2025-11-27 16:36:12', '2025-11-27 16:36:12'),
(14, 7, 'test 1', '', NULL, NULL, 'planned', 23, '2025-11-28 02:21:39', '2025-11-28 02:21:39'),
(15, 7, 'test 2', '', NULL, NULL, 'planned', 23, '2025-11-28 03:10:40', '2025-11-28 03:10:40');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `product` varchar(100) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `date_sold` date DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `company_id`, `product`, `product_name`, `quantity`, `price`, `date_sold`, `total_price`, `date`) VALUES
(2, 6, 'Kangkong chips', NULL, 22, 429.00, '2025-10-07', NULL, NULL),
(3, 6, 'carrot chips', NULL, 87, 20.00, '2025-10-07', NULL, NULL),
(9, 7, 'burger', NULL, 100, 500.00, '2025-10-07', NULL, NULL),
(11, 7, 'bbq', NULL, 200, 25.00, '2025-09-07', NULL, NULL),
(12, 7, 'tinapay', NULL, 20, 2.00, '2025-09-07', NULL, NULL),
(14, 7, 'Popsicle', NULL, 100, 1000.00, '2025-10-20', NULL, NULL),
(15, 7, 'Hotdog', NULL, 100, 5000.00, '2025-10-20', NULL, NULL),
(16, 7, 'Gummy', NULL, 3, 2.00, '2025-10-20', NULL, NULL),
(18, 7, '0', 'kangkong chips', 2, 123.00, '2025-11-25', 246.00, '2025-11-25'),
(19, 7, '0', 'kangkong chips', 2, 555.00, '2025-11-25', 1110.00, '2025-11-25'),
(20, 7, '0', 'kangkong chips', 3, 555.00, '2025-11-25', 1665.00, '2025-11-25'),
(21, 7, '0', 'ballpe', 5, 555.00, '2025-11-25', 2775.00, '2025-11-25'),
(22, 7, '4', 'chiicken', 2, 300.00, '2025-11-25', 600.00, '2025-11-25'),
(23, 7, '1', 'andoks', 2, 555.00, '2025-11-25', 1110.00, '2025-11-25'),
(24, 7, '4', 'chiicken', 8, 300.00, '2025-11-25', 2400.00, '2025-11-25'),
(25, 7, '1', 'andoks', 2, 555.00, '2025-11-25', 1110.00, '2025-11-25'),
(26, 7, '5', 'Kuyukot', 2, 300.00, '2025-11-25', 600.00, '2025-11-25'),
(27, 7, '4', 'chiicken', 90, 300.00, '2025-11-25', 27000.00, '2025-11-25'),
(28, 7, '6', 'kangkong chips', 6, 2521.00, '2025-11-25', 15126.00, '2025-11-25'),
(29, 7, '5', 'Kuyukot', 7, 300.00, '2025-11-25', 2100.00, '2025-11-25'),
(30, 7, '5', 'Kuyukot', 186, 300.00, '2025-11-25', 55800.00, '2025-11-25'),
(31, 7, '6', 'Choco', 11, 200.00, '2025-11-25', 2200.00, '2025-11-25'),
(32, 7, '5', 'Kuyukot', 2, 300.00, '2025-11-25', 600.00, '2025-11-25'),
(33, 7, '5', 'Kuyukot', 3, 300.00, '2025-11-25', 900.00, '2025-11-25'),
(34, 7, '5', 'Kuyukot', 2, 300.00, '2025-11-25', 600.00, '2025-11-25'),
(35, 7, '7', 'Butter', 5, 150.00, '2025-11-25', 750.00, '2025-11-25'),
(36, 7, '5', 'Kuyukot', 3, 300.00, '2025-11-25', 900.00, '2025-11-25'),
(37, 7, '8', 'Peanut', 5, 200.00, '2025-11-25', 1000.00, '2025-11-25'),
(38, 7, '9', 'Donut', 5, 75.00, '2025-11-25', 375.00, '2025-11-25'),
(39, 7, 'Chicken', 'Chicken', 10, 250.00, '2025-11-25', 2500.00, '2025-11-25'),
(40, 7, 'Chicken', 'Chicken', 10, 250.00, '2025-11-25', 2500.00, '2025-11-25'),
(41, 7, 'Chicken', 'Chicken', 5, 250.00, '2025-11-25', 1250.00, '2025-11-25'),
(42, 7, '10', 'Chicken', 4, 250.00, '2025-11-26', 1000.00, '2025-11-26'),
(43, 7, '10', 'Chicken', 13, 250.00, '2025-11-26', 3250.00, '2025-11-26'),
(44, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(45, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(46, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(47, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(48, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(49, 7, '8', 'Peanut', 1, 200.00, '2025-11-27', 200.00, '2025-11-27'),
(50, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(51, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(52, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(53, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(54, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(55, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(56, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(57, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(58, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(59, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(60, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(61, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(62, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(63, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(64, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(65, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(66, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(67, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(68, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(69, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(70, 7, '11', 'Bread', 2, 20.00, '2025-11-27', 40.00, '2025-11-27'),
(71, 7, '13', 'Cocoa', 2, 50.00, '2025-11-27', 100.00, '2025-11-27'),
(72, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(73, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(74, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(75, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(76, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(77, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(78, 7, '9', 'Donut', 1, 75.00, '2025-11-27', 75.00, '2025-11-27'),
(79, 7, '12', 'Hotdog', 1, 25.00, '2025-11-27', 25.00, '2025-11-27'),
(80, 7, '17', 'Choco Choco', 3, 100.00, '2025-11-27', 300.00, '2025-11-27'),
(81, 7, '17', 'Choco Choco', 3, 100.00, '2025-11-27', 300.00, '2025-11-27'),
(82, 7, '17', 'Choco Choco', 10, 100.00, '2025-11-27', 1000.00, '2025-11-27'),
(83, 7, '17', 'Choco Choco', 10, 100.00, '2025-11-27', 1000.00, '2025-11-27'),
(84, 7, '12', 'Hotdog', 1, 25.00, '2025-11-27', 25.00, '2025-11-27'),
(85, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(86, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(87, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(88, 7, '9', 'Donut', 1, 75.00, '2025-11-27', 75.00, '2025-11-27'),
(89, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-27', 100.00, '2025-11-27'),
(90, 7, '9', 'Donut', 1, 75.00, '2025-11-27', 75.00, '2025-11-27'),
(91, 7, '10', 'Chicken', 1, 250.00, '2025-11-27', 250.00, '2025-11-27'),
(92, 7, '11', 'Bread', 1, 20.00, '2025-11-27', 20.00, '2025-11-27'),
(93, 7, '12', 'Hotdog', 1, 25.00, '2025-11-27', 25.00, '2025-11-27'),
(94, 7, '18', 'Potato', 5, 100.00, '2025-11-27', 500.00, '2025-11-27'),
(95, 7, '19', 'Sugar', 2, 25.00, '2025-11-28', 50.00, '2025-11-28'),
(96, 7, '17', 'Choco Choco', 1, 100.00, '2025-11-28', 100.00, '2025-11-28'),
(98, 7, '19', 'Sugar', 70, 25.00, '2025-11-28', 1750.00, '2025-11-28'),
(99, 7, '19', 'Sugar', 2, 25.00, '2025-11-28', 50.00, '2025-11-28'),
(100, 7, '19', 'Sugar', 1, 25.00, '2025-11-28', 25.00, '2025-11-28'),
(101, 7, '17', 'Choco Choco', 3, 100.00, '2025-11-28', 300.00, '2025-11-28'),
(102, 7, '20', 'Bread', 1, 20.00, '2025-11-28', 20.00, '2025-11-28'),
(103, 7, '19', 'Sugar', 1, 25.00, '2025-11-28', 25.00, '2025-11-28'),
(104, 7, '21', 'Salt', 1, 50.00, '2025-11-28', 50.00, '2025-11-28'),
(105, 7, '19', 'Sugar', 1, 25.00, '2025-11-28', 25.00, '2025-11-28');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `date` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `type` enum('Revenue','Expense') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'employee',
  `employee_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `username`, `password`, `role`, `employee_id`) VALUES
(23, 7, 'jiji', '$2y$10$vh5VLPGiyVI6T77JbE8w/u5sK79yXI0FZGYpH5WqSZ00g/oSXJy5u', 'admin', NULL),
(25, 7, 'j', '$2y$10$z3N/IkRm4avSYfSb6LrDNedxHWaHzXslrrcPCjwAeicpoF9VRdTRe', 'head_hr', '001'),
(29, 7, 'j2', '$2y$10$XqPfHRmQvble/V1Ot1/FmOU.ElsQmQKUqKafTA5yDUNDbRHBWv1B6', 'head_sales', '003'),
(30, 7, 'Big', '$2y$10$8J4R73akplrDsxWk032P2eNTHGmgc3EJIwOvTpojNNRIadllD0qpK', 'head_inventory', '004'),
(31, 8, 'qcuadmin', '$2y$10$3fRtoJjfrC3igh1C0TiGzud7f55hzmPBhRddoMqhoko9lcPJJdFd6', 'admin', NULL),
(32, 8, 'Romnick', '$2y$10$6GP76exRHqQmez67NXG.2ucDfJJWzlekfRULc0o.TMbSwYe98VCo6', 'head_hr', '111'),
(33, 9, 'pupadmin', '$2y$10$UMdWBfHfRCE3leVyzh4zDe/z/Tge/h5atgVYCxnpsYjCx76abONKG', 'admin', NULL),
(35, 8, 'mik', '$2y$10$dn0Zm5TpYXEG60Jli2.hO.yL7.honax21eJWEYGGOXfzT2lmbOUkq', 'head_finance', '333'),
(36, 8, 'Sam', '$2y$10$EJrnk4J.gvDWJYNi1Y2B2eq.0puvwQFlA3JHSm8.4fVbG.i0ElXce', 'head_sales', '444'),
(37, 8, 'Laynes', '$2y$10$/mfxjLuUuCWGb3oFG1nvsOFYxa8.Zw5fA5CbHjCIS2cZlXhsCG.Im', 'head_inventory', '555'),
(38, 10, 'www', '$2y$10$eWqDw8Vu6RrtzKXIpKLvNu8eRyyE8omSmDj.iyAim3lGqYXVrvANW', 'admin', NULL),
(39, 8, '02', '$2y$10$BG3hT2InLY1unFMGwpi1R.9wnJgTLXcKFGPbqD4nAv/zrP9Fy91oW', 'hr_employee', '02'),
(40, 7, 'finance', '$2y$10$W2IbYrRZ1Z4qohaaOsGcye1sUsYesxTdwot.2vMzYN.Jhxo/PaLe6', 'head_finance', '2'),
(42, 7, 'asd', '$2y$10$RaJjUFt6vC1EVTpNva1OlO1BM6HzJ60UvZCjhPnEeFuow7Jx35RC.', 'head_finance', '9999'),
(43, 7, 'jojo', '$2y$10$c3mEeYOsF08BwobFH29lHOjRzRMq2ZecigBzdn.5GkNWdVjnSwO3W', 'head_sales', '789'),
(44, 7, 'juju', '$2y$10$Ge7vOWeqM8j.XVtmdpeN9emIVm3ak4O1s6bFx1fDOAfJCFO0UrL12', 'head_finance', '852'),
(45, 7, 'ualatjr', '$2y$10$yjYq4kkqfRqi07rEQ4KM0u.U9aPCKjuPy19WiyTzBXcJhjusKJlRu', 'head_sales', '6767'),
(46, 7, 'nickjr', '$2y$10$Q92SXiFMyfeLIVN0VIDHxe7sysg6ftKzqStY7YqXWkiLKCOfRxcI2', 'head_finance', '676767'),
(50, 7, 'POS', '$2y$10$0QMQN3eDUPYxUXICONaNTu45w2BQQrPgZkm3papZQgFTlT/zUj5je', 'head_pos', '124'),
(53, 7, 'Big Boy', '$2y$10$z11EQpGWYe4O4/E.gv80oe.b6ElqQOjOxbimxPI6MYAJyXUdnWmDy', 'head_pos', '00'),
(54, 7, 'Boy', '$2y$10$GiMjaVsWqjUa0LkOPenlN.MIQZYk47foz7Mh3XeLraouZ5/ETJL72', 'head_hr', '000');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `company_id`, `vendor_name`, `email`, `contact_number`, `address`, `created_at`, `supplier_id`) VALUES
(11, 7, 'MCDO Supplier', 'idanan.romnick.vendiola31@gmail.com', '09481187589', '1231231', '2025-11-26 01:06:25', 8),
(12, 7, 'Bossing', 'evangelistalirio30@gmail.com', '023435465789', 'taga dyan sa tabi', '2025-11-27 14:38:53', 123),
(13, 7, 'John', 'ualat.johndaniel.katapang@gmail.com', '023435465789', 'QCU', '2025-11-27 14:56:06', 35),
(14, 7, 'Sef', 'casangcapanjosef13@gmail.com', '023435465789', 'Naku po', '2025-11-27 16:46:20', 10),
(15, 7, 'Daniel', 'johndanielualat@gmail.com', '023435465789', 'Caloocan', '2025-11-28 02:26:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `scheduled_start_time` time NOT NULL,
  `scheduled_end_time` time NOT NULL,
  `allow_paid_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`id`, `company_id`, `employee_id`, `scheduled_start_time`, `scheduled_end_time`, `allow_paid_overtime`, `created_at`) VALUES
(1, 7, NULL, '09:00:00', '18:00:00', 0, '2025-11-11 12:40:07'),
(2, 6, NULL, '08:00:00', '17:00:00', 0, '2025-11-11 12:40:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_module_timestamp` (`company_id`,`module`,`timestamp`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `business_intelligence`
--
ALTER TABLE `business_intelligence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bi_company` (`company_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `finance`
--
ALTER TABLE `finance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_finance_company` (`company_id`);

--
-- Indexes for table `hr`
--
ALTER TABLE `hr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_emp_id_company` (`employee_id`,`company_id`),
  ADD KEY `idx_hr_company` (`company_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_company` (`company_id`);

--
-- Indexes for table `inventory_bom`
--
ALTER TABLE `inventory_bom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inventory_bom_company` (`company_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_id` (`inventory_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `inventory_purchase_requests`
--
ALTER TABLE `inventory_purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_purchase_project` (`project_id`),
  ADD KEY `idx_purchase_inventory` (`inventory_id`),
  ADD KEY `idx_purchase_company` (`company_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `payroll_ibfk_2` (`employee_id`);

--
-- Indexes for table `pm_assignments`
--
ALTER TABLE `pm_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `pm_costs`
--
ALTER TABLE `pm_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `pm_project_materials`
--
ALTER TABLE `pm_project_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pm_material_project` (`project_id`),
  ADD KEY `idx_pm_material_inventory` (`inventory_id`),
  ADD KEY `idx_pm_material_company` (`company_id`);

--
-- Indexes for table `pm_project_meta`
--
ALTER TABLE `pm_project_meta`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `idx_company` (`company_id`);

--
-- Indexes for table `pm_resources`
--
ALTER TABLE `pm_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `pm_tasks`
--
ALTER TABLE `pm_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `pm_time_entries`
--
ALTER TABLE `pm_time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `pos_counters`
--
ALTER TABLE `pos_counters`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `pos_items`
--
ALTER TABLE `pos_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_pos_items_inventory` (`inventory_id`);

--
-- Indexes for table `pos_payments`
--
ALTER TABLE `pos_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_company_invoice` (`company_id`,`invoice_no`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `pos_sales_items`
--
ALTER TABLE `pos_sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pos_sale_id` (`pos_sale_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `pos_transactions`
--
ALTER TABLE `pos_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `idx_pos_transactions_company` (`company_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sales_company` (`company_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendors_company` (`company_id`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_employee` (`company_id`,`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=361;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `business_intelligence`
--
ALTER TABLE `business_intelligence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `finance`
--
ALTER TABLE `finance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `hr`
--
ALTER TABLE `hr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_purchase_requests`
--
ALTER TABLE `inventory_purchase_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pm_assignments`
--
ALTER TABLE `pm_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pm_costs`
--
ALTER TABLE `pm_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pm_project_materials`
--
ALTER TABLE `pm_project_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `pm_resources`
--
ALTER TABLE `pm_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pm_tasks`
--
ALTER TABLE `pm_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pm_time_entries`
--
ALTER TABLE `pm_time_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pos_items`
--
ALTER TABLE `pos_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_payments`
--
ALTER TABLE `pos_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_sales`
--
ALTER TABLE `pos_sales`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_sales_items`
--
ALTER TABLE `pos_sales_items`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_transactions`
--
ALTER TABLE `pos_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `business_intelligence`
--
ALTER TABLE `business_intelligence`
  ADD CONSTRAINT `business_intelligence_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `finance`
--
ALTER TABLE `finance`
  ADD CONSTRAINT `finance_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hr`
--
ALTER TABLE `hr`
  ADD CONSTRAINT `hr_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_bom`
--
ALTER TABLE `inventory_bom`
  ADD CONSTRAINT `inventory_bom_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_purchase_requests`
--
ALTER TABLE `inventory_purchase_requests`
  ADD CONSTRAINT `inventory_purchase_requests_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_project_materials`
--
ALTER TABLE `pm_project_materials`
  ADD CONSTRAINT `pm_project_materials_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_project_meta`
--
ALTER TABLE `pm_project_meta`
  ADD CONSTRAINT `pm_project_meta_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_resources`
--
ALTER TABLE `pm_resources`
  ADD CONSTRAINT `pm_resources_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_counters`
--
ALTER TABLE `pos_counters`
  ADD CONSTRAINT `pos_counters_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD CONSTRAINT `pos_sales_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_transactions`
--
ALTER TABLE `pos_transactions`
  ADD CONSTRAINT `pos_transactions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `work_schedules_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `hr` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_assignments`
--
ALTER TABLE `pm_assignments`
  ADD CONSTRAINT `pm_assignments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `pm_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pm_assignments_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `pm_resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_costs`
--
ALTER TABLE `pm_costs`
  ADD CONSTRAINT `pm_costs_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `pm_tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pm_costs_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pm_tasks`
--
ALTER TABLE `pm_tasks`
  ADD CONSTRAINT `pm_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pm_time_entries`
--
ALTER TABLE `pm_time_entries`
  ADD CONSTRAINT `pm_time_entries_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `pm_tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pm_time_entries_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `pm_resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_items`
--
ALTER TABLE `pos_items`
  ADD CONSTRAINT `pos_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_payments`
--
ALTER TABLE `pos_payments`
  ADD CONSTRAINT `pos_payments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `pos_transactions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pos_sales_items`
--
ALTER TABLE `pos_sales_items`
  ADD CONSTRAINT `pos_sales_items_ibfk_1` FOREIGN KEY (`pos_sale_id`) REFERENCES `pos_sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
