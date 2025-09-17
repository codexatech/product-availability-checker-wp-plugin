<?php

/**
 * Plugin Name: Product Availability Checker
 * Description: Manage WooCommerce product availability by zip code.
 * Version: 1.0
 * Author: Codexa Craft
 * Text Domain: product-availability-checker
 *
 * @package ProductAvailabilityChecker
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin paths and URLs.
define('PAC_PATH', plugin_dir_path(__FILE__));
define('PAC_URL', plugin_dir_url(__FILE__));

// Include required classes.
require_once PAC_PATH . 'includes/class-pac-handler.php';
require_once PAC_PATH . 'includes/class-pac-admin.php';
require_once PAC_PATH . 'includes/class-pac-frontend.php';

/**
 * Initialize plugin classes.
 */
add_action('plugins_loaded', function () {

    // Initialize handler (used by admin and frontend classes).
    $handler = new PAC_Handler();

    // Initialize admin class only in admin screens.
    if (is_admin()) {
        new PAC_Admin();
    }

    // Initialize frontend class if WooCommerce is active.
    if (class_exists('WooCommerce')) {
        new PAC_Frontend();
    }
}, 20);


