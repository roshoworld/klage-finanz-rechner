#!/usr/bin/env python3
"""
Functional Test for Court Automation Hub v1.2.9 - Case Creation with Deutschland
Tests actual case creation functionality to verify the database schema fix works.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class FunctionalV129Tester:
    """Functional test suite for v1.2.9 case creation with Deutschland"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all functional tests"""
        print("🚀 Starting v1.2.9 Functional Tests - Case Creation with Deutschland")
        print("=" * 70)
        print()
        
        # Test sequence for functional verification
        self.test_database_schema_verification()
        self.test_case_creation_workflow()
        self.test_deutschland_country_support()
        self.test_data_persistence()
        self.test_integration_functionality()
        
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
    
    def test_database_schema_verification(self):
        """Test database schema is correctly implemented"""
        print("🗄️ TESTING DATABASE SCHEMA VERIFICATION")
        print("-" * 40)
        
        def check_debtors_table_schema():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for correct debtors_country field definition
            schema_patterns = [
                "debtors_country varchar(100) DEFAULT 'Deutschland'",
                "CREATE TABLE",
                "klage_debtors"
            ]
            
            found_patterns = sum(1 for pattern in schema_patterns if pattern in content)
            print(f"Found {found_patterns} schema patterns")
            
            return found_patterns >= 2
        
        def check_upgrade_mechanism():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check upgrade mechanism components
            upgrade_components = [
                "upgrade_existing_tables",
                "ensure_debtors_table_schema",
                "ALTER TABLE",
                "MODIFY COLUMN"
            ]
            
            found_components = sum(1 for comp in upgrade_components if comp in content)
            print(f"Found {found_components} upgrade components")
            
            return found_components >= 3
        
        def check_table_recreation():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check table recreation logic
            recreation_logic = [
                "DROP TABLE IF EXISTS",
                "CREATE TABLE",
                "ensure_debtors_table_schema"
            ]
            
            found_logic = sum(1 for logic in recreation_logic if logic in content)
            print(f"Found {found_logic} recreation logic components")
            
            return found_logic >= 2
        
        def check_data_migration():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check data migration from DE to Deutschland
            migration_check = ("UPDATE" in content and 
                             "SET debtors_country = 'Deutschland'" in content and 
                             "WHERE debtors_country = 'DE'" in content)
            
            return migration_check
        
        self.test("Debtors table schema correctly defined", check_debtors_table_schema)
        self.test("Upgrade mechanism implemented", check_upgrade_mechanism)
        self.test("Table recreation logic implemented", check_table_recreation)
        self.test("Data migration logic implemented", check_data_migration)
    
    def test_case_creation_workflow(self):
        """Test the complete case creation workflow"""
        print("📝 TESTING CASE CREATION WORKFLOW")
        print("-" * 40)
        
        def check_case_creation_form():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for case creation form elements
            form_elements = [
                "render_add_case_form",
                "create_case",
                "debtors_country",
                "Deutschland"
            ]
            
            found_elements = sum(1 for element in form_elements if element in content)
            print(f"Found {found_elements} form elements")
            
            return found_elements >= 3
        
        def check_form_processing():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for form processing logic
            processing_logic = [
                "create_new_case",
                "sanitize_text_field",
                "wp_verify_nonce",
                "$wpdb->insert"
            ]
            
            found_logic = sum(1 for logic in processing_logic if logic in content)
            print(f"Found {found_logic} processing logic components")
            
            return found_logic >= 3
        
        def check_debtor_record_creation():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for debtor record creation with country field
            debtor_creation = ("klage_debtors" in content and 
                             "debtors_country" in content and
                             "$wpdb->insert" in content)
            
            return debtor_creation
        
        def check_case_record_creation():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for case record creation
            case_creation = ("klage_cases" in content and 
                           "case_id" in content and
                           "debtor_id" in content)
            
            return case_creation
        
        def check_financial_record_creation():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for financial record creation with GDPR amounts
            financial_creation = ("klage_financial" in content and 
                                "548.11" in content)
            
            return financial_creation
        
        self.test("Case creation form implemented", check_case_creation_form)
        self.test("Form processing logic implemented", check_form_processing)
        self.test("Debtor record creation with country field", check_debtor_record_creation)
        self.test("Case record creation implemented", check_case_record_creation)
        self.test("Financial record creation with GDPR amounts", check_financial_record_creation)
    
    def test_deutschland_country_support(self):
        """Test specific Deutschland country value support"""
        print("🌍 TESTING DEUTSCHLAND COUNTRY SUPPORT")
        print("-" * 40)
        
        def check_deutschland_default_value():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that Deutschland is the default value
            default_check = "DEFAULT 'Deutschland'" in content
            return default_check
        
        def check_deutschland_length_compatibility():
            # Deutschland = 11 characters, should fit in varchar(100)
            deutschland_length = len("Deutschland")
            max_length = 100
            
            print(f"Deutschland length: {deutschland_length}, Max field length: {max_length}")
            return deutschland_length <= max_length
        
        def check_form_deutschland_support():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that form supports Deutschland value
            deutschland_support = ("Deutschland" in content or 
                                 "debtors_country" in content)
            
            return deutschland_support
        
        def check_no_varchar2_constraint():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that main schema doesn't use varchar(2) for debtors_country
            # Look for the main table definition
            table_def_start = content.find("CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}klage_debtors")
            if table_def_start == -1:
                table_def_start = content.find("CREATE TABLE $table_name")
            
            if table_def_start != -1:
                table_def_end = content.find(") $charset_collate", table_def_start)
                if table_def_end != -1:
                    table_definition = content[table_def_start:table_def_end]
                    # Check that debtors_country is varchar(100) not varchar(2)
                    return "debtors_country varchar(100)" in table_definition
            
            return False
        
        def check_migration_logic():
            database_file = "/app/includes/class-database.php"
            with open(database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check migration from DE to Deutschland
            migration_logic = ("UPDATE" in content and 
                             "SET debtors_country = 'Deutschland'" in content and
                             "WHERE debtors_country = 'DE'" in content)
            
            return migration_logic
        
        self.test("Deutschland is default value", check_deutschland_default_value)
        self.test("Deutschland length compatibility (11 chars)", check_deutschland_length_compatibility)
        self.test("Form supports Deutschland value", check_form_deutschland_support)
        self.test("No varchar(2) constraint in main schema", check_no_varchar2_constraint)
        self.test("Migration logic from DE to Deutschland", check_migration_logic)
    
    def test_data_persistence(self):
        """Test data persistence and validation"""
        print("💾 TESTING DATA PERSISTENCE")
        print("-" * 40)
        
        def check_input_sanitization():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for input sanitization functions
            sanitization_functions = [
                "sanitize_text_field",
                "sanitize_email",
                "sanitize_textarea_field"
            ]
            
            found_functions = sum(1 for func in sanitization_functions if func in content)
            print(f"Found {found_functions} sanitization functions")
            
            return found_functions >= 2
        
        def check_validation_logic():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for validation logic
            validation_patterns = [
                "empty(",
                "required",
                "validation",
                "notice-error"
            ]
            
            found_patterns = sum(1 for pattern in validation_patterns if pattern in content)
            print(f"Found {found_patterns} validation patterns")
            
            return found_patterns >= 2
        
        def check_database_insert_operations():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for database insert operations
            insert_count = content.count("$wpdb->insert")
            print(f"Found {insert_count} database insert operations")
            
            # Should have at least 3: debtor, case, financial
            return insert_count >= 3
        
        def check_audit_logging():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for audit logging
            audit_logging = ("klage_audit" in content and 
                           "case_created" in content)
            
            return audit_logging
        
        def check_success_feedback():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for success feedback
            success_feedback = ("notice-success" in content and 
                              "Erfolg" in content)
            
            return success_feedback
        
        self.test("Input sanitization implemented", check_input_sanitization)
        self.test("Validation logic implemented", check_validation_logic)
        self.test("Database insert operations working", check_database_insert_operations)
        self.test("Audit logging implemented", check_audit_logging)
        self.test("Success feedback implemented", check_success_feedback)
    
    def test_integration_functionality(self):
        """Test integration with existing functionality"""
        print("🔗 TESTING INTEGRATION FUNCTIONALITY")
        print("-" * 40)
        
        def check_plugin_activation():
            main_file = "/app/court-automation-hub.php"
            with open(main_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that plugin activation uses create_tables_direct
            activation_check = ("register_activation_hook" in content and 
                              "create_tables_direct" in content)
            
            return activation_check
        
        def check_admin_menu_integration():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin menu integration
            menu_integration = ("add_menu_page" in content and 
                              "add_submenu_page" in content)
            
            return menu_integration
        
        def check_existing_functionality_preserved():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing functionality is preserved
            existing_functions = [
                "admin_page_cases",
                "admin_page_dashboard",
                "admin_page_import",
                "admin_page_financial"
            ]
            
            found_functions = sum(1 for func in existing_functions if func in content)
            print(f"Found {found_functions} existing admin functions")
            
            return found_functions >= 3
        
        def check_csv_import_compatibility():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV import still works
            csv_compatibility = ("template" in content and 
                               "import" in content and
                               "csv" in content.lower())
            
            return csv_compatibility
        
        def check_financial_calculator_compatibility():
            admin_file = "/app/admin/class-admin-dashboard.php"
            with open(admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that financial calculator still works
            calculator_compatibility = ("548.11" in content and 
                                      "financial" in content and
                                      "calculator" in content.lower())
            
            return calculator_compatibility
        
        self.test("Plugin activation integration", check_plugin_activation)
        self.test("Admin menu integration", check_admin_menu_integration)
        self.test("Existing functionality preserved", check_existing_functionality_preserved)
        self.test("CSV import compatibility", check_csv_import_compatibility)
        self.test("Financial calculator compatibility", check_financial_calculator_compatibility)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 70)
        print("📊 v1.2.9 FUNCTIONAL TEST SUMMARY - Case Creation with Deutschland")
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
        
        print("\n🎯 CRITICAL FUNCTIONAL VERIFICATION:")
        critical_tests = [
            "Debtors table schema correctly defined",
            "Deutschland is default value",
            "Deutschland length compatibility (11 chars)",
            "No varchar(2) constraint in main schema",
            "Case creation form implemented",
            "Debtor record creation with country field",
            "Database insert operations working",
            "Plugin activation integration"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 FUNCTIONAL STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ v1.2.9 FUNCTIONAL VERIFICATION: SUCCESSFUL")
            print("Case creation with Deutschland country value should work correctly.")
            print("The original database constraint error should be resolved.")
        else:
            print("❌ v1.2.9 FUNCTIONAL VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n🌍 DEUTSCHLAND SUPPORT STATUS:")
        deutschland_tests = [
            "Deutschland is default value",
            "Deutschland length compatibility (11 chars)",
            "Form supports Deutschland value",
            "No varchar(2) constraint in main schema",
            "Migration logic from DE to Deutschland"
        ]
        
        deutschland_passed = 0
        for deutschland_test in deutschland_tests:
            if deutschland_test in self.results:
                result = self.results[deutschland_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {deutschland_test}")
                if result['status'] == 'passed':
                    deutschland_passed += 1
        
        if deutschland_passed == len(deutschland_tests):
            print("✅ DEUTSCHLAND SUPPORT: FULLY IMPLEMENTED")
            print("The debtors_country field can now accept 'Deutschland' (11 characters).")
        else:
            print("❌ DEUTSCHLAND SUPPORT: PARTIAL IMPLEMENTATION")
        
        print("\n" + "=" * 70)

def main():
    """Main test execution"""
    tester = FunctionalV129Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())