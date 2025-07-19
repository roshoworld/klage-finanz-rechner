<?php
/**
 * Financial Database Manager
 * Handles database operations for financial calculator
 */

class CAH_Financial_DB_Manager {
    
    public function __construct() {
        add_action('init', array($this, 'init_db'));
    }
    
    public function init_db() {
        $this->create_tables();
    }
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Cost items table
        $cost_items_table = $wpdb->prefix . 'cah_cost_items';
        $cost_items_sql = "CREATE TABLE $cost_items_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            description text,
            default_amount decimal(10,2) DEFAULT 0.00,
            category varchar(100) DEFAULT 'general',
            is_active tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name),
            KEY category (category),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Financial templates table
        $templates_table = $wpdb->prefix . 'cah_financial_templates';
        $templates_sql = "CREATE TABLE $templates_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            description text,
            is_default tinyint(1) DEFAULT 0,
            cost_items longtext,
            mwst_rate decimal(5,2) DEFAULT 19.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name)
        ) $charset_collate;";
        
        // Case financial data table
        $financial_table = $wpdb->prefix . 'cah_case_financial';
        $financial_sql = "CREATE TABLE $financial_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            case_id int(11) NOT NULL,
            template_id int(11),
            cost_items longtext,
            subtotal decimal(10,2) DEFAULT 0.00,
            mwst_amount decimal(10,2) DEFAULT 0.00,
            mwst_rate decimal(5,2) DEFAULT 19.00,
            total decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY case_id (case_id),
            KEY template_id (template_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($cost_items_sql);
        dbDelta($templates_sql);
        dbDelta($financial_sql);
        
        // Create default cost items if none exist
        $this->create_default_cost_items();
    }
    
    public function delete_case_financial_data($case_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_case_financial';
        $wpdb->delete($table_name, array('case_id' => $case_id));
    }
    
    public function get_case_financial_data($case_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_case_financial';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE case_id = %d", $case_id));
    }
    
    public function update_case_financial_data($case_id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_case_financial';
        
        $existing = $this->get_case_financial_data($case_id);
        
        if ($existing) {
            $wpdb->update($table_name, $data, array('case_id' => $case_id));
        } else {
            $data['case_id'] = $case_id;
            $wpdb->insert($table_name, $data);
        }
    }
}