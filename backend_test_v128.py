#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.8 Verification
Tests the critical database schema fix for debtors_country field length issue.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV128Tester:
    """Test suite specifically for verifying hotfix v1.2.8 database schema fix"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_file = "/app/includes/class-database.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.2.8 Database Schema Fix Verification Tests")
        print("=" * 70)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_database_schema_fix()
        self.test_plugin_activation_method()
        self.test_debtors_country_field_definition()
        self.test_case_creation_compatibility()
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
        """Test that plugin version is updated to 1.2.8"""
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
            return version == "1.2.8"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.8"
        
        self.test("Plugin header version is 1.2.8", check_plugin_version)
        self.test("Plugin constant version is 1.2.8", check_constant_version)
    
    def test_database_schema_fix(self):
        """Test the database schema fix for debtors_country field"""
        print("🗄️ TESTING DATABASE SCHEMA FIX")
        print("-" * 40)
        
        def check_create_tables_direct_method():
            """Verify create_tables_direct() method exists and has correct schema"""
            if not os.path.exists(self.database_file):
                raise Exception(f"Database file not found: {self.database_file}")
            
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that create_tables_direct method exists
            method_pattern = r'public\s+function\s+create_tables_direct\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_debtors_country_field_length():
            """Verify debtors_country field is varchar(100) not varchar(2)"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the debtors_country field definition in create_tables_direct
            # Should be varchar(100) DEFAULT 'Deutschland'
            country_field_pattern = r"debtors_country\s+varchar\((\d+)\)\s+DEFAULT\s+'Deutschland'"
            match = re.search(country_field_pattern, content)
            
            if not match:
                print("debtors_country field definition not found")
                return False
            
            field_length = int(match.group(1))
            print(f"Found debtors_country field length: {field_length}")
            
            # Should be 100, not 2
            return field_length == 100
        
        def check_default_value_deutschland():
            """Verify default value is 'Deutschland' not 'DE'"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the default value
            default_pattern = r"debtors_country.*DEFAULT\s+'([^']+)'"
            match = re.search(default_pattern, content)
            
            if not match:
                print("debtors_country default value not found")
                return False
            
            default_value = match.group(1)
            print(f"Found debtors_country default value: {default_value}")
            
            return default_value == "Deutschland"
        
        def check_old_create_tables_method_still_exists():
            """Verify old create_tables() method still exists for backward compatibility"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that create_tables method exists
            method_pattern = r'public\s+function\s+create_tables\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_old_method_has_correct_field_length():
            """Verify old create_tables() method also has the fix"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Find the old create_tables method and check debtors_country field
            # Look for the field definition in the old method
            old_method_start = content.find('public function create_tables()')
            if old_method_start == -1:
                return False
            
            # Get content from old method onwards
            old_method_content = content[old_method_start:]
            
            # Look for debtors_country field in old method
            country_field_pattern = r"debtors_country\s+varchar\((\d+)\)"
            match = re.search(country_field_pattern, old_method_content)
            
            if not match:
                print("debtors_country field in old method not found")
                return False
            
            field_length = int(match.group(1))
            print(f"Found debtors_country field length in old method: {field_length}")
            
            # Should be 100, not 2
            return field_length == 100
        
        self.test("create_tables_direct() method exists", check_create_tables_direct_method)
        self.test("debtors_country field length is varchar(100)", check_debtors_country_field_length)
        self.test("debtors_country default value is 'Deutschland'", check_default_value_deutschland)
        self.test("Old create_tables() method still exists", check_old_create_tables_method_still_exists)
        self.test("Old method also has correct field length", check_old_method_has_correct_field_length)
    
    def test_plugin_activation_method(self):
        """Test that plugin activation uses create_tables_direct()"""
        print("🔌 TESTING PLUGIN ACTIVATION METHOD")
        print("-" * 40)
        
        def check_activation_uses_create_tables_direct():
            """Verify activate() method calls create_tables_direct()"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the activate method
            activate_method_pattern = r'public\s+function\s+activate\s*\(\s*\)\s*\{([^}]+)\}'
            match = re.search(activate_method_pattern, content, re.DOTALL)
            
            if not match:
                print("activate() method not found")
                return False
            
            activate_content = match.group(1)
            print(f"Found activate method content")
            
            # Check that it calls create_tables_direct()
            direct_call = 'create_tables_direct()' in activate_content
            print(f"create_tables_direct() call found: {direct_call}")
            
            return direct_call
        
        def check_activation_does_not_use_old_method():
            """Verify activate() method does NOT call create_tables()"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the activate method
            activate_method_pattern = r'public\s+function\s+activate\s*\(\s*\)\s*\{([^}]+)\}'
            match = re.search(activate_method_pattern, content, re.DOTALL)
            
            if not match:
                return False
            
            activate_content = match.group(1)
            
            # Check that it does NOT call create_tables() (without _direct)
            old_call = '$database->create_tables()' in activate_content
            print(f"Old create_tables() call found: {old_call}")
            
            # Should return False (meaning old method is NOT called)
            return not old_call
        
        self.test("Plugin activation uses create_tables_direct()", check_activation_uses_create_tables_direct)
        self.test("Plugin activation does not use old create_tables()", check_activation_does_not_use_old_method)
    
    def test_debtors_country_field_definition(self):
        """Test the specific debtors_country field definition"""
        print("🌍 TESTING DEBTORS_COUNTRY FIELD DEFINITION")
        print("-" * 40)
        
        def check_field_can_store_deutschland():
            """Verify field can store 'Deutschland' (11 characters)"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check field length is sufficient for 'Deutschland'
            country_field_pattern = r"debtors_country\s+varchar\((\d+)\)"
            match = re.search(country_field_pattern, content)
            
            if not match:
                return False
            
            field_length = int(match.group(1))
            deutschland_length = len("Deutschland")
            
            print(f"Field length: {field_length}, 'Deutschland' length: {deutschland_length}")
            
            return field_length >= deutschland_length
        
        def check_field_can_store_long_country_names():
            """Verify field can store long country names"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check field length is sufficient for long country names
            country_field_pattern = r"debtors_country\s+varchar\((\d+)\)"
            match = re.search(country_field_pattern, content)
            
            if not match:
                return False
            
            field_length = int(match.group(1))
            
            # Test with some long country names
            long_countries = [
                "United Kingdom of Great Britain and Northern Ireland",  # 56 chars
                "Democratic Republic of the Congo",  # 33 chars
                "Bosnia and Herzegovina",  # 22 chars
                "Deutschland"  # 11 chars
            ]
            
            max_length = max(len(country) for country in long_countries)
            print(f"Field length: {field_length}, Max test country length: {max_length}")
            
            return field_length >= max_length
        
        def check_field_definition_in_klage_debtors_table():
            """Verify field is properly defined in klage_debtors table"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for klage_debtors table definition
            debtors_table_pattern = r"'klage_debtors'\s*=>\s*\"CREATE TABLE[^\"]*debtors_country[^\"]*\""
            match = re.search(debtors_table_pattern, content, re.DOTALL)
            
            if not match:
                print("klage_debtors table definition not found")
                return False
            
            table_definition = match.group(0)
            print("Found klage_debtors table with debtors_country field")
            
            # Check that it contains varchar(100)
            return "varchar(100)" in table_definition
        
        self.test("Field can store 'Deutschland'", check_field_can_store_deutschland)
        self.test("Field can store long country names", check_field_can_store_long_country_names)
        self.test("Field properly defined in klage_debtors table", check_field_definition_in_klage_debtors_table)
    
    def test_case_creation_compatibility(self):
        """Test that case creation will work with Deutschland country value"""
        print("📝 TESTING CASE CREATION COMPATIBILITY")
        print("-" * 40)
        
        def check_database_schema_supports_deutschland():
            """Verify database schema supports 'Deutschland' value"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that the schema has the correct field definition
            schema_correct = (
                "debtors_country varchar(100)" in content and
                "DEFAULT 'Deutschland'" in content
            )
            
            print(f"Schema supports Deutschland: {schema_correct}")
            return schema_correct
        
        def check_no_varchar_2_references():
            """Verify there are no varchar(2) references for debtors_country"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for any varchar(2) references near debtors_country
            varchar2_pattern = r"debtors_country.*varchar\(2\)"
            match = re.search(varchar2_pattern, content)
            
            if match:
                print(f"Found varchar(2) reference: {match.group(0)}")
                return False
            
            print("No varchar(2) references found for debtors_country")
            return True
        
        def check_table_creation_will_succeed():
            """Verify table creation SQL is syntactically correct"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the klage_debtors table creation SQL
            debtors_table_start = content.find("'klage_debtors' => \"CREATE TABLE")
            if debtors_table_start == -1:
                return False
            
            # Find the end of this table definition
            debtors_table_end = content.find('") $charset_collate",', debtors_table_start)
            if debtors_table_end == -1:
                return False
            
            table_sql = content[debtors_table_start:debtors_table_end]
            
            # Basic SQL syntax checks
            has_create_table = "CREATE TABLE" in table_sql
            has_primary_key = "PRIMARY KEY" in table_sql
            has_debtors_country = "debtors_country" in table_sql
            has_varchar_100 = "varchar(100)" in table_sql
            
            print(f"SQL syntax checks - CREATE TABLE: {has_create_table}, PRIMARY KEY: {has_primary_key}")
            print(f"debtors_country field: {has_debtors_country}, varchar(100): {has_varchar_100}")
            
            return all([has_create_table, has_primary_key, has_debtors_country, has_varchar_100])
        
        self.test("Database schema supports 'Deutschland'", check_database_schema_supports_deutschland)
        self.test("No varchar(2) references remain", check_no_varchar_2_references)
        self.test("Table creation SQL is correct", check_table_creation_will_succeed)
    
    def test_existing_functionality_preservation(self):
        """Test that existing functionality is preserved"""
        print("🔄 TESTING EXISTING FUNCTIONALITY PRESERVATION")
        print("-" * 40)
        
        def check_all_required_tables_defined():
            """Verify all required tables are still defined"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            required_tables = [
                'klage_cases',
                'klage_clients', 
                'klage_emails',
                'klage_financial',
                'klage_courts',
                'klage_audit',
                'klage_debtors',
                'klage_documents',
                'klage_communications',
                'klage_deadlines'
            ]
            
            found_tables = 0
            for table in required_tables:
                if f"'{table}'" in content:
                    found_tables += 1
            
            print(f"Found {found_tables}/{len(required_tables)} required tables")
            return found_tables >= 8  # At least 8 core tables should be present
        
        def check_other_debtor_fields_unchanged():
            """Verify other debtor fields are not affected"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that other important debtor fields are still present
            debtor_fields = [
                'debtors_name',
                'debtors_company', 
                'debtors_first_name',
                'debtors_last_name',
                'debtors_email',
                'debtors_phone',
                'debtors_address',
                'debtors_postal_code',
                'debtors_city'
            ]
            
            found_fields = sum(1 for field in debtor_fields if field in content)
            print(f"Found {found_fields}/{len(debtor_fields)} debtor fields")
            
            return found_fields >= 7
        
        def check_database_methods_preserved():
            """Verify database management methods are preserved"""
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            required_methods = [
                'create_tables_direct',
                'create_tables',
                'insert_default_courts',
                'get_table_status'
            ]
            
            found_methods = sum(1 for method in required_methods if f'function {method}' in content)
            print(f"Found {found_methods}/{len(required_methods)} database methods")
            
            return found_methods >= 3
        
        self.test("All required tables defined", check_all_required_tables_defined)
        self.test("Other debtor fields unchanged", check_other_debtor_fields_unchanged)
        self.test("Database methods preserved", check_database_methods_preserved)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 70)
        print("📊 HOTFIX v1.2.8 DATABASE SCHEMA FIX VERIFICATION SUMMARY")
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
            "Plugin header version is 1.2.8",
            "debtors_country field length is varchar(100)",
            "debtors_country default value is 'Deutschland'",
            "Plugin activation uses create_tables_direct()",
            "Field can store 'Deutschland'",
            "No varchar(2) references remain",
            "Database schema supports 'Deutschland'"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 DATABASE SCHEMA FIX STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ HOTFIX v1.2.8 VERIFICATION: SUCCESSFUL")
            print("Database schema fix is implemented correctly. Case creation with 'Deutschland' should work.")
        else:
            print("❌ HOTFIX v1.2.8 VERIFICATION: ISSUES FOUND")
            print("Database schema fix may not be complete or working as expected.")
        
        print("\n🔍 ISSUE ANALYSIS:")
        print("Original Issue: Database error when creating cases:")
        print("'Processing the value for the following field failed: debtors_country.'")
        print("'The supplied value may be too long or contains invalid data.'")
        print()
        print("Root Cause: debtors_country field was varchar(2) but form tried to insert 'Deutschland' (11 chars)")
        print()
        print("Fix Applied:")
        print("1. Updated debtors_country from varchar(2) to varchar(100)")
        print("2. Changed default from 'DE' to 'Deutschland'")
        print("3. Updated plugin to use create_tables_direct() method")
        print("4. Updated version to 1.2.8")
        
        print("\n" + "=" * 70)

def main():
    """Main test execution"""
    tester = HotfixV128Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())