<?php
/**
 * Audit Logger class
 * Handles comprehensive audit logging for compliance and tracking
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Audit_Logger {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Log an action for audit trail
     */
    public function log_action($case_id, $action_type, $action_details, $gdpr_data_source = null) {
        $current_user = wp_get_current_user();
        $user_identifier = $current_user->ID > 0 ? $current_user->user_login : 'system';
        
        $audit_data = array(
            'case_id' => $case_id,
            'audit_created_by' => $user_identifier,
            'action_type' => $action_type,
            'action_details' => $action_details,
            'gdpr_data_source' => $gdpr_data_source ?: 'email_marketing_campaign',
            'gdpr_retention_period' => 3 // 3 years default
        );
        
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'klage_audit',
            $audit_data,
            array('%d', '%s', '%s', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            error_log('Audit log insertion failed: ' . $this->wpdb->last_error);
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get audit log for a specific case
     */
    public function get_case_audit_log($case_id, $limit = 100) {
        $logs = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_audit 
                 WHERE case_id = %d 
                 ORDER BY audit_created_at DESC 
                 LIMIT %d",
                $case_id,
                $limit
            )
        );
        
        return $logs;
    }
    
    /**
     * Get all audit logs with filtering
     */
    public function get_audit_logs($args = array()) {
        $defaults = array(
            'case_id' => null,
            'action_type' => null,
            'user' => null,
            'date_from' => null,
            'date_to' => null,
            'limit' => 100,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['case_id'])) {
            $where_conditions[] = 'case_id = %d';
            $where_values[] = $args['case_id'];
        }
        
        if (!empty($args['action_type'])) {
            $where_conditions[] = 'action_type = %s';
            $where_values[] = $args['action_type'];
        }
        
        if (!empty($args['user'])) {
            $where_conditions[] = 'audit_created_by = %s';
            $where_values[] = $args['user'];
        }
        
        if (!empty($args['date_from'])) {
            $where_conditions[] = 'audit_created_at >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where_conditions[] = 'audit_created_at <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $query = "
            SELECT * FROM {$this->wpdb->prefix}klage_audit
            $where_clause
            ORDER BY audit_created_at DESC
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
     * Clean up old audit logs based on GDPR retention period
     */
    public function cleanup_old_logs() {
        // Get logs older than their retention period
        $old_logs = $this->wpdb->get_results(
            "SELECT id, gdpr_retention_period, audit_created_at 
             FROM {$this->wpdb->prefix}klage_audit 
             WHERE DATE_ADD(audit_created_at, INTERVAL gdpr_retention_period YEAR) < NOW()"
        );
        
        $deleted_count = 0;
        
        foreach ($old_logs as $log) {
            $result = $this->wpdb->delete(
                $this->wpdb->prefix . 'klage_audit',
                array('id' => $log->id),
                array('%d')
            );
            
            if ($result !== false) {
                $deleted_count++;
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Export audit logs for compliance reporting
     */
    public function export_audit_logs($case_id = null, $format = 'csv') {
        $logs = $case_id ? $this->get_case_audit_log($case_id, 9999) : $this->get_audit_logs(array('limit' => 9999));
        
        if (empty($logs)) {
            return false;
        }
        
        switch ($format) {
            case 'csv':
                return $this->export_to_csv($logs);
            case 'json':
                return $this->export_to_json($logs);
            default:
                return $logs;
        }
    }
    
    /**
     * Export audit logs to CSV format
     */
    private function export_to_csv($logs) {
        $csv_data = array();
        
        // CSV header
        $csv_data[] = array(
            'ID',
            'Case ID',
            'Created By',
            'Created At',
            'Action Type',
            'Action Details',
            'GDPR Data Source',
            'Retention Period'
        );
        
        // CSV rows
        foreach ($logs as $log) {
            $csv_data[] = array(
                $log->id,
                $log->case_id,
                $log->audit_created_by,
                $log->audit_created_at,
                $log->action_type,
                $log->action_details,
                $log->gdpr_data_source,
                $log->gdpr_retention_period
            );
        }
        
        return $csv_data;
    }
    
    /**
     * Export audit logs to JSON format
     */
    private function export_to_json($logs) {
        $json_data = array(
            'export_date' => current_time('mysql'),
            'total_records' => count($logs),
            'audit_logs' => $logs
        );
        
        return json_encode($json_data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get audit statistics
     */
    public function get_audit_statistics($days = 30) {
        $stats = array();
        
        // Total logs in period
        $stats['total_logs'] = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}klage_audit 
                 WHERE audit_created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
        
        // Logs by action type
        $stats['by_action_type'] = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT action_type, COUNT(*) as count 
                 FROM {$this->wpdb->prefix}klage_audit 
                 WHERE audit_created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                 GROUP BY action_type 
                 ORDER BY count DESC",
                $days
            )
        );
        
        // Logs by user
        $stats['by_user'] = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT audit_created_by, COUNT(*) as count 
                 FROM {$this->wpdb->prefix}klage_audit 
                 WHERE audit_created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                 GROUP BY audit_created_by 
                 ORDER BY count DESC 
                 LIMIT 10",
                $days
            )
        );
        
        return $stats;
    }
}