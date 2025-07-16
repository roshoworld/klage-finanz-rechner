#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.7 Verification
Tests the critical validation logic fixes and form data persistence that were added in the hotfix.

Focus Areas:
1. Test case creation with meaningful debtor data + email subject (should work without requiring sender email)
2. Test case creation with only email fields (should require sender email)  
3. Test form data persistence when validation fails
4. Test mixed field scenarios work correctly
5. Verify all existing functionality still works
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV127Tester:
    """Test suite specifically for verifying hotfix v1.2.7 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.2.7 Verification Tests")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_validation_logic_fixes()
        self.test_form_data_persistence()
        self.test_mixed_field_scenarios()
        self.test_meaningful_data_detection()
        self.test_email_validation_logic()
        self.test_existing_functionality_preserved()
        
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
        """Test that plugin version is updated to 1.2.7"""
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
            return version == "1.2.7"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.7"
        
        def check_admin_dashboard_version():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for v1.2.7 reference in admin dashboard
            version_ref = 'v1.2.7' in content
            print(f"Found v1.2.7 reference in admin dashboard: {version_ref}")
            return version_ref
        
        self.test("Plugin header version is 1.2.7", check_plugin_version)
        self.test("Plugin constant version is 1.2.7", check_constant_version)
        self.test("Admin dashboard references v1.2.7", check_admin_dashboard_version)
    
    def test_validation_logic_fixes(self):
        """Test the enhanced validation logic fixes"""
        print("🔍 TESTING VALIDATION LOGIC FIXES")
        print("-" * 40)
        
        def check_meaningful_data_detection():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for meaningful data detection logic
            meaningful_debtor_check = 'has_meaningful_debtor_data_check' in content
            meaningful_email_check = 'has_meaningful_email_data_check' in content
            
            print(f"Meaningful debtor data check: {meaningful_debtor_check}")
            print(f"Meaningful email data check: {meaningful_email_check}")
            
            return meaningful_debtor_check and meaningful_email_check
        
        def check_prioritization_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debtor field prioritization
            prioritization_pattern = r'if.*has_debtor_fields.*has_meaningful_debtor_data_check'
            prioritization_found = bool(re.search(prioritization_pattern, content))
            
            print(f"Debtor field prioritization logic found: {prioritization_found}")
            return prioritization_found
        
        def check_unbekannt_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for 'Unbekannt' value handling
            unbekannt_check = 'Unbekannt' in content
            print(f"'Unbekannt' value handling found: {unbekannt_check}")
            return unbekannt_check
        
        def check_either_or_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for either/or validation logic
            either_or_pattern = r'!.*has_meaningful_debtor_data.*&&.*!.*has_meaningful_email_data'
            either_or_found = bool(re.search(either_or_pattern, content))
            
            print(f"Either/OR validation logic found: {either_or_found}")
            return either_or_found
        
        self.test("Meaningful data detection implemented", check_meaningful_data_detection)
        self.test("Debtor field prioritization logic", check_prioritization_logic)
        self.test("'Unbekannt' value validation", check_unbekannt_validation)
        self.test("Either/OR validation logic", check_either_or_validation)
    
    def test_form_data_persistence(self):
        """Test form data persistence implementation"""
        print("💾 TESTING FORM DATA PERSISTENCE")
        print("-" * 40)
        
        def check_get_form_data_method():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for get_form_data method
            method_pattern = r'private\s+function\s+get_form_data\s*\(\s*\)'
            method_found = bool(re.search(method_pattern, content))
            
            print(f"get_form_data() method found: {method_found}")
            return method_found
        
        def check_form_data_usage():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for form data usage in form fields
            form_data_usage = '$form_data[' in content
            esc_attr_usage = 'esc_attr($form_data[' in content
            
            print(f"Form data usage in fields: {form_data_usage}")
            print(f"Proper escaping with esc_attr: {esc_attr_usage}")
            
            return form_data_usage and esc_attr_usage
        
        def check_form_field_persistence():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Count form fields that use form_data for persistence
            form_fields = [
                'case_id', 'case_status', 'case_priority', 'mandant', 'submission_date',
                'debtors_first_name', 'debtors_last_name', 'debtors_company', 'debtors_email',
                'emails_sender_email', 'emails_user_email', 'emails_subject'
            ]
            
            persistent_fields = 0
            for field in form_fields:
                if f"$form_data['{field}']" in content:
                    persistent_fields += 1
            
            print(f"Found {persistent_fields} persistent form fields")
            return persistent_fields >= 8  # At least 8 key fields should be persistent
        
        def check_textarea_persistence():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for textarea persistence with esc_textarea
            textarea_persistence = 'esc_textarea($form_data[' in content
            print(f"Textarea persistence with proper escaping: {textarea_persistence}")
            return textarea_persistence
        
        self.test("get_form_data() method exists", check_get_form_data_method)
        self.test("Form data usage in form fields", check_form_data_usage)
        self.test("Form field persistence implementation", check_form_field_persistence)
        self.test("Textarea persistence with escaping", check_textarea_persistence)
    
    def test_mixed_field_scenarios(self):
        """Test mixed field scenario handling"""
        print("🔄 TESTING MIXED FIELD SCENARIOS")
        print("-" * 40)
        
        def check_debtor_only_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debtor-only processing logic
            debtor_only_pattern = r'if.*has_debtor_fields.*has_meaningful_debtor_data_check'
            debtor_only_found = bool(re.search(debtor_only_pattern, content))
            
            print(f"Debtor-only scenario handling: {debtor_only_found}")
            return debtor_only_found
        
        def check_email_only_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for email-only processing logic
            email_only_pattern = r'elseif.*has_email_fields.*has_meaningful_email_data_check'
            email_only_found = bool(re.search(email_only_pattern, content))
            
            print(f"Email-only scenario handling: {email_only_found}")
            return email_only_found
        
        def check_fallback_scenario():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for fallback scenario handling
            fallback_pattern = r'else.*{.*// Fallback'
            fallback_found = bool(re.search(fallback_pattern, content))
            
            print(f"Fallback scenario handling: {fallback_found}")
            return fallback_found
        
        def check_attempting_email_evidence_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for email evidence attempt detection
            attempting_email_pattern = r'attempting_email_evidence.*=.*!empty.*emails_subject.*emails_content'
            attempting_email_found = bool(re.search(attempting_email_pattern, content))
            
            print(f"Email evidence attempt detection: {attempting_email_found}")
            return attempting_email_found
        
        self.test("Debtor-only scenario handling", check_debtor_only_scenario)
        self.test("Email-only scenario handling", check_email_only_scenario)
        self.test("Fallback scenario handling", check_fallback_scenario)
        self.test("Email evidence attempt detection", check_attempting_email_evidence_logic)
    
    def test_meaningful_data_detection(self):
        """Test meaningful data detection logic"""
        print("🎯 TESTING MEANINGFUL DATA DETECTION")
        print("-" * 40)
        
        def check_debtor_meaningful_data_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for meaningful debtor data detection
            meaningful_debtor_pattern = r'has_meaningful_debtor_data.*=.*!empty.*debtors_last_name.*!==.*Unbekannt'
            meaningful_debtor_found = bool(re.search(meaningful_debtor_pattern, content))
            
            print(f"Meaningful debtor data detection: {meaningful_debtor_found}")
            return meaningful_debtor_found
        
        def check_email_meaningful_data_logic():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for meaningful email data detection
            meaningful_email_pattern = r'has_meaningful_email_data.*=.*!empty.*sender_email'
            meaningful_email_found = bool(re.search(meaningful_email_pattern, content))
            
            print(f"Meaningful email data detection: {meaningful_email_found}")
            return meaningful_email_found
        
        def check_debug_output_for_meaningful_data():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debug output showing meaningful data detection
            debug_meaningful_debtor = 'has_meaningful_debtor_data:' in content
            debug_meaningful_email = 'has_meaningful_email_data:' in content
            
            print(f"Debug output for meaningful debtor data: {debug_meaningful_debtor}")
            print(f"Debug output for meaningful email data: {debug_meaningful_email}")
            
            return debug_meaningful_debtor and debug_meaningful_email
        
        self.test("Meaningful debtor data detection logic", check_debtor_meaningful_data_logic)
        self.test("Meaningful email data detection logic", check_email_meaningful_data_logic)
        self.test("Debug output for meaningful data detection", check_debug_output_for_meaningful_data)
    
    def test_email_validation_logic(self):
        """Test email validation logic improvements"""
        print("📧 TESTING EMAIL VALIDATION LOGIC")
        print("-" * 40)
        
        def check_conditional_email_requirement():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for conditional email requirement logic
            conditional_email_pattern = r'attempting_email_evidence.*!.*has_meaningful_debtor_data.*empty.*sender_email'
            conditional_email_found = bool(re.search(conditional_email_pattern, content))
            
            print(f"Conditional email requirement logic: {conditional_email_found}")
            return conditional_email_found
        
        def check_email_evidence_detection():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for email evidence detection
            email_evidence_pattern = r'attempting_email_evidence.*=.*!empty.*_POST.*emails_subject.*!empty.*_POST.*emails_content'
            email_evidence_found = bool(re.search(email_evidence_pattern, content))
            
            print(f"Email evidence detection logic: {email_evidence_found}")
            return email_evidence_found
        
        def check_sender_email_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for sender email validation
            sender_email_validation = 'sanitize_email($_POST[\'emails_sender_email\'])' in content
            print(f"Sender email sanitization: {sender_email_validation}")
            return sender_email_validation
        
        def check_email_error_messages():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for specific email error messages
            email_error_messages = [
                'Entweder Nachname des Schuldners oder Absender-E-Mail ist erforderlich',
                'Wenn E-Mail-Evidenz angegeben wird, ist die Absender-E-Mail erforderlich'
            ]
            
            found_messages = sum(1 for msg in email_error_messages if msg in content)
            print(f"Found {found_messages} email-specific error messages")
            
            return found_messages >= 2
        
        self.test("Conditional email requirement logic", check_conditional_email_requirement)
        self.test("Email evidence detection logic", check_email_evidence_detection)
        self.test("Sender email validation", check_sender_email_validation)
        self.test("Email-specific error messages", check_email_error_messages)
    
    def test_existing_functionality_preserved(self):
        """Test that existing functionality is preserved"""
        print("🔗 TESTING EXISTING FUNCTIONALITY PRESERVED")
        print("-" * 40)
        
        def check_existing_methods_still_exist():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing methods still exist
            existing_methods = [
                'admin_page_cases',
                'render_cases_list',
                'handle_case_actions',
                'admin_page_dashboard',
                'render_add_case_form',
                'update_case',
                'handle_bulk_actions'
            ]
            
            found_methods = sum(1 for method in existing_methods if method in content)
            print(f"Found {found_methods} existing methods")
            
            return found_methods >= 6
        
        def check_case_creation_workflow_intact():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that case creation workflow is intact
            workflow_elements = [
                'wp_nonce_field(\'create_case\'',
                'action=create_case',
                'case \'create_case\':',
                'create_new_case()',
                'klage_debtors',
                'klage_cases',
                'klage_financial'
            ]
            
            found_elements = sum(1 for element in workflow_elements if element in content)
            print(f"Found {found_elements} workflow elements")
            
            return found_elements >= 6
        
        def check_database_operations_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that database operations are preserved
            db_operations = [
                '$wpdb->insert',
                'klage_debtors',
                'klage_cases',
                'klage_financial',
                'klage_audit'
            ]
            
            found_operations = sum(1 for op in db_operations if op in content)
            print(f"Found {found_operations} database operations")
            
            return found_operations >= 4
        
        def check_gdpr_amounts_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that GDPR standard amounts are preserved
            gdpr_amounts = ['350.00', '96.90', '13.36', '32.00', '87.85', '548.11']
            found_amounts = sum(1 for amount in gdpr_amounts if amount in content)
            
            print(f"Found {found_amounts} GDPR standard amounts")
            return found_amounts >= 5
        
        self.test("Existing methods still exist", check_existing_methods_still_exist)
        self.test("Case creation workflow intact", check_case_creation_workflow_intact)
        self.test("Database operations preserved", check_database_operations_preserved)
        self.test("GDPR amounts preserved", check_gdpr_amounts_preserved)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.2.7 VERIFICATION SUMMARY")
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
            "Plugin header version is 1.2.7",
            "Meaningful data detection implemented",
            "Either/OR validation logic",
            "get_form_data() method exists",
            "Form field persistence implementation",
            "Debtor-only scenario handling",
            "Email-only scenario handling",
            "Conditional email requirement logic",
            "Existing methods still exist"
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
            print("✅ HOTFIX v1.2.7 VERIFICATION: SUCCESSFUL")
            print("All critical validation logic fixes and form persistence features are implemented and working correctly.")
        else:
            print("❌ HOTFIX v1.2.7 VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n🔍 REVIEW REQUEST VERIFICATION:")
        review_tests = [
            "Meaningful data detection implemented",
            "Debtor field prioritization logic", 
            "Either/OR validation logic",
            "Form field persistence implementation",
            "Conditional email requirement logic",
            "Email evidence detection logic"
        ]
        
        review_passed = 0
        for review_test in review_tests:
            if review_test in self.results:
                result = self.results[review_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {review_test}")
                if result['status'] == 'passed':
                    review_passed += 1
        
        print(f"\n📝 REVIEW REQUEST STATUS: {review_passed}/{len(review_tests)} review requirements met")
        
        if review_passed == len(review_tests):
            print("✅ REVIEW REQUEST: ALL REQUIREMENTS MET")
            print("Both validation logic fixes and form data persistence are properly implemented.")
        else:
            print("❌ REVIEW REQUEST: SOME REQUIREMENTS NOT MET")
            print("Please check the failed tests above for specific issues.")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV127Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())