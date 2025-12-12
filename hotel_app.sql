-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 12, 2025 at 07:54 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint UNSIGNED NOT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `approvals`
--

CREATE TABLE `approvals` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED NOT NULL,
  `requested_by` bigint UNSIGNED NOT NULL,
  `old_data` json NOT NULL,
  `new_data` json NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `job` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date DEFAULT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock` decimal(10,2) NOT NULL DEFAULT '0.00',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0000_00_00_000000_create_websockets_statistics_entries_table', 1),
(2, '2014_10_12_000000_create_users_table', 1),
(3, '2014_10_12_100000_create_password_resets_table', 1),
(4, '2019_08_19_000000_create_failed_jobs_table', 1),
(5, '2021_04_09_143243_create_customers_table', 1),
(6, '2021_04_09_143318_create_types_table', 1),
(7, '2021_04_09_143330_create_rooms_table_revised', 1),
(8, '2021_04_09_143335_create_transactions_table', 1),
(9, '2021_04_09_143453_create_payments_table', 1),
(10, '2021_04_17_202643_add_status_to_payments_table', 1),
(11, '2021_04_18_205922_create_notifications_table', 1),
(12, '2024_09_24_191240_create_activity_log_table', 1),
(13, '2024_09_24_191241_add_event_column_to_activity_log_table', 1),
(14, '2024_09_24_191242_add_batch_uuid_column_to_activity_log_table', 1),
(15, '2025_11_06_135657_create_ruang_rapat_pakets_table', 1),
(16, '2025_11_12_102616_create_rapat_customers_table', 1),
(17, '2025_11_12_102748_create_rapat_transactions_table', 1),
(18, '2025_11_18_113106_create_amenities_table', 1),
(19, '2025_11_19_135203_create_ingredients_table', 1),
(20, '2025_11_25_112254_update_role_enum_in_users_table', 1),
(21, '2025_11_25_112449_update_role_enum_in_users_table', 1),
(22, '2025_12_02_103118_create_approvals_table', 1),
(23, '2025_12_03_113616_add_status_to_rooms_table', 1),
(24, '2025_12_04_000000_add_phone_and_make_fields_nullable_in_customers_table', 1),
(25, '2025_12_10_100007_add_paid_amount_to_transactions_table', 1),
(26, '2025_12_10_104608_fix_paid_amount_for_active_checkins', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `transaction_id` bigint UNSIGNED NOT NULL,
  `price` decimal(65,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapat_customers`
--

CREATE TABLE `rapat_customers` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapat_transactions`
--

CREATE TABLE `rapat_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `rapat_customer_id` bigint UNSIGNED NOT NULL,
  `ruang_rapat_paket_id` bigint UNSIGNED NOT NULL,
  `tanggal_pemakaian` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `status_reservasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `jumlah_peserta` int NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `total_pembayaran` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` bigint UNSIGNED NOT NULL,
  `type_id` bigint UNSIGNED NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` bigint NOT NULL,
  `price` double NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Available',
  `area_sqm` double DEFAULT NULL,
  `breakfast` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `room_facilities` longtext COLLATE utf8mb4_unicode_ci,
  `bathroom_facilities` longtext COLLATE utf8mb4_unicode_ci,
  `main_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `type_id`, `number`, `capacity`, `price`, `name`, `status`, `area_sqm`, `breakfast`, `room_facilities`, `bathroom_facilities`, `main_image_path`, `created_at`, `updated_at`) VALUES
(1, 1, '101', 2, 450000, 'Deluxe Garden View', 'Available', 15.4, 'Yes', 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Meja, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(2, 2, '103', 2, 550000, 'Superior Pool View', 'Available', 27.7, 'Yes', 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Meja, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(3, 3, '401', 4, 1500000, 'Sawunggaling Suite', 'Available', 47.7, 'Yes', 'AC, 2 Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Living Room, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(4, 4, '402', 3, 1200000, 'Pitaloka Suite', 'Available', 34.2, 'Yes', 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(5, 5, '403', 2, 950000, 'Boscha Suite', 'Available', 25.5, 'Yes', 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(6, 6, '404', 2, 1000000, 'Priangan Suite', 'Available', 27, 'Yes', 'AC, Smart TV, Coffee & Tea Set, Water Kettle, Minibar, Meja, Balkon', 'Water Heater, Shower, Handuk, Amenities (Shampoo, Sabun, Dental Kit, Slipper)', 'img/default/default-room.png', '2025-12-12 07:53:17', '2025-12-12 07:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `ruang_rapat_pakets`
--

CREATE TABLE `ruang_rapat_pakets` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_paket` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fasilitas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `room_id` bigint UNSIGNED NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_price` bigint NOT NULL DEFAULT '0',
  `paid_amount` bigint NOT NULL DEFAULT '0',
  `breakfast` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `information` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `name`, `information`, `created_at`, `updated_at`) VALUES
(1, 'Deluxe', 'Kamar Deluxe menawarkan kenyamanan dengan ukuran kamar yang bervariasi mulai dari 15.4 m² hingga 27.6 m². Dilengkapi dengan fasilitas modern seperti AC, Smart TV, Coffee & Tea Set, serta balkon di beberapa kamar. Kamar mandi dilengkapi dengan water heater, shower, handuk, dan amenities lengkap (shampoo, sabun, dental kit, slipper). Cocok untuk tamu yang mencari kenyamanan dengan harga terjangkau.', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(2, 'Superior', 'Kamar Superior merupakan pilihan premium dengan ukuran kamar berkisar 21.4 m² hingga 27.7 m². Menawarkan fasilitas yang lebih lengkap dengan AC, Smart TV, Coffee & Tea Set, water kettle, meja kerja, dan balkon yang menghadirkan pemandangan indah. Kamar mandi dilengkapi dengan water heater, shower berkualitas, handuk premium, dan amenities lengkap. Ideal untuk tamu yang menginginkan pengalaman menginap yang lebih eksklusif.', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(3, 'Sawunggaling Suite', 'Suite termewah dengan luas 47.7 m² yang menawarkan pengalaman menginap istimewa. Dilengkapi dengan living room terpisah, 2 Smart TV, minibar, AC, coffee & tea set, water kettle, dan meja kerja. Kamar mandi mewah dengan water heater, shower premium, handuk berkualitas tinggi, dan amenities lengkap. Balkon private memberikan pemandangan spektakuler. Sempurna untuk tamu VIP atau pasangan yang merayakan momen spesial.', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(4, 'Pitaloka', 'Kamar Pitaloka seluas 34.2 m² menghadirkan kombinasi sempurna antara kemewahan dan kenyamanan. Dilengkapi dengan AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja yang luas. Kamar mandi premium dengan water heater, shower berkualitas, handuk lembut, dan amenities lengkap. Balkon private untuk bersantai sambil menikmati pemandangan. Pilihan ideal untuk pengalaman menginap yang berkesan.', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(5, 'Boscha', 'Kamar Boscha dengan luas 25.5 m² menawarkan kenyamanan modern dengan sentuhan elegan. Fasilitas lengkap meliputi AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja. Kamar mandi dilengkapi dengan water heater, shower, handuk berkualitas, dan amenities lengkap. Balkon private memberikan ruang santai yang nyaman. Cocok untuk tamu bisnis maupun leisure yang menghargai kualitas.', '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(6, 'Priangan', 'Kamar Priangan seluas 27 m² menggabungkan fungsionalitas dan kenyamanan. Dilengkapi dengan AC, Smart TV, minibar, coffee & tea set, water kettle, dan meja kerja yang praktis. Kamar mandi modern dengan water heater, shower, handuk lembut, dan amenities lengkap. Balkon private menjadi nilai tambah untuk menikmati udara segar. Pilihan tepat untuk pengalaman menginap yang menyenangkan dan berkesan.', '2025-12-12 07:53:17', '2025-12-12 07:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('Super','Admin','Customer','Manager','Dapur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `random_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar`, `role`, `email_verified_at`, `password`, `random_key`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Sawunggaling Super', 'super@gmail.com', NULL, 'Super', NULL, '$2y$10$mm1aWB2cF7tefz.fVsZ1Y.HFGK/aduSIbkXHK2QkfHDVT/QvfiJcq', 'YoNZTth8JkNIFSDjJOurmQNQV6uYQAGscARiRBrGn4BsihtXo3BJ4D1RMoJM', NULL, '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(2, 'Staff Admin', 'admin@gmail.com', NULL, 'Admin', NULL, '$2y$10$Z4..EgADDtXcWkFvLVbMMu6eyd754TcmWHZeAm/H94USWQzIRo2Ua', 'rd6iaO9CrcVPCGpOfwlIYfTLPTFxkUTGmmozLufOKi2UZfXQ7e5ObMzVOEro', NULL, '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(3, 'Manager Hotel', 'manager@gmail.com', NULL, 'Manager', NULL, '$2y$10$aGrEU80yTWMTLqM8f.DMDuSLZPZwdo5AlHsEJibYA9X6NUQb./vha', 'bwZDTnzI62hij5wmu2XeSi1v0M1uAerDXMVQBrUKTGXbqWKCJxKpYF7J1RmM', NULL, '2025-12-12 07:53:17', '2025-12-12 07:53:17'),
(4, 'Kepala Dapur', 'dapur@gmail.com', NULL, 'Dapur', NULL, '$2y$10$GFTvls7pJuYR.shx9e6dUeum0s6rPMmrTMqvQa0LXfDk01IR5wAVi', 'DbQAuZPipLwVojlLYZR7aHQmlSCgDl7pndYF9UGcCs8fjFe6LfVuoIOOqdur', NULL, '2025-12-12 07:53:17', '2025-12-12 07:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `websockets_statistics_entries`
--

CREATE TABLE `websockets_statistics_entries` (
  `id` int UNSIGNED NOT NULL,
  `app_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int NOT NULL,
  `websocket_message_count` int NOT NULL,
  `api_message_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_user_id_foreign` (`user_id`),
  ADD KEY `payments_transaction_id_foreign` (`transaction_id`);

--
-- Indexes for table `rapat_customers`
--
ALTER TABLE `rapat_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rapat_customers_email_unique` (`email`);

--
-- Indexes for table `rapat_transactions`
--
ALTER TABLE `rapat_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rapat_transactions_rapat_customer_id_foreign` (`rapat_customer_id`),
  ADD KEY `rapat_transactions_ruang_rapat_paket_id_foreign` (`ruang_rapat_paket_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rooms_type_id_foreign` (`type_id`);

--
-- Indexes for table `ruang_rapat_pakets`
--
ALTER TABLE `ruang_rapat_pakets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transactions_user_id_foreign` (`user_id`),
  ADD KEY `transactions_customer_id_foreign` (`customer_id`),
  ADD KEY `transactions_room_id_foreign` (`room_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `approvals`
--
ALTER TABLE `approvals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rapat_customers`
--
ALTER TABLE `rapat_customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rapat_transactions`
--
ALTER TABLE `rapat_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ruang_rapat_pakets`
--
ALTER TABLE `ruang_rapat_pakets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  ADD CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `rapat_transactions`
--
ALTER TABLE `rapat_transactions`
  ADD CONSTRAINT `rapat_transactions_rapat_customer_id_foreign` FOREIGN KEY (`rapat_customer_id`) REFERENCES `rapat_customers` (`id`),
  ADD CONSTRAINT `rapat_transactions_ruang_rapat_paket_id_foreign` FOREIGN KEY (`ruang_rapat_paket_id`) REFERENCES `ruang_rapat_pakets` (`id`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `transactions_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
