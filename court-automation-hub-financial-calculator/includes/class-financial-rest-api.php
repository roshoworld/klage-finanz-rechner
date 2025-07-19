<?php
/**
 * Financial Calculator REST API
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_REST_API {
    
    private $db_manager;
    private $calculator;
    private $template_manager;
    
    public function __construct() {
        $this->db_manager = new CAH_Financial_DB_Manager();
        $this->calculator = new CAH_Financial_Calculator_Engine();
        $this->template_manager = new CAH_Financial_Template_Manager();
        
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        $namespace = 'cah-financial/v1';
        
        // Templates endpoints
        register_rest_route($namespace, '/templates', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_templates'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/templates/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/templates', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_template'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/templates/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_template'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/templates/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_template'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Cost items endpoints
        register_rest_route($namespace, '/cost-items/template/(?P<template_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_template_cost_items'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/cost-items/case/(?P<case_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_case_cost_items'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/cost-items', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_cost_item'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/cost-items/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_cost_item'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/cost-items/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_cost_item'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Calculator endpoints
        register_rest_route($namespace, '/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_totals'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Case financial endpoints
        register_rest_route($namespace, '/case-financial/(?P<case_id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_case_financial'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($namespace, '/case-financial/(?P<case_id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'save_case_financial'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    public function check_permissions() {
        return current_user_can('manage_options');
    }
    
    // Template endpoints
    public function get_templates(WP_REST_Request $request) {
        $templates = $this->db_manager->get_templates();
        
        return new WP_REST_Response($templates, 200);
    }
    
    public function get_template(WP_REST_Request $request) {
        $template_id = $request->get_param('id');
        $template = $this->template_manager->get_template_with_totals($template_id);
        
        if (!$template) {
            return new WP_Error('template_not_found', 'Template not found', array('status' => 404));
        }
        
        return new WP_REST_Response($template, 200);
    }
    
    public function create_template(WP_REST_Request $request) {
        $name = $request->get_param('name');
        $description = $request->get_param('description') ?: '';
        
        if (empty($name)) {
            return new WP_Error('missing_name', 'Template name is required', array('status' => 400));
        }
        
        $template_id = $this->db_manager->create_template($name, $description, false);
        
        if (!$template_id) {
            return new WP_Error('create_failed', 'Failed to create template', array('status' => 500));
        }
        
        $template = $this->db_manager->get_template($template_id);
        return new WP_REST_Response($template, 201);
    }
    
    public function update_template(WP_REST_Request $request) {
        $template_id = $request->get_param('id');
        $name = $request->get_param('name');
        $description = $request->get_param('description');
        
        $template = $this->db_manager->get_template($template_id);
        if (!$template) {
            return new WP_Error('template_not_found', 'Template not found', array('status' => 404));
        }
        
        $update_data = array();
        if (!empty($name)) {
            $update_data['name'] = $name;
        }
        if ($description !== null) {
            $update_data['description'] = $description;
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', array('status' => 400));
        }
        
        $result = $this->db_manager->update_template($template_id, $update_data);
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update template', array('status' => 500));
        }
        
        $updated_template = $this->db_manager->get_template($template_id);
        return new WP_REST_Response($updated_template, 200);
    }
    
    public function delete_template(WP_REST_Request $request) {
        $template_id = $request->get_param('id');
        
        $template = $this->db_manager->get_template($template_id);
        if (!$template) {
            return new WP_Error('template_not_found', 'Template not found', array('status' => 404));
        }
        
        if ($template->is_default) {
            return new WP_Error('cannot_delete_default', 'Cannot delete default template', array('status' => 403));
        }
        
        $result = $this->db_manager->delete_template($template_id);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete template', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Template deleted successfully'), 200);
    }
    
    // Cost items endpoints
    public function get_template_cost_items(WP_REST_Request $request) {
        $template_id = $request->get_param('template_id');
        $items = $this->db_manager->get_cost_items_by_template($template_id);
        
        return new WP_REST_Response($items, 200);
    }
    
    public function get_case_cost_items(WP_REST_Request $request) {
        $case_id = $request->get_param('case_id');
        $items = $this->db_manager->get_cost_items_by_case($case_id);
        
        return new WP_REST_Response($items, 200);
    }
    
    public function create_cost_item(WP_REST_Request $request) {
        $template_id = $request->get_param('template_id');
        $case_id = $request->get_param('case_id');
        $name = $request->get_param('name');
        $category = $request->get_param('category');
        $amount = $request->get_param('amount');
        $description = $request->get_param('description') ?: '';
        $is_percentage = $request->get_param('is_percentage') ?: false;
        $sort_order = $request->get_param('sort_order') ?: 0;
        
        // Validate required fields
        if (empty($name) || empty($category) || !is_numeric($amount)) {
            return new WP_Error('missing_data', 'Name, category, and amount are required', array('status' => 400));
        }
        
        // Validate category
        $valid_categories = array('grundkosten', 'gerichtskosten', 'anwaltskosten', 'sonstige');
        if (!in_array($category, $valid_categories)) {
            return new WP_Error('invalid_category', 'Invalid category', array('status' => 400));
        }
        
        $item_id = $this->db_manager->create_cost_item(
            $template_id,
            $case_id,
            $name,
            $category,
            $amount,
            $description,
            $is_percentage,
            $sort_order
        );
        
        if (!$item_id) {
            return new WP_Error('create_failed', 'Failed to create cost item', array('status' => 500));
        }
        
        return new WP_REST_Response(array('id' => $item_id, 'message' => 'Cost item created successfully'), 201);
    }
    
    public function update_cost_item(WP_REST_Request $request) {
        $item_id = $request->get_param('id');
        
        $update_data = array();
        $fields = array('name', 'category', 'amount', 'description', 'is_percentage', 'sort_order');
        
        foreach ($fields as $field) {
            $value = $request->get_param($field);
            if ($value !== null) {
                $update_data[$field] = $value;
            }
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_data', 'No data to update', array('status' => 400));
        }
        
        $result = $this->db_manager->update_cost_item($item_id, $update_data);
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Failed to update cost item', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Cost item updated successfully'), 200);
    }
    
    public function delete_cost_item(WP_REST_Request $request) {
        $item_id = $request->get_param('id');
        
        $result = $this->db_manager->delete_cost_item($item_id);
        
        if (!$result) {
            return new WP_Error('delete_failed', 'Failed to delete cost item', array('status' => 500));
        }
        
        return new WP_REST_Response(array('message' => 'Cost item deleted successfully'), 200);
    }
    
    // Calculator endpoints
    public function calculate_totals(WP_REST_Request $request) {
        $items = $request->get_param('items');
        $vat_rate = $request->get_param('vat_rate') ?: 19.00;
        
        if (!is_array($items)) {
            return new WP_Error('invalid_items', 'Items must be an array', array('status' => 400));
        }
        
        // Convert array data to objects
        $cost_items = array();
        foreach ($items as $item) {
            $cost_items[] = (object) $item;
        }
        
        $totals = $this->calculator->calculate_totals($cost_items, $vat_rate);
        
        return new WP_REST_Response($totals, 200);
    }
    
    // Case financial endpoints
    public function get_case_financial(WP_REST_Request $request) {
        $case_id = $request->get_param('case_id');
        
        $case_financial = $this->db_manager->get_case_financial($case_id);
        $cost_items = $this->db_manager->get_cost_items_by_case($case_id);
        
        $response = array(
            'case_financial' => $case_financial,
            'cost_items' => $cost_items
        );
        
        if ($cost_items) {
            $totals = $this->calculator->calculate_totals($cost_items);
            $response['totals'] = $totals;
        }
        
        return new WP_REST_Response($response, 200);
    }
    
    public function save_case_financial(WP_REST_Request $request) {
        $case_id = $request->get_param('case_id');
        $template_id = $request->get_param('template_id');
        $items = $request->get_param('items');
        $totals = $request->get_param('totals');
        
        if (!$case_id) {
            return new WP_Error('missing_case_id', 'Case ID is required', array('status' => 400));
        }
        
        // Create or update case financial record
        $existing_financial = $this->db_manager->get_case_financial($case_id);
        
        if ($existing_financial) {
            $financial_data = array();
            if ($template_id) $financial_data['template_id'] = $template_id;
            if ($totals) {
                $financial_data['subtotal'] = $totals['subtotal'];
                $financial_data['vat_rate'] = $totals['vat_rate'];
                $financial_data['vat_amount'] = $totals['vat_amount'];
                $financial_data['total_amount'] = $totals['total_amount'];
            }
            
            if (!empty($financial_data)) {
                $this->db_manager->update_case_financial($case_id, $financial_data);
            }
        } else {
            $this->db_manager->create_case_financial($case_id, $template_id);
            
            if ($totals) {
                $this->db_manager->update_case_financial($case_id, array(
                    'subtotal' => $totals['subtotal'],
                    'vat_rate' => $totals['vat_rate'],
                    'vat_amount' => $totals['vat_amount'],
                    'total_amount' => $totals['total_amount']
                ));
            }
        }
        
        // Update cost items
        if ($items) {
            // Delete existing case cost items
            global $wpdb;
            $wpdb->delete(
                $wpdb->prefix . 'cah_cost_items',
                array('case_id' => $case_id),
                array('%d')
            );
            
            // Create new cost items
            foreach ($items as $item) {
                $this->db_manager->create_cost_item(
                    null,
                    $case_id,
                    $item['name'],
                    $item['category'],
                    $item['amount'],
                    $item['description'] ?: '',
                    $item['is_percentage'] ?: false,
                    $item['sort_order'] ?: 0
                );
            }
        }
        
        return new WP_REST_Response(array('message' => 'Case financial data saved successfully'), 200);
    }
}