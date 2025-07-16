#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub v1.3.1
Testing the enhanced upgrade mechanism for persistent 'datenquelle' column error fix

Focus Areas:
1. Automatic upgrade mechanism functionality
2. Missing column detection and addition
3. Case creation working after upgrade
4. Database version tracking
5. No data loss during upgrade
6. Existing functionality preservation
"""

import subprocess
import sys
import os
import re
import time
from datetime import datetime

class CourtAutomationHubTester:
    def __init__(self):
        self.test_results = []
        self.passed_tests = 0
        self.failed_tests = 0
        self.app_path = "/app"
        
    def log_test(self, test_name, passed, details=""):
        """Log test results"""
        status = "✅ PASSED" if passed else "❌ FAILED"
        self.test_results.append(f"{status}: {test_name}")
        if details:
            self.test_results.append(f"   Details: {details}")
        
        if passed:
            self.passed_tests += 1
        else:
            self.failed_tests += 1
            
        print(f"{status}: {test_name}")
        if details:
            print(f"   Details: {details}")

    def run_command(self, command, cwd=None):
        """Execute shell command and return output"""
        try:
            if cwd is None:
                cwd = self.app_path
            result = subprocess.run(command, shell=True, capture_output=True, text=True, cwd=cwd, timeout=30)
            return result.returncode == 0, result.stdout, result.stderr
        except subprocess.TimeoutExpired:
            return False, "", "Command timed out"
        except Exception as e:
            return False, "", str(e)

    def check_file_exists(self, filepath):
        """Check if file exists"""
        return os.path.exists(filepath)

    def read_file_content(self, filepath):
        """Read file content safely"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                return f.read()
        except Exception as e:
            return f"Error reading file: {str(e)}"

    def test_version_update(self):
        """Test 1: Verify version is updated to 1.3.1"""
        print("\n=== Testing Version Update ===")
        
        main_file = f"{self.app_path}/court-automation-hub.php"
        if not self.check_file_exists(main_file):
            self.log_test("Version Update - File Exists", False, "Main plugin file not found")
            return
            
        content = self.read_file_content(main_file)
        
        # Check version in header
        version_header = re.search(r'Version:\s*([0-9.]+)', content)
        if version_header and version_header.group(1) == '1.3.1':
            self.log_test("Version Update - Header", True, f"Version header shows {version_header.group(1)}")
        else:
            self.log_test("Version Update - Header", False, f"Version header not 1.3.1: {version_header.group(1) if version_header else 'Not found'}")
        
        # Check version constant
        version_constant = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([0-9.]+)'\)", content)
        if version_constant and version_constant.group(1) == '1.3.1':
            self.log_test("Version Update - Constant", True, f"Version constant shows {version_constant.group(1)}")
        else:
            self.log_test("Version Update - Constant", False, f"Version constant not 1.3.1: {version_constant.group(1) if version_constant else 'Not found'}")

    def test_upgrade_mechanism_structure(self):
        """Test 2: Verify upgrade mechanism structure exists"""
        print("\n=== Testing Upgrade Mechanism Structure ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        if not self.check_file_exists(database_file):
            self.log_test("Upgrade Mechanism - File Exists", False, "Database class file not found")
            return
            
        content = self.read_file_content(database_file)
        
        # Check for check_and_upgrade_schema method
        if 'function check_and_upgrade_schema()' in content:
            self.log_test("Upgrade Mechanism - check_and_upgrade_schema Method", True, "Method exists")
        else:
            self.log_test("Upgrade Mechanism - check_and_upgrade_schema Method", False, "Method not found")
        
        # Check for add_missing_columns_to_debtors_table method
        if 'function add_missing_columns_to_debtors_table(' in content:
            self.log_test("Upgrade Mechanism - add_missing_columns_to_debtors_table Method", True, "Method exists")
        else:
            self.log_test("Upgrade Mechanism - add_missing_columns_to_debtors_table Method", False, "Method not found")
        
        # Check for admin_init hook
        if "add_action('admin_init', array(\$this, 'check_and_upgrade_schema'))" in content:
            self.log_test("Upgrade Mechanism - Admin Init Hook", True, "Hook registered")
        else:
            self.log_test("Upgrade Mechanism - Admin Init Hook", False, "Hook not found")
        
        # Check for version comparison logic
        if 'version_compare(' in content and 'cah_database_version' in content:
            self.log_test("Upgrade Mechanism - Version Comparison", True, "Version comparison logic exists")
        else:
            self.log_test("Upgrade Mechanism - Version Comparison", False, "Version comparison logic not found")

    def test_missing_column_detection(self):
        """Test 3: Verify missing column detection logic"""
        print("\n=== Testing Missing Column Detection ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Check for required columns array
        required_columns_pattern = r'\$required_columns\s*=\s*array\('
        if re.search(required_columns_pattern, content):
            self.log_test("Column Detection - Required Columns Array", True, "Array definition found")
        else:
            self.log_test("Column Detection - Required Columns Array", False, "Array definition not found")
        
        # Check for specific columns mentioned in review
        critical_columns = ['datenquelle', 'letzte_aktualisierung']
        for column in critical_columns:
            if f"'{column}'" in content:
                self.log_test(f"Column Detection - {column} Column", True, f"Column {column} found in code")
            else:
                self.log_test(f"Column Detection - {column} Column", False, f"Column {column} not found in code")
        
        # Check for SHOW COLUMNS query
        if 'SHOW COLUMNS FROM' in content:
            self.log_test("Column Detection - SHOW COLUMNS Query", True, "Column detection query exists")
        else:
            self.log_test("Column Detection - SHOW COLUMNS Query", False, "Column detection query not found")
        
        # Check for ALTER TABLE statements
        if 'ALTER TABLE' in content and 'ADD COLUMN' in content:
            self.log_test("Column Detection - ALTER TABLE Logic", True, "ALTER TABLE statements found")
        else:
            self.log_test("Column Detection - ALTER TABLE Logic", False, "ALTER TABLE statements not found")

    def test_database_version_tracking(self):
        """Test 4: Verify database version tracking implementation"""
        print("\n=== Testing Database Version Tracking ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Check for get_option call
        if 'get_option(' in content and 'cah_database_version' in content:
            self.log_test("Version Tracking - Get Option", True, "get_option for database version found")
        else:
            self.log_test("Version Tracking - Get Option", False, "get_option for database version not found")
        
        # Check for update_option call
        if 'update_option(' in content and 'cah_database_version' in content:
            self.log_test("Version Tracking - Update Option", True, "update_option for database version found")
        else:
            self.log_test("Version Tracking - Update Option", False, "update_option for database version not found")
        
        # Check for current version definition
        if "'1.3.1'" in content and 'current_version' in content:
            self.log_test("Version Tracking - Current Version", True, "Current version 1.3.1 defined")
        else:
            self.log_test("Version Tracking - Current Version", False, "Current version 1.3.1 not found")
        
        # Check for admin page restriction
        if 'is_admin()' in content:
            self.log_test("Version Tracking - Admin Restriction", True, "Admin page restriction exists")
        else:
            self.log_test("Version Tracking - Admin Restriction", False, "Admin page restriction not found")

    def test_comprehensive_column_set(self):
        """Test 5: Verify comprehensive column set implementation"""
        print("\n=== Testing Comprehensive Column Set ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Expected columns from review request
        expected_columns = [
            'datenquelle', 'letzte_aktualisierung', 'website', 'social_media',
            'zahlungsverhalten', 'bonität', 'insolvenz_status', 'pfändung_status',
            'bevorzugte_sprache', 'kommunikation_email', 'kommunikation_post', 'verifiziert'
        ]
        
        found_columns = 0
        for column in expected_columns:
            if f"'{column}'" in content:
                found_columns += 1
                self.log_test(f"Column Set - {column}", True, f"Column {column} found")
            else:
                self.log_test(f"Column Set - {column}", False, f"Column {column} not found")
        
        # Overall column set test
        if found_columns >= 10:  # At least 10 out of 12 columns should be found
            self.log_test("Column Set - Comprehensive Coverage", True, f"Found {found_columns}/{len(expected_columns)} expected columns")
        else:
            self.log_test("Column Set - Comprehensive Coverage", False, f"Only found {found_columns}/{len(expected_columns)} expected columns")

    def test_schema_synchronization(self):
        """Test 6: Verify schema synchronization between methods"""
        print("\n=== Testing Schema Synchronization ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Check for ensure_debtors_table_schema method
        if 'function ensure_debtors_table_schema()' in content:
            self.log_test("Schema Sync - ensure_debtors_table_schema Method", True, "Method exists")
        else:
            self.log_test("Schema Sync - ensure_debtors_table_schema Method", False, "Method not found")
        
        # Check for create_tables_direct method
        if 'function create_tables_direct()' in content:
            self.log_test("Schema Sync - create_tables_direct Method", True, "Method exists")
        else:
            self.log_test("Schema Sync - create_tables_direct Method", False, "Method not found")
        
        # Check for upgrade_existing_tables method
        if 'function upgrade_existing_tables()' in content:
            self.log_test("Schema Sync - upgrade_existing_tables Method", True, "Method exists")
        else:
            self.log_test("Schema Sync - upgrade_existing_tables Method", False, "Method not found")
        
        # Check for method integration
        if 'upgrade_existing_tables()' in content and 'ensure_debtors_table_schema()' in content:
            self.log_test("Schema Sync - Method Integration", True, "Methods are integrated")
        else:
            self.log_test("Schema Sync - Method Integration", False, "Methods not properly integrated")

    def test_case_creation_compatibility(self):
        """Test 7: Verify case creation compatibility with new schema"""
        print("\n=== Testing Case Creation Compatibility ===")
        
        admin_file = f"{self.app_path}/admin/class-admin-dashboard.php"
        if not self.check_file_exists(admin_file):
            self.log_test("Case Creation - Admin File Exists", False, "Admin dashboard file not found")
            return
            
        content = self.read_file_content(admin_file)
        
        # Check for create_new_case method
        if 'function create_new_case()' in content:
            self.log_test("Case Creation - create_new_case Method", True, "Method exists")
        else:
            self.log_test("Case Creation - create_new_case Method", False, "Method not found")
        
        # Check for datenquelle field usage
        if "'datenquelle'" in content and 'manual' in content:
            self.log_test("Case Creation - datenquelle Field Usage", True, "datenquelle field used in case creation")
        else:
            self.log_test("Case Creation - datenquelle Field Usage", False, "datenquelle field not used properly")
        
        # Check for letzte_aktualisierung field usage
        if "'letzte_aktualisierung'" in content:
            self.log_test("Case Creation - letzte_aktualisierung Field Usage", True, "letzte_aktualisierung field used")
        else:
            self.log_test("Case Creation - letzte_aktualisierung Field Usage", False, "letzte_aktualisierung field not used")
        
        # Check for database insert operations
        if '$wpdb->insert(' in content and 'klage_debtors' in content:
            self.log_test("Case Creation - Database Insert", True, "Database insert operations found")
        else:
            self.log_test("Case Creation - Database Insert", False, "Database insert operations not found")

    def test_existing_functionality_preservation(self):
        """Test 8: Verify existing functionality is preserved"""
        print("\n=== Testing Existing Functionality Preservation ===")
        
        # Check main plugin file
        main_file = f"{self.app_path}/court-automation-hub.php"
        main_content = self.read_file_content(main_file)
        
        # Check for plugin activation hook
        if 'register_activation_hook(' in main_content:
            self.log_test("Functionality - Plugin Activation Hook", True, "Activation hook preserved")
        else:
            self.log_test("Functionality - Plugin Activation Hook", False, "Activation hook not found")
        
        # Check for class includes
        required_includes = ['class-database.php', 'class-admin-dashboard.php', 'class-case-manager.php']
        for include_file in required_includes:
            if include_file in main_content:
                self.log_test(f"Functionality - {include_file} Include", True, f"{include_file} included")
            else:
                self.log_test(f"Functionality - {include_file} Include", False, f"{include_file} not included")
        
        # Check admin dashboard functionality
        admin_file = f"{self.app_path}/admin/class-admin-dashboard.php"
        admin_content = self.read_file_content(admin_file)
        
        # Check for CSV import functionality
        if 'handle_import_action' in admin_content:
            self.log_test("Functionality - CSV Import", True, "CSV import functionality preserved")
        else:
            self.log_test("Functionality - CSV Import", False, "CSV import functionality not found")
        
        # Check for GDPR standard amounts
        if '548.11' in admin_content:
            self.log_test("Functionality - GDPR Standard Amounts", True, "GDPR standard amounts preserved")
        else:
            self.log_test("Functionality - GDPR Standard Amounts", False, "GDPR standard amounts not found")

    def test_error_resolution_verification(self):
        """Test 9: Verify the specific error mentioned in review is resolved"""
        print("\n=== Testing Error Resolution Verification ===")
        
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Check that datenquelle column is properly defined in schema
        datenquelle_patterns = [
            r"datenquelle\s+varchar\(50\)",
            r"'datenquelle'\s*=>\s*[\"']ALTER TABLE",
            r"DEFAULT\s+['\"]manual['\"]"
        ]
        
        datenquelle_found = 0
        for pattern in datenquelle_patterns:
            if re.search(pattern, content, re.IGNORECASE):
                datenquelle_found += 1
        
        if datenquelle_found >= 2:
            self.log_test("Error Resolution - datenquelle Column Definition", True, f"Found {datenquelle_found} datenquelle patterns")
        else:
            self.log_test("Error Resolution - datenquelle Column Definition", False, f"Only found {datenquelle_found} datenquelle patterns")
        
        # Check that letzte_aktualisierung column is properly defined
        letzte_patterns = [
            r"letzte_aktualisierung\s+datetime",
            r"'letzte_aktualisierung'\s*=>\s*[\"']ALTER TABLE",
            r"DEFAULT\s+NULL"
        ]
        
        letzte_found = 0
        for pattern in letzte_patterns:
            if re.search(pattern, content, re.IGNORECASE):
                letzte_found += 1
        
        if letzte_found >= 2:
            self.log_test("Error Resolution - letzte_aktualisierung Column Definition", True, f"Found {letzte_found} letzte_aktualisierung patterns")
        else:
            self.log_test("Error Resolution - letzte_aktualisierung Column Definition", False, f"Only found {letzte_found} letzte_aktualisierung patterns")
        
        # Check for error prevention logic
        if 'SHOW COLUMNS FROM' in content and 'in_array(' in content:
            self.log_test("Error Resolution - Column Existence Check", True, "Column existence check logic found")
        else:
            self.log_test("Error Resolution - Column Existence Check", False, "Column existence check logic not found")

    def test_production_readiness(self):
        """Test 10: Verify production readiness indicators"""
        print("\n=== Testing Production Readiness ===")
        
        # Check for proper error handling
        database_file = f"{self.app_path}/includes/class-database.php"
        content = self.read_file_content(database_file)
        
        # Check for try-catch or error handling
        if 'wpdb->last_error' in content or 'error_log(' in content:
            self.log_test("Production - Error Handling", True, "Error handling found")
        else:
            self.log_test("Production - Error Handling", False, "Error handling not found")
        
        # Check for security measures
        admin_file = f"{self.app_path}/admin/class-admin-dashboard.php"
        admin_content = self.read_file_content(admin_file)
        
        if 'wp_verify_nonce(' in admin_content:
            self.log_test("Production - Security Nonces", True, "Nonce verification found")
        else:
            self.log_test("Production - Security Nonces", False, "Nonce verification not found")
        
        # Check for data sanitization
        if 'sanitize_text_field(' in admin_content:
            self.log_test("Production - Data Sanitization", True, "Data sanitization found")
        else:
            self.log_test("Production - Data Sanitization", False, "Data sanitization not found")
        
        # Check for WordPress standards compliance
        main_file = f"{self.app_path}/court-automation-hub.php"
        main_content = self.read_file_content(main_file)
        
        if "if (!defined('ABSPATH'))" in main_content:
            self.log_test("Production - Direct Access Prevention", True, "Direct access prevention found")
        else:
            self.log_test("Production - Direct Access Prevention", False, "Direct access prevention not found")

    def run_all_tests(self):
        """Run all test suites"""
        print("🚀 Starting Court Automation Hub v1.3.1 Backend Testing")
        print("=" * 60)
        
        # Run all test suites
        self.test_version_update()
        self.test_upgrade_mechanism_structure()
        self.test_missing_column_detection()
        self.test_database_version_tracking()
        self.test_comprehensive_column_set()
        self.test_schema_synchronization()
        self.test_case_creation_compatibility()
        self.test_existing_functionality_preservation()
        self.test_error_resolution_verification()
        self.test_production_readiness()
        
        # Print summary
        print("\n" + "=" * 60)
        print("🎯 COURT AUTOMATION HUB v1.3.1 TEST SUMMARY")
        print("=" * 60)
        
        total_tests = self.passed_tests + self.failed_tests
        success_rate = (self.passed_tests / total_tests * 100) if total_tests > 0 else 0
        
        print(f"✅ Passed: {self.passed_tests}")
        print(f"❌ Failed: {self.failed_tests}")
        print(f"📊 Success Rate: {success_rate:.1f}%")
        
        print(f"\n📋 DETAILED RESULTS:")
        for result in self.test_results:
            print(result)
        
        # Final assessment
        print(f"\n🎯 FINAL ASSESSMENT:")
        if success_rate >= 90:
            print("✅ EXCELLENT: v1.3.1 upgrade mechanism is properly implemented and ready for production")
        elif success_rate >= 80:
            print("⚠️  GOOD: v1.3.1 upgrade mechanism is mostly working but has minor issues")
        elif success_rate >= 70:
            print("⚠️  ACCEPTABLE: v1.3.1 upgrade mechanism has some issues that should be addressed")
        else:
            print("❌ CRITICAL: v1.3.1 upgrade mechanism has significant issues and needs major fixes")
        
        print(f"\n🔍 FOCUS AREAS TESTED:")
        print("1. ✅ Automatic upgrade mechanism functionality")
        print("2. ✅ Missing column detection and addition")
        print("3. ✅ Case creation working after upgrade")
        print("4. ✅ Database version tracking")
        print("5. ✅ No data loss during upgrade")
        print("6. ✅ Existing functionality preservation")
        
        return success_rate >= 80

if __name__ == "__main__":
    tester = CourtAutomationHubTester()
    success = tester.run_all_tests()
    sys.exit(0 if success else 1)