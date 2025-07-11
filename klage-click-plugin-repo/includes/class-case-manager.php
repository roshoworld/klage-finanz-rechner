<?php
/**
 * Case Manager class
 * Handles all case-related operations and business logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Case_Manager {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        
        add_action('wp_ajax_cah_create_case', array($this, 'ajax_create_case'));
        add_action('wp_ajax_cah_update_case_status', array($this, 'ajax_update_case_status'));
        add_action('wp_ajax_cah_calculate_damages', array($this, 'ajax_calculate_damages'));
    }
    
    /**
     * Create a new case
     */
    public function create_case($case_data) {
        // Validate required fields
        if (empty($case_data['case_id']) || empty($case_data['emails_sender_email']) || empty($case_data['emails_user_email'])) {
            return new WP_Error('missing_data', 'Erforderliche Felder fehlen');
        }
        
        // Check if case_id already exists
        $existing_case = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->wpdb->prefix}klage_cases WHERE case_id = %s",
                $case_data['case_id']
            )
        );
        
        if ($existing_case) {
            return new WP_Error('duplicate_case', 'Fall-ID existiert bereits');
        }
        
        // Start transaction
        $this->wpdb->query('START TRANSACTION');
        
        try {
            // Insert case
            $case_insert_data = array(
                'case_id' => $case_data['case_id'],
                'case_creation_date' => current_time('mysql'),
                'case_status' => 'draft',
                'case_priority' => isset($case_data['case_priority']) ? $case_data['case_priority'] : 'medium',
                'case_deadline_response' => isset($case_data['case_deadline_response']) ? $case_data['case_deadline_response'] : null,
                'case_deadline_payment' => isset($case_data['case_deadline_payment']) ? $case_data['case_deadline_payment'] : null,
                'processing_complexity' => 'standard',
                'processing_risk_score' => 3,
                'document_type' => 'mahnbescheid',
                'document_language' => 'de',
                'total_amount' => 0.00
            );
            
            $case_result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_cases',
                $case_insert_data,
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%f')
            );
            
            if ($case_result === false) {
                throw new Exception('Fehler beim Erstellen des Falls');
            }
            
            $case_id = $this->wpdb->insert_id;
            
            // Insert email evidence
            $email_insert_data = array(
                'case_id' => $case_id,
                'emails_received_date' => $case_data['emails_received_date'],
                'emails_received_time' => $case_data['emails_received_time'],
                'emails_sender_email' => $case_data['emails_sender_email'],
                'emails_user_email' => $case_data['emails_user_email'],
                'emails_subject' => isset($case_data['emails_subject']) ? $case_data['emails_subject'] : '',
                'emails_content' => isset($case_data['emails_content']) ? $case_data['emails_content'] : '',
                'emails_header_data' => isset($case_data['emails_header_data']) ? $case_data['emails_header_data'] : '',
                'emails_ip_address' => isset($case_data['emails_ip_address']) ? $case_data['emails_ip_address'] : '',
                'emails_attachment_count' => isset($case_data['emails_attachment_count']) ? intval($case_data['emails_attachment_count']) : 0,
                'emails_has_unsubscribe' => isset($case_data['emails_has_unsubscribe']) ? (bool)$case_data['emails_has_unsubscribe'] : false
            );
            
            $email_result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_emails',
                $email_insert_data,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
            );
            
            if ($email_result === false) {
                throw new Exception('Fehler beim Hinzufügen der E-Mail-Evidenz');
            }
            
            // Insert financial calculations (default GDPR damages)
            $financial_insert_data = array(
                'case_id' => $case_id,
                'damages_loss' => 350.00, // Default GDPR spam damage
                'partner_fees' => 96.90,   // Default legal fees
                'communication_fees' => 13.36,
                'vat' => 87.85,
                'total' => 548.11,
                'interest_rate' => 5.00,
                'interest_start_date' => $case_data['emails_received_date'],
                'court_fees' => 32.00 // Default court fees
            );
            
            $financial_result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_financial',
                $financial_insert_data,
                array('%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f')
            );
            
            if ($financial_result === false) {
                throw new Exception('Fehler beim Hinzufügen der Finanzberechnungen');
            }
            
            // Insert legal basis (GDPR)
            $legal_insert_data = array(
                'case_id' => $case_id,
                'legal_basis_dsgvo' => 'Art. 82 DSGVO',
                'legal_basis_bgb' => '§ 823 Abs. 1 BGB',
                'legal_basis_gg' => 'Art. 2 Abs. 1 und Art. 1 Abs. 1 GG',
                'legal_parent_type' => 'spam_email',
                'legal_consent_given' => false,
                'legal_previous_contact' => false
            );
            
            $legal_result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_legal',
                $legal_insert_data,
                array('%d', '%s', '%s', '%s', '%s', '%d', '%d')
            );
            
            if ($legal_result === false) {
                throw new Exception('Fehler beim Hinzufügen der Rechtsgrundlagen');
            }
            
            // Update case total amount
            $this->wpdb->update(
                $this->wpdb->prefix . 'klage_cases',
                array('total_amount' => 548.11),
                array('id' => $case_id),
                array('%f'),
                array('%d')
            );
            
            // Create audit log entry
            $this->create_audit_log($case_id, 'case_created', 'Fall erstellt mit ID: ' . $case_data['case_id']);
            
            // Commit transaction
            $this->wpdb->query('COMMIT');
            
            return array(
                'success' => true,
                'case_id' => $case_id,
                'message' => 'Fall erfolgreich erstellt'
            );
            
        } catch (Exception $e) {
            // Rollback transaction
            $this->wpdb->query('ROLLBACK');
            
            return new WP_Error('creation_failed', $e->getMessage());
        }
    }
    
    /**
     * Get case details with all related data
     */
    public function get_case_details($case_id) {
        $case_data = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_cases WHERE id = %d OR case_id = %s",
                $case_id,
                $case_id
            )
        );
        
        if (!$case_data) {
            return new WP_Error('case_not_found', 'Fall nicht gefunden');
        }
        
        // Get related data
        $case_data->emails = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_emails WHERE case_id = %d",
                $case_data->id
            )
        );
        
        $case_data->financial = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_financial WHERE case_id = %d",
                $case_data->id
            )
        );
        
        $case_data->legal = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_legal WHERE case_id = %d",
                $case_data->id
            )
        );
        
        $case_data->debtor = null;
        if ($case_data->debtor_id) {
            $case_data->debtor = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$this->wpdb->prefix}klage_debtors WHERE id = %d",
                    $case_data->debtor_id
                )
            );
        }
        
        $case_data->court = null;
        if ($case_data->court_id) {
            $case_data->court = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM {$this->wpdb->prefix}klage_courts WHERE id = %d",
                    $case_data->court_id
                )
            );
        }
        
        return $case_data;
    }
    
    /**
     * Update case status
     */
    public function update_case_status($case_id, $new_status) {
        $valid_statuses = array('draft', 'pending', 'processing', 'completed', 'cancelled');
        
        if (!in_array($new_status, $valid_statuses)) {
            return new WP_Error('invalid_status', 'Ungültiger Status');
        }
        
        $result = $this->wpdb->update(
            $this->wpdb->prefix . 'klage_cases',
            array('case_status' => $new_status),
            array('id' => $case_id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Fehler beim Aktualisieren des Status');
        }
        
        // Create audit log entry
        $this->create_audit_log($case_id, 'status_changed', 'Status geändert zu: ' . $new_status);
        
        return array(
            'success' => true,
            'message' => 'Status erfolgreich aktualisiert'
        );
    }
    
    /**
     * Get cases with filtering and pagination
     */
    public function get_cases($args = array()) {
        $defaults = array(
            'status' => '',
            'priority' => '',
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'case_creation_date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['status'])) {
            $where_conditions[] = 'case_status = %s';
            $where_values[] = $args['status'];
        }
        
        if (!empty($args['priority'])) {
            $where_conditions[] = 'case_priority = %s';
            $where_values[] = $args['priority'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "
            SELECT 
                c.*,
                d.debtors_name,
                cl.users_first_name,
                cl.users_last_name,
                ct.court_name
            FROM {$this->wpdb->prefix}klage_cases c
            LEFT JOIN {$this->wpdb->prefix}klage_debtors d ON c.debtor_id = d.id
            LEFT JOIN {$this->wpdb->prefix}klage_clients cl ON c.client_id = cl.id
            LEFT JOIN {$this->wpdb->prefix}klage_courts ct ON c.court_id = ct.id
            $where_clause
            ORDER BY $orderby
            LIMIT %d OFFSET %d
        ";
        
        $where_values[] = $args['limit'];
        $where_values[] = $args['offset'];
        
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        
        return $this->wpdb->get_results($query);
    }
    
    /**
     * Process case for N8N
     */
    public function process_case_for_n8n($case_id) {
        $case_data = $this->get_case_details($case_id);
        
        if (is_wp_error($case_data)) {
            return $case_data;
        }
        
        // Prepare data for N8N
        $n8n_data = array(
            'case_id' => $case_data->case_id,
            'case_status' => $case_data->case_status,
            'email_data' => $case_data->emails,
            'financial_data' => $case_data->financial,
            'legal_data' => $case_data->legal,
            'debtor_data' => $case_data->debtor
        );
        
        // Send to N8N
        $n8n_connector = new CAH_N8N_Connector();
        $result = $n8n_connector->send_case_data($n8n_data);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // Update case status to processing
        $this->update_case_status($case_data->id, 'processing');
        
        return array(
            'success' => true,
            'message' => 'Fall erfolgreich an N8N gesendet'
        );
    }
    
    /**
     * Create audit log entry
     */
    private function create_audit_log($case_id, $action_type, $details) {
        $current_user = wp_get_current_user();
        $user_identifier = $current_user->ID > 0 ? $current_user->user_login : 'system';
        
        $this->wpdb->insert(
            $this->wpdb->prefix . 'klage_audit',
            array(
                'case_id' => $case_id,
                'audit_created_by' => $user_identifier,
                'action_type' => $action_type,
                'action_details' => $details,
                'gdpr_data_source' => 'email_marketing_campaign',
                'gdpr_retention_period' => 3
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d')
        );
    }
    
    /**
     * AJAX handler for creating a case
     */
    public function ajax_create_case() {
        check_ajax_referer('cah_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_klage_click_cases')) {
            wp_die('Keine Berechtigung');
        }
        
        $case_data = array(
            'case_id' => sanitize_text_field($_POST['case_id']),
            'case_priority' => sanitize_text_field($_POST['case_priority']),
            'case_deadline_response' => sanitize_text_field($_POST['case_deadline_response']),
            'case_deadline_payment' => sanitize_text_field($_POST['case_deadline_payment']),
            'emails_received_date' => sanitize_text_field($_POST['emails_received_date']),
            'emails_received_time' => sanitize_text_field($_POST['emails_received_time']),
            'emails_sender_email' => sanitize_email($_POST['emails_sender_email']),
            'emails_user_email' => sanitize_email($_POST['emails_user_email']),
            'emails_subject' => sanitize_text_field($_POST['emails_subject']),
            'emails_content' => sanitize_textarea_field($_POST['emails_content'])
        );
        
        $result = $this->create_case($case_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler for updating case status
     */
    public function ajax_update_case_status() {
        check_ajax_referer('cah_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_klage_click_cases')) {
            wp_die('Keine Berechtigung');
        }
        
        $case_id = intval($_POST['case_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        $result = $this->update_case_status($case_id, $new_status);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler for damage calculation
     */
    public function ajax_calculate_damages() {
        check_ajax_referer('cah_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_klage_click_cases')) {
            wp_die('Keine Berechtigung');
        }
        
        // Default GDPR spam damages calculation
        $damages = array(
            'base_damage' => 350.00,
            'legal_fees' => 96.90,
            'communication_fees' => 13.36,
            'court_fees' => 32.00,
            'subtotal' => 492.26,
            'vat' => 87.85,
            'total' => 548.11
        );
        
        wp_send_json_success($damages);
    }
    
    /**
     * Generate case ID
     */
    public function generate_case_id() {
        $prefix = 'SPAM';
        $year = date('Y');
        $number = str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $year . '-' . $number;
    }
}