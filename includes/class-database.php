<?php
/**
 * Database Class - Core Plugin v1.4.9
 */

class CAH_Database {
    
    public function create_tables_direct() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Cases table
        $cases_table = $wpdb->prefix . 'klage_cases';
        $cases_sql = "CREATE TABLE $cases_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            case_number varchar(100) NOT NULL,
            debtor_name varchar(200),
            amount decimal(10,2) DEFAULT 0.00,
            status varchar(50) DEFAULT 'Open',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY case_number (case_number),
            KEY status (status)
        ) $charset_collate;";
        
        // Debtors table
        $debtors_table = $wpdb->prefix . 'klage_debtors';
        $debtors_sql = "CREATE TABLE $debtors_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(200) NOT NULL,
            email varchar(100),
            phone varchar(50),
            address text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY name (name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($cases_sql);
        dbDelta($debtors_sql);
    }
}