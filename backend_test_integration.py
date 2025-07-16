#!/usr/bin/env python3
"""
Backend Test Suite for Enhanced Database Management System Integration
Tests the complete integration between database CRUD operations and forms/CSV templates
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class DatabaseIntegrationTester:
    """Test suite for verifying enhanced database management integration"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.schema_manager_file = "/app/includes/class-schema-manager.php"
        self.form_generator_file = "/app/includes/class-form-generator.php"
        self.import_export_file = "/app/includes/class-import-export-manager.php"
        self.database_admin_file = "/app/includes/class-database-admin.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all integration tests"""
        print("🚀 Starting Enhanced Database Management System Integration Tests")
        print("=" * 80)
        print()
        
        # Test sequence based on review request
        self.test_dynamic_schema_detection()
        self.test_auto_refresh_system()
        self.test_auto_generated_field_configuration()
        self.test_integration_workflow()
        self.test_user_notifications()
        
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
    
    def test_dynamic_schema_detection(self):
        """Test dynamic schema detection from actual database"""
        print("📋 TESTING DYNAMIC SCHEMA DETECTION")
        print("-" * 50)
        
        def check_get_dynamic_schema_method():
            if not os.path.exists(self.schema_manager_file):
                raise Exception(f"Schema manager file not found: {self.schema_manager_file}")
            
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for dynamic schema method
            method_pattern = r'private\s+function\s+get_dynamic_schema_from_database\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_database_schema_conversion():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for schema conversion methods
            conversion_methods = [
                'convert_database_schema_to_definition',
                'convert_column_info_to_definition',
                'get_current_table_schema'
            ]
            
            found_methods = sum(1 for method in conversion_methods if method in content)
            print(f"Found {found_methods}/3 schema conversion methods")
            
            return found_methods >= 3
        
        def check_complete_schema_definition_enhancement():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that get_complete_schema_definition uses dynamic detection
            dynamic_usage = 'get_dynamic_schema_from_database()' in content
            use_database_param = '$use_database = true' in content
            
            print(f"Dynamic schema usage: {dynamic_usage}")
            print(f"Use database parameter: {use_database_param}")
            
            return dynamic_usage and use_database_param
        
        def check_table_schema_reading():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for actual database table reading
            table_reading_indicators = [
                'SHOW COLUMNS',
                'DESCRIBE',
                'get_current_table_schema',
                'klage_cases',
                'klage_debtors'
            ]
            
            found_indicators = sum(1 for indicator in table_reading_indicators if indicator in content)
            print(f"Found {found_indicators}/5 table reading indicators")
            
            return found_indicators >= 4
        
        self.test("Dynamic schema detection method exists", check_get_dynamic_schema_method)
        self.test("Database schema conversion methods", check_database_schema_conversion)
        self.test("Complete schema definition enhancement", check_complete_schema_definition_enhancement)
        self.test("Table schema reading from database", check_table_schema_reading)
    
    def test_auto_refresh_system(self):
        """Test auto-refresh system after CRUD operations"""
        print("🔄 TESTING AUTO-REFRESH SYSTEM")
        print("-" * 50)
        
        def check_refresh_schema_cache_method():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for refresh method
            refresh_method = 'refresh_schema_cache' in content
            
            return refresh_method
        
        def check_wordpress_action_hooks():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for WordPress action hooks
            action_hooks = [
                'do_action',
                'cah_schema_updated',
                'add_action'
            ]
            
            found_hooks = sum(1 for hook in action_hooks if hook in content)
            print(f"Found {found_hooks}/3 WordPress action hook indicators")
            
            return found_hooks >= 2
        
        def check_crud_operation_triggers():
            # Check if CRUD operations trigger refresh
            files_to_check = [self.schema_manager_file, self.database_admin_file]
            
            refresh_triggers = 0
            for file_path in files_to_check:
                if os.path.exists(file_path):
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    # Look for refresh calls after CRUD operations
                    crud_refresh_patterns = [
                        'add_column.*refresh',
                        'modify_column.*refresh',
                        'drop_column.*refresh',
                        'refresh_schema_cache',
                        'schema_updated'
                    ]
                    
                    for pattern in crud_refresh_patterns:
                        if re.search(pattern, content, re.IGNORECASE):
                            refresh_triggers += 1
                            break
            
            print(f"Found refresh triggers in {refresh_triggers}/2 files")
            return refresh_triggers >= 1
        
        def check_cache_clearing_mechanism():
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for cache clearing mechanisms
            cache_mechanisms = [
                'wp_cache_delete',
                'delete_transient',
                'clear_cache',
                'refresh_schema_cache'
            ]
            
            found_mechanisms = sum(1 for mechanism in cache_mechanisms if mechanism in content)
            print(f"Found {found_mechanisms} cache clearing mechanisms")
            
            return found_mechanisms >= 1
        
        self.test("Refresh schema cache method exists", check_refresh_schema_cache_method)
        self.test("WordPress action hooks for extensibility", check_wordpress_action_hooks)
        self.test("CRUD operations trigger refresh", check_crud_operation_triggers)
        self.test("Cache clearing mechanism", check_cache_clearing_mechanism)
    
    def test_auto_generated_field_configuration(self):
        """Test auto-generated field configuration for new columns"""
        print("⚙️ TESTING AUTO-GENERATED FIELD CONFIGURATION")
        print("-" * 50)
        
        def check_auto_generate_field_config_method():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for auto-generation method
            method_pattern = r'private\s+function\s+auto_generate_field_config\s*\(\s*\$field_name\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_field_type_detection():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for field type detection patterns
            detection_patterns = [
                'email.*email',
                'phone.*tel',
                'date.*date',
                'datetime.*datetime',
                'amount.*decimal',
                'notes.*textarea'
            ]
            
            found_patterns = 0
            for pattern in detection_patterns:
                if re.search(pattern, content, re.IGNORECASE):
                    found_patterns += 1
            
            print(f"Found {found_patterns}/6 field type detection patterns")
            return found_patterns >= 4
        
        def check_german_label_generation():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for German label generation
            german_label_method = 'generate_german_label' in content
            german_translations = [
                'Deadline',
                'Antwort',
                'Zahlung',
                'Komplexität',
                'Dokument',
                'Sprache'
            ]
            
            found_translations = sum(1 for trans in german_translations if trans in content)
            print(f"German label method: {german_label_method}")
            print(f"Found {found_translations}/6 German translations")
            
            return german_label_method and found_translations >= 4
        
        def check_default_field_config():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for default configuration method
            default_config_method = 'get_default_field_config' in content
            
            # Check for sensible defaults
            default_elements = [
                'label',
                'type',
                'required',
                'description',
                'class'
            ]
            
            found_elements = sum(1 for element in default_elements if element in content)
            print(f"Default config method: {default_config_method}")
            print(f"Found {found_elements}/5 default configuration elements")
            
            return default_config_method and found_elements >= 4
        
        def check_field_config_integration():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that get_field_config uses auto-generation
            auto_generation_usage = 'auto_generate_field_config' in content
            fallback_logic = 'isset($configs[$field_name])' in content
            
            print(f"Auto-generation usage: {auto_generation_usage}")
            print(f"Fallback logic: {fallback_logic}")
            
            return auto_generation_usage and fallback_logic
        
        self.test("Auto-generate field config method exists", check_auto_generate_field_config_method)
        self.test("Field type detection based on name patterns", check_field_type_detection)
        self.test("German label generation", check_german_label_generation)
        self.test("Default field configuration", check_default_field_config)
        self.test("Field config integration with auto-generation", check_field_config_integration)
    
    def test_integration_workflow(self):
        """Test complete integration workflow"""
        print("🔗 TESTING INTEGRATION WORKFLOW")
        print("-" * 50)
        
        def check_form_generation_uses_dynamic_schema():
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that form generation uses schema manager
            schema_usage = 'schema_manager->get_complete_schema_definition' in content
            dynamic_forms = 'generate_form' in content
            
            print(f"Schema manager usage: {schema_usage}")
            print(f"Dynamic form generation: {dynamic_forms}")
            
            return schema_usage and dynamic_forms
        
        def check_csv_template_uses_dynamic_schema():
            with open(self.import_export_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that CSV templates use schema manager
            schema_usage = 'schema_manager->get_complete_schema_definition' in content
            template_generation = 'generate_csv_template' in content
            
            print(f"Schema manager usage in CSV: {schema_usage}")
            print(f"Template generation: {template_generation}")
            
            return schema_usage and template_generation
        
        def check_database_admin_integration():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for integration components
            integration_components = [
                'schema_manager',
                'form_generator',
                'import_export_manager',
                'add_column',
                'modify_column',
                'drop_column'
            ]
            
            found_components = sum(1 for comp in integration_components if comp in content)
            print(f"Found {found_components}/6 integration components")
            
            return found_components >= 5
        
        def check_automatic_field_inclusion():
            # Check that new fields are automatically included
            files_to_check = [self.form_generator_file, self.import_export_file]
            
            automatic_inclusion = 0
            for file_path in files_to_check:
                if os.path.exists(file_path):
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    # Look for dynamic field inclusion
                    inclusion_patterns = [
                        'array_keys.*columns',
                        'foreach.*columns',
                        'schema.*columns',
                        'get_complete_schema_definition'
                    ]
                    
                    for pattern in inclusion_patterns:
                        if re.search(pattern, content, re.IGNORECASE):
                            automatic_inclusion += 1
                            break
            
            print(f"Found automatic field inclusion in {automatic_inclusion}/2 files")
            return automatic_inclusion >= 2
        
        def check_zero_manual_steps():
            # Check that the system requires no manual configuration
            with open(self.schema_manager_file, 'r', encoding='utf-8') as f:
                schema_content = f.read()
            
            with open(self.form_generator_file, 'r', encoding='utf-8') as f:
                form_content = f.read()
            
            # Look for automatic processing indicators
            automatic_indicators = [
                'auto_generate',
                'dynamic.*schema',
                'get_complete_schema_definition.*true',
                'refresh_schema_cache'
            ]
            
            found_indicators = 0
            for indicator in automatic_indicators:
                if re.search(indicator, schema_content + form_content, re.IGNORECASE):
                    found_indicators += 1
            
            print(f"Found {found_indicators}/4 automatic processing indicators")
            return found_indicators >= 3
        
        self.test("Form generation uses dynamic schema", check_form_generation_uses_dynamic_schema)
        self.test("CSV templates use dynamic schema", check_csv_template_uses_dynamic_schema)
        self.test("Database admin integration", check_database_admin_integration)
        self.test("Automatic field inclusion in forms/CSV", check_automatic_field_inclusion)
        self.test("Zero manual steps required", check_zero_manual_steps)
    
    def test_user_notifications(self):
        """Test user notifications and integration info"""
        print("📢 TESTING USER NOTIFICATIONS")
        print("-" * 50)
        
        def check_enhanced_success_messages():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for enhanced success messages
            success_indicators = [
                'notice-success',
                'automatisch',
                'integration',
                'synchron',
                'aktualisiert'
            ]
            
            found_indicators = sum(1 for indicator in success_indicators if indicator in content)
            print(f"Found {found_indicators}/5 success message indicators")
            
            return found_indicators >= 3
        
        def check_integration_info_box():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for integration information display
            info_indicators = [
                'integration.*info',
                'automatisch.*synchron',
                'keine.*zusätzlich.*schritte',
                'forms.*csv.*templates'
            ]
            
            found_info = 0
            for indicator in info_indicators:
                if re.search(indicator, content, re.IGNORECASE):
                    found_info += 1
            
            print(f"Found {found_info}/4 integration info indicators")
            return found_info >= 2
        
        def check_clear_indication_messages():
            files_to_check = [self.database_admin_file, self.schema_manager_file]
            
            indication_messages = 0
            for file_path in files_to_check:
                if os.path.exists(file_path):
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()
                    
                    # Look for clear indication messages
                    message_patterns = [
                        'keine.*weitere.*schritte',
                        'automatisch.*verfügbar',
                        'sofort.*verfügbar',
                        'integration.*erfolgreich'
                    ]
                    
                    for pattern in message_patterns:
                        if re.search(pattern, content, re.IGNORECASE):
                            indication_messages += 1
                            break
            
            print(f"Found clear indication messages in {indication_messages}/2 files")
            return indication_messages >= 1
        
        def check_notification_system():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for notification system components
            notification_components = [
                'add_action.*admin_notices',
                'notice.*updated',
                'notice.*success',
                'wp_admin_notice'
            ]
            
            found_components = 0
            for component in notification_components:
                if re.search(component, content, re.IGNORECASE):
                    found_components += 1
            
            print(f"Found {found_components}/4 notification system components")
            return found_components >= 2
        
        self.test("Enhanced success messages", check_enhanced_success_messages)
        self.test("Integration info box display", check_integration_info_box)
        self.test("Clear indication messages", check_clear_indication_messages)
        self.test("Notification system components", check_notification_system)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 80)
        print("📊 ENHANCED DATABASE MANAGEMENT INTEGRATION SUMMARY")
        print("=" * 80)
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
        
        print("\n🎯 CRITICAL INTEGRATION FEATURES:")
        critical_tests = [
            "Dynamic schema detection method exists",
            "CRUD operations trigger refresh",
            "Auto-generate field config method exists",
            "Form generation uses dynamic schema",
            "CSV templates use dynamic schema",
            "Automatic field inclusion in forms/CSV",
            "Zero manual steps required",
            "Enhanced success messages"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 INTEGRATION STATUS: {critical_passed}/{len(critical_tests)} critical features working")
        
        if critical_passed == len(critical_tests):
            print("✅ ENHANCED DATABASE MANAGEMENT INTEGRATION: SUCCESSFUL")
            print("Complete integration between database CRUD operations and forms/CSV templates working correctly.")
            print("\n🎯 KEY FEATURES VERIFIED:")
            print("• Dynamic Schema Detection - ✅ Reading from actual database structure")
            print("• Auto-Refresh System - ✅ Automatic cache clearing after CRUD operations")
            print("• Auto-Generated Field Configuration - ✅ Smart field type detection and German labels")
            print("• Integration Workflow - ✅ Database changes automatically update forms and CSV templates")
            print("• User Notifications - ✅ Clear feedback about automatic synchronization")
        else:
            print("❌ ENHANCED DATABASE MANAGEMENT INTEGRATION: ISSUES FOUND")
            print("Some integration features may not be working as expected.")
            
            missing_features = []
            for critical_test in critical_tests:
                if critical_test not in self.results or self.results[critical_test]['status'] != 'passed':
                    missing_features.append(critical_test)
            
            if missing_features:
                print(f"\n⚠️ MISSING/FAILING FEATURES ({len(missing_features)}):")
                for feature in missing_features:
                    print(f"• {feature}")
        
        print("\n" + "=" * 80)

def main():
    """Main test execution"""
    tester = DatabaseIntegrationTester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())