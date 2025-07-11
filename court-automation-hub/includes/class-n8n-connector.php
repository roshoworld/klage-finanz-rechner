<?php
/**
 * N8N Connector class
 * Handles communication with N8N automation workflows
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_N8N_Connector {
    
    private $n8n_url;
    private $n8n_key;
    private $debug_mode;
    
    public function __construct() {
        $this->n8n_url = get_option('klage_click_n8n_url');
        $this->n8n_key = get_option('klage_click_n8n_key');
        $this->debug_mode = get_option('klage_click_debug_mode', false);
    }
    
    /**
     * Send case data to N8N for processing
     */
    public function send_case_data($case_data) {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'webhook/klage-click-case-processing';
        
        $payload = array(
            'action' => 'process_case',
            'timestamp' => current_time('mysql'),
            'case_data' => $case_data
        );
        
        $response = $this->send_request($endpoint, $payload);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        // Log the response if debug mode is enabled
        if ($this->debug_mode) {
            error_log('N8N Response: ' . print_r($response, true));
        }
        
        return array(
            'success' => true,
            'response' => $response,
            'message' => 'Daten erfolgreich an N8N gesendet'
        );
    }
    
    /**
     * Send document generation request to N8N
     */
    public function generate_document($case_id, $document_type = 'mahnbescheid') {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'webhook/klage-click-document-generation';
        
        $payload = array(
            'action' => 'generate_document',
            'case_id' => $case_id,
            'document_type' => $document_type,
            'timestamp' => current_time('mysql')
        );
        
        $response = $this->send_request($endpoint, $payload);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'document_data' => $response,
            'message' => 'Dokument erfolgreich generiert'
        );
    }
    
    /**
     * Send court submission request to N8N
     */
    public function submit_to_court($case_id, $court_data) {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'webhook/klage-click-court-submission';
        
        $payload = array(
            'action' => 'submit_to_court',
            'case_id' => $case_id,
            'court_data' => $court_data,
            'timestamp' => current_time('mysql')
        );
        
        $response = $this->send_request($endpoint, $payload);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'submission_data' => $response,
            'message' => 'Erfolgreich an Gericht übermittelt'
        );
    }
    
    /**
     * Request AI analysis of case
     */
    public function request_ai_analysis($case_data) {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'webhook/klage-click-ai-analysis';
        
        $payload = array(
            'action' => 'analyze_case',
            'case_data' => $case_data,
            'analysis_type' => 'gdpr_spam_violation',
            'timestamp' => current_time('mysql')
        );
        
        $response = $this->send_request($endpoint, $payload);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'analysis_result' => $response,
            'message' => 'KI-Analyse erfolgreich durchgeführt'
        );
    }
    
    /**
     * Get workflow status from N8N
     */
    public function get_workflow_status($process_id) {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'api/v1/processes/' . $process_id;
        
        $response = $this->send_get_request($endpoint);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'status' => $response,
            'message' => 'Workflow-Status erfolgreich abgerufen'
        );
    }
    
    /**
     * Send HTTP POST request to N8N
     */
    private function send_request($endpoint, $payload) {
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->n8n_key
        );
        
        $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => $headers,
            'body' => json_encode($payload)
        );
        
        $response = wp_remote_request($endpoint, $args);
        
        if (is_wp_error($response)) {
            return new WP_Error('n8n_request_failed', 'N8N Anfrage fehlgeschlagen: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            return new WP_Error('n8n_error', 'N8N Fehler (Code: ' . $response_code . '): ' . $response_body);
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('n8n_invalid_response', 'Ungültige JSON-Antwort von N8N');
        }
        
        return $decoded_response;
    }
    
    /**
     * Send HTTP GET request to N8N
     */
    private function send_get_request($endpoint) {
        $headers = array(
            'Authorization' => 'Bearer ' . $this->n8n_key
        );
        
        $args = array(
            'method' => 'GET',
            'timeout' => 30,
            'headers' => $headers
        );
        
        $response = wp_remote_request($endpoint, $args);
        
        if (is_wp_error($response)) {
            return new WP_Error('n8n_request_failed', 'N8N Anfrage fehlgeschlagen: ' . $response->get_error_message());
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code >= 400) {
            return new WP_Error('n8n_error', 'N8N Fehler (Code: ' . $response_code . '): ' . $response_body);
        }
        
        $decoded_response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('n8n_invalid_response', 'Ungültige JSON-Antwort von N8N');
        }
        
        return $decoded_response;
    }
    
    /**
     * Test N8N connection
     */
    public function test_connection() {
        if (empty($this->n8n_url) || empty($this->n8n_key)) {
            return new WP_Error('n8n_not_configured', 'N8N ist nicht konfiguriert');
        }
        
        $endpoint = trailingslashit($this->n8n_url) . 'webhook/klage-click-test';
        
        $payload = array(
            'action' => 'test_connection',
            'timestamp' => current_time('mysql'),
            'source' => 'wordpress_plugin'
        );
        
        $response = $this->send_request($endpoint, $payload);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return array(
            'success' => true,
            'message' => 'N8N Verbindung erfolgreich getestet'
        );
    }
    
    /**
     * Handle N8N webhook callbacks
     */
    public function handle_webhook_callback($data) {
        if (empty($data['case_id'])) {
            return new WP_Error('invalid_webhook', 'Ungültige Webhook-Daten');
        }
        
        $case_manager = new CAH_Case_Manager();
        
        // Update case based on webhook data
        switch ($data['action']) {
            case 'document_generated':
                $this->handle_document_generated($data);
                break;
            case 'court_submitted':
                $this->handle_court_submitted($data);
                break;
            case 'ai_analysis_complete':
                $this->handle_ai_analysis_complete($data);
                break;
            case 'workflow_error':
                $this->handle_workflow_error($data);
                break;
            default:
                return new WP_Error('unknown_action', 'Unbekannte Webhook-Aktion');
        }
        
        return array(
            'success' => true,
            'message' => 'Webhook erfolgreich verarbeitet'
        );
    }
    
    /**
     * Handle document generated webhook
     */
    private function handle_document_generated($data) {
        global $wpdb;
        
        // Update case with document information
        $wpdb->update(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_status' => 'processing',
                'updated_at' => current_time('mysql')
            ),
            array('case_id' => $data['case_id']),
            array('%s', '%s'),
            array('%s')
        );
        
        // Create audit log entry
        $audit_logger = new CAH_Audit_Logger();
        $audit_logger->log_action($data['case_id'], 'document_generated', 'Dokument generiert: ' . $data['document_type']);
    }
    
    /**
     * Handle court submitted webhook
     */
    private function handle_court_submitted($data) {
        global $wpdb;
        
        // Update case status
        $wpdb->update(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_status' => 'completed',
                'updated_at' => current_time('mysql')
            ),
            array('case_id' => $data['case_id']),
            array('%s', '%s'),
            array('%s')
        );
        
        // Create audit log entry
        $audit_logger = new CAH_Audit_Logger();
        $audit_logger->log_action($data['case_id'], 'court_submitted', 'Erfolgreich an Gericht übermittelt');
    }
    
    /**
     * Handle AI analysis complete webhook
     */
    private function handle_ai_analysis_complete($data) {
        // Store AI analysis results
        // This would typically update relevant database tables with analysis results
        
        // Create audit log entry
        $audit_logger = new CAH_Audit_Logger();
        $audit_logger->log_action($data['case_id'], 'ai_analysis_complete', 'KI-Analyse abgeschlossen');
    }
    
    /**
     * Handle workflow error webhook
     */
    private function handle_workflow_error($data) {
        global $wpdb;
        
        // Update case status to indicate error
        $wpdb->update(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_status' => 'draft', // Reset to draft for manual review
                'updated_at' => current_time('mysql')
            ),
            array('case_id' => $data['case_id']),
            array('%s', '%s'),
            array('%s')
        );
        
        // Create audit log entry
        $audit_logger = new CAH_Audit_Logger();
        $audit_logger->log_action($data['case_id'], 'workflow_error', 'Workflow-Fehler: ' . $data['error_message']);
    }
}