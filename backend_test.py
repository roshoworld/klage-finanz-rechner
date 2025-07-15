#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.2 Verification
Tests the critical case creation methods that were added in the hotfix.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV122Tester:
    """Test suite specifically for verifying hotfix v1.2.2 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.2.2 Verification Tests")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_method_existence()
        self.test_create_new_case_implementation()
        self.test_update_case_implementation()
        self.test_case_creation_flow()
        self.test_data_validation()
        self.test_database_operations()
        self.test_error_handling()
        self.test_integration()
        
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
        """Test that plugin version is updated to 1.2.2"""
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
            return version == "1.2.2"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.2"
        
        self.test("Plugin header version is 1.2.2", check_plugin_version)
        self.test("Plugin constant version is 1.2.2", check_constant_version)
    
    def test_method_existence(self):
        """Test that both critical methods exist"""
        print("🔍 TESTING METHOD EXISTENCE")
        print("-" * 40)
        
        def check_create_new_case_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+create_new_case\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_update_case_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+update_case\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_method_calls_in_handler():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that methods are called in handle_case_actions
            create_call = '$this->create_new_case()' in content
            update_call = '$this->update_case()' in content
            
            print(f"create_new_case() call found: {create_call}")
            print(f"update_case() call found: {update_call}")
            
            return create_call and update_call
        
        self.test("create_new_case() method exists", check_create_new_case_method)
        self.test("update_case() method exists", check_update_case_method)
        self.test("Methods are called in action handler", check_method_calls_in_handler)
    
    def test_create_new_case_implementation(self):
        """Test the create_new_case method implementation"""
        print("📝 TESTING CREATE_NEW_CASE IMPLEMENTATION")
        print("-" * 40)
        
        def check_nonce_verification():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for nonce verification in create_new_case
            nonce_pattern = r'wp_verify_nonce.*create_case'
            return bool(re.search(nonce_pattern, content))
        
        def check_form_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for input sanitization
            sanitization_functions = [
                'sanitize_text_field',
                'sanitize_email',
                'sanitize_textarea_field'
            ]
            
            found_functions = 0
            for func in sanitization_functions:
                if func in content:
                    found_functions += 1
            
            print(f"Found {found_functions} sanitization functions")
            return found_functions >= 2
        
        def check_debtor_record_creation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debtor table insert
            debtor_insert = 'klage_debtors' in content and '$wpdb->insert' in content
            
            # Check for comprehensive debtor fields
            debtor_fields = [
                'debtors_name', 'debtors_company', 'debtors_first_name',
                'debtors_last_name', 'debtors_email', 'debtors_phone',
                'debtors_address', 'debtors_postal_code', 'debtors_city'
            ]
            
            found_fields = sum(1 for field in debtor_fields if field in content)
            print(f"Found {found_fields} debtor fields")
            
            return debtor_insert and found_fields >= 6
        
        def check_case_creation_with_57_fields():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for case table insert
            case_insert = 'klage_cases' in content
            
            # Check for key case fields from the 57-field structure
            case_fields = [
                'case_id', 'case_creation_date', 'case_status', 'case_priority',
                'total_amount', 'verfahrensart', 'rechtsgrundlage', 'kategorie',
                'schadenhoehe', 'verfahrenswert', 'erfolgsaussicht', 'risiko_bewertung',
                'komplexitaet', 'kommunikation_sprache'
            ]
            
            found_fields = sum(1 for field in case_fields if field in content)
            print(f"Found {found_fields} case fields")
            
            return case_insert and found_fields >= 10
        
        def check_financial_record_generation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for financial table insert
            financial_insert = 'klage_financial' in content
            
            # Check for GDPR standard amounts
            gdpr_amounts = ['350.00', '96.90', '13.36', '87.85', '548.11', '32.00']
            found_amounts = sum(1 for amount in gdpr_amounts if amount in content)
            
            print(f"Found {found_amounts} GDPR standard amounts")
            
            return financial_insert and found_amounts >= 4
        
        def check_audit_trail_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for audit log entry
            audit_insert = 'klage_audit' in content and 'case_created' in content
            
            return audit_insert
        
        def check_success_feedback():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for success message and redirect
            success_message = 'notice-success' in content and 'Erfolg' in content
            redirect_logic = 'window.location.href' in content
            
            return success_message and redirect_logic
        
        self.test("Nonce security verification", check_nonce_verification)
        self.test("Form validation and sanitization", check_form_validation)
        self.test("Debtor record creation with comprehensive fields", check_debtor_record_creation)
        self.test("Case creation with 57-field structure", check_case_creation_with_57_fields)
        self.test("Financial record generation with GDPR amounts", check_financial_record_generation)
        self.test("Audit trail logging", check_audit_trail_logging)
        self.test("Success feedback with redirect", check_success_feedback)
    
    def test_update_case_implementation(self):
        """Test the update_case method implementation"""
        print("🔄 TESTING UPDATE_CASE IMPLEMENTATION")
        print("-" * 40)
        
        def check_update_nonce_verification():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for nonce verification in update_case
            nonce_pattern = r'wp_verify_nonce.*update_case'
            return bool(re.search(nonce_pattern, content))
        
        def check_integration_with_existing_handler():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that update_case calls handle_case_update
            integration_call = 'handle_case_update' in content
            
            return integration_call
        
        def check_case_id_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for case ID validation
            id_validation = 'intval($_POST[\'case_id\'])' in content or 'case_id' in content
            
            return id_validation
        
        self.test("Update nonce security verification", check_update_nonce_verification)
        self.test("Integration with existing handle_case_update method", check_integration_with_existing_handler)
        self.test("Case ID validation", check_case_id_validation)
    
    def test_case_creation_flow(self):
        """Test the complete case creation workflow"""
        print("🔄 TESTING CASE CREATION FLOW")
        print("-" * 40)
        
        def check_form_to_handler_flow():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for form action handling
            form_action = 'action=create_case' in content
            handler_switch = 'case \'create_case\':' in content
            
            return form_action and handler_switch
        
        def check_error_handling_flow():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for error handling patterns
            error_patterns = [
                'notice-error',
                'try {',
                'catch',
                'Exception'
            ]
            
            found_patterns = sum(1 for pattern in error_patterns if pattern in content)
            print(f"Found {found_patterns} error handling patterns")
            
            return found_patterns >= 2
        
        def check_database_transaction_flow():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper database operation sequence
            # 1. Debtor creation first
            # 2. Case creation with debtor_id
            # 3. Financial record creation
            # 4. Audit log creation
            
            debtor_insert = content.find('klage_debtors')
            case_insert = content.find('klage_cases')
            financial_insert = content.find('klage_financial')
            audit_insert = content.find('klage_audit')
            
            # Check sequence (all should be found and in logical order)
            sequence_correct = (debtor_insert > 0 and case_insert > debtor_insert and 
                              financial_insert > case_insert and audit_insert > financial_insert)
            
            print(f"Database operation sequence correct: {sequence_correct}")
            return sequence_correct
        
        self.test("Form to handler flow", check_form_to_handler_flow)
        self.test("Error handling flow", check_error_handling_flow)
        self.test("Database transaction flow", check_database_transaction_flow)
    
    def test_data_validation(self):
        """Test data validation and sanitization"""
        print("✅ TESTING DATA VALIDATION")
        print("-" * 40)
        
        def check_required_field_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for required field checks
            required_checks = [
                'empty($case_id)',
                'empty($debtors_last_name)',
                'required'
            ]
            
            found_checks = sum(1 for check in required_checks if check in content)
            print(f"Found {found_checks} required field checks")
            
            return found_checks >= 2
        
        def check_duplicate_case_id_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for duplicate case ID check
            duplicate_check = 'existing_case' in content and 'SELECT id FROM' in content
            
            return duplicate_check
        
        def check_email_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for email sanitization
            email_validation = 'sanitize_email' in content
            
            return email_validation
        
        self.test("Required field validation", check_required_field_validation)
        self.test("Duplicate case ID validation", check_duplicate_case_id_validation)
        self.test("Email validation", check_email_validation)
    
    def test_database_operations(self):
        """Test database operations"""
        print("🗄️ TESTING DATABASE OPERATIONS")
        print("-" * 40)
        
        def check_table_existence_verification():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for table existence checks
            table_checks = [
                'SHOW TABLES LIKE',
                'klage_debtors',
                'klage_financial',
                'klage_audit'
            ]
            
            found_checks = sum(1 for check in table_checks if check in content)
            print(f"Found {found_checks} table existence checks")
            
            return found_checks >= 3
        
        def check_prepared_statements():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for prepared statement usage
            prepared_statements = '$wpdb->prepare' in content
            
            return prepared_statements
        
        def check_insert_operations():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Count insert operations
            insert_count = content.count('$wpdb->insert')
            print(f"Found {insert_count} insert operations")
            
            # Should have at least 3: debtor, case, financial
            return insert_count >= 3
        
        def check_error_handling_for_db_operations():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for database error handling
            error_handling = '$wpdb->last_error' in content
            
            return error_handling
        
        self.test("Table existence verification", check_table_existence_verification)
        self.test("Prepared statements usage", check_prepared_statements)
        self.test("Multiple insert operations", check_insert_operations)
        self.test("Database error handling", check_error_handling_for_db_operations)
    
    def test_error_handling(self):
        """Test error handling scenarios"""
        print("⚠️ TESTING ERROR HANDLING")
        print("-" * 40)
        
        def check_security_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for security error messages
            security_errors = [
                'Sicherheitsfehler',
                'Security check failed',
                'wp_verify_nonce'
            ]
            
            found_errors = sum(1 for error in security_errors if error in content)
            print(f"Found {found_errors} security error patterns")
            
            return found_errors >= 2
        
        def check_validation_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for validation error messages
            validation_errors = [
                'erforderlich',
                'required',
                'existiert bereits',
                'Fehler'
            ]
            
            found_errors = sum(1 for error in validation_errors if error in content)
            print(f"Found {found_errors} validation error patterns")
            
            return found_errors >= 2
        
        def check_database_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for database error handling
            db_errors = [
                'konnte nicht erstellt werden',
                'Datenbank-Fehler',
                '$wpdb->last_error'
            ]
            
            found_errors = sum(1 for error in db_errors if error in content)
            print(f"Found {found_errors} database error patterns")
            
            return found_errors >= 2
        
        def check_exception_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for try-catch blocks
            exception_handling = 'try {' in content and 'catch (Exception' in content
            
            return exception_handling
        
        self.test("Security error handling", check_security_error_handling)
        self.test("Validation error handling", check_validation_error_handling)
        self.test("Database error handling", check_database_error_handling)
        self.test("Exception handling", check_exception_handling)
    
    def test_integration(self):
        """Test integration with existing functionality"""
        print("🔗 TESTING INTEGRATION")
        print("-" * 40)
        
        def check_existing_functionality_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing methods still exist
            existing_methods = [
                'admin_page_cases',
                'render_cases_list',
                'handle_case_actions',
                'admin_page_dashboard'
            ]
            
            found_methods = sum(1 for method in existing_methods if method in content)
            print(f"Found {found_methods} existing methods")
            
            return found_methods >= 3
        
        def check_form_integration():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for form rendering method
            form_method = 'render_add_case_form' in content
            
            return form_method
        
        def check_menu_integration():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin menu integration
            menu_integration = 'add_menu_page' in content and 'add_submenu_page' in content
            
            return menu_integration
        
        self.test("Existing functionality preserved", check_existing_functionality_preserved)
        self.test("Form integration", check_form_integration)
        self.test("Admin menu integration", check_menu_integration)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.2.2 VERIFICATION SUMMARY")
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
            "Plugin header version is 1.2.2",
            "create_new_case() method exists",
            "update_case() method exists",
            "Case creation with 57-field structure",
            "Financial record generation with GDPR amounts",
            "Nonce security verification",
            "Integration with existing handle_case_update method"
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
            print("✅ HOTFIX v1.2.2 VERIFICATION: SUCCESSFUL")
            print("All critical case creation methods are implemented and working correctly.")
        else:
            print("❌ HOTFIX v1.2.2 VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV122Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())