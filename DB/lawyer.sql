-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 24, 2025 at 05:23 AM
-- Server version: 10.6.18-MariaDB-0ubuntu0.22.04.1
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lawyer`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `billing_address` text DEFAULT NULL,
  `type` enum('company','individual') NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_other` text DEFAULT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `linkedin_url` text DEFAULT NULL,
  `website_url` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client`, `email`, `billing_address`, `type`, `contact_person`, `mobile`, `currency_id`, `source_id`, `source_other`, `country_id`, `created_by`, `linkedin_url`, `website_url`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Tech Corp', 'tech@corp.com', '253 Teagan GrovesSouth Oren, WY 05367-5013', 'individual', 'Kashish Shah', '+1-539-223-7397', 2, 1, 'Website', 3, 1, 'http://carter.info/et-totam-aliquam-laboriosam-delectus.html', 'http://www.harvey.biz/', NULL, '2025-01-16 02:00:31', '2025-01-22 00:18:39'),
(2, 'Global Enterprises', 'global@enterprises.com', '27907 Santina SummitCruickshankview, NJ 96616-3794', 'company', 'Kashish Shah', '73174 79580', 1, 2, 'Referral', 4, 1, 'https://crooks.com/incidunt-qui-maiores-delectus-ab-facilis.html', 'http://fay.com/', NULL, '2025-01-16 02:00:32', '2025-01-22 00:18:16'),
(6, 'test', 'anon1277tester@yopmail.com', 'Happy Street', 'individual', 'Kashish Shah', '99258678055', 1, NULL, 'Nothing', 1, 1, NULL, NULL, '2025-01-22 00:25:59', '2025-01-22 00:20:37', '2025-01-22 00:25:59'),
(7, 'Pratham Shah', 'pratham0207@gmail.com', 'Parshwa Building', 'individual', 'Kashish Shah', '99258678055', 3, NULL, 'Website', 1, 1, NULL, NULL, NULL, '2025-01-23 04:22:40', '2025-01-23 04:22:40');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `country`, `code`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'India', 'Indian Rupees', '1', NULL, '2025-01-11 06:05:45', '2025-01-13 06:09:05'),
(3, 'America', 'USA Dollars', '1', NULL, '2025-01-13 06:09:32', '2025-01-15 23:28:48'),
(4, 'Europe', 'Euro', '1', NULL, '2025-01-13 07:59:39', '2025-01-13 08:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `currency`, `code`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Sri Lankan Rupee', 'LKR', '1', NULL, NULL, '2025-01-16 02:00:40'),
(2, 'America', 'Euro', '1', NULL, NULL, '2025-01-22 05:08:03'),
(3, 'Euro', 'EUR', '0', NULL, NULL, '2025-01-22 05:07:55'),
(4, 'Europe', 'Indian Rupees', '1', NULL, '2025-01-22 05:07:06', '2025-01-22 05:07:06');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '0 - Inactive, 1 - Active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'New', 'new', '1', NULL, '2025-01-06 08:35:28', '2025-01-10 04:02:12'),
(2, 'Manager Department', 'Hello here is a manager department which is active', '1', NULL, '2025-01-07 01:08:30', '2025-01-10 05:16:20'),
(3, 'The department', 'This is an inactive department', '1', NULL, '2025-01-07 01:08:53', '2025-01-10 05:16:18'),
(4, 'Department 4', 'This is for testing', '1', NULL, '2025-01-07 03:34:17', '2025-01-10 05:16:15'),
(5, 'Management', 'Management Department is too good', '0', NULL, '2025-01-10 03:17:20', '2025-01-10 05:16:23'),
(6, 'Administrator', 'Administrator department has all the access in the website', '0', NULL, '2025-01-10 05:16:56', '2025-01-13 07:31:52'),
(7, 'Manager', 'Manager has all access in this website like administrator', '0', NULL, '2025-01-10 05:26:28', '2025-01-13 07:31:57'),
(8, 'Client', 'Client has a limited access', '1', NULL, '2025-01-10 05:28:55', '2025-01-13 07:31:05'),
(9, 'Test', 'testing', '1', NULL, '2025-01-10 05:36:08', '2025-01-13 07:32:54'),
(10, 'New Depart', 'depart ment', '1', NULL, '2025-01-13 07:33:50', '2025-01-13 07:33:50'),
(11, 'rule', 'asdasdadada', '1', NULL, '2025-01-13 07:33:55', '2025-01-13 07:34:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(6, '2025_01_03_125805_create_personal_access_tokens_table', 2),
(7, '2025_01_06_095115_create_departments_table', 3),
(8, '2025_01_10_125039_create_currencies_table', 4),
(9, '2025_01_10_125232_create_countries_table', 4),
(10, '2025_01_16_051415_create_sources_table', 5),
(11, '2025_01_13_131214_create_clients_table', 6),
(12, '2025_01_22_060331_create_quotes_tables', 7),
(14, '2025_01_23_095948_create_quotes_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('kashishwork1507@gmail.com', '$2y$12$GvkQ5f/epgpd3pEjdEtT/.ZYk1G/AtusVGI/oeLXTEyNbMUxieOi6', '2025-01-13 08:17:09');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotes`
--

