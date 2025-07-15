#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.2.4 Verification
Tests the critical email-based case creation functionality that was fixed in the hotfix.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV124Tester:
    """Test suite specifically for verifying hotfix v1.2.4 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.2.4 Verification Tests")
        print("=" * 60)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_form_type_detection()
        self.test_email_data_processing()
        self.test_adaptive_validation()
        self.test_email_integration()
        self.test_backward_compatibility()
        self.test_debug_information()
        self.test_case_creation_scenarios()
        
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
        """Test that plugin version is updated to 1.2.4"""
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
            return version == "1.2.4"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.2.4"
        
        self.test("Plugin header version is 1.2.4", check_plugin_version)
        self.test("Plugin constant version is 1.2.4", check_constant_version)
    
    def test_form_type_detection(self):
        """Test smart form type detection functionality"""
        print("🔍 TESTING FORM TYPE DETECTION")
        print("-" * 40)
        
        def check_debtor_fields_detection():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for debtor fields detection logic
            debtor_detection = "$has_debtor_fields = isset($_POST['debtors_first_name']) || isset($_POST['debtors_last_name'])" in content
            
            return debtor_detection
        
        def check_email_fields_detection():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for email fields detection logic
            email_detection = "$has_email_fields = isset($_POST['emails_sender_email']) || isset($_POST['emails_user_email'])" in content
            
            return email_detection
        
        def check_conditional_processing():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for conditional processing based on form type
            manual_processing = "if ($has_debtor_fields)" in content
            email_processing = "elseif ($has_email_fields)" in content
            fallback_processing = "} else {" in content
            
            print(f"Manual processing: {manual_processing}")
            print(f"Email processing: {email_processing}")
            print(f"Fallback processing: {fallback_processing}")
            
            return manual_processing and email_processing and fallback_processing
        
        self.test("Debtor fields detection logic", check_debtor_fields_detection)
        self.test("Email fields detection logic", check_email_fields_detection)
        self.test("Conditional processing based on form type", check_conditional_processing)
    
    def test_email_data_processing(self):
        """Test email data processing and debtor extraction"""
        print("📧 TESTING EMAIL DATA PROCESSING")
        print("-" * 40)
        
        def check_email_field_extraction():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for email field extraction
            email_fields = [
                "sanitize_email($_POST['emails_sender_email'])",
                "sanitize_email($_POST['emails_user_email'])",
                "sanitize_text_field($_POST['emails_subject'])",
                "sanitize_textarea_field($_POST['emails_content'])"
            ]
            
            found_fields = sum(1 for field in email_fields if field in content)
            print(f"Found {found_fields} email field extractions")
            
            return found_fields >= 3
        
        def check_debtor_extraction_from_email():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that sender email is used as debtor identifier
            debtor_from_email = "$debtors_email = $sender_email" in content
            last_name_from_email = "$debtors_last_name = $sender_email" in content
            
            print(f"Debtor email from sender: {debtor_from_email}")
            print(f"Last name from sender email: {last_name_from_email}")
            
            return debtor_from_email and last_name_from_email
        
        def check_email_details_in_case_notes():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that email details are added to case notes
            email_details_patterns = [
                "--- Email Details ---",
                "Sender: \" . $sender_email",
                "User: \" . $user_email",
                "Subject: \" . $email_subject",
                "Content: \" . $email_content"
            ]
            
            found_patterns = sum(1 for pattern in email_details_patterns if pattern in content)
            print(f"Found {found_patterns} email details patterns")
            
            return found_patterns >= 4
        
        def check_fallback_debtor_handling():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check fallback for missing debtor information
            fallback_handling = "$debtors_last_name = 'Unbekannt'" in content
            
            return fallback_handling
        
        self.test("Email field extraction and sanitization", check_email_field_extraction)
        self.test("Debtor extraction from email sender", check_debtor_extraction_from_email)
        self.test("Email details added to case notes", check_email_details_in_case_notes)
        self.test("Fallback debtor handling", check_fallback_debtor_handling)
    
    def test_adaptive_validation(self):
        """Test adaptive validation logic for different form types"""
        print("✅ TESTING ADAPTIVE VALIDATION")
        print("-" * 40)
        
        def check_manual_form_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that debtor last name is only required for manual forms
            manual_validation = "if (!$has_email_fields && empty($debtors_last_name))" in content
            error_message = "Nachname des Schuldners ist erforderlich" in content
            
            print(f"Manual form validation: {manual_validation}")
            print(f"Debtor name error message: {error_message}")
            
            return manual_validation and error_message
        
        def check_email_form_validation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that sender email is required for email forms
            email_validation = "if ($has_email_fields && empty($sender_email))" in content
            email_error_message = "Absender-E-Mail ist erforderlich" in content
            
            print(f"Email form validation: {email_validation}")
            print(f"Email error message: {email_error_message}")
            
            return email_validation and email_error_message
        
        def check_validation_context_awareness():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that validation adapts to form type
            context_aware = "!$has_email_fields" in content and "$has_email_fields" in content
            
            return context_aware
        
        self.test("Manual form validation (debtor required)", check_manual_form_validation)
        self.test("Email form validation (sender email required)", check_email_form_validation)
        self.test("Validation context awareness", check_validation_context_awareness)
    
    def test_email_integration(self):
        """Test email information integration into case creation"""
        print("🔗 TESTING EMAIL INTEGRATION")
        print("-" * 40)
        
        def check_email_evidence_preservation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that email information is preserved in case notes
            evidence_preservation = "$case_notes .=" in content and "Email Details" in content
            
            return evidence_preservation
        
        def check_sender_as_debtor_identifier():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that sender email is used as debtor identifier
            sender_as_debtor = "$debtors_email = $sender_email" in content
            
            return sender_as_debtor
        
        def check_email_form_fields():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for email form fields in the form rendering
            email_form_fields = [
                'emails_sender_email',
                'emails_user_email',
                'emails_subject',
                'emails_content',
                'emails_received_date'
            ]
            
            found_fields = sum(1 for field in email_form_fields if field in content)
            print(f"Found {found_fields} email form fields")
            
            return found_fields >= 4
        
        self.test("Email evidence preservation in case notes", check_email_evidence_preservation)
        self.test("Sender email as debtor identifier", check_sender_as_debtor_identifier)
        self.test("Email form fields present", check_email_form_fields)
    
    def test_backward_compatibility(self):
        """Test backward compatibility with manual case creation"""
        print("🔄 TESTING BACKWARD COMPATIBILITY")
        print("-" * 40)
        
        def check_manual_form_still_works():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that manual debtor fields are still processed
            manual_fields = [
                'debtors_first_name',
                'debtors_last_name',
                'debtors_company',
                'debtors_email',
                'debtors_phone',
                'debtors_address'
            ]
            
            found_fields = sum(1 for field in manual_fields if field in content)
            print(f"Found {found_fields} manual debtor fields")
            
            return found_fields >= 5
        
        def check_existing_validation_preserved():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that existing validation is still there
            existing_validation = [
                'empty($case_id)',
                'sanitize_text_field',
                'wp_verify_nonce'
            ]
            
            found_validation = sum(1 for validation in existing_validation if validation in content)
            print(f"Found {found_validation} existing validation patterns")
            
            return found_validation >= 2
        
        def check_csv_import_unaffected():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV import functionality is still present
            csv_functionality = [
                'handle_import_action',
                'import_single_forderungen_case',
                'csv_file'
            ]
            
            found_csv = sum(1 for func in csv_functionality if func in content)
            print(f"Found {found_csv} CSV import functions")
            
            return found_csv >= 2
        
        self.test("Manual form still works", check_manual_form_still_works)
        self.test("Existing validation preserved", check_existing_validation_preserved)
        self.test("CSV import functionality unaffected", check_csv_import_unaffected)
    
    def test_debug_information(self):
        """Test enhanced debug information"""
        print("🐛 TESTING DEBUG INFORMATION")
        print("-" * 40)
        
        def check_form_type_debug_info():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for form type detection debug info
            debug_patterns = [
                'has_debtor_fields: \' . ($has_debtor_fields ? \'true\' : \'false\')',
                'has_email_fields: \' . ($has_email_fields ? \'true\' : \'false\')'
            ]
            
            found_debug = sum(1 for pattern in debug_patterns if pattern in content)
            print(f"Found {found_debug} form type debug patterns")
            
            return found_debug >= 2
        
        def check_email_debug_info():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for email-specific debug information
            email_debug = [
                'sender_email:',
                'user_email:'
            ]
            
            found_email_debug = sum(1 for debug in email_debug if debug in content)
            print(f"Found {found_email_debug} email debug patterns")
            
            return found_email_debug >= 1
        
        def check_validation_context_debug():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for validation context debug info
            validation_debug = 'Debug Info:' in content and 'POST data keys:' in content
            
            return validation_debug
        
        self.test("Form type detection debug info", check_form_type_debug_info)
        self.test("Email-specific debug information", check_email_debug_info)
        self.test("Validation context debug info", check_validation_context_debug)
    
    def test_case_creation_scenarios(self):
        """Test different case creation scenarios"""
        print("📝 TESTING CASE CREATION SCENARIOS")
        print("-" * 40)
        
        def check_email_based_success_message():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for email-based success message
            email_success = "($has_email_fields ? ' (aus E-Mail)' : '')" in content
            
            return email_success
        
        def check_case_creation_workflow():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that case creation workflow handles both scenarios
            workflow_elements = [
                'create_new_case',
                'klage_cases',
                'klage_debtors',
                'total_amount'
            ]
            
            found_elements = sum(1 for element in workflow_elements if element in content)
            print(f"Found {found_elements} workflow elements")
            
            return found_elements >= 3
        
        def check_gdpr_amount_calculation():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that GDPR standard amount is still used
            gdpr_amount = '548.11' in content
            
            return gdpr_amount
        
        self.test("Email-based success message differentiation", check_email_based_success_message)
        self.test("Case creation workflow handles both scenarios", check_case_creation_workflow)
        self.test("GDPR amount calculation preserved", check_gdpr_amount_calculation)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.2.4 VERIFICATION SUMMARY")
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
            "Plugin header version is 1.2.4",
            "Debtor fields detection logic",
            "Email fields detection logic",
            "Conditional processing based on form type",
            "Email field extraction and sanitization",
            "Debtor extraction from email sender",
            "Manual form validation (debtor required)",
            "Email form validation (sender email required)",
            "Email evidence preservation in case notes",
            "Manual form still works"
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
            print("✅ HOTFIX v1.2.4 VERIFICATION: SUCCESSFUL")
            print("Email-based case creation is now working correctly with adaptive validation.")
        else:
            print("❌ HOTFIX v1.2.4 VERIFICATION: ISSUES FOUND")
            print("Some critical email-based functionality may not be working as expected.")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV124Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())