<?php
/**
 * Uninstall script for Product Availability Checker
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove stored ZIP rules
delete_option('pac_zip_rules');
