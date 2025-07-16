#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Admin Menu Integration Fix v1.4.1
Tests the admin menu integration fix for Database Management interface.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class AdminMenuIntegrationTester:
    """Test suite specifically for verifying admin menu integration fix v1.4.1"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_admin_file = "/app/includes/class-database-admin.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        self.admin_dashboard_file = "/app/admin/class-admin-dashboard.php"
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all admin menu integration tests"""
        print("🚀 Starting Admin Menu Integration Fix v1.4.1 Verification Tests")
        print("=" * 70)
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_parent_menu_slug_fix()
        self.test_page_parameter_fix()
        self.test_url_references_fix()
        self.test_navigation_links_fix()
        self.test_form_actions_fix()
        self.test_tab_navigation_fix()
        self.test_menu_structure_integration()
        self.test_database_management_accessibility()
        
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
        """Test that plugin version is updated to 1.4.1"""
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
            return version == "1.4.1"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.4.1"
        
        self.test("Plugin header version is 1.4.1", check_plugin_version)
        self.test("Plugin constant version is 1.4.1", check_constant_version)
    
    def test_parent_menu_slug_fix(self):
        """Test that parent menu slug is changed from 'court-automation-hub' to 'klage-click-hub'"""
        print("🔍 TESTING PARENT MENU SLUG FIX")
        print("-" * 40)
        
        def check_database_admin_parent_slug():
            if not os.path.exists(self.database_admin_file):
                raise Exception(f"Database admin file not found: {self.database_admin_file}")
            
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that 'klage-click-hub' is used as parent menu slug
            correct_parent = "'klage-click-hub'" in content
            
            # Check that old 'court-automation-hub' is NOT used
            old_parent_not_used = "'court-automation-hub'" not in content
            
            print(f"Uses 'klage-click-hub' as parent: {correct_parent}")
            print(f"Old 'court-automation-hub' not used: {old_parent_not_used}")
            
            return correct_parent and old_parent_not_used
        
        def check_add_submenu_page_call():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the specific add_submenu_page call with correct parent
            submenu_pattern = r"add_submenu_page\(\s*'klage-click-hub'"
            return bool(re.search(submenu_pattern, content))
        
        def check_main_menu_exists():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that main menu 'klage-click-hub' exists in admin dashboard
            main_menu_pattern = r"'klage-click-hub'"
            return bool(re.search(main_menu_pattern, content))
        
        self.test("Database Admin uses correct parent menu slug", check_database_admin_parent_slug)
        self.test("add_submenu_page call uses correct parent", check_add_submenu_page_call)
        self.test("Main menu 'klage-click-hub' exists", check_main_menu_exists)
    
    def test_page_parameter_fix(self):
        """Test that page parameter is changed from 'cah-database-management' to 'klage-click-database'"""
        print("📝 TESTING PAGE PARAMETER FIX")
        print("-" * 40)
        
        def check_submenu_page_parameter():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that 'klage-click-database' is used as page parameter
            correct_page = "'klage-click-database'" in content
            
            # Check that old 'cah-database-management' is NOT used
            old_page_not_used = "'cah-database-management'" not in content
            
            print(f"Uses 'klage-click-database' as page: {correct_page}")
            print(f"Old 'cah-database-management' not used: {old_page_not_used}")
            
            return correct_page and old_page_not_used
        
        def check_add_submenu_page_parameter():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Look for the specific add_submenu_page call with correct page parameter
            submenu_pattern = r"add_submenu_page\([^)]*'klage-click-database'"
            return bool(re.search(submenu_pattern, content))
        
        self.test("Database Admin uses correct page parameter", check_submenu_page_parameter)
        self.test("add_submenu_page call uses correct page parameter", check_add_submenu_page_parameter)
    
    def test_url_references_fix(self):
        """Test that all URL references use the correct page parameter"""
        print("🔗 TESTING URL REFERENCES FIX")
        print("-" * 40)
        
        def check_navigation_urls():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Count occurrences of correct page parameter in URLs
            correct_urls = content.count('page=klage-click-database')
            
            # Check that old URLs are not used
            old_urls = content.count('page=cah-database-management')
            
            print(f"Found {correct_urls} correct URL references")
            print(f"Found {old_urls} old URL references")
            
            return correct_urls >= 10 and old_urls == 0
        
        def check_tab_navigation_urls():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check specific tab navigation URLs
            tab_urls = [
                '?page=klage-click-database&tab=schema',
                '?page=klage-click-database&tab=data',
                '?page=klage-click-database&tab=import',
                '?page=klage-click-database&tab=forms'
            ]
            
            found_tabs = sum(1 for url in tab_urls if url in content)
            print(f"Found {found_tabs} correct tab URLs")
            
            return found_tabs == 4
        
        def check_action_urls():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check action URLs (edit, new, etc.)
            action_patterns = [
                r'page=klage-click-database&tab=data&table=',
                r'page=klage-click-database&tab=data&.*&action=edit',
                r'page=klage-click-database&tab=data&.*&action=new'
            ]
            
            found_actions = sum(1 for pattern in action_patterns if re.search(pattern, content))
            print(f"Found {found_actions} correct action URL patterns")
            
            return found_actions >= 2
        
        self.test("Navigation URLs use correct page parameter", check_navigation_urls)
        self.test("Tab navigation URLs are correct", check_tab_navigation_urls)
        self.test("Action URLs use correct page parameter", check_action_urls)
    
    def test_navigation_links_fix(self):
        """Test that navigation links work correctly"""
        print("🧭 TESTING NAVIGATION LINKS FIX")
        print("-" * 40)
        
        def check_tab_navigation_structure():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for nav-tab-wrapper structure
            nav_wrapper = 'nav-tab-wrapper' in content
            
            # Check for individual tab links
            tab_links = [
                'Schema Management',
                'Data Management', 
                'Import/Export',
                'Form Generator'
            ]
            
            found_tabs = sum(1 for tab in tab_links if tab in content)
            print(f"Found {found_tabs} tab links")
            
            return nav_wrapper and found_tabs == 4
        
        def check_active_tab_logic():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for active tab logic
            active_tab_pattern = r'nav-tab-active.*\?\s*\'\'.*:'
            active_tab_logic = bool(re.search(active_tab_pattern, content))
            
            # Check for tab parameter handling
            tab_param = '$_GET[\'tab\']' in content
            
            return active_tab_logic or tab_param
        
        def check_breadcrumb_navigation():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for back/cancel links
            cancel_links = content.count('Cancel')
            back_links = content.count('?page=klage-click-database&tab=data&table=')
            
            print(f"Found {cancel_links} cancel links and {back_links} back links")
            
            return cancel_links >= 1 and back_links >= 1
        
        self.test("Tab navigation structure is correct", check_tab_navigation_structure)
        self.test("Active tab logic works", check_active_tab_logic)
        self.test("Breadcrumb navigation works", check_breadcrumb_navigation)
    
    def test_form_actions_fix(self):
        """Test that form actions use correct URLs"""
        print("📋 TESTING FORM ACTIONS FIX")
        print("-" * 40)
        
        def check_form_method_posts():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for form method="post"
            post_forms = content.count('method="post"')
            
            print(f"Found {post_forms} POST forms")
            
            return post_forms >= 2
        
        def check_nonce_fields():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for wp_nonce_field calls
            nonce_fields = content.count('wp_nonce_field')
            
            print(f"Found {nonce_fields} nonce fields")
            
            return nonce_fields >= 3
        
        def check_action_handlers():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for action handlers
            action_handlers = [
                'sync_schema',
                'import_csv',
                'save_data'
            ]
            
            found_handlers = sum(1 for handler in action_handlers if handler in content)
            print(f"Found {found_handlers} action handlers")
            
            return found_handlers == 3
        
        self.test("Form methods are POST", check_form_method_posts)
        self.test("Nonce fields are present", check_nonce_fields)
        self.test("Action handlers are implemented", check_action_handlers)
    
    def test_tab_navigation_fix(self):
        """Test that all tabs are accessible and work correctly"""
        print("📑 TESTING TAB NAVIGATION FIX")
        print("-" * 40)
        
        def check_tab_switch_logic():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for switch statement with all tabs
            switch_cases = [
                "case 'schema':",
                "case 'data':",
                "case 'import':",
                "case 'forms':"
            ]
            
            found_cases = sum(1 for case in switch_cases if case in content)
            print(f"Found {found_cases} switch cases")
            
            return found_cases == 4
        
        def check_tab_rendering_methods():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for tab rendering methods
            render_methods = [
                'render_schema_management_tab',
                'render_data_management_tab',
                'render_import_export_tab',
                'render_form_generator_tab'
            ]
            
            found_methods = sum(1 for method in render_methods if method in content)
            print(f"Found {found_methods} tab rendering methods")
            
            return found_methods == 4
        
        def check_default_tab():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for default tab logic
            default_tab = "$_GET['tab'] ?? 'schema'" in content
            
            return default_tab
        
        self.test("Tab switch logic is complete", check_tab_switch_logic)
        self.test("All tab rendering methods exist", check_tab_rendering_methods)
        self.test("Default tab is set correctly", check_default_tab)
    
    def test_menu_structure_integration(self):
        """Test that menu structure integrates correctly with existing admin"""
        print("🏗️ TESTING MENU STRUCTURE INTEGRATION")
        print("-" * 40)
        
        def check_database_admin_class_initialization():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that Database Admin class is initialized
            db_admin_init = 'CAH_Database_Admin()' in content
            
            return db_admin_init
        
        def check_admin_menu_hook():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin_menu hook
            admin_menu_hook = "add_action('admin_menu'" in content
            
            return admin_menu_hook
        
        def check_menu_capabilities():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper capability requirement
            manage_options = "'manage_options'" in content
            
            return manage_options
        
        def check_menu_icon_and_position():
            with open(self.admin_dashboard_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for menu icon and position in main menu
            menu_icon = 'dashicons-hammer' in content
            menu_position = ', 30' in content
            
            return menu_icon and menu_position
        
        self.test("Database Admin class is initialized", check_database_admin_class_initialization)
        self.test("Admin menu hook is registered", check_admin_menu_hook)
        self.test("Menu capabilities are correct", check_menu_capabilities)
        self.test("Main menu has icon and position", check_menu_icon_and_position)
    
    def test_database_management_accessibility(self):
        """Test that Database Management interface is accessible"""
        print("🔓 TESTING DATABASE MANAGEMENT ACCESSIBILITY")
        print("-" * 40)
        
        def check_submenu_title():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for correct submenu title
            submenu_title = "'Database Management'" in content
            
            return submenu_title
        
        def check_page_callback():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for page callback method
            callback_method = 'render_database_management_page' in content
            
            return callback_method
        
        def check_page_wrapper():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for WordPress admin page wrapper
            page_wrapper = '<div class="wrap">' in content
            page_title = '<h1>Database Management</h1>' in content
            
            return page_wrapper and page_title
        
        def check_admin_actions_handler():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin actions handler
            admin_init_hook = "add_action('admin_init'" in content
            handle_actions = 'handle_admin_actions' in content
            
            return admin_init_hook and handle_actions
        
        def check_ajax_handlers():
            with open(self.database_admin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for AJAX handlers
            ajax_handlers = [
                'wp_ajax_cah_sync_schema',
                'wp_ajax_cah_export_data'
            ]
            
            found_handlers = sum(1 for handler in ajax_handlers if handler in content)
            print(f"Found {found_handlers} AJAX handlers")
            
            return found_handlers == 2
        
        self.test("Submenu title is correct", check_submenu_title)
        self.test("Page callback method exists", check_page_callback)
        self.test("Page wrapper and title are correct", check_page_wrapper)
        self.test("Admin actions handler is registered", check_admin_actions_handler)
        self.test("AJAX handlers are registered", check_ajax_handlers)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 70)
        print("📊 ADMIN MENU INTEGRATION FIX v1.4.1 VERIFICATION SUMMARY")
        print("=" * 70)
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
        
        print("\n🎯 CRITICAL ADMIN MENU FIX VERIFICATION:")
        critical_tests = [
            "Plugin header version is 1.4.1",
            "Database Admin uses correct parent menu slug",
            "Database Admin uses correct page parameter",
            "Navigation URLs use correct page parameter",
            "Tab navigation URLs are correct",
            "All tab rendering methods exist",
            "Database Admin class is initialized",
            "Submenu title is correct",
            "Page callback method exists"
        ]
        
        critical_passed = 0
        for critical_test in critical_tests:
            if critical_test in self.results:
                result = self.results[critical_test]
                status_icon = '✅' if result['status'] == 'passed' else '❌'
                print(f"{status_icon} {critical_test}")
                if result['status'] == 'passed':
                    critical_passed += 1
        
        print(f"\n🚀 ADMIN MENU FIX STATUS: {critical_passed}/{len(critical_tests)} critical tests passed")
        
        if critical_passed == len(critical_tests):
            print("✅ ADMIN MENU INTEGRATION FIX v1.4.1: SUCCESSFUL")
            print("Database Management menu should now appear under 'Klage.Click Hub' in WordPress admin.")
            print("All tabs and navigation should work correctly.")
        else:
            print("❌ ADMIN MENU INTEGRATION FIX v1.4.1: ISSUES FOUND")
            print("Some menu integration functionality may not be working as expected.")
        
        print("\n📝 EXPECTED RESULTS:")
        print("- Database Management menu appears under 'Klage.Click Hub' in WordPress admin")
        print("- All tabs (Schema, Data, Import/Export, Form Generator) are accessible")
        print("- Navigation links and form actions work correctly")
        print("- URLs use correct page parameter 'klage-click-database'")
        print("- Existing functionality is preserved")
        
        print("\n" + "=" * 70)

def main():
    """Main test execution"""
    tester = AdminMenuIntegrationTester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())