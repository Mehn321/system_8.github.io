-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 02:55 PM
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
-- Database: `borrowing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `admin_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'CREATE', 'borrowing_transactions', 1, NULL, '{\"borrower_id\": 1, \"activity_purpose\": \"basta \"}', NULL, NULL, '2025-10-02 12:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password_hash`, `email`, `full_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@system.com', 'System Administrator', '2025-09-21 08:28:32', '2025-10-02 12:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `approval_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `approval_type` enum('issued_by','assessed_received_by','noted_by') NOT NULL,
  `admin_id` int(11) NOT NULL,
  `approval_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approvals`
--

INSERT INTO `approvals` (`approval_id`, `transaction_id`, `approval_type`, `admin_id`, `approval_date`, `remarks`, `created_at`) VALUES
(1, 1, 'issued_by', 1, '2025-10-02 12:51:12', NULL, '2025-10-02 12:51:12'),
(2, 1, 'assessed_received_by', 1, '2025-10-02 12:51:17', NULL, '2025-10-02 12:51:17'),
(3, 1, 'noted_by', 1, '2025-10-02 12:51:33', NULL, '2025-10-02 12:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_items`
--

CREATE TABLE `borrowed_items` (
  `borrowed_item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_required` int(11) NOT NULL,
  `quantity_issued` int(11) NOT NULL DEFAULT 0,
  `quantity_returned` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `borrowed_items`
--
DELIMITER $$
CREATE TRIGGER `update_item_quantity_on_borrow` AFTER INSERT ON `borrowed_items` FOR EACH ROW BEGIN
    UPDATE items
    SET available_quantity = available_quantity - NEW.quantity_issued
    WHERE item_id = NEW.item_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_item_quantity_on_return` AFTER UPDATE ON `borrowed_items` FOR EACH ROW BEGIN
    IF NEW.quantity_returned > OLD.quantity_returned THEN
        UPDATE items
        SET available_quantity = available_quantity + (NEW.quantity_returned - OLD.quantity_returned)
        WHERE item_id = NEW.item_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `borrower_id` int(11) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department_course_office` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowers`
--

INSERT INTO `borrowers` (`borrower_id`, `id_number`, `full_name`, `department_course_office`, `contact_number`, `email_address`, `created_at`, `updated_at`) VALUES
(1, 'ajhds', 'kdgakgdks', 'djak', '09674185880', 'aclo@gmail.com', '2025-09-21 09:35:37', '2025-09-21 09:35:37');

-- --------------------------------------------------------

--
-- Table structure for table `borrowing_transactions`
--

CREATE TABLE `borrowing_transactions` (
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `activity_purpose` varchar(255) NOT NULL,
  `place_of_activity` varchar(100) NOT NULL,
  `date_requested` date NOT NULL,
  `date_needed` date NOT NULL,
  `date_of_return` date NOT NULL,
  `status` enum('pending','approved','issued','returned','overdue','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowing_transactions`
--

INSERT INTO `borrowing_transactions` (`transaction_id`, `borrower_id`, `activity_purpose`, `place_of_activity`, `date_requested`, `date_needed`, `date_of_return`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'basta ', 'dagat', '2025-10-02', '2025-10-15', '2025-10-23', 'returned', '2025-10-02 12:51:04', '2025-10-02 12:51:33');

--
-- Triggers `borrowing_transactions`
--
DELIMITER $$
CREATE TRIGGER `log_admin_activity` AFTER INSERT ON `borrowing_transactions` FOR EACH ROW BEGIN
    INSERT INTO activity_logs (admin_id, action, table_name, record_id, new_values)
    VALUES (1, 'CREATE', 'borrowing_transactions', NEW.transaction_id,
            JSON_OBJECT('borrower_id', NEW.borrower_id, 'activity_purpose', NEW.activity_purpose));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `total_quantity` int(11) NOT NULL DEFAULT 0,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `idx_items_category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(2, 'Furniture', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(3, 'Supplies', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(4, 'Other', '2025-09-21 08:28:32', '2025-09-21 08:28:32');

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_code`, `item_name`, `item_description`, `category_id`, `total_quantity`, `available_quantity`, `unit`, `created_at`, `updated_at`) VALUES
(1, 'LAPTOP001', 'Dell Laptop', 'Dell Inspiron 15 3000 Series', 1, 10, 10, 'pieces', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(2, 'PROJECTOR001', 'Epson Projector', 'Epson EB-S41 SVGA Projector', 1, 5, 5, 'pieces', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(3, 'WHITEBOARD001', 'Whiteboard', 'Standard Whiteboard 4x6 feet', 2, 8, 8, 'pieces', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(4, 'MARKER001', 'Whiteboard Markers', 'Set of colored whiteboard markers', 3, 50, 50, 'sets', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(5, '03704e21', 'pampers', 'basta', 4, 82, 82, 'pcs', '2025-09-21 09:36:30', '2025-09-21 09:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_description`, `created_at`, `updated_at`) VALUES
(1, 'max_items_per_transaction', '5', 'Maximum number of items a borrower can request per transaction', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(2, 'max_borrowing_days', '7', 'Maximum number of days items can be borrowed', '2025-09-21 08:28:32', '2025-09-21 08:28:32'),
(3, 'overdue_notice_days', '3', 'Number of days before due date to send notification', '2025-09-21 08:28:32', '2025-09-21 08:28:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_logs_admin` (`admin_id`),
  ADD KEY `idx_logs_created` (`created_at`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD UNIQUE KEY `unique_transaction_approval_type` (`transaction_id`,`approval_type`),
  ADD KEY `idx_approvals_transaction` (`transaction_id`),
  ADD KEY `idx_approvals_admin` (`admin_id`);

--
-- Indexes for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD PRIMARY KEY (`borrowed_item_id`),
  ADD UNIQUE KEY `unique_transaction_item` (`transaction_id`,`item_id`),
  ADD KEY `idx_borrowed_items_transaction` (`transaction_id`),
  ADD KEY `idx_borrowed_items_item` (`item_id`);

--
-- Indexes for table `borrowers`
--
ALTER TABLE `borrowers`
  ADD PRIMARY KEY (`borrower_id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD UNIQUE KEY `email_address` (`email_address`),
  ADD KEY `idx_borrowers_id_number` (`id_number`),
  ADD KEY `idx_borrowers_email` (`email_address`),
  ADD KEY `idx_borrowers_department` (`department_course_office`);

--
-- Indexes for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `idx_transactions_borrower` (`borrower_id`),
  ADD KEY `idx_transactions_dates` (`date_requested`,`date_needed`,`date_of_return`),
  ADD KEY `idx_transactions_status` (`status`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD UNIQUE KEY `item_code` (`item_code`),
  ADD KEY `idx_items_available` (`available_quantity`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  MODIFY `borrowed_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrowers`
--
ALTER TABLE `borrowers`
  MODIFY `borrower_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowing_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD CONSTRAINT `borrowed_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `borrowing_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowed_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowing_transactions`
--
ALTER TABLE `borrowing_transactions`
  ADD CONSTRAINT `borrowing_transactions_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`borrower_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
