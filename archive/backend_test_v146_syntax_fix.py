#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Version 1.4.6 Syntax Error Fix Verification
Tests the PHP syntax error fix in class-form-generator.php and core functionality.

Focus Areas:
1. Plugin activation works without syntax errors
2. No PHP syntax errors exist in the form generator class
3. Database Management system is accessible through WordPress admin
4. Core case creation functionality still works
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class SyntaxFixV146Tester:
    """Test suite specifically for verifying v1.4.6 syntax error fix"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.form_generator_file = "/app/includes/class-form-generator.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.database_admin_file = "/app/includes/class-database-admin.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all syntax fix verification tests"""
        print("🚀 Starting Version 1.4.6 Syntax Error Fix Verification Tests")
        print("=" * 70)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_php_syntax_validation()
        self.test_plugin_activation_readiness()
        self.test_form_generator_functionality()
        self.test_database_management_accessibility()
        self.test_core_case_creation_functionality()
        
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
        """Test that plugin version is updated to 1.4.6"""
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
            return version == "1.4.6"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.4.6"
        
        self.test("Plugin header version is 1.4.6", check_plugin_version)
        self.test("Plugin constant version is 1.4.6", check_constant_version)
    
    def test_php_syntax_validation(self):
        """Test PHP syntax validation for all critical files"""
        print("🔍 TESTING PHP SYNTAX VALIDATION")
        print("-" * 40)
        
        def check_form_generator_syntax():
            """Check PHP syntax in form generator class"""
            if not os.path.exists(self.form_generator_file):
                raise Exception(f"Form generator file not found: {self.form_generator_file}")
            
            try:
                # Use PHP to check syntax
                result = subprocess.run(['php', '-l', self.form_generator_file], 
                                      capture_output=True, text=True)
                
                print(f"PHP syntax check result: {result.returncode}")
                if result.returncode != 0:
                    print(f"Syntax error output: {result.stderr}")
                    return False
                
                print("✅ No syntax errors found in form generator")
                return True
            except FileNotFoundError:
                print("⚠️ PHP not available for syntax checking, checking manually...")
                return self.manual_syntax_check(self.form_generator_file)
        
        def check_main_plugin_syntax():
            """Check PHP syntax in main plugin file"""
            try:
                result = subprocess.run(['php', '-l', self.main_plugin_file], 
                                      capture_output=True, text=True)
                
                if result.returncode != 0:
                    print(f"Syntax error in main plugin: {result.stderr}")
                    return False
                
                return True
            except FileNotFoundError:
                return self.manual_syntax_check(self.main_plugin_file)
        
        def check_admin_dashboard_syntax():
            """Check PHP syntax in admin dashboard"""
            try:
                result = subprocess.run(['php', '-l', self.admin_dashboard_file], 
                                      capture_output=True, text=True)
                
                if result.returncode != 0:
                    print(f"Syntax error in admin dashboard: {result.stderr}")
                    return False
                
                return True
            except FileNotFoundError:
                return self.manual_syntax_check(self.admin_dashboard_file)
        
        def check_database_admin_syntax():
            """Check PHP syntax in database admin"""
            if not os.path.exists(self.database_admin_file):
                print("Database admin file not found, skipping...")
                return True
                
            try:
                result = subprocess.run(['php', '-l', self.database_admin_file], 
                                      capture_output=True, text=True)
                
                if result.returncode != 0:
                    print(f"Syntax error in database admin: {result.stderr}")
                    return False
                
                return True
            except FileNotFoundError:
                return self.manual_syntax_check(self.database_admin_file)
        
        self.test("Form generator PHP syntax is valid", check_form_generator_syntax)
        self.test("Main plugin PHP syntax is valid", check_main_plugin_syntax)
        self.test("Admin dashboard PHP syntax is valid", check_admin_dashboard_syntax)
        self.test("Database admin PHP syntax is valid", check_database_admin_syntax)
    
    def manual_syntax_check(self, file_path: str) -> bool:
        """Manual syntax check for basic PHP structure"""
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Check for balanced braces
        open_braces = content.count('{')
        close_braces = content.count('}')
        
        if open_braces != close_braces:
            print(f"❌ Unbalanced braces: {open_braces} open, {close_braces} close")
            return False
        
        # Check for balanced parentheses
        open_parens = content.count('(')
        close_parens = content.count(')')
        
        if open_parens != close_parens:
            print(f"❌ Unbalanced parentheses: {open_parens} open, {close_parens} close")
            return False
        
        # Check for PHP opening tag
        if not content.strip().startswith('<?php'):
            print("❌ Missing PHP opening tag")
            return False
        
        # Check for common syntax error patterns
        error_patterns = [
            r'unexpected.*variable.*\$configs',  # The specific error mentioned
            r'unexpected.*token.*public',
            r'syntax error',
            r'Parse error'
        ]
        
        for pattern in error_patterns:
            if re.search(pattern, content, re.IGNORECASE):
                print(f"❌ Found potential syntax error pattern: {pattern}")
                return False
        
        print("✅ Manual syntax check passed")
        return True
    
    def test_plugin_activation_readiness(self):
        """Test plugin activation readiness"""
        print("🔌 TESTING PLUGIN ACTIVATION READINESS")
        print("-" * 40)
        
        def check_activation_hook():
            """Check that activation hook is properly registered"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for activation hook registration
            activation_hook = 'register_activation_hook' in content
            activate_method = 'public function activate()' in content
            
            print(f"Activation hook registered: {activation_hook}")
            print(f"Activate method exists: {activate_method}")
            
            return activation_hook and activate_method
        
        def check_required_includes():
            """Check that all required files are included"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            required_includes = [
                'class-database.php',
                'class-form-generator.php',
                'class-admin-dashboard.php',
                'class-database-admin.php'
            ]
            
            found_includes = 0
            for include_file in required_includes:
                if include_file in content:
                    found_includes += 1
                    print(f"✅ Found include: {include_file}")
                else:
                    print(f"❌ Missing include: {include_file}")
            
            return found_includes >= 3
        
        def check_class_definitions():
            """Check that main classes are properly defined"""
            files_to_check = [
                (self.main_plugin_file, 'CourtAutomationHub'),
                (self.form_generator_file, 'CAH_Form_Generator'),
                (self.admin_dashboard_file, 'CAH_Admin_Dashboard')
            ]
            
            all_classes_found = True
            
            for file_path, class_name in files_to_check:
                if os.path.exists(file_path):
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    class_pattern = f'class\\s+{class_name}'
                    if re.search(class_pattern, content):
                        print(f"✅ Found class: {class_name}")
                    else:
                        print(f"❌ Missing class: {class_name}")
                        all_classes_found = False
                else:
                    print(f"❌ File not found: {file_path}")
                    all_classes_found = False
            
            return all_classes_found
        
        def check_database_creation():
            """Check that database creation is called in activation"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for database creation in activate method
            db_creation = 'create_tables' in content and 'activate()' in content
            
            return db_creation
        
        self.test("Activation hook properly registered", check_activation_hook)
        self.test("Required files are included", check_required_includes)
        self.test("Main classes are properly defined", check_class_definitions)
        self.test("Database creation in activation", check_database_creation)
    
    def test_form_generator_functionality(self):
        """Test form generator class functionality"""
        print("📝 TESTING FORM GENERATOR FUNCTIONALITY")
        print("-" * 40)
        
        def check_form_generator_methods():
            """Check that all required methods exist in form generator"""
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            required_methods = [
                'generate_form',
                'group_fields_by_category',
                'render_field_group',
                'render_field_input',
                'get_field_config'
            ]
            
            found_methods = 0
            for method in required_methods:
                if f'function {method}' in content:
                    found_methods += 1
                    print(f"✅ Found method: {method}")
                else:
                    print(f"❌ Missing method: {method}")
            
            return found_methods >= 4
        
        def check_field_type_support():
            """Check that various field types are supported"""
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            field_types = [
                'text', 'email', 'tel', 'number', 'date', 
                'textarea', 'select', 'checkbox', 'decimal'
            ]
            
            found_types = 0
            for field_type in field_types:
                if f"'{field_type}'" in content:
                    found_types += 1
            
            print(f"Found {found_types} field types")
            return found_types >= 6
        
        def check_german_labels():
            """Check that German labels are implemented"""
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            german_labels = [
                'Fall-ID', 'Status', 'Priorität', 'Mandant', 
                'Vorname', 'Nachname', 'E-Mail', 'Telefon'
            ]
            
            found_labels = 0
            for label in german_labels:
                if label in content:
                    found_labels += 1
            
            print(f"Found {found_labels} German labels")
            return found_labels >= 5
        
        def check_validation_javascript():
            """Check that form validation JavaScript is included"""
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            js_validation = 'generate_form_validation_js' in content
            jquery_usage = 'jQuery' in content
            validation_logic = 'required' in content
            
            return js_validation and jquery_usage and validation_logic
        
        self.test("Form generator methods exist", check_form_generator_methods)
        self.test("Field type support implemented", check_field_type_support)
        self.test("German labels implemented", check_german_labels)
        self.test("Form validation JavaScript included", check_validation_javascript)
    
    def test_database_management_accessibility(self):
        """Test Database Management system accessibility"""
        print("🗄️ TESTING DATABASE MANAGEMENT ACCESSIBILITY")
        print("-" * 40)
        
        def check_database_admin_class():
            """Check that database admin class exists and is properly structured"""
            if not os.path.exists(self.database_admin_file):
                print("Database admin file not found")
                return False
            
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for class definition
            class_exists = 'class CAH_Database_Admin' in content
            
            # Check for admin menu integration
            menu_integration = 'add_submenu_page' in content
            
            # Check for page parameter
            page_param = 'klage-click-database' in content
            
            print(f"Database admin class exists: {class_exists}")
            print(f"Menu integration found: {menu_integration}")
            print(f"Correct page parameter: {page_param}")
            
            return class_exists and menu_integration and page_param
        
        def check_admin_menu_integration():
            """Check admin menu integration in main plugin"""
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that database admin is initialized
            db_admin_init = 'CAH_Database_Admin' in content
            
            return db_admin_init
        
        def check_database_management_tabs():
            """Check that database management tabs are implemented"""
            if not os.path.exists(self.database_admin_file):
                return True  # Skip if file doesn't exist
            
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            expected_tabs = [
                'Schema', 'Data', 'Import', 'Export', 'Form'
            ]
            
            found_tabs = 0
            for tab in expected_tabs:
                if tab in content:
                    found_tabs += 1
            
            print(f"Found {found_tabs} database management tabs")
            return found_tabs >= 3
        
        def check_parent_menu_slug():
            """Check that correct parent menu slug is used"""
            if not os.path.exists(self.database_admin_file):
                return True
            
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Should use 'klage-click-hub' as parent menu
            correct_parent = 'klage-click-hub' in content
            
            # Should NOT use old 'court-automation-hub'
            old_parent = 'court-automation-hub' in content
            
            print(f"Correct parent menu slug: {correct_parent}")
            print(f"Old parent menu slug found: {old_parent}")
            
            return correct_parent and not old_parent
        
        self.test("Database admin class properly structured", check_database_admin_class)
        self.test("Admin menu integration in main plugin", check_admin_menu_integration)
        self.test("Database management tabs implemented", check_database_management_tabs)
        self.test("Correct parent menu slug used", check_parent_menu_slug)
    
    def test_core_case_creation_functionality(self):
        """Test core case creation functionality"""
        print("⚖️ TESTING CORE CASE CREATION FUNCTIONALITY")
        print("-" * 40)
        
        def check_case_creation_methods():
            """Check that case creation methods exist"""
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            creation_methods = [
                'create_new_case',
                'handle_case_actions',
                'render_add_case_form'
            ]
            
            found_methods = 0
            for method in creation_methods:
                if method in content:
                    found_methods += 1
                    print(f"✅ Found method: {method}")
                else:
                    print(f"❌ Missing method: {method}")
            
            return found_methods >= 2
        
        def check_database_tables_integration():
            """Check integration with database tables"""
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            required_tables = [
                'klage_cases',
                'klage_debtors',
                'klage_financial'
            ]
            
            found_tables = 0
            for table in required_tables:
                if table in content:
                    found_tables += 1
                    print(f"✅ Found table reference: {table}")
            
            return found_tables >= 2
        
        def check_gdpr_compliance():
            """Check GDPR compliance with standard amounts"""
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            gdpr_amounts = ['350.00', '548.11', '96.90']
            
            found_amounts = 0
            for amount in gdpr_amounts:
                if amount in content:
                    found_amounts += 1
                    print(f"✅ Found GDPR amount: €{amount}")
            
            return found_amounts >= 2
        
        def check_security_measures():
            """Check security measures in case creation"""
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            security_measures = [
                'wp_verify_nonce',
                'sanitize_text_field',
                'sanitize_email'
            ]
            
            found_measures = 0
            for measure in security_measures:
                if measure in content:
                    found_measures += 1
                    print(f"✅ Found security measure: {measure}")
            
            return found_measures >= 2
        
        def check_form_validation():
            """Check form validation logic"""
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            validation_patterns = [
                'empty(',
                'required',
                'validation',
                'error'
            ]
            
            found_patterns = 0
            for pattern in validation_patterns:
                if pattern in content:
                    found_patterns += 1
            
            print(f"Found {found_patterns} validation patterns")
            return found_patterns >= 2
        
        self.test("Case creation methods exist", check_case_creation_methods)
        self.test("Database tables integration", check_database_tables_integration)
        self.test("GDPR compliance with standard amounts", check_gdpr_compliance)
        self.test("Security measures implemented", check_security_measures)
        self.test("Form validation logic present", check_form_validation)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 70)
        print("📊 VERSION 1.4.6 SYNTAX ERROR FIX VERIFICATION SUMMARY")
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
        
        print("\n🎯 CRITICAL SYNTAX FIX VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.4.6",
            "Form generator PHP syntax is valid",
            "Main plugin PHP syntax is valid",
            "Activation hook properly registered",
            "Form generator methods exist",
            "Database admin class properly structured",
            "Case creation methods exist"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 SYNTAX FIX STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ VERSION 1.4.6 SYNTAX ERROR FIX: SUCCESSFUL")
            print("Plugin activation should work without syntax errors.")
            print("Form generator class is functional.")
            print("Database Management system is accessible.")
            print("Core case creation functionality is preserved.")
        else:
            print("❌ VERSION 1.4.6 SYNTAX ERROR FIX: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n📝 REVIEW REQUEST VERIFICATION:")
        review_requirements = [
            ("Plugin activation works without syntax errors", ["Form generator PHP syntax is valid", "Main plugin PHP syntax is valid", "Activation hook properly registered"]),
            ("No PHP syntax errors in form generator class", ["Form generator PHP syntax is valid"]),
            ("Database Management system accessible", ["Database admin class properly structured"]),
            ("Core case creation functionality works", ["Case creation methods exist", "Database tables integration"])
        ]
        
        for requirement, related_tests in review_requirements:
            requirement_passed = all(
                self.results.get(test, {}).get('status') == 'passed' 
                for test in related_tests
            )
            status_icon = '✅' if requirement_passed else '❌'
            print(f"{status_icon} {requirement}")
        
        print("\n" + "=" * 70)

def main():
    """Main test execution"""
    tester = SyntaxFixV146Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())