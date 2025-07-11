<?php
/**
 * Financial Calculator class
 * Handles damage calculations and financial computations
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Calculator {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Calculate GDPR spam damages
     */
    public function calculate_gdpr_damages($case_data) {
        $damages = array();
        
        // Base GDPR damage (Art. 82 DSGVO)
        $damages['base_damage'] = $this->calculate_base_gdpr_damage($case_data);
        
        // Legal fees (RVG)
        $damages['legal_fees'] = $this->calculate_legal_fees($damages['base_damage']);
        
        // Communication costs
        $damages['communication_fees'] = $this->calculate_communication_fees();
        
        // Court fees
        $damages['court_fees'] = $this->calculate_court_fees($damages['base_damage']);
        
        // Subtotal
        $damages['subtotal'] = $damages['base_damage'] + $damages['legal_fees'] + 
                              $damages['communication_fees'] + $damages['court_fees'];
        
        // VAT (19%)
        $damages['vat'] = $this->calculate_vat($damages['legal_fees'] + $damages['communication_fees']);
        
        // Total amount
        $damages['total'] = $damages['subtotal'] + $damages['vat'];
        
        // Interest calculation
        $damages['interest'] = $this->calculate_interest($damages['total'], $case_data);
        
        return $damages;
    }
    
    /**
     * Calculate base GDPR damage according to Art. 82 DSGVO
     */
    private function calculate_base_gdpr_damage($case_data) {
        // Standard GDPR spam damage in Germany
        $base_damage = 350.00;
        
        // Adjust based on case specifics
        $multiplier = 1.0;
        
        // Check for aggravating factors
        if (isset($case_data['emails_attachment_count']) && $case_data['emails_attachment_count'] > 0) {
            $multiplier += 0.1; // 10% increase for attachments
        }
        
        if (isset($case_data['emails_has_unsubscribe']) && !$case_data['emails_has_unsubscribe']) {
            $multiplier += 0.2; // 20% increase for missing unsubscribe
        }
        
        // Check for repeat offenses
        if ($this->is_repeat_offender($case_data['emails_sender_email'])) {
            $multiplier += 0.3; // 30% increase for repeat offenses
        }
        
        return round($base_damage * $multiplier, 2);
    }
    
    /**
     * Calculate legal fees according to RVG (Rechtsanwaltsvergütungsgesetz)
     */
    private function calculate_legal_fees($claim_amount) {
        // RVG fee table for claims up to €2000
        if ($claim_amount <= 500) {
            return 96.90; // 1.3 fee for value €500
        } elseif ($claim_amount <= 1000) {
            return 132.75; // 1.3 fee for value €1000
        } elseif ($claim_amount <= 1500) {
            return 168.60; // 1.3 fee for value €1500
        } else {
            return 204.45; // 1.3 fee for value €2000
        }
    }
    
    /**
     * Calculate communication fees
     */
    private function calculate_communication_fees() {
        // Standard communication fees (postage, phone, etc.)
        return 13.36;
    }
    
    /**
     * Calculate court fees according to GKG (Gerichtskostengesetz)
     */
    private function calculate_court_fees($claim_amount) {
        // GKG fee table for Amtsgericht
        if ($claim_amount <= 300) {
            return 23.00;
        } elseif ($claim_amount <= 600) {
            return 32.00;
        } elseif ($claim_amount <= 900) {
            return 41.00;
        } elseif ($claim_amount <= 1200) {
            return 50.00;
        } else {
            return 59.00;
        }
    }
    
    /**
     * Calculate VAT (19%)
     */
    private function calculate_vat($taxable_amount) {
        return round($taxable_amount * 0.19, 2);
    }
    
    /**
     * Calculate interest on damages
     */
    private function calculate_interest($principal, $case_data) {
        $interest_data = array();
        
        // Standard interest rate (5% per year)
        $annual_rate = 0.05;
        
        // Calculate days since email received
        $email_date = new DateTime($case_data['emails_received_date']);
        $current_date = new DateTime();
        $days_elapsed = $current_date->diff($email_date)->days;
        
        // Calculate interest
        $daily_rate = $annual_rate / 365;
        $interest_amount = $principal * $daily_rate * $days_elapsed;
        
        $interest_data['principal'] = $principal;
        $interest_data['annual_rate'] = $annual_rate;
        $interest_data['days_elapsed'] = $days_elapsed;
        $interest_data['interest_amount'] = round($interest_amount, 2);
        $interest_data['total_with_interest'] = $principal + $interest_amount;
        
        return $interest_data;
    }
    
    /**
     * Check if sender is a repeat offender
     */
    private function is_repeat_offender($sender_email) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}klage_emails 
                 WHERE emails_sender_email = %s",
                $sender_email
            )
        );
        
        return $count > 1;
    }
    
    /**
     * Save financial calculations to database
     */
    public function save_financial_data($case_id, $financial_data) {
        $insert_data = array(
            'case_id' => $case_id,
            'damages_loss' => $financial_data['base_damage'],
            'partner_fees' => $financial_data['legal_fees'],
            'communication_fees' => $financial_data['communication_fees'],
            'vat' => $financial_data['vat'],
            'total' => $financial_data['total'],
            'interest_rate' => 5.00,
            'interest_start_date' => current_time('Y-m-d'),
            'court_fees' => $financial_data['court_fees']
        );
        
        // Check if financial data already exists
        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->wpdb->prefix}klage_financial WHERE case_id = %d",
                $case_id
            )
        );
        
        if ($existing) {
            // Update existing record
            $result = $this->wpdb->update(
                $this->wpdb->prefix . 'klage_financial',
                $insert_data,
                array('case_id' => $case_id),
                array('%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f'),
                array('%d')
            );
        } else {
            // Insert new record
            $result = $this->wpdb->insert(
                $this->wpdb->prefix . 'klage_financial',
                $insert_data,
                array('%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s', '%f')
            );
        }
        
        if ($result === false) {
            return new WP_Error('save_failed', 'Finanzdaten konnten nicht gespeichert werden');
        }
        
        return true;
    }
    
    /**
     * Get financial data for a case
     */
    public function get_case_financial_data($case_id) {
        $financial_data = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_financial WHERE case_id = %d",
                $case_id
            )
        );
        
        if (!$financial_data) {
            return new WP_Error('not_found', 'Finanzdaten nicht gefunden');
        }
        
        return $financial_data;
    }
    
    /**
     * Calculate settlement amount (reduced amount for quick settlement)
     */
    public function calculate_settlement_amount($case_id, $reduction_percentage = 20) {
        $financial_data = $this->get_case_financial_data($case_id);
        
        if (is_wp_error($financial_data)) {
            return $financial_data;
        }
        
        $settlement_amount = $financial_data->total * (1 - ($reduction_percentage / 100));
        
        return array(
            'original_amount' => $financial_data->total,
            'reduction_percentage' => $reduction_percentage,
            'settlement_amount' => round($settlement_amount, 2),
            'savings' => round($financial_data->total - $settlement_amount, 2)
        );
    }
    
    /**
     * Generate financial summary for case
     */
    public function generate_financial_summary($case_id) {
        $case_manager = new CAH_Case_Manager();
        $case_data = $case_manager->get_case_details($case_id);
        
        if (is_wp_error($case_data)) {
            return $case_data;
        }
        
        $financial_data = $case_data->financial;
        
        $summary = array(
            'case_id' => $case_data->case_id,
            'claim_details' => array(
                'base_damage' => $financial_data->damages_loss,
                'legal_fees' => $financial_data->partner_fees,
                'communication_fees' => $financial_data->communication_fees,
                'court_fees' => $financial_data->court_fees,
                'vat' => $financial_data->vat,
                'total' => $financial_data->total
            ),
            'interest_calculation' => array(
                'interest_rate' => $financial_data->interest_rate,
                'interest_start_date' => $financial_data->interest_start_date,
                'current_interest' => $this->calculate_current_interest($financial_data)
            ),
            'settlement_options' => array(
                'immediate_settlement' => $this->calculate_settlement_amount($case_id, 30),
                'standard_settlement' => $this->calculate_settlement_amount($case_id, 20),
                'minimal_settlement' => $this->calculate_settlement_amount($case_id, 10)
            )
        );
        
        return $summary;
    }
    
    /**
     * Calculate current interest based on elapsed time
     */
    private function calculate_current_interest($financial_data) {
        $start_date = new DateTime($financial_data->interest_start_date);
        $current_date = new DateTime();
        $days_elapsed = $current_date->diff($start_date)->days;
        
        $daily_rate = ($financial_data->interest_rate / 100) / 365;
        $interest_amount = $financial_data->total * $daily_rate * $days_elapsed;
        
        return array(
            'days_elapsed' => $days_elapsed,
            'interest_amount' => round($interest_amount, 2),
            'total_with_interest' => round($financial_data->total + $interest_amount, 2)
        );
    }
    
    /**
     * Export financial data to CSV
     */
    public function export_financial_data($case_ids = array()) {
        $where_clause = '';
        $where_values = array();
        
        if (!empty($case_ids)) {
            $placeholders = str_repeat(',%d', count($case_ids));
            $placeholders = substr($placeholders, 1);
            $where_clause = "WHERE c.id IN ($placeholders)";
            $where_values = $case_ids;
        }
        
        $query = "
            SELECT 
                c.case_id,
                c.case_creation_date,
                c.case_status,
                f.damages_loss,
                f.partner_fees,
                f.communication_fees,
                f.court_fees,
                f.vat,
                f.total,
                f.interest_rate,
                f.interest_start_date
            FROM {$this->wpdb->prefix}klage_cases c
            LEFT JOIN {$this->wpdb->prefix}klage_financial f ON c.id = f.case_id
            $where_clause
            ORDER BY c.case_creation_date DESC
        ";
        
        if (!empty($where_values)) {
            $query = $this->wpdb->prepare($query, $where_values);
        }
        
        $results = $this->wpdb->get_results($query);
        
        return $results;
    }
}