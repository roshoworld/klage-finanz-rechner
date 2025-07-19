<?php
/**
 * Database Class - Full functionality restored v1.5.1
 */

if (!class_exists('CAH_Database')) {
    class CAH_Database {
        
        public function create_tables_direct() {
            global $wpdb;
            
            $charset_collate = $wpdb->get_charset_collate();
            
            // Cases table - Enhanced with full functionality
            $cases_table = $wpdb->prefix . 'klage_cases';
            $cases_sql = "CREATE TABLE $cases_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                case_number varchar(100) NOT NULL,
                debtor_id int(11),
                lawyer_id int(11),
                court_id int(11),
                debtor_name varchar(200),
                debtor_email varchar(100),
                debtor_address text,
                amount decimal(10,2) DEFAULT 0.00,
                status varchar(50) DEFAULT 'Open',
                priority varchar(20) DEFAULT 'Normal',
                description text,
                legal_basis text,
                evidence_files text,
                next_action_date datetime,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_by int(11),
                updated_by int(11),
                PRIMARY KEY (id),
                UNIQUE KEY case_number (case_number),
                KEY debtor_id (debtor_id),
                KEY lawyer_id (lawyer_id),
                KEY court_id (court_id),
                KEY status (status),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            // Debtors table - Full debtor management
            $debtors_table = $wpdb->prefix . 'klage_debtors';
            $debtors_sql = "CREATE TABLE $debtors_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(200) NOT NULL,
                email varchar(100),
                phone varchar(50),
                address text,
                company varchar(200),
                tax_id varchar(100),
                notes text,
                status varchar(50) DEFAULT 'Active',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY name (name),
                KEY email (email),
                KEY status (status)
            ) $charset_collate;";
            
            // Lawyers table
            $lawyers_table = $wpdb->prefix . 'klage_lawyers';
            $lawyers_sql = "CREATE TABLE $lawyers_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(200) NOT NULL,
                email varchar(100),
                phone varchar(50),
                address text,
                bar_number varchar(100),
                specialization text,
                hourly_rate decimal(8,2),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY name (name)
            ) $charset_collate;";
            
            // Courts table
            $courts_table = $wpdb->prefix . 'klage_courts';
            $courts_sql = "CREATE TABLE $courts_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                name varchar(200) NOT NULL,
                type varchar(100),
                address text,
                phone varchar(50),
                email varchar(100),
                filing_fees decimal(8,2),
                jurisdiction text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY name (name),
                KEY type (type)
            ) $charset_collate;";
            
            // Documents table
            $documents_table = $wpdb->prefix . 'klage_documents';
            $documents_sql = "CREATE TABLE $documents_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                case_id int(11) NOT NULL,
                title varchar(200) NOT NULL,
                type varchar(100),
                file_path varchar(500),
                file_size int(11),
                mime_type varchar(100),
                description text,
                upload_date datetime DEFAULT CURRENT_TIMESTAMP,
                uploaded_by int(11),
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY type (type),
                KEY upload_date (upload_date)
            ) $charset_collate;";
            
            // Audit log table
            $audit_table = $wpdb->prefix . 'klage_audit_log';
            $audit_sql = "CREATE TABLE $audit_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                action varchar(100) NOT NULL,
                description text,
                object_type varchar(50),
                object_id int(11),
                user_id int(11),
                ip_address varchar(45),
                user_agent text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY action (action),
                KEY object_type (object_type),
                KEY object_id (object_id),
                KEY user_id (user_id),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($cases_sql);
            dbDelta($debtors_sql);
            dbDelta($lawyers_sql);
            dbDelta($courts_sql);
            dbDelta($documents_sql);
            dbDelta($audit_sql);
            
            // Create default data
            $this->create_default_data();
        }
        
        private function create_default_data() {
            global $wpdb;
            
            // Create default court
            $courts_table = $wpdb->prefix . 'klage_courts';
            $existing_court = $wpdb->get_var("SELECT COUNT(*) FROM $courts_table");
            
            if ($existing_court == 0) {
                $wpdb->insert($courts_table, array(
                    'name' => 'Amtsgericht München',
                    'type' => 'Amtsgericht',
                    'address' => 'Maxburgstraße 1, 80333 München',
                    'phone' => '+49 89 5597-0',
                    'filing_fees' => 50.00,
                    'jurisdiction' => 'München und Umgebung'
                ));
            }
            
            // Create default lawyer
            $lawyers_table = $wpdb->prefix . 'klage_lawyers';
            $existing_lawyer = $wpdb->get_var("SELECT COUNT(*) FROM $lawyers_table");
            
            if ($existing_lawyer == 0) {
                $wpdb->insert($lawyers_table, array(
                    'name' => 'Standard Legal Representative',
                    'email' => 'legal@klage.click',
                    'specialization' => 'DSGVO, Datenschutz, Zivilrecht',
                    'hourly_rate' => 200.00
                ));
            }
        }
        
        // Upgrade existing tables
        public function upgrade_existing_tables() {
            global $wpdb;
            
            // Add any missing columns to existing tables
            $cases_table = $wpdb->prefix . 'klage_cases';
            
            // Check if priority column exists
            $priority_exists = $wpdb->get_results("SHOW COLUMNS FROM $cases_table LIKE 'priority'");
            if (empty($priority_exists)) {
                $wpdb->query("ALTER TABLE $cases_table ADD COLUMN priority varchar(20) DEFAULT 'Normal' AFTER status");
            }
            
            // Check if legal_basis column exists
            $legal_basis_exists = $wpdb->get_results("SHOW COLUMNS FROM $cases_table LIKE 'legal_basis'");
            if (empty($legal_basis_exists)) {
                $wpdb->query("ALTER TABLE $cases_table ADD COLUMN legal_basis text AFTER description");
            }
        }
        
        // Get table status
        public function get_table_status() {
            global $wpdb;
            
            $tables = array(
                'klage_cases' => $wpdb->prefix . 'klage_cases',
                'klage_debtors' => $wpdb->prefix . 'klage_debtors',
                'klage_lawyers' => $wpdb->prefix . 'klage_lawyers',
                'klage_courts' => $wpdb->prefix . 'klage_courts',
                'klage_documents' => $wpdb->prefix . 'klage_documents',
                'klage_audit_log' => $wpdb->prefix . 'klage_audit_log'
            );
            
            $status = array();
            foreach ($tables as $key => $table_name) {
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
                $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $table_name") : 0;
                $status[$key] = array(
                    'exists' => !empty($exists),
                    'count' => $count
                );
            }
            
            return $status;
        }
    }
}