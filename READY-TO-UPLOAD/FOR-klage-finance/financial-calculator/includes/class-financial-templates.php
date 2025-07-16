<?php
/**
 * Financial Templates Management
 * Manages global financial calculation templates
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Templates {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    public function create_default_templates() {
        $this->create_gdpr_template();
        $this->create_contract_template();
        $this->create_general_template();
    }
    
    private function create_gdpr_template() {
        $template_table = $this->wpdb->prefix . 'cah_financial_templates';
        $items_table = $this->wpdb->prefix . 'cah_financial_template_items';
        
        // Create GDPR template
        $template_id = $this->create_template(
            'GDPR Standard',
            'GDPR',
            'Standard GDPR violation calculation (€548.11)',
            true
        );
        
        // Add template items
        $gdpr_items = array(
            array(
                'item_name' => 'Grundschaden',
                'item_category' => 'Damages',
                'default_amount' => 350.00,
                'is_taxable' => 0,
                'description' => 'DSGVO Art. 82 Schadenersatz',
                'display_order' => 1
            ),
            array(
                'item_name' => 'Anwaltskosten',
                'item_category' => 'Legal Fees',
                'default_amount' => 96.90,
                'is_taxable' => 1,
                'description' => 'RVG Rechtsanwaltsgebühren',
                'display_order' => 2
            ),
            array(
                'item_name' => 'Kommunikationskosten',
                'item_category' => 'Communication',
                'default_amount' => 13.36,
                'is_taxable' => 1,
                'description' => 'Porto, Telefon, Fax',
                'display_order' => 3
            ),
            array(
                'item_name' => 'Gerichtskosten',
                'item_category' => 'Court Fees',
                'default_amount' => 32.00,
                'is_taxable' => 0,
                'description' => 'Verfahrenskosten',
                'display_order' => 4
            )
        );
        
        foreach ($gdpr_items as $item) {
            $this->add_template_item($template_id, $item);
        }
    }
    
    private function create_contract_template() {
        $template_id = $this->create_template(
            'Contract Dispute',
            'CONTRACT',
            'Standard contract dispute calculation',
            false
        );
        
        $contract_items = array(
            array(
                'item_name' => 'Vertragsverletzung',
                'item_category' => 'Damages',
                'default_amount' => 500.00,
                'is_taxable' => 0,
                'description' => 'Grundschaden bei Vertragsverletzung',
                'display_order' => 1
            ),
            array(
                'item_name' => 'Anwaltskosten',
                'item_category' => 'Legal Fees',
                'default_amount' => 150.00,
                'is_taxable' => 1,
                'description' => 'Anwaltsgebühren nach RVG',
                'display_order' => 2
            ),
            array(
                'item_name' => 'Gerichtskosten',
                'item_category' => 'Court Fees',
                'default_amount' => 75.00,
                'is_taxable' => 0,
                'description' => 'Gerichtsgebühren',
                'display_order' => 3
            )
        );
        
        foreach ($contract_items as $item) {
            $this->add_template_item($template_id, $item);
        }
    }
    
    private function create_general_template() {
        $template_id = $this->create_template(
            'General Case',
            'GENERAL',
            'General case calculation template',
            false
        );
        
        $general_items = array(
            array(
                'item_name' => 'Schadenersatz',
                'item_category' => 'Damages',
                'default_amount' => 0.00,
                'is_taxable' => 0,
                'description' => 'Grundschaden',
                'display_order' => 1
            ),
            array(
                'item_name' => 'Anwaltskosten',
                'item_category' => 'Legal Fees',
                'default_amount' => 0.00,
                'is_taxable' => 1,
                'description' => 'Anwaltsgebühren',
                'display_order' => 2
            ),
            array(
                'item_name' => 'Sonstige Kosten',
                'item_category' => 'Other',
                'default_amount' => 0.00,
                'is_taxable' => 0,
                'description' => 'Sonstige Verfahrenskosten',
                'display_order' => 3
            )
        );
        
        foreach ($general_items as $item) {
            $this->add_template_item($template_id, $item);
        }
    }
    
    private function create_template($name, $type, $description, $is_default = false) {
        $table_name = $this->wpdb->prefix . 'cah_financial_templates';
        
        $this->wpdb->insert($table_name, array(
            'template_name' => $name,
            'template_type' => $type,
            'description' => $description,
            'is_default' => $is_default ? 1 : 0,
            'is_active' => 1
        ));
        
        return $this->wpdb->insert_id;
    }
    
    private function add_template_item($template_id, $item) {
        $table_name = $this->wpdb->prefix . 'cah_financial_template_items';
        
        $this->wpdb->insert($table_name, array(
            'template_id' => $template_id,
            'item_name' => $item['item_name'],
            'item_category' => $item['item_category'],
            'default_amount' => $item['default_amount'],
            'is_taxable' => $item['is_taxable'],
            'description' => $item['description'],
            'display_order' => $item['display_order']
        ));
    }
    
    public function get_all_templates() {
        $table_name = $this->wpdb->prefix . 'cah_financial_templates';
        
        return $this->wpdb->get_results(
            "SELECT * FROM $table_name WHERE is_active = 1 ORDER BY is_default DESC, template_name ASC"
        );
    }
    
    public function get_template_items($template_id) {
        $table_name = $this->wpdb->prefix . 'cah_financial_template_items';
        
        return $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM $table_name WHERE template_id = %d ORDER BY display_order ASC",
            $template_id
        ));
    }
    
    public function apply_default_template($case_id) {
        $template_table = $this->wpdb->prefix . 'cah_financial_templates';
        $items_table = $this->wpdb->prefix . 'cah_financial_template_items';
        
        // Get default template
        $default_template = $this->wpdb->get_row(
            "SELECT * FROM $template_table WHERE is_default = 1 AND is_active = 1 LIMIT 1"
        );
        
        if ($default_template) {
            $this->apply_template_to_case($case_id, $default_template->id);
        }
    }
    
    public function apply_template_to_case($case_id, $template_id) {
        $template_items = $this->get_template_items($template_id);
        
        $financial_data = array();
        foreach ($template_items as $item) {
            $financial_data[] = array(
                'template_id' => $template_id,
                'item_name' => $item->item_name,
                'item_category' => $item->item_category,
                'amount' => $item->default_amount,
                'is_taxable' => $item->is_taxable,
                'description' => $item->description,
                'display_order' => $item->display_order
            );
        }
        
        $database = new CAH_Financial_Database();
        $database->save_case_financial_data($case_id, $financial_data);
    }
}