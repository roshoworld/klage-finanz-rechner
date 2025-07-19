# Court Automation Hub - Core Plugin v1.4.9

## Overview
Enhanced core plugin with financial calculator integration capabilities for case management.

## New Features in v1.4.9
- ✅ **Financial Tab Integration**: Seamless integration with Financial Calculator plugin
- ✅ **Template Selection**: Optional dropdown in case management
- ✅ **Per-Case Financial Data**: Individual cost modifications per case
- ✅ **WordPress Hooks**: `cah_case_created`, `cah_case_updated`, `cah_case_deleted`
- ✅ **PHP 8.2 Compatibility**: Fixed dynamic property deprecation warnings

## Integration with Financial Calculator Plugin
When the "Court Automation Hub - Financial Calculator" plugin is installed:
- **Financial tab appears** in case creation/editing
- **Template selection dropdown** (optional, no default)
- **Real-time cost calculations** 
- **Save modifications as new templates**

## Database Tables
- `wp_klage_cases` - Core case management
- `wp_klage_debtors` - Debtor information

## WordPress Hooks
- `do_action('cah_case_created', $case_id)` - Fired when case is created
- `do_action('cah_case_updated', $case_id)` - Fired when case is updated  
- `do_action('cah_case_deleted', $case_id)` - Fired when case is deleted

## Requirements
- WordPress 5.0+
- PHP 7.4+
- Optional: Court Automation Hub - Financial Calculator plugin

## Installation
1. Upload plugin to WordPress plugins directory
2. Activate the plugin
3. Optionally install Financial Calculator plugin for enhanced functionality

## Version
1.4.9 - Financial Calculator Integration Support

## License
GPL v2 or later