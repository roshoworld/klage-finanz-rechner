# Installation Guide - Court Automation Hub v1.4.9

## Requirements
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+

## Installation Steps

### 1. Core Plugin Installation
1. Download the core plugin ZIP file
2. Upload to WordPress via Plugins → Add New → Upload Plugin
3. Activate "Court Automation Hub"
4. Database tables will be created automatically

### 2. Financial Calculator Plugin (Optional)
1. Download the Financial Calculator plugin ZIP file
2. Upload and activate "Court Automation Hub - Financial Calculator"
3. Financial tabs will appear in case management

### 3. Initial Setup
1. Go to WordPress Admin → Klage Click
2. Navigate to Cases to start managing cases
3. If Financial Calculator is installed, use the Financial tab in cases

## Plugin Integration
- Core plugin provides case management foundation
- Financial plugin adds cost calculation capabilities
- Both plugins communicate via WordPress hooks

## Verification
- Check that both plugins appear in Plugins list as active
- Verify Klage Click menu appears in WordPress admin
- Test case creation with financial tab (if Financial plugin installed)

## Troubleshooting
- Ensure PHP version is 7.4+
- Check that WordPress is updated to 5.0+
- Verify database permissions for table creation
- Deactivate/reactivate plugins if issues occur

## Next Steps
1. Create your first case
2. Test financial functionality (if plugin installed)
3. Import existing data via CSV (if needed)