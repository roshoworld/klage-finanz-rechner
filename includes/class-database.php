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
        
        // Run upgrade check on admin init
        add_action('admin_init', array($this, 'check_and_upgrade_schema'));
    }
    
    /**
     * Check and upgrade database schema if needed
     */
    public function check_and_upgrade_schema() {
        // Only run on admin pages
        if (!is_admin()) {
            return;
        }
        
        // Check if we need to upgrade
        $version_option = get_option('cah_database_version', '1.0.0');
        $current_version = '1.3.3';
        
        if (version_compare($version_option, $current_version, '<')) {
            $this->upgrade_existing_tables();
            update_option('cah_database_version', $current_version);
        }
    }
    
    /**
     * Ensure debtors table has correct schema
     */
    private function ensure_debtors_table_schema() {
        $charset_collate = $this->wpdb->get_charset_collate();
        $table_name = $this->wpdb->prefix . 'klage_debtors';
        
        // Drop and recreate the table to ensure correct schema
        $this->wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // Create with correct schema
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            
            -- Basic Information
            debtors_name varchar(200) NOT NULL,
            debtors_company varchar(200),
            debtors_first_name varchar(100),
            debtors_last_name varchar(100),
            debtors_email varchar(255),
            debtors_phone varchar(50),
            debtors_fax varchar(50),
            
            -- Address Information
            debtors_address varchar(200),
            debtors_street varchar(150),
            debtors_house_number varchar(20),
            debtors_address_addition varchar(100),
            debtors_postal_code varchar(20),
            debtors_city varchar(100),
            debtors_state varchar(100),
            debtors_country varchar(100) DEFAULT 'Deutschland',
            
            -- Legal Information
            rechtsform varchar(50) DEFAULT 'natuerliche_person',
            handelsregister_nr varchar(50),
            ustid varchar(50),
            geschaeftsfuehrer varchar(200),
            
            -- Additional Contact
            website varchar(255),
            social_media text,
            
            -- Financial Information
            finanzielle_situation varchar(50) DEFAULT 'unbekannt',
            zahlungsverhalten varchar(20) DEFAULT 'unbekannt',
            bonität varchar(20) DEFAULT 'unbekannt',
            
            -- Legal Status
            insolvenz_status varchar(20) DEFAULT 'nein',
            pfändung_status varchar(20) DEFAULT 'nein',
            
            -- Communication preferences
            bevorzugte_sprache varchar(5) DEFAULT 'de',
            kommunikation_email tinyint(1) DEFAULT 1,
            kommunikation_post tinyint(1) DEFAULT 1,
            
            -- Metadata
            datenquelle varchar(50) DEFAULT 'manual',
            verifiziert tinyint(1) DEFAULT 0,
            letzte_aktualisierung datetime DEFAULT NULL,
            
            -- Timestamps
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            KEY debtors_name (debtors_name),
            KEY debtors_email (debtors_email),
            KEY debtors_postal_code (debtors_postal_code)
        ) $charset_collate";
        
        $this->wpdb->query($sql);
    }
    
    /**
     * Upgrade existing tables to fix schema issues
     */
    private function upgrade_existing_tables() {
        $table_name = $this->wpdb->prefix . 'klage_debtors';
        
        // Check if table exists
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if ($table_exists) {
            // Fix debtors_country field length issue
            $column_info = $this->wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'debtors_country'");
            
            if (!empty($column_info)) {
                $column_type = $column_info[0]->Type;
                
                // If it's varchar(2), update it to varchar(100)
                if (strpos($column_type, 'varchar(2)') !== false) {
                    $alter_sql = "ALTER TABLE $table_name MODIFY COLUMN debtors_country varchar(100) DEFAULT 'Deutschland'";
                    $this->wpdb->query($alter_sql);
                    
                    // Update existing 'DE' values to 'Deutschland'
                    $update_sql = "UPDATE $table_name SET debtors_country = 'Deutschland' WHERE debtors_country = 'DE'";
                    $this->wpdb->query($update_sql);
                }
            }
            
            // Add missing columns if they don't exist
            $this->add_missing_columns_to_debtors_table($table_name);
        }
        
        // Also upgrade cases table
        $this->upgrade_cases_table();
    }
    
    /**
     * Upgrade cases table to add missing columns
     */
    private function upgrade_cases_table() {
        $table_name = $this->wpdb->prefix . 'klage_cases';
        
        // Check if table exists
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if ($table_exists) {
            $this->add_missing_columns_to_cases_table($table_name);
        }
    }
    
    /**
     * Add missing columns to existing cases table
     */
    private function add_missing_columns_to_cases_table($table_name) {
        // Define columns that should exist in cases table
        $required_columns = array(
            'mandant' => "ALTER TABLE $table_name ADD COLUMN mandant varchar(100) DEFAULT NULL",
            'brief_status' => "ALTER TABLE $table_name ADD COLUMN brief_status varchar(20) DEFAULT 'pending'",
            'briefe' => "ALTER TABLE $table_name ADD COLUMN briefe int(3) DEFAULT 1",
            'schuldner' => "ALTER TABLE $table_name ADD COLUMN schuldner varchar(200) DEFAULT NULL",
            'beweise' => "ALTER TABLE $table_name ADD COLUMN beweise text DEFAULT NULL",
            'dokumente' => "ALTER TABLE $table_name ADD COLUMN dokumente text DEFAULT NULL",
            'links_zu_dokumenten' => "ALTER TABLE $table_name ADD COLUMN links_zu_dokumenten text DEFAULT NULL",
            'verfahrensart' => "ALTER TABLE $table_name ADD COLUMN verfahrensart varchar(50) DEFAULT 'mahnverfahren'",
            'rechtsgrundlage' => "ALTER TABLE $table_name ADD COLUMN rechtsgrundlage varchar(100) DEFAULT 'DSGVO Art. 82'",
            'zeitraum_von' => "ALTER TABLE $table_name ADD COLUMN zeitraum_von date DEFAULT NULL",
            'zeitraum_bis' => "ALTER TABLE $table_name ADD COLUMN zeitraum_bis date DEFAULT NULL",
            'anzahl_verstoesse' => "ALTER TABLE $table_name ADD COLUMN anzahl_verstoesse int(5) DEFAULT 1",
            'schadenhoehe' => "ALTER TABLE $table_name ADD COLUMN schadenhoehe decimal(10,2) DEFAULT 548.11",
            'anwaltsschreiben_status' => "ALTER TABLE $table_name ADD COLUMN anwaltsschreiben_status varchar(20) DEFAULT 'pending'",
            'mahnung_status' => "ALTER TABLE $table_name ADD COLUMN mahnung_status varchar(20) DEFAULT 'pending'",
            'klage_status' => "ALTER TABLE $table_name ADD COLUMN klage_status varchar(20) DEFAULT 'pending'",
            'vollstreckung_status' => "ALTER TABLE $table_name ADD COLUMN vollstreckung_status varchar(20) DEFAULT 'pending'",
            'egvp_aktenzeichen' => "ALTER TABLE $table_name ADD COLUMN egvp_aktenzeichen varchar(50) DEFAULT NULL",
            'xjustiz_uuid' => "ALTER TABLE $table_name ADD COLUMN xjustiz_uuid varchar(100) DEFAULT NULL",
            'gericht_zustaendig' => "ALTER TABLE $table_name ADD COLUMN gericht_zustaendig varchar(100) DEFAULT NULL",
            'verfahrenswert' => "ALTER TABLE $table_name ADD COLUMN verfahrenswert decimal(10,2) DEFAULT 548.11",
            'deadline_antwort' => "ALTER TABLE $table_name ADD COLUMN deadline_antwort date DEFAULT NULL",
            'deadline_zahlung' => "ALTER TABLE $table_name ADD COLUMN deadline_zahlung date DEFAULT NULL",
            'mahnung_datum' => "ALTER TABLE $table_name ADD COLUMN mahnung_datum date DEFAULT NULL",
            'klage_datum' => "ALTER TABLE $table_name ADD COLUMN klage_datum date DEFAULT NULL",
            'erfolgsaussicht' => "ALTER TABLE $table_name ADD COLUMN erfolgsaussicht varchar(20) DEFAULT 'hoch'",
            'risiko_bewertung' => "ALTER TABLE $table_name ADD COLUMN risiko_bewertung varchar(20) DEFAULT 'niedrig'",
            'komplexitaet' => "ALTER TABLE $table_name ADD COLUMN komplexitaet varchar(20) DEFAULT 'standard'",
            'kommunikation_sprache' => "ALTER TABLE $table_name ADD COLUMN kommunikation_sprache varchar(5) DEFAULT 'de'",
            'bevorzugter_kontakt' => "ALTER TABLE $table_name ADD COLUMN bevorzugter_kontakt varchar(20) DEFAULT 'email'",
            'kategorie' => "ALTER TABLE $table_name ADD COLUMN kategorie varchar(50) DEFAULT 'GDPR_SPAM'",
            'prioritaet_intern' => "ALTER TABLE $table_name ADD COLUMN prioritaet_intern varchar(20) DEFAULT 'medium'",
            'bearbeitungsstatus' => "ALTER TABLE $table_name ADD COLUMN bearbeitungsstatus varchar(20) DEFAULT 'neu'",
            'import_source' => "ALTER TABLE $table_name ADD COLUMN import_source varchar(50) DEFAULT 'manual'"
        );
        
        // Get existing columns
        $existing_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $table_name");
        $existing_column_names = array();
        
        foreach ($existing_columns as $column) {
            $existing_column_names[] = $column->Field;
        }
        
        // Add missing columns
        foreach ($required_columns as $column_name => $alter_sql) {
            if (!in_array($column_name, $existing_column_names)) {
                $this->wpdb->query($alter_sql);
            }
        }
    }
    
    /**
     * Add missing columns to existing debtors table
     */
    private function add_missing_columns_to_debtors_table($table_name) {
        // Define columns that should exist
        $required_columns = array(
            'datenquelle' => "ALTER TABLE $table_name ADD COLUMN datenquelle varchar(50) DEFAULT 'manual'",
            'letzte_aktualisierung' => "ALTER TABLE $table_name ADD COLUMN letzte_aktualisierung datetime DEFAULT NULL",
            'website' => "ALTER TABLE $table_name ADD COLUMN website varchar(255)",
            'social_media' => "ALTER TABLE $table_name ADD COLUMN social_media text",
            'zahlungsverhalten' => "ALTER TABLE $table_name ADD COLUMN zahlungsverhalten varchar(20) DEFAULT 'unbekannt'",
            'bonität' => "ALTER TABLE $table_name ADD COLUMN bonität varchar(20) DEFAULT 'unbekannt'",
            'insolvenz_status' => "ALTER TABLE $table_name ADD COLUMN insolvenz_status varchar(20) DEFAULT 'nein'",
            'pfändung_status' => "ALTER TABLE $table_name ADD COLUMN pfändung_status varchar(20) DEFAULT 'nein'",
            'bevorzugte_sprache' => "ALTER TABLE $table_name ADD COLUMN bevorzugte_sprache varchar(5) DEFAULT 'de'",
            'kommunikation_email' => "ALTER TABLE $table_name ADD COLUMN kommunikation_email tinyint(1) DEFAULT 1",
            'kommunikation_post' => "ALTER TABLE $table_name ADD COLUMN kommunikation_post tinyint(1) DEFAULT 1",
            'verifiziert' => "ALTER TABLE $table_name ADD COLUMN verifiziert tinyint(1) DEFAULT 0"
        );
        
        // Get existing columns
        $existing_columns = $this->wpdb->get_results("SHOW COLUMNS FROM $table_name");
        $existing_column_names = array();
        
        foreach ($existing_columns as $column) {
            $existing_column_names[] = $column->Field;
        }
        
        // Add missing columns
        foreach ($required_columns as $column_name => $alter_sql) {
            if (!in_array($column_name, $existing_column_names)) {
                $this->wpdb->query($alter_sql);
            }
        }
    }
    
    /**
     * Direct table creation method (bypasses dbDelta issues)
     */
    public function create_tables_direct() {
        $results = array(
            'success' => true,
            'message' => '',
            'details' => array()
        );
        
        $charset_collate = $this->wpdb->get_charset_collate();
        
        // First, handle existing table updates
        $this->upgrade_existing_tables();
        
        // Ensure debtors table has correct schema
        $this->ensure_debtors_table_schema();
        
        // Define all tables with simpler SQL
        $tables = array(
            'klage_cases' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_cases (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id varchar(100) NOT NULL,
                case_creation_date datetime NOT NULL,
                case_updated_date datetime DEFAULT NULL,
                case_status varchar(20) DEFAULT 'draft',
                case_priority varchar(20) DEFAULT 'medium',
                case_notes text DEFAULT NULL,
                brief_status varchar(20) DEFAULT 'pending',
                submission_date date DEFAULT NULL,
                mandant varchar(100) DEFAULT NULL,
                client_id bigint(20) unsigned,
                debtor_id bigint(20) unsigned,
                total_amount decimal(10,2) DEFAULT 0.00,
                court_id bigint(20) unsigned,
                import_source varchar(50) DEFAULT NULL,
                
                -- Core Forderungen.com fields
                briefe int(3) DEFAULT 1,
                schuldner varchar(200) DEFAULT NULL,
                beweise text DEFAULT NULL,
                dokumente text DEFAULT NULL,
                links_zu_dokumenten text DEFAULT NULL,
                
                -- Legal Processing fields
                verfahrensart varchar(50) DEFAULT 'mahnverfahren',
                rechtsgrundlage varchar(100) DEFAULT 'DSGVO Art. 82',
                zeitraum_von date DEFAULT NULL,
                zeitraum_bis date DEFAULT NULL,
                anzahl_verstoesse int(5) DEFAULT 1,
                schadenhoehe decimal(10,2) DEFAULT 548.11,
                
                -- Document Management
                anwaltsschreiben_status varchar(20) DEFAULT 'pending',
                mahnung_status varchar(20) DEFAULT 'pending',
                klage_status varchar(20) DEFAULT 'pending',
                vollstreckung_status varchar(20) DEFAULT 'pending',
                
                -- Court Integration (EGVP/XJustiz)
                egvp_aktenzeichen varchar(50) DEFAULT NULL,
                xjustiz_uuid varchar(100) DEFAULT NULL,
                gericht_zustaendig varchar(100) DEFAULT NULL,
                verfahrenswert decimal(10,2) DEFAULT 548.11,
                
                -- Timeline Management
                deadline_antwort date DEFAULT NULL,
                deadline_zahlung date DEFAULT NULL,
                mahnung_datum date DEFAULT NULL,
                klage_datum date DEFAULT NULL,
                
                -- Risk Assessment
                erfolgsaussicht varchar(20) DEFAULT 'hoch',
                risiko_bewertung varchar(20) DEFAULT 'niedrig',
                komplexitaet varchar(20) DEFAULT 'standard',
                
                -- Communication
                kommunikation_sprache varchar(5) DEFAULT 'de',
                bevorzugter_kontakt varchar(20) DEFAULT 'email',
                
                -- Additional metadata
                kategorie varchar(50) DEFAULT 'GDPR_SPAM',
                unterkategorie varchar(50) DEFAULT 'Newsletter',
                bearbeitungsstatus varchar(30) DEFAULT 'neu',
                prioritaet_intern varchar(20) DEFAULT 'normal',
                
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY case_status (case_status),
                KEY debtor_id (debtor_id),
                KEY submission_date (submission_date)
            ) $charset_collate",
            
            'klage_clients' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_clients (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                user_id bigint(20) unsigned,
                users_first_name varchar(100) NOT NULL,
                users_last_name varchar(100) NOT NULL,
                users_email varchar(255) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate",
            
            'klage_emails' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_emails (
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
            ) $charset_collate",
            
            // Financial tables moved to separate plugin
            // 'klage_financial' => removed in v1.4.7
            
            'klage_courts' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_courts (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                court_name varchar(100) NOT NULL,
                court_address varchar(200) NOT NULL,
                court_egvp_id varchar(20),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate",
            
            'klage_audit' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_audit (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                action varchar(50) NOT NULL,
                details text,
                user_id bigint(20) unsigned NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate",
            
            'klage_debtors' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_debtors (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                
                -- Basic Information
                debtors_name varchar(200) NOT NULL,
                debtors_company varchar(200),
                debtors_first_name varchar(100),
                debtors_last_name varchar(100),
                debtors_email varchar(255),
                debtors_phone varchar(50),
                debtors_fax varchar(50),
                
                -- Address Information
                debtors_address varchar(200),
                debtors_street varchar(150),
                debtors_house_number varchar(20),
                debtors_address_addition varchar(100),
                debtors_postal_code varchar(20),
                debtors_city varchar(100),
                debtors_state varchar(100),
                debtors_country varchar(100) DEFAULT 'Deutschland',
                
                -- Legal Information
                rechtsform varchar(50) DEFAULT 'natuerliche_person',
                handelsregister_nr varchar(50),
                ustid varchar(50),
                geschaeftsfuehrer varchar(200),
                
                -- Additional Contact
                website varchar(255),
                social_media text,
                
                -- Financial Information
                zahlungsverhalten varchar(20) DEFAULT 'unbekannt',
                bonität varchar(20) DEFAULT 'unbekannt',
                
                -- Legal Status
                insolvenz_status varchar(20) DEFAULT 'nein',
                pfändung_status varchar(20) DEFAULT 'nein',
                
                -- Communication preferences
                bevorzugte_sprache varchar(5) DEFAULT 'de',
                kommunikation_email tinyint(1) DEFAULT 1,
                kommunikation_post tinyint(1) DEFAULT 1,
                
                -- Metadata
                datenquelle varchar(50) DEFAULT 'manual',
                verifiziert tinyint(1) DEFAULT 0,
                letzte_aktualisierung datetime DEFAULT NULL,
                
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY debtors_name (debtors_name),
                KEY debtors_email (debtors_email),
                KEY debtors_postal_code (debtors_postal_code),
                KEY debtors_city (debtors_city)
            ) $charset_collate",
            
            // Financial fields table moved to separate plugin
            // 'klage_financial_fields' => removed in v1.4.7
            
            'klage_import_templates' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_import_templates (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                template_name varchar(100) NOT NULL,
                template_type varchar(50) NOT NULL,
                field_mapping text NOT NULL,
                default_values text,
                validation_rules text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate",
            
            'klage_documents' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_documents (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                document_type varchar(50) NOT NULL,
                document_name varchar(200) NOT NULL,
                document_path varchar(500),
                document_url varchar(500),
                document_size bigint(20) DEFAULT 0,
                document_mime_type varchar(100),
                document_hash varchar(64),
                document_status varchar(20) DEFAULT 'active',
                created_by bigint(20) unsigned,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY document_type (document_type)
            ) $charset_collate",
            
            'klage_communications' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_communications (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                communication_type varchar(50) NOT NULL,
                direction varchar(20) NOT NULL,
                sender_name varchar(200),
                sender_email varchar(255),
                recipient_name varchar(200),
                recipient_email varchar(255),
                subject varchar(500),
                content text,
                sent_date datetime NOT NULL,
                status varchar(20) DEFAULT 'sent',
                tracking_id varchar(100),
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY communication_type (communication_type),
                KEY sent_date (sent_date)
            ) $charset_collate",
            
            'klage_deadlines' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_deadlines (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                deadline_type varchar(50) NOT NULL,
                deadline_date date NOT NULL,
                deadline_time time DEFAULT '23:59:59',
                description text,
                reminder_sent tinyint(1) DEFAULT 0,
                status varchar(20) DEFAULT 'active',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY deadline_date (deadline_date),
                KEY deadline_type (deadline_type)
            ) $charset_collate",
            
            'klage_case_history' => "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_case_history (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                case_id bigint(20) unsigned NOT NULL,
                action_type varchar(50) NOT NULL,
                action_description text,
                old_value text,
                new_value text,
                user_id bigint(20) unsigned,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY case_id (case_id),
                KEY action_type (action_type),
                KEY created_at (created_at)
            ) $charset_collate"
        );
        
        // Create each table individually
        $created_count = 0;
        $failed_count = 0;
        
        foreach ($tables as $table_name => $sql) {
            $result = $this->wpdb->query($sql);
            
            if ($result !== false) {
                $created_count++;
                $results['details'][] = "✅ $table_name: Erfolgreich erstellt";
            } else {
                $failed_count++;
                $results['details'][] = "❌ $table_name: Fehler - " . $this->wpdb->last_error;
                $results['success'] = false;
            }
        }
        
        // Insert default courts if courts table was created
        if ($created_count > 0) {
            $this->insert_default_courts();
        }
        
        if ($results['success']) {
            $results['message'] = "$created_count Tabellen erfolgreich erstellt. Dashboard aktualisieren!";
        } else {
            $results['message'] = "$failed_count Tabellen fehlgeschlagen. Debug-Modus aktivieren für Details.";
        }
        
        return $results;
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
            debtors_country varchar(100) DEFAULT 'Deutschland',
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
        
        // Financial table removed in v1.4.7 - moved to separate plugin
        // Financial functionality now handled by court-automation-hub-financial-calculator plugin
        
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
        // Financial table removed in v1.4.7 - moved to separate plugin
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
        $tables = array('klage_cases', 'klage_debtors', 'klage_clients', 'klage_emails', 'klage_courts');
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