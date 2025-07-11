<?php
/**
 * REST API class
 * Handles all REST API endpoints for external integrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Rest_API {
    
    private $namespace = 'klage-click/v1';
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Cases endpoints
        register_rest_route($this->namespace, '/cases', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cases'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($this->namespace, '/cases', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_case'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($this->namespace, '/cases/(?P<id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_case'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        register_rest_route($this->namespace, '/cases/(?P<id>[\d]+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_case'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // N8N webhook endpoints
        register_rest_route($this->namespace, '/webhook/(?P<action>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => array($this, 'check_webhook_permissions')
        ));
        
        // Status endpoint
        register_rest_route($this->namespace, '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_status'),
            'permission_callback' => '__return_true'
        ));
        
        // Document generation endpoint
        register_rest_route($this->namespace, '/cases/(?P<id>[\d]+)/documents', array(
            'methods' => 'POST',
            'callback' => array($this, 'generate_document'),
            'permission_callback' => array($this, 'check_permissions')
        ));
        
        // Court submission endpoint
        register_rest_route($this->namespace, '/cases/(?P<id>[\d]+)/submit', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_to_court'),
            'permission_callback' => array($this, 'check_permissions')
        ));
    }
    
    /**
     * Get cases
     */
    public function get_cases($request) {
        $case_manager = new CAH_Case_Manager();
        
        $args = array(
            'status' => $request->get_param('status'),
            'priority' => $request->get_param('priority'),
            'limit' => $request->get_param('limit') ?: 20,
            'offset' => $request->get_param('offset') ?: 0
        );
        
        $cases = $case_manager->get_cases($args);
        
        return rest_ensure_response($cases);
    }
    
    /**
     * Create a new case
     */
    public function create_case($request) {
        $case_manager = new CAH_Case_Manager();
        
        $case_data = array(
            'case_id' => $request->get_param('case_id'),
            'case_priority' => $request->get_param('case_priority'),
            'case_deadline_response' => $request->get_param('case_deadline_response'),
            'case_deadline_payment' => $request->get_param('case_deadline_payment'),
            'emails_received_date' => $request->get_param('emails_received_date'),
            'emails_received_time' => $request->get_param('emails_received_time'),
            'emails_sender_email' => $request->get_param('emails_sender_email'),
            'emails_user_email' => $request->get_param('emails_user_email'),
            'emails_subject' => $request->get_param('emails_subject'),
            'emails_content' => $request->get_param('emails_content'),
            'emails_header_data' => $request->get_param('emails_header_data'),
            'emails_ip_address' => $request->get_param('emails_ip_address'),
            'emails_attachment_count' => $request->get_param('emails_attachment_count'),
            'emails_has_unsubscribe' => $request->get_param('emails_has_unsubscribe')
        );
        
        $result = $case_manager->create_case($case_data);
        
        if (is_wp_error($result)) {
            return new WP_Error('case_creation_failed', $result->get_error_message(), array('status' => 400));
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Get a specific case
     */
    public function get_case($request) {
        $case_manager = new CAH_Case_Manager();
        $case_id = $request->get_param('id');
        
        $case = $case_manager->get_case_details($case_id);
        
        if (is_wp_error($case)) {
            return new WP_Error('case_not_found', $case->get_error_message(), array('status' => 404));
        }
        
        return rest_ensure_response($case);
    }
    
    /**
     * Update a case
     */
    public function update_case($request) {
        $case_manager = new CAH_Case_Manager();
        $case_id = $request->get_param('id');
        $new_status = $request->get_param('status');
        
        if (!empty($new_status)) {
            $result = $case_manager->update_case_status($case_id, $new_status);
            
            if (is_wp_error($result)) {
                return new WP_Error('case_update_failed', $result->get_error_message(), array('status' => 400));
            }
            
            return rest_ensure_response($result);
        }
        
        return new WP_Error('no_update_data', 'Keine Aktualisierungsdaten bereitgestellt', array('status' => 400));
    }
    
    /**
     * Handle N8N webhooks
     */
    public function handle_webhook($request) {
        $action = $request->get_param('action');
        $data = $request->get_json_params();
        
        $n8n_connector = new CAH_N8N_Connector();
        
        // Add the action to the data
        $data['action'] = $action;
        
        $result = $n8n_connector->handle_webhook_callback($data);
        
        if (is_wp_error($result)) {
            return new WP_Error('webhook_processing_failed', $result->get_error_message(), array('status' => 400));
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Get system status
     */
    public function get_status($request) {
        $database = new CAH_Database();
        $table_status = $database->get_table_status();
        
        $status = array(
            'plugin_version' => CAH_PLUGIN_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'database_tables' => $table_status,
            'n8n_configured' => !empty(get_option('klage_click_n8n_url')) && !empty(get_option('klage_click_n8n_key')),
            'timestamp' => current_time('mysql')
        );
        
        return rest_ensure_response($status);
    }
    
    /**
     * Generate document for a case
     */
    public function generate_document($request) {
        $case_id = $request->get_param('id');
        $document_type = $request->get_param('document_type') ?: 'mahnbescheid';
        
        $n8n_connector = new CAH_N8N_Connector();
        $result = $n8n_connector->generate_document($case_id, $document_type);
        
        if (is_wp_error($result)) {
            return new WP_Error('document_generation_failed', $result->get_error_message(), array('status' => 400));
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Submit case to court
     */
    public function submit_to_court($request) {
        $case_id = $request->get_param('id');
        
        $case_manager = new CAH_Case_Manager();
        $case_data = $case_manager->get_case_details($case_id);
        
        if (is_wp_error($case_data)) {
            return new WP_Error('case_not_found', $case_data->get_error_message(), array('status' => 404));
        }
        
        $n8n_connector = new CAH_N8N_Connector();
        $result = $n8n_connector->submit_to_court($case_id, $case_data->court);
        
        if (is_wp_error($result)) {
            return new WP_Error('court_submission_failed', $result->get_error_message(), array('status' => 400));
        }
        
        return rest_ensure_response($result);
    }
    
    /**
     * Check permissions for API endpoints
     */
    public function check_permissions($request) {
        // Check if user has capability
        if (current_user_can('manage_klage_click_cases')) {
            return true;
        }
        
        // Check for API key authentication
        $api_key = $request->get_header('X-API-Key');
        if (!empty($api_key)) {
            $stored_key = get_option('klage_click_api_key');
            if (!empty($stored_key) && hash_equals($stored_key, $api_key)) {
                return true;
            }
        }
        
        return new WP_Error('rest_forbidden', 'Keine Berechtigung für diese Aktion', array('status' => 403));
    }
    
    /**
     * Check permissions for webhook endpoints
     */
    public function check_webhook_permissions($request) {
        // Check for N8N webhook authentication
        $auth_header = $request->get_header('Authorization');
        if (!empty($auth_header) && strpos($auth_header, 'Bearer ') === 0) {
            $token = substr($auth_header, 7);
            $stored_key = get_option('klage_click_n8n_key');
            if (!empty($stored_key) && hash_equals($stored_key, $token)) {
                return true;
            }
        }
        
        // Check for webhook secret
        $webhook_secret = $request->get_header('X-Webhook-Secret');
        if (!empty($webhook_secret)) {
            $stored_secret = get_option('klage_click_webhook_secret');
            if (!empty($stored_secret) && hash_equals($stored_secret, $webhook_secret)) {
                return true;
            }
        }
        
        return new WP_Error('rest_forbidden', 'Ungültige Webhook-Authentifizierung', array('status' => 403));
    }
}