#!/usr/bin/env python3
"""
Focused test for the v1.3.1 upgrade mechanism functionality
Testing the actual upgrade process and column addition
"""

import subprocess
import sys
import os
import re

def test_admin_init_hook():
    """Test the admin_init hook registration"""
    print("=== Testing Admin Init Hook Registration ===")
    
    database_file = "/app/includes/class-database.php"
    with open(database_file, 'r') as f:
        content = f.read()
    
    # Look for the exact hook registration
    patterns = [
        r"add_action\('admin_init',\s*array\(\$this,\s*'check_and_upgrade_schema'\)",
        r"add_action\('admin_init',.*check_and_upgrade_schema",
        r"admin_init.*check_and_upgrade_schema"
    ]
    
    found = False
    for pattern in patterns:
        if re.search(pattern, content):
            print(f"✅ FOUND: Admin init hook with pattern: {pattern}")
            found = True
            break
    
    if not found:
        print("❌ FAILED: Admin init hook not found")
        # Show the actual constructor content
        constructor_match = re.search(r'public function __construct\(\)\s*\{([^}]+)\}', content, re.DOTALL)
        if constructor_match:
            print("Constructor content:")
            print(constructor_match.group(1))
    
    return found

def test_upgrade_mechanism_flow():
    """Test the complete upgrade mechanism flow"""
    print("\n=== Testing Complete Upgrade Mechanism Flow ===")
    
    database_file = "/app/includes/class-database.php"
    with open(database_file, 'r') as f:
        content = f.read()
    
    # Test the flow: check_and_upgrade_schema -> upgrade_existing_tables -> add_missing_columns_to_debtors_table
    
    # 1. Check version comparison logic
    version_check = re.search(r'version_compare\([^)]+\)', content)
    if version_check:
        print(f"✅ Version comparison logic: {version_check.group(0)}")
    else:
        print("❌ Version comparison logic not found")
    
    # 2. Check upgrade_existing_tables call
    upgrade_call = re.search(r'\$this->upgrade_existing_tables\(\)', content)
    if upgrade_call:
        print("✅ upgrade_existing_tables() call found")
    else:
        print("❌ upgrade_existing_tables() call not found")
    
    # 3. Check add_missing_columns_to_debtors_table call
    add_columns_call = re.search(r'\$this->add_missing_columns_to_debtors_table\([^)]*\)', content)
    if add_columns_call:
        print(f"✅ add_missing_columns_to_debtors_table() call: {add_columns_call.group(0)}")
    else:
        print("❌ add_missing_columns_to_debtors_table() call not found")
    
    # 4. Check database version update
    version_update = re.search(r'update_option\([^)]*cah_database_version[^)]*\)', content)
    if version_update:
        print(f"✅ Database version update: {version_update.group(0)}")
    else:
        print("❌ Database version update not found")

def test_column_addition_logic():
    """Test the specific column addition logic"""
    print("\n=== Testing Column Addition Logic ===")
    
    database_file = "/app/includes/class-database.php"
    with open(database_file, 'r') as f:
        content = f.read()
    
    # Find the add_missing_columns_to_debtors_table method
    method_match = re.search(r'function add_missing_columns_to_debtors_table\([^{]*\{([^}]+(?:\{[^}]*\}[^}]*)*)\}', content, re.DOTALL)
    
    if method_match:
        method_content = method_match.group(1)
        print("✅ add_missing_columns_to_debtors_table method found")
        
        # Check for required columns array
        if '$required_columns' in method_content:
            print("✅ Required columns array found")
        else:
            print("❌ Required columns array not found")
        
        # Check for existing columns detection
        if 'SHOW COLUMNS FROM' in method_content:
            print("✅ Existing columns detection found")
        else:
            print("❌ Existing columns detection not found")
        
        # Check for column addition loop
        if 'foreach' in method_content and 'ALTER TABLE' in method_content:
            print("✅ Column addition loop found")
        else:
            print("❌ Column addition loop not found")
        
        # Check for specific critical columns
        critical_columns = ['datenquelle', 'letzte_aktualisierung']
        for column in critical_columns:
            if f"'{column}'" in method_content:
                print(f"✅ Critical column {column} found in method")
            else:
                print(f"❌ Critical column {column} not found in method")
    else:
        print("❌ add_missing_columns_to_debtors_table method not found")

def main():
    print("🔍 FOCUSED v1.3.1 UPGRADE MECHANISM TEST")
    print("=" * 50)
    
    test_admin_init_hook()
    test_upgrade_mechanism_flow()
    test_column_addition_logic()
    
    print("\n" + "=" * 50)
    print("🎯 FOCUSED TEST COMPLETE")

if __name__ == "__main__":
    main()