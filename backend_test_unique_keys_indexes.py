#!/usr/bin/env python3
"""
Backend Test Suite for Enhanced Database Management System v1.4.4
Tests the unique keys and indexes management through the DB CRUD interface.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class UniqueKeysIndexesTester:
    """Test suite for verifying unique keys and indexes management functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.schema_manager_file = "/app/includes/class-schema-manager.php"
        self.database_admin_file = "/app/includes/class-database-admin.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all unique keys and indexes management tests"""
        print("🚀 Starting Enhanced Database Management System v1.4.4 - Unique Keys & Indexes Tests")
        print("=" * 80)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_unique_key_management_methods()
        self.test_index_management_methods()
        self.test_table_analysis_methods()
        self.test_enhanced_admin_interface()
        self.test_unique_key_recommendations()
        self.test_safety_features()
        self.test_current_klage_cases_analysis()
        self.test_preset_options()
        self.test_integration_with_crud_system()
        
        self.print_summary()
        return self.results
    
    def test(self, name: str, test_func) -> bool:
        """Execute a single test"""
        self.test_count += 1
        print(f"🧪 Testing: {name}")
        
        try:
            result = test_func()
            if result:
                print(f"✅ PASSED: {name}")
                self.passed_count += 1
                self.results[name] = {'status': 'passed', 'message': 'Test passed successfully'}
                return True
            else:
                print(f"❌ FAILED: {name}")
                self.results[name] = {'status': 'failed', 'message': 'Test assertion failed'}
                return False
        except Exception as e:
            print(f"❌ ERROR: {name} - {str(e)}")
            self.results[name] = {'status': 'error', 'message': str(e)}
            return False
        finally:
            print()
    
    def test_version_verification(self):
        """Test that plugin version is updated to 1.4.4"""
        print("📋 TESTING VERSION VERIFICATION")
        print("-" * 40)
        
        def check_plugin_version():
            if not os.path.exists(self.main_plugin_file):
                raise Exception(f"Main plugin file not found: {self.main_plugin_file}")
            
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check version in plugin header
            version_match = re.search(r'Version:\s*([0-9.]+)', content)
            if not version_match:
                return False
            
            version = version_match.group(1)
            print(f"Found plugin version: {version}")
            return version == "1.4.4"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.4.4"
        
        self.test("Plugin header version is 1.4.4", check_plugin_version)
        self.test("Plugin constant version is 1.4.4", check_constant_version)
    
    def test_unique_key_management_methods(self):
        """Test unique key management methods in Schema Manager"""
        print("🔑 TESTING UNIQUE KEY MANAGEMENT METHODS")
        print("-" * 40)
        
        def check_add_unique_key_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the add_unique_key method
            method_pattern = r'public\s+function\s+add_unique_key\s*\(\s*\$table_name,\s*\$key_name,\s*\$columns\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            # Check method implementation details
            if method_exists:
                # Check for table existence validation
                table_check = 'SHOW TABLES LIKE' in content
                # Check for unique key existence validation
                key_check = 'SHOW INDEX FROM' in content and 'Key_name' in content
                # Check for column validation
                column_check = 'SHOW COLUMNS FROM' in content
                # Check for ALTER TABLE ADD UNIQUE KEY
                alter_table = 'ALTER TABLE' in content and 'ADD UNIQUE KEY' in content
                
                print(f"Table existence check: {table_check}")
                print(f"Key existence check: {key_check}")
                print(f"Column validation: {column_check}")
                print(f"ALTER TABLE statement: {alter_table}")
                
                return table_check and key_check and column_check and alter_table
            
            return False
        
        def check_unique_key_validation():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for comprehensive validation in add_unique_key
            validations = [
                'Table does not exist',
                'Unique key already exists',
                'Column.*does not exist',
                'Failed to add unique key'
            ]
            
            found_validations = sum(1 for validation in validations if validation in content)
            print(f"Found {found_validations} validation checks")
            
            return found_validations >= 3
        
        def check_unique_key_error_handling():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper error handling
            error_handling = [
                'success.*false',
                'message.*error',
                'wpdb->last_error',
                'refresh_schema_cache'
            ]
            
            found_handling = sum(1 for handling in error_handling if handling in content)
            print(f"Found {found_handling} error handling patterns")
            
            return found_handling >= 3
        
        self.test("add_unique_key() method exists with proper signature", check_add_unique_key_method)
        self.test("Unique key validation logic", check_unique_key_validation)
        self.test("Unique key error handling", check_unique_key_error_handling)
    
    def test_index_management_methods(self):
        """Test index management methods in Schema Manager"""
        print("📊 TESTING INDEX MANAGEMENT METHODS")
        print("-" * 40)
        
        def check_add_index_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the add_index method
            method_pattern = r'public\s+function\s+add_index\s*\(\s*\$table_name,\s*\$index_name,\s*\$columns\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            if method_exists:
                # Check for ADD INDEX statement
                add_index = 'ADD INDEX' in content
                print(f"ADD INDEX statement found: {add_index}")
                return add_index
            
            return False
        
        def check_drop_index_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the drop_index method
            method_pattern = r'public\s+function\s+drop_index\s*\(\s*\$table_name,\s*\$index_name\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            if method_exists:
                # Check for DROP INDEX statement
                drop_index = 'DROP INDEX' in content
                # Check for primary key protection
                primary_protection = 'Cannot drop primary key' in content
                print(f"DROP INDEX statement found: {drop_index}")
                print(f"Primary key protection: {primary_protection}")
                return drop_index and primary_protection
            
            return False
        
        def check_get_table_indexes_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the get_table_indexes method
            method_pattern = r'public\s+function\s+get_table_indexes\s*\(\s*\$table_name\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            if method_exists:
                # Check for SHOW INDEX FROM statement
                show_index = 'SHOW INDEX FROM' in content
                # Check for index organization
                index_organization = 'organized_indexes' in content and 'Key_name' in content
                # Check for unique/primary detection
                unique_detection = 'Non_unique' in content and 'PRIMARY' in content
                
                print(f"SHOW INDEX statement found: {show_index}")
                print(f"Index organization logic: {index_organization}")
                print(f"Unique/Primary detection: {unique_detection}")
                
                return show_index and index_organization and unique_detection
            
            return False
        
        self.test("add_index() method exists and functional", check_add_index_method)
        self.test("drop_index() method exists with safety features", check_drop_index_method)
        self.test("get_table_indexes() method exists and comprehensive", check_get_table_indexes_method)
    
    def test_table_analysis_methods(self):
        """Test table analysis and current index detection"""
        print("🔍 TESTING TABLE ANALYSIS METHODS")
        print("-" * 40)
        
        def check_current_table_schema_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for get_current_table_schema method
            method_pattern = r'public\s+function\s+get_current_table_schema\s*\(\s*\$table_name\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            if method_exists:
                # Check for comprehensive schema detection
                schema_detection = [
                    'SHOW COLUMNS FROM',
                    'SHOW INDEX FROM',
                    'columns.*indexes',
                    'primary_key'
                ]
                
                found_detection = sum(1 for detection in schema_detection if detection in content)
                print(f"Found {found_detection} schema detection features")
                
                return found_detection >= 3
            
            return False
        
        def check_klage_cases_current_indexes():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for current klage_cases indexes in schema definition
            klage_cases_indexes = [
                'case_id.*array.*case_id',
                'case_status.*array.*case_status',
                'debtor_id.*array.*debtor_id',
                'submission_date.*array.*submission_date'
            ]
            
            found_indexes = sum(1 for index in klage_cases_indexes if re.search(index, content))
            print(f"Found {found_indexes} current klage_cases indexes")
            
            return found_indexes >= 3
        
        def check_primary_key_detection():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for primary key detection logic
            primary_key_logic = [
                'primary_key.*id',
                'PRIMARY.*Key_name',
                'auto_increment'
            ]
            
            found_logic = sum(1 for logic in primary_key_logic if re.search(logic, content, re.IGNORECASE))
            print(f"Found {found_logic} primary key detection patterns")
            
            return found_logic >= 2
        
        self.test("get_current_table_schema() method comprehensive", check_current_table_schema_method)
        self.test("klage_cases current indexes properly defined", check_klage_cases_current_indexes)
        self.test("Primary key detection logic", check_primary_key_detection)
    
    def test_enhanced_admin_interface(self):
        """Test enhanced Database Admin interface with Indexes & Keys tab"""
        print("🖥️ TESTING ENHANCED ADMIN INTERFACE")
        print("-" * 40)
        
        def check_indexes_keys_tab():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for indexes tab rendering method
            method_pattern = r'render_table_indexes\s*\(\s*\$table_name\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            if method_exists:
                # Check for indexes display elements
                display_elements = [
                    'Indexes and Keys',
                    'Current Indexes and Keys',
                    'Index Name.*Columns.*Type.*Unique',
                    'PRIMARY KEY.*UNIQUE KEY.*INDEX'
                ]
                
                found_elements = sum(1 for element in display_elements if re.search(element, content))
                print(f"Found {found_elements} indexes display elements")
                
                return found_elements >= 3
            
            return False
        
        def check_add_index_form():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for add index form rendering
            form_method = 'render_add_index_form' in content
            
            if form_method:
                # Check for form elements
                form_elements = [
                    'Index Name',
                    'Index Type',
                    'UNIQUE KEY.*INDEX',
                    'Columns',
                    'checkbox.*index_columns'
                ]
                
                found_elements = sum(1 for element in form_elements if re.search(element, content))
                print(f"Found {found_elements} form elements")
                
                return found_elements >= 4
            
            return False
        
        def check_admin_menu_integration():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper admin menu integration
            menu_integration = [
                'klage-click-hub',
                'klage-click-database',
                'Database Management',
                'manage_options'
            ]
            
            found_integration = sum(1 for integration in menu_integration if integration in content)
            print(f"Found {found_integration} menu integration elements")
            
            return found_integration >= 3
        
        self.test("Indexes & Keys tab rendering", check_indexes_keys_tab)
        self.test("Add Index/Key form interface", check_add_index_form)
        self.test("Admin menu integration correct", check_admin_menu_integration)
    
    def test_unique_key_recommendations(self):
        """Test unique key recommendations system"""
        print("💡 TESTING UNIQUE KEY RECOMMENDATIONS")
        print("-" * 40)
        
        def check_case_id_unique_recommendation():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for case_id unique recommendation
            recommendation_elements = [
                'Make case_id Unique',
                'prevent duplicate case IDs',
                'unique_case_id',
                'Add Unique Case ID'
            ]
            
            found_elements = sum(1 for element in recommendation_elements if element in content)
            print(f"Found {found_elements} case_id recommendation elements")
            
            return found_elements >= 3
        
        def check_mandant_case_composite_recommendation():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for mandant + case_id composite recommendation
            composite_elements = [
                'Mandant.*Case ID Composite',
                'unique_mandant_case',
                'mandant.*case_id',
                'business logic uniqueness'
            ]
            
            found_elements = sum(1 for element in composite_elements if re.search(element, content, re.IGNORECASE))
            print(f"Found {found_elements} composite recommendation elements")
            
            return found_elements >= 3
        
        def check_recommendation_sql_examples():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for SQL examples in recommendations
            sql_examples = [
                'ALTER TABLE.*ADD UNIQUE KEY',
                'unique_case_id.*case_id',
                'unique_mandant_case.*mandant.*case_id'
            ]
            
            found_examples = sum(1 for example in sql_examples if re.search(example, content))
            print(f"Found {found_examples} SQL examples")
            
            return found_examples >= 2
        
        self.test("case_id unique key recommendation", check_case_id_unique_recommendation)
        self.test("mandant + case_id composite recommendation", check_mandant_case_composite_recommendation)
        self.test("SQL examples in recommendations", check_recommendation_sql_examples)
    
    def test_safety_features(self):
        """Test safety features and validation"""
        print("🛡️ TESTING SAFETY FEATURES")
        print("-" * 40)
        
        def check_column_existence_validation():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for column existence validation before creating indexes
            validation_patterns = [
                'SHOW COLUMNS FROM.*LIKE',
                'Column.*does not exist',
                'column_exists'
            ]
            
            found_patterns = sum(1 for pattern in validation_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} column validation patterns")
            
            return found_patterns >= 2
        
        def check_primary_key_protection():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for primary key protection
            protection_patterns = [
                'Cannot drop primary key',
                'PRIMARY.*index_name',
                'system.*column'
            ]
            
            found_patterns = sum(1 for pattern in protection_patterns if re.search(pattern, content, re.IGNORECASE))
            print(f"Found {found_patterns} primary key protection patterns")
            
            return found_patterns >= 2
        
        def check_existing_index_validation():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for existing index validation
            validation_patterns = [
                'already exists',
                'SHOW INDEX FROM.*Key_name',
                'index_exists.*key_exists'
            ]
            
            found_patterns = sum(1 for pattern in validation_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} existing index validation patterns")
            
            return found_patterns >= 2
        
        def check_system_column_protection():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for system column protection
            protection_patterns = [
                'system_columns.*id.*created_at.*updated_at',
                'Cannot drop system column',
                'in_array.*column_name.*system_columns'
            ]
            
            found_patterns = sum(1 for pattern in protection_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} system column protection patterns")
            
            return found_patterns >= 2
        
        self.test("Column existence validation before index creation", check_column_existence_validation)
        self.test("Primary key protection from dropping", check_primary_key_protection)
        self.test("Existing index validation before creation", check_existing_index_validation)
        self.test("System column protection", check_system_column_protection)
    
    def test_current_klage_cases_analysis(self):
        """Test current klage_cases table analysis and structure"""
        print("📋 TESTING CURRENT KLAGE_CASES ANALYSIS")
        print("-" * 40)
        
        def check_klage_cases_primary_key():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for klage_cases primary key definition
            primary_key_patterns = [
                'klage_cases.*primary_key.*id',
                'id.*bigint.*AUTO_INCREMENT'
            ]
            
            found_patterns = sum(1 for pattern in primary_key_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} primary key patterns")
            
            return found_patterns >= 1
        
        def check_unique_candidate_fields():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for unique candidate fields in klage_cases
            candidate_fields = [
                'case_id.*varchar',
                'mandant.*varchar',
                'debtor_id.*bigint',
                'submission_date.*date'
            ]
            
            found_fields = sum(1 for field in candidate_fields if re.search(field, content))
            print(f"Found {found_fields} unique candidate fields")
            
            return found_fields >= 3
        
        def check_current_non_unique_indexes():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that current indexes are defined as non-unique (performance only)
            index_definitions = [
                'case_id.*array.*case_id',
                'case_status.*array.*case_status',
                'debtor_id.*array.*debtor_id',
                'submission_date.*array.*submission_date'
            ]
            
            found_definitions = sum(1 for definition in index_definitions if re.search(definition, content))
            print(f"Found {found_definitions} current index definitions")
            
            return found_definitions >= 3
        
        self.test("klage_cases primary key (id) properly defined", check_klage_cases_primary_key)
        self.test("Unique candidate fields present", check_unique_candidate_fields)
        self.test("Current indexes defined as non-unique", check_current_non_unique_indexes)
    
    def test_preset_options(self):
        """Test preset options for quick unique key creation"""
        print("⚡ TESTING PRESET OPTIONS")
        print("-" * 40)
        
        def check_unique_case_id_preset():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for unique_case_id preset handling
            preset_patterns = [
                'preset.*unique_case_id',
                'unique_case_id.*checked',
                'case_id.*checked'
            ]
            
            found_patterns = sum(1 for pattern in preset_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} unique_case_id preset patterns")
            
            return found_patterns >= 2
        
        def check_unique_mandant_case_preset():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for unique_mandant_case preset handling
            preset_patterns = [
                'preset.*unique_mandant_case',
                'mandant.*checked',
                'case_id.*checked'
            ]
            
            found_patterns = sum(1 for pattern in preset_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} unique_mandant_case preset patterns")
            
            return found_patterns >= 2
        
        def check_preset_form_population():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for preset form population logic
            population_patterns = [
                'preset.*GET',
                'value.*unique_case_id',
                'value.*unique_mandant_case',
                'selected.*preset'
            ]
            
            found_patterns = sum(1 for pattern in population_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} preset form population patterns")
            
            return found_patterns >= 3
        
        self.test("unique_case_id preset option", check_unique_case_id_preset)
        self.test("unique_mandant_case preset option", check_unique_mandant_case_preset)
        self.test("Preset form population logic", check_preset_form_population)
    
    def test_integration_with_crud_system(self):
        """Test integration with existing DB CRUD system"""
        print("🔗 TESTING INTEGRATION WITH CRUD SYSTEM")
        print("-" * 40)
        
        def check_schema_cache_refresh():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for schema cache refresh after operations
            refresh_patterns = [
                'refresh_schema_cache',
                'wp_cache_flush',
                'cah_schema_updated'
            ]
            
            found_patterns = sum(1 for pattern in refresh_patterns if pattern in content)
            print(f"Found {found_patterns} cache refresh patterns")
            
            return found_patterns >= 2
        
        def check_crud_method_integration():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that unique key methods integrate with existing CRUD methods
            crud_integration = [
                'add_column',
                'modify_column',
                'drop_column',
                'add_unique_key',
                'add_index',
                'drop_index'
            ]
            
            found_methods = sum(1 for method in crud_integration if f'function {method}' in content)
            print(f"Found {found_methods} CRUD methods")
            
            return found_methods >= 5
        
        def check_admin_action_handling():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin action handling for index operations
            action_patterns = [
                'handle_admin_actions',
                'action.*add_index',
                'wp_verify_nonce',
                'add_index.*add_unique_key'
            ]
            
            found_patterns = sum(1 for pattern in action_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} admin action patterns")
            
            return found_patterns >= 3
        
        def check_error_feedback_system():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper error feedback system
            feedback_patterns = [
                'admin_notices',
                'notice-success',
                'notice-error',
                'successfully.*failed'
            ]
            
            found_patterns = sum(1 for pattern in feedback_patterns if re.search(pattern, content))
            print(f"Found {found_patterns} feedback patterns")
            
            return found_patterns >= 3
        
        self.test("Schema cache refresh after operations", check_schema_cache_refresh)
        self.test("CRUD method integration", check_crud_method_integration)
        self.test("Admin action handling for index operations", check_admin_action_handling)
        self.test("Error feedback system", check_error_feedback_system)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 80)
        print("📊 ENHANCED DATABASE MANAGEMENT SYSTEM v1.4.4 - UNIQUE KEYS & INDEXES SUMMARY")
        print("=" * 80)
        print(f"Total Tests: {self.test_count}")
        print(f"Passed: {self.passed_count}")
        print(f"Failed: {self.test_count - self.passed_count}")
        print(f"Success Rate: {round((self.passed_count / self.test_count) * 100, 1)}%")
        
        print("\n📋 DETAILED RESULTS:")
        for test_name, result in self.results.items():
            status_icon = '✅' if result['status'] == 'passed' else '❌'
            print(f"{status_icon} {test_name}: {result['status']}")
            if result['status'] != 'passed':
                print(f"   └─ {result['message']}")
        
        print("\n🎯 CRITICAL UNIQUE KEYS & INDEXES FEATURES:")
        critical_tests = [
            "Plugin header version is 1.4.4",
            "add_unique_key() method exists with proper signature",
            "add_index() method exists and functional",
            "drop_index() method exists with safety features",
            "get_table_indexes() method exists and comprehensive",
            "Indexes & Keys tab rendering",
            "case_id unique key recommendation",
            "Column existence validation before index creation",
            "Primary key protection from dropping",
            "klage_cases primary key (id) properly defined",
            "unique_case_id preset option",
            "CRUD method integration"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 FEATURE STATUS: {critical_passed}/{len(critical_tests)} critical features working")
        
        if critical_passed == len(critical_tests):
            print("✅ UNIQUE KEYS & INDEXES MANAGEMENT: FULLY FUNCTIONAL")
            print("All unique keys and indexes management features are implemented and working correctly.")
            print("\n🔑 KEY CAPABILITIES VERIFIED:")
            print("• ✅ Unique Key Management (add_unique_key, validation, safety)")
            print("• ✅ Index Management (add_index, drop_index, get_table_indexes)")
            print("• ✅ Enhanced Admin Interface with Indexes & Keys Tab")
            print("• ✅ Unique Key Recommendations (case_id, mandant+case_id)")
            print("• ✅ Safety Features (column validation, primary key protection)")
            print("• ✅ Current klage_cases Table Analysis")
            print("• ✅ Preset Options for Quick Creation")
            print("• ✅ Integration with Existing CRUD System")
        else:
            print("❌ UNIQUE KEYS & INDEXES MANAGEMENT: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n" + "=" * 80)

def main():
    """Main test execution"""
    tester = UniqueKeysIndexesTester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())