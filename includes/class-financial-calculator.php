<?php
/**
 * Financial Calculator Engine
 * Core calculation logic for financial operations
 */

class CAH_Financial_Calculator_Engine {
    
    public function calculate_totals($cost_items, $mwst_rate = 19.00) {
        $subtotal = 0;
        
        // Calculate subtotal
        foreach ($cost_items as $item => $amount) {
            $subtotal += floatval($amount);
        }
        
        // Calculate MwSt
        $mwst_amount = $subtotal * ($mwst_rate / 100);
        
        // Calculate total
        $total = $subtotal + $mwst_amount;
        
        return array(
            'subtotal' => round($subtotal, 2),
            'mwst_amount' => round($mwst_amount, 2),
            'mwst_rate' => $mwst_rate,
            'total' => round($total, 2)
        );
    }
    
    public function format_currency($amount) {
        return '€' . number_format($amount, 2, ',', '.');
    }
    
    public function get_default_cost_items() {
        return array(
            'grundkosten' => 548.11,
            'gerichtskosten' => 50.00,
            'anwaltskosten' => 200.00,
            'sonstige' => 0.00
        );
    }
    
    public function validate_cost_items($cost_items) {
        $errors = array();
        
        foreach ($cost_items as $item => $amount) {
            if (!is_numeric($amount) || $amount < 0) {
                $errors[] = "Invalid amount for {$item}";
            }
        }
        
        return $errors;
    }
    
    public function calculate_case_financial($case_id, $cost_items = null, $mwst_rate = 19.00) {
        if ($cost_items === null) {
            $cost_items = $this->get_default_cost_items();
        }
        
        $calculation = $this->calculate_totals($cost_items, $mwst_rate);
        
        // Save to database
        $db_manager = new CAH_Financial_DB_Manager();
        $financial_data = array(
            'cost_items' => json_encode($cost_items),
            'subtotal' => $calculation['subtotal'],
            'mwst_amount' => $calculation['mwst_amount'],
            'mwst_rate' => $mwst_rate,
            'total' => $calculation['total']
        );
        
        $db_manager->update_case_financial_data($case_id, $financial_data);
        
        return $calculation;
    }
}