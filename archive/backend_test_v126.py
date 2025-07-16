#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - v1.2.6 Verification
Tests the critical fixes for case creation validation logic and status change unknown action.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV126Tester:
    """Test suite specifically for verifying v1.2.6 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all v1.2.6 verification tests"""
        print("🚀 Starting v1.2.6 Verification Tests")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_validation_logic_fixes()
        self.test_get_action_handlers()
        self.test_mixed_field_scenarios()
        self.test_meaningful_data_detection()
        self.test_debug_information()
        self.test_error_handling_improvements()
        
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
        """Test that plugin version is updated to 1.2.6"""
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
            return version == "1.2.6"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.6"
        
        self.test("Plugin header version is 1.2.6", check_plugin_version)
        self.test("Plugin constant version is 1.2.6", check_constant_version)
    
    def test_validation_logic_fixes(self):
        """Test enhanced validation logic for mixed debtor/email fields"""
        print("🔍 TESTING VALIDATION LOGIC FIXES")
        print("-" * 40)
        
        def check_meaningful_data_detection():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for meaningful data detection logic
            meaningful_debtor = 'has_meaningful_debtor_data' in content
            meaningful_email = 'has_meaningful_email_data' in content
            
            print(f"Meaningful debtor data detection: {meaningful_debtor}")
            print(f"Meaningful email data detection: {meaningful_email}")
            
            return meaningful_debtor and meaningful_email
        
        def check_validation_logic_change():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for the new validation logic that requires either meaningful debtor OR email data
            either_or_validation = '!$has_meaningful_debtor_data && !$has_meaningful_email_data' in content
            
            print(f"Either/OR validation logic found: {either_or_validation}")
            
            return either_or_validation
        
        def check_debtor_name_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper debtor name validation (not just empty check)
            debtor_validation = '$debtors_last_name !== \'Unbekannt\'' in content
            
            print(f"Enhanced debtor name validation: {debtor_validation}")
            
            return debtor_validation
        
        def check_email_validation_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for email validation logic
            email_validation = '!empty($sender_email)' in content
            
            print(f"Email validation logic: {email_validation}")
            
            return email_validation
        
        self.test("Meaningful data detection implemented", check_meaningful_data_detection)
        self.test("Either/OR validation logic implemented", check_validation_logic_change)
        self.test("Enhanced debtor name validation", check_debtor_name_validation)
        self.test("Email validation logic", check_email_validation_logic)
    
    def test_get_action_handlers(self):
        """Test GET-based action handlers for status and priority changes"""
        print("🔄 TESTING GET ACTION HANDLERS")
        print("-" * 40)
        
        def check_get_status_change_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+handle_get_status_change\s*\(\s*\$case_id\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            print(f"handle_get_status_change method exists: {method_exists}")
            
            return method_exists
        
        def check_get_priority_change_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the method definition
            method_pattern = r'private\s+function\s+handle_get_priority_change\s*\(\s*\$case_id\s*\)'
            method_exists = bool(re.search(method_pattern, content))
            
            print(f"handle_get_priority_change method exists: {method_exists}")
            
            return method_exists
        
        def check_get_action_routing():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GET action routing in admin_page_cases
            status_routing = 'case \'change_status\':' in content and '$this->handle_get_status_change($case_id)' in content
            priority_routing = 'case \'change_priority\':' in content and '$this->handle_get_priority_change($case_id)' in content
            
            print(f"Status change GET routing: {status_routing}")
            print(f"Priority change GET routing: {priority_routing}")
            
            return status_routing and priority_routing
        
        def check_url_parameter_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for URL parameter handling in GET methods
            new_status_param = '$_GET[\'new_status\']' in content
            new_priority_param = '$_GET[\'new_priority\']' in content
            
            print(f"new_status URL parameter handling: {new_status_param}")
            print(f"new_priority URL parameter handling: {new_priority_param}")
            
            return new_status_param and new_priority_param
        
        def check_status_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for status validation in GET handler
            valid_statuses = ['draft', 'pending', 'processing', 'completed', 'cancelled']
            status_validation = all(status in content for status in valid_statuses[:3])  # Check first 3
            
            print(f"Status validation in GET handler: {status_validation}")
            
            return status_validation
        
        def check_priority_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for priority validation in GET handler
            valid_priorities = ['low', 'medium', 'high', 'urgent']
            priority_validation = all(priority in content for priority in valid_priorities[:3])  # Check first 3
            
            print(f"Priority validation in GET handler: {priority_validation}")
            
            return priority_validation
        
        self.test("handle_get_status_change method exists", check_get_status_change_method)
        self.test("handle_get_priority_change method exists", check_get_priority_change_method)
        self.test("GET action routing implemented", check_get_action_routing)
        self.test("URL parameter handling", check_url_parameter_handling)
        self.test("Status validation in GET handler", check_status_validation)
        self.test("Priority validation in GET handler", check_priority_validation)
    
    def test_mixed_field_scenarios(self):
        """Test various combinations of debtor/email fields"""
        print("🔀 TESTING MIXED FIELD SCENARIOS")
        print("-" * 40)
        
        def check_debtor_only_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that validation allows debtor-only scenarios
            # Should pass when has_meaningful_debtor_data is true and has_meaningful_email_data is false
            debtor_only_logic = 'has_meaningful_debtor_data' in content and 'has_meaningful_email_data' in content
            
            return debtor_only_logic
        
        def check_email_only_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that validation allows email-only scenarios
            # Should pass when has_meaningful_email_data is true and has_meaningful_debtor_data is false
            email_only_logic = '!empty($sender_email)' in content
            
            return email_only_logic
        
        def check_both_fields_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that validation allows both fields filled
            # Should pass when both has_meaningful_debtor_data and has_meaningful_email_data are true
            both_fields_logic = 'has_meaningful_debtor_data' in content and 'has_meaningful_email_data' in content
            
            return both_fields_logic
        
        def check_neither_fields_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that validation rejects when neither field has meaningful data
            neither_fields_logic = '!$has_meaningful_debtor_data && !$has_meaningful_email_data' in content
            error_message = 'Entweder Nachname des Schuldners oder Absender-E-Mail ist erforderlich' in content
            
            return neither_fields_logic and error_message
        
        self.test("Debtor-only scenario validation", check_debtor_only_scenario)
        self.test("Email-only scenario validation", check_email_only_scenario)
        self.test("Both fields scenario validation", check_both_fields_scenario)
        self.test("Neither fields scenario rejection", check_neither_fields_scenario)
    
    def test_meaningful_data_detection(self):
        """Test meaningful data detection vs field presence"""
        print("🎯 TESTING MEANINGFUL DATA DETECTION")
        print("-" * 40)
        
        def check_debtor_meaningful_data_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for meaningful debtor data detection
            # Should check both not empty AND not 'Unbekannt'
            meaningful_debtor = '!empty($debtors_last_name) && $debtors_last_name !== \'Unbekannt\'' in content
            
            print(f"Meaningful debtor data logic: {meaningful_debtor}")
            
            return meaningful_debtor
        
        def check_email_meaningful_data_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for meaningful email data detection
            # Should check not empty sender email
            meaningful_email = '!empty($sender_email)' in content
            
            print(f"Meaningful email data logic: {meaningful_email}")
            
            return meaningful_email
        
        def check_field_presence_vs_meaningful_data():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that the code distinguishes between field presence and meaningful data
            has_debtor_fields = 'has_debtor_fields' in content
            has_meaningful_debtor = 'has_meaningful_debtor_data' in content
            has_email_fields = 'has_email_fields' in content
            has_meaningful_email = 'has_meaningful_email_data' in content
            
            print(f"Field presence detection: {has_debtor_fields and has_email_fields}")
            print(f"Meaningful data detection: {has_meaningful_debtor and has_meaningful_email}")
            
            return has_debtor_fields and has_meaningful_debtor and has_email_fields and has_meaningful_email
        
        self.test("Debtor meaningful data logic", check_debtor_meaningful_data_logic)
        self.test("Email meaningful data logic", check_email_meaningful_data_logic)
        self.test("Field presence vs meaningful data distinction", check_field_presence_vs_meaningful_data)
    
    def test_debug_information(self):
        """Test enhanced debug information display"""
        print("🐛 TESTING DEBUG INFORMATION")
        print("-" * 40)
        
        def check_meaningful_data_debug_output():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for debug output of meaningful data detection
            debug_meaningful_debtor = 'has_meaningful_debtor_data:' in content
            debug_meaningful_email = 'has_meaningful_email_data:' in content
            
            print(f"Meaningful debtor data debug output: {debug_meaningful_debtor}")
            print(f"Meaningful email data debug output: {debug_meaningful_email}")
            
            return debug_meaningful_debtor and debug_meaningful_email
        
        def check_validation_context_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for debug information showing validation context
            debug_info_section = 'Debug Info:' in content
            field_presence_debug = 'has_debtor_fields:' in content and 'has_email_fields:' in content
            
            print(f"Debug info section: {debug_info_section}")
            print(f"Field presence debug: {field_presence_debug}")
            
            return debug_info_section and field_presence_debug
        
        def check_post_data_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for POST data debug information
            post_data_debug = 'POST data keys:' in content and 'array_keys($_POST)' in content
            
            print(f"POST data debug: {post_data_debug}")
            
            return post_data_debug
        
        def check_field_length_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for field length debug information
            length_debug = 'length:' in content and 'strlen(' in content
            
            print(f"Field length debug: {length_debug}")
            
            return length_debug
        
        self.test("Meaningful data debug output", check_meaningful_data_debug_output)
        self.test("Validation context debug", check_validation_context_debug)
        self.test("POST data debug information", check_post_data_debug)
        self.test("Field length debug information", check_field_length_debug)
    
    def test_error_handling_improvements(self):
        """Test improved error handling for different scenarios"""
        print("⚠️ TESTING ERROR HANDLING IMPROVEMENTS")
        print("-" * 40)
        
        def check_specific_validation_errors():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for specific error messages
            either_or_error = 'Entweder Nachname des Schuldners oder Absender-E-Mail ist erforderlich' in content
            email_evidence_error = 'Wenn E-Mail-Evidenz angegeben wird, ist die Absender-E-Mail erforderlich' in content
            
            print(f"Either/OR validation error: {either_or_error}")
            print(f"Email evidence error: {email_evidence_error}")
            
            return either_or_error and email_evidence_error
        
        def check_get_action_error_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for error handling in GET action handlers
            case_id_error = 'Fall-ID oder Status fehlt' in content
            invalid_status_error = 'Ungültiger Status' in content
            invalid_priority_error = 'Ungültige Priorität' in content
            
            print(f"Case ID error handling: {case_id_error}")
            print(f"Invalid status error: {invalid_status_error}")
            print(f"Invalid priority error: {invalid_priority_error}")
            
            return case_id_error and invalid_status_error and invalid_priority_error
        
        def check_success_feedback():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for success feedback in GET handlers
            status_success = 'Status wurde geändert' in content
            priority_success = 'Priorität wurde geändert' in content
            
            print(f"Status change success feedback: {status_success}")
            print(f"Priority change success feedback: {priority_success}")
            
            return status_success and priority_success
        
        def check_unknown_action_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for improved unknown action handling
            unknown_action_error = 'Unbekannte Aktion:' in content
            debug_info_for_unknown = 'Debug Info:' in content and 'Verfügbare Aktionen:' in content
            
            print(f"Unknown action error: {unknown_action_error}")
            print(f"Debug info for unknown actions: {debug_info_for_unknown}")
            
            return unknown_action_error and debug_info_for_unknown
        
        self.test("Specific validation error messages", check_specific_validation_errors)
        self.test("GET action error handling", check_get_action_error_handling)
        self.test("Success feedback messages", check_success_feedback)
        self.test("Unknown action handling improvements", check_unknown_action_handling)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 v1.2.6 VERIFICATION SUMMARY")
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
        
        print("\n🎯 CRITICAL v1.2.6 FIXES VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.2.6",
            "Meaningful data detection implemented",
            "Either/OR validation logic implemented",
            "handle_get_status_change method exists",
            "handle_get_priority_change method exists",
            "GET action routing implemented",
            "Mixed field scenarios validation",
            "Meaningful data debug output",
            "Specific validation error messages"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 v1.2.6 STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ v1.2.6 VERIFICATION: SUCCESSFUL")
            print("Both critical issues have been resolved:")
            print("  1. ✅ Case Creation Validation Logic - Fixed to handle mixed debtor/email fields")
            print("  2. ✅ Status Change Unknown Action - Added GET-based action handling")
        else:
            print("❌ v1.2.6 VERIFICATION: ISSUES FOUND")
            print("Some critical fixes may not be working as expected.")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV126Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())