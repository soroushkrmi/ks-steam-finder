<?php
/**
 * Plugin Name: KhasteShop Steam Finder
 * Description: ربات جستجوگر و افزودن بازی‌های استیم به سبد خرید ووکامرس
 * Version: 1.0.0
 * Author: Soroush Karami
 */

if (!defined('ABSPATH')) {
    exit; // جلوگیری از دسترسی مستقیم
}

// تعریف مسیر اصلی پلاگین
define('KS_FINDER_PATH', plugin_dir_path(__FILE__));

// بارگذاری ماژول‌ها
require_once KS_FINDER_PATH . 'includes/class-ks-scraper.php';
require_once KS_FINDER_PATH . 'includes/class-ks-ajax.php';
require_once KS_FINDER_PATH . 'includes/class-ks-woocommerce.php';
require_once KS_FINDER_PATH . 'includes/class-ks-shortcode.php';