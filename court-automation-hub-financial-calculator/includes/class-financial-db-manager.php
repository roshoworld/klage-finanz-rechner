<?php
/**
 * Financial Database Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_DB_Manager {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    public function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $tables = array(
            'cah_financial_templates' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_financial_templates (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description text,
                is_default tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_is_default (is_default),
                INDEX idx_name (name)
            ) $charset_collate",
            
            'cah_cost_items' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_cost_items (
                id int(11) NOT NULL AUTO_INCREMENT,
                template_id int(11),
                case_id int(11) DEFAULT NULL,
                name varchar(255) NOT NULL,
                category enum('grundkosten', 'gerichtskosten', 'anwaltskosten', 'sonstige') NOT NULL DEFAULT 'grundkosten',
                amount decimal(10,2) NOT NULL DEFAULT 0.00,
                description text,
                is_percentage tinyint(1) DEFAULT 0,
                sort_order int(11) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_template_id (template_id),
                INDEX idx_case_id (case_id),
                INDEX idx_category (category),
                FOREIGN KEY (template_id) REFERENCES {$this->wpdb->prefix}cah_financial_templates(id) ON DELETE CASCADE
            ) $charset_collate",
            
            'cah_case_financial' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_case_financial (
                id int(11) NOT NULL AUTO_INCREMENT,
                case_id int(11) NOT NULL,
                template_id int(11),
                subtotal decimal(10,2) DEFAULT 0.00,
                vat_rate decimal(5,2) DEFAULT 19.00,
                vat_amount decimal(10,2) DEFAULT 0.00,
                total_amount decimal(10,2) DEFAULT 0.00,
                notes text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_case (case_id),
                INDEX idx_template_id (template_id),
                FOREIGN KEY (template_id) REFERENCES {$this->wpdb->prefix}cah_financial_templates(id) ON DELETE SET NULL
            ) $charset_collate"
        );
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($tables as $table_name => $sql) {
            dbDelta($sql);
        }
    }
    
    // Template CRUD operations
    public function create_template($name, $description = '', $is_default = false) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'cah_financial_templates',
            array(
                'name' => $name,
                'description' => $description,
                'is_default' => $is_default ? 1 : 0
            ),
            array('%s', '%s', '%d')
        );
    }
    
    public function get_template($id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}cah_financial_templates WHERE id = %d",
                $id
            )
        );
    }
    
    public function get_templates($include_default = true) {
        $where = $include_default ? '' : 'WHERE is_default = 0';
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->wpdb->prefix}cah_financial_templates {$where} ORDER BY is_default DESC, name ASC"
        );
    }
    
    public function update_template($id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'cah_financial_templates',
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
    }
    
    public function delete_template($id) {
        // First delete associated cost items
        $this->wpdb->delete(
            $this->wpdb->prefix . 'cah_cost_items',
            array('template_id' => $id),
            array('%d')
        );
        
        // Then delete template
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'cah_financial_templates',
            array('id' => $id),
            array('%d')
        );
    }
    
    // Cost Item CRUD operations
    public function create_cost_item($template_id, $case_id, $name, $category, $amount, $description = '', $is_percentage = false, $sort_order = 0) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'cah_cost_items',
            array(
                'template_id' => $template_id,
                'case_id' => $case_id,
                'name' => $name,
                'category' => $category,
                'amount' => $amount,
                'description' => $description,
                'is_percentage' => $is_percentage ? 1 : 0,
                'sort_order' => $sort_order
            ),
            array('%d', '%d', '%s', '%s', '%f', '%s', '%d', '%d')
        );
    }
    
    public function get_cost_items_by_template($template_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}cah_cost_items WHERE template_id = %d AND case_id IS NULL ORDER BY sort_order ASC, category ASC",
                $template_id
            )
        );
    }
    
    public function get_cost_items_by_case($case_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}cah_cost_items WHERE case_id = %d ORDER BY sort_order ASC, category ASC",
                $case_id
            )
        );
    }
    
    public function update_cost_item($id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'cah_cost_items',
            $data,
            array('id' => $id),
            null,
            array('%d')
        );
    }
    
    public function delete_cost_item($id) {
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'cah_cost_items',
            array('id' => $id),
            array('%d')
        );
    }
    
    // Case Financial CRUD operations
    public function create_case_financial($case_id, $template_id = null) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'cah_case_financial',
            array(
                'case_id' => $case_id,
                'template_id' => $template_id
            ),
            array('%d', '%d')
        );
    }
    
    public function get_case_financial($case_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}cah_case_financial WHERE case_id = %d",
                $case_id
            )
        );
    }
    
    public function update_case_financial($case_id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'cah_case_financial',
            $data,
            array('case_id' => $case_id),
            null,
            array('%d')
        );
    }
    
    public function delete_case_financial($case_id) {
        // First delete associated cost items
        $this->wpdb->delete(
            $this->wpdb->prefix . 'cah_cost_items',
            array('case_id' => $case_id),
            array('%d')
        );
        
        // Then delete case financial record
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'cah_case_financial',
            array('case_id' => $case_id),
            array('%d')
        );
    }
}