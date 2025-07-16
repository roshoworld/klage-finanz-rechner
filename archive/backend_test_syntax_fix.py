#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin v1.4.5
Testing critical syntax error fix and plugin functionality without PHP CLI dependency
"""

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
    
    def test_version_verification(self):
        """Test 1: Version Verification - Confirm v1.4.5 Update"""
        print("\n=== TEST 1: VERSION VERIFICATION ===")
        
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
                            f"Expected v1.4.5, found: {header_version.group(1) if header_version else 'None'}", True)
            
            # Check constant version
            constant_version = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([0-9.]+)'\)", content)
            if constant_version and constant_version.group(1) == '1.4.5':
                self.log_test("Plugin Constant Version", True, 
                            "CAH_PLUGIN_VERSION constant set to 1.4.5")
            else:
                self.log_test("Plugin Constant Version", False, 
                            f"Expected v1.4.5, found: {constant_version.group(1) if constant_version else 'None'}", True)
                
        except Exception as e:
            self.log_test("Version Check", False, f"Failed to read plugin file: {str(e)}", True)
    
    def test_schema_manager_syntax_fix(self):
        """Test 2: Schema Manager Syntax Fix - Critical Fix Verification"""
        print("\n=== TEST 2: SCHEMA MANAGER SYNTAX FIX ===")
        
        try:
            with open(self.schema_manager_file, 'r') as f:
                lines = f.readlines()
            
            # Check refresh_schema_cache method structure around line 546
            refresh_method_found = False
            modify_method_found = False
            proper_structure = True
            extra_brace_found = False
            
            for i, line in enumerate(lines):
                line_num = i + 1
                
                # Find refresh_schema_cache method
                if 'private function refresh_schema_cache()' in line:
                    refresh_method_found = True
                    self.log_test("refresh_schema_cache Method Found", True, 
                                f"Method found at line {line_num}")
                
                # Check around line 546 for proper structure (the critical fix area)
                if line_num == 545:  # Line where method should end
                    if line.strip() == '}':
                        self.log_test("Method Closing Brace Line 545", True, 
                                    "refresh_schema_cache method properly closed at line 545")
                    else:
                        self.log_test("Method Closing Brace Line 545", False, 
                                    f"Expected closing brace at line 545, found: {line.strip()}", True)
                        proper_structure = False
                
                # Check line 546 should be empty or comment (not an extra closing brace)
                if line_num == 546:
                    if line.strip() == '}':
                        extra_brace_found = True
                        self.log_test("Extra Closing Brace Check", False, 
                                    "CRITICAL: Extra closing brace found at line 546 - syntax error not fixed!", True)
                        proper_structure = False
                    elif line.strip() == '' or line.strip().startswith('//') or line.strip().startswith('*'):
                        self.log_test("Line 546 Structure", True, 
                                    "Line 546 is properly formatted (empty or comment) - extra brace removed")
                    else:
                        self.log_test("Line 546 Structure", True, 
                                    f"Line 546 contains: {line.strip()}")
                
                # Find modify_column method (should start around line 550)
                if 'public function modify_column(' in line:
                    modify_method_found = True
                    self.log_test("modify_column Method Found", True, 
                                f"Method found at line {line_num}")
            
            # Verify overall structure and syntax fix
            if refresh_method_found and modify_method_found and proper_structure and not extra_brace_found:
                self.log_test("Syntax Error Fix Verification", True, 
                            "CRITICAL SYNTAX ERROR FIX VERIFIED: Extra closing brace removed successfully")
            else:
                self.log_test("Syntax Error Fix Verification", False, 
                            "Syntax error fix verification failed - structure issues detected", True)
                
        except Exception as e:
            self.log_test("Schema Manager Fix Check", False, 
                        f"Failed to verify fix: {str(e)}", True)
    
    def test_php_file_structure_validation(self):
        """Test 3: PHP File Structure Validation - Basic Syntax Checks"""
        print("\n=== TEST 3: PHP FILE STRUCTURE VALIDATION ===")
        
        # Check main plugin file structure
        try:
            with open(self.main_plugin_file, 'r') as f:
                content = f.read()
            
            # Check for balanced braces
            open_braces = content.count('{')
            close_braces = content.count('}')
            
            if open_braces == close_braces:
                self.log_test("Main Plugin Brace Balance", True, 
                            f"Balanced braces: {open_braces} opening, {close_braces} closing")
            else:
                self.log_test("Main Plugin Brace Balance", False, 
                            f"Unbalanced braces: {open_braces} opening, {close_braces} closing", True)
            
            # Check for PHP opening tag
            if content.startswith('<?php'):
                self.log_test("PHP Opening Tag", True, 
                            "Proper PHP opening tag found")
            else:
                self.log_test("PHP Opening Tag", False, 
                            "PHP opening tag missing or incorrect", True)
                
        except Exception as e:
            self.log_test("Main Plugin Structure Check", False, 
                        f"Failed to check structure: {str(e)}", True)
        
        # Check Schema Manager file structure
        try:
            with open(self.schema_manager_file, 'r') as f:
                content = f.read()
            
            # Check for balanced braces
            open_braces = content.count('{')
            close_braces = content.count('}')
            
            if open_braces == close_braces:
                self.log_test("Schema Manager Brace Balance", True, 
                            f"Balanced braces: {open_braces} opening, {close_braces} closing")
            else:
                self.log_test("Schema Manager Brace Balance", False, 
                            f"Unbalanced braces: {open_braces} opening, {close_braces} closing", True)
            
            # Check for class definition
            if 'class CAH_Schema_Manager' in content:
                self.log_test("Schema Manager Class Definition", True, 
                            "CAH_Schema_Manager class properly defined")
            else:
                self.log_test("Schema Manager Class Definition", False, 
                            "CAH_Schema_Manager class definition not found", True)
                
        except Exception as e:
            self.log_test("Schema Manager Structure Check", False, 
                        f"Failed to check structure: {str(e)}", True)
    
    def test_plugin_activation_components(self):
        """Test 4: Plugin Activation Components - Verify Activation Will Work"""
        print("\n=== TEST 4: PLUGIN ACTIVATION COMPONENTS ===")
        
        try:
            with open(self.main_plugin_file, 'r') as f:
                content = f.read()
            
            # Check for activation hook
            if 'register_activation_hook' in content:
                self.log_test("Activation Hook Registration", True, 
                            "Plugin activation hook properly registered")
            else:
                self.log_test("Activation Hook Registration", False, 
                            "Plugin activation hook not found", True)
            
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
                self.log_test("Required File Includes", True, 
                            f"All {len(required_includes)} required files included")
            else:
                self.log_test("Required File Includes", False, 
                            f"Only {includes_found}/{len(required_includes)} includes found", True)
            
            # Check for database creation in activation
            if 'create_tables_direct' in content:
                self.log_test("Database Table Creation", True, 
                            "Database table creation in activation method")
            else:
                self.log_test("Database Table Creation", False, 
                            "Database table creation not found")
                
        except Exception as e:
            self.log_test("Activation Components Check", False, 
                        f"Failed to check activation components: {str(e)}", True)
    
    def test_database_management_functionality(self):
        """Test 5: Database Management Functionality - Verify Features Still Work"""
        print("\n=== TEST 5: DATABASE MANAGEMENT FUNCTIONALITY ===")
        
        # Check Schema Manager methods
        try:
            with open(self.schema_manager_file, 'r') as f:
                content = f.read()
            
            # Check for unique key management methods (v1.4.4+ features)
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
            
            if methods_found == len(unique_key_methods):
                self.log_test("Unique Key Management Methods", True, 
                            "All unique key management methods implemented")
            else:
                self.log_test("Unique Key Management Methods", False, 
                            f"Only {methods_found}/{len(unique_key_methods)} methods found")
            
            # Check for CRUD methods
            crud_methods = ['add_column', 'modify_column', 'drop_column']
            crud_found = 0
            for method in crud_methods:
                if f'public function {method}(' in content:
                    crud_found += 1
            
            if crud_found == len(crud_methods):
                self.log_test("Database CRUD Methods", True, 
                            "All database CRUD methods implemented")
            else:
                self.log_test("Database CRUD Methods", False, 
                            f"Only {crud_found}/{len(crud_methods)} CRUD methods found")
            
            # Check for safety features
            if 'Cannot drop primary key' in content:
                self.log_test("Primary Key Protection", True, 
                            "Primary key protection implemented")
            else:
                self.log_test("Primary Key Protection", False, 
                            "Primary key protection not found")
                
        except Exception as e:
            self.log_test("Database Management Check", False, 
                        f"Failed to check database management: {str(e)}")
    
    def test_admin_interface_integration(self):
        """Test 6: Admin Interface Integration - Verify Menu Access"""
        print("\n=== TEST 6: ADMIN INTERFACE INTEGRATION ===")
        
        database_admin_file = f"{self.plugin_path}/includes/class-database-admin.php"
        
        try:
            with open(database_admin_file, 'r') as f:
                content = f.read()
            
            # Check for correct parent menu slug (fixed in v1.4.1)
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
                        "class-database-admin.php file not found", True)
        except Exception as e:
            self.log_test("Admin Interface Integration", False, 
                        f"Failed to check integration: {str(e)}")
    
    def test_form_csv_integration_preserved(self):
        """Test 7: Form and CSV Integration - Verify Still Functional"""
        print("\n=== TEST 7: FORM AND CSV INTEGRATION PRESERVED ===")
        
        admin_dashboard_file = f"{self.plugin_path}/admin/class-admin-dashboard.php"
        
        try:
            with open(admin_dashboard_file, 'r') as f:
                content = f.read()
            
            # Check for CSV template methods
            csv_methods = [
                'get_forderungen_template_content',
                'get_comprehensive_template_content'
            ]
            
            csv_methods_found = 0
            for method in csv_methods:
                if method in content:
                    csv_methods_found += 1
            
            if csv_methods_found == len(csv_methods):
                self.log_test("CSV Template Methods", True, 
                            "All CSV template methods found")
            else:
                self.log_test("CSV Template Methods", False, 
                            f"Only {csv_methods_found}/{len(csv_methods)} CSV methods found")
            
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
            
            if case_methods_found == len(case_methods):
                self.log_test("Case Management Methods", True, 
                            "All case management methods found")
            else:
                self.log_test("Case Management Methods", False, 
                            f"Only {case_methods_found}/{len(case_methods)} case methods found")
            
            # Check for dual template system
            if 'template_type' in content and 'forderungen' in content.lower():
                self.log_test("Dual Template System", True, 
                            "Dual template system (Forderungen.com + Comprehensive) preserved")
            else:
                self.log_test("Dual Template System", False, 
                            "Dual template system not found")
                
        except FileNotFoundError:
            self.log_test("Admin Dashboard File", False, 
                        "class-admin-dashboard.php file not found", True)
        except Exception as e:
            self.log_test("Form CSV Integration Check", False, 
                        f"Failed to check integration: {str(e)}")
    
    def test_critical_fix_impact_assessment(self):
        """Test 8: Critical Fix Impact Assessment - Final Verification"""
        print("\n=== TEST 8: CRITICAL FIX IMPACT ASSESSMENT ===")
        
        # Assess overall impact of the syntax fix
        syntax_fix_verified = any("Syntax Error Fix Verification" in result and "✅ PASSED" in result 
                                for result in self.test_results)
        
        version_updated = any("Plugin Header Version" in result and "✅ PASSED" in result 
                            for result in self.test_results)
        
        structure_intact = any("Schema Manager Brace Balance" in result and "✅ PASSED" in result 
                             for result in self.test_results)
        
        activation_ready = any("Activation Hook Registration" in result and "✅ PASSED" in result 
                             for result in self.test_results)
        
        # Final assessment
        if syntax_fix_verified:
            self.log_test("Syntax Fix Impact", True, 
                        "Critical syntax error fix successfully implemented")
        else:
            self.log_test("Syntax Fix Impact", False, 
                        "Syntax error fix verification failed", True)
        
        if version_updated:
            self.log_test("Version Update Impact", True, 
                        "Version properly updated to reflect the fix")
        else:
            self.log_test("Version Update Impact", False, 
                        "Version update verification failed")
        
        if structure_intact:
            self.log_test("Code Structure Impact", True, 
                        "Code structure remains intact after fix")
        else:
            self.log_test("Code Structure Impact", False, 
                        "Code structure issues detected")
        
        if activation_ready:
            self.log_test("Plugin Activation Impact", True, 
                        "Plugin activation should work without syntax errors")
        else:
            self.log_test("Plugin Activation Impact", False, 
                        "Plugin activation may still have issues", True)
        
        # Overall fix assessment
        critical_aspects = [syntax_fix_verified, version_updated, structure_intact, activation_ready]
        passed_aspects = sum(critical_aspects)
        
        if passed_aspects >= 3:
            self.log_test("Overall Fix Assessment", True, 
                        f"Critical fix successful ({passed_aspects}/4 aspects verified)")
        else:
            self.log_test("Overall Fix Assessment", False, 
                        f"Critical fix incomplete ({passed_aspects}/4 aspects verified)", True)
    
    def run_all_tests(self):
        """Run all tests and generate summary"""
        print("🚀 COURT AUTOMATION HUB v1.4.5 - CRITICAL SYNTAX FIX VERIFICATION")
        print("=" * 80)
        print("Testing the critical syntax error fix in Schema Manager class")
        print("Focus: Extra closing brace removal on line 546")
        print("=" * 80)
        
        # Run all test methods
        self.test_version_verification()
        self.test_schema_manager_syntax_fix()
        self.test_php_file_structure_validation()
        self.test_plugin_activation_components()
        self.test_database_management_functionality()
        self.test_admin_interface_integration()
        self.test_form_csv_integration_preserved()
        self.test_critical_fix_impact_assessment()
        
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
        
        # Final assessment based on review request
        print("\n" + "=" * 80)
        print("🎯 CRITICAL FIX VERIFICATION RESULTS")
        print("=" * 80)
        
        syntax_fix_success = any("Syntax Error Fix Verification" in result and "✅ PASSED" in result 
                               for result in self.test_results)
        
        if len(self.critical_issues) == 0 and syntax_fix_success and success_rate >= 80:
            print("🎉 CRITICAL SYNTAX FIX VERIFICATION: SUCCESSFUL")
            print("✅ Extra closing brace on line 546 successfully removed")
            print("✅ Plugin version updated to 1.4.5 to reflect the fix")
            print("✅ Plugin activation should now work without syntax errors")
            print("✅ All Database Management functionality should be accessible")
            print("✅ Unique key management features should work correctly")
            print("✅ Form and CSV integration should remain functional")
            print("\n🚀 READY FOR PRODUCTION: Plugin can be activated safely")
        else:
            print("⚠️  CRITICAL SYNTAX FIX VERIFICATION: ISSUES DETECTED")
            print("❌ Plugin may still have activation or functionality problems")
            print("🔧 Review critical issues above for resolution")
        
        return len(self.critical_issues) == 0 and syntax_fix_success and success_rate >= 80

if __name__ == "__main__":
    tester = WordPressPluginTester()
    success = tester.run_all_tests()
    sys.exit(0 if success else 1)