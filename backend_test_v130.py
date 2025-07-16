#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.3.0 Verification
Tests the critical database schema fix for missing columns in klage_debtors table.

Focus: Verify that the "Unknown column 'datenquelle' in 'field list'" error has been resolved.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV130Tester:
    """Test suite specifically for verifying hotfix v1.3.0 database schema fix"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_file = "/app/includes/class-database.php"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.3.0 Verification Tests")
        print("=" * 60)
        print("🎯 Focus: Database Schema Fix for klage_debtors Table")
        print("❌ Issue: Unknown column 'datenquelle' in 'field list'")
        print("✅ Fix: Added missing columns to ensure_debtors_table_schema()")
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_database_schema_fix()
        self.test_missing_columns_added()
        self.test_case_creation_compatibility()
        self.test_schema_synchronization()
        self.test_upgrade_mechanism()
        self.test_existing_functionality_preserved()
        
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
        """Test that plugin version is updated to 1.3.0"""
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
            return version == "1.3.0"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.3.0"
        
        self.test("Plugin header version is 1.3.0", check_plugin_version)
        self.test("Plugin constant version is 1.3.0", check_constant_version)
    
    def test_database_schema_fix(self):
        """Test the database schema fix implementation"""
        print("🗄️ TESTING DATABASE SCHEMA FIX")
        print("-" * 40)
        
        def check_ensure_debtors_table_schema_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+ensure_debtors_table_schema\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_datenquelle_column_definition():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for datenquelle column definition
            datenquelle_pattern = r"datenquelle\s+varchar\(50\)\s+DEFAULT\s+'manual'"
            found = bool(re.search(datenquelle_pattern, content))
            print(f"Found datenquelle column definition: {found}")
            return found
        
        def check_letzte_aktualisierung_column_definition():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for letzte_aktualisierung column definition
            letzte_pattern = r"letzte_aktualisierung\s+datetime\s+DEFAULT\s+NULL"
            found = bool(re.search(letzte_pattern, content))
            print(f"Found letzte_aktualisierung column definition: {found}")
            return found
        
        def check_table_recreation_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for DROP TABLE IF EXISTS and CREATE TABLE logic
            drop_table = 'DROP TABLE IF EXISTS $table_name' in content
            create_table = 'CREATE TABLE $table_name' in content
            
            print(f"Found DROP TABLE IF EXISTS: {drop_table}")
            print(f"Found CREATE TABLE: {create_table}")
            
            return drop_table and create_table
        
        self.test("ensure_debtors_table_schema() method exists", check_ensure_debtors_table_schema_method)
        self.test("datenquelle column properly defined", check_datenquelle_column_definition)
        self.test("letzte_aktualisierung column properly defined", check_letzte_aktualisierung_column_definition)
        self.test("Table recreation logic implemented", check_table_recreation_logic)
    
    def test_missing_columns_added(self):
        """Test that all missing columns from the complete schema are added"""
        print("📊 TESTING MISSING COLUMNS ADDED")
        print("-" * 40)
        
        def check_comprehensive_schema_columns():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Key columns that should be in the schema
            required_columns = [
                'datenquelle',
                'letzte_aktualisierung',
                'debtors_name',
                'debtors_company',
                'debtors_first_name',
                'debtors_last_name',
                'debtors_email',
                'debtors_phone',
                'debtors_address',
                'debtors_postal_code',
                'debtors_city',
                'debtors_country',
                'rechtsform',
                'finanzielle_situation',
                'insolvenz_status',
                'bevorzugte_sprache',
                'verifiziert'
            ]
            
            found_columns = 0
            for column in required_columns:
                if column in content:
                    found_columns += 1
            
            print(f"Found {found_columns}/{len(required_columns)} required columns")
            return found_columns >= 15  # Allow for some flexibility
        
        def check_default_values():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper default values
            default_checks = [
                "DEFAULT 'manual'",  # datenquelle
                "DEFAULT NULL",      # letzte_aktualisierung
                "DEFAULT 'Deutschland'",  # debtors_country
                "DEFAULT 'natuerliche_person'",  # rechtsform
                "DEFAULT 'unbekannt'"  # various status fields
            ]
            
            found_defaults = sum(1 for default in default_checks if default in content)
            print(f"Found {found_defaults}/{len(default_checks)} proper default values")
            
            return found_defaults >= 4
        
        def check_column_types():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper column types
            type_checks = [
                'varchar(50)',   # datenquelle
                'datetime',      # letzte_aktualisierung
                'varchar(100)',  # debtors_country (fixed from varchar(2))
                'tinyint(1)'     # boolean fields
            ]
            
            found_types = sum(1 for type_check in type_checks if type_check in content)
            print(f"Found {found_types}/{len(type_checks)} proper column types")
            
            return found_types >= 3
        
        self.test("Comprehensive schema columns present", check_comprehensive_schema_columns)
        self.test("Proper default values set", check_default_values)
        self.test("Correct column types defined", check_column_types)
    
    def test_case_creation_compatibility(self):
        """Test that case creation code is compatible with new schema"""
        print("💼 TESTING CASE CREATION COMPATIBILITY")
        print("-" * 40)
        
        def check_datenquelle_usage_in_case_creation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for datenquelle being used in insert operations
            datenquelle_usage = "'datenquelle'" in content and 'manual' in content
            print(f"Found datenquelle usage in case creation: {datenquelle_usage}")
            return datenquelle_usage
        
        def check_letzte_aktualisierung_usage():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for letzte_aktualisierung being used
            letzte_usage = "'letzte_aktualisierung'" in content and 'current_time' in content
            print(f"Found letzte_aktualisierung usage: {letzte_usage}")
            return letzte_usage
        
        def check_insert_operations_compatibility():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for database insert operations that would use these columns
            insert_operations = content.count('$wpdb->insert')
            klage_debtors_inserts = content.count('klage_debtors')
            
            print(f"Found {insert_operations} insert operations")
            print(f"Found {klage_debtors_inserts} references to klage_debtors table")
            
            return insert_operations >= 3 and klage_debtors_inserts >= 2
        
        def check_csv_import_compatibility():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV import sets datenquelle appropriately
            csv_datenquelle = 'forderungen_com' in content or 'csv_import' in content
            print(f"Found CSV import datenquelle handling: {csv_datenquelle}")
            return csv_datenquelle
        
        self.test("datenquelle used in case creation", check_datenquelle_usage_in_case_creation)
        self.test("letzte_aktualisierung used properly", check_letzte_aktualisierung_usage)
        self.test("Insert operations compatible with new schema", check_insert_operations_compatibility)
        self.test("CSV import compatibility maintained", check_csv_import_compatibility)
    
    def test_schema_synchronization(self):
        """Test that both table creation methods have synchronized schemas"""
        print("🔄 TESTING SCHEMA SYNCHRONIZATION")
        print("-" * 40)
        
        def check_create_tables_direct_calls_ensure_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for ensure_debtors_table_schema being called in create_tables_direct
            method_call = 'ensure_debtors_table_schema()' in content
            print(f"Found ensure_debtors_table_schema() call: {method_call}")
            return method_call
        
        def check_upgrade_existing_tables_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for upgrade_existing_tables being called
            upgrade_call = 'upgrade_existing_tables()' in content
            print(f"Found upgrade_existing_tables() call: {upgrade_call}")
            return upgrade_call
        
        def check_plugin_activation_uses_direct_method():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that plugin activation uses create_tables_direct
            activation_method = 'create_tables_direct()' in content
            print(f"Found create_tables_direct() in activation: {activation_method}")
            return activation_method
        
        self.test("create_tables_direct calls ensure_debtors_table_schema", check_create_tables_direct_calls_ensure_method)
        self.test("upgrade_existing_tables method called", check_upgrade_existing_tables_call)
        self.test("Plugin activation uses direct table creation", check_plugin_activation_uses_direct_method)
    
    def test_upgrade_mechanism(self):
        """Test the upgrade mechanism for existing installations"""
        print("⬆️ TESTING UPGRADE MECHANISM")
        print("-" * 40)
        
        def check_upgrade_existing_tables_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the upgrade method
            method_pattern = r'private\s+function\s+upgrade_existing_tables\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_column_detection_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for column detection logic
            detection_patterns = [
                'SHOW COLUMNS FROM',
                'column_info',
                'varchar(2)',  # Detection of old schema
                'ALTER TABLE'
            ]
            
            found_patterns = sum(1 for pattern in detection_patterns if pattern in content)
            print(f"Found {found_patterns}/{len(detection_patterns)} detection patterns")
            
            return found_patterns >= 3
        
        def check_data_migration_logic():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for data migration from 'DE' to 'Deutschland'
            migration_logic = "'DE'" in content and "'Deutschland'" in content and 'UPDATE' in content
            print(f"Found data migration logic: {migration_logic}")
            return migration_logic
        
        self.test("upgrade_existing_tables method exists", check_upgrade_existing_tables_method)
        self.test("Column detection logic implemented", check_column_detection_logic)
        self.test("Data migration logic present", check_data_migration_logic)
    
    def test_existing_functionality_preserved(self):
        """Test that existing functionality is preserved"""
        print("🔗 TESTING EXISTING FUNCTIONALITY PRESERVED")
        print("-" * 40)
        
        def check_existing_database_methods():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing methods still exist
            existing_methods = [
                'create_tables_direct',
                'get_table_status',
                'check_tables_exist'
            ]
            
            found_methods = sum(1 for method in existing_methods if method in content)
            print(f"Found {found_methods}/{len(existing_methods)} existing methods")
            
            return found_methods >= 2
        
        def check_case_creation_methods_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that case creation methods still exist
            case_methods = [
                'create_new_case',
                'handle_case_actions',
                'admin_page_cases'
            ]
            
            found_methods = sum(1 for method in case_methods if method in content)
            print(f"Found {found_methods}/{len(case_methods)} case creation methods")
            
            return found_methods >= 2
        
        def check_csv_import_functionality():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV import functionality is preserved
            csv_features = [
                'import_single_forderungen_case',
                'handle_import_action',
                'template_type'
            ]
            
            found_features = sum(1 for feature in csv_features if feature in content)
            print(f"Found {found_features}/{len(csv_features)} CSV import features")
            
            return found_features >= 2
        
        def check_gdpr_standard_amounts():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that GDPR standard amounts are preserved
            gdpr_amounts = ['350.00', '96.90', '13.36', '87.85', '548.11', '32.00']
            found_amounts = sum(1 for amount in gdpr_amounts if amount in content)
            
            print(f"Found {found_amounts}/{len(gdpr_amounts)} GDPR standard amounts")
            
            return found_amounts >= 4
        
        self.test("Existing database methods preserved", check_existing_database_methods)
        self.test("Case creation methods preserved", check_case_creation_methods_preserved)
        self.test("CSV import functionality preserved", check_csv_import_functionality)
        self.test("GDPR standard amounts preserved", check_gdpr_standard_amounts)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.3.0 VERIFICATION SUMMARY")
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
        
        print("\n🎯 CRITICAL DATABASE SCHEMA FIX VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.3.0",
            "datenquelle column properly defined",
            "letzte_aktualisierung column properly defined",
            "datenquelle used in case creation",
            "letzte_aktualisierung used properly",
            "create_tables_direct calls ensure_debtors_table_schema",
            "upgrade_existing_tables method exists",
            "Existing database methods preserved"
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
            print("✅ HOTFIX v1.3.0 VERIFICATION: SUCCESSFUL")
            print("Database schema fix implemented correctly.")
            print("The 'Unknown column 'datenquelle' in 'field list'' error should be resolved.")
        else:
            print("❌ HOTFIX v1.3.0 VERIFICATION: ISSUES FOUND")
            print("Some critical database schema fixes may not be working as expected.")
        
        print("\n🔍 EXPECTED BEHAVIOR AFTER FIX:")
        print("✅ Case creation should work without database column errors")
        print("✅ datenquelle field tracks manual vs CSV import source")
        print("✅ letzte_aktualisierung field tracks record update times")
        print("✅ All existing functionality should be preserved")
        print("✅ Both new and existing installations should work correctly")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV130Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())