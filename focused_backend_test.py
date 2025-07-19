#!/usr/bin/env python3
"""
Focused Backend Testing for Court Automation Hub Financial Calculator
Tests the most critical functionality for WordPress plugin integration
"""

import os
import json
from datetime import datetime

class FocusedFinancialCalculatorTester:
    def __init__(self):
        self.test_results = []
        self.critical_issues = []
        self.minor_issues = []
        
    def log_result(self, test_name, status, message, details=None):
        """Log test result"""
        result = {
            'test': test_name,
            'status': status,  # 'PASS', 'FAIL', 'MINOR'
            'message': message,
            'details': details,
            'timestamp': datetime.now().isoformat()
        }
        self.test_results.append(result)
        
        if status == 'FAIL':
            self.critical_issues.append(f"{test_name}: {message}")
        elif status == 'MINOR':
            self.minor_issues.append(f"{test_name}: {message}")
            
        print(f"[{status}] {test_name}: {message}")
        if details:
            print(f"    Details: {details}")
    
    def test_plugin_activation_readiness(self):
        """Test if plugins are ready for WordPress activation"""
        print("1. PLUGIN ACTIVATION READINESS")
        print("-" * 50)
        
        # Check main plugin files
        core_plugin = "/app/court-automation-hub.php"
        financial_plugin = "/app/court-automation-hub-financial-calculator/court-automation-hub-financial-calculator.php"
        
        activation_ready = True
        
        # Check core plugin header
        try:
            with open(core_plugin, 'r', encoding='utf-8') as f:
                core_content = f.read()
            
            required_headers = [
                'Plugin Name: Court Automation Hub',
                'Version: 1.5.4',
                'class CourtAutomationHub'
            ]
            
            missing_headers = []
            for header in required_headers:
                if header not in core_content:
                    missing_headers.append(header)
            
            if missing_headers:
                self.log_result("Core Plugin Headers", 'FAIL', 
                              f"Missing required headers: {', '.join(missing_headers)}")
                activation_ready = False
            else:
                self.log_result("Core Plugin Headers", 'PASS', "All required headers found")
                
        except Exception as e:
            self.log_result("Core Plugin Headers", 'FAIL', f"Error reading core plugin: {str(e)}")
            activation_ready = False
        
        # Check financial plugin header and dependency
        try:
            with open(financial_plugin, 'r', encoding='utf-8') as f:
                financial_content = f.read()
            
            required_headers = [
                'Plugin Name: Court Automation Hub - Financial Calculator',
                'Version: 1.0.5',
                'Requires Plugins: court-automation-hub',
                'class CAH_Financial_Calculator_Plugin'
            ]
            
            missing_headers = []
            for header in required_headers:
                if header not in financial_content:
                    missing_headers.append(header)
            
            if missing_headers:
                self.log_result("Financial Plugin Headers", 'FAIL', 
                              f"Missing required headers: {', '.join(missing_headers)}")
                activation_ready = False
            else:
                self.log_result("Financial Plugin Headers", 'PASS', "All required headers found")
                
            # Check dependency checking logic
            if 'is_core_plugin_active' in financial_content and 'class_exists(\'CourtAutomationHub\')' in financial_content:
                self.log_result("Dependency Check", 'PASS', "Proper dependency checking implemented")
            else:
                self.log_result("Dependency Check", 'FAIL', "Missing or incomplete dependency checking")
                activation_ready = False
                
        except Exception as e:
            self.log_result("Financial Plugin Headers", 'FAIL', f"Error reading financial plugin: {str(e)}")
            activation_ready = False
        
        return activation_ready
    
    def test_database_schema_creation(self):
        """Test database table creation functionality"""
        print("\n2. DATABASE SCHEMA CREATION")
        print("-" * 50)
        
        db_manager_file = "/app/court-automation-hub-financial-calculator/includes/class-financial-db-manager.php"
        
        try:
            with open(db_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for all required tables
            required_tables = {
                'cah_financial_templates': [
                    'id int(11) NOT NULL AUTO_INCREMENT',
                    'name varchar(255) NOT NULL',
                    'is_default tinyint(1) DEFAULT 0',
                    'PRIMARY KEY (id)'
                ],
                'cah_cost_items': [
                    'id int(11) NOT NULL AUTO_INCREMENT',
                    'template_id int(11)',
                    'case_id int(11) DEFAULT NULL',
                    'category enum(',
                    'FOREIGN KEY (template_id)'
                ],
                'cah_case_financial': [
                    'id int(11) NOT NULL AUTO_INCREMENT',
                    'case_id int(11) NOT NULL',
                    'vat_rate decimal(5,2) DEFAULT 19.00',
                    'UNIQUE KEY unique_case (case_id)'
                ]
            }
            
            schema_complete = True
            
            for table_name, required_fields in required_tables.items():
                missing_fields = []
                for field in required_fields:
                    if field not in content:
                        missing_fields.append(field)
                
                if missing_fields:
                    self.log_result(f"Table Schema: {table_name}", 'FAIL',
                                  f"Missing fields: {', '.join(missing_fields)}")
                    schema_complete = False
                else:
                    self.log_result(f"Table Schema: {table_name}", 'PASS', "All required fields found")
            
            # Check for proper database operations
            db_operations = [
                'dbDelta',
                'create_tables',
                'get_charset_collate'
            ]
            
            missing_operations = []
            for operation in db_operations:
                if operation not in content:
                    missing_operations.append(operation)
            
            if missing_operations:
                self.log_result("Database Operations", 'FAIL',
                              f"Missing operations: {', '.join(missing_operations)}")
                schema_complete = False
            else:
                self.log_result("Database Operations", 'PASS', "All database operations found")
            
            return schema_complete
            
        except Exception as e:
            self.log_result("Database Schema", 'FAIL', f"Error checking schema: {str(e)}")
            return False
    
    def test_ajax_endpoint_implementation(self):
        """Test AJAX endpoint implementation for case integration"""
        print("\n3. AJAX ENDPOINT IMPLEMENTATION")
        print("-" * 50)
        
        integration_file = "/app/court-automation-hub-financial-calculator/includes/class-case-financial-integration.php"
        
        try:
            with open(integration_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Critical AJAX endpoints for case integration
            critical_endpoints = {
                'load_financial_templates': 'ajax_load_templates',
                'load_template_items': 'ajax_load_template_items', 
                'calculate_financial_totals': 'ajax_calculate_totals',
                'save_case_financial': 'ajax_save_case_financial',
                'save_financial_as_template': 'ajax_save_as_template'
            }
            
            endpoints_working = True
            
            for endpoint_action, handler_method in critical_endpoints.items():
                # Check if AJAX action is registered
                ajax_registration = f"wp_ajax_{endpoint_action}"
                handler_exists = f"function {handler_method}"
                nonce_check = "check_ajax_referer('cah_financial_nonce'"
                
                if ajax_registration not in content:
                    self.log_result(f"AJAX Registration: {endpoint_action}", 'FAIL', 
                                  "AJAX action not registered")
                    endpoints_working = False
                elif handler_method not in content:
                    self.log_result(f"AJAX Handler: {endpoint_action}", 'FAIL', 
                                  "Handler method not found")
                    endpoints_working = False
                elif nonce_check not in content:
                    self.log_result(f"AJAX Security: {endpoint_action}", 'MINOR', 
                                  "Nonce verification not found")
                else:
                    self.log_result(f"AJAX Endpoint: {endpoint_action}", 'PASS', 
                                  "Complete implementation found")
            
            # Check for JavaScript integration
            js_integration_checks = [
                'wp_localize_script',
                'cah_case_financial',
                'ajax_url',
                'nonce'
            ]
            
            missing_js = []
            for check in js_integration_checks:
                if check not in content:
                    missing_js.append(check)
            
            if missing_js:
                self.log_result("JavaScript Integration", 'FAIL',
                              f"Missing JS integration: {', '.join(missing_js)}")
                endpoints_working = False
            else:
                self.log_result("JavaScript Integration", 'PASS', "Complete JS integration found")
            
            return endpoints_working
            
        except Exception as e:
            self.log_result("AJAX Endpoints", 'FAIL', f"Error checking endpoints: {str(e)}")
            return False
    
    def test_financial_calculation_engine(self):
        """Test the financial calculation engine"""
        print("\n4. FINANCIAL CALCULATION ENGINE")
        print("-" * 50)
        
        calculator_file = "/app/court-automation-hub-financial-calculator/includes/class-financial-calculator.php"
        
        try:
            with open(calculator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            calculation_ready = True
            
            # Check core calculation method
            if 'function calculate_totals' not in content:
                self.log_result("Calculation Method", 'FAIL', "calculate_totals method not found")
                calculation_ready = False
            else:
                self.log_result("Calculation Method", 'PASS', "calculate_totals method found")
            
            # Check German VAT handling
            vat_checks = [
                '19.00',  # German VAT rate
                'vat_rate',
                'vat_amount',
                'subtotal',
                'total_amount'
            ]
            
            missing_vat = []
            for check in vat_checks:
                if check not in content:
                    missing_vat.append(check)
            
            if missing_vat:
                self.log_result("VAT Calculation", 'FAIL',
                              f"Missing VAT components: {', '.join(missing_vat)}")
                calculation_ready = False
            else:
                self.log_result("VAT Calculation", 'PASS', "Complete VAT calculation found")
            
            # Check cost categories
            required_categories = [
                'grundkosten',
                'gerichtskosten',
                'anwaltskosten', 
                'sonstige'
            ]
            
            missing_categories = []
            for category in required_categories:
                if category not in content:
                    missing_categories.append(category)
            
            if missing_categories:
                self.log_result("Cost Categories", 'FAIL',
                              f"Missing categories: {', '.join(missing_categories)}")
                calculation_ready = False
            else:
                self.log_result("Cost Categories", 'PASS', "All required categories found")
            
            # Check default GDPR costs
            gdpr_checks = [
                'get_default_gdpr_costs',
                'DSGVO Grundschaden',
                'Anwaltskosten',
                'Gerichtskosten'
            ]
            
            missing_gdpr = []
            for check in gdpr_checks:
                if check not in content:
                    missing_gdpr.append(check)
            
            if missing_gdpr:
                self.log_result("GDPR Defaults", 'MINOR',
                              f"Missing GDPR components: {', '.join(missing_gdpr)}")
            else:
                self.log_result("GDPR Defaults", 'PASS', "Complete GDPR defaults found")
            
            return calculation_ready
            
        except Exception as e:
            self.log_result("Financial Calculations", 'FAIL', f"Error checking calculations: {str(e)}")
            return False
    
    def test_case_integration_hooks(self):
        """Test integration with core case management"""
        print("\n5. CASE INTEGRATION HOOKS")
        print("-" * 50)
        
        integration_file = "/app/court-automation-hub-financial-calculator/includes/class-case-financial-integration.php"
        
        try:
            with open(integration_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            integration_ready = True
            
            # Check for case lifecycle hooks
            required_hooks = [
                'cah_case_created',
                'cah_case_updated',
                'cah_case_deleted'
            ]
            
            missing_hooks = []
            for hook in required_hooks:
                if f"add_action('{hook}'" not in content:
                    missing_hooks.append(hook)
            
            if missing_hooks:
                self.log_result("Case Lifecycle Hooks", 'FAIL',
                              f"Missing hooks: {', '.join(missing_hooks)}")
                integration_ready = False
            else:
                self.log_result("Case Lifecycle Hooks", 'PASS', "All lifecycle hooks found")
            
            # Check for financial tab rendering
            tab_checks = [
                'render_financial_tab_content',
                'financial-tab-template',
                'case-financial-content'
            ]
            
            missing_tab = []
            for check in tab_checks:
                if check not in content:
                    missing_tab.append(check)
            
            if missing_tab:
                self.log_result("Financial Tab Rendering", 'FAIL',
                              f"Missing tab components: {', '.join(missing_tab)}")
                integration_ready = False
            else:
                self.log_result("Financial Tab Rendering", 'PASS', "Complete tab rendering found")
            
            # Check for script enqueuing
            script_checks = [
                'admin_enqueue_scripts',
                'wp_enqueue_script',
                'wp_enqueue_style'
            ]
            
            missing_scripts = []
            for check in script_checks:
                if check not in content:
                    missing_scripts.append(check)
            
            if missing_scripts:
                self.log_result("Script Enqueuing", 'MINOR',
                              f"Missing script components: {', '.join(missing_scripts)}")
            else:
                self.log_result("Script Enqueuing", 'PASS', "Complete script enqueuing found")
            
            return integration_ready
            
        except Exception as e:
            self.log_result("Case Integration", 'FAIL', f"Error checking integration: {str(e)}")
            return False
    
    def test_default_template_creation(self):
        """Test default template creation on activation"""
        print("\n6. DEFAULT TEMPLATE CREATION")
        print("-" * 50)
        
        template_manager_file = "/app/court-automation-hub-financial-calculator/includes/class-financial-template-manager.php"
        
        try:
            with open(template_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            templates_ready = True
            
            # Check for template creation method
            if 'function create_default_templates' not in content:
                self.log_result("Template Creation Method", 'FAIL', "create_default_templates method not found")
                templates_ready = False
            else:
                self.log_result("Template Creation Method", 'PASS', "create_default_templates method found")
            
            # Check for default templates
            default_templates = [
                'DSGVO Standard Template',
                'Business DSGVO Template', 
                'Minimal DSGVO Template'
            ]
            
            missing_templates = []
            for template in default_templates:
                if template not in content:
                    missing_templates.append(template)
            
            if missing_templates:
                self.log_result("Default Templates", 'MINOR',
                              f"Missing templates: {', '.join(missing_templates)}")
            else:
                self.log_result("Default Templates", 'PASS', "All default templates found")
            
            # Check for activation hook
            main_plugin_file = "/app/court-automation-hub-financial-calculator/court-automation-hub-financial-calculator.php"
            with open(main_plugin_file, 'r', encoding='utf-8') as f:
                main_content = f.read()
            
            if 'register_activation_hook' not in main_content or 'create_default_templates' not in main_content:
                self.log_result("Activation Hook", 'FAIL', "Template creation not hooked to activation")
                templates_ready = False
            else:
                self.log_result("Activation Hook", 'PASS', "Template creation properly hooked")
            
            return templates_ready
            
        except Exception as e:
            self.log_result("Default Templates", 'FAIL', f"Error checking templates: {str(e)}")
            return False
    
    def run_focused_tests(self):
        """Run focused tests on critical functionality"""
        print("=" * 80)
        print("COURT AUTOMATION HUB FINANCIAL CALCULATOR - FOCUSED BACKEND TESTING")
        print("=" * 80)
        
        # Run critical tests
        test_results = []
        
        test_results.append(self.test_plugin_activation_readiness())
        test_results.append(self.test_database_schema_creation())
        test_results.append(self.test_ajax_endpoint_implementation())
        test_results.append(self.test_financial_calculation_engine())
        test_results.append(self.test_case_integration_hooks())
        test_results.append(self.test_default_template_creation())
        
        # Print summary
        self.print_focused_summary(test_results)
        
        return all(test_results)
    
    def print_focused_summary(self, test_results):
        """Print focused test summary"""
        print("\n" + "=" * 80)
        print("FOCUSED TEST SUMMARY")
        print("=" * 80)
        
        total_tests = len(self.test_results)
        passed_tests = len([r for r in self.test_results if r['status'] == 'PASS'])
        failed_tests = len([r for r in self.test_results if r['status'] == 'FAIL'])
        minor_tests = len([r for r in self.test_results if r['status'] == 'MINOR'])
        
        print(f"Total Tests: {total_tests}")
        print(f"✅ Passed: {passed_tests}")
        print(f"⚠️  Minor Issues: {minor_tests}")
        print(f"❌ Critical Failures: {failed_tests}")
        print()
        
        if self.critical_issues:
            print("🚨 CRITICAL ISSUES (Must Fix):")
            for issue in self.critical_issues:
                print(f"  ❌ {issue}")
            print()
        
        if self.minor_issues:
            print("⚠️  MINOR ISSUES (Recommended to Fix):")
            for issue in self.minor_issues:
                print(f"  ⚠️  {issue}")
            print()
        
        # Overall assessment
        critical_areas_passed = sum(test_results)
        total_critical_areas = len(test_results)
        
        print("📊 CRITICAL AREA ASSESSMENT:")
        print(f"  Plugin Activation Ready: {'✅' if test_results[0] else '❌'}")
        print(f"  Database Schema Ready: {'✅' if test_results[1] else '❌'}")
        print(f"  AJAX Endpoints Ready: {'✅' if test_results[2] else '❌'}")
        print(f"  Financial Engine Ready: {'✅' if test_results[3] else '❌'}")
        print(f"  Case Integration Ready: {'✅' if test_results[4] else '❌'}")
        print(f"  Default Templates Ready: {'✅' if test_results[5] else '❌'}")
        print()
        
        if critical_areas_passed == total_critical_areas:
            print("🎉 ALL CRITICAL AREAS PASSED!")
            print("✅ Financial Calculator plugin is ready for WordPress activation and testing!")
        elif critical_areas_passed >= 4:
            print("✅ MOSTLY READY - Minor issues but core functionality should work")
            print("🔧 Address critical issues before production deployment")
        else:
            print("❌ NOT READY - Critical issues must be fixed")
            print("🚨 Plugin may not work properly until issues are resolved")
        
        print("=" * 80)

def main():
    """Main focused test execution"""
    tester = FocusedFinancialCalculatorTester()
    success = tester.run_focused_tests()
    
    return success

if __name__ == "__main__":
    main()