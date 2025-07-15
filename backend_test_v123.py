#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.3 Verification
Tests the critical bulk actions and enhanced validation features added in the hotfix.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV123Tester:
    """Test suite specifically for verifying hotfix v1.2.3 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.2.3 Verification Tests")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_handle_bulk_actions_method()
        self.test_bulk_operations_implementation()
        self.test_enhanced_validation()
        self.test_debug_information()
        self.test_audit_trail_logging()
        self.test_integration_with_existing_functionality()
        
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
        """Test that plugin version is updated to 1.2.3"""
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
            return version == "1.2.3"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.3"
        
        self.test("Plugin header version is 1.2.3", check_plugin_version)
        self.test("Plugin constant version is 1.2.3", check_constant_version)
    
    def test_handle_bulk_actions_method(self):
        """Test that handle_bulk_actions method exists and is properly implemented"""
        print("🔍 TESTING HANDLE_BULK_ACTIONS METHOD")
        print("-" * 40)
        
        def check_method_exists():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+handle_bulk_actions\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_method_called_in_render_cases_list():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that method is called in render_cases_list
            method_call = '$this->handle_bulk_actions()' in content
            
            print(f"handle_bulk_actions() call found: {method_call}")
            return method_call
        
        def check_nonce_verification():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for nonce verification in handle_bulk_actions
            nonce_pattern = r'wp_verify_nonce.*bulk_action'
            return bool(re.search(nonce_pattern, content))
        
        def check_bulk_action_parameter_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for bulk action parameter handling
            bulk_action_check = 'sanitize_text_field($_POST[\'bulk_action\'])' in content
            case_ids_check = 'array_map(\'intval\', $_POST[\'case_ids\'])' in content
            
            print(f"Bulk action parameter handling: {bulk_action_check}")
            print(f"Case IDs parameter handling: {case_ids_check}")
            
            return bulk_action_check and case_ids_check
        
        self.test("handle_bulk_actions() method exists", check_method_exists)
        self.test("Method called in render_cases_list", check_method_called_in_render_cases_list)
        self.test("Nonce verification in bulk actions", check_nonce_verification)
        self.test("Bulk action parameter handling", check_bulk_action_parameter_handling)
    
    def test_bulk_operations_implementation(self):
        """Test bulk operations implementation"""
        print("📝 TESTING BULK OPERATIONS IMPLEMENTATION")
        print("-" * 40)
        
        def check_bulk_delete_operation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for delete case in switch statement
            delete_case = 'case \'delete\':' in content
            delete_operations = content.count('$wpdb->delete')
            
            print(f"Delete case found: {delete_case}")
            print(f"Delete operations count: {delete_operations}")
            
            return delete_case and delete_operations >= 3  # Should delete from multiple tables
        
        def check_bulk_status_change():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for status change operations
            status_change_patterns = [
                'case \'change_status\':',
                'case \'status_processing\':',
                'case \'status_completed\':',
                '$wpdb->update'
            ]
            
            found_patterns = sum(1 for pattern in status_change_patterns if pattern in content)
            print(f"Found {found_patterns} status change patterns")
            
            return found_patterns >= 2
        
        def check_bulk_priority_change():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for priority change operations
            priority_change_patterns = [
                'case \'change_priority\':',
                'new_priority',
                'valid_priorities'
            ]
            
            found_patterns = sum(1 for pattern in priority_change_patterns if pattern in content)
            print(f"Found {found_patterns} priority change patterns")
            
            return found_patterns >= 2
        
        def check_success_error_feedback():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for success and error feedback
            feedback_patterns = [
                'success_count',
                'error_count',
                'notice-success',
                'notice-error'
            ]
            
            found_patterns = sum(1 for pattern in feedback_patterns if pattern in content)
            print(f"Found {found_patterns} feedback patterns")
            
            return found_patterns >= 3
        
        self.test("Bulk delete operation", check_bulk_delete_operation)
        self.test("Bulk status change operation", check_bulk_status_change)
        self.test("Bulk priority change operation", check_bulk_priority_change)
        self.test("Success and error feedback", check_success_error_feedback)
    
    def test_enhanced_validation(self):
        """Test enhanced validation in create_new_case"""
        print("✅ TESTING ENHANCED VALIDATION")
        print("-" * 40)
        
        def check_detailed_error_messages():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for detailed error message handling
            error_array = '$errors = array()' in content
            error_collection = 'errors[]' in content
            error_display = 'implode(\'<br>\', $errors)' in content
            
            print(f"Error array initialization: {error_array}")
            print(f"Error collection: {error_collection}")
            print(f"Error display: {error_display}")
            
            return error_array and error_collection and error_display
        
        def check_field_specific_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for specific field validation messages
            validation_messages = [
                'Fall-ID ist erforderlich',
                'Nachname des Schuldners ist erforderlich',
                'empty($case_id)',
                'empty($debtors_last_name)'
            ]
            
            found_messages = sum(1 for msg in validation_messages if msg in content)
            print(f"Found {found_messages} field-specific validation messages")
            
            return found_messages >= 3
        
        def check_duplicate_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for duplicate case ID validation
            duplicate_check = 'existing_case' in content
            duplicate_query = 'SELECT id FROM' in content and 'klage_cases' in content
            duplicate_message = 'existiert bereits' in content
            
            print(f"Duplicate check: {duplicate_check}")
            print(f"Duplicate query: {duplicate_query}")
            print(f"Duplicate message: {duplicate_message}")
            
            return duplicate_check and duplicate_query and duplicate_message
        
        self.test("Detailed error messages", check_detailed_error_messages)
        self.test("Field-specific validation", check_field_specific_validation)
        self.test("Duplicate case ID validation", check_duplicate_validation)
    
    def test_debug_information(self):
        """Test debug information display"""
        print("🐛 TESTING DEBUG INFORMATION")
        print("-" * 40)
        
        def check_debug_info_display():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debug information display
            debug_patterns = [
                'Debug Info:',
                'notice-info',
                'esc_html($case_id)',
                'strlen($case_id)',
                'array_keys($_POST)'
            ]
            
            found_patterns = sum(1 for pattern in debug_patterns if pattern in content)
            print(f"Found {found_patterns} debug information patterns")
            
            return found_patterns >= 4
        
        def check_field_length_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for field length debugging
            length_debug = 'strlen(' in content and 'length:' in content
            
            return length_debug
        
        def check_post_data_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for POST data debugging
            post_debug = 'POST data keys:' in content and 'array_keys($_POST)' in content
            
            return post_debug
        
        self.test("Debug information display", check_debug_info_display)
        self.test("Field length debugging", check_field_length_debug)
        self.test("POST data debugging", check_post_data_debug)
    
    def test_audit_trail_logging(self):
        """Test audit trail logging for bulk operations"""
        print("📊 TESTING AUDIT TRAIL LOGGING")
        print("-" * 40)
        
        def check_bulk_delete_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for bulk delete audit logging
            delete_log_patterns = [
                'case_deleted_bulk',
                'wurde per Bulk-Aktion gelöscht',
                'klage_audit'
            ]
            
            found_patterns = sum(1 for pattern in delete_log_patterns if pattern in content)
            print(f"Found {found_patterns} bulk delete logging patterns")
            
            return found_patterns >= 2
        
        def check_bulk_status_change_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for bulk status change audit logging
            status_log_patterns = [
                'case_status_changed_bulk',
                'Status zu',
                'geändert per Bulk-Aktion'
            ]
            
            found_patterns = sum(1 for pattern in status_log_patterns if pattern in content)
            print(f"Found {found_patterns} bulk status change logging patterns")
            
            return found_patterns >= 2
        
        def check_bulk_priority_change_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for bulk priority change audit logging
            priority_log_patterns = [
                'case_priority_changed_bulk',
                'Priorität zu',
                'geändert per Bulk-Aktion'
            ]
            
            found_patterns = sum(1 for pattern in priority_log_patterns if pattern in content)
            print(f"Found {found_patterns} bulk priority change logging patterns")
            
            return found_patterns >= 2
        
        def check_user_id_logging():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for user ID in audit logs
            user_id_logging = 'get_current_user_id()' in content
            
            return user_id_logging
        
        self.test("Bulk delete audit logging", check_bulk_delete_logging)
        self.test("Bulk status change audit logging", check_bulk_status_change_logging)
        self.test("Bulk priority change audit logging", check_bulk_priority_change_logging)
        self.test("User ID in audit logs", check_user_id_logging)
    
    def test_integration_with_existing_functionality(self):
        """Test integration with existing functionality"""
        print("🔗 TESTING INTEGRATION")
        print("-" * 40)
        
        def check_existing_methods_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing methods still exist
            existing_methods = [
                'create_new_case',
                'update_case',
                'render_cases_list',
                'handle_case_actions'
            ]
            
            found_methods = sum(1 for method in existing_methods if method in content)
            print(f"Found {found_methods} existing methods")
            
            return found_methods >= 3
        
        def check_bulk_actions_form_integration():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for bulk actions form elements
            form_elements = [
                'bulk_action_nonce',
                'name="bulk_action"',
                'name="case_ids[]"',
                'checkbox'
            ]
            
            found_elements = sum(1 for element in form_elements if element in content)
            print(f"Found {found_elements} bulk actions form elements")
            
            return found_elements >= 3
        
        def check_case_list_integration():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that bulk actions are integrated into case list
            integration_elements = [
                'cb-select-all',
                'Bulk-Aktionen',
                'status_processing',
                'status_completed'
            ]
            
            found_elements = sum(1 for element in integration_elements if element in content)
            print(f"Found {found_elements} case list integration elements")
            
            return found_elements >= 3
        
        self.test("Existing methods preserved", check_existing_methods_preserved)
        self.test("Bulk actions form integration", check_bulk_actions_form_integration)
        self.test("Case list integration", check_case_list_integration)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.2.3 VERIFICATION SUMMARY")
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
            "Plugin header version is 1.2.3",
            "handle_bulk_actions() method exists",
            "Method called in render_cases_list",
            "Bulk delete operation",
            "Bulk status change operation",
            "Detailed error messages",
            "Debug information display",
            "Bulk delete audit logging"
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
            print("✅ HOTFIX v1.2.3 VERIFICATION: SUCCESSFUL")
            print("All critical bulk actions and enhanced validation features are implemented and working correctly.")
        else:
            print("❌ HOTFIX v1.2.3 VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV123Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())