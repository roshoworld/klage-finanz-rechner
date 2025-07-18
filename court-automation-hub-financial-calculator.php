<?php
/**
 * Plugin Name: Court Automation Hub - Financial Calculator
 * Plugin URI: https://klage.click
 * Description: Advanced Financial Calculator for Court Automation Hub - Full CRUD system for case finances with templates
 * Version: 1.0.0
 * Author: Klage.Click
 * Text Domain: cah-financial-calculator
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CAH_FINANCIAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAH_FINANCIAL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAH_FINANCIAL_PLUGIN_VERSION', '1.0.0');

// Check if main plugin is active
if (!class_exists('CourtAutomationHub')) {
    add_action('admin_notices', 'cah_financial_admin_notice');
    add_action('admin_init', 'cah_financial_deactivate_self');
    return;
}

// Check for class conflicts (in case old financial calculator classes exist)
if (class_exists('CAH_Financial_Calculator') && !class_exists('CAH_Financial_Calculator_Plugin')) {
    add_action('admin_notices', 'cah_financial_conflict_notice');
    add_action('admin_init', 'cah_financial_deactivate_self');
    return;
}

function cah_financial_admin_notice() {
    ?>
    <div class="notice notice-error">
        <p><strong>Court Automation Hub - Financial Calculator</strong> requires the main "Court Automation Hub" plugin to be installed and activated first.</p>
        <p>Please install and activate the core plugin, then try activating this plugin again.</p>
    </div>
    <?php
}

function cah_financial_conflict_notice() {
    ?>
    <div class="notice notice-error">
        <p><strong>Court Automation Hub - Financial Calculator</strong> detected class conflicts with the core plugin.</p>
        <p>Please ensure you're using the latest separated version of the core plugin that has the financial calculator removed.</p>
    </div>
    <?php
}

function cah_financial_deactivate_self() {
    deactivate_plugins(plugin_basename(__FILE__));
}

// Main plugin class
class CAH_Financial_Calculator_Plugin {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check core plugin version compatibility
        if (defined('CAH_PLUGIN_VERSION') && version_compare(CAH_PLUGIN_VERSION, '1.4.8', '<')) {
            add_action('admin_notices', 'cah_financial_version_notice');
            return;
        }
        
        // Load plugin components
        $this->load_includes();
        
        // Initialize components
        new CAH_Financial_DB_Manager();
        new CAH_Financial_Admin();
        new CAH_Financial_Template_Manager();
        new CAH_Financial_REST_API();
        
        // Add actions for main plugin integration
        add_action('cah_case_created', array($this, 'handle_case_created'));
        add_action('cah_case_updated', array($this, 'handle_case_updated'));
        add_action('cah_case_deleted', array($this, 'handle_case_deleted'));
    }
    
    private function load_includes() {
        $includes = array(
            'includes/class-financial-db-manager.php',
            'includes/class-financial-admin.php',
            'includes/class-financial-template-manager.php',
            'includes/class-financial-rest-api.php',
            'includes/class-financial-calculator.php'
        );
        
        foreach ($includes as $file) {
            $filepath = CAH_FINANCIAL_PLUGIN_PATH . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }
    
    public function activate() {
        // Load required files for activation
        $this->load_includes();
        
        // Create database tables
        $database = new CAH_Financial_DB_Manager();
        $database->create_tables();
        
        // Create default templates
        $templates = new CAH_Financial_Template_Manager();
        $templates->create_default_templates();
        
        // Set activation flag
        update_option('cah_financial_activated', true);
        update_option('cah_financial_version', CAH_FINANCIAL_PLUGIN_VERSION);
    }
    
    public function deactivate() {
        // Clean up activation flag
        delete_option('cah_financial_activated');
    }
    
    public function handle_case_created($case_id) {
        // Apply default template to new case
        if (class_exists('CAH_Financial_Template_Manager')) {
            $templates = new CAH_Financial_Template_Manager();
            $templates->apply_default_template($case_id);
        }
    }
    
    public function handle_case_updated($case_id) {
        // Handle case updates if needed
        do_action('cah_financial_case_updated', $case_id);
    }
    
    public function handle_case_deleted($case_id) {
        // Clean up financial data when case is deleted
        if (class_exists('CAH_Financial_DB_Manager')) {
            $database = new CAH_Financial_DB_Manager();
            $database->delete_case_financial_data($case_id);
        }
    }
}

// Initialize the plugin
new CAH_Financial_Calculator_Plugin();