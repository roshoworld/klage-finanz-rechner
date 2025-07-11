<?php
/**
 * Debtor Manager class
 * Handles debtor information and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Debtor_Manager {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Create or update debtor
     */
    public function create_or_update_debtor($debtor_data) {
        // Validate required fields
        if (empty($debtor_data['debtors_name']) || empty($debtor_data['debtors_street']) || 
            empty($debtor_data['debtors_postal_code']) || empty($debtor_data['debtors_city'])) {
            return new WP_Error('missing_data', 'Erforderliche Schuldner-Daten fehlen');
        }
        
        // Check if debtor already exists
        $existing_debtor = $this->find_existing_debtor($debtor_data);
        
        if ($existing_debtor) {
            // Update existing debtor
            $result = $this->update_debtor($existing_debtor->id, $debtor_data);
            if (is_wp_error($result)) {
                return $result;
            }
            return $existing_debtor->id;
        } else {
            // Create new debtor
            return $this->create_debtor($debtor_data);
        }
    }
    
    /**
     * Create new debtor
     */
    public function create_debtor($debtor_data) {
        $insert_data = array(
            'debtors_name' => sanitize_text_field($debtor_data['debtors_name']),
            'debtors_legal_form' => isset($debtor_data['debtors_legal_form']) ? 
                sanitize_text_field($debtor_data['debtors_legal_form']) : 'einzelperson',
            'debtors_first_name' => isset($debtor_data['debtors_first_name']) ? 
                sanitize_text_field($debtor_data['debtors_first_name']) : null,
            'debtors_last_name' => isset($debtor_data['debtors_last_name']) ? 
                sanitize_text_field($debtor_data['debtors_last_name']) : null,
            'debtors_street' => sanitize_text_field($debtor_data['debtors_street']),
            'debtors_house_number' => sanitize_text_field($debtor_data['debtors_house_number']),
            'debtors_postal_code' => sanitize_text_field($debtor_data['debtors_postal_code']),
            'debtors_city' => sanitize_text_field($debtor_data['debtors_city']),
            'debtors_country' => isset($debtor_data['debtors_country']) ? 
                sanitize_text_field($debtor_data['debtors_country']) : 'DE',
            'debtors_email' => isset($debtor_data['debtors_email']) ? 
                sanitize_email($debtor_data['debtors_email']) : null,
            'debtors_phone' => isset($debtor_data['debtors_phone']) ? 
                sanitize_text_field($debtor_data['debtors_phone']) : null
        );
        
        $result = $this->wpdb->insert(
            $this->wpdb->prefix . 'klage_debtors',
            $insert_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('creation_failed', 'Schuldner konnte nicht erstellt werden: ' . $this->wpdb->last_error);
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Update existing debtor
     */
    public function update_debtor($debtor_id, $debtor_data) {
        $update_data = array();
        
        // Only update fields that are provided
        if (isset($debtor_data['debtors_name'])) {
            $update_data['debtors_name'] = sanitize_text_field($debtor_data['debtors_name']);
        }
        if (isset($debtor_data['debtors_legal_form'])) {
            $update_data['debtors_legal_form'] = sanitize_text_field($debtor_data['debtors_legal_form']);
        }
        if (isset($debtor_data['debtors_first_name'])) {
            $update_data['debtors_first_name'] = sanitize_text_field($debtor_data['debtors_first_name']);
        }
        if (isset($debtor_data['debtors_last_name'])) {
            $update_data['debtors_last_name'] = sanitize_text_field($debtor_data['debtors_last_name']);
        }
        if (isset($debtor_data['debtors_street'])) {
            $update_data['debtors_street'] = sanitize_text_field($debtor_data['debtors_street']);
        }
        if (isset($debtor_data['debtors_house_number'])) {
            $update_data['debtors_house_number'] = sanitize_text_field($debtor_data['debtors_house_number']);
        }
        if (isset($debtor_data['debtors_postal_code'])) {
            $update_data['debtors_postal_code'] = sanitize_text_field($debtor_data['debtors_postal_code']);
        }
        if (isset($debtor_data['debtors_city'])) {
            $update_data['debtors_city'] = sanitize_text_field($debtor_data['debtors_city']);
        }
        if (isset($debtor_data['debtors_country'])) {
            $update_data['debtors_country'] = sanitize_text_field($debtor_data['debtors_country']);
        }
        if (isset($debtor_data['debtors_email'])) {
            $update_data['debtors_email'] = sanitize_email($debtor_data['debtors_email']);
        }
        if (isset($debtor_data['debtors_phone'])) {
            $update_data['debtors_phone'] = sanitize_text_field($debtor_data['debtors_phone']);
        }
        
        if (empty($update_data)) {
            return new WP_Error('no_update_data', 'Keine Aktualisierungsdaten bereitgestellt');
        }
        
        $result = $this->wpdb->update(
            $this->wpdb->prefix . 'klage_debtors',
            $update_data,
            array('id' => $debtor_id),
            null,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('update_failed', 'Schuldner konnte nicht aktualisiert werden: ' . $this->wpdb->last_error);
        }
        
        return true;
    }
    
    /**
     * Find existing debtor by name and address
     */
    private function find_existing_debtor($debtor_data) {
        $existing_debtor = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_debtors 
                 WHERE debtors_name = %s 
                 AND debtors_postal_code = %s 
                 AND debtors_city = %s",
                $debtor_data['debtors_name'],
                $debtor_data['debtors_postal_code'],
                $debtor_data['debtors_city']
            )
        );
        
        return $existing_debtor;
    }
    
    /**
     * Get debtor by ID
     */
    public function get_debtor($debtor_id) {
        $debtor = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_debtors WHERE id = %d",
                $debtor_id
            )
        );
        
        if (!$debtor) {
            return new WP_Error('debtor_not_found', 'Schuldner nicht gefunden');
        }
        
        return $debtor;
    }
    
    /**
     * Get all debtors with filtering
     */
    public function get_debtors($args = array()) {
        $defaults = array(
            'search' => '',
            'legal_form' => '',
            'city' => '',
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'debtors_name',
            'order' => 'ASC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where_conditions = array();
        $where_values = array();
        
        if (!empty($args['search'])) {
            $where_conditions[] = '(debtors_name LIKE %s OR debtors_first_name LIKE %s OR debtors_last_name LIKE %s OR debtors_email LIKE %s)';
            $search_term = '%' . $args['search'] . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        if (!empty($args['legal_form'])) {
            $where_conditions[] = 'debtors_legal_form = %s';
            $where_values[] = $args['legal_form'];
        }
        
        if (!empty($args['city'])) {
            $where_conditions[] = 'debtors_city = %s';
            $where_values[] = $args['city'];
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $query = "
            SELECT * FROM {$this->wpdb->prefix}klage_debtors
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
     * Delete debtor
     */
    public function delete_debtor($debtor_id) {
        // Check if debtor is used in any cases
        $case_count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->wpdb->prefix}klage_cases WHERE debtor_id = %d",
                $debtor_id
            )
        );
        
        if ($case_count > 0) {
            return new WP_Error('debtor_in_use', 'Schuldner kann nicht gelöscht werden, da er in Fällen verwendet wird');
        }
        
        $result = $this->wpdb->delete(
            $this->wpdb->prefix . 'klage_debtors',
            array('id' => $debtor_id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('deletion_failed', 'Schuldner konnte nicht gelöscht werden');
        }
        
        return true;
    }
    
    /**
     * Get debtor statistics
     */
    public function get_debtor_statistics() {
        $stats = array();
        
        // Total debtors
        $stats['total_debtors'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->wpdb->prefix}klage_debtors"
        );
        
        // Debtors by legal form
        $stats['by_legal_form'] = $this->wpdb->get_results(
            "SELECT debtors_legal_form, COUNT(*) as count 
             FROM {$this->wpdb->prefix}klage_debtors 
             GROUP BY debtors_legal_form 
             ORDER BY count DESC"
        );
        
        // Debtors by city (top 10)
        $stats['by_city'] = $this->wpdb->get_results(
            "SELECT debtors_city, COUNT(*) as count 
             FROM {$this->wpdb->prefix}klage_debtors 
             GROUP BY debtors_city 
             ORDER BY count DESC 
             LIMIT 10"
        );
        
        return $stats;
    }
    
    /**
     * Validate German postal code
     */
    public function validate_german_postal_code($postal_code) {
        return preg_match('/^[0-9]{5}$/', $postal_code);
    }
    
    /**
     * Validate German phone number
     */
    public function validate_german_phone($phone) {
        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid German phone number format
        return preg_match('/^(0[1-9]|1[56789]|49[1-9])[0-9]{7,11}$/', $phone);
    }
    
    /**
     * Format debtor address
     */
    public function format_debtor_address($debtor) {
        $address_parts = array();
        
        if (!empty($debtor->debtors_street)) {
            $street_address = $debtor->debtors_street;
            if (!empty($debtor->debtors_house_number)) {
                $street_address .= ' ' . $debtor->debtors_house_number;
            }
            $address_parts[] = $street_address;
        }
        
        if (!empty($debtor->debtors_postal_code) && !empty($debtor->debtors_city)) {
            $address_parts[] = $debtor->debtors_postal_code . ' ' . $debtor->debtors_city;
        }
        
        if (!empty($debtor->debtors_country) && $debtor->debtors_country !== 'DE') {
            $address_parts[] = $debtor->debtors_country;
        }
        
        return implode(', ', $address_parts);
    }
    
    /**
     * Get debtor full name
     */
    public function get_debtor_full_name($debtor) {
        if (!empty($debtor->debtors_first_name) && !empty($debtor->debtors_last_name)) {
            return $debtor->debtors_first_name . ' ' . $debtor->debtors_last_name;
        }
        
        return $debtor->debtors_name;
    }
}