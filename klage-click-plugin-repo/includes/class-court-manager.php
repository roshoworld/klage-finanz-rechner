<?php
/**
 * Court Manager class
 * Handles German court system integration and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Court_Manager {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
     * Get court by postal code (jurisdiction determination)
     */
    public function get_court_by_postal_code($postal_code) {
        // Simple mapping for major German postal code areas to courts
        $court_mapping = array(
            '10' => 'Amtsgericht Berlin-Mitte',      // Berlin
            '20' => 'Amtsgericht Hamburg',           // Hamburg
            '30' => 'Amtsgericht Hannover',          // Hannover
            '40' => 'Amtsgericht Düsseldorf',        // Düsseldorf
            '50' => 'Amtsgericht Köln',              // Köln
            '60' => 'Amtsgericht Frankfurt am Main', // Frankfurt
            '70' => 'Amtsgericht Stuttgart',         // Stuttgart
            '80' => 'Amtsgericht München',           // München
            '90' => 'Amtsgericht Nürnberg'           // Nürnberg
        );
        
        $postal_prefix = substr($postal_code, 0, 2);
        $court_name = isset($court_mapping[$postal_prefix]) ? 
                      $court_mapping[$postal_prefix] : 
                      'Amtsgericht Frankfurt am Main'; // Default fallback
        
        $court = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}klage_courts WHERE court_name = %s",
                $court_name
            )
        );
        
        return $court;
    }
    
    /**
     * Assign court to case based on debtor location
     */
    public function assign_court_to_case($case_id, $debtor_postal_code) {
        $court = $this->get_court_by_postal_code($debtor_postal_code);
        
        if (!$court) {
            return new WP_Error('court_not_found', 'Zuständiges Gericht nicht gefunden');
        }
        
        $result = $this->wpdb->update(
            $this->wpdb->prefix . 'klage_cases',
            array('court_id' => $court->id),
            array('id' => $case_id),
            array('%d'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('assignment_failed', 'Gerichtszuordnung fehlgeschlagen');
        }
        
        return $court;
    }
    
    /**
     * Get all available courts
     */
    public function get_all_courts() {
        return $this->wpdb->get_results(
            "SELECT * FROM {$this->wpdb->prefix}klage_courts ORDER BY court_name ASC"
        );
    }
}