<?php
/**
 * Plugin Name: Court Automation Hub
 * Plugin URI: https://klage.click
 * Description: Multi-purpose legal automation platform for German courts with AI-powered processing
 * Version: 1.4.2
 * Author: Klage.Click
 * Text Domain: court-automation-hub
 * Domain Path: /languages
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CAH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAH_PLUGIN_VERSION', '1.4.2');

// Main plugin class
class CourtAutomationHub {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Load text domain
        load_plugin_textdomain('court-automation-hub', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        // Include required files
        $this->includes();
        
        // Initialize components
        $this->init_components();
        
        // Add hooks
        $this->add_hooks();
    }
    
    private function includes() {
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-schema-manager.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-form-generator.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-import-export-manager.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-database-admin.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-case-manager.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-audit-logger.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-debtor-manager.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-email-evidence.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-financial-calculator.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-legal-framework.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-court-manager.php';
        require_once CAH_PLUGIN_PATH . 'includes/class-n8n-connector.php';
        require_once CAH_PLUGIN_PATH . 'admin/class-admin-dashboard.php';
        require_once CAH_PLUGIN_PATH . 'api/class-rest-api.php';
    }
    
    private function init_components() {
        // Initialize schema manager and auto-sync database
        $schema_manager = new CAH_Schema_Manager();
        $schema_manager->synchronize_all_tables();
        
        // Initialize all components
        $this->database = new CAH_Database();
        $this->admin_dashboard = new CAH_Admin_Dashboard();
        $this->database_admin = new CAH_Database_Admin();
        $this->rest_api = new CAH_REST_API();
        $this->audit_logger = new CAH_Audit_Logger();
        $this->case_manager = new CAH_Case_Manager();
        $this->debtor_manager = new CAH_Debtor_Manager();
        $this->email_evidence = new CAH_Email_Evidence();
        $this->financial_calculator = new CAH_Financial_Calculator();
        $this->legal_framework = new CAH_Legal_Framework();
        $this->court_manager = new CAH_Court_Manager();
        $this->n8n_connector = new CAH_N8N_Connector();
    }
    
    private function add_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('cah-frontend', CAH_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CAH_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-frontend', CAH_PLUGIN_URL . 'assets/css/frontend.css', array(), CAH_PLUGIN_VERSION);
    }
    
    public function admin_enqueue_scripts() {
        wp_enqueue_script('cah-admin', CAH_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CAH_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-admin', CAH_PLUGIN_URL . 'assets/css/admin.css', array(), CAH_PLUGIN_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('cah-admin', 'cah_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cah_admin_nonce')
        ));
    }
    
    public function activate() {
        // Include database class for activation
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        
        // Create database tables
        $database = new CAH_Database();
        $database->create_tables_direct();
        
        // Add capabilities
        $this->add_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function add_capabilities() {
        $capabilities = array(
            'manage_klage_click_cases',
            'edit_klage_click_cases', 
            'view_klage_click_cases',
            'manage_klage_click_debtors',
            'manage_klage_click_documents',
            'manage_klage_click_templates',
            'manage_klage_click_settings'
        );
        
        $administrator = get_role('administrator');
        if ($administrator) {
            foreach ($capabilities as $capability) {
                $administrator->add_cap($capability);
            }
        }
    }
}

// Initialize the plugin
new CourtAutomationHub();