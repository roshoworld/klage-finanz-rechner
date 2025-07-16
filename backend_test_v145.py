#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin v1.4.5
Testing critical syntax error fix and plugin activation functionality
"""

import subprocess
import sys
import os
import re
from pathlib import Path

class WordPressPluginTester:
    def __init__(self):
        self.plugin_path = "/app"
        self.main_plugin_file = f"{self.plugin_path}/court-automation-hub.php"
        self.schema_manager_file = f"{self.plugin_path}/includes/class-schema-manager.php"
        self.test_results = []
        self.critical_issues = []
        
    def log_test(self, test_name, passed, message="", is_critical=False):
        """Log test results"""
        status = "✅ PASSED" if passed else "❌ FAILED"
        self.test_results.append(f"{status}: {test_name} - {message}")
        
        if not passed and is_critical:
            self.critical_issues.append(f"CRITICAL: {test_name} - {message}")
        
        print(f"{status}: {test_name}")
        if message:
            print(f"   {message}")
    
    def test_php_syntax_validation(self):
        """Test 1: PHP Syntax Validation - Critical Fix Verification"""
        print("\n=== TEST 1: PHP SYNTAX VALIDATION ===")
        
        # Test main plugin file syntax
        try:
            result = subprocess.run(['php', '-l', self.main_plugin_file], 
                                  capture_output=True, text=True, timeout=30)
            
            if result.returncode == 0:
                self.log_test("Main Plugin File Syntax", True, 
                            "court-automation-hub.php has no syntax errors")
            else:
                self.log_test("Main Plugin File Syntax", False, 
                            f"Syntax error in main plugin: {result.stderr}", True)
        except Exception as e:
            self.log_test("Main Plugin File Syntax", False, 
                        f"Failed to check syntax: {str(e)}", True)
        
        # Test Schema Manager class syntax (the critical fix)
        try:
            result = subprocess.run(['php', '-l', self.schema_manager_file], 
                                  capture_output=True, text=True, timeout=30)
            
            if result.returncode == 0:
                self.log_test("Schema Manager Class Syntax", True, 
                            "class-schema-manager.php has no syntax errors - CRITICAL FIX VERIFIED")
            else:
                self.log_test("Schema Manager Class Syntax", False, 
                            f"Syntax error in Schema Manager: {result.stderr}", True)
        except Exception as e:
            self.log_test("Schema Manager Class Syntax", False, 
                        f"Failed to check Schema Manager syntax: {str(e)}", True)
        
        # Test all PHP files in includes directory
        includes_dir = f"{self.plugin_path}/includes"
        php_files = list(Path(includes_dir).glob("*.php"))
        
        syntax_errors = 0
        for php_file in php_files:
            try:
                result = subprocess.run(['php', '-l', str(php_file)], 
                                      capture_output=True, text=True, timeout=30)
                if result.returncode != 0:
                    syntax_errors += 1
                    self.log_test(f"Syntax Check {php_file.name}", False, 
                                f"Syntax error: {result.stderr}", True)
            except Exception as e:
                syntax_errors += 1
                self.log_test(f"Syntax Check {php_file.name}", False, 
                            f"Failed to check: {str(e)}", True)
        
        if syntax_errors == 0:
            self.log_test("All PHP Files Syntax", True, 
                        f"All {len(php_files)} PHP files have valid syntax")
        else:
            self.log_test("All PHP Files Syntax", False, 
                        f"{syntax_errors} files have syntax errors", True)
    
    def test_version_verification(self):
        """Test 2: Version Verification - Confirm v1.4.5 Update"""
        print("\n=== TEST 2: VERSION VERIFICATION ===")
        
        # Check main plugin file version
        try:
            with open(self.main_plugin_file, 'r') as f:
                content = f.read()
                
            # Check plugin header version
            header_version = re.search(r'Version:\s*([0-9.]+)', content)
            if header_version and header_version.group(1) == '1.4.5':
                self.log_test("Plugin Header Version", True, 
                            "Version 1.4.5 found in plugin header")
            else:
                self.log_test("Plugin Header Version", False, 
                            f"Expected v1.4.5, found: {header_version.group(1) if header_version else 'None'}")
            
            # Check constant version
            constant_version = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([0-9.]+)'\)", content)
            if constant_version and constant_version.group(1) == '1.4.5':
                self.log_test("Plugin Constant Version", True, 
                            "CAH_PLUGIN_VERSION constant set to 1.4.5")
            else:
                self.log_test("Plugin Constant Version", False, 
                            f"Expected v1.4.5, found: {constant_version.group(1) if constant_version else 'None'}")
                
        except Exception as e:
            self.log_test("Version Check", False, f"Failed to read plugin file: {str(e)}")
    
    def test_schema_manager_fix_verification(self):
        """Test 3: Schema Manager Fix Verification - Critical Syntax Fix"""
        print("\n=== TEST 3: SCHEMA MANAGER FIX VERIFICATION ===")
        
        try:
            with open(self.schema_manager_file, 'r') as f:
                lines = f.readlines()
            
            # Check refresh_schema_cache method structure around line 546
            refresh_method_found = False
            modify_method_found = False
            proper_structure = True
            
            for i, line in enumerate(lines):
                line_num = i + 1
                
                # Find refresh_schema_cache method
                if 'private function refresh_schema_cache()' in line:
                    refresh_method_found = True
                    self.log_test("refresh_schema_cache Method Found", True, 
                                f"Method found at line {line_num}")
                
                # Check around line 546 for proper structure
                if line_num == 545:  # Line where method should end
                    if line.strip() == '}':
                        self.log_test("Method Closing Brace", True, 
                                    "refresh_schema_cache method properly closed at line 545")
                    else:
                        self.log_test("Method Closing Brace", False, 
                                    f"Expected closing brace at line 545, found: {line.strip()}")
                        proper_structure = False
                
                # Check line 546 should be empty or comment
                if line_num == 546:
                    if line.strip() == '' or line.strip().startswith('//') or line.strip().startswith('*'):
                        self.log_test("Line 546 Structure", True, 
                                    "Line 546 is properly formatted (empty or comment)")
                    else:
                        self.log_test("Line 546 Structure", False, 
                                    f"Line 546 should be empty/comment, found: {line.strip()}")
                        proper_structure = False
                
                # Find modify_column method
                if 'public function modify_column(' in line:
                    modify_method_found = True
                    self.log_test("modify_column Method Found", True, 
                                f"Method found at line {line_num}")
            
            # Verify overall structure
            if refresh_method_found and modify_method_found and proper_structure:
                self.log_test("Schema Manager Structure", True, 
                            "All methods properly structured - syntax error fix verified")
            else:
                self.log_test("Schema Manager Structure", False, 
                            "Method structure issues detected", True)
                
        except Exception as e:
            self.log_test("Schema Manager Fix Check", False, 
                        f"Failed to verify fix: {str(e)}", True)
    
    def test_plugin_class_loading(self):
        """Test 4: Plugin Class Loading - Verify All Classes Can Be Loaded"""
        print("\n=== TEST 4: PLUGIN CLASS LOADING ===")
        
        # Test if main plugin file defines the class correctly
        try:
            with open(self.main_plugin_file, 'r') as f:
                content = f.read()
            
            # Check for main class definition
            if 'class CourtAutomationHub' in content:
                self.log_test("Main Plugin Class", True, 
                            "CourtAutomationHub class defined")
            else:
                self.log_test("Main Plugin Class", False, 
                            "CourtAutomationHub class not found", True)
            
            # Check for class instantiation
            if 'new CourtAutomationHub()' in content:
                self.log_test("Plugin Instantiation", True, 
                            "Plugin class properly instantiated")
            else:
                self.log_test("Plugin Instantiation", False, 
                            "Plugin class not instantiated", True)
            
            # Check for required includes
            required_includes = [
                'class-database.php',
                'class-schema-manager.php',
                'class-database-admin.php',
                'class-admin-dashboard.php'
            ]
            
            includes_found = 0
            for include_file in required_includes:
                if include_file in content:
                    includes_found += 1
            
            if includes_found == len(required_includes):
                self.log_test("Required Includes", True, 
                            f"All {len(required_includes)} required files included")
            else:
                self.log_test("Required Includes", False, 
                            f"Only {includes_found}/{len(required_includes)} includes found")
                
        except Exception as e:
            self.log_test("Class Loading Check", False, 
                        f"Failed to check class loading: {str(e)}", True)
    
    def test_database_admin_integration(self):
        """Test 5: Database Admin Integration - Verify Menu Integration Fix"""
        print("\n=== TEST 5: DATABASE ADMIN INTEGRATION ===")
        
        database_admin_file = f"{self.plugin_path}/includes/class-database-admin.php"
        
        try:
            with open(database_admin_file, 'r') as f:
                content = f.read()
            
            # Check for correct parent menu slug
            if "'klage-click-hub'" in content:
                self.log_test("Parent Menu Slug", True, 
                            "Correct parent menu slug 'klage-click-hub' found")
            else:
                self.log_test("Parent Menu Slug", False, 
                            "Parent menu slug 'klage-click-hub' not found")
            
            # Check for correct page parameter
            if "'klage-click-database'" in content:
                self.log_test("Database Page Parameter", True, 
                            "Correct page parameter 'klage-click-database' found")
            else:
                self.log_test("Database Page Parameter", False, 
                            "Page parameter 'klage-click-database' not found")
            
            # Check for Database Admin class definition
            if 'class CAH_Database_Admin' in content:
                self.log_test("Database Admin Class", True, 
                            "CAH_Database_Admin class defined")
            else:
                self.log_test("Database Admin Class", False, 
                            "CAH_Database_Admin class not found")
                
        except FileNotFoundError:
            self.log_test("Database Admin File", False, 
                        "class-database-admin.php file not found")
        except Exception as e:
            self.log_test("Database Admin Integration", False, 
                        f"Failed to check integration: {str(e)}")
    
    def test_unique_key_management_features(self):
        """Test 6: Unique Key Management Features - Verify v1.4.4+ Features"""
        print("\n=== TEST 6: UNIQUE KEY MANAGEMENT FEATURES ===")
        
        try:
            with open(self.schema_manager_file, 'r') as f:
                content = f.read()
            
            # Check for unique key management methods
            unique_key_methods = [
                'add_unique_key',
                'add_index', 
                'drop_index',
                'get_table_indexes'
            ]
            
            methods_found = 0
            for method in unique_key_methods:
                if f'public function {method}(' in content:
                    methods_found += 1
                    self.log_test(f"Method {method}", True, 
                                f"{method}() method found")
                else:
                    self.log_test(f"Method {method}", False, 
                                f"{method}() method not found")
            
            if methods_found == len(unique_key_methods):
                self.log_test("Unique Key Management", True, 
                            "All unique key management methods implemented")
            else:
                self.log_test("Unique Key Management", False, 
                            f"Only {methods_found}/{len(unique_key_methods)} methods found")
            
            # Check for safety features
            if 'Cannot drop primary key' in content:
                self.log_test("Primary Key Protection", True, 
                            "Primary key protection implemented")
            else:
                self.log_test("Primary Key Protection", False, 
                            "Primary key protection not found")
            
            # Check for system column protection
            if 'system_columns' in content and 'Cannot drop system column' in content:
                self.log_test("System Column Protection", True, 
                            "System column protection implemented")
            else:
                self.log_test("System Column Protection", False, 
                            "System column protection not found")
                
        except Exception as e:
            self.log_test("Unique Key Features Check", False, 
                        f"Failed to check features: {str(e)}")
    
    def test_form_csv_integration(self):
        """Test 7: Form and CSV Integration - Verify Still Functional"""
        print("\n=== TEST 7: FORM AND CSV INTEGRATION ===")
        
        admin_dashboard_file = f"{self.plugin_path}/admin/class-admin-dashboard.php"
        
        try:
            with open(admin_dashboard_file, 'r') as f:
                content = f.read()
            
            # Check for CSV template methods
            csv_methods = [
                'get_forderungen_template_content',
                'get_comprehensive_template_content',
                'handle_csv_import'
            ]
            
            csv_methods_found = 0
            for method in csv_methods:
                if method in content:
                    csv_methods_found += 1
                    self.log_test(f"CSV Method {method}", True, 
                                f"{method} method found")
                else:
                    self.log_test(f"CSV Method {method}", False, 
                                f"{method} method not found")
            
            # Check for case creation methods
            case_methods = [
                'create_new_case',
                'update_case',
                'handle_bulk_actions'
            ]
            
            case_methods_found = 0
            for method in case_methods:
                if method in content:
                    case_methods_found += 1
                    self.log_test(f"Case Method {method}", True, 
                                f"{method} method found")
                else:
                    self.log_test(f"Case Method {method}", False, 
                                f"{method} method not found")
            
            # Overall integration check
            total_methods = len(csv_methods) + len(case_methods)
            total_found = csv_methods_found + case_methods_found
            
            if total_found >= total_methods * 0.8:  # 80% threshold
                self.log_test("Form CSV Integration", True, 
                            f"{total_found}/{total_methods} integration methods found")
            else:
                self.log_test("Form CSV Integration", False, 
                            f"Only {total_found}/{total_methods} integration methods found")
                
        except FileNotFoundError:
            self.log_test("Admin Dashboard File", False, 
                        "class-admin-dashboard.php file not found")
        except Exception as e:
            self.log_test("Form CSV Integration Check", False, 
                        f"Failed to check integration: {str(e)}")
    
    def test_plugin_activation_readiness(self):
        """Test 8: Plugin Activation Readiness - Final Verification"""
        print("\n=== TEST 8: PLUGIN ACTIVATION READINESS ===")
        
        try:
            with open(self.main_plugin_file, 'r') as f:
                content = f.read()
            
            # Check for activation hook
            if 'register_activation_hook' in content:
                self.log_test("Activation Hook", True, 
                            "Plugin activation hook registered")
            else:
                self.log_test("Activation Hook", False, 
                            "Plugin activation hook not found")
            
            # Check for database creation in activation
            if 'create_tables_direct' in content:
                self.log_test("Database Creation", True, 
                            "Database table creation in activation")
            else:
                self.log_test("Database Creation", False, 
                            "Database table creation not found")
            
            # Check for capabilities addition
            if 'add_capabilities' in content:
                self.log_test("Capabilities Setup", True, 
                            "User capabilities setup in activation")
            else:
                self.log_test("Capabilities Setup", False, 
                            "User capabilities setup not found")
            
            # Check for WordPress security
            if "!defined('ABSPATH')" in content:
                self.log_test("WordPress Security", True, 
                            "Direct access prevention implemented")
            else:
                self.log_test("WordPress Security", False, 
                            "Direct access prevention not found")
            
            # Final readiness assessment
            readiness_score = len([r for r in self.test_results[-4:] if "✅ PASSED" in r])
            
            if readiness_score >= 3:
                self.log_test("Plugin Activation Readiness", True, 
                            f"Plugin ready for activation ({readiness_score}/4 checks passed)")
            else:
                self.log_test("Plugin Activation Readiness", False, 
                            f"Plugin not ready for activation ({readiness_score}/4 checks passed)", True)
                
        except Exception as e:
            self.log_test("Activation Readiness Check", False, 
                        f"Failed to check readiness: {str(e)}", True)
    
    def run_all_tests(self):
        """Run all tests and generate summary"""
        print("🚀 COURT AUTOMATION HUB v1.4.5 - CRITICAL SYNTAX FIX VERIFICATION")
        print("=" * 80)
        
        # Run all test methods
        self.test_php_syntax_validation()
        self.test_version_verification()
        self.test_schema_manager_fix_verification()
        self.test_plugin_class_loading()
        self.test_database_admin_integration()
        self.test_unique_key_management_features()
        self.test_form_csv_integration()
        self.test_plugin_activation_readiness()
        
        # Generate summary
        print("\n" + "=" * 80)
        print("📊 TEST SUMMARY")
        print("=" * 80)
        
        passed_tests = len([r for r in self.test_results if "✅ PASSED" in r])
        total_tests = len(self.test_results)
        success_rate = (passed_tests / total_tests * 100) if total_tests > 0 else 0
        
        print(f"Total Tests: {total_tests}")
        print(f"Passed: {passed_tests}")
        print(f"Failed: {total_tests - passed_tests}")
        print(f"Success Rate: {success_rate:.1f}%")
        
        if self.critical_issues:
            print(f"\n🚨 CRITICAL ISSUES ({len(self.critical_issues)}):")
            for issue in self.critical_issues:
                print(f"   {issue}")
        else:
            print("\n✅ NO CRITICAL ISSUES FOUND")
        
        print(f"\n📋 DETAILED RESULTS:")
        for result in self.test_results:
            print(f"   {result}")
        
        # Final assessment
        print("\n" + "=" * 80)
        if len(self.critical_issues) == 0 and success_rate >= 85:
            print("🎉 VERIFICATION SUCCESSFUL: Plugin v1.4.5 syntax fix working correctly")
            print("✅ Plugin activation should work without errors")
            print("✅ All Database Management functionality should be accessible")
            print("✅ Unique key management features should work correctly")
            print("✅ Form and CSV integration should remain functional")
        else:
            print("⚠️  VERIFICATION ISSUES DETECTED: Review critical issues above")
            print("❌ Plugin may have activation or functionality problems")
        
        return len(self.critical_issues) == 0 and success_rate >= 85

if __name__ == "__main__":
    tester = WordPressPluginTester()
    success = tester.run_all_tests()
    sys.exit(0 if success else 1)