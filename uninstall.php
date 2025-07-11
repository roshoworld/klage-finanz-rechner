<?php
/**
 * Uninstall script for Court Automation Hub
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete all plugin tables
$tables = array(
    $wpdb->prefix . 'klage_cases',
    $wpdb->prefix . 'klage_debtors',
    $wpdb->prefix . 'klage_clients',
    $wpdb->prefix . 'klage_emails',
    $wpdb->prefix . 'klage_financial',
    $wpdb->prefix . 'klage_legal',
    $wpdb->prefix . 'klage_courts',
    $wpdb->prefix . 'klage_audit'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete all plugin options
$options = array(
    'klage_click_n8n_url',
    'klage_click_n8n_key',
    'klage_click_egvp_url',
    'klage_click_egvp_key',
    'klage_click_debug_mode',
    'klage_click_api_key',
    'klage_click_webhook_secret'
);

foreach ($options as $option) {
    delete_option($option);
}

// Remove capabilities from all roles
$roles = wp_roles()->roles;
$capabilities = array(
    'manage_klage_click_cases',
    'edit_klage_click_cases',
    'view_klage_click_cases',
    'manage_klage_click_debtors',
    'manage_klage_click_documents',
    'manage_klage_click_templates',
    'manage_klage_click_settings'
);

foreach ($roles as $role_name => $role_info) {
    $role = get_role($role_name);
    if ($role) {
        foreach ($capabilities as $cap) {
            $role->remove_cap($cap);
        }
    }
}