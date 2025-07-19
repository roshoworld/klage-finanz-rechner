<?php
/**
 * Plugin Name: Court Automation Hub - Financial Calculator
 * Plugin URI: https://klage.click/financial-calculator
 * Description: Advanced financial calculator for legal proceedings with template management and case integration
 * Version: 1.0.5
 * Author: Klage.Click
 * Text Domain: court-automation-hub-financial
 * Domain Path: /languages
 * License: GPL v2 or later
 * Requires Plugins: court-automation-hub
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CAH_FC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CAH_FC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAH_FC_PLUGIN_VERSION', '1.0.5');

// Main plugin class
class CAH_Financial_Calculator_Plugin {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check if core plugin is active
        if (!$this->is_core_plugin_active()) {
            add_action('admin_notices', array($this, 'core_plugin_required_notice'));
            return;
        }
        
        // Load text domain
        load_plugin_textdomain('court-automation-hub-financial', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        // Include required files
        $this->includes();
        
        // Initialize components
        $this->init_components();
        
        // Add hooks
        $this->add_hooks();
    }
    
    private function is_core_plugin_active() {
        return class_exists('CourtAutomationHub');
    }
    
    public function core_plugin_required_notice() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Court Automation Hub - Financial Calculator</strong> erfordert das aktivierte Core Plugin "Court Automation Hub".</p>
        </div>
        <?php
    }
    
    private function includes() {
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-financial-db-manager.php';
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-financial-calculator.php';
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-financial-template-manager.php';
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-financial-admin.php';
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-financial-rest-api.php';
        require_once CAH_FC_PLUGIN_PATH . 'includes/class-case-financial-integration.php';
    }
    
    private function init_components() {
        // Initialize database manager and create tables
        $this->db_manager = new CAH_Financial_DB_Manager();
        $this->db_manager->create_tables();
        
        // Initialize all components
        $this->calculator = new CAH_Financial_Calculator_Engine();
        $this->template_manager = new CAH_Financial_Template_Manager();
        $this->admin = new CAH_Financial_Admin();
        $this->rest_api = new CAH_Financial_REST_API();
        $this->case_integration = new CAH_Case_Financial_Integration();
    }
    
    private function add_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function admin_enqueue_scripts() {
        wp_enqueue_script('cah-financial-admin', CAH_FC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CAH_FC_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-financial-admin', CAH_FC_PLUGIN_URL . 'assets/css/admin.css', array(), CAH_FC_PLUGIN_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('cah-financial-admin', 'cah_financial_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cah_financial_nonce')
        ));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('cah-financial-frontend', CAH_FC_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), CAH_FC_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-financial-frontend', CAH_FC_PLUGIN_URL . 'assets/css/frontend.css', array(), CAH_FC_PLUGIN_VERSION);
    }
    
    public function activate() {
        // Create database tables
        $db_manager = new CAH_Financial_DB_Manager();
        $db_manager->create_tables();
        
        // Create default templates
        $template_manager = new CAH_Financial_Template_Manager();
        $template_manager->create_default_templates();
    }
    
    public function deactivate() {
        // Clean up if needed
    }
}

// Initialize the plugin
new CAH_Financial_Calculator_Plugin();