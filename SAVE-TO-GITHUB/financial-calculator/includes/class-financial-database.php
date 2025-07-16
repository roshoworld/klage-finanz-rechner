<?php
/**
 * Financial Database Management
 * Creates and manages financial calculator database tables
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Database {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    public function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        $tables = array(
            'cah_financial_templates' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_financial_templates (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                template_name varchar(100) NOT NULL,
                template_type varchar(50) NOT NULL,
                description text,
                is_default tinyint(1) DEFAULT 0,
                is_active tinyint(1) DEFAULT 1,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY template_type (template_type)
            ) $charset_collate",
            
            'cah_financial_template_items' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_financial_template_items (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                template_id bigint(20) unsigned NOT NULL,
                item_name varchar(100) NOT NULL,
                item_category varchar(50) NOT NULL,
                default_amount decimal(10,2) DEFAULT 0.00,
                is_taxable tinyint(1) DEFAULT 0,
                description text,
                display_order int(3) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY template_id (template_id),
                KEY item_category (item_category)
            ) $charset_collate",
            
            'cah_case_financial_data' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}cah_case_financial_data (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                template_id bigint(20) unsigned,
                item_name varchar(100) NOT NULL,
                item_category varchar(50) NOT NULL,
                amount decimal(10,2) DEFAULT 0.00,
                is_taxable tinyint(1) DEFAULT 0,
                description text,
                display_order int(3) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY template_id (template_id),
                KEY item_category (item_category)
            ) $charset_collate"
        );
        
        foreach ($tables as $table_name => $sql) {
            $this->wpdb->query($sql);
        }
    }
    
    public function delete_case_financial_data($case_id) {
        $table_name = $this->wpdb->prefix . 'cah_case_financial_data';
        $this->wpdb->delete($table_name, array('case_id' => $case_id));
    }
    
    public function get_case_financial_data($case_id) {
        $table_name = $this->wpdb->prefix . 'cah_case_financial_data';
        
        $results = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $table_name WHERE case_id = %d ORDER BY display_order ASC",
            $case_id
        ));
        
        return $results;
    }
    
    public function save_case_financial_data($case_id, $financial_data) {
        $table_name = $this->wpdb->prefix . 'cah_case_financial_data';
        
        // First, delete existing data for this case
        $this->delete_case_financial_data($case_id);
        
        // Insert new data
        foreach ($financial_data as $item) {
            $this->wpdb->insert($table_name, array(
                'case_id' => $case_id,
                'template_id' => $item['template_id'] ?? null,
                'item_name' => $item['item_name'],
                'item_category' => $item['item_category'],
                'amount' => $item['amount'],
                'is_taxable' => $item['is_taxable'] ?? 0,
                'description' => $item['description'] ?? '',
                'display_order' => $item['display_order'] ?? 0
            ));
        }
    }
    
    public function calculate_case_totals($case_id) {
        $financial_data = $this->get_case_financial_data($case_id);
        
        $subtotal = 0;
        $tax_amount = 0;
        $total = 0;
        
        foreach ($financial_data as $item) {
            $subtotal += $item->amount;
            
            if ($item->is_taxable) {
                $tax_amount += $item->amount * 0.19; // 19% German MwSt
            }
        }
        
        $total = $subtotal + $tax_amount;
        
        return array(
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $total
        );
    }
}