# Court Automation Hub - Financial Calculator v1.0.0

## 📋 SiteGround Deployment Instructions

### 1. Prerequisites
**IMPORTANT:** You must install the Core Plugin first!
- Install and activate "Court Automation Hub" core plugin
- Then install this financial calculator plugin

### 2. Upload to SiteGround
1. Zip this entire `court-automation-hub-financial-calculator` folder
2. Upload the zip file to your SiteGround WordPress site
3. Extract in `/wp-content/plugins/` directory
4. Activate the plugin from WordPress admin

### 3. What This Plugin Does
- ✅ Advanced financial calculations for legal cases
- ✅ Template management system (GDPR, Contract Dispute, etc.)
- ✅ Automatic 19% MwSt calculations
- ✅ Full CRUD operations for financial data
- ✅ Integration with core plugin via WordPress hooks
- ✅ Separate database tables (cah_ prefix)

### 4. Default Templates Included
- **GDPR Standard:** €548.11 total (€350 base + fees + 19% MwSt)
- **Contract Dispute:** €843.50 total 
- **General Case:** Customizable template

### 5. System Requirements
- **Core Plugin:** Court Automation Hub v1.4.8+ (REQUIRED)
- PHP 7.4+
- WordPress 5.0+
- MySQL 5.7+

### 6. WordPress Integration
This plugin automatically integrates with the core plugin:
- Listens to `cah_case_created` hook
- Listens to `cah_case_updated` hook  
- Listens to `cah_case_deleted` hook

### 7. Database Tables Created
- `cah_financial_templates` - Global templates
- `cah_financial_template_items` - Template items
- `cah_case_financial_data` - Per-case financial data

---
**Version:** 1.0.0 (Clean Cut Implementation)
**Status:** Production Ready
**Dependencies:** Court Automation Hub Core Plugin v1.4.8+