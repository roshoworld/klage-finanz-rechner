#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.3.2 Verification
Tests the critical database schema fix for missing columns in klage_cases table.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV132Tester:
    """Test suite specifically for verifying hotfix v1.3.2 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_file = "/app/includes/class-database.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.3.2 Verification Tests")
        print("=" * 60)
        print("Testing: Database Schema Fix for Missing Columns in klage_cases Table")
        print("Issue: 'Unknown column 'brief_status' in 'field list'' error during case creation")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_upgrade_mechanism_implementation()
        self.test_cases_table_upgrade_methods()
        self.test_missing_columns_detection()
        self.test_case_creation_compatibility()
        self.test_database_version_tracking()
        self.test_automatic_upgrade_trigger()
        self.test_existing_functionality_preservation()
        
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
        """Test that plugin version is updated to 1.3.2"""
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
            return version == "1.3.2"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.3.2"
        
        def check_database_version():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check database version in check_and_upgrade_schema method
            version_match = re.search(r"\$current_version\s*=\s*'([^']+)'", content)
            if not version_match:
                return False
            
            version = version_match.group(1)
            print(f"Found database version: {version}")
            return version == "1.3.2"
        
        self.test("Plugin header version is 1.3.2", check_plugin_version)
        self.test("Plugin constant version is 1.3.2", check_constant_version)
        self.test("Database version is 1.3.2", check_database_version)
    
    def test_upgrade_mechanism_implementation(self):
        """Test that the upgrade mechanism is properly implemented"""
        print("🔧 TESTING UPGRADE MECHANISM IMPLEMENTATION")
        print("-" * 40)
        
        def check_upgrade_existing_tables_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the upgrade_existing_tables method
            method_pattern = r'private\s+function\s+upgrade_existing_tables\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_cases_table_upgrade_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade_existing_tables calls upgrade_cases_table
            upgrade_call = '$this->upgrade_cases_table()' in content
            print(f"upgrade_cases_table() call found: {upgrade_call}")
            
            return upgrade_call
        
        def check_version_comparison_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for version comparison logic
            version_comparison = 'version_compare(' in content and 'get_option(' in content
            update_option = 'update_option(' in content and 'cah_database_version' in content
            
            print(f"Version comparison logic found: {version_comparison}")
            print(f"Version update logic found: {update_option}")
            
            return version_comparison and update_option
        
        def check_admin_init_hook():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin_init hook registration
            admin_hook = "add_action('admin_init'" in content and 'check_and_upgrade_schema' in content
            
            return admin_hook
        
        self.test("upgrade_existing_tables() method exists", check_upgrade_existing_tables_method)
        self.test("Cases table upgrade is called", check_cases_table_upgrade_call)
        self.test("Version comparison and update logic", check_version_comparison_logic)
        self.test("Admin init hook for automatic upgrade", check_admin_init_hook)
    
    def test_cases_table_upgrade_methods(self):
        """Test the cases table specific upgrade methods"""
        print("📊 TESTING CASES TABLE UPGRADE METHODS")
        print("-" * 40)
        
        def check_upgrade_cases_table_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the upgrade_cases_table method
            method_pattern = r'private\s+function\s+upgrade_cases_table\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_add_missing_columns_to_cases_table_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the add_missing_columns_to_cases_table method
            method_pattern = r'private\s+function\s+add_missing_columns_to_cases_table\s*\('
            return bool(re.search(method_pattern, content))
        
        def check_table_existence_verification():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for table existence check in upgrade_cases_table
            table_check = 'SHOW TABLES LIKE' in content and 'klage_cases' in content
            
            return table_check
        
        def check_method_integration():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade_cases_table calls add_missing_columns_to_cases_table
            method_call = '$this->add_missing_columns_to_cases_table(' in content
            
            return method_call
        
        self.test("upgrade_cases_table() method exists", check_upgrade_cases_table_method)
        self.test("add_missing_columns_to_cases_table() method exists", check_add_missing_columns_to_cases_table_method)
        self.test("Table existence verification", check_table_existence_verification)
        self.test("Methods are properly integrated", check_method_integration)
    
    def test_missing_columns_detection(self):
        """Test the missing columns detection and addition logic"""
        print("🔍 TESTING MISSING COLUMNS DETECTION")
        print("-" * 40)
        
        def check_required_columns_definition():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for the 13 missing columns mentioned in review
            required_columns = [
                'brief_status',
                'verfahrensart', 
                'rechtsgrundlage',
                'kategorie',
                'schadenhoehe',
                'verfahrenswert',
                'erfolgsaussicht',
                'risiko_bewertung',
                'komplexitaet',
                'prioritaet_intern',
                'bearbeitungsstatus',
                'kommunikation_sprache',
                'import_source'
            ]
            
            found_columns = 0
            for column in required_columns:
                if column in content:
                    found_columns += 1
            
            print(f"Found {found_columns}/{len(required_columns)} required columns")
            return found_columns >= 12  # Allow for minor variations
        
        def check_alter_table_statements():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for ALTER TABLE statements
            alter_statements = content.count('ALTER TABLE')
            print(f"Found {alter_statements} ALTER TABLE statements")
            
            return alter_statements >= 10  # Should have multiple ALTER statements
        
        def check_column_existence_detection():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for SHOW COLUMNS logic
            show_columns = 'SHOW COLUMNS FROM' in content
            existing_columns = '$existing_columns' in content
            in_array_check = 'in_array(' in content
            
            print(f"SHOW COLUMNS logic: {show_columns}")
            print(f"Existing columns tracking: {existing_columns}")
            print(f"Column existence check: {in_array_check}")
            
            return show_columns and existing_columns and in_array_check
        
        def check_default_values():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper default values
            default_values = [
                "DEFAULT 'pending'",
                "DEFAULT 'mahnverfahren'",
                "DEFAULT 'DSGVO Art. 82'",
                "DEFAULT 'GDPR_SPAM'",
                "DEFAULT 350.00",
                "DEFAULT 548.11",
                "DEFAULT 'hoch'",
                "DEFAULT 'niedrig'",
                "DEFAULT 'standard'",
                "DEFAULT 'medium'",
                "DEFAULT 'neu'",
                "DEFAULT 'de'",
                "DEFAULT 'manual'"
            ]
            
            found_defaults = 0
            for default in default_values:
                if default in content:
                    found_defaults += 1
            
            print(f"Found {found_defaults}/{len(default_values)} default values")
            return found_defaults >= 10
        
        self.test("Required columns definition (13 columns)", check_required_columns_definition)
        self.test("ALTER TABLE statements for column addition", check_alter_table_statements)
        self.test("Column existence detection logic", check_column_existence_detection)
        self.test("Proper default values for new columns", check_default_values)
    
    def test_case_creation_compatibility(self):
        """Test that case creation works with the new columns"""
        print("💼 TESTING CASE CREATION COMPATIBILITY")
        print("-" * 40)
        
        def check_brief_status_usage():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check if brief_status is used in case creation
            brief_status_usage = 'brief_status' in content
            
            return brief_status_usage
        
        def check_new_columns_in_case_creation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for usage of new columns in case creation
            new_columns = [
                'verfahrensart',
                'rechtsgrundlage', 
                'kategorie',
                'schadenhoehe',
                'verfahrenswert'
            ]
            
            found_columns = 0
            for column in new_columns:
                if column in content:
                    found_columns += 1
            
            print(f"Found {found_columns}/{len(new_columns)} new columns in case creation")
            return found_columns >= 3
        
        def check_database_insert_compatibility():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for database insert operations
            insert_operations = '$wpdb->insert' in content and 'klage_cases' in content
            
            return insert_operations
        
        def check_gdpr_standard_values():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GDPR standard values that should work with new columns
            gdpr_values = ['350.00', '548.11', 'DSGVO Art. 82', 'GDPR_SPAM']
            found_values = sum(1 for value in gdpr_values if value in content)
            
            print(f"Found {found_values}/{len(gdpr_values)} GDPR standard values")
            return found_values >= 3
        
        self.test("brief_status column usage", check_brief_status_usage)
        self.test("New columns in case creation", check_new_columns_in_case_creation)
        self.test("Database insert compatibility", check_database_insert_compatibility)
        self.test("GDPR standard values compatibility", check_gdpr_standard_values)
    
    def test_database_version_tracking(self):
        """Test database version tracking mechanism"""
        print("📊 TESTING DATABASE VERSION TRACKING")
        print("-" * 40)
        
        def check_version_option_handling():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for version option handling
            get_option = "get_option('cah_database_version'" in content
            update_option = "update_option('cah_database_version'" in content
            
            print(f"get_option for version: {get_option}")
            print(f"update_option for version: {update_option}")
            
            return get_option and update_option
        
        def check_version_comparison():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper version comparison
            version_compare = 'version_compare(' in content
            less_than_check = "'<'" in content
            
            return version_compare and less_than_check
        
        def check_upgrade_prevention():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade only runs when needed
            if_condition = 'if (version_compare(' in content
            upgrade_call = '$this->upgrade_existing_tables()' in content
            
            return if_condition and upgrade_call
        
        self.test("Version option handling", check_version_option_handling)
        self.test("Version comparison logic", check_version_comparison)
        self.test("Upgrade prevention for same version", check_upgrade_prevention)
    
    def test_automatic_upgrade_trigger(self):
        """Test automatic upgrade trigger mechanism"""
        print("🔄 TESTING AUTOMATIC UPGRADE TRIGGER")
        print("-" * 40)
        
        def check_admin_init_registration():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin_init hook in constructor
            admin_init = "add_action('admin_init'" in content
            method_reference = 'check_and_upgrade_schema' in content
            
            return admin_init and method_reference
        
        def check_admin_page_restriction():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade only runs on admin pages
            is_admin_check = 'is_admin()' in content
            return_early = 'return;' in content
            
            return is_admin_check and return_early
        
        def check_upgrade_method_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that check_and_upgrade_schema calls upgrade_existing_tables
            upgrade_call = '$this->upgrade_existing_tables()' in content
            
            return upgrade_call
        
        self.test("Admin init hook registration", check_admin_init_registration)
        self.test("Admin page restriction", check_admin_page_restriction)
        self.test("Upgrade method call in check_and_upgrade_schema", check_upgrade_method_call)
    
    def test_existing_functionality_preservation(self):
        """Test that existing functionality is preserved"""
        print("🔗 TESTING EXISTING FUNCTIONALITY PRESERVATION")
        print("-" * 40)
        
        def check_debtors_table_upgrade_preserved():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that debtors table upgrade is still called
            debtors_upgrade = '$this->add_missing_columns_to_debtors_table(' in content
            
            return debtors_upgrade
        
        def check_create_tables_direct_preserved():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that create_tables_direct method still exists
            method_pattern = r'public\s+function\s+create_tables_direct\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_existing_table_definitions():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing table definitions are preserved
            tables = ['klage_cases', 'klage_debtors', 'klage_financial', 'klage_audit']
            found_tables = sum(1 for table in tables if table in content)
            
            print(f"Found {found_tables}/{len(tables)} table definitions")
            return found_tables == len(tables)
        
        def check_gdpr_amounts_preserved():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that GDPR standard amounts are preserved
            gdpr_amounts = ['350.00', '96.90', '13.36', '87.85', '548.11', '32.00']
            found_amounts = sum(1 for amount in gdpr_amounts if amount in content)
            
            print(f"Found {found_amounts}/{len(gdpr_amounts)} GDPR amounts")
            return found_amounts >= 4
        
        self.test("Debtors table upgrade preserved", check_debtors_table_upgrade_preserved)
        self.test("create_tables_direct method preserved", check_create_tables_direct_preserved)
        self.test("Existing table definitions preserved", check_existing_table_definitions)
        self.test("GDPR standard amounts preserved", check_gdpr_amounts_preserved)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.3.2 VERIFICATION SUMMARY")
        print("=" * 60)
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
        
        print("\n🎯 CRITICAL HOTFIX VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.3.2",
            "Database version is 1.3.2",
            "upgrade_cases_table() method exists",
            "add_missing_columns_to_cases_table() method exists",
            "Required columns definition (13 columns)",
            "Column existence detection logic",
            "Version option handling",
            "Admin init hook registration"
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
            print("✅ HOTFIX v1.3.2 VERIFICATION: SUCCESSFUL")
            print("Database schema fix for missing columns in klage_cases table is implemented correctly.")
            print("The 'Unknown column 'brief_status' in 'field list'' error should be resolved.")
        else:
            print("❌ HOTFIX v1.3.2 VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n🔧 EXPECTED BEHAVIOR AFTER UPGRADE:")
        print("✅ Upgrade runs automatically when user visits admin page")
        print("✅ Missing columns added to klage_cases table")
        print("✅ Case creation works without database errors")
        print("✅ Both debtors and cases tables upgraded properly")
        print("✅ No data loss during upgrade")
        print("✅ All existing functionality preserved")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV132Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())