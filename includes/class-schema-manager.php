<?php
/**
 * Database Schema Manager - Complete CRUD System
 * Handles all database schema operations, validation, and synchronization
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Schema_Manager {
    
    private $wpdb;
    private $table_prefix;
    
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix;
    }
    
    /**
     * Get complete schema definition for all plugin tables
     */
    public function get_complete_schema_definition() {
        return array(
            'klage_cases' => array(
                'columns' => array(
                    'id' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                    'case_id' => 'varchar(100) NOT NULL',
                    'case_creation_date' => 'datetime NOT NULL',
                    'case_updated_date' => 'datetime DEFAULT NULL',
                    'case_status' => 'varchar(20) DEFAULT "draft"',
                    'case_priority' => 'varchar(20) DEFAULT "medium"',
                    'case_notes' => 'text DEFAULT NULL',
                    'brief_status' => 'varchar(20) DEFAULT "pending"',
                    'submission_date' => 'date DEFAULT NULL',
                    'mandant' => 'varchar(100) DEFAULT NULL',
                    'client_id' => 'bigint(20) unsigned DEFAULT NULL',
                    'debtor_id' => 'bigint(20) unsigned DEFAULT NULL',
                    'total_amount' => 'decimal(10,2) DEFAULT 0.00',
                    'court_id' => 'bigint(20) unsigned DEFAULT NULL',
                    'import_source' => 'varchar(50) DEFAULT NULL',
                    'briefe' => 'int(3) DEFAULT 1',
                    'schuldner' => 'varchar(200) DEFAULT NULL',
                    'beweise' => 'text DEFAULT NULL',
                    'dokumente' => 'text DEFAULT NULL',
                    'links_zu_dokumenten' => 'text DEFAULT NULL',
                    'verfahrensart' => 'varchar(50) DEFAULT "mahnverfahren"',
                    'rechtsgrundlage' => 'varchar(100) DEFAULT "DSGVO Art. 82"',
                    'zeitraum_von' => 'date DEFAULT NULL',
                    'zeitraum_bis' => 'date DEFAULT NULL',
                    'anzahl_verstoesse' => 'int(5) DEFAULT 1',
                    'schadenhoehe' => 'decimal(10,2) DEFAULT 548.11',
                    'anwaltsschreiben_status' => 'varchar(20) DEFAULT "pending"',
                    'mahnung_status' => 'varchar(20) DEFAULT "pending"',
                    'klage_status' => 'varchar(20) DEFAULT "pending"',
                    'vollstreckung_status' => 'varchar(20) DEFAULT "pending"',
                    'egvp_aktenzeichen' => 'varchar(50) DEFAULT NULL',
                    'xjustiz_uuid' => 'varchar(100) DEFAULT NULL',
                    'gericht_zustaendig' => 'varchar(100) DEFAULT NULL',
                    'verfahrenswert' => 'decimal(10,2) DEFAULT 548.11',
                    'deadline_antwort' => 'date DEFAULT NULL',
                    'deadline_zahlung' => 'date DEFAULT NULL',
                    'mahnung_datum' => 'date DEFAULT NULL',
                    'klage_datum' => 'date DEFAULT NULL',
                    'erfolgsaussicht' => 'varchar(20) DEFAULT "hoch"',
                    'risiko_bewertung' => 'varchar(20) DEFAULT "niedrig"',
                    'komplexitaet' => 'varchar(20) DEFAULT "standard"',
                    'kommunikation_sprache' => 'varchar(5) DEFAULT "de"',
                    'bevorzugter_kontakt' => 'varchar(20) DEFAULT "email"',
                    'kategorie' => 'varchar(50) DEFAULT "GDPR_SPAM"',
                    'prioritaet_intern' => 'varchar(20) DEFAULT "medium"',
                    'bearbeitungsstatus' => 'varchar(20) DEFAULT "neu"',
                    'case_deadline_response' => 'date DEFAULT NULL',
                    'case_deadline_payment' => 'date DEFAULT NULL',
                    'processing_complexity' => 'varchar(20) DEFAULT "standard"',
                    'processing_risk_score' => 'decimal(3,2) DEFAULT 0.50',
                    'document_type' => 'varchar(50) DEFAULT "email"',
                    'document_language' => 'varchar(5) DEFAULT "de"',
                    'created_at' => 'datetime DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                ),
                'primary_key' => 'id',
                'indexes' => array(
                    'case_id' => array('case_id'),
                    'case_status' => array('case_status'),
                    'debtor_id' => array('debtor_id'),
                    'submission_date' => array('submission_date')
                )
            ),
            
            'klage_debtors' => array(
                'columns' => array(
                    'id' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                    'debtors_name' => 'varchar(200) NOT NULL',
                    'debtors_company' => 'varchar(200) DEFAULT NULL',
                    'debtors_first_name' => 'varchar(100) DEFAULT NULL',
                    'debtors_last_name' => 'varchar(100) DEFAULT NULL',
                    'debtors_email' => 'varchar(255) DEFAULT NULL',
                    'debtors_phone' => 'varchar(50) DEFAULT NULL',
                    'debtors_fax' => 'varchar(50) DEFAULT NULL',
                    'debtors_address' => 'varchar(200) DEFAULT NULL',
                    'debtors_street' => 'varchar(150) DEFAULT NULL',
                    'debtors_house_number' => 'varchar(20) DEFAULT NULL',
                    'debtors_address_addition' => 'varchar(100) DEFAULT NULL',
                    'debtors_postal_code' => 'varchar(20) DEFAULT NULL',
                    'debtors_city' => 'varchar(100) DEFAULT NULL',
                    'debtors_state' => 'varchar(100) DEFAULT NULL',
                    'debtors_country' => 'varchar(100) DEFAULT "Deutschland"',
                    'rechtsform' => 'varchar(50) DEFAULT "natuerliche_person"',
                    'handelsregister_nr' => 'varchar(50) DEFAULT NULL',
                    'ustid' => 'varchar(50) DEFAULT NULL',
                    'geschaeftsfuehrer' => 'varchar(200) DEFAULT NULL',
                    'website' => 'varchar(255) DEFAULT NULL',
                    'social_media' => 'text DEFAULT NULL',
                    'finanzielle_situation' => 'varchar(50) DEFAULT "unbekannt"',
                    'zahlungsverhalten' => 'varchar(20) DEFAULT "unbekannt"',
                    'bonität' => 'varchar(20) DEFAULT "unbekannt"',
                    'insolvenz_status' => 'varchar(20) DEFAULT "nein"',
                    'pfändung_status' => 'varchar(20) DEFAULT "nein"',
                    'bevorzugte_sprache' => 'varchar(5) DEFAULT "de"',
                    'kommunikation_email' => 'tinyint(1) DEFAULT 1',
                    'kommunikation_post' => 'tinyint(1) DEFAULT 1',
                    'datenquelle' => 'varchar(50) DEFAULT "manual"',
                    'verifiziert' => 'tinyint(1) DEFAULT 0',
                    'letzte_aktualisierung' => 'datetime DEFAULT NULL',
                    'created_at' => 'datetime DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                ),
                'primary_key' => 'id',
                'indexes' => array(
                    'debtors_name' => array('debtors_name'),
                    'debtors_email' => array('debtors_email'),
                    'debtors_postal_code' => array('debtors_postal_code'),
                    'debtors_city' => array('debtors_city')
                )
            ),
            
            'klage_financial' => array(
                'columns' => array(
                    'id' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                    'case_id' => 'bigint(20) unsigned NOT NULL',
                    'damages_loss' => 'decimal(10,2) DEFAULT 350.00',
                    'partner_fees' => 'decimal(10,2) DEFAULT 96.90',
                    'communication_fees' => 'decimal(10,2) DEFAULT 13.36',
                    'vat' => 'decimal(10,2) DEFAULT 87.85',
                    'court_fees' => 'decimal(10,2) DEFAULT 32.00',
                    'total' => 'decimal(10,2) DEFAULT 548.11',
                    'streitwert' => 'decimal(10,2) DEFAULT 548.11',
                    'schadenersatz' => 'decimal(10,2) DEFAULT 350.00',
                    'anwaltskosten' => 'decimal(10,2) DEFAULT 96.90',
                    'gerichtskosten' => 'decimal(10,2) DEFAULT 32.00',
                    'nebenkosten' => 'decimal(10,2) DEFAULT 13.36',
                    'auslagen' => 'decimal(10,2) DEFAULT 0.00',
                    'mahnkosten' => 'decimal(10,2) DEFAULT 0.00',
                    'vollstreckungskosten' => 'decimal(10,2) DEFAULT 0.00',
                    'zinsen' => 'decimal(10,2) DEFAULT 0.00',
                    'payment_status' => 'varchar(20) DEFAULT "offen"',
                    'payment_date' => 'date DEFAULT NULL',
                    'payment_amount' => 'decimal(10,2) DEFAULT 0.00',
                    'payment_method' => 'varchar(50) DEFAULT NULL',
                    'kostenkategorie' => 'varchar(50) DEFAULT "GDPR_Standard"',
                    'gebuehrenstruktur' => 'varchar(50) DEFAULT "RVG"',
                    'custom_fields' => 'text DEFAULT NULL',
                    'calculation_template_id' => 'bigint(20) unsigned DEFAULT NULL',
                    'created_at' => 'datetime DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
                ),
                'primary_key' => 'id',
                'indexes' => array(
                    'case_id' => array('case_id'),
                    'payment_status' => array('payment_status')
                )
            ),
            
            'klage_audit' => array(
                'columns' => array(
                    'id' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
                    'case_id' => 'bigint(20) unsigned NOT NULL',
                    'action' => 'varchar(50) NOT NULL',
                    'details' => 'text DEFAULT NULL',
                    'user_id' => 'bigint(20) unsigned NOT NULL',
                    'created_at' => 'datetime DEFAULT CURRENT_TIMESTAMP'
                ),
                'primary_key' => 'id',
                'indexes' => array(
                    'case_id' => array('case_id'),
                    'action' => array('action'),
                    'user_id' => array('user_id')
                )
            )
        );
    }
    
    /**
     * Get current database schema for a table
     */
    public function get_current_table_schema($table_name) {
        $full_table_name = $this->table_prefix . $table_name;
        
        // Check if table exists
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
        if (!$table_exists) {
            return false;
        }
        
        // Get column information
        $columns = $this->wpdb->get_results("SHOW COLUMNS FROM $full_table_name", ARRAY_A);
        $indexes = $this->wpdb->get_results("SHOW INDEX FROM $full_table_name", ARRAY_A);
        
        $schema = array(
            'columns' => array(),
            'indexes' => array()
        );
        
        foreach ($columns as $column) {
            $schema['columns'][$column['Field']] = $column;
        }
        
        foreach ($indexes as $index) {
            if ($index['Key_name'] !== 'PRIMARY') {
                $schema['indexes'][$index['Key_name']][] = $index['Column_name'];
            } else {
                $schema['primary_key'] = $index['Column_name'];
            }
        }
        
        return $schema;
    }
    
    /**
     * Compare expected schema with current database schema
     */
    public function compare_schemas($table_name) {
        $expected = $this->get_complete_schema_definition()[$table_name] ?? null;
        $current = $this->get_current_table_schema($table_name);
        
        if (!$expected) {
            return array('error' => 'Table not defined in expected schema');
        }
        
        if (!$current) {
            return array('status' => 'table_missing', 'action' => 'create_table');
        }
        
        $differences = array();
        
        // Compare columns
        foreach ($expected['columns'] as $col_name => $col_def) {
            if (!isset($current['columns'][$col_name])) {
                $differences['missing_columns'][] = $col_name;
            }
        }
        
        // Check for extra columns (not in expected)
        foreach ($current['columns'] as $col_name => $col_def) {
            if (!isset($expected['columns'][$col_name])) {
                $differences['extra_columns'][] = $col_name;
            }
        }
        
        return $differences;
    }
    
    /**
     * Synchronize database schema with expected schema
     */
    public function synchronize_schema($table_name) {
        $differences = $this->compare_schemas($table_name);
        
        if (isset($differences['error'])) {
            return array('success' => false, 'message' => $differences['error']);
        }
        
        if (isset($differences['status']) && $differences['status'] === 'table_missing') {
            return $this->create_table($table_name);
        }
        
        $full_table_name = $this->table_prefix . $table_name;
        $expected = $this->get_complete_schema_definition()[$table_name];
        
        // Add missing columns
        if (isset($differences['missing_columns'])) {
            foreach ($differences['missing_columns'] as $col_name) {
                $col_def = $expected['columns'][$col_name];
                $sql = "ALTER TABLE $full_table_name ADD COLUMN $col_name $col_def";
                $result = $this->wpdb->query($sql);
                
                if ($result === false) {
                    return array('success' => false, 'message' => 'Failed to add column: ' . $col_name);
                }
            }
        }
        
        return array('success' => true, 'message' => 'Schema synchronized successfully');
    }
    
    /**
     * Create table with complete schema
     */
    public function create_table($table_name) {
        $schema = $this->get_complete_schema_definition()[$table_name] ?? null;
        
        if (!$schema) {
            return array('success' => false, 'message' => 'Table schema not defined');
        }
        
        $full_table_name = $this->table_prefix . $table_name;
        $charset_collate = $this->wpdb->get_charset_collate();
        
        // Build column definitions
        $column_defs = array();
        foreach ($schema['columns'] as $col_name => $col_def) {
            $column_defs[] = "$col_name $col_def";
        }
        
        // Add primary key
        if (isset($schema['primary_key'])) {
            $column_defs[] = "PRIMARY KEY ({$schema['primary_key']})";
        }
        
        // Add indexes
        if (isset($schema['indexes'])) {
            foreach ($schema['indexes'] as $index_name => $index_columns) {
                $columns = implode(', ', $index_columns);
                $column_defs[] = "KEY $index_name ($columns)";
            }
        }
        
        $sql = "CREATE TABLE $full_table_name (\n" . implode(",\n", $column_defs) . "\n) $charset_collate";
        
        $result = $this->wpdb->query($sql);
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to create table: ' . $this->wpdb->last_error);
        }
        
        return array('success' => true, 'message' => 'Table created successfully');
    }
    
    /**
     * Synchronize all plugin tables
     */
    public function synchronize_all_tables() {
        $results = array();
        $schema_definition = $this->get_complete_schema_definition();
        
        foreach ($schema_definition as $table_name => $schema) {
            $results[$table_name] = $this->synchronize_schema($table_name);
        }
        
        return $results;
    }
    
    /**
     * Get table data with pagination
     */
    public function get_table_data($table_name, $limit = 50, $offset = 0) {
        $full_table_name = $this->table_prefix . $table_name;
        
        // Check if table exists
        $table_exists = $this->wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
        if (!$table_exists) {
            return array('error' => 'Table does not exist');
        }
        
        $sql = "SELECT * FROM $full_table_name LIMIT $limit OFFSET $offset";
        $data = $this->wpdb->get_results($sql, ARRAY_A);
        
        $count_sql = "SELECT COUNT(*) FROM $full_table_name";
        $total_count = $this->wpdb->get_var($count_sql);
        
        return array(
            'data' => $data,
            'total' => $total_count,
            'limit' => $limit,
            'offset' => $offset
        );
    }
    
    /**
     * Insert data into table
     */
    public function insert_data($table_name, $data) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $result = $this->wpdb->insert($full_table_name, $data);
        
        if ($result === false) {
            return array('success' => false, 'message' => $this->wpdb->last_error);
        }
        
        return array('success' => true, 'id' => $this->wpdb->insert_id);
    }
    
    /**
     * Update data in table
     */
    public function update_data($table_name, $data, $where) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $result = $this->wpdb->update($full_table_name, $data, $where);
        
        if ($result === false) {
            return array('success' => false, 'message' => $this->wpdb->last_error);
        }
        
        return array('success' => true, 'rows_affected' => $result);
    }
    
    /**
     * Delete data from table
     */
    public function delete_data($table_name, $where) {
        $full_table_name = $this->table_prefix . $table_name;
        
        $result = $this->wpdb->delete($full_table_name, $where);
        
        if ($result === false) {
            return array('success' => false, 'message' => $this->wpdb->last_error);
        }
        
        return array('success' => true, 'rows_affected' => $result);
    }
    
    /**
     * Get schema validation status
     */
    public function get_schema_status() {
        $status = array();
        $schema_definition = $this->get_complete_schema_definition();
        
        foreach ($schema_definition as $table_name => $schema) {
            $differences = $this->compare_schemas($table_name);
            
            if (isset($differences['error'])) {
                $status[$table_name] = array('status' => 'error', 'message' => $differences['error']);
            } elseif (isset($differences['status']) && $differences['status'] === 'table_missing') {
                $status[$table_name] = array('status' => 'missing', 'message' => 'Table does not exist');
            } elseif (empty($differences)) {
                $status[$table_name] = array('status' => 'ok', 'message' => 'Schema is synchronized');
            } else {
                $missing = count($differences['missing_columns'] ?? array());
                $extra = count($differences['extra_columns'] ?? array());
                $status[$table_name] = array(
                    'status' => 'out_of_sync',
                    'message' => "Missing columns: $missing, Extra columns: $extra",
                    'details' => $differences
                );
            }
        }
        
        return $status;
    }
}