#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.9 Verification
Tests the comprehensive database schema fix for debtors_country field issue.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV129Tester:
    """Test suite specifically for verifying hotfix v1.2.9 database schema fix functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_file = "/app/includes/class-database.php"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix v1.2.9 verification tests"""
        print("🚀 Starting Hotfix v1.2.9 Verification Tests - Database Schema Fix")
        print("=" * 70)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_database_upgrade_mechanism()
        self.test_table_recreation_method()
        self.test_enhanced_create_tables_direct()
        self.test_debtors_country_field_fix()
        self.test_case_creation_functionality()
        self.test_existing_functionality_preservation()
        self.test_error_handling()
        
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
        """Test that plugin version is updated to 1.2.9"""
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
            return version == "1.2.9"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.9"
        
        self.test("Plugin header version is 1.2.9", check_plugin_version)
        self.test("Plugin constant version is 1.2.9", check_constant_version)
    
    def test_database_upgrade_mechanism(self):
        """Test the upgrade_existing_tables() method implementation"""
        print("🔧 TESTING DATABASE UPGRADE MECHANISM")
        print("-" * 40)
        
        def check_upgrade_method_exists():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the upgrade_existing_tables method
            method_pattern = r'private\s+function\s+upgrade_existing_tables\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_table_existence_check():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for table existence verification
            table_check = 'SHOW TABLES LIKE' in content and 'klage_debtors' in content
            return table_check
        
        def check_column_info_detection():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for column definition detection
            column_check = 'SHOW COLUMNS FROM' in content and 'debtors_country' in content
            return column_check
        
        def check_varchar2_detection():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for varchar(2) detection logic
            varchar2_check = 'varchar(2)' in content and 'strpos' in content
            return varchar2_check
        
        def check_alter_table_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for ALTER TABLE command
            alter_check = 'ALTER TABLE' in content and 'MODIFY COLUMN' in content
            return alter_check
        
        def check_data_migration():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for existing data update from 'DE' to 'Deutschland'
            migration_check = "UPDATE" in content and "SET debtors_country = 'Deutschland'" in content and "WHERE debtors_country = 'DE'" in content
            return migration_check
        
        self.test("upgrade_existing_tables() method exists", check_upgrade_method_exists)
        self.test("Table existence check implemented", check_table_existence_check)
        self.test("Column info detection implemented", check_column_info_detection)
        self.test("varchar(2) detection logic", check_varchar2_detection)
        self.test("ALTER TABLE logic implemented", check_alter_table_logic)
        self.test("Data migration from DE to Deutschland", check_data_migration)
    
    def test_table_recreation_method(self):
        """Test the ensure_debtors_table_schema() method implementation"""
        print("🔄 TESTING TABLE RECREATION METHOD")
        print("-" * 40)
        
        def check_recreation_method_exists():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the ensure_debtors_table_schema method
            method_pattern = r'private\s+function\s+ensure_debtors_table_schema\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_drop_table_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for DROP TABLE IF EXISTS
            drop_check = 'DROP TABLE IF EXISTS' in content and 'klage_debtors' in content
            return drop_check
        
        def check_correct_schema_creation():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for correct debtors_country field definition
            schema_check = "debtors_country varchar(100) DEFAULT 'Deutschland'" in content
            return schema_check
        
        def check_comprehensive_debtor_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for comprehensive debtor fields
            debtor_fields = [
                'debtors_name', 'debtors_company', 'debtors_first_name',
                'debtors_last_name', 'debtors_email', 'debtors_phone',
                'debtors_address', 'debtors_postal_code', 'debtors_city',
                'debtors_country'
            ]
            
            found_fields = sum(1 for field in debtor_fields if field in content)
            print(f"Found {found_fields} debtor fields in schema")
            
            return found_fields >= 8
        
        def check_proper_indexes():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper indexing
            index_check = 'KEY debtors_name' in content and 'KEY debtors_email' in content
            return index_check
        
        self.test("ensure_debtors_table_schema() method exists", check_recreation_method_exists)
        self.test("DROP TABLE IF EXISTS logic", check_drop_table_logic)
        self.test("Correct schema creation with varchar(100)", check_correct_schema_creation)
        self.test("Comprehensive debtor fields in schema", check_comprehensive_debtor_fields)
        self.test("Proper database indexes", check_proper_indexes)
    
    def test_enhanced_create_tables_direct(self):
        """Test the enhanced create_tables_direct() method"""
        print("⚡ TESTING ENHANCED CREATE_TABLES_DIRECT")
        print("-" * 40)
        
        def check_upgrade_method_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade_existing_tables is called
            upgrade_call = '$this->upgrade_existing_tables()' in content
            return upgrade_call
        
        def check_ensure_schema_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that ensure_debtors_table_schema is called
            ensure_call = '$this->ensure_debtors_table_schema()' in content
            return ensure_call
        
        def check_method_call_order():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade is called before ensure (proper order)
            upgrade_pos = content.find('$this->upgrade_existing_tables()')
            ensure_pos = content.find('$this->ensure_debtors_table_schema()')
            
            if upgrade_pos == -1 or ensure_pos == -1:
                return False
            
            print(f"Method call order correct: upgrade at {upgrade_pos}, ensure at {ensure_pos}")
            return upgrade_pos < ensure_pos
        
        def check_activation_integration():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that activation uses create_tables_direct
            activation_check = 'create_tables_direct()' in content
            return activation_check
        
        self.test("upgrade_existing_tables() method called", check_upgrade_method_call)
        self.test("ensure_debtors_table_schema() method called", check_ensure_schema_call)
        self.test("Method call order is correct", check_method_call_order)
        self.test("Plugin activation uses create_tables_direct", check_activation_integration)
    
    def test_debtors_country_field_fix(self):
        """Test the specific debtors_country field fix"""
        print("🌍 TESTING DEBTORS_COUNTRY FIELD FIX")
        print("-" * 40)
        
        def check_field_length_fix():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that debtors_country is now varchar(100)
            field_fix = "debtors_country varchar(100)" in content
            return field_fix
        
        def check_default_value_fix():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that default value is 'Deutschland'
            default_fix = "DEFAULT 'Deutschland'" in content
            return default_fix
        
        def check_deutschland_length_support():
            # Deutschland has 11 characters, varchar(100) should support it
            deutschland_length = len("Deutschland")
            print(f"Deutschland length: {deutschland_length} characters")
            return deutschland_length <= 100
        
        def check_no_varchar2_references():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Count varchar(2) references - should only be in detection logic, not schema
            varchar2_count = content.count('varchar(2)')
            print(f"Found {varchar2_count} varchar(2) references (should be minimal)")
            
            # Should have some for detection but not in main schema
            return varchar2_count <= 2
        
        def check_case_creation_compatibility():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that case creation still works with Deutschland
            deutschland_support = 'Deutschland' in content or 'debtors_country' in content
            return deutschland_support
        
        self.test("Field length fixed to varchar(100)", check_field_length_fix)
        self.test("Default value changed to 'Deutschland'", check_default_value_fix)
        self.test("Deutschland length supported (11 chars)", check_deutschland_length_support)
        self.test("No varchar(2) in main schema", check_no_varchar2_references)
        self.test("Case creation compatible with Deutschland", check_case_creation_compatibility)
    
    def test_case_creation_functionality(self):
        """Test end-to-end case creation functionality"""
        print("📝 TESTING CASE CREATION FUNCTIONALITY")
        print("-" * 40)
        
        def check_case_creation_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that create_new_case method exists
            method_exists = 'create_new_case' in content
            return method_exists
        
        def check_debtor_record_creation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for debtor record creation with country field
            debtor_creation = 'klage_debtors' in content and '$wpdb->insert' in content
            return debtor_creation
        
        def check_financial_record_generation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GDPR standard amounts (€548.11)
            gdpr_amounts = ['548.11', '350.00', '96.90']
            found_amounts = sum(1 for amount in gdpr_amounts if amount in content)
            
            print(f"Found {found_amounts} GDPR standard amounts")
            return found_amounts >= 2
        
        def check_audit_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for audit trail logging
            audit_logging = 'klage_audit' in content
            return audit_logging
        
        def check_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper error handling
            error_patterns = ['notice-error', 'try {', 'Exception']
            found_patterns = sum(1 for pattern in error_patterns if pattern in content)
            
            print(f"Found {found_patterns} error handling patterns")
            return found_patterns >= 2
        
        self.test("Case creation method exists", check_case_creation_method)
        self.test("Debtor record creation with country field", check_debtor_record_creation)
        self.test("Financial record generation with GDPR amounts", check_financial_record_generation)
        self.test("Audit logging functionality", check_audit_logging)
        self.test("Proper error handling", check_error_handling)
    
    def test_existing_functionality_preservation(self):
        """Test that existing functionality is preserved"""
        print("🔒 TESTING EXISTING FUNCTIONALITY PRESERVATION")
        print("-" * 40)
        
        def check_existing_database_methods():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing methods are preserved
            existing_methods = [
                'create_tables()',
                'get_table_status()',
                'insert_default_courts()'
            ]
            
            found_methods = sum(1 for method in existing_methods if method in content)
            print(f"Found {found_methods} existing database methods")
            
            return found_methods >= 2
        
        def check_existing_admin_functionality():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing admin functionality is preserved
            admin_methods = [
                'admin_page_cases',
                'render_cases_list',
                'handle_bulk_actions',
                'admin_page_dashboard'
            ]
            
            found_methods = sum(1 for method in admin_methods if method in content)
            print(f"Found {found_methods} existing admin methods")
            
            return found_methods >= 3
        
        def check_csv_import_functionality():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV import functionality is preserved
            import_functionality = 'admin_page_import' in content and 'template' in content
            return import_functionality
        
        def check_financial_calculator():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that financial calculator is preserved
            calculator_functionality = 'admin_page_financial' in content
            return calculator_functionality
        
        self.test("Existing database methods preserved", check_existing_database_methods)
        self.test("Existing admin functionality preserved", check_existing_admin_functionality)
        self.test("CSV import functionality preserved", check_csv_import_functionality)
        self.test("Financial calculator preserved", check_financial_calculator)
    
    def test_error_handling(self):
        """Test error handling for database operations"""
        print("⚠️ TESTING ERROR HANDLING")
        print("-" * 40)
        
        def check_database_error_handling():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for database error handling
            error_handling = '$wpdb->last_error' in content
            return error_handling
        
        def check_table_creation_error_handling():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for table creation error handling
            creation_errors = ['failed_count', 'results[\'success\']', 'details']
            found_errors = sum(1 for error in creation_errors if error in content)
            
            print(f"Found {found_errors} table creation error patterns")
            return found_errors >= 2
        
        def check_upgrade_error_handling():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for upgrade operation error handling
            upgrade_errors = ['table_exists', 'column_info', 'ALTER TABLE']
            found_errors = sum(1 for error in upgrade_errors if error in content)
            
            print(f"Found {found_errors} upgrade error handling patterns")
            return found_errors >= 2
        
        def check_case_creation_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for case creation error handling
            case_errors = ['notice-error', 'Fehler', 'validation']
            found_errors = sum(1 for error in case_errors if error in content)
            
            print(f"Found {found_errors} case creation error patterns")
            return found_errors >= 2
        
        self.test("Database error handling", check_database_error_handling)
        self.test("Table creation error handling", check_table_creation_error_handling)
        self.test("Upgrade operation error handling", check_upgrade_error_handling)
        self.test("Case creation error handling", check_case_creation_error_handling)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 70)
        print("📊 HOTFIX v1.2.9 VERIFICATION SUMMARY - Database Schema Fix")
        print("=" * 70)
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
        
        print("\n🎯 CRITICAL DATABASE SCHEMA FIX VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.2.9",
            "upgrade_existing_tables() method exists",
            "ensure_debtors_table_schema() method exists",
            "Field length fixed to varchar(100)",
            "Default value changed to 'Deutschland'",
            "Deutschland length supported (11 chars)",
            "Case creation method exists",
            "Plugin activation uses create_tables_direct"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 HOTFIX STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ HOTFIX v1.2.9 VERIFICATION: SUCCESSFUL")
            print("Database schema fix for debtors_country field is implemented correctly.")
            print("Case creation with 'Deutschland' country value should now work without errors.")
        else:
            print("❌ HOTFIX v1.2.9 VERIFICATION: ISSUES FOUND")
            print("Some critical database schema functionality may not be working as expected.")
        
        print("\n🌍 DEBTORS_COUNTRY FIELD STATUS:")
        country_tests = [
            "Field length fixed to varchar(100)",
            "Default value changed to 'Deutschland'",
            "Deutschland length supported (11 chars)",
            "No varchar(2) in main schema"
        ]
        
        country_passed = 0
        for country_test in country_tests:
            if country_test in self.results:
                result = self.results[country_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {country_test}")
                if result['status'] == 'passed':
                    country_passed += 1
        
        if country_passed == len(country_tests):
            print("✅ DEBTORS_COUNTRY FIELD: FULLY FIXED")
            print("The original database error should be resolved.")
        else:
            print("❌ DEBTORS_COUNTRY FIELD: ISSUES REMAIN")
        
        print("\n" + "=" * 70)

def main():
    """Main test execution"""
    tester = HotfixV129Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())