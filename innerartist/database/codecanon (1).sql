-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2018 at 01:35 PM
-- Server version: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `codecanon`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_companyname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_street_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_street_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_companyname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_street_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_street_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `firstname`, `lastname`, `billing_firstname`, `billing_lastname`, `billing_companyname`, `billing_country`, `billing_street_1`, `billing_street_2`, `billing_city`, `billing_state`, `billing_zip`, `billing_phone`, `billing_email`, `shipping_firstname`, `shipping_lastname`, `shipping_companyname`, `shipping_country`, `shipping_street_1`, `shipping_street_2`, `shipping_city`, `shipping_state`, `shipping_zip`, `shipping_phone`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '', '', 'tesdddd', '1', '123', '123', '123333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333', '123', '123', '434343', '456464trte', '1234343434344444444444444444444444444444444444444444444444444444444444', '4343343aa@aaa.com', '123', '123', '123', '123', '123', '123', '123', '123', '123', '123', 2, '2017-10-12 02:59:53', '2017-10-16 17:32:46'),
(2, '', '', 'aksh', 'mishra', 'softthink technology', 'BAHRAIN', '99', 'fyffyfytfty', 'indore', 'madhya pradesh', '452010', '9977885566', 'aksh@gmail.com', 'aks', 'mishra', 'softthink technology', 'BAHAMAS', '99', 'skkff', 'indore', 'madhya pradesh', '452010', '9977885566', 13, '2017-12-22 06:38:17', '2017-12-23 06:43:43');

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `id` int(10) UNSIGNED NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`id`, `content`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '{\"action\":\"uploaded\",\"itemName\":\"photo\",\"happenedAt\":1515755261225,\"items\":[{\"name\":\"TransLogo.png\",\"id\":2,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:07:40', '2018-01-12 11:07:40'),
(2, '{\"action\":\"uploaded\",\"itemName\":\"photo\",\"happenedAt\":1515755269855,\"items\":[{\"name\":\"logome ssscsssopy.png\",\"id\":3,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:07:49', '2018-01-12 11:07:49'),
(3, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515755381211,\"items\":[{\"name\":\"Logome ssscsssopy.png\",\"id\":3,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:09:40', '2018-01-12 11:09:40'),
(4, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515755393952,\"items\":[{\"name\":\"Logome ssscsssopy.png\",\"id\":3,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:09:53', '2018-01-12 11:09:53'),
(5, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755402955,\"items\":[{\"name\":\"Logome ssscsssopy.png\",\"id\":3,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:02', '2018-01-12 11:10:02'),
(6, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755406818,\"items\":[{\"name\":\"20171012_122655.jpg\",\"id\":4,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:06', '2018-01-12 11:10:06'),
(7, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755419676,\"items\":[{\"name\":\"IMG_3430.JPG\",\"id\":1,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:19', '2018-01-12 11:10:19'),
(8, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755430516,\"items\":[{\"name\":\"20171012_122655.jpg\",\"id\":4,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:31', '2018-01-12 11:10:31'),
(9, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755433237,\"items\":[{\"name\":\"Logome ssscsssopy.png\",\"id\":3,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:32', '2018-01-12 11:10:32'),
(10, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515755441540,\"items\":[{\"name\":\"IMG_3430.JPG\",\"id\":1,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 11:10:41', '2018-01-12 11:10:41'),
(11, '{\"action\":\"created\",\"itemName\":\"photo\",\"happenedAt\":1515770748315,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 15:25:48', '2018-01-12 15:25:48'),
(12, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515771436038,\"items\":[{\"name\":\"Untitled Photo\",\"id\":6,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 15:37:16', '2018-01-12 15:37:16'),
(13, '{\"action\":\"uploaded\",\"itemName\":\"photo\",\"happenedAt\":1515772453401,\"items\":[{\"name\":\"Cars.jpg\",\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 15:54:13', '2018-01-12 15:54:13'),
(14, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515772621339,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 15:57:01', '2018-01-12 15:57:01'),
(15, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515772847287,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 16:00:47', '2018-01-12 16:00:47'),
(16, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515772980928,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 16:03:01', '2018-01-12 16:03:01'),
(17, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515775161782,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 16:39:22', '2018-01-12 16:39:22'),
(18, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515782434494,\"items\":[{\"name\":\"Untitled Photo\",\"id\":6,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 18:40:35', '2018-01-12 18:40:35'),
(19, '{\"action\":\"created\",\"itemName\":\"photo\",\"happenedAt\":1515791331980,\"items\":[{\"name\":\"Untitled Photo\",\"id\":8,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 21:08:52', '2018-01-12 21:08:52'),
(20, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515791485650,\"items\":[{\"name\":\"Untitled Photo\",\"id\":8,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 21:11:25', '2018-01-12 21:11:25'),
(21, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515793135225,\"items\":[{\"name\":\"Untitled Photo\",\"id\":5,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-12 21:38:55', '2018-01-12 21:38:55'),
(22, '{\"action\":\"uploaded\",\"itemName\":\"photo\",\"happenedAt\":1515834717762,\"items\":[{\"name\":\"20.jpg\",\"id\":9,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":3}', 3, '2018-01-13 09:11:58', '2018-01-13 09:11:58'),
(23, '{\"action\":\"edited\",\"itemName\":\"photo\",\"happenedAt\":1515834854310,\"items\":[{\"name\":\"20.jpg\",\"id\":9,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":3}', 3, '2018-01-13 09:14:14', '2018-01-13 09:14:14'),
(24, '{\"action\":\"uploaded\",\"itemName\":\"photo\",\"happenedAt\":1515834909045,\"items\":[{\"name\":\"201410080935142.jpg\",\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":9}', 3, '2018-01-13 09:15:09', '2018-01-13 09:15:09'),
(25, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515840708298,\"items\":[{\"name\":\"20.jpg\",\"id\":9,\"icon\":\"picture\"},{\"name\":\"201410080935142.jpg\",\"id\":10,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":3}', 1, '2018-01-13 10:51:48', '2018-01-13 10:51:48'),
(26, '{\"action\":\"deleted\",\"itemName\":\"photo\",\"happenedAt\":1515840717905,\"items\":[{\"name\":\"Untitled Photo\",\"id\":8,\"icon\":\"picture\"}],\"user\":\"You\",\"folder_id\":1}', 1, '2018-01-13 10:51:57', '2018-01-13 10:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `dp_activity`
--

CREATE TABLE `dp_activity` (
  `id` int(10) UNSIGNED NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_folders`
--

CREATE TABLE `dp_folders` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `share_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_folders`
--

INSERT INTO `dp_folders` (`id`, `name`, `description`, `user_id`, `share_id`, `password`, `created_at`, `updated_at`) VALUES
(1, 'root', NULL, 1, 'XAXw2e1UpN2aUgD', NULL, '2018-01-09 10:23:29', '2018-01-09 10:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `dp_labels`
--

CREATE TABLE `dp_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_labels`
--

INSERT INTO `dp_labels` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'favorite', '2018-01-09 10:23:00', '2018-01-09 10:23:00'),
(2, 'trashed', '2018-01-09 10:23:00', '2018-01-09 10:23:00');

-- --------------------------------------------------------

--
-- Table structure for table `dp_migrations`
--

CREATE TABLE `dp_migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_migrations`
--

INSERT INTO `dp_migrations` (`migration`, `batch`) VALUES
('2014_10_12_000000_create_users_table', 1),
('2014_10_12_100000_create_password_resets_table', 1),
('2015_04_127_156842_create_users_oauth_table', 1),
('2015_04_13_140047_create_photo_models_table', 1),
('2015_04_18_134312_create_folders_table', 1),
('2015_04_28_152847_create_activity_table', 1),
('2015_05_05_131439_create_labels_table', 1),
('2015_05_05_131450_create_photos_labels_table', 1),
('2015_05_29_131549_create_settings_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dp_orders`
--

CREATE TABLE `dp_orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `size_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `total_price` double(15,2) NOT NULL,
  `status` enum('on-cart','ordered','confirmed','delivered') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

--
-- Dumping data for table `dp_orders`
--

INSERT INTO `dp_orders` (`id`, `user_id`, `product_id`, `material_id`, `size_id`, `category_id`, `total_price`, `status`, `created_at`, `updated_at`, `quantity`) VALUES
(1, 2, 5, 1, NULL, 4, 4000.00, 'ordered', '2017-04-26 05:45:35', '2017-04-26 05:46:14', 1),
(2, 2, 5, 1, NULL, 4, 4000.00, 'ordered', '2017-04-26 07:32:42', '2017-04-26 07:33:13', 1),
(3, 2, 6, 1, NULL, 4, 2020.00, 'on-cart', '2017-04-26 07:37:29', '2017-04-26 07:37:29', 1),
(4, 4, 5, 1, NULL, 4, 4000.00, 'on-cart', '2017-04-26 10:05:58', '2017-04-26 10:05:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dp_password_resets`
--

CREATE TABLE `dp_password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_photos`
--

CREATE TABLE `dp_photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `share_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `attach_id` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `serialized_editor_state` mediumtext COLLATE utf8_unicode_ci,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_photos_labels`
--

CREATE TABLE `dp_photos_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `photo_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dp_products`
--

CREATE TABLE `dp_products` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `path_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `custom_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `custom_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `canvas_height` smallint(6) DEFAULT NULL,
  `canvas_width` smallint(6) DEFAULT NULL,
  `crop_art_width` int(11) NOT NULL,
  `crop_art_height` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `regular_price` int(11) NOT NULL,
  `sale_price` int(11) NOT NULL,
  `sale_end_date` datetime NOT NULL,
  `flat_rate` int(11) NOT NULL,
  `two_days_shipping` int(11) NOT NULL,
  `rush_delivery` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_products`
--

INSERT INTO `dp_products` (`id`, `title`, `path_image`, `custom_title`, `custom_description`, `canvas_height`, `canvas_width`, `crop_art_width`, `crop_art_height`, `price`, `regular_price`, `sale_price`, `sale_end_date`, `flat_rate`, `two_days_shipping`, `rush_delivery`, `created_at`, `updated_at`) VALUES
(2, 'Induction', '37.jpg', 'gffhjfyjh', 'ddfhhg', NULL, NULL, 8, 7, 6, 5, 4, '2018-01-03 00:00:00', 3, 2, 1, '0000-00-00 00:00:00', '2018-01-03 07:36:35'),
(3, 'Microwave', '38.jpg', 'sdxf', 'gffff', 8, 8, 6, 6, 7, 4, 11, '2018-01-15 00:00:00', 8, 9, 10, '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'Phone', '35.jpg', 'trff', 'tgfr', 4, 4, 5, 7, 7, 8, 4, '2018-01-15 00:00:00', 5, 6, 7, '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `dp_settings`
--

CREATE TABLE `dp_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_settings`
--

INSERT INTO `dp_settings` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'homeTagline', 'Inner Artist. Changing the way art is created.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'homeByline', 'Drag and drop anywhere on the homepage to upload and host your images. 5MB Limit. Register or login to access your dashboard, image editor and other features..', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'homeButtonText', 'Register Now', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'homepage', 'landing', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'validExtensions', 'jpg, jpeg, png, gif', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'maxFileSize', '5', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'maxUserSpace', '41943040', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'enableRegistration', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'siteName', 'Inner Artist', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'enableHomeUpload', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'maxSimultUploads', '10', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'enablePushState', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'dateLocale', 'en', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'pushStateRootUrl', '/', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'disqusShortname', 'pixie', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `dp_users`
--

CREATE TABLE `dp_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permissions` text COLLATE utf8_unicode_ci,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `dp_users`
--

INSERT INTO `dp_users` (`id`, `username`, `first_name`, `last_name`, `avatar_url`, `gender`, `permissions`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Development', NULL, NULL, NULL, NULL, '{\"admin\":1}', 'admin@gmail.com', '$2y$10$YbziGvNtF6/4cxQjV1kqSeQABGdUdPG5I/SJ6OdKCxkkjnVI8e/DW', '1pKpWyjp7JpeHoXA3XiA2NergtJTB2jMCZOGfU37pqJBWXk76nPxRIwruZRj', '2018-01-09 10:23:29', '2018-01-13 09:09:46'),
(2, NULL, NULL, NULL, NULL, NULL, NULL, 'demo@gmail.com', '$2y$10$YbziGvNtF6/4cxQjV1kqSeQABGdUdPG5I/SJ6OdKCxkkjnVI8e/DW', 'HHlf6ROQPYra1CZo9cBNABPmyCoSjbmGFK3nHqB35DRIUHJCDHylJGremsaq', '2018-01-12 12:55:07', '2018-01-13 09:18:16'),
(3, NULL, NULL, NULL, NULL, NULL, NULL, 'kai@gmail.com', '$2y$10$VD3b6pB9.DSfDvLR8lIX.ubLIAvuhzzSxgpXYs8Hl0P4bw.xUu5y.', 'wNAW1D9W2iCN7qLdzKJY1dB2Lg8BNc3gv3zyqD8XY1IcDxGg0lk42Sy24vOy', '2018-01-13 09:10:14', '2018-01-13 09:17:26');

-- --------------------------------------------------------

--
-- Table structure for table `dp_users_oauth`
--

CREATE TABLE `dp_users_oauth` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `service` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folders`
--

CREATE TABLE `folders` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `share_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `folders`
--

INSERT INTO `folders` (`id`, `name`, `description`, `user_id`, `share_id`, `password`, `created_at`, `updated_at`) VALUES
(1, 'root', NULL, 1, 'wSaN3TM4RSST0ID', NULL, '2018-01-12 10:56:53', '2018-01-12 10:56:53'),
(2, 'root', NULL, 2, 'W2QUUqYQWpkqyN3', NULL, '2018-01-12 12:55:07', '2018-01-12 12:55:07'),
(3, 'root', NULL, 3, 'wAyfAE77kjTDofc', NULL, '2018-01-13 09:10:14', '2018-01-13 09:10:14'),
(4, 'root', NULL, 4, 'wv3TnzmQpx2gp8i', NULL, '2018-01-15 02:02:28', '2018-01-15 02:02:28'),
(5, 'root', NULL, 5, 'U5QlQ5KcWWqWpCJ', NULL, '2018-01-15 02:03:49', '2018-01-15 02:03:49'),
(6, 'root', NULL, 6, 'kTIDIXkEQpoA867', NULL, '2018-01-15 02:04:07', '2018-01-15 02:04:07'),
(7, 'root', NULL, 7, 'vbdWTOhDSCVp8ug', NULL, '2018-01-15 02:04:22', '2018-01-15 02:04:22'),
(8, 'root', NULL, 8, 'MJWIWJ4tr1SIfwz', NULL, '2018-01-15 02:04:37', '2018-01-15 02:04:37'),
(9, 'root', NULL, 9, 'ZZj5CaXJBJRwDsg', NULL, '2018-01-15 02:04:53', '2018-01-15 02:04:53'),
(10, 'root', NULL, 10, 'RKznILRv7UpgZh7', NULL, '2018-01-15 02:05:23', '2018-01-15 02:05:23'),
(11, 'root', NULL, 11, 'LfCAcPO1XqUFy0a', NULL, '2018-01-15 02:05:47', '2018-01-15 02:05:47'),
(12, 'root', NULL, 12, 'OALlWKcjL2CF2cX', NULL, '2018-01-15 02:06:03', '2018-01-15 02:06:03'),
(13, 'root', NULL, 13, 'reqOBMQ0muZaPkU', NULL, '2018-01-15 02:06:20', '2018-01-15 02:06:20'),
(14, 'root', NULL, 14, 'kcj0f3NLCQENt80', NULL, '2018-01-15 02:06:34', '2018-01-15 02:06:34'),
(15, 'root', NULL, 15, 'tHVOPG2CXDRLoYk', NULL, '2018-01-15 02:06:47', '2018-01-15 02:06:47'),
(16, 'root', NULL, 16, '8Qu8G7Q3zCma3R2', NULL, '2018-01-15 02:07:07', '2018-01-15 02:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `inner_account`
--

CREATE TABLE `inner_account` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_companyname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_street_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_street_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `billing_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_companyname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_street_1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_street_2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_zip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shipping_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `inner_account`
--

INSERT INTO `inner_account` (`id`, `firstname`, `lastname`, `billing_firstname`, `billing_lastname`, `billing_companyname`, `billing_country`, `billing_street_1`, `billing_street_2`, `billing_city`, `billing_state`, `billing_zip`, `billing_phone`, `billing_email`, `shipping_firstname`, `shipping_lastname`, `shipping_companyname`, `shipping_country`, `shipping_street_1`, `shipping_street_2`, `shipping_city`, `shipping_state`, `shipping_zip`, `shipping_phone`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '', '', 'tesdddd', '1', '123', '123', '123333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333333', '123', '123', '434343', '456464trte', '1234343434344444444444444444444444444444444444444444444444444444444444', '4343343aa@aaa.com', '123', '123', '123', '123', '123', '123', '123', '123', '123', '123', 2, '2017-10-12 02:59:53', '2017-10-16 17:32:46'),
(2, '', '', 'aksh', 'mishra', 'softthink technology', 'BAHRAIN', '99', 'fyffyfytfty', 'indore', 'madhya pradesh', '452010', '9977885566', 'aksh@gmail.com', 'aks', 'mishra', 'softthink technology', 'BAHAMAS', '99', 'skkff', 'indore', 'madhya pradesh', '452010', '9977885566', 13, '2017-12-22 06:38:17', '2017-12-23 06:43:43');

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

CREATE TABLE `labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `labels`
--

INSERT INTO `labels` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'favorite', '2018-01-10 13:18:44', '2018-01-10 13:18:44'),
(2, 'trashed', '2018-01-10 13:18:44', '2018-01-10 13:18:44');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2014_10_12_000000_create_users_table', 1),
('2014_10_12_100000_create_password_resets_table', 1),
('2015_04_127_156842_create_users_oauth_table', 1),
('2015_04_13_140047_create_photo_models_table', 1),
('2015_04_18_134312_create_folders_table', 1),
('2015_04_28_152847_create_activity_table', 1),
('2015_05_05_131439_create_labels_table', 1),
('2015_05_05_131450_create_photos_labels_table', 1),
('2015_05_29_131549_create_settings_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE `photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `share_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `attach_id` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `serialized_editor_state` mediumtext COLLATE utf8_unicode_ci,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `name`, `description`, `file_name`, `share_id`, `attach_id`, `file_size`, `user_id`, `folder_id`, `serialized_editor_state`, `width`, `height`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'TransLogo.png', NULL, 'ruicefammaodnifv.png', 'ruicefammaodnifv', NULL, 26610, 1, 1, NULL, 225, 90, NULL, '2018-01-12 11:07:39', '2018-01-12 11:07:39', NULL),
(5, 'Untitled Photo', NULL, 'pP8p4L3NeHf32djn.png', 'xf69ELEsK3L3mBr3', NULL, 36662, 1, 1, '{\"objects\":[{\"type\":\"image\",\"originX\":\"left\",\"originY\":\"top\",\"left\":0,\"top\":0,\"width\":940,\"height\":788,\"fill\":\"rgb(0,0,0)\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":false,\"name\":\"mainImage\",\"src\":\"http://code.technofox.co.in/innerartist/uploads/1/5/original.pP8p4L3NeHf32djn.png\",\"filters\":[],\"crossOrigin\":\"\",\"alignX\":\"none\",\"alignY\":\"none\",\"meetOrSlice\":\"meet\"},{\"type\":\"path-group\",\"originX\":\"left\",\"originY\":\"top\",\"left\":305.67,\"top\":227.72,\"width\":512,\"height\":512,\"fill\":\"\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":0.61,\"scaleY\":0.61,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":0.5,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"name\":\"sticker\",\"paths\":[{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":226.5,\"top\":263.52,\"width\":44.53,\"height\":120.17,\"fill\":\"#C56528\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",266.825,317.759],[\"c\",-5.021,-14.39,-11.119,-30.046,-6.395,-45.372],[\"c\",0.9,-2.92,2.168,-5.968,3.779,-8.869],[\"c\",-5.68,2.437,-10.973,6.048,-15.242,10.439],[\"c\",-20.162,20.739,-17.209,49.932,-22.466,76.348],[\"c\",0.31,10.016,1.831,20.9,9.634,27.702],[\"c\",2.678,2.335,5.689,4.241,8.886,5.681],[\"c\",-0.002,-0.045,-0.009,-0.09,-0.011,-0.135],[\"c\",0.002,-2.038,0.153,-3.982,0.523,-5.986],[\"c\",0.59,-3.202,1.439,-6.364,3.078,-9.201],[\"c\",3.131,-5.42,8.906,-8.512,13.355,-12.684],[\"c\",4.439,-4.162,8.359,-8.58,9.064,-14.863],[\"C\",271.924,332.866,269.415,325.176,266.825,317.759],[\"z\"]],\"pathOffset\":{\"x\":248.76550000000003,\"y\":323.60300000000007}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":133.07,\"top\":87.29,\"width\":238.62,\"height\":56.73,\"fill\":\"#D6E5E5\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",370.744,87.289],[\"c\",-0.016,0.015,-0.03,0.029,-0.046,0.044],[\"c\",-7.83,7.172,-18.618,11.994,-28.619,15.185],[\"c\",-12.717,4.057,-26.017,6.115,-39.318,6.946],[\"c\",-13.744,0.858,-27.508,0.687,-41.268,0.426],[\"c\",-14.448,-0.274,-28.908,-0.123,-43.338,-0.961],[\"c\",-28.338,-1.646,-56.245,-6.648,-83.653,-13.848],[\"c\",-0.648,11.784,-1.759,23.546,-1.332,35.347],[\"c\",20.525,1.716,39.642,10.609,60.2,12.416],[\"c\",23.484,2.063,47.068,0.77,70.602,0.799],[\"c\",34.568,0.042,75.278,1.778,101.322,-25.146],[\"c\",0.871,-0.9,1.85,-1.407,2.841,-1.614],[\"C\",372.354,107.737,371.611,97.408,370.744,87.289],[\"z\"]],\"pathOffset\":{\"x\":252.38387810686777,\"y\":115.6528118338596}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":136.22,\"top\":53.13,\"width\":229.98,\"height\":46.02,\"fill\":\"#D6E5E5\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",365.972,73.572],[\"c\",-2.439,-4.069,-8.024,-6.85,-12.896,-8.916],[\"c\",-6.925,-2.937,-14.178,-5.014,-21.508,-6.67],[\"c\",-18.162,-4.102,-34.957,-5.051,-53.703,-4.825],[\"c\",-30.691,0.369,-61.335,4.28,-91.727,8.312],[\"c\",-18.628,2.472,-38.988,6.793,-49.918,22.716],[\"c\",22.223,5.877,44.821,10.414,67.713,12.66],[\"c\",23.693,2.324,47.693,2.16,71.488,2.283],[\"c\",21.994,0.114,45.09,-0.296,65.973,-8.011],[\"c\",7.256,-2.681,15.979,-6.076,21.646,-11.604],[\"c\",0.867,-0.847,1.735,-1.72,2.43,-2.72],[\"c\",-0.188,0.271,0.689,-1.862,0.594,-1.245],[\"C\",366.271,74.618,366.278,74.083,365.972,73.572],[\"z\"],[\"M\",308.382,84.906],[\"c\",-6.133,0.178,-12.148,-2.129,-18.027,-2.29],[\"c\",-6.299,-0.171,-7.668,-8.992,-2.795,-10.687],[\"c\",0.207,-2.053,1.36,-3.96,3.553,-4.489],[\"c\",8.367,-2.019,17.127,-3.292,25.265,0.264],[\"c\",1.737,0.76,2.888,2.662,3.038,4.504],[\"C\",319.987,79.211,315.593,84.697,308.382,84.906],[\"z\"]],\"pathOffset\":{\"x\":251.20944129623354,\"y\":76.13796271252808}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":255.94,\"top\":260.12,\"width\":54.97,\"height\":126.61,\"fill\":\"#C56528\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",304.536,274.155],[\"c\",-5.535,-9.434,-13.998,-13.616,-23.027,-14.031],[\"c\",-3.696,1.781,-6.841,7.021,-8.273,9.892],[\"c\",-3.017,6.042,-4.022,12.178,-3.217,18.922],[\"c\",1.93,16.167,12.174,30.828,12.174,47.283],[\"c\",-0.001,13.818,-6.838,23.097,-17.395,31.416],[\"c\",-4.957,3.907,-8.201,6.978,-8.771,13.625],[\"c\",-0.153,1.783,-0.051,3.616,-0.067,5.335],[\"c\",6.011,0.56,12.144,-0.525,17.671,-3.628],[\"c\",18.566,-10.421,29.871,-30.722,34.301,-51.036],[\"C\",311.802,314.179,314.19,290.608,304.536,274.155],[\"z\"]],\"pathOffset\":{\"x\":283.42682679338265,\"y\":323.43109528180577}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":108.39,\"top\":115.52,\"width\":298.65,\"height\":83.94,\"fill\":\"#D6E5E5\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",380.376,115.528],[\"c\",-0.018,-0.002,-0.032,-0.007,-0.049,-0.01],[\"c\",-0.721,2.563,-1.687,5.087,-2.973,7.558],[\"c\",-0.94,1.806,-2.428,2.686,-3.991,2.878],[\"c\",-27.306,28.033,-67.525,28.734,-104.131,28.666],[\"c\",-24.011,-0.045,-48.101,1.233,-72.077,-0.506],[\"c\",-11.993,-0.87,-23.246,-3.307,-34.813,-6.509],[\"c\",-11.101,-3.073,-22.379,-6.332,-33.979,-6.492],[\"c\",-2.736,-0.038,-5.204,-1.577,-5.789,-4.279],[\"c\",-1.393,1.535,-2.792,3.064,-4.162,4.621],[\"c\",-3.676,4.177,-8.215,9.03,-9.558,14.614],[\"c\",-2.45,10.19,5.29,20.06,13.608,25.23],[\"c\",1.82,0.016,3.559,0.826,4.411,2.491],[\"c\",19.325,10.042,42.985,11.725,64.261,13.055],[\"c\",23.902,1.494,47.928,2.64,71.878,2.613],[\"c\",26.935,-0.03,54.349,-1.667,80.365,-9.132],[\"c\",13.009,-3.733,25.776,-8.623,37.561,-15.33],[\"c\",9.308,-5.298,21.774,-11.461,26.108,-21.963],[\"C\",414.665,134.577,397.439,118.222,380.376,115.528],[\"z\"]],\"pathOffset\":{\"x\":257.7199504714619,\"y\":157.48823368894182}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":127.72,\"top\":182,\"width\":263.27,\"height\":312.87,\"fill\":\"#1E4384\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",390.96,182.005],[\"c\",-31.104,18.729,-68.227,26.208,-104.199,27.902],[\"c\",-27.053,1.274,-54.239,0.113,-81.266,-1.345],[\"c\",-19.281,-1.04,-38.771,-1.856,-57.672,-6.125],[\"c\",-6.822,-1.541,-13.679,-3.488,-20.102,-6.201],[\"c\",1.154,21.972,5.469,43.618,9.512,65.233],[\"c\",4.543,24.29,8.221,48.774,12.004,73.194],[\"c\",3.963,25.584,7.717,51.203,11.945,76.745],[\"c\",1.738,10.493,2.964,21.048,4.711,31.535],[\"c\",1.337,8.028,2.707,16.439,6.234,23.849],[\"c\",0.263,0.551,0.419,1.08,0.496,1.584],[\"c\",13.535,13.655,33.449,19.781,51.948,22.876],[\"c\",16.817,2.814,34.816,4.496,51.872,3.143],[\"c\",14.381,-1.14,28.859,-3.683,42.773,-7.474],[\"c\",6.821,-1.858,14.043,-3.833,20.263,-7.287],[\"c\",2.042,-1.134,4.257,-2.42,5.547,-3.887],[\"c\",0.554,-0.629,0.986,-1.46,0.849,-0.919],[\"c\",0.108,-0.425,0.177,-0.582,0.215,-0.633],[\"c\",-0.033,-0.053,-0.086,-0.218,-0.148,-0.663],[\"c\",-0.589,-4.155,2.992,-6.129,6.23,-5.617],[\"c\",0.184,-1.849,1.236,-3.61,3.436,-4.497],[\"c\",-0.264,0.106,0.527,-0.48,0.93,-1.229],[\"c\",0.742,-1.386,1.432,-2.835,1.886,-4.345],[\"c\",1.149,-3.818,1.972,-7.725,2.606,-11.661],[\"c\",1.17,-7.25,1.75,-14.562,2.736,-21.834],[\"c\",3.684,-27.134,7.086,-54.305,10.943,-81.416],[\"c\",4.112,-28.9,8.896,-57.639,11.893,-86.682],[\"c\",1.453,-14.09,2.779,-28.197,3.713,-42.332],[\"C\",391.017,203.291,390.995,192.651,390.96,182.005],[\"z\"],[\"M\",321.528,314.693],[\"c\",-1.49,20.138,-7.184,40.275,-19.251,56.713],[\"c\",-13.864,18.884,-35.95,32.603,-59.512,23.159],[\"c\",-9.22,-3.695,-17.267,-9.691,-21.808,-18.697],[\"c\",-3.804,-7.544,-5.042,-15.922,-5.391,-24.291],[\"c\",-0.175,-0.677,-0.21,-1.434,-0.072,-2.262],[\"c\",-0.025,-1.094,-0.042,-2.187,-0.047,-3.276],[\"c\",-0.006,-1.532,0.506,-2.771,1.311,-3.703],[\"c\",4.305,-26.609,3.305,-55.333,23.945,-75.644],[\"c\",10.799,-10.626,26.715,-18.47,41.857,-17.886],[\"c\",1.263,-0.163,2.567,-0.201,3.918,-0.085],[\"c\",1.043,0.089,1.88,0.405,2.535,0.869],[\"c\",6.559,1.383,12.801,4.58,18.211,10.089],[\"C\",321.151,273.865,322.921,295.866,321.528,314.693],[\"z\"]],\"pathOffset\":{\"x\":259.35798997064853,\"y\":338.4380144517802}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":97.3,\"top\":42.06,\"width\":322.64,\"height\":463.8,\"fill\":\"#010101\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",417.741,132.497],[\"c\",-5.063,-15.292,-19.844,-25.332,-35.266,-27.767],[\"c\",-0.14,-0.022,-0.273,-0.032,-0.41,-0.044],[\"c\",0.623,-10.219,-1.057,-20.782,-1.154,-30.978],[\"c\",-0.031,-3.301,-2.425,-4.875,-4.969,-4.903],[\"c\",-2.014,-4.558,-6.816,-7.938,-10.811,-10.392],[\"c\",-12.365,-7.596,-27.992,-10.91,-42.12,-13.348],[\"c\",-20.853,-3.597,-42.634,-3.395,-63.667,-2.295],[\"c\",-15.759,0.823,-31.461,2.557,-47.124,4.437],[\"c\",-12.384,1.486,-24.864,2.716,-37.16,4.84],[\"c\",-19.073,3.296,-37.796,10.461,-48.481,26.726],[\"c\",-1.428,0.891,-2.489,2.391,-2.606,4.445],[\"c\",-0.332,0.643,-0.653,1.297,-0.962,1.965],[\"c\",-1.042,2.256,-0.5,4.312,0.76,5.767],[\"c\",-0.393,9.746,-1.299,19.469,-1.584,29.208],[\"c\",-11.523,15.95,-33.467,31.272,-21.338,53.403],[\"c\",3.904,7.125,9.338,12.539,15.66,16.737],[\"c\",0.512,24.303,5.277,48.19,9.755,72.018],[\"c\",4.683,24.921,8.419,50.056,12.3,75.114],[\"c\",3.963,25.58,7.736,51.189,11.965,76.727],[\"c\",1.714,10.354,2.918,20.77,4.643,31.121],[\"c\",1.177,7.062,2.422,14.504,4.971,21.307],[\"c\",-0.811,1.491,-0.787,3.334,0.735,5.102],[\"c\",15.015,17.431,36.748,25.653,58.849,29.873],[\"c\",18.731,3.576,39.18,5.265,58.236,3.753],[\"c\",16.477,-1.307,33.125,-4.534,48.979,-9.159],[\"c\",10.062,-2.936,29.846,-8.627,30.056,-21.926],[\"c\",0.698,0.023,1.45,-0.104,2.249,-0.426],[\"c\",6.761,-2.725,9.107,-10.356,10.809,-16.812],[\"c\",2.213,-8.399,3.01,-17.168,3.954,-25.779],[\"c\",2.944,-26.846,6.929,-53.635,10.681,-80.379],[\"c\",4.238,-30.206,9.327,-60.248,12.515,-90.586],[\"c\",1.546,-14.72,2.9,-29.46,3.909,-44.227],[\"c\",0.94,-13.756,0.809,-27.493,0.799,-41.272],[\"c\",1.389,-1.011,2.768,-2.039,4.125,-3.103],[\"C\",418.06,162.217,422.519,146.925,417.741,132.497],[\"z\"],[\"M\",186.138,61.473],[\"c\",30.392,-4.032,61.035,-7.943,91.727,-8.312],[\"c\",18.746,-0.226,35.541,0.723,53.703,4.825],[\"c\",7.33,1.656,14.583,3.733,21.508,6.67],[\"c\",4.872,2.066,10.457,4.847,12.896,8.916],[\"c\",0.307,0.512,0.299,1.046,0.092,1.98],[\"c\",0.096,-0.618,-0.782,1.516,-0.594,1.245],[\"c\",-0.694,1,-1.562,1.873,-2.43,2.72],[\"c\",-5.668,5.528,-14.391,8.923,-21.646,11.604],[\"c\",-20.883,7.715,-43.979,8.125,-65.973,8.011],[\"c\",-23.795,-0.123,-47.795,0.042,-71.488,-2.283],[\"c\",-22.892,-2.246,-45.49,-6.782,-67.713,-12.66],[\"C\",147.149,68.266,167.51,63.944,186.138,61.473],[\"z\"],[\"M\",134.502,95.081],[\"c\",27.408,7.2,55.315,12.202,83.653,13.848],[\"c\",14.43,0.838,28.89,0.687,43.338,0.961],[\"c\",13.76,0.261,27.523,0.433,41.268,-0.426],[\"c\",13.302,-0.831,26.602,-2.889,39.318,-6.946],[\"c\",10.001,-3.191,20.789,-8.013,28.619,-15.185],[\"c\",0.016,-0.015,0.03,-0.029,0.046,-0.044],[\"c\",0.867,10.119,1.61,20.448,-2.609,29.594],[\"c\",-0.991,0.207,-1.97,0.714,-2.841,1.614],[\"c\",-26.044,26.924,-66.754,25.188,-101.322,25.146],[\"c\",-23.533,-0.029,-47.117,1.265,-70.602,-0.799],[\"c\",-20.559,-1.806,-39.675,-10.7,-60.2,-12.416],[\"C\",132.743,118.627,133.854,106.865,134.502,95.081],[\"z\"],[\"M\",390.315,213.921],[\"c\",-0.934,14.135,-2.26,28.242,-3.713,42.332],[\"c\",-2.996,29.043,-7.78,57.782,-11.893,86.682],[\"c\",-3.857,27.111,-7.26,54.282,-10.943,81.416],[\"c\",-0.986,7.272,-1.566,14.583,-2.736,21.834],[\"c\",-0.635,3.936,-1.457,7.842,-2.606,11.661],[\"c\",-0.454,1.51,-1.144,2.959,-1.886,4.345],[\"c\",-0.402,0.75,-1.193,1.335,-0.93,1.229],[\"c\",-2.199,0.887,-3.252,2.648,-3.436,4.497],[\"c\",-3.238,-0.512,-6.819,1.462,-6.23,5.617],[\"c\",0.062,0.445,0.115,0.61,0.148,0.663],[\"c\",-0.038,0.051,-0.106,0.208,-0.215,0.633],[\"c\",0.138,-0.541,-0.295,0.29,-0.849,0.919],[\"c\",-1.29,1.466,-3.505,2.753,-5.547,3.887],[\"c\",-6.22,3.454,-13.441,5.429,-20.263,7.287],[\"c\",-13.914,3.791,-28.393,6.333,-42.773,7.474],[\"c\",-17.056,1.353,-35.055,-0.329,-51.872,-3.143],[\"c\",-18.499,-3.096,-38.413,-9.221,-51.948,-22.876],[\"c\",-0.077,-0.505,-0.233,-1.033,-0.496,-1.584],[\"c\",-3.527,-7.41,-4.897,-15.821,-6.234,-23.849],[\"c\",-1.747,-10.487,-2.973,-21.042,-4.711,-31.535],[\"c\",-4.229,-25.542,-7.982,-51.161,-11.945,-76.745],[\"c\",-3.783,-24.42,-7.461,-48.904,-12.004,-73.194],[\"c\",-4.043,-21.615,-8.357,-43.261,-9.512,-65.233],[\"c\",6.423,2.713,13.279,4.66,20.102,6.201],[\"c\",18.9,4.269,38.391,5.084,57.672,6.125],[\"c\",27.026,1.458,54.213,2.619,81.266,1.345],[\"c\",35.973,-1.694,73.095,-9.173,104.199,-27.902],[\"C\",390.995,192.651,391.017,203.291,390.315,213.921],[\"z\"],[\"M\",407.046,153.033],[\"c\",-4.334,10.502,-16.801,16.666,-26.108,21.963],[\"c\",-11.784,6.707,-24.552,11.597,-37.561,15.33],[\"c\",-26.017,7.465,-53.431,9.102,-80.365,9.132],[\"c\",-23.95,0.026,-47.976,-1.12,-71.878,-2.613],[\"c\",-21.275,-1.33,-44.936,-3.013,-64.261,-13.055],[\"c\",-0.853,-1.665,-2.591,-2.476,-4.411,-2.491],[\"c\",-8.318,-5.171,-16.059,-15.04,-13.608,-25.23],[\"c\",1.343,-5.584,5.882,-10.438,9.558,-14.614],[\"c\",1.37,-1.557,2.77,-3.086,4.162,-4.621],[\"c\",0.585,2.702,3.053,4.241,5.789,4.279],[\"c\",11.601,0.16,22.879,3.419,33.979,6.492],[\"c\",11.567,3.202,22.82,5.639,34.813,6.509],[\"c\",23.977,1.739,48.066,0.461,72.077,0.506],[\"c\",36.605,0.069,76.825,-0.633,104.131,-28.666],[\"c\",1.563,-0.192,3.051,-1.072,3.991,-2.878],[\"c\",1.286,-2.471,2.252,-4.995,2.973,-7.558],[\"c\",0.017,0.003,0.031,0.008,0.049,0.01],[\"C\",397.439,118.222,414.665,134.577,407.046,153.033],[\"z\"]],\"pathOffset\":{\"x\":258.6196550376103,\"y\":273.96577545631976}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":284.62,\"top\":65.46,\"width\":34.84,\"height\":19.45,\"fill\":\"#010101\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",319.415,72.208],[\"c\",-0.15,-1.842,-1.301,-3.744,-3.038,-4.504],[\"c\",-8.138,-3.557,-16.897,-2.283,-25.265,-0.264],[\"c\",-2.192,0.529,-3.346,2.437,-3.553,4.489],[\"c\",-4.873,1.695,-3.504,10.516,2.795,10.687],[\"c\",5.879,0.161,11.895,2.468,18.027,2.29],[\"C\",315.593,84.697,319.987,79.211,319.415,72.208],[\"z\"]],\"pathOffset\":{\"x\":302.04490954904827,\"y\":75.18999868329897}},{\"type\":\"path\",\"originX\":\"left\",\"originY\":\"top\",\"left\":215.41,\"top\":248.66,\"width\":106.12,\"height\":148.97,\"fill\":\"#010101\",\"stroke\":null,\"strokeWidth\":1,\"strokeDashArray\":null,\"strokeLineCap\":\"butt\",\"strokeLineJoin\":\"miter\",\"strokeMiterLimit\":10,\"scaleX\":1,\"scaleY\":1,\"angle\":0,\"flipX\":false,\"flipY\":false,\"opacity\":1,\"shadow\":null,\"visible\":true,\"clipTo\":null,\"backgroundColor\":\"\",\"fillRule\":\"nonzero\",\"globalCompositeOperation\":\"source-over\",\"selectable\":true,\"path\":[[\"M\",307.226,259.682],[\"c\",-5.41,-5.51,-11.652,-8.706,-18.211,-10.089],[\"c\",-0.655,-0.464,-1.492,-0.78,-2.535,-0.869],[\"c\",-1.351,-0.116,-2.655,-0.078,-3.918,0.085],[\"c\",-15.143,-0.584,-31.059,7.26,-41.857,17.886],[\"c\",-20.641,20.311,-19.641,49.034,-23.945,75.644],[\"c\",-0.805,0.932,-1.316,2.171,-1.311,3.703],[\"c\",0.005,1.089,0.021,2.182,0.047,3.276],[\"c\",-0.138,0.829,-0.103,1.585,0.072,2.262],[\"c\",0.349,8.369,1.587,16.747,5.391,24.291],[\"c\",4.541,9.005,12.588,15.001,21.808,18.697],[\"c\",23.562,9.443,45.647,-4.275,59.512,-23.159],[\"c\",12.067,-16.438,17.761,-36.575,19.251,-56.713],[\"C\",322.921,295.866,321.151,273.865,307.226,259.682],[\"z\"],[\"M\",248.612,368.366],[\"c\",-1.639,2.837,-2.488,5.999,-3.078,9.201],[\"c\",-0.37,2.003,-0.521,3.948,-0.523,5.986],[\"c\",0.002,0.045,0.009,0.09,0.011,0.135],[\"c\",-3.196,-1.439,-6.208,-3.346,-8.886,-5.681],[\"c\",-7.803,-6.802,-9.324,-17.686,-9.634,-27.702],[\"c\",5.257,-26.416,2.304,-55.609,22.466,-76.348],[\"c\",4.27,-4.392,9.562,-8.003,15.242,-10.439],[\"c\",-1.611,2.901,-2.879,5.949,-3.779,8.869],[\"c\",-4.725,15.326,1.373,30.982,6.395,45.372],[\"c\",2.59,7.417,5.099,15.107,4.207,23.06],[\"c\",-0.705,6.284,-4.625,10.701,-9.064,14.863],[\"C\",257.519,359.854,251.743,362.945,248.612,368.366],[\"z\"],[\"M\",307.931,331.933],[\"c\",-4.43,20.314,-15.734,40.615,-34.301,51.036],[\"c\",-5.527,3.103,-11.66,4.188,-17.671,3.628],[\"c\",0.017,-1.719,-0.086,-3.552,0.067,-5.335],[\"c\",0.57,-6.647,3.814,-9.718,8.771,-13.625],[\"c\",10.557,-8.319,17.394,-17.598,17.395,-31.416],[\"c\",0,-16.456,-10.244,-31.116,-12.174,-47.283],[\"c\",-0.806,-6.744,0.2,-12.88,3.217,-18.922],[\"c\",1.433,-2.871,4.577,-8.111,8.273,-9.892],[\"c\",9.029,0.416,17.492,4.597,23.027,14.031],[\"C\",314.19,290.608,311.802,314.179,307.931,331.933],[\"z\"]],\"pathOffset\":{\"x\":268.470608056266,\"y\":323.1432178523376}}]}],\"background\":\"rgba(20, 146, 225, 0.47)\",\"customActions\":{}}', 940, 788, NULL, '2018-01-12 15:25:48', '2018-01-12 21:38:55', NULL),
(7, 'Cars.jpg', NULL, 'pipmtr4uesp4cooy.jpeg', 'pipmtr4uesp4cooy', NULL, 162240, 1, 1, NULL, 840, 194, NULL, '2018-01-12 15:54:13', '2018-01-12 15:54:13', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `photos_labels`
--

CREATE TABLE `photos_labels` (
  `id` int(10) UNSIGNED NOT NULL,
  `photo_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `created_at`, `updated_at`) VALUES
(1, 'homeTagline', 'Inner Artist. Changing the way art is created.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'homeByline', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'homeButtonText', 'Register Now', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'homepage', 'landing', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'validExtensions', 'jpg, jpeg, png, gif', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'maxFileSize', '5', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'maxUserSpace', '41943040', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'enableRegistration', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'siteName', 'Inner Artist', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'enableHomeUpload', '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'maxSimultUploads', '10', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'enablePushState', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'dateLocale', 'en', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'pushStateRootUrl', '/', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'disqusShortname', 'pixie', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permissions` text COLLATE utf8_unicode_ci,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `avatar_url`, `gender`, `permissions`, `email`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Development', NULL, NULL, NULL, NULL, '{\"admin\":1}', 'admin@gmail.com', '$2y$10$YbziGvNtF6/4cxQjV1kqSeQABGdUdPG5I/SJ6OdKCxkkjnVI8e/DW', 'A3VfTa81MGDc8jlwQ4RTpteXFrOpxy9AHbMmFj11FEVsK3nIV7ryym6PNW3a', '2018-01-09 10:23:29', '2018-01-16 02:24:28'),
(2, NULL, NULL, NULL, NULL, NULL, NULL, 'demo@gmail.com', '$2y$10$YbziGvNtF6/4cxQjV1kqSeQABGdUdPG5I/SJ6OdKCxkkjnVI8e/DW', 'HHlf6ROQPYra1CZo9cBNABPmyCoSjbmGFK3nHqB35DRIUHJCDHylJGremsaq', '2018-01-12 12:55:07', '2018-01-13 09:18:16'),
(3, NULL, NULL, NULL, NULL, NULL, NULL, 'kai@gmail.com', '$2y$10$VD3b6pB9.DSfDvLR8lIX.ubLIAvuhzzSxgpXYs8Hl0P4bw.xUu5y.', 'wNAW1D9W2iCN7qLdzKJY1dB2Lg8BNc3gv3zyqD8XY1IcDxGg0lk42Sy24vOy', '2018-01-13 09:10:14', '2018-01-13 09:17:26'),
(4, NULL, NULL, NULL, NULL, NULL, NULL, 'a@gmail.com', '$2y$10$krBYoptKATRY8c95xSlWG.C40pfY.tAHuTInybKFHyVj2rF7ljrNa', NULL, '2018-01-15 02:02:28', '2018-01-15 02:02:28'),
(5, NULL, NULL, NULL, NULL, NULL, NULL, 'ak@gmail.com', '$2y$10$cNrJnb3mihqrtKMGtU/UOedrgmG3VN1k1Yc6j/Yfp2g2T0G57IaLS', NULL, '2018-01-15 02:03:49', '2018-01-15 02:03:49'),
(6, NULL, NULL, NULL, NULL, NULL, NULL, 's@gmail.com', '$2y$10$i8EtE6Xfs9tvFFPaz3rS6OWCUyOrhP1.UpLEtWU.uwa8/P5O/4RkS', NULL, '2018-01-15 02:04:07', '2018-01-15 02:04:07'),
(7, NULL, NULL, NULL, NULL, NULL, NULL, 'b@gmail.com', '$2y$10$D8jkRnp/tAlFeWZOaQ2oVOHRHvYTbB8Lp4ANQFbGb/dUhpHrFbuq2', NULL, '2018-01-15 02:04:22', '2018-01-15 02:04:22'),
(8, NULL, NULL, NULL, NULL, NULL, NULL, 'c@gmail.com', '$2y$10$D4O/b/6kPRMBZSwS7dU/UeYyw7CIECKwcbW/3YQ2wuwjJlq72aZGi', NULL, '2018-01-15 02:04:37', '2018-01-15 02:04:37'),
(9, NULL, NULL, NULL, NULL, NULL, NULL, 'd@gmail.com', '$2y$10$W2MhM7623JbIHesoNK3uL.HUYTBBLcEAIiS1pCFdKRA/FYKO2ZY2e', NULL, '2018-01-15 02:04:53', '2018-01-15 02:04:53'),
(10, NULL, NULL, NULL, NULL, NULL, NULL, 'e@gmail.com', '$2y$10$w6OZRx.mNw/3IgurDk/Fo.d5SlKt5ZL4.KQv7B0uPckEVunZt18Zi', NULL, '2018-01-15 02:05:23', '2018-01-15 02:05:23'),
(11, NULL, NULL, NULL, NULL, NULL, NULL, 'f@gmail.com', '$2y$10$tOCBegZaqXEpe.cxXQy33.9mf5C/kzhunY1LzJule0pRimjfTAzpW', NULL, '2018-01-15 02:05:47', '2018-01-15 02:05:47'),
(12, NULL, NULL, NULL, NULL, NULL, NULL, 'g@gmail.com', '$2y$10$FbPbA8Dx89IU9aog63f4iubYr0BwqXdcwnesp6LtZ3t3clQNnbKSq', NULL, '2018-01-15 02:06:03', '2018-01-15 02:06:03'),
(13, NULL, NULL, NULL, NULL, NULL, NULL, 'h@gmail.com', '$2y$10$AoUb9oN/SUxSxSnEH8fGuusbr/CuYlwpaDvpoSxyZpE0tospZC4cW', NULL, '2018-01-15 02:06:20', '2018-01-15 02:06:20'),
(14, NULL, NULL, NULL, NULL, NULL, NULL, 'i@gmail.com', '$2y$10$LkbweH4IGaLsdJ.b1WlRluID6xYE0oQbRQTIr9iHt8F0Cb7wMxUl6', NULL, '2018-01-15 02:06:34', '2018-01-15 02:06:34'),
(15, NULL, NULL, NULL, NULL, NULL, NULL, 'j@gmail.com', '$2y$10$uqvmu8qZNqEzmDUpG1jxMOiExA8jd5VmLiWnMXb.OHvOXH7w0ws2O', NULL, '2018-01-15 02:06:47', '2018-01-15 02:06:47'),
(16, NULL, NULL, NULL, NULL, NULL, NULL, 'k@gmail.com', '$2y$10$waPBTprVvMxr4lxzzNSLJOfRYL/I4JHdCympI/6bbzotqLX7XPQRO', NULL, '2018-01-15 02:07:07', '2018-01-15 02:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `users_oauth`
--

CREATE TABLE `users_oauth` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `service` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inner_account_user_id_foreign` (`user_id`);

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_user_id_index` (`user_id`);

--
-- Indexes for table `dp_activity`
--
ALTER TABLE `dp_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_user_id_index` (`user_id`);

--
-- Indexes for table `dp_folders`
--
ALTER TABLE `dp_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folders_user_id_index` (`user_id`),
  ADD KEY `folders_share_id_index` (`share_id`);

--
-- Indexes for table `dp_labels`
--
ALTER TABLE `dp_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `labels_name_unique` (`name`);

--
-- Indexes for table `dp_orders`
--
ALTER TABLE `dp_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dp_password_resets`
--
ALTER TABLE `dp_password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `dp_photos`
--
ALTER TABLE `dp_photos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photos_attach_id_unique` (`attach_id`),
  ADD KEY `photos_user_id_index` (`user_id`),
  ADD KEY `photos_share_id_index` (`share_id`),
  ADD KEY `photos_attach_id_index` (`attach_id`);

--
-- Indexes for table `dp_photos_labels`
--
ALTER TABLE `dp_photos_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photos_labels_photo_id_label_id_unique` (`photo_id`,`label_id`),
  ADD KEY `photos_labels_photo_id_index` (`photo_id`),
  ADD KEY `photos_labels_label_id_index` (`label_id`);

--
-- Indexes for table `dp_products`
--
ALTER TABLE `dp_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dp_settings`
--
ALTER TABLE `dp_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_name_unique` (`name`);

--
-- Indexes for table `dp_users`
--
ALTER TABLE `dp_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `dp_users_oauth`
--
ALTER TABLE `dp_users_oauth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_oauth_user_id_service_unique` (`user_id`,`service`),
  ADD UNIQUE KEY `users_oauth_token_unique` (`token`),
  ADD KEY `users_oauth_user_id_index` (`user_id`);

--
-- Indexes for table `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `folders_user_id_index` (`user_id`),
  ADD KEY `folders_share_id_index` (`share_id`);

--
-- Indexes for table `inner_account`
--
ALTER TABLE `inner_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inner_account_user_id_foreign` (`user_id`);

--
-- Indexes for table `labels`
--
ALTER TABLE `labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `labels_name_unique` (`name`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`),
  ADD KEY `password_resets_token_index` (`token`);

--
-- Indexes for table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photos_attach_id_unique` (`attach_id`),
  ADD KEY `photos_user_id_index` (`user_id`),
  ADD KEY `photos_share_id_index` (`share_id`),
  ADD KEY `photos_attach_id_index` (`attach_id`);

--
-- Indexes for table `photos_labels`
--
ALTER TABLE `photos_labels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `photos_labels_photo_id_label_id_unique` (`photo_id`,`label_id`),
  ADD KEY `photos_labels_photo_id_index` (`photo_id`),
  ADD KEY `photos_labels_label_id_index` (`label_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `users_oauth`
--
ALTER TABLE `users_oauth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_oauth_user_id_service_unique` (`user_id`,`service`),
  ADD UNIQUE KEY `users_oauth_token_unique` (`token`),
  ADD KEY `users_oauth_user_id_index` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `dp_activity`
--
ALTER TABLE `dp_activity`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dp_folders`
--
ALTER TABLE `dp_folders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `dp_labels`
--
ALTER TABLE `dp_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `dp_orders`
--
ALTER TABLE `dp_orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `dp_photos`
--
ALTER TABLE `dp_photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dp_photos_labels`
--
ALTER TABLE `dp_photos_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dp_products`
--
ALTER TABLE `dp_products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `dp_settings`
--
ALTER TABLE `dp_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `dp_users`
--
ALTER TABLE `dp_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `dp_users_oauth`
--
ALTER TABLE `dp_users_oauth`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `inner_account`
--
ALTER TABLE `inner_account`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `labels`
--
ALTER TABLE `labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `photos_labels`
--
ALTER TABLE `photos_labels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `users_oauth`
--
ALTER TABLE `users_oauth`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
