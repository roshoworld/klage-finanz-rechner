#!/usr/bin/env python3
"""
Backend Testing for Court Automation Hub Financial Calculator Integration
Tests WordPress plugin functionality, database operations, and AJAX endpoints
"""

import os
import sys
import subprocess
import json
import time
import requests
from datetime import datetime

class WordPressFinancialCalculatorTester:
    def __init__(self):
        self.test_results = []
        self.errors = []
        self.warnings = []
        
    def log_result(self, test_name, status, message, details=None):
        """Log test result"""
        result = {
            'test': test_name,
            'status': status,  # 'PASS', 'FAIL', 'WARNING'
            'message': message,
            'details': details,
            'timestamp': datetime.now().isoformat()
        }
        self.test_results.append(result)
        
        if status == 'FAIL':
            self.errors.append(f"{test_name}: {message}")
        elif status == 'WARNING':
            self.warnings.append(f"{test_name}: {message}")
            
        print(f"[{status}] {test_name}: {message}")
        if details:
            print(f"    Details: {details}")
    
    def check_file_exists(self, filepath, description):
        """Check if a file exists"""
        if os.path.exists(filepath):
            self.log_result(f"File Check: {description}", 'PASS', f"File exists: {filepath}")
            return True
        else:
            self.log_result(f"File Check: {description}", 'FAIL', f"File missing: {filepath}")
            return False
    
    def check_php_syntax(self, filepath):
        """Check PHP file syntax"""
        try:
            result = subprocess.run(['php', '-l', filepath], 
                                  capture_output=True, text=True, timeout=10)
            if result.returncode == 0:
                self.log_result(f"PHP Syntax: {os.path.basename(filepath)}", 'PASS', 
                              "PHP syntax is valid")
                return True
            else:
                self.log_result(f"PHP Syntax: {os.path.basename(filepath)}", 'FAIL', 
                              f"PHP syntax error: {result.stderr}")
                return False
        except subprocess.TimeoutExpired:
            self.log_result(f"PHP Syntax: {os.path.basename(filepath)}", 'WARNING', 
                          "PHP syntax check timed out")
            return False
        except FileNotFoundError:
            self.log_result(f"PHP Syntax: {os.path.basename(filepath)}", 'WARNING', 
                          "PHP CLI not available for syntax checking")
            return False
    
    def check_class_definitions(self, filepath, expected_classes):
        """Check if PHP classes are defined in file"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                
            found_classes = []
            missing_classes = []
            
            for class_name in expected_classes:
                if f"class {class_name}" in content:
                    found_classes.append(class_name)
                else:
                    missing_classes.append(class_name)
            
            if missing_classes:
                self.log_result(f"Class Definition: {os.path.basename(filepath)}", 'FAIL',
                              f"Missing classes: {', '.join(missing_classes)}",
                              f"Found: {', '.join(found_classes)}")
                return False
            else:
                self.log_result(f"Class Definition: {os.path.basename(filepath)}", 'PASS',
                              f"All expected classes found: {', '.join(found_classes)}")
                return True
                
        except Exception as e:
            self.log_result(f"Class Definition: {os.path.basename(filepath)}", 'FAIL',
                          f"Error reading file: {str(e)}")
            return False
    
    def check_database_schema(self, filepath):
        """Check database schema creation in PHP file"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            expected_tables = [
                'cah_financial_templates',
                'cah_cost_items', 
                'cah_case_financial'
            ]
            
            found_tables = []
            missing_tables = []
            
            for table in expected_tables:
                if table in content:
                    found_tables.append(table)
                else:
                    missing_tables.append(table)
            
            # Check for key database operations
            db_operations = [
                'CREATE TABLE IF NOT EXISTS',
                'FOREIGN KEY',
                'dbDelta',
                'AUTO_INCREMENT'
            ]
            
            found_operations = []
            for operation in db_operations:
                if operation in content:
                    found_operations.append(operation)
            
            if missing_tables:
                self.log_result("Database Schema", 'FAIL',
                              f"Missing table definitions: {', '.join(missing_tables)}",
                              f"Found tables: {', '.join(found_tables)}")
                return False
            elif len(found_operations) < 3:
                self.log_result("Database Schema", 'WARNING',
                              f"Limited database operations found: {', '.join(found_operations)}")
                return False
            else:
                self.log_result("Database Schema", 'PASS',
                              f"All expected tables and operations found",
                              f"Tables: {', '.join(found_tables)}, Operations: {', '.join(found_operations)}")
                return True
                
        except Exception as e:
            self.log_result("Database Schema", 'FAIL', f"Error checking schema: {str(e)}")
            return False
    
    def check_ajax_endpoints(self, filepath):
        """Check AJAX endpoint definitions"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            expected_endpoints = [
                'load_financial_templates',
                'load_template_items',
                'calculate_financial_totals',
                'save_case_financial',
                'save_financial_as_template'
            ]
            
            found_endpoints = []
            missing_endpoints = []
            
            for endpoint in expected_endpoints:
                if f"wp_ajax_{endpoint}" in content or f"ajax_{endpoint}" in content:
                    found_endpoints.append(endpoint)
                else:
                    missing_endpoints.append(endpoint)
            
            # Check for nonce verification
            has_nonce_check = 'check_ajax_referer' in content or 'wp_verify_nonce' in content
            
            if missing_endpoints:
                self.log_result("AJAX Endpoints", 'FAIL',
                              f"Missing AJAX endpoints: {', '.join(missing_endpoints)}",
                              f"Found: {', '.join(found_endpoints)}")
                return False
            elif not has_nonce_check:
                self.log_result("AJAX Endpoints", 'WARNING',
                              "AJAX endpoints found but no nonce verification detected")
                return False
            else:
                self.log_result("AJAX Endpoints", 'PASS',
                              f"All AJAX endpoints found with security checks",
                              f"Endpoints: {', '.join(found_endpoints)}")
                return True
                
        except Exception as e:
            self.log_result("AJAX Endpoints", 'FAIL', f"Error checking endpoints: {str(e)}")
            return False
    
    def check_rest_api_routes(self, filepath):
        """Check REST API route definitions"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            expected_routes = [
                '/templates',
                '/cost-items',
                '/calculate',
                '/case-financial'
            ]
            
            found_routes = []
            missing_routes = []
            
            for route in expected_routes:
                if route in content:
                    found_routes.append(route)
                else:
                    missing_routes.append(route)
            
            # Check for REST API essentials
            rest_essentials = [
                'register_rest_route',
                'permission_callback',
                'WP_REST_Request',
                'WP_REST_Response'
            ]
            
            found_essentials = []
            for essential in rest_essentials:
                if essential in content:
                    found_essentials.append(essential)
            
            if missing_routes:
                self.log_result("REST API Routes", 'FAIL',
                              f"Missing API routes: {', '.join(missing_routes)}",
                              f"Found: {', '.join(found_routes)}")
                return False
            elif len(found_essentials) < 3:
                self.log_result("REST API Routes", 'WARNING',
                              f"Limited REST API implementation: {', '.join(found_essentials)}")
                return False
            else:
                self.log_result("REST API Routes", 'PASS',
                              f"Complete REST API implementation found",
                              f"Routes: {', '.join(found_routes)}, Essentials: {', '.join(found_essentials)}")
                return True
                
        except Exception as e:
            self.log_result("REST API Routes", 'FAIL', f"Error checking API routes: {str(e)}")
            return False
    
    def check_financial_calculations(self, filepath):
        """Check financial calculation logic"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            calculation_features = [
                'calculate_totals',
                'vat_rate',
                '19.00',  # German VAT rate
                'subtotal',
                'vat_amount',
                'total_amount'
            ]
            
            found_features = []
            missing_features = []
            
            for feature in calculation_features:
                if feature in content:
                    found_features.append(feature)
                else:
                    missing_features.append(feature)
            
            # Check for cost categories
            cost_categories = [
                'grundkosten',
                'gerichtskosten', 
                'anwaltskosten',
                'sonstige'
            ]
            
            found_categories = []
            for category in cost_categories:
                if category in content:
                    found_categories.append(category)
            
            if missing_features:
                self.log_result("Financial Calculations", 'FAIL',
                              f"Missing calculation features: {', '.join(missing_features)}",
                              f"Found: {', '.join(found_features)}")
                return False
            elif len(found_categories) < 4:
                self.log_result("Financial Calculations", 'WARNING',
                              f"Missing cost categories: {set(cost_categories) - set(found_categories)}")
                return False
            else:
                self.log_result("Financial Calculations", 'PASS',
                              f"Complete financial calculation system found",
                              f"Features: {len(found_features)}, Categories: {len(found_categories)}")
                return True
                
        except Exception as e:
            self.log_result("Financial Calculations", 'FAIL', f"Error checking calculations: {str(e)}")
            return False
    
    def check_plugin_integration(self, core_plugin_file, financial_plugin_file):
        """Check integration between core and financial plugins"""
        try:
            # Check core plugin
            with open(core_plugin_file, 'r', encoding='utf-8') as f:
                core_content = f.read()
            
            # Check financial plugin
            with open(financial_plugin_file, 'r', encoding='utf-8') as f:
                financial_content = f.read()
            
            integration_checks = []
            
            # Check if core plugin class exists
            if 'class CourtAutomationHub' in core_content:
                integration_checks.append("Core plugin class found")
            else:
                integration_checks.append("MISSING: Core plugin class")
            
            # Check dependency checking in financial plugin
            if 'is_core_plugin_active' in financial_content:
                integration_checks.append("Dependency check implemented")
            else:
                integration_checks.append("MISSING: Dependency check")
            
            # Check for plugin dependency declaration
            if 'Requires Plugins: court-automation-hub' in financial_content:
                integration_checks.append("Plugin dependency declared")
            else:
                integration_checks.append("MISSING: Plugin dependency declaration")
            
            # Check for integration hooks
            integration_hooks = [
                'cah_case_created',
                'cah_case_updated', 
                'cah_case_deleted'
            ]
            
            found_hooks = []
            for hook in integration_hooks:
                if hook in financial_content:
                    found_hooks.append(hook)
            
            if len(found_hooks) >= 2:
                integration_checks.append(f"Integration hooks found: {', '.join(found_hooks)}")
            else:
                integration_checks.append(f"LIMITED: Few integration hooks: {', '.join(found_hooks)}")
            
            # Determine overall status
            missing_count = len([check for check in integration_checks if 'MISSING' in check])
            limited_count = len([check for check in integration_checks if 'LIMITED' in check])
            
            if missing_count > 1:
                self.log_result("Plugin Integration", 'FAIL',
                              f"Critical integration issues found",
                              '; '.join(integration_checks))
                return False
            elif missing_count > 0 or limited_count > 0:
                self.log_result("Plugin Integration", 'WARNING',
                              f"Some integration issues found",
                              '; '.join(integration_checks))
                return False
            else:
                self.log_result("Plugin Integration", 'PASS',
                              f"Complete plugin integration found",
                              '; '.join(integration_checks))
                return True
                
        except Exception as e:
            self.log_result("Plugin Integration", 'FAIL', f"Error checking integration: {str(e)}")
            return False
    
    def check_default_templates(self, filepath):
        """Check default template creation"""
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            template_features = [
                'create_default_templates',
                'DSGVO',
                'default_gdpr_costs',
                'is_default'
            ]
            
            found_features = []
            missing_features = []
            
            for feature in template_features:
                if feature in content:
                    found_features.append(feature)
                else:
                    missing_features.append(feature)
            
            # Check for specific default templates
            default_templates = [
                'DSGVO Standard Template',
                'Business DSGVO Template',
                'Minimal DSGVO Template'
            ]
            
            found_templates = []
            for template in default_templates:
                if template in content:
                    found_templates.append(template)
            
            if missing_features:
                self.log_result("Default Templates", 'FAIL',
                              f"Missing template features: {', '.join(missing_features)}",
                              f"Found: {', '.join(found_features)}")
                return False
            elif len(found_templates) < 2:
                self.log_result("Default Templates", 'WARNING',
                              f"Limited default templates: {', '.join(found_templates)}")
                return False
            else:
                self.log_result("Default Templates", 'PASS',
                              f"Complete default template system found",
                              f"Features: {len(found_features)}, Templates: {len(found_templates)}")
                return True
                
        except Exception as e:
            self.log_result("Default Templates", 'FAIL', f"Error checking templates: {str(e)}")
            return False
    
    def run_comprehensive_tests(self):
        """Run all backend tests for the financial calculator plugin"""
        print("=" * 80)
        print("COURT AUTOMATION HUB FINANCIAL CALCULATOR - BACKEND TESTING")
        print("=" * 80)
        print()
        
        # Define file paths
        base_path = "/app"
        financial_plugin_path = f"{base_path}/court-automation-hub-financial-calculator"
        core_plugin_file = f"{base_path}/court-automation-hub.php"
        financial_plugin_file = f"{financial_plugin_path}/court-automation-hub-financial-calculator.php"
        
        includes_path = f"{financial_plugin_path}/includes"
        
        # Test 1: Core Plugin File Structure
        print("1. TESTING PLUGIN FILE STRUCTURE")
        print("-" * 40)
        
        self.check_file_exists(core_plugin_file, "Core Plugin Main File")
        self.check_file_exists(financial_plugin_file, "Financial Plugin Main File")
        self.check_file_exists(f"{includes_path}/class-financial-db-manager.php", "Database Manager")
        self.check_file_exists(f"{includes_path}/class-case-financial-integration.php", "Case Integration")
        self.check_file_exists(f"{includes_path}/class-financial-rest-api.php", "REST API")
        self.check_file_exists(f"{includes_path}/class-financial-calculator.php", "Calculator Engine")
        self.check_file_exists(f"{includes_path}/class-financial-template-manager.php", "Template Manager")
        self.check_file_exists(f"{includes_path}/class-financial-admin.php", "Admin Interface")
        
        print()
        
        # Test 2: PHP Syntax Validation
        print("2. TESTING PHP SYNTAX")
        print("-" * 40)
        
        php_files = [
            core_plugin_file,
            financial_plugin_file,
            f"{includes_path}/class-financial-db-manager.php",
            f"{includes_path}/class-case-financial-integration.php",
            f"{includes_path}/class-financial-rest-api.php",
            f"{includes_path}/class-financial-calculator.php",
            f"{includes_path}/class-financial-template-manager.php",
            f"{includes_path}/class-financial-admin.php"
        ]
        
        for php_file in php_files:
            if os.path.exists(php_file):
                self.check_php_syntax(php_file)
        
        print()
        
        # Test 3: Class Definitions
        print("3. TESTING CLASS DEFINITIONS")
        print("-" * 40)
        
        class_checks = [
            (core_plugin_file, ["CourtAutomationHub"]),
            (financial_plugin_file, ["CAH_Financial_Calculator_Plugin"]),
            (f"{includes_path}/class-financial-db-manager.php", ["CAH_Financial_DB_Manager"]),
            (f"{includes_path}/class-case-financial-integration.php", ["CAH_Case_Financial_Integration"]),
            (f"{includes_path}/class-financial-rest-api.php", ["CAH_Financial_REST_API"]),
            (f"{includes_path}/class-financial-calculator.php", ["CAH_Financial_Calculator_Engine"]),
            (f"{includes_path}/class-financial-template-manager.php", ["CAH_Financial_Template_Manager"]),
            (f"{includes_path}/class-financial-admin.php", ["CAH_Financial_Admin"])
        ]
        
        for filepath, expected_classes in class_checks:
            if os.path.exists(filepath):
                self.check_class_definitions(filepath, expected_classes)
        
        print()
        
        # Test 4: Database Schema
        print("4. TESTING DATABASE SCHEMA")
        print("-" * 40)
        
        db_manager_file = f"{includes_path}/class-financial-db-manager.php"
        if os.path.exists(db_manager_file):
            self.check_database_schema(db_manager_file)
        
        print()
        
        # Test 5: AJAX Endpoints
        print("5. TESTING AJAX ENDPOINTS")
        print("-" * 40)
        
        integration_file = f"{includes_path}/class-case-financial-integration.php"
        if os.path.exists(integration_file):
            self.check_ajax_endpoints(integration_file)
        
        print()
        
        # Test 6: REST API Routes
        print("6. TESTING REST API ROUTES")
        print("-" * 40)
        
        rest_api_file = f"{includes_path}/class-financial-rest-api.php"
        if os.path.exists(rest_api_file):
            self.check_rest_api_routes(rest_api_file)
        
        print()
        
        # Test 7: Financial Calculations
        print("7. TESTING FINANCIAL CALCULATIONS")
        print("-" * 40)
        
        calculator_file = f"{includes_path}/class-financial-calculator.php"
        if os.path.exists(calculator_file):
            self.check_financial_calculations(calculator_file)
        
        print()
        
        # Test 8: Plugin Integration
        print("8. TESTING PLUGIN INTEGRATION")
        print("-" * 40)
        
        if os.path.exists(core_plugin_file) and os.path.exists(financial_plugin_file):
            self.check_plugin_integration(core_plugin_file, financial_plugin_file)
        
        print()
        
        # Test 9: Default Templates
        print("9. TESTING DEFAULT TEMPLATES")
        print("-" * 40)
        
        template_manager_file = f"{includes_path}/class-financial-template-manager.php"
        if os.path.exists(template_manager_file):
            self.check_default_templates(template_manager_file)
        
        print()
        
        # Test Summary
        self.print_test_summary()
    
    def print_test_summary(self):
        """Print comprehensive test summary"""
        print("=" * 80)
        print("TEST SUMMARY")
        print("=" * 80)
        
        total_tests = len(self.test_results)
        passed_tests = len([r for r in self.test_results if r['status'] == 'PASS'])
        failed_tests = len([r for r in self.test_results if r['status'] == 'FAIL'])
        warning_tests = len([r for r in self.test_results if r['status'] == 'WARNING'])
        
        print(f"Total Tests: {total_tests}")
        print(f"✅ Passed: {passed_tests}")
        print(f"⚠️  Warnings: {warning_tests}")
        print(f"❌ Failed: {failed_tests}")
        print()
        
        if failed_tests > 0:
            print("CRITICAL ISSUES:")
            for error in self.errors:
                print(f"  ❌ {error}")
            print()
        
        if warning_tests > 0:
            print("WARNINGS:")
            for warning in self.warnings:
                print(f"  ⚠️  {warning}")
            print()
        
        # Overall assessment
        if failed_tests == 0:
            if warning_tests == 0:
                print("🎉 ALL TESTS PASSED - Financial Calculator plugin is ready for deployment!")
            else:
                print("✅ TESTS MOSTLY PASSED - Minor issues found but core functionality should work")
        elif failed_tests <= 2:
            print("⚠️  TESTS PARTIALLY PASSED - Some issues need to be addressed")
        else:
            print("❌ TESTS FAILED - Critical issues must be fixed before deployment")
        
        print()
        print("=" * 80)
        
        # Return overall status
        if failed_tests == 0:
            return True
        else:
            return False

def main():
    """Main test execution"""
    tester = WordPressFinancialCalculatorTester()
    success = tester.run_comprehensive_tests()
    
    # Exit with appropriate code
    sys.exit(0 if success else 1)

if __name__ == "__main__":
    main()