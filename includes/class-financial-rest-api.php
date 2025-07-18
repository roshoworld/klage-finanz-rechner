<?php
/**
 * Financial REST API
 * Provides REST API endpoints for financial data
 */

class CAH_Financial_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        register_rest_route('cah-financial/v1', '/case/(?P<case_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_case_financial'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('cah-financial/v1', '/case/(?P<case_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_case_financial'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('cah-financial/v1', '/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_templates'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route('cah-financial/v1', '/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_totals'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    public function check_permissions() {
        return current_user_can('manage_options');
    }
    
    public function get_case_financial($request) {
        $case_id = $request['case_id'];
        
        $db_manager = new CAH_Financial_DB_Manager();
        $financial_data = $db_manager->get_case_financial_data($case_id);
        
        if (!$financial_data) {
            return new WP_Error('no_financial_data', 'No financial data found for this case', array('status' => 404));
        }
        
        // Parse cost items
        $financial_data->cost_items = json_decode($financial_data->cost_items, true);
        
        return rest_ensure_response($financial_data);
    }
    
    public function update_case_financial($request) {
        $case_id = $request['case_id'];
        $params = $request->get_params();
        
        $calculator = new CAH_Financial_Calculator_Engine();
        $db_manager = new CAH_Financial_DB_Manager();
        
        $cost_items = $params['cost_items'];
        $mwst_rate = isset($params['mwst_rate']) ? $params['mwst_rate'] : 19.00;
        
        $calculation = $calculator->calculate_totals($cost_items, $mwst_rate);
        
        $financial_data = array(
            'cost_items' => json_encode($cost_items),
            'subtotal' => $calculation['subtotal'],
            'mwst_amount' => $calculation['mwst_amount'],
            'mwst_rate' => $mwst_rate,
            'total' => $calculation['total']
        );
        
        $db_manager->update_case_financial_data($case_id, $financial_data);
        
        return rest_ensure_response($calculation);
    }
    
    public function get_templates($request) {
        $template_manager = new CAH_Financial_Template_Manager();
        $templates = $template_manager->get_all_templates();
        
        // Parse cost items for each template
        foreach ($templates as $template) {
            $template->cost_items = json_decode($template->cost_items, true);
        }
        
        return rest_ensure_response($templates);
    }
    
    public function calculate_totals($request) {
        $params = $request->get_params();
        
        $calculator = new CAH_Financial_Calculator();
        
        $cost_items = $params['cost_items'];
        $mwst_rate = isset($params['mwst_rate']) ? $params['mwst_rate'] : 19.00;
        
        $calculation = $calculator->calculate_totals($cost_items, $mwst_rate);
        
        return rest_ensure_response($calculation);
    }
}