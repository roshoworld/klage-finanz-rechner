<?php
/**
 * Database management class
 * Creates and manages all database tables based on the data model
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Database {
    
    private $wpdb;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    public function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
        // Cases table
        $sql_cases = "CREATE TABLE {$this->wpdb->prefix}klage_cases (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            case_id varchar(100) NOT NULL UNIQUE,
            case_creation_date datetime NOT NULL,
            case_status enum('draft','pending','processing','completed','cancelled') DEFAULT 'draft',
            case_priority enum('low','medium','high','urgent') DEFAULT 'medium',
            client_id bigint(20) unsigned,
            debtor_id bigint(20) unsigned,
            case_deadline_response date,
            case_deadline_payment date,
            processing_complexity enum('simple','standard','complex') DEFAULT 'standard',
            processing_risk_score tinyint(3) unsigned DEFAULT 3,
            document_type enum('mahnbescheid','klage') DEFAULT 'mahnbescheid',
            document_language varchar(2) DEFAULT 'de',
            total_amount decimal(10,2) DEFAULT 0.00,
            court_id bigint(20) unsigned,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY case_id (case_id),
            KEY case_status (case_status),
            KEY client_id (client_id),
            KEY debtor_id (debtor_id),
            KEY court_id (court_id)
        ) $charset_collate;";
        
        // Debtors table
        $sql_debtors = "CREATE TABLE {$this->wpdb->prefix}klage_debtors (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            debtors_name varchar(200) NOT NULL,
            debtors_legal_form enum('einzelperson','gmbh','ag','kg','ohg','gbr','ev','andere') DEFAULT 'einzelperson',
            debtors_first_name varchar(100),
            debtors_last_name varchar(100),
            debtors_street varchar(100) NOT NULL,
            debtors_house_number varchar(10) NOT NULL,
            debtors_postal_code varchar(5) NOT NULL,
            debtors_city varchar(100) NOT NULL,
            debtors_country varchar(2) DEFAULT 'DE',
            debtors_email varchar(255),
            debtors_phone varchar(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY debtors_name (debtors_name),
            KEY debtors_postal_code (debtors_postal_code),
            KEY debtors_city (debtors_city)
        ) $charset_collate;";
        
        // Clients table
        $sql_clients = "CREATE TABLE {$this->wpdb->prefix}klage_clients (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned,
            users_first_name varchar(100) NOT NULL,
            users_last_name varchar(100) NOT NULL,
            users_email varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Emails table
        $sql_emails = "CREATE TABLE {$this->wpdb->prefix}klage_emails (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            case_id bigint(20) unsigned NOT NULL,
            emails_received_date date NOT NULL,
            emails_received_time time NOT NULL,
            emails_sender_email varchar(255) NOT NULL,
            emails_user_email varchar(255) NOT NULL,
            emails_subject varchar(200),
            emails_content text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Financial table
        $sql_financial = "CREATE TABLE {$this->wpdb->prefix}klage_financial (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            case_id bigint(20) unsigned NOT NULL,
            damages_loss decimal(10,2) DEFAULT 350.00,
            partner_fees decimal(10,2) DEFAULT 96.90,
            communication_fees decimal(10,2) DEFAULT 13.36,
            vat decimal(10,2) DEFAULT 87.85,
            total decimal(10,2) DEFAULT 548.11,
            court_fees decimal(10,2) DEFAULT 32.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Courts table
        $sql_courts = "CREATE TABLE {$this->wpdb->prefix}klage_courts (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            court_name varchar(100) NOT NULL,
            court_address varchar(200) NOT NULL,
            court_egvp_id varchar(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Execute all table creation queries
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create each table individually with error checking
        $results = array();
        
        $results['cases'] = dbDelta($sql_cases);
        $results['debtors'] = dbDelta($sql_debtors);
        $results['clients'] = dbDelta($sql_clients);
        $results['emails'] = dbDelta($sql_emails);
        $results['financial'] = dbDelta($sql_financial);
        $results['courts'] = dbDelta($sql_courts);
        
        // Insert default courts
        $this->insert_default_courts();
        
        // Log results for debugging
        if (get_option('klage_click_debug_mode')) {
            error_log('Klage.Click Database Creation Results: ' . print_r($results, true));
        }
        
        return $results;
    }
    
    private function insert_default_courts() {
        // Check if courts already exist
        $court_count = $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->wpdb->prefix}klage_courts");
        
        if ($court_count == 0) {
            // Insert default German courts
            $default_courts = array(
                array(
                    'court_name' => 'Amtsgericht Frankfurt am Main',
                    'court_address' => 'Gerichtsstraße 2, 60313 Frankfurt am Main',
                    'court_egvp_id' => 'AG.FFM.001'
                ),
                array(
                    'court_name' => 'Amtsgericht München',
                    'court_address' => 'Pacellistraße 5, 80333 München',
                    'court_egvp_id' => 'AG.MUC.001'
                ),
                array(
                    'court_name' => 'Amtsgericht Berlin-Mitte',
                    'court_address' => 'Littenstraße 12-17, 10179 Berlin',
                    'court_egvp_id' => 'AG.BER.001'
                ),
                array(
                    'court_name' => 'Amtsgericht Hamburg',
                    'court_address' => 'Sievekingplatz 1, 20355 Hamburg',
                    'court_egvp_id' => 'AG.HAM.001'
                )
            );
            
            foreach ($default_courts as $court) {
                $this->wpdb->insert(
                    $this->wpdb->prefix . 'klage_courts',
                    $court,
                    array('%s', '%s', '%s')
                );
            }
        }
    }
    
    public function get_table_status() {
        $tables = array('klage_cases', 'klage_debtors', 'klage_clients', 'klage_emails', 'klage_financial', 'klage_courts');
        $status = array();
        
        foreach ($tables as $table) {
            $full_table_name = $this->wpdb->prefix . $table;
            $exists = $this->wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
            $count = $exists ? $this->wpdb->get_var("SELECT COUNT(*) FROM $full_table_name") : 0;
            
            $status[$table] = array(
                'exists' => !empty($exists),
                'count' => $count
            );
        }
        
        return $status;
    }
}