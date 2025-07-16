<?php
/**
 * Financial Template Manager
 * Handles CRUD operations for financial templates
 */

class CAH_Financial_Template_Manager {
    
    public function __construct() {
        add_action('admin_init', array($this, 'handle_template_actions'));
    }
    
    public function create_default_templates() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_financial_templates';
        
        // Check if default template already exists
        $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE is_default = 1");
        
        if ($existing == 0) {
            $default_costs = array(
                'grundkosten' => 548.11,
                'gerichtskosten' => 50.00,
                'anwaltskosten' => 200.00,
                'sonstige' => 0.00
            );
            
            $wpdb->insert($table_name, array(
                'name' => 'DSGVO Standard Template',
                'description' => 'Standard financial template for GDPR cases',
                'is_default' => 1,
                'cost_items' => json_encode($default_costs),
                'mwst_rate' => 19.00
            ));
        }
    }
    
    public function apply_default_template($case_id) {
        global $wpdb;
        
        $templates_table = $wpdb->prefix . 'cah_financial_templates';
        $template = $wpdb->get_row("SELECT * FROM $templates_table WHERE is_default = 1 LIMIT 1");
        
        if ($template) {
            $db_manager = new CAH_Financial_DB_Manager();
            $calculator = new CAH_Financial_Calculator();
            
            $cost_items = json_decode($template->cost_items, true);
            $calculation = $calculator->calculate_totals($cost_items, $template->mwst_rate);
            
            $financial_data = array(
                'template_id' => $template->id,
                'cost_items' => $template->cost_items,
                'subtotal' => $calculation['subtotal'],
                'mwst_amount' => $calculation['mwst_amount'],
                'mwst_rate' => $template->mwst_rate,
                'total' => $calculation['total']
            );
            
            $db_manager->update_case_financial_data($case_id, $financial_data);
        }
    }
    
    public function get_all_templates() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_financial_templates';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY is_default DESC, name ASC");
    }
    
    public function get_template($template_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_financial_templates';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $template_id));
    }
    
    public function save_template($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_financial_templates';
        
        if (isset($data['id']) && $data['id'] > 0) {
            $wpdb->update($table_name, $data, array('id' => $data['id']));
            return $data['id'];
        } else {
            $wpdb->insert($table_name, $data);
            return $wpdb->insert_id;
        }
    }
    
    public function delete_template($template_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cah_financial_templates';
        return $wpdb->delete($table_name, array('id' => $template_id));
    }
    
    public function handle_template_actions() {
        if (isset($_POST['financial_template_action'])) {
            $action = $_POST['financial_template_action'];
            
            switch ($action) {
                case 'save':
                    $this->handle_save_template();
                    break;
                case 'delete':
                    $this->handle_delete_template();
                    break;
            }
        }
    }
    
    private function handle_save_template() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'financial_template_save')) {
            return;
        }
        
        $data = array(
            'name' => sanitize_text_field($_POST['template_name']),
            'description' => sanitize_textarea_field($_POST['template_description']),
            'is_default' => isset($_POST['is_default']) ? 1 : 0,
            'cost_items' => json_encode($_POST['cost_items']),
            'mwst_rate' => floatval($_POST['mwst_rate'])
        );
        
        if (isset($_POST['template_id'])) {
            $data['id'] = intval($_POST['template_id']);
        }
        
        $this->save_template($data);
    }
    
    private function handle_delete_template() {
        if (!wp_verify_nonce($_POST['_wpnonce'], 'financial_template_delete')) {
            return;
        }
        
        $template_id = intval($_POST['template_id']);
        $this->delete_template($template_id);
    }
}