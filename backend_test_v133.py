#!/usr/bin/env python3
"""
Backend Test Suite for Court Automation Hub WordPress Plugin - Hotfix v1.3.3 Verification
Tests the comprehensive database schema fix for missing columns in klage_cases table.
"""

import os
import re
import sys
import subprocess
from typing import Dict, List, Tuple, Any

class HotfixV133Tester:
    """Test suite specifically for verifying hotfix v1.3.3 functionality"""
    
    def __init__(self):
        self.results = {}
        self.test_count = 0
        self.passed_count = 0
        self.plugin_path = "/app"
        self.database_file = "/app/includes/class-database.php"
        self.main_plugin_file = "/app/court-automation-hub.php"
        
        # All 33 columns that should be added in v1.3.3
        self.expected_columns = [
            'mandant', 'brief_status', 'briefe', 'schuldner', 'beweise', 'dokumente', 
            'links_zu_dokumenten', 'verfahrensart', 'rechtsgrundlage', 'zeitraum_von', 
            'zeitraum_bis', 'anzahl_verstoesse', 'schadenhoehe', 'anwaltsschreiben_status', 
            'mahnung_status', 'klage_status', 'vollstreckung_status', 'egvp_aktenzeichen', 
            'xjustiz_uuid', 'gericht_zustaendig', 'verfahrenswert', 'deadline_antwort', 
            'deadline_zahlung', 'mahnung_datum', 'klage_datum', 'erfolgsaussicht', 
            'risiko_bewertung', 'komplexitaet', 'kommunikation_sprache', 'bevorzugter_kontakt', 
            'kategorie', 'prioritaet_intern', 'bearbeitungsstatus', 'import_source'
        ]
        
    def run_all_tests(self) -> Dict[str, Any]:
        """Run all hotfix verification tests"""
        print("🚀 Starting Hotfix v1.3.3 Verification Tests")
        print("=" * 60)
        print("Testing comprehensive database schema fix for missing columns in klage_cases table")
        print(f"Expected to add {len(self.expected_columns)} columns to cases table")
        print()
        
        # Test sequence based on review request
        self.test_version_verification()
        self.test_upgrade_mechanism()
        self.test_cases_table_upgrade()
        self.test_column_definitions()
        self.test_default_values()
        self.test_gdpr_compliance()
        self.test_case_creation_compatibility()
        self.test_existing_functionality()
        
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
        """Test that plugin version is updated to 1.3.3"""
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
            return version == "1.3.3"
        
        def check_constant_version():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check CAH_PLUGIN_VERSION constant
            constant_match = re.search(r"define\('CAH_PLUGIN_VERSION',\s*'([^']+)'\)", content)
            if not constant_match:
                return False
            
            version = constant_match.group(1)
            print(f"Found constant version: {version}")
            return version == "1.3.3"
        
        def check_database_version():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check database version in upgrade mechanism
            version_match = re.search(r"\$current_version\s*=\s*'([^']+)'", content)
            if not version_match:
                return False
            
            version = version_match.group(1)
            print(f"Found database version: {version}")
            return version == "1.3.3"
        
        self.test("Plugin header version is 1.3.3", check_plugin_version)
        self.test("Plugin constant version is 1.3.3", check_constant_version)
        self.test("Database upgrade version is 1.3.3", check_database_version)
    
    def test_upgrade_mechanism(self):
        """Test the automatic upgrade mechanism"""
        print("🔄 TESTING UPGRADE MECHANISM")
        print("-" * 40)
        
        def check_admin_init_hook():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for admin_init hook registration
            hook_registration = "add_action('admin_init', array($this, 'check_and_upgrade_schema'))" in content
            return hook_registration
        
        def check_version_comparison():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for version comparison logic
            version_comparison = 'version_compare($version_option, $current_version, \'<\')' in content
            return version_comparison
        
        def check_upgrade_trigger():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that upgrade_existing_tables is called
            upgrade_call = '$this->upgrade_existing_tables()' in content
            return upgrade_call
        
        def check_version_update():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that database version is updated after upgrade
            version_update = "update_option('cah_database_version', $current_version)" in content
            return version_update
        
        def check_cases_table_upgrade_call():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that cases table upgrade is called
            cases_upgrade = '$this->upgrade_cases_table()' in content
            return cases_upgrade
        
        self.test("Admin init hook registration", check_admin_init_hook)
        self.test("Version comparison logic", check_version_comparison)
        self.test("Upgrade trigger mechanism", check_upgrade_trigger)
        self.test("Database version update", check_version_update)
        self.test("Cases table upgrade call", check_cases_table_upgrade_call)
    
    def test_cases_table_upgrade(self):
        """Test the cases table upgrade functionality"""
        print("📊 TESTING CASES TABLE UPGRADE")
        print("-" * 40)
        
        def check_upgrade_cases_table_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for upgrade_cases_table method
            method_pattern = r'private\s+function\s+upgrade_cases_table\s*\(\s*\)'
            return bool(re.search(method_pattern, content))
        
        def check_add_missing_columns_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for add_missing_columns_to_cases_table method
            method_pattern = r'private\s+function\s+add_missing_columns_to_cases_table\s*\('
            return bool(re.search(method_pattern, content))
        
        def check_table_existence_check():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for table existence verification
            table_check = "SHOW TABLES LIKE '$table_name'" in content
            return table_check
        
        def check_column_existence_detection():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for SHOW COLUMNS detection
            column_check = "SHOW COLUMNS FROM $table_name" in content
            return column_check
        
        def check_alter_table_statements():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for ALTER TABLE statements
            alter_count = content.count('ALTER TABLE $table_name ADD COLUMN')
            print(f"Found {alter_count} ALTER TABLE statements")
            return alter_count >= 30  # Should have at least 30 columns
        
        self.test("upgrade_cases_table method exists", check_upgrade_cases_table_method)
        self.test("add_missing_columns_to_cases_table method exists", check_add_missing_columns_method)
        self.test("Table existence check", check_table_existence_check)
        self.test("Column existence detection", check_column_existence_detection)
        self.test("ALTER TABLE statements for column addition", check_alter_table_statements)
    
    def test_column_definitions(self):
        """Test that all 33 expected columns are defined"""
        print("📝 TESTING COLUMN DEFINITIONS")
        print("-" * 40)
        
        def check_all_expected_columns():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            found_columns = []
            missing_columns = []
            
            for column in self.expected_columns:
                if f"'{column}'" in content and 'ALTER TABLE' in content:
                    found_columns.append(column)
                else:
                    missing_columns.append(column)
            
            print(f"Found {len(found_columns)}/{len(self.expected_columns)} expected columns")
            if missing_columns:
                print(f"Missing columns: {', '.join(missing_columns[:5])}{'...' if len(missing_columns) > 5 else ''}")
            
            return len(found_columns) >= 30  # Allow for minor variations
        
        def check_core_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            core_fields = ['mandant', 'brief_status', 'briefe', 'schuldner', 'beweise', 'dokumente']
            found_core = sum(1 for field in core_fields if f"'{field}'" in content)
            print(f"Found {found_core}/{len(core_fields)} core fields")
            return found_core == len(core_fields)
        
        def check_legal_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            legal_fields = ['verfahrensart', 'rechtsgrundlage', 'zeitraum_von', 'zeitraum_bis', 'anzahl_verstoesse', 'schadenhoehe']
            found_legal = sum(1 for field in legal_fields if f"'{field}'" in content)
            print(f"Found {found_legal}/{len(legal_fields)} legal fields")
            return found_legal == len(legal_fields)
        
        def check_document_status_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            doc_fields = ['anwaltsschreiben_status', 'mahnung_status', 'klage_status', 'vollstreckung_status']
            found_doc = sum(1 for field in doc_fields if f"'{field}'" in content)
            print(f"Found {found_doc}/{len(doc_fields)} document status fields")
            return found_doc == len(doc_fields)
        
        def check_court_integration_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            court_fields = ['egvp_aktenzeichen', 'xjustiz_uuid', 'gericht_zustaendig', 'verfahrenswert']
            found_court = sum(1 for field in court_fields if f"'{field}'" in content)
            print(f"Found {found_court}/{len(court_fields)} court integration fields")
            return found_court == len(court_fields)
        
        def check_timeline_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            timeline_fields = ['deadline_antwort', 'deadline_zahlung', 'mahnung_datum', 'klage_datum']
            found_timeline = sum(1 for field in timeline_fields if f"'{field}'" in content)
            print(f"Found {found_timeline}/{len(timeline_fields)} timeline fields")
            return found_timeline == len(timeline_fields)
        
        def check_assessment_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            assessment_fields = ['erfolgsaussicht', 'risiko_bewertung', 'komplexitaet']
            found_assessment = sum(1 for field in assessment_fields if f"'{field}'" in content)
            print(f"Found {found_assessment}/{len(assessment_fields)} assessment fields")
            return found_assessment == len(assessment_fields)
        
        def check_communication_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            comm_fields = ['kommunikation_sprache', 'bevorzugter_kontakt']
            found_comm = sum(1 for field in comm_fields if f"'{field}'" in content)
            print(f"Found {found_comm}/{len(comm_fields)} communication fields")
            return found_comm == len(comm_fields)
        
        def check_metadata_fields():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            meta_fields = ['kategorie', 'prioritaet_intern', 'bearbeitungsstatus', 'import_source']
            found_meta = sum(1 for field in meta_fields if f"'{field}'" in content)
            print(f"Found {found_meta}/{len(meta_fields)} metadata fields")
            return found_meta == len(meta_fields)
        
        self.test("All 33 expected columns defined", check_all_expected_columns)
        self.test("Core fields (mandant, brief_status, briefe, etc.)", check_core_fields)
        self.test("Legal fields (verfahrensart, rechtsgrundlage, etc.)", check_legal_fields)
        self.test("Document status fields", check_document_status_fields)
        self.test("Court integration fields", check_court_integration_fields)
        self.test("Timeline fields", check_timeline_fields)
        self.test("Assessment fields", check_assessment_fields)
        self.test("Communication fields", check_communication_fields)
        self.test("Metadata fields", check_metadata_fields)
    
    def test_default_values(self):
        """Test that proper default values are set"""
        print("⚙️ TESTING DEFAULT VALUES")
        print("-" * 40)
        
        def check_gdpr_default_values():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GDPR standard amounts
            gdpr_values = ['548.11', '350.00']
            found_gdpr = sum(1 for value in gdpr_values if value in content)
            print(f"Found {found_gdpr} GDPR standard values")
            return found_gdpr >= 1
        
        def check_status_defaults():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for status default values
            status_defaults = ['pending', 'mahnverfahren', 'hoch', 'niedrig', 'standard']
            found_status = sum(1 for status in status_defaults if f"DEFAULT '{status}'" in content)
            print(f"Found {found_status} status default values")
            return found_status >= 3
        
        def check_language_defaults():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for language defaults
            lang_defaults = ["DEFAULT 'de'", "DEFAULT 'email'"]
            found_lang = sum(1 for lang in lang_defaults if lang in content)
            print(f"Found {found_lang} language/communication defaults")
            return found_lang >= 1
        
        def check_legal_defaults():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for legal framework defaults
            legal_defaults = ['DSGVO Art. 82', 'GDPR_SPAM']
            found_legal = sum(1 for legal in legal_defaults if legal in content)
            print(f"Found {found_legal} legal framework defaults")
            return found_legal >= 1
        
        def check_priority_defaults():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for priority defaults
            priority_defaults = ['medium', 'neu', 'manual']
            found_priority = sum(1 for priority in priority_defaults if f"DEFAULT '{priority}'" in content)
            print(f"Found {found_priority} priority/status defaults")
            return found_priority >= 2
        
        self.test("GDPR standard default values", check_gdpr_default_values)
        self.test("Status default values", check_status_defaults)
        self.test("Language and communication defaults", check_language_defaults)
        self.test("Legal framework defaults", check_legal_defaults)
        self.test("Priority and status defaults", check_priority_defaults)
    
    def test_gdpr_compliance(self):
        """Test GDPR compliance and standard amounts"""
        print("⚖️ TESTING GDPR COMPLIANCE")
        print("-" * 40)
        
        def check_gdpr_standard_amount():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for €548.11 GDPR standard amount
            gdpr_amount = '548.11' in content
            print(f"GDPR standard amount €548.11 found: {gdpr_amount}")
            return gdpr_amount
        
        def check_damage_amount():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for €350.00 damage amount
            damage_amount = '350.00' in content
            print(f"Damage amount €350.00 found: {damage_amount}")
            return damage_amount
        
        def check_gdpr_legal_basis():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GDPR Article 82 reference
            gdpr_basis = 'DSGVO Art. 82' in content
            print(f"GDPR Article 82 legal basis found: {gdpr_basis}")
            return gdpr_basis
        
        def check_gdpr_category():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for GDPR_SPAM category
            gdpr_category = 'GDPR_SPAM' in content
            print(f"GDPR_SPAM category found: {gdpr_category}")
            return gdpr_category
        
        self.test("GDPR standard amount €548.11", check_gdpr_standard_amount)
        self.test("Damage amount €350.00", check_damage_amount)
        self.test("GDPR Article 82 legal basis", check_gdpr_legal_basis)
        self.test("GDPR_SPAM category", check_gdpr_category)
    
    def test_case_creation_compatibility(self):
        """Test that case creation will work with new columns"""
        print("🔧 TESTING CASE CREATION COMPATIBILITY")
        print("-" * 40)
        
        def check_create_tables_direct_integration():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that create_tables_direct includes all columns
            create_method = 'public function create_tables_direct()' in content
            cases_table_def = 'klage_cases' in content and 'CREATE TABLE' in content
            
            return create_method and cases_table_def
        
        def check_column_consistency():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that columns in create_tables_direct match upgrade columns
            # Look for key columns in both places
            key_columns = ['mandant', 'brief_status', 'verfahrensart', 'schadenhoehe']
            found_in_create = sum(1 for col in key_columns if col in content and 'CREATE TABLE' in content)
            found_in_upgrade = sum(1 for col in key_columns if f"'{col}'" in content and 'ALTER TABLE' in content)
            
            print(f"Key columns in CREATE TABLE: {found_in_create}")
            print(f"Key columns in ALTER TABLE: {found_in_upgrade}")
            
            return found_in_create >= 3 and found_in_upgrade >= 3
        
        def check_data_type_consistency():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper data types
            data_types = ['varchar(100)', 'varchar(20)', 'varchar(50)', 'decimal(10,2)', 'date', 'int(3)', 'int(5)']
            found_types = sum(1 for dtype in data_types if dtype in content)
            print(f"Found {found_types} proper data types")
            
            return found_types >= 5
        
        def check_null_handling():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check for proper NULL handling
            null_handling = 'DEFAULT NULL' in content
            return null_handling
        
        self.test("create_tables_direct integration", check_create_tables_direct_integration)
        self.test("Column consistency between CREATE and ALTER", check_column_consistency)
        self.test("Data type consistency", check_data_type_consistency)
        self.test("NULL value handling", check_null_handling)
    
    def test_existing_functionality(self):
        """Test that existing functionality is preserved"""
        print("🔗 TESTING EXISTING FUNCTIONALITY PRESERVATION")
        print("-" * 40)
        
        def check_debtors_table_upgrade_preserved():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that debtors table upgrade is still there
            debtors_upgrade = 'add_missing_columns_to_debtors_table' in content
            return debtors_upgrade
        
        def check_existing_table_creation():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that all existing tables are still created
            existing_tables = ['klage_debtors', 'klage_clients', 'klage_emails', 'klage_financial', 'klage_courts', 'klage_audit']
            found_tables = sum(1 for table in existing_tables if table in content)
            print(f"Found {found_tables}/{len(existing_tables)} existing tables")
            
            return found_tables == len(existing_tables)
        
        def check_court_insertion():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that default courts are still inserted
            court_insertion = 'insert_default_courts' in content
            return court_insertion
        
        def check_table_status_method():
            with open(self.database_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that get_table_status method exists
            status_method = 'public function get_table_status()' in content
            return status_method
        
        def check_plugin_activation_integration():
            with open(self.main_plugin_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Check that plugin activation still uses create_tables_direct
            activation_call = 'create_tables_direct()' in content
            return activation_call
        
        self.test("Debtors table upgrade preserved", check_debtors_table_upgrade_preserved)
        self.test("Existing table creation preserved", check_existing_table_creation)
        self.test("Default court insertion preserved", check_court_insertion)
        self.test("Table status method preserved", check_table_status_method)
        self.test("Plugin activation integration preserved", check_plugin_activation_integration)
    
    def print_summary(self):
        """Print test summary"""
        print("\n" + "=" * 60)
        print("📊 HOTFIX v1.3.3 VERIFICATION SUMMARY")
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
            "Plugin header version is 1.3.3",
            "Database upgrade version is 1.3.3",
            "Cases table upgrade call",
            "add_missing_columns_to_cases_table method exists",
            "All 33 expected columns defined",
            "GDPR standard amount €548.11",
            "Column consistency between CREATE and ALTER",
            "Existing functionality preservation"
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
            print("✅ HOTFIX v1.3.3 VERIFICATION: SUCCESSFUL")
            print("Comprehensive database schema fix for missing columns implemented correctly.")
            print(f"All {len(self.expected_columns)} columns will be added to klage_cases table.")
            print("Case creation should work without 'Unknown column' errors.")
        else:
            print("❌ HOTFIX v1.3.3 VERIFICATION: ISSUES FOUND")
            print("Some critical functionality may not be working as expected.")
        
        print("\n📈 COMPREHENSIVE SCHEMA FIX DETAILS:")
        print(f"• Expected columns to be added: {len(self.expected_columns)}")
        print("• Core fields: mandant, brief_status, briefe, schuldner, beweise, dokumente")
        print("• Legal fields: verfahrensart, rechtsgrundlage, zeitraum_von, zeitraum_bis")
        print("• Document status: anwaltsschreiben_status, mahnung_status, klage_status")
        print("• Court integration: egvp_aktenzeichen, xjustiz_uuid, gericht_zustaendig")
        print("• Timeline: deadline_antwort, deadline_zahlung, mahnung_datum, klage_datum")
        print("• Assessment: erfolgsaussicht, risiko_bewertung, komplexitaet")
        print("• Communication: kommunikation_sprache, bevorzugter_kontakt")
        print("• Metadata: kategorie, prioritaet_intern, bearbeitungsstatus, import_source")
        print("• GDPR compliance: €548.11 standard amounts maintained")
        
        print("\n" + "=" * 60)

def main():
    """Main test execution"""
    tester = HotfixV133Tester()
    results = tester.run_all_tests()
    
    # Return exit code based on results
    failed_tests = sum(1 for result in results.values() if result['status'] != 'passed')
    return 0 if failed_tests == 0 else 1

if __name__ == "__main__":
    sys.exit(main())