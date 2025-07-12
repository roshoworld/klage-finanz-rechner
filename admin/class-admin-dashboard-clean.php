<?php
/**
 * Admin Dashboard class - Clean Fix v1.1.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function admin_init() {
        register_setting('klage_click_settings', 'klage_click_n8n_url');
        register_setting('klage_click_settings', 'klage_click_n8n_key');
        register_setting('klage_click_settings', 'klage_click_debug_mode');
        
        // Add AJAX handlers for file downloads
        add_action('wp_ajax_klage_download_template', array($this, 'ajax_download_template'));
        add_action('wp_ajax_klage_export_calculation', array($this, 'ajax_export_calculation'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Klage.Click Hub', 'court-automation-hub'),
            __('Klage.Click Hub', 'court-automation-hub'),
            'manage_options',
            'klage-click-hub',
            array($this, 'admin_page_dashboard'),
            'dashicons-hammer',
            30
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Fälle', 'court-automation-hub'),
            __('Fälle', 'court-automation-hub'),
            'manage_options',
            'klage-click-cases',
            array($this, 'admin_page_cases')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Finanz-Rechner', 'court-automation-hub'),
            __('Finanz-Rechner', 'court-automation-hub'),
            'manage_options',
            'klage-click-financial',
            array($this, 'admin_page_financial')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('CSV Import', 'court-automation-hub'),
            __('CSV Import', 'court-automation-hub'),
            'manage_options',
            'klage-click-import',
            array($this, 'admin_page_import')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Hilfe & Prozesse', 'court-automation-hub'),
            __('Hilfe & Prozesse', 'court-automation-hub'),
            'manage_options',
            'klage-click-help',
            array($this, 'admin_page_help')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Einstellungen', 'court-automation-hub'),
            __('Einstellungen', 'court-automation-hub'),
            'manage_options',
            'klage-click-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    public function admin_page_dashboard() {
        echo '<div class="wrap"><h1>Dashboard - v1.1.2 Works!</h1><p>Clean file structure restored.</p></div>';
    }
    
    public function admin_page_cases() {
        echo '<div class="wrap"><h1>Cases - v1.1.2</h1><p>Cases functionality will be restored.</p></div>';
    }
    
    public function admin_page_financial() {
        echo '<div class="wrap"><h1>Financial Calculator - v1.1.2</h1><p>Calculator functionality will be restored.</p></div>';
    }
    
    public function admin_page_import() {
        echo '<div class="wrap"><h1>CSV Import - v1.1.2</h1><p>Import functionality will be restored.</p></div>';
    }
    
    public function admin_page_help() {
        echo '<div class="wrap"><h1>Help & Processes - v1.1.2</h1><p>Help system will be restored.</p></div>';
    }
    
    public function admin_page_settings() {
        echo '<div class="wrap"><h1>Settings - v1.1.2</h1><p>Settings functionality will be restored.</p></div>';
    }
    
    public function ajax_download_template() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['_wpnonce'], 'download_template')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Create CSV template
        $filename = 'forderungen_import_template_' . date('Y-m-d') . '.csv';
        
        // Set headers for download
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8 Excel compatibility
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // CSV Header
        $header = array(
            'Fall-ID',
            'Fall-Status', 
            'Brief-Status',
            'Mandant',
            'Einreichungsdatum',
            'Beweise',
            'Firmenname',
            'Vorname',
            'Nachname', 
            'Adresse',
            'Postleitzahl',
            'Stadt',
            'Land',
            'Email',
            'Telefon',
            'Notizen'
        );
        
        echo implode(';', $header) . "\n";
        
        // Sample data
        echo "SPAM-2024-0001;draft;pending;Ihre Firma GmbH;2024-01-15;SPAM E-Mail;;Max;Mustermann;Musterstraße 123;12345;Musterstadt;Deutschland;spam@example.com;+49123456789;Test\n";
        
        exit;
    }
    
    public function ajax_export_calculation() {
        echo "CSV Export functionality - v1.1.2";
        exit;
    }
}