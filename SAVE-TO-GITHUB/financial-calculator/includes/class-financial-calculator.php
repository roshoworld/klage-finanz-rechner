<?php
/**
 * Financial Calculator Core
 * Core calculation engine for financial calculations
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Calculator {
    
    const TAX_RATE = 0.19; // 19% German MwSt
    
    public function __construct() {
        // Initialize calculator
    }
    
    public function calculate_case_totals($financial_items) {
        $subtotal = 0;
        $tax_amount = 0;
        $total = 0;
        
        foreach ($financial_items as $item) {
            $amount = floatval($item['amount'] ?? 0);
            $is_taxable = !empty($item['is_taxable']);
            
            $subtotal += $amount;
            
            if ($is_taxable) {
                $tax_amount += $amount * self::TAX_RATE;
            }
        }
        
        $total = $subtotal + $tax_amount;
        
        return array(
            'subtotal' => $subtotal,
            'tax_amount' => $tax_amount,
            'total' => $total,
            'tax_rate' => self::TAX_RATE
        );
    }
    
    public function format_currency($amount, $currency = 'EUR') {
        $formatted = number_format($amount, 2);
        
        switch ($currency) {
            case 'EUR':
                return "€{$formatted}";
            case 'USD':
                return "${$formatted}";
            default:
                return "{$formatted} {$currency}";
        }
    }
    
    public function calculate_template_totals($template_id) {
        $templates = new CAH_Financial_Templates();
        $template_items = $templates->get_template_items($template_id);
        
        $items = array();
        foreach ($template_items as $item) {
            $items[] = array(
                'amount' => $item->default_amount,
                'is_taxable' => $item->is_taxable
            );
        }
        
        return $this->calculate_case_totals($items);
    }
    
    public function validate_financial_data($financial_data) {
        $errors = array();
        
        if (empty($financial_data) || !is_array($financial_data)) {
            $errors[] = 'Financial data is required';
            return $errors;
        }
        
        foreach ($financial_data as $index => $item) {
            $item_errors = array();
            
            if (empty($item['item_name'])) {
                $item_errors[] = 'Item name is required';
            }
            
            if (empty($item['item_category'])) {
                $item_errors[] = 'Item category is required';
            }
            
            if (!isset($item['amount']) || !is_numeric($item['amount']) || $item['amount'] < 0) {
                $item_errors[] = 'Amount must be a valid positive number';
            }
            
            if (!empty($item_errors)) {
                $errors["item_{$index}"] = $item_errors;
            }
        }
        
        return $errors;
    }
    
    public function apply_discount($amount, $discount_type, $discount_value) {
        switch ($discount_type) {
            case 'percentage':
                return $amount * (1 - ($discount_value / 100));
            case 'fixed':
                return max(0, $amount - $discount_value);
            default:
                return $amount;
        }
    }
    
    public function calculate_payment_schedule($total_amount, $installments = 1, $interest_rate = 0) {
        if ($installments <= 0) {
            return array();
        }
        
        if ($installments == 1) {
            return array(
                array(
                    'installment' => 1,
                    'amount' => $total_amount,
                    'interest' => 0,
                    'principal' => $total_amount,
                    'due_date' => date('Y-m-d', strtotime('+30 days'))
                )
            );
        }
        
        $schedule = array();
        $monthly_rate = $interest_rate / 12 / 100;
        
        if ($monthly_rate > 0) {
            // Calculate monthly payment with interest
            $monthly_payment = $total_amount * 
                ($monthly_rate * pow(1 + $monthly_rate, $installments)) / 
                (pow(1 + $monthly_rate, $installments) - 1);
            
            $remaining_balance = $total_amount;
            
            for ($i = 1; $i <= $installments; $i++) {
                $interest_payment = $remaining_balance * $monthly_rate;
                $principal_payment = $monthly_payment - $interest_payment;
                $remaining_balance -= $principal_payment;
                
                $schedule[] = array(
                    'installment' => $i,
                    'amount' => $monthly_payment,
                    'interest' => $interest_payment,
                    'principal' => $principal_payment,
                    'balance' => max(0, $remaining_balance),
                    'due_date' => date('Y-m-d', strtotime("+{$i} months"))
                );
            }
        } else {
            // Simple equal installments without interest
            $installment_amount = $total_amount / $installments;
            
            for ($i = 1; $i <= $installments; $i++) {
                $schedule[] = array(
                    'installment' => $i,
                    'amount' => $installment_amount,
                    'interest' => 0,
                    'principal' => $installment_amount,
                    'balance' => ($installments - $i) * $installment_amount,
                    'due_date' => date('Y-m-d', strtotime("+{$i} months"))
                );
            }
        }
        
        return $schedule;
    }
    
    public function generate_invoice_data($case_id, $financial_data) {
        $totals = $this->calculate_case_totals($financial_data);
        
        return array(
            'case_id' => $case_id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'items' => $financial_data,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'total' => $totals['total'],
            'tax_rate' => self::TAX_RATE * 100,
            'currency' => 'EUR'
        );
    }
    
    public function export_financial_data($case_id, $format = 'csv') {
        $database = new CAH_Financial_Database();
        $financial_data = $database->get_case_financial_data($case_id);
        $totals = $database->calculate_case_totals($case_id);
        
        switch ($format) {
            case 'csv':
                return $this->export_to_csv($financial_data, $totals);
            case 'json':
                return $this->export_to_json($financial_data, $totals);
            default:
                return false;
        }
    }
    
    private function export_to_csv($financial_data, $totals) {
        $csv = "Item Name,Category,Amount,Taxable,Description\n";
        
        foreach ($financial_data as $item) {
            $csv .= sprintf(
                '"%s","%s",%.2f,"%s","%s"' . "\n",
                $item->item_name,
                $item->item_category,
                $item->amount,
                $item->is_taxable ? 'Yes' : 'No',
                $item->description
            );
        }
        
        $csv .= "\n";
        $csv .= sprintf('"Subtotal","",%.2f,"",""' . "\n", $totals['subtotal']);
        $csv .= sprintf('"Tax (19%%)","",%.2f,"",""' . "\n", $totals['tax_amount']);
        $csv .= sprintf('"Total","",%.2f,"",""' . "\n", $totals['total']);
        
        return $csv;
    }
    
    private function export_to_json($financial_data, $totals) {
        return json_encode(array(
            'items' => $financial_data,
            'totals' => $totals,
            'export_date' => date('Y-m-d H:i:s')
        ), JSON_PRETTY_PRINT);
    }
}