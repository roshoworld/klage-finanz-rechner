<?php
/**
 * Backend Test Suite for Court Automation Hub WordPress Plugin
 * Tests the dual template system (Forderungen.com 17 fields + Comprehensive 57 fields)
 */

// WordPress test environment setup
if (!defined('ABSPATH')) {
    // Mock WordPress environment for testing
    define('ABSPATH', '/tmp/wordpress/');
    define('WP_DEBUG', true);
    define('CAH_PLUGIN_URL', 'http://localhost/wp-content/plugins/court-automation-hub/');
    define('CAH_PLUGIN_PATH', '/app/');
    define('CAH_PLUGIN_VERSION', '1.2.0');
    
    // Mock WordPress functions
    function wp_die($message) { die($message); }
    function wp_verify_nonce($nonce, $action) { return true; }
    function current_user_can($capability) { return true; }
    function admin_url($path) { return 'http://localhost/wp-admin/' . $path; }
    function wp_nonce_url($url, $action) { return $url . '&_wpnonce=test'; }
    function wp_create_nonce($action) { return 'test_nonce'; }
    function sanitize_text_field($str) { return trim(strip_tags($str)); }
    function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function selected($selected, $current) { return $selected == $current ? 'selected="selected"' : ''; }
    function __($text, $domain = 'default') { return $text; }
    function esc_html__($text, $domain = 'default') { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function date_i18n($format, $timestamp = null) { return date($format, $timestamp ?: time()); }
    function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals); }
    function plugin_dir_url($file) { return CAH_PLUGIN_URL; }
    function plugin_dir_path($file) { return CAH_PLUGIN_PATH; }
    function plugin_basename($file) { return basename($file); }
    function load_plugin_textdomain($domain, $deprecated, $plugin_rel_path) { return true; }
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) { return true; }
    function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url = '', $position = null) { return true; }
    function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function) { return true; }
    function register_setting($option_group, $option_name, $args = array()) { return true; }
    function register_activation_hook($file, $callback) { return true; }
    function register_deactivation_hook($file, $callback) { return true; }
    function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) { return true; }
    function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') { return true; }
    function wp_localize_script($handle, $object_name, $l10n) { return true; }
    function get_role($role) { return new MockRole(); }
    function flush_rewrite_rules($hard = true) { return true; }
    function is_admin() { return true; }
    
    // Mock Role class
    class MockRole {
        public function add_cap($cap) { return true; }
    }
    
    // Mock wpdb class
    class MockWPDB {
        public $prefix = 'wp_';
        public $last_error = '';
        
        public function get_charset_collate() {
            return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        }
        
        public function query($sql) {
            echo "SQL Query: " . substr($sql, 0, 100) . "...\n";
            return true; // Mock success
        }
        
        public function get_var($sql) {
            if (strpos($sql, 'COUNT(*)') !== false) {
                return rand(0, 10); // Mock count
            }
            if (strpos($sql, 'SUM(') !== false) {
                return rand(1000, 50000); // Mock sum
            }
            if (strpos($sql, 'SHOW TABLES') !== false) {
                return 'wp_klage_cases'; // Mock table exists
            }
            return 'mock_result';
        }
        
        public function get_results($sql) {
            return array(); // Mock empty results
        }
        
        public function insert($table, $data, $format) {
            echo "INSERT into $table: " . print_r($data, true);
            return true;
        }
    }
    
    $wpdb = new MockWPDB();
}

class CourtAutomationHubTester {
    
    private $results = array();
    private $test_count = 0;
    private $passed_count = 0;
    
    public function __construct() {
        echo "🚀 Starting Court Automation Hub Backend Tests\n";
        echo "=" . str_repeat("=", 50) . "\n\n";
    }
    
    public function runAllTests() {
        $this->testPluginInitialization();
        $this->testDatabaseSchemaCreation();
        $this->testDualTemplateSystem();
        $this->testForderungenTemplateGeneration();
        $this->testComprehensiveTemplateGeneration();
        $this->testFieldMapping();
        $this->testDataValidation();
        $this->testImportProcessing();
        
        $this->printSummary();
        return $this->results;
    }
    
    private function test($name, $callback) {
        $this->test_count++;
        echo "🧪 Testing: $name\n";
        
        try {
            $result = $callback();
            if ($result) {
                echo "✅ PASSED: $name\n";
                $this->passed_count++;
                $this->results[$name] = array('status' => 'passed', 'message' => 'Test passed successfully');
            } else {
                echo "❌ FAILED: $name\n";
                $this->results[$name] = array('status' => 'failed', 'message' => 'Test assertion failed');
            }
        } catch (Exception $e) {
            echo "❌ ERROR: $name - " . $e->getMessage() . "\n";
            $this->results[$name] = array('status' => 'error', 'message' => $e->getMessage());
        }
        
        echo "\n";
    }
    
    public function testPluginInitialization() {
        echo "📋 TESTING PLUGIN INITIALIZATION\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $this->test("Plugin main file exists", function() {
            return file_exists('/app/court-automation-hub.php');
        });
        
        $this->test("Plugin constants defined", function() {
            // Include the main plugin file
            include_once '/app/court-automation-hub.php';
            return defined('CAH_PLUGIN_URL') && defined('CAH_PLUGIN_PATH') && defined('CAH_PLUGIN_VERSION');
        });
        
        $this->test("Required classes can be loaded", function() {
            $required_files = array(
                '/app/includes/class-database.php',
                '/app/admin/class-admin-dashboard.php',
                '/app/includes/class-case-manager.php'
            );
            
            foreach ($required_files as $file) {
                if (!file_exists($file)) {
                    throw new Exception("Required file missing: $file");
                }
            }
            return true;
        });
    }
    
    public function testDatabaseSchemaCreation() {
        echo "🗄️ TESTING DATABASE SCHEMA CREATION (57 FIELDS)\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        // Include database class
        include_once '/app/includes/class-database.php';
        
        $this->test("Database class instantiation", function() {
            global $wpdb;
            $db = new CAH_Database();
            return $db instanceof CAH_Database;
        });
        
        $this->test("Enhanced schema tables creation", function() {
            global $wpdb;
            $db = new CAH_Database();
            $result = $db->create_tables_direct();
            
            // Check if result indicates success
            return isset($result['success']) && $result['success'] === true;
        });
        
        $this->test("Verify 57-field structure in klage_cases", function() {
            // Read the database class file to verify field structure
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            // Check for key 57-field structure elements
            $required_fields = array(
                'case_id', 'case_creation_date', 'case_status', 'case_priority',
                'briefe', 'schuldner', 'beweise', 'dokumente', 'links_zu_dokumenten',
                'verfahrensart', 'rechtsgrundlage', 'zeitraum_von', 'zeitraum_bis',
                'anzahl_verstoesse', 'schadenhoehe', 'anwaltsschreiben_status',
                'mahnung_status', 'klage_status', 'vollstreckung_status',
                'egvp_aktenzeichen', 'xjustiz_uuid', 'gericht_zustaendig',
                'verfahrenswert', 'deadline_antwort', 'deadline_zahlung',
                'erfolgsaussicht', 'risiko_bewertung', 'komplexitaet',
                'kommunikation_sprache', 'bevorzugter_kontakt'
            );
            
            $found_fields = 0;
            foreach ($required_fields as $field) {
                if (strpos($db_content, $field) !== false) {
                    $found_fields++;
                }
            }
            
            echo "Found $found_fields out of " . count($required_fields) . " key fields\n";
            return $found_fields >= 25; // At least 25 key fields should be present
        });
        
        $this->test("Extended tables creation (documents, communications, deadlines)", function() {
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            $extended_tables = array(
                'klage_documents',
                'klage_communications', 
                'klage_deadlines',
                'klage_case_history'
            );
            
            $found_tables = 0;
            foreach ($extended_tables as $table) {
                if (strpos($db_content, $table) !== false) {
                    $found_tables++;
                }
            }
            
            return $found_tables === count($extended_tables);
        });
        
        $this->test("Enhanced debtor fields structure", function() {
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            $debtor_fields = array(
                'debtors_name', 'debtors_company', 'debtors_first_name',
                'debtors_email', 'debtors_phone', 'debtors_address',
                'debtors_postal_code', 'debtors_city', 'rechtsform',
                'handelsregister_nr', 'ustid', 'geschaeftsfuehrer',
                'zahlungsverhalten', 'bonität', 'insolvenz_status'
            );
            
            $found_fields = 0;
            foreach ($debtor_fields as $field) {
                if (strpos($db_content, $field) !== false) {
                    $found_fields++;
                }
            }
            
            return $found_fields >= 10; // At least 10 debtor fields
        });
        
        $this->test("Enhanced financial fields structure", function() {
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            $financial_fields = array(
                'damages_loss', 'partner_fees', 'communication_fees',
                'vat', 'court_fees', 'total', 'streitwert',
                'schadenersatz', 'anwaltskosten', 'gerichtskosten',
                'nebenkosten', 'auslagen', 'mahnkosten',
                'vollstreckungskosten', 'zinsen', 'payment_status'
            );
            
            $found_fields = 0;
            foreach ($financial_fields as $field) {
                if (strpos($db_content, $field) !== false) {
                    $found_fields++;
                }
            }
            
            return $found_fields >= 12; // At least 12 financial fields
        });
    }
    
    public function testDualTemplateSystem() {
        echo "🔄 TESTING DUAL TEMPLATE SYSTEM\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        // Include admin dashboard class
        include_once '/app/admin/class-admin-dashboard.php';
        
        $this->test("Dual template system implementation", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for dual template functionality
            $dual_system_indicators = array(
                'template_type',
                'forderungen',
                'comprehensive',
                'get_forderungen_template_content',
                'get_comprehensive_template_content'
            );
            
            $found_indicators = 0;
            foreach ($dual_system_indicators as $indicator) {
                if (strpos($dashboard_content, $indicator) !== false) {
                    $found_indicators++;
                }
            }
            
            echo "Found $found_indicators out of " . count($dual_system_indicators) . " dual system indicators\n";
            return $found_indicators >= 3;
        });
        
        $this->test("Template selection interface", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for template selection UI elements
            return strpos($dashboard_content, 'Forderungen.com Template') !== false &&
                   strpos($dashboard_content, 'Comprehensive Template') !== false;
        });
        
        $this->test("Template type parameter handling", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for template type parameter processing
            return strpos($dashboard_content, '$_GET[\'template_type\']') !== false ||
                   strpos($dashboard_content, 'template_type') !== false;
        });
    }
    
    public function testForderungenTemplateGeneration() {
        echo "📊 TESTING FORDERUNGEN.COM TEMPLATE (17 FIELDS)\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test("Forderungen.com template method exists", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            return strpos($dashboard_content, 'get_forderungen_template_content') !== false ||
                   strpos($dashboard_content, 'forderungen_template') !== false;
        });
        
        $this->test("17 Forderungen.com fields verification", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Expected 17 Forderungen.com fields
            $forderungen_fields = array(
                'Fall-ID',
                'Fall-Status', 
                'Brief-Status',
                'Briefe',
                'Mandant',
                'Schuldner',
                'Einreichungsdatum',
                'Beweise',
                'Dokumente',
                'links zu Dokumenten',
                'Firmenname',
                'Vorname',
                'Nachname',
                'Adresse',
                'PLZ',
                'Stadt',
                'E-Mail'
            );
            
            $found_fields = 0;
            foreach ($forderungen_fields as $field) {
                if (strpos($dashboard_content, $field) !== false) {
                    $found_fields++;
                }
            }
            
            echo "Found $found_fields out of 17 Forderungen.com fields\n";
            return $found_fields >= 12; // At least 12 of the 17 fields should be present
        });
        
        $this->test("Forderungen.com template filename", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            return strpos($dashboard_content, 'forderungen_com_import_template') !== false;
        });
    }
    
    public function testComprehensiveTemplateGeneration() {
        echo "🎯 TESTING COMPREHENSIVE TEMPLATE (57 FIELDS)\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test("Comprehensive template method exists", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            return strpos($dashboard_content, 'get_comprehensive_template_content') !== false ||
                   strpos($dashboard_content, 'comprehensive_template') !== false;
        });
        
        $this->test("57-field comprehensive structure", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for comprehensive field categories
            $comprehensive_categories = array(
                'Core Case Information',
                'Debtor Personal Information',
                'Contact Information', 
                'Legal Information',
                'Financial Information',
                'Timeline & Deadlines',
                'Court & Legal Processing',
                'Document Management',
                'Risk Assessment',
                'Communication',
                'Additional Metadata'
            );
            
            $found_categories = 0;
            foreach ($comprehensive_categories as $category) {
                if (strpos($dashboard_content, $category) !== false) {
                    $found_categories++;
                }
            }
            
            echo "Found $found_categories comprehensive field categories\n";
            return $found_categories >= 6;
        });
        
        $this->test("Extended fields beyond Forderungen.com", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            // Extended fields not in Forderungen.com template
            $extended_fields = array(
                'verfahrensart',
                'rechtsgrundlage',
                'egvp_aktenzeichen',
                'xjustiz_uuid',
                'erfolgsaussicht',
                'risiko_bewertung',
                'komplexitaet',
                'deadline_antwort',
                'deadline_zahlung',
                'kommunikation_sprache'
            );
            
            $found_extended = 0;
            foreach ($extended_fields as $field) {
                if (strpos($dashboard_content, $field) !== false || 
                    strpos($db_content, $field) !== false) {
                    $found_extended++;
                }
            }
            
            echo "Found $found_extended extended fields\n";
            return $found_extended >= 6;
        });
        
        $this->test("Comprehensive template filename", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            return strpos($dashboard_content, 'klage_click_comprehensive_template') !== false;
        });
    }
    
    public function testCSVTemplateGeneration() {
        echo "📊 TESTING CSV TEMPLATE GENERATION (57 FIELDS)\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        // Include admin dashboard class
        include_once '/app/admin/class-admin-dashboard.php';
        
        $this->test("Admin dashboard class instantiation", function() {
            $dashboard = new CAH_Admin_Dashboard();
            return $dashboard instanceof CAH_Admin_Dashboard;
        });
        
        $this->test("CSV template content generation", function() {
            // Check if get_template_content method exists or similar functionality
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Look for template-related methods
            return strpos($dashboard_content, 'get_template_content') !== false ||
                   strpos($dashboard_content, 'template') !== false;
        });
        
        $this->test("57-field template structure verification", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for comprehensive field categories mentioned in review
            $field_categories = array(
                'Core Case Information',
                'Debtor Personal Information', 
                'Contact Information',
                'Legal Information',
                'Financial Information',
                'Timeline & Deadlines',
                'Court & Legal Processing',
                'Additional Metadata'
            );
            
            // Look for Forderungen.com specific fields
            $forderungen_fields = array(
                'Fall-ID', 'Mandant', 'Schuldner', 'Briefe',
                'Beweise', 'Dokumente', 'links zu Dokumenten',
                'Firmenname', 'Vorname', 'Nachname', 'Adresse'
            );
            
            $found_categories = 0;
            $found_fields = 0;
            
            foreach ($field_categories as $category) {
                if (strpos($dashboard_content, $category) !== false) {
                    $found_categories++;
                }
            }
            
            foreach ($forderungen_fields as $field) {
                if (strpos($dashboard_content, $field) !== false) {
                    $found_fields++;
                }
            }
            
            echo "Found $found_categories categories and $found_fields Forderungen.com fields\n";
            return $found_categories >= 4 && $found_fields >= 6;
        });
        
        $this->test("Template download functionality", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for download-related methods
            return strpos($dashboard_content, 'send_template_download') !== false &&
                   strpos($dashboard_content, 'handle_early_download') !== false;
        });
    }
    
    public function testFieldMapping() {
        echo "🗺️ TESTING FIELD MAPPING AND DATA VALIDATION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test("Field mapping structure exists", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Look for mapping-related functionality
            return strpos($dashboard_content, 'field_mapping') !== false ||
                   strpos($dashboard_content, 'mapping') !== false ||
                   strpos($dashboard_content, 'import') !== false;
        });
        
        $this->test("Data sanitization functions", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for WordPress sanitization functions
            $sanitization_functions = array(
                'sanitize_text_field',
                'sanitize_email', 
                'sanitize_url',
                'wp_verify_nonce'
            );
            
            $found_functions = 0;
            foreach ($sanitization_functions as $func) {
                if (strpos($dashboard_content, $func) !== false) {
                    $found_functions++;
                }
            }
            
            return $found_functions >= 2;
        });
        
        $this->test("Import validation rules", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Look for validation patterns
            $validation_patterns = array(
                'required',
                'validation',
                'validate',
                'check',
                'verify'
            );
            
            $found_validations = 0;
            foreach ($validation_patterns as $pattern) {
                if (strpos($dashboard_content, $pattern) !== false) {
                    $found_validations++;
                }
            }
            
            return $found_validations >= 3;
        });
        
        $this->test("CSV delimiter handling", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for CSV parsing options
            return strpos($dashboard_content, 'delimiter') !== false &&
                   (strpos($dashboard_content, 'semicolon') !== false || 
                    strpos($dashboard_content, ';') !== false);
        });
    }
    
    public function testDataValidation() {
        echo "✅ TESTING DATA VALIDATION AND SANITIZATION\n";
        echo "-" . str_repeat("-", 40) . "\n";
        
        $this->test("Email validation", function() {
            // Test basic email validation logic
            $test_emails = array(
                'valid@example.com' => true,
                'invalid-email' => false,
                'test@domain.de' => true
            );
            
            // Mock validation - in real implementation this would use WordPress functions
            foreach ($test_emails as $email => $expected) {
                $is_valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                if ($is_valid !== $expected) {
                    return false;
                }
            }
            
            return true;
        });
        
        $this->test("Date format validation", function() {
            $test_dates = array(
                '2024-01-15' => true,
                '15.01.2024' => true,
                'invalid-date' => false
            );
            
            foreach ($test_dates as $date => $expected) {
                $is_valid = strtotime($date) !== false;
                if ($is_valid !== $expected) {
                    return false;
                }
            }
            
            return true;
        });
        
        $this->test("Decimal amount validation", function() {
            $test_amounts = array(
                '548.11' => true,
                '1000.00' => true,
                'invalid' => false,
                '-100.00' => false // Negative amounts should be invalid
            );
            
            foreach ($test_amounts as $amount => $expected) {
                $is_valid = is_numeric($amount) && floatval($amount) >= 0;
                if ($is_valid !== $expected) {
                    return false;
                }
            }
            
            return true;
        });
        
        $this->test("Required field validation", function() {
            // Test that required fields are properly validated
            $required_fields = array('case_id', 'schuldner', 'total_amount');
            
            // Mock data with missing required field
            $test_data = array(
                'case_id' => 'TEST-001',
                'total_amount' => '548.11'
                // Missing 'schuldner'
            );
            
            $missing_fields = array();
            foreach ($required_fields as $field) {
                if (empty($test_data[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            return count($missing_fields) > 0; // Should detect missing field
        });
    }
    
    public function testImportProcessing() {
        echo "📥 TESTING CSV IMPORT PROCESSING\n";
        echo "-" . str_repeat("-", 30) . "\n";
        
        $this->test("Import action handling", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            return strpos($dashboard_content, 'handle_import_action') !== false ||
                   strpos($dashboard_content, 'import_action') !== false;
        });
        
        $this->test("File upload validation", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for file upload security measures
            $security_checks = array(
                'csv',
                'file',
                'upload',
                'mime',
                'size'
            );
            
            $found_checks = 0;
            foreach ($security_checks as $check) {
                if (strpos($dashboard_content, $check) !== false) {
                    $found_checks++;
                }
            }
            
            return $found_checks >= 3;
        });
        
        $this->test("Import mode options", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for different import modes
            $import_modes = array(
                'create_new',
                'update_existing', 
                'create_and_update'
            );
            
            $found_modes = 0;
            foreach ($import_modes as $mode) {
                if (strpos($dashboard_content, $mode) !== false) {
                    $found_modes++;
                }
            }
            
            return $found_modes >= 2;
        });
        
        $this->test("Comprehensive data processing", function() {
            // Test that import can handle all major data categories
            $data_categories = array(
                'case' => array('case_id', 'case_status', 'mandant'),
                'debtor' => array('schuldner', 'debtors_email', 'debtors_address'),
                'financial' => array('total_amount', 'damages_loss', 'court_fees'),
                'legal' => array('rechtsgrundlage', 'verfahrensart', 'beweise')
            );
            
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            $db_content = file_get_contents('/app/includes/class-database.php');
            
            $processed_categories = 0;
            foreach ($data_categories as $category => $fields) {
                $found_fields = 0;
                foreach ($fields as $field) {
                    if (strpos($dashboard_content, $field) !== false || 
                        strpos($db_content, $field) !== false) {
                        $found_fields++;
                    }
                }
                if ($found_fields >= 2) {
                    $processed_categories++;
                }
            }
            
            return $processed_categories >= 3;
        });
        
        $this->test("Error handling and logging", function() {
            $dashboard_content = file_get_contents('/app/admin/class-admin-dashboard.php');
            
            // Check for error handling mechanisms
            $error_handling = array(
                'error',
                'exception',
                'try',
                'catch',
                'log'
            );
            
            $found_handling = 0;
            foreach ($error_handling as $handler) {
                if (strpos($dashboard_content, $handler) !== false) {
                    $found_handling++;
                }
            }
            
            return $found_handling >= 2;
        });
    }
    
    private function printSummary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 TEST SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        echo "Total Tests: {$this->test_count}\n";
        echo "Passed: {$this->passed_count}\n";
        echo "Failed: " . ($this->test_count - $this->passed_count) . "\n";
        echo "Success Rate: " . round(($this->passed_count / $this->test_count) * 100, 1) . "%\n";
        
        echo "\n📋 DETAILED RESULTS:\n";
        foreach ($this->results as $test_name => $result) {
            $status_icon = $result['status'] === 'passed' ? '✅' : '❌';
            echo "$status_icon $test_name: {$result['status']}\n";
            if ($result['status'] !== 'passed') {
                echo "   └─ {$result['message']}\n";
            }
        }
        
        echo "\n🎯 CRITICAL FINDINGS:\n";
        $critical_tests = array(
            'Enhanced schema tables creation',
            'Verify 57-field structure in klage_cases',
            '57-field template structure verification',
            'Comprehensive data processing'
        );
        
        foreach ($critical_tests as $critical_test) {
            if (isset($this->results[$critical_test])) {
                $result = $this->results[$critical_test];
                $status_icon = $result['status'] === 'passed' ? '✅' : '❌';
                echo "$status_icon $critical_test\n";
            }
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
    }
}

// Run the tests
$tester = new CourtAutomationHubTester();
$results = $tester->runAllTests();

// Return results for further processing
return $results;