CREATE TABLE `quotes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(6) DEFAULT NULL,
  `expiry_at` date NOT NULL DEFAULT '2025-02-22',
  `status` enum('quoted','awarded','lost') NOT NULL,
  `description` text NOT NULL,
  `assignment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotes`
--

INSERT INTO `quotes` (`id`, `client_id`, `reference`, `expiry_at`, `status`, `description`, `assignment_id`, `created_by`, `approved_by`, `approved_at`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 7, NULL, '2025-02-23', 'awarded', 'Hello This side lawyer admin this is the case of this things....', NULL, 1, NULL, NULL, NULL, '2025-01-23 22:55:49', '2025-01-23 23:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `quote_tasks`
--

CREATE TABLE `quote_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quote_id` bigint(20) UNSIGNED NOT NULL,
  `amount` int(11) NOT NULL,
  `currency_id` bigint(20) UNSIGNED NOT NULL,
  `description` text NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quote_tasks`
--

INSERT INTO `quote_tasks` (`id`, `quote_id`, `amount`, `currency_id`, `description`, `department_id`, `deleted_at`, `created_at`, `updated_at`) VALUES
(3, 3, 10, 2, 'Admin Fees', 6, NULL, '2025-01-23 22:55:49', '2025-01-23 22:55:49'),
(4, 3, 5, 2, 'Manager fees', 2, NULL, '2025-01-23 22:55:49', '2025-01-23 22:55:49'),
(5, 3, 15, 2, 'Client translator fees', 6, NULL, '2025-01-23 23:15:40', '2025-01-23 23:15:40');

-- --------------------------------------------------------

--
-- Table structure for table `quote_users`
--

CREATE TABLE `quote_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quote_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `access_level` enum('read','edit') NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE `sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` text NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sources`
--

INSERT INTO `sources` (`id`, `title`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Website', NULL, NULL, NULL),
(2, 'Referral', NULL, NULL, NULL),
(3, 'Social Media', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Kashish Shah', 'kashishwork1507@gmail.com', '2025-01-03 07:16:25', '$2y$12$sFNPnYkVzyQeFSDmhW/NWeQhDvxzbVK4sFM0rP781VTrbKXwYtw8K', 'UfdulDC0geboIiEF5sxPlf6AJl0Q0u7zHI3cAqbVpgIlhiVxBXEgHY4whsdI', NULL, '2025-01-03 07:16:26', '2025-01-06 01:48:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_email_unique` (`email`),
  ADD KEY `clients_currency_id_foreign` (`currency_id`),
  ADD KEY `clients_source_id_foreign` (`source_id`),
  ADD KEY `clients_country_id_foreign` (`country_id`),
  ADD KEY `clients_created_by_foreign` (`created_by`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `quotes`
--
ALTER TABLE `quotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotes_client_id_foreign` (`client_id`),
  ADD KEY `quotes_created_by_foreign` (`created_by`),
  ADD KEY `quotes_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `quote_tasks`
--
ALTER TABLE `quote_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quote_tasks_quote_id_foreign` (`quote_id`),
  ADD KEY `quote_tasks_currency_id_foreign` (`currency_id`),
  ADD KEY `quote_tasks_department_id_foreign` (`department_id`);

--
-- Indexes for table `quote_users`
--
ALTER TABLE `quote_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quote_users_quote_id_foreign` (`quote_id`),
  ADD KEY `quote_users_user_id_foreign` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotes`
--
ALTER TABLE `quotes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quote_tasks`
--
ALTER TABLE `quote_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `quote_users`
--
ALTER TABLE `quote_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sources`
--
ALTER TABLE `sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clients_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `clients_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotes`
--
ALTER TABLE `quotes`
  ADD CONSTRAINT `quotes_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quote_tasks`
--
ALTER TABLE `quote_tasks`
  ADD CONSTRAINT `quote_tasks_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  ADD CONSTRAINT `quote_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `quote_tasks_quote_id_foreign` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quote_users`
--
ALTER TABLE `quote_users`
  ADD CONSTRAINT `quote_users_quote_id_foreign` FOREIGN KEY (`quote_id`) REFERENCES `quotes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quote_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
