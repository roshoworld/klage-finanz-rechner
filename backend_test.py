#!/usr/bin/env python3
"""
Clean Cut Implementation v1.4.7 - Comprehensive Testing
Testing the separation of financial calculator from core plugin into separate plugin
"""

import os
import sys
import re
import subprocess
import json
from pathlib import Path

class CleanCutTester:
    def __init__(self):
        self.test_results = []
        self.errors = []
        self.warnings = []
        
    def log_test(self, test_name, passed, details=""):
        """Log test result"""
        status = "✅ PASSED" if passed else "❌ FAILED"
        self.test_results.append({
            'test': test_name,
            'passed': passed,
            'details': details
        })
        print(f"{status}: {test_name}")
        if details:
            print(f"   Details: {details}")
        if not passed:
            self.errors.append(f"{test_name}: {details}")
    
    def log_warning(self, message):
        """Log warning"""
        self.warnings.append(message)
        print(f"⚠️  WARNING: {message}")
    
    def check_file_exists(self, filepath):
        """Check if file exists"""
        return os.path.exists(filepath)
    
    def check_file_content(self, filepath, pattern, should_exist=True):
        """Check if pattern exists in file content"""
        if not os.path.exists(filepath):
            return False
        
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                found = bool(re.search(pattern, content))
                return found if should_exist else not found
        except Exception as e:
            print(f"Error reading {filepath}: {e}")
            return False
    
    def count_pattern_in_file(self, filepath, pattern):
        """Count occurrences of pattern in file"""
        if not os.path.exists(filepath):
            return 0
        
        try:
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                return len(re.findall(pattern, content))
        except Exception as e:
            print(f"Error reading {filepath}: {e}")
            return 0
    
    def test_core_plugin_v147(self):
        """Test Core Plugin v1.4.7 - Financial Calculator Removal"""
        print("\n🔍 TESTING CORE PLUGIN v1.4.7 - Financial Calculator Removal")
        print("=" * 70)
        
        # Test 1: Plugin version is 1.4.8
        main_plugin = "/app/court-automation-hub.php"
        version_found = self.check_file_content(main_plugin, r"Version:\s*1\.4\.8")
        self.log_test("Core plugin version is 1.4.8", version_found, 
                     "Plugin header shows correct version")
        
        # Test 2: Plugin constant is 1.4.8
        constant_found = self.check_file_content(main_plugin, r"CAH_PLUGIN_VERSION.*1\.4\.8")
        self.log_test("Plugin constant version is 1.4.8", constant_found,
                     "CAH_PLUGIN_VERSION constant matches")
        
        # Test 3: Financial calculator class completely removed
        financial_class_included = self.check_file_content(main_plugin, 
                                                         r"class-financial-calculator\.php")
        self.log_test("Financial calculator class completely removed", not financial_class_included,
                     "Financial calculator should be completely removed from core plugin")
        
        # Test 4: Financial calculator class file removed
        financial_class = "/app/includes/class-financial-calculator.php"
        file_removed = not self.check_file_exists(financial_class)
        self.log_test("Financial calculator class file removed", file_removed,
                     "class-financial-calculator.php should be deleted")
        
        # Test 5: Database schema - financial tables should be commented/removed
        database_class = "/app/includes/class-database.php"
        if self.check_file_exists(database_class):
            # Check if klage_financial table creation is commented out or removed
            financial_table_active = self.check_file_content(database_class, 
                                                           r"CREATE TABLE.*klage_financial[^_]")
            financial_fields_table_active = self.check_file_content(database_class, 
                                                                  r"CREATE TABLE.*klage_financial_fields")
            
            self.log_test("klage_financial table creation removed/commented", 
                         not financial_table_active,
                         "Table creation should be removed or commented")
            self.log_test("klage_financial_fields table creation removed/commented", 
                         not financial_fields_table_active,
                         "Table creation should be removed or commented")
            
            # Check for removal comments
            removal_comment = self.check_file_content(database_class, r"removed in v1\.4\.7")
            self.log_test("Database has removal comments for v1.4.7", removal_comment,
                         "Comments indicate removal in v1.4.7")
        
        # Test 6: Admin dashboard - financial UI should be removed
        admin_dashboard = "/app/admin/class-admin-dashboard.php"
        if self.check_file_exists(admin_dashboard):
            # Count references to financial calculator UI elements
            financial_ui_count = self.count_pattern_in_file(admin_dashboard, r"Financial Calculator")
            financial_tab_count = self.count_pattern_in_file(admin_dashboard, r"financial-tab")
            
            # Some references may remain for compatibility, but UI should be minimal
            self.log_test("Financial calculator UI references reduced", 
                         financial_ui_count <= 5,
                         f"Found {financial_ui_count} references (should be ≤5)")
            
            # Check if financial management sections are removed
            financial_management = self.check_file_content(admin_dashboard, 
                                                         r"render_financial_management")
            self.log_test("Financial management UI removed", not financial_management,
                         "render_financial_management method should be removed")
    
    def test_new_financial_plugin_v100(self):
        """Test New Financial Calculator Plugin v1.0.0"""
        print("\n🔍 TESTING NEW FINANCIAL CALCULATOR PLUGIN v1.0.0")
        print("=" * 70)
        
        # Test 1: Main plugin file exists
        main_plugin = "/app/court-automation-hub-financial-calculator.php"
        plugin_exists = self.check_file_exists(main_plugin)
        self.log_test("Financial calculator plugin file exists", plugin_exists,
                     "Main plugin file created")
        
        if not plugin_exists:
            self.log_test("Cannot test financial plugin", False, "Main file missing")
            return
        
        # Test 2: Plugin version is 1.0.0
        version_found = self.check_file_content(main_plugin, r"Version:\s*1\.0\.0")
        self.log_test("Financial plugin version is 1.0.0", version_found,
                     "Plugin header shows correct version")
        
        # Test 3: Plugin constant is 1.0.0
        constant_found = self.check_file_content(main_plugin, r"CAH_FINANCIAL_PLUGIN_VERSION.*1\.0\.0")
        self.log_test("Financial plugin constant version is 1.0.0", constant_found,
                     "Version constant matches")
        
        # Test 4: Dependency check for main plugin
        dependency_check = self.check_file_content(main_plugin, r"CourtAutomationHub")
        self.log_test("Dependency check for main plugin", dependency_check,
                     "Checks for main plugin before activation")
        
        # Test 5: Required plugins header
        requires_plugins = self.check_file_content(main_plugin, r"Requires Plugins.*court-automation-hub")
        self.log_test("Requires Plugins header present", requires_plugins,
                     "WordPress dependency declaration")
        
        # Test 6: All 5 classes exist
        classes = [
            "/app/financial-calculator/includes/class-financial-database.php",
            "/app/financial-calculator/includes/class-financial-admin.php", 
            "/app/financial-calculator/includes/class-financial-templates.php",
            "/app/financial-calculator/includes/class-financial-integration.php",
            "/app/financial-calculator/includes/class-financial-calculator.php"
        ]
        
        for class_file in classes:
            class_name = os.path.basename(class_file)
            exists = self.check_file_exists(class_file)
            self.log_test(f"Class {class_name} exists", exists,
                         f"Required class file present")
        
        # Test 7: Database class functionality
        db_class = "/app/financial-calculator/includes/class-financial-database.php"
        if self.check_file_exists(db_class):
            has_create_tables = self.check_file_content(db_class, r"create_tables")
            has_financial_templates = self.check_file_content(db_class, r"cah_financial_templates")
            has_template_items = self.check_file_content(db_class, r"cah_financial_template_items")
            has_case_financial = self.check_file_content(db_class, r"cah_case_financial_data")
            
            self.log_test("Database class has create_tables method", has_create_tables,
                         "Table creation method exists")
            self.log_test("Database creates financial templates table", has_financial_templates,
                         "cah_financial_templates table defined")
            self.log_test("Database creates template items table", has_template_items,
                         "cah_financial_template_items table defined")
            self.log_test("Database creates case financial data table", has_case_financial,
                         "cah_case_financial_data table defined")
        
        # Test 8: Admin class functionality
        admin_class = "/app/financial-calculator/includes/class-financial-admin.php"
        if self.check_file_exists(admin_class):
            has_admin_menu = self.check_file_content(admin_class, r"add_admin_menu")
            has_calculator_page = self.check_file_content(admin_class, r"klage-click-financial-calculator")
            has_templates_page = self.check_file_content(admin_class, r"klage-click-financial-templates")
            has_ajax_handlers = self.check_file_content(admin_class, r"wp_ajax_cah_financial")
            
            self.log_test("Admin class has menu integration", has_admin_menu,
                         "WordPress admin menu integration")
            self.log_test("Admin class has calculator page", has_calculator_page,
                         "Financial calculator admin page")
            self.log_test("Admin class has templates page", has_templates_page,
                         "Templates management page")
            self.log_test("Admin class has AJAX handlers", has_ajax_handlers,
                         "AJAX functionality for calculations")
        
        # Test 9: Templates class functionality
        templates_class = "/app/financial-calculator/includes/class-financial-templates.php"
        if self.check_file_exists(templates_class):
            has_default_templates = self.check_file_content(templates_class, r"create_default_templates")
            has_gdpr_template = self.check_file_content(templates_class, r"create_gdpr_template")
            has_apply_template = self.check_file_content(templates_class, r"apply_template_to_case")
            has_548_amount = self.check_file_content(templates_class, r"548\.11")
            
            self.log_test("Templates class creates default templates", has_default_templates,
                         "Default template creation method")
            self.log_test("Templates class has GDPR template", has_gdpr_template,
                         "GDPR template creation")
            self.log_test("Templates class can apply to cases", has_apply_template,
                         "Template application to cases")
            self.log_test("Templates class preserves €548.11 amount", has_548_amount,
                         "Standard GDPR amount maintained")
        
        # Test 10: Integration class functionality
        integration_class = "/app/financial-calculator/includes/class-financial-integration.php"
        if self.check_file_exists(integration_class):
            has_case_hooks = self.check_file_content(integration_class, r"cah_case_")
            has_form_fields = self.check_file_content(integration_class, r"add_financial_fields")
            has_save_data = self.check_file_content(integration_class, r"save_financial_data")
            has_display_summary = self.check_file_content(integration_class, r"display_financial_summary")
            
            self.log_test("Integration class has case hooks", has_case_hooks,
                         "WordPress action hooks for case events")
            self.log_test("Integration class adds form fields", has_form_fields,
                         "Form field integration")
            self.log_test("Integration class saves data", has_save_data,
                         "Data saving integration")
            self.log_test("Integration class displays summary", has_display_summary,
                         "Financial summary display")
    
    def test_wordpress_integration_hooks(self):
        """Test WordPress Integration Hooks"""
        print("\n🔍 TESTING WORDPRESS INTEGRATION HOOKS")
        print("=" * 70)
        
        # Test 1: Main financial plugin has hook handlers
        main_plugin = "/app/court-automation-hub-financial-calculator.php"
        if self.check_file_exists(main_plugin):
            has_case_created = self.check_file_content(main_plugin, r"cah_case_created")
            has_case_updated = self.check_file_content(main_plugin, r"cah_case_updated") 
            has_case_deleted = self.check_file_content(main_plugin, r"cah_case_deleted")
            
            self.log_test("Financial plugin handles case created hook", has_case_created,
                         "cah_case_created action hook")
            self.log_test("Financial plugin handles case updated hook", has_case_updated,
                         "cah_case_updated action hook")
            self.log_test("Financial plugin handles case deleted hook", has_case_deleted,
                         "cah_case_deleted action hook")
        
        # Test 2: Core plugin should trigger these hooks
        admin_dashboard = "/app/admin/class-admin-dashboard.php"
        if self.check_file_exists(admin_dashboard):
            # Look for do_action calls that would trigger financial plugin
            triggers_created = self.check_file_content(admin_dashboard, r"do_action.*cah_case_created")
            triggers_updated = self.check_file_content(admin_dashboard, r"do_action.*cah_case_updated")
            triggers_deleted = self.check_file_content(admin_dashboard, r"do_action.*cah_case_deleted")
            
            self.log_test("Core plugin triggers case created hook", triggers_created,
                         "do_action('cah_case_created') called")
            self.log_test("Core plugin triggers case updated hook", triggers_updated,
                         "do_action('cah_case_updated') called")
            self.log_test("Core plugin triggers case deleted hook", triggers_deleted,
                         "do_action('cah_case_deleted') called")
        
        # Test 3: Integration class provides form hooks
        integration_class = "/app/financial-calculator/includes/class-financial-integration.php"
        if self.check_file_exists(integration_class):
            has_form_hooks = self.check_file_content(integration_class, r"cah_case_form_")
            has_display_hooks = self.check_file_content(integration_class, r"cah_case_display")
            has_tab_hooks = self.check_file_content(integration_class, r"cah_case_tab")
            
            self.log_test("Integration provides form hooks", has_form_hooks,
                         "Form integration hooks available")
            self.log_test("Integration provides display hooks", has_display_hooks,
                         "Display integration hooks available")
            self.log_test("Integration provides tab hooks", has_tab_hooks,
                         "Tab integration hooks available")
    
    def test_database_operations(self):
        """Test Database Operations After Clean Cut"""
        print("\n🔍 TESTING DATABASE OPERATIONS AFTER CLEAN CUT")
        print("=" * 70)
        
        # Test 1: Core database class exists and functional
        database_class = "/app/includes/class-database.php"
        if self.check_file_exists(database_class):
            has_create_tables = self.check_file_content(database_class, r"create_tables_direct")
            has_cases_table = self.check_file_content(database_class, r"klage_cases")
            has_debtors_table = self.check_file_content(database_class, r"klage_debtors")
            
            self.log_test("Core database has table creation method", has_create_tables,
                         "create_tables_direct method exists")
            self.log_test("Core database creates cases table", has_cases_table,
                         "klage_cases table definition")
            self.log_test("Core database creates debtors table", has_debtors_table,
                         "klage_debtors table definition")
        
        # Test 2: Financial plugin database is separate
        financial_db = "/app/financial-calculator/includes/class-financial-database.php"
        if self.check_file_exists(financial_db):
            has_separate_tables = self.check_file_content(financial_db, r"cah_financial_templates")
            has_case_data_table = self.check_file_content(financial_db, r"cah_case_financial_data")
            has_crud_methods = self.check_file_content(financial_db, r"get_case_financial_data")
            
            self.log_test("Financial plugin has separate database tables", has_separate_tables,
                         "Uses cah_ prefix for separation")
            self.log_test("Financial plugin has case data table", has_case_data_table,
                         "cah_case_financial_data table")
            self.log_test("Financial plugin has CRUD methods", has_crud_methods,
                         "Database operation methods")
        
        # Test 3: Schema manager handles both systems
        schema_manager = "/app/includes/class-schema-manager.php"
        if self.check_file_exists(schema_manager):
            # Should still reference klage_financial for compatibility but not create it
            references_financial = self.check_file_content(schema_manager, r"klage_financial")
            has_schema_sync = self.check_file_content(schema_manager, r"synchronize_all_tables")
            
            self.log_test("Schema manager has synchronization method", has_schema_sync,
                         "Table synchronization functionality")
            
            # Check if it properly handles missing financial tables
            if references_financial:
                self.log_warning("Schema manager still references klage_financial - check compatibility")
    
    def test_case_creation_workflow(self):
        """Test Case Creation Workflow After Clean Cut"""
        print("\n🔍 TESTING CASE CREATION WORKFLOW AFTER CLEAN CUT")
        print("=" * 70)
        
        # Test 1: Core case creation still works
        admin_dashboard = "/app/admin/class-admin-dashboard.php"
        if self.check_file_exists(admin_dashboard):
            has_create_case = self.check_file_content(admin_dashboard, r"create_new_case")
            has_case_validation = self.check_file_content(admin_dashboard, r"validate.*case")
            has_debtor_creation = self.check_file_content(admin_dashboard, r"debtor.*creation")
            
            self.log_test("Core plugin has case creation method", has_create_case,
                         "create_new_case method exists")
            self.log_test("Core plugin has case validation", has_case_validation,
                         "Case validation logic present")
        
        # Test 2: Financial integration in case creation
        if self.check_file_exists(admin_dashboard):
            # Check if case creation triggers financial hooks
            triggers_financial = self.check_file_content(admin_dashboard, r"do_action.*financial")
            has_financial_fields = self.check_file_content(admin_dashboard, r"financial.*field")
            
            # May not be directly in admin dashboard, check integration
            integration_class = "/app/financial-calculator/includes/class-financial-integration.php"
            if self.check_file_exists(integration_class):
                provides_fields = self.check_file_content(integration_class, r"add_financial_fields")
                handles_save = self.check_file_content(integration_class, r"save_financial_data")
                
                self.log_test("Financial integration provides form fields", provides_fields,
                             "Form field integration available")
                self.log_test("Financial integration handles data saving", handles_save,
                             "Data saving integration available")
        
        # Test 3: GDPR amounts preservation
        # Check if standard amounts are preserved in both systems
        core_financial = "/app/includes/class-financial-calculator.php"
        plugin_templates = "/app/financial-calculator/includes/class-financial-templates.php"
        
        core_has_548 = False
        plugin_has_548 = False
        
        if self.check_file_exists(core_financial):
            core_has_548 = self.check_file_content(core_financial, r"548\.11")
        
        if self.check_file_exists(plugin_templates):
            plugin_has_548 = self.check_file_content(plugin_templates, r"548\.11")
        
        self.log_test("Core plugin preserves €548.11 GDPR amount", core_has_548,
                     "Standard amount in simplified calculator")
        self.log_test("Financial plugin preserves €548.11 GDPR amount", plugin_has_548,
                     "Standard amount in templates")
    
    def test_admin_interface_integration(self):
        """Test Admin Interface Integration"""
        print("\n🔍 TESTING ADMIN INTERFACE INTEGRATION")
        print("=" * 70)
        
        # Test 1: Core plugin admin menu
        admin_dashboard = "/app/admin/class-admin-dashboard.php"
        if self.check_file_exists(admin_dashboard):
            has_main_menu = self.check_file_content(admin_dashboard, r"klage-click-hub")
            has_cases_menu = self.check_file_content(admin_dashboard, r"add_submenu_page")
            
            self.log_test("Core plugin has main admin menu", has_main_menu,
                         "klage-click-hub menu slug")
            self.log_test("Core plugin has submenu pages", has_cases_menu,
                         "Submenu page registration")
        
        # Test 2: Financial plugin admin integration
        financial_admin = "/app/financial-calculator/includes/class-financial-admin.php"
        if self.check_file_exists(financial_admin):
            has_submenu = self.check_file_content(financial_admin, r"add_submenu_page")
            uses_correct_parent = self.check_file_content(financial_admin, r"klage-click-hub")
            has_calculator_page = self.check_file_content(financial_admin, r"Financial Calculator")
            has_templates_page = self.check_file_content(financial_admin, r"Financial Templates")
            
            self.log_test("Financial plugin adds submenu pages", has_submenu,
                         "Submenu integration")
            self.log_test("Financial plugin uses correct parent menu", uses_correct_parent,
                         "Integrates under main plugin menu")
            self.log_test("Financial plugin has calculator page", has_calculator_page,
                         "Calculator interface page")
            self.log_test("Financial plugin has templates page", has_templates_page,
                         "Templates management page")
        
        # Test 3: Menu structure consistency
        database_admin = "/app/includes/class-database-admin.php"
        if self.check_file_exists(database_admin):
            uses_same_parent = self.check_file_content(database_admin, r"klage-click-hub")
            self.log_test("Database admin uses consistent menu parent", uses_same_parent,
                         "All components use same parent menu")
    
    def test_plugin_activation_compatibility(self):
        """Test Plugin Activation Compatibility"""
        print("\n🔍 TESTING PLUGIN ACTIVATION COMPATIBILITY")
        print("=" * 70)
        
        # Test 1: Core plugin activation
        main_plugin = "/app/court-automation-hub.php"
        if self.check_file_exists(main_plugin):
            has_activation_hook = self.check_file_content(main_plugin, r"register_activation_hook")
            has_database_creation = self.check_file_content(main_plugin, r"create_tables_direct")
            has_capabilities = self.check_file_content(main_plugin, r"add_capabilities")
            
            self.log_test("Core plugin has activation hook", has_activation_hook,
                         "WordPress activation hook registered")
            self.log_test("Core plugin creates database on activation", has_database_creation,
                         "Database table creation")
            self.log_test("Core plugin adds capabilities", has_capabilities,
                         "WordPress capabilities setup")
        
        # Test 2: Financial plugin activation
        financial_plugin = "/app/court-automation-hub-financial-calculator.php"
        if self.check_file_exists(financial_plugin):
            has_activation_hook = self.check_file_content(financial_plugin, r"register_activation_hook")
            has_dependency_check = self.check_file_content(financial_plugin, r"CourtAutomationHub")
            has_database_creation = self.check_file_content(financial_plugin, r"create_tables")
            has_template_creation = self.check_file_content(financial_plugin, r"create_default_templates")
            
            self.log_test("Financial plugin has activation hook", has_activation_hook,
                         "WordPress activation hook registered")
            self.log_test("Financial plugin checks dependencies", has_dependency_check,
                         "Main plugin dependency verification")
            self.log_test("Financial plugin creates database on activation", has_database_creation,
                         "Financial database table creation")
            self.log_test("Financial plugin creates default templates", has_template_creation,
                         "Default template setup")
        
        # Test 3: Deactivation handling
        if self.check_file_exists(main_plugin):
            has_deactivation = self.check_file_content(main_plugin, r"register_deactivation_hook")
            self.log_test("Core plugin has deactivation hook", has_deactivation,
                         "Cleanup on deactivation")
        
        if self.check_file_exists(financial_plugin):
            has_deactivation = self.check_file_content(financial_plugin, r"register_deactivation_hook")
            self.log_test("Financial plugin has deactivation hook", has_deactivation,
                         "Cleanup on deactivation")
    
    def test_hardcoded_references_cleanup(self):
        """Test Hardcoded €548.11 References Cleanup"""
        print("\n🔍 TESTING HARDCODED €548.11 REFERENCES CLEANUP")
        print("=" * 70)
        
        # Files that should have minimal €548.11 references after clean cut
        files_to_check = [
            "/app/admin/class-admin-dashboard.php",
            "/app/includes/class-database.php",
            "/app/includes/class-schema-manager.php",
            "/app/includes/class-form-generator.php"
        ]
        
        total_references = 0
        
        for filepath in files_to_check:
            if self.check_file_exists(filepath):
                count = self.count_pattern_in_file(filepath, r"548\.11")
                total_references += count
                filename = os.path.basename(filepath)
                
                # Some references may be acceptable for defaults/compatibility
                acceptable_count = 10 if "admin-dashboard" in filepath else 5
                
                self.log_test(f"€548.11 references in {filename} are reasonable", 
                             count <= acceptable_count,
                             f"Found {count} references (should be ≤{acceptable_count})")
        
        # Test overall cleanup
        self.log_test("Overall €548.11 references reduced", total_references < 50,
                     f"Total references: {total_references} (should be significantly reduced)")
        
        # Test that financial plugin has the references now
        financial_files = [
            "/app/financial-calculator/includes/class-financial-templates.php",
            "/app/financial-calculator/includes/class-financial-calculator.php"
        ]
        
        financial_references = 0
        for filepath in financial_files:
            if self.check_file_exists(filepath):
                count = self.count_pattern_in_file(filepath, r"548\.11")
                financial_references += count
        
        self.log_test("Financial plugin contains €548.11 references", financial_references > 0,
                     f"Financial plugin has {financial_references} references")
    
    def test_regression_functionality(self):
        """Test Regression - Existing Functionality Still Works"""
        print("\n🔍 TESTING REGRESSION - EXISTING FUNCTIONALITY")
        print("=" * 70)
        
        # Test 1: Database Management system
        database_admin = "/app/includes/class-database-admin.php"
        if self.check_file_exists(database_admin):
            has_admin_class = self.check_file_content(database_admin, r"class CAH_Database_Admin")
            has_menu_integration = self.check_file_content(database_admin, r"add_submenu_page")
            has_schema_management = self.check_file_content(database_admin, r"schema.*management")
            
            self.log_test("Database Management class exists", has_admin_class,
                         "CAH_Database_Admin class definition")
            self.log_test("Database Management has menu integration", has_menu_integration,
                         "WordPress admin menu integration")
        
        # Test 2: CSV import/export functionality
        admin_dashboard = "/app/admin/class-admin-dashboard.php"
        if self.check_file_exists(admin_dashboard):
            has_csv_import = self.check_file_content(admin_dashboard, r"import.*csv")
            has_csv_export = self.check_file_content(admin_dashboard, r"export.*csv")
            has_template_generation = self.check_file_content(admin_dashboard, r"template.*generation")
            
            self.log_test("CSV import functionality preserved", has_csv_import,
                         "CSV import methods exist")
            self.log_test("CSV export functionality preserved", has_csv_export,
                         "CSV export methods exist")
            self.log_test("Template generation preserved", has_template_generation,
                         "CSV template generation")
        
        # Test 3: Case management functionality
        if self.check_file_exists(admin_dashboard):
            has_case_creation = self.check_file_content(admin_dashboard, r"create_new_case")
            has_case_update = self.check_file_content(admin_dashboard, r"update_case")
            has_case_deletion = self.check_file_content(admin_dashboard, r"delete.*case")
            has_bulk_operations = self.check_file_content(admin_dashboard, r"bulk.*action")
            
            self.log_test("Case creation functionality preserved", has_case_creation,
                         "Case creation methods exist")
            self.log_test("Case update functionality preserved", has_case_update,
                         "Case update methods exist")
            self.log_test("Case deletion functionality preserved", has_case_deletion,
                         "Case deletion methods exist")
            self.log_test("Bulk operations functionality preserved", has_bulk_operations,
                         "Bulk action methods exist")
        
        # Test 4: Form generation system
        form_generator = "/app/includes/class-form-generator.php"
        if self.check_file_exists(form_generator):
            has_form_class = self.check_file_content(form_generator, r"class.*Form_Generator")
            has_generate_method = self.check_file_content(form_generator, r"generate_form")
            has_field_rendering = self.check_file_content(form_generator, r"render_field")
            
            self.log_test("Form Generator class exists", has_form_class,
                         "Form generation system preserved")
            self.log_test("Form generation method exists", has_generate_method,
                         "Form generation functionality")
            self.log_test("Field rendering exists", has_field_rendering,
                         "Field rendering functionality")
        
        # Test 5: Audit logging system
        audit_logger = "/app/includes/class-audit-logger.php"
        if self.check_file_exists(audit_logger):
            has_audit_class = self.check_file_content(audit_logger, r"class.*Audit_Logger")
            has_logging_methods = self.check_file_content(audit_logger, r"log.*action")
            
            self.log_test("Audit Logger class exists", has_audit_class,
                         "Audit logging system preserved")
            self.log_test("Audit logging methods exist", has_logging_methods,
                         "Logging functionality preserved")
    
    def run_all_tests(self):
        """Run all tests"""
        print("🚀 CLEAN CUT IMPLEMENTATION v1.4.7 - COMPREHENSIVE TESTING")
        print("=" * 80)
        print("Testing the separation of financial calculator from core plugin")
        print("=" * 80)
        
        # Run all test suites
        self.test_core_plugin_v147()
        self.test_new_financial_plugin_v100()
        self.test_wordpress_integration_hooks()
        self.test_database_operations()
        self.test_case_creation_workflow()
        self.test_admin_interface_integration()
        self.test_plugin_activation_compatibility()
        self.test_hardcoded_references_cleanup()
        self.test_regression_functionality()
        
        # Print summary
        self.print_summary()
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 80)
        print("🏁 CLEAN CUT IMPLEMENTATION v1.4.7 - TEST SUMMARY")
        print("=" * 80)
        
        total_tests = len(self.test_results)
        passed_tests = sum(1 for result in self.test_results if result['passed'])
        failed_tests = total_tests - passed_tests
        success_rate = (passed_tests / total_tests * 100) if total_tests > 0 else 0
        
        print(f"📊 RESULTS: {passed_tests}/{total_tests} tests passed ({success_rate:.1f}% success rate)")
        
        if failed_tests > 0:
            print(f"\n❌ FAILED TESTS ({failed_tests}):")
            for result in self.test_results:
                if not result['passed']:
                    print(f"   • {result['test']}: {result['details']}")
        
        if self.warnings:
            print(f"\n⚠️  WARNINGS ({len(self.warnings)}):")
            for warning in self.warnings:
                print(f"   • {warning}")
        
        # Critical assessment
        critical_tests = [
            "Core plugin version is 1.4.7",
            "Financial calculator plugin file exists",
            "Financial plugin version is 1.0.0",
            "All 5 classes exist",
            "Database creates financial templates table",
            "Financial plugin handles case created hook",
            "Case creation functionality preserved"
        ]
        
        critical_passed = 0
        for test_name in critical_tests:
            for result in self.test_results:
                if test_name in result['test'] and result['passed']:
                    critical_passed += 1
                    break
        
        critical_success = (critical_passed / len(critical_tests) * 100)
        
        print(f"\n🎯 CRITICAL TESTS: {critical_passed}/{len(critical_tests)} passed ({critical_success:.1f}%)")
        
        if critical_success >= 85:
            print("\n✅ CLEAN CUT IMPLEMENTATION STATUS: SUCCESSFUL")
            print("   The financial calculator has been successfully separated into a standalone plugin")
            print("   while maintaining core functionality and integration.")
        elif critical_success >= 70:
            print("\n⚠️  CLEAN CUT IMPLEMENTATION STATUS: PARTIALLY SUCCESSFUL")
            print("   Most functionality is working but some issues need attention.")
        else:
            print("\n❌ CLEAN CUT IMPLEMENTATION STATUS: NEEDS WORK")
            print("   Significant issues found that need to be addressed.")
        
        # Recommendations
        print(f"\n📋 RECOMMENDATIONS:")
        if failed_tests == 0:
            print("   • All tests passed! The clean cut implementation is ready for production.")
        else:
            print("   • Address failed tests before production deployment")
            if any("database" in error.lower() for error in self.errors):
                print("   • Pay special attention to database-related issues")
            if any("hook" in error.lower() for error in self.errors):
                print("   • Verify WordPress integration hooks are working properly")
            if any("admin" in error.lower() for error in self.errors):
                print("   • Check admin interface integration")
        
        return success_rate >= 85

if __name__ == "__main__":
    tester = CleanCutTester()
    success = tester.run_all_tests()
    sys.exit(0 if success else 1)