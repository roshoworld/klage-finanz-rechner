# Court Automation Hub - Financial Calculator Plugin

## Overview
This is the separated Financial Calculator plugin for Court Automation Hub. It provides advanced financial calculation capabilities with template management and full CRUD operations.

## Features
- ✅ **Template-based Cost Management**: Create and manage reusable financial templates
- ✅ **Automatic MwSt Calculations**: 19% MwSt calculation with configurable rates
- ✅ **Case Integration**: Seamlessly integrates with core plugin via WordPress hooks
- ✅ **REST API**: Full REST API support for financial data operations
- ✅ **CRUD Operations**: Complete Create, Read, Update, Delete functionality
- ✅ **Default Templates**: Pre-configured GDPR case templates

## Requirements
- WordPress 5.0+
- PHP 7.4+
- **Court Automation Hub** plugin (core plugin must be installed first)

## Installation
1. Install and activate the main **Court Automation Hub** plugin first
2. Upload this plugin to your WordPress plugins directory
3. Activate the **Court Automation Hub - Financial Calculator** plugin
4. Default templates will be created automatically

## Plugin Structure
```
court-automation-hub-financial-calculator.php  # Main plugin file
includes/
├── class-financial-db-manager.php             # Database operations
├── class-financial-admin.php                  # Admin interface
├── class-financial-calculator.php             # Core calculation logic
├── class-financial-template-manager.php       # Template CRUD operations
└── class-financial-rest-api.php              # REST API endpoints
```

## Integration with Core Plugin
The plugin integrates with the main Court Automation Hub via WordPress hooks:
- `cah_case_created` - Applies default template to new cases
- `cah_case_updated` - Updates financial data when cases change
- `cah_case_deleted` - Cleans up financial data when cases are deleted

## Database Tables
- `wp_cah_financial_templates` - Financial templates storage
- `wp_cah_case_financial` - Case-specific financial data

## Admin Interface
- **Financial Calculator**: Main dashboard and overview
- **Financial Templates**: Template management interface

## REST API Endpoints
- `GET /wp-json/cah-financial/v1/case/{case_id}` - Get case financial data
- `POST /wp-json/cah-financial/v1/case/{case_id}` - Update case financial data
- `GET /wp-json/cah-financial/v1/templates` - Get all templates
- `POST /wp-json/cah-financial/v1/calculate` - Calculate totals

## Default Cost Items
- Grundkosten: €548.11
- Gerichtskosten: €50.00
- Anwaltskosten: €200.00
- Sonstige: €0.00

## Version
1.0.5 - Full Case Management Integration

### Changelog
- **v1.0.5**: Complete case management integration (Financial tab, template selection, per-case modifications, save as template)
- **v1.0.4**: Added complete Cost Items CRUD system (Create, Edit, Delete, List cost items)  
- **v1.0.3**: Fixed function conflicts and duplicate plugin issues
- **v1.0.2**: Added complete template editing functionality (Create, Edit, Delete templates)
- **v1.0.1**: Fixed menu integration, resolved class conflicts, improved compatibility
- **v1.0.0**: Initial separated plugin release

## License
GPL v2 or later

## Support
This plugin requires the main Court Automation Hub plugin to function properly.