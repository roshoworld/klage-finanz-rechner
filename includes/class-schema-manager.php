<?php
/**
 * Schema Manager - Database schema synchronization v1.5.1
 */

if (!class_exists('CAH_Schema_Manager')) {
    class CAH_Schema_Manager {
        
        private $current_version = '1.5.1';
        
        public function synchronize_all_tables() {
            $this->check_version_and_upgrade();
            $this->ensure_indexes();
            $this->cleanup_orphaned_data();
        }
        
        private function check_version_and_upgrade() {
            $stored_version = get_option('cah_db_version', '0.0.0');
            
            if (version_compare($stored_version, $this->current_version, '<')) {
                $this->run_upgrades($stored_version);
                update_option('cah_db_version', $this->current_version);
            }
        }
        
        private function run_upgrades($from_version) {
            global $wpdb;
            
            // Upgrade from any version below 1.5.0
            if (version_compare($from_version, '1.5.0', '<')) {
                $this->upgrade_to_150();
            }
            
            // Upgrade to 1.5.1
            if (version_compare($from_version, '1.5.1', '<')) {
                $this->upgrade_to_151();
            }
        }
        
        private function upgrade_to_150() {
            global $wpdb;
            
            // Add missing columns that might not exist in older versions
            $cases_table = $wpdb->prefix . 'klage_cases';
            
            $columns_to_add = array(
                'debtor_id' => "ALTER TABLE $cases_table ADD COLUMN debtor_id int(11) AFTER case_number",
                'lawyer_id' => "ALTER TABLE $cases_table ADD COLUMN lawyer_id int(11) AFTER debtor_id",
                'court_id' => "ALTER TABLE $cases_table ADD COLUMN court_id int(11) AFTER lawyer_id",
                'priority' => "ALTER TABLE $cases_table ADD COLUMN priority varchar(20) DEFAULT 'Normal' AFTER status",
                'legal_basis' => "ALTER TABLE $cases_table ADD COLUMN legal_basis text AFTER description",
                'evidence_files' => "ALTER TABLE $cases_table ADD COLUMN evidence_files text AFTER legal_basis",
                'next_action_date' => "ALTER TABLE $cases_table ADD COLUMN next_action_date datetime AFTER evidence_files",
                'created_by' => "ALTER TABLE $cases_table ADD COLUMN created_by int(11) AFTER updated_at",
                'updated_by' => "ALTER TABLE $cases_table ADD COLUMN updated_by int(11) AFTER created_by"
            );
            
            foreach ($columns_to_add as $column => $sql) {
                $exists = $wpdb->get_results("SHOW COLUMNS FROM $cases_table LIKE '$column'");
                if (empty($exists)) {
                    $wpdb->query($sql);
                }
            }
        }
        
        private function upgrade_to_151() {
            // Add any 1.5.1 specific upgrades here
            $this->ensure_foreign_keys();
        }
        
        private function ensure_indexes() {
            global $wpdb;
            
            // Ensure important indexes exist
            $cases_table = $wpdb->prefix . 'klage_cases';
            
            $indexes = array(
                "CREATE INDEX idx_case_status ON $cases_table (status)",
                "CREATE INDEX idx_case_created ON $cases_table (created_at)",
                "CREATE INDEX idx_case_debtor ON $cases_table (debtor_id)",
                "CREATE INDEX idx_case_lawyer ON $cases_table (lawyer_id)"
            );
            
            foreach ($indexes as $index_sql) {
                $wpdb->query($index_sql);
            }
        }
        
        private function ensure_foreign_keys() {
            // Note: MySQL foreign keys would go here if needed
            // For now, we maintain referential integrity in application logic
        }
        
        private function cleanup_orphaned_data() {
            global $wpdb;
            
            // Clean up any orphaned records
            $cases_table = $wpdb->prefix . 'klage_cases';
            $debtors_table = $wpdb->prefix . 'klage_debtors';
            
            // Remove cases with invalid debtor_id
            $wpdb->query("
                UPDATE $cases_table c 
                LEFT JOIN $debtors_table d ON c.debtor_id = d.id 
                SET c.debtor_id = NULL 
                WHERE c.debtor_id IS NOT NULL AND d.id IS NULL
            ");
        }
        
        public function get_schema_info() {
            return array(
                'version' => $this->current_version,
                'stored_version' => get_option('cah_db_version', '0.0.0'),
                'tables' => $this->get_table_info()
            );
        }
        
        private function get_table_info() {
            global $wpdb;
            
            $tables = array(
                'klage_cases',
                'klage_debtors', 
                'klage_lawyers',
                'klage_courts',
                'klage_documents',
                'klage_audit_log'
            );
            
            $info = array();
            foreach ($tables as $table) {
                $full_name = $wpdb->prefix . $table;
                $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_name'");
                $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_name") : 0;
                
                $info[$table] = array(
                    'exists' => !empty($exists),
                    'count' => $count,
                    'full_name' => $full_name
                );
            }
            
            return $info;
        }
    }
}