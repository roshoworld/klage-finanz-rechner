#!/usr/bin/env python3
"""
Backend Test Suite for Enhanced Database Management System v1.4.2
Tests the new database structure CRUD operations and schema management features
"""

import sys
import time
from datetime import datetime

class DatabaseManagementTester:
    def __init__(self):
        self.test_results = []
        self.total_tests = 0
        self.passed_tests = 0
        
    def log_test(self, test_name, passed, message=""):
        """Log test result"""
        self.total_tests += 1
        if passed:
            self.passed_tests += 1
            status = "✅ PASS"
        else:
            status = "❌ FAIL"
        
        result = f"{status}: {test_name}"
        if message:
            result += f" - {message}"
        
        print(result)
        self.test_results.append({
            'test': test_name,
            'passed': passed,
            'message': message
        })
        
    def test_schema_manager_class_exists(self):
        """Test 1: Verify Schema Manager class exists and is properly loaded"""
        try:
            # Check if the class file exists and contains the required methods
            with open('/app/includes/class-schema-manager.php', 'r') as f:
                content = f.read()
                
            # Check for key methods
            required_methods = [
                'add_column',
                'modify_column', 
                'drop_column',
                'get_complete_schema_definition',
                'compare_schemas',
                'synchronize_schema'
            ]
            
            methods_found = 0
            for method in required_methods:
                if f'function {method}(' in content:
                    methods_found += 1
                    
            self.log_test("Schema Manager Class Methods", 
                         methods_found == len(required_methods),
                         f"Found {methods_found}/{len(required_methods)} required methods")
            
        except Exception as e:
            self.log_test("Schema Manager Class Methods", False, f"Error: {str(e)}")
    
    def test_enhanced_schema_definition(self):
        """Test 2: Verify the 6 missing columns are added to schema definition"""
        try:
            with open('/app/includes/class-schema-manager.php', 'r') as f:
                content = f.read()
                
            # Check for the 6 missing columns mentioned in review request
            missing_columns = [
                'case_deadline_response',
                'case_deadline_payment', 
                'processing_complexity',
                'processing_risk_score',
                'document_type',
                'document_language'
            ]
            
            columns_found = 0
            for column in missing_columns:
                if f"'{column}'" in content:
                    columns_found += 1
                    
            self.log_test("Enhanced Schema Definition - Missing Columns Added",
                         columns_found == len(missing_columns),
                         f"Found {columns_found}/{len(missing_columns)} missing columns in schema")
            
        except Exception as e:
            self.log_test("Enhanced Schema Definition - Missing Columns Added", False, f"Error: {str(e)}")
    
    def test_database_admin_class_exists(self):
        """Test 3: Verify Database Admin class exists with enhanced features"""
        try:
            with open('/app/includes/class-database-admin.php', 'r') as f:
                content = f.read()
                
            # Check for enhanced admin features
            required_features = [
                'render_schema_management_tab',
                'render_schema_status',
                'render_table_structure',
                'render_add_column_form',
                'handle_admin_actions',
                'add_column',
                'drop_column'
            ]
            
            features_found = 0
            for feature in required_features:
                if f'function {feature}(' in content or f'{feature}(' in content:
                    features_found += 1
                    
            self.log_test("Database Admin Class Enhanced Features",
                         features_found >= 6,
                         f"Found {features_found}/{len(required_features)} enhanced features")
            
        except Exception as e:
            self.log_test("Database Admin Class Enhanced Features", False, f"Error: {str(e)}")
    
    def test_form_generator_class_exists(self):
        """Test 4: Verify Form Generator class exists for dynamic forms"""
        try:
            with open('/app/includes/class-form-generator.php', 'r') as f:
                content = f.read()
                
            # Check for form generation methods
            required_methods = [
                'generate_form',
                'group_fields_by_category',
                'render_field_group',
                'render_field_input',
                'generate_form_validation_js'
            ]
            
            methods_found = 0
            for method in required_methods:
                if f'function {method}(' in content:
                    methods_found += 1
                    
            self.log_test("Form Generator Class Methods",
                         methods_found >= 4,
                         f"Found {methods_found}/{len(required_methods)} form generation methods")
            
        except Exception as e:
            self.log_test("Form Generator Class Methods", False, f"Error: {str(e)}")
    
    def test_import_export_manager_class_exists(self):
        """Test 5: Verify Import/Export Manager class exists"""
        try:
            with open('/app/includes/class-import-export-manager.php', 'r') as f:
                content = f.read()
                
            # Check for import/export methods
            required_methods = [
                'generate_csv_template',
                'process_csv_import',
                'export_table_data',
                'get_available_templates'
            ]
            
            methods_found = 0
            for method in required_methods:
                if f'function {method}(' in content:
                    methods_found += 1
                    
            self.log_test("Import/Export Manager Class Methods",
                         methods_found >= 3,
                         f"Found {methods_found}/{len(required_methods)} import/export methods")
            
        except Exception as e:
            self.log_test("Import/Export Manager Class Methods", False, f"Error: {str(e)}")
    
    def test_plugin_version_updated(self):
        """Test 6: Verify plugin version is updated to 1.4.2"""
        try:
            with open('/app/court-automation-hub.php', 'r') as f:
                content = f.read()
                
            # Check version in header and constant
            version_in_header = "Version: 1.4.2" in content
            version_in_constant = "define('CAH_PLUGIN_VERSION', '1.4.2')" in content
            
            self.log_test("Plugin Version Updated to 1.4.2",
                         version_in_header and version_in_constant,
                         f"Header: {version_in_header}, Constant: {version_in_constant}")
            
        except Exception as e:
            self.log_test("Plugin Version Updated to 1.4.2", False, f"Error: {str(e)}")
    
    def test_database_admin_initialization(self):
        """Test 7: Verify Database Admin is properly initialized in main plugin"""
        try:
            with open('/app/court-automation-hub.php', 'r') as f:
                content = f.read()
                
            # Check if Database Admin is included and initialized
            includes_admin = "class-database-admin.php" in content
            initializes_admin = "$this->database_admin = new CAH_Database_Admin()" in content
            
            self.log_test("Database Admin Initialization",
                         includes_admin and initializes_admin,
                         f"Included: {includes_admin}, Initialized: {initializes_admin}")
            
        except Exception as e:
            self.log_test("Database Admin Initialization", False, f"Error: {str(e)}")
    
    def test_schema_manager_crud_methods(self):
        """Test 8: Verify Schema Manager CRUD methods implementation"""
        try:
            with open('/app/includes/class-schema-manager.php', 'r') as f:
                content = f.read()
                
            # Check add_column method implementation
            add_column_check = (
                'function add_column(' in content and
                'ALTER TABLE' in content and
                'ADD COLUMN' in content
            )
            
            # Check modify_column method implementation  
            modify_column_check = (
                'function modify_column(' in content and
                'MODIFY COLUMN' in content
            )
            
            # Check drop_column method implementation
            drop_column_check = (
                'function drop_column(' in content and
                'DROP COLUMN' in content and
                'system_columns' in content
            )
            
            all_methods_implemented = add_column_check and modify_column_check and drop_column_check
            
            self.log_test("Schema Manager CRUD Methods Implementation",
                         all_methods_implemented,
                         f"Add: {add_column_check}, Modify: {modify_column_check}, Drop: {drop_column_check}")
            
        except Exception as e:
            self.log_test("Schema Manager CRUD Methods Implementation", False, f"Error: {str(e)}")
    
    def test_safety_features_system_columns(self):
        """Test 9: Verify safety features prevent dropping system columns"""
        try:
            with open('/app/includes/class-schema-manager.php', 'r') as f:
                content = f.read()
                
            # Check for system column protection
            system_columns_defined = "system_columns = array('id', 'created_at', 'updated_at')" in content
            protection_check = "in_array($column_name, $system_columns)" in content
            error_message = "Cannot drop system column" in content
            
            safety_implemented = system_columns_defined and protection_check and error_message
            
            self.log_test("Safety Features - System Column Protection",
                         safety_implemented,
                         f"System columns defined: {system_columns_defined}, Protection: {protection_check}")
            
        except Exception as e:
            self.log_test("Safety Features - System Column Protection", False, f"Error: {str(e)}")
    
    def test_admin_menu_integration(self):
        """Test 10: Verify admin menu integration is correct"""
        try:
            with open('/app/includes/class-database-admin.php', 'r') as f:
                content = f.read()
                
            # Check for correct parent menu slug and page parameter
            correct_parent_slug = "'klage-click-hub'" in content
            correct_page_param = "'klage-click-database'" in content
            menu_hook = "add_submenu_page(" in content
            
            menu_integration_correct = correct_parent_slug and correct_page_param and menu_hook
            
            self.log_test("Admin Menu Integration",
                         menu_integration_correct,
                         f"Parent slug: {correct_parent_slug}, Page param: {correct_page_param}")
            
        except Exception as e:
            self.log_test("Admin Menu Integration", False, f"Error: {str(e)}")
    
    def run_all_tests(self):
        """Run all backend tests"""
        print("=" * 80)
        print("BACKEND TEST SUITE: Enhanced Database Management System v1.4.2")
        print("=" * 80)
        print(f"Test started at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        print()
        
        # Run all tests
        test_methods = [
            self.test_schema_manager_class_exists,
            self.test_enhanced_schema_definition,
            self.test_database_admin_class_exists,
            self.test_form_generator_class_exists,
            self.test_import_export_manager_class_exists,
            self.test_plugin_version_updated,
            self.test_database_admin_initialization,
            self.test_schema_manager_crud_methods,
            self.test_safety_features_system_columns,
            self.test_admin_menu_integration
        ]
        
        for test_method in test_methods:
            try:
                test_method()
            except Exception as e:
                self.log_test(test_method.__name__, False, f"Exception: {str(e)}")
        
        # Print summary
        print()
        print("=" * 80)
        print("TEST SUMMARY")
        print("=" * 80)
        print(f"Total Tests: {self.total_tests}")
        print(f"Passed: {self.passed_tests}")
        print(f"Failed: {self.total_tests - self.passed_tests}")
        print(f"Success Rate: {(self.passed_tests/self.total_tests)*100:.1f}%")
        print()
        
        if self.passed_tests == self.total_tests:
            print("🎉 ALL TESTS PASSED! Enhanced Database Management System is working correctly.")
        else:
            print("⚠️  Some tests failed. Please review the failed tests above.")
        
        return self.passed_tests == self.total_tests

if __name__ == "__main__":
    tester = DatabaseManagementTester()
    success = tester.run_all_tests()
    sys.exit(0 if success else 1)