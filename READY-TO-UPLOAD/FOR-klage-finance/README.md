# Court Automation Hub - Financial Calculator v1.0.0

## 💰 Advanced Financial Calculator Plugin

**WordPress plugin providing advanced financial calculations and template management for German legal cases.**

### 🎯 What This Plugin Does
- ✅ **Financial Templates:** Pre-built templates for GDPR, Contract Disputes, etc.
- ✅ **Automatic Calculations:** 19% German MwSt, subtotals, grand totals
- ✅ **CRUD Operations:** Full Create, Read, Update, Delete for financial data
- ✅ **Case Integration:** Seamless integration with core plugin
- ✅ **Template Management:** Create and manage custom calculation templates

### 🚨 **IMPORTANT: Install Core Plugin First!**

This plugin **REQUIRES** the main plugin to work:
1. **First:** Install [Court Automation Hub - Core Plugin](https://github.com/roshoworld/klage-click-court-automation)
2. **Second:** Install this Financial Calculator plugin

### 📦 Installation

#### Option 1: Download & Upload to WordPress
1. Click the **green "Code" button** above
2. Select **"Download ZIP"**
3. Upload the zip file to your WordPress site via:
   - **WordPress Admin** → Plugins → Add New → Upload Plugin
   - **OR** extract and upload to `/wp-content/plugins/`

#### Option 2: Direct Download
1. Download the latest release from the [Releases page](https://github.com/roshoworld/klage-finance/releases)
2. Upload to your WordPress site

### 💼 Default Templates Included

#### **GDPR Standard Template (€548.11)**
- **Grundschaden:** €350.00
- **Anwaltskosten:** €96.90 (taxable)
- **Kommunikationskosten:** €13.36 (taxable)
- **Gerichtskosten:** €32.00
- **Total with 19% MwSt:** €548.11

#### **Contract Dispute Template (€843.50)**
- **Vertragsverletzung:** €500.00
- **Anwaltskosten:** €150.00 (taxable)
- **Gerichtskosten:** €75.00
- **Total with 19% MwSt:** €843.50

#### **General Template**
- Fully customizable for any case type

### 🔗 Integration with Core Plugin

This plugin integrates automatically with the core plugin:
- **New Case Created** → Applies default financial template
- **Case Updated** → Updates financial calculations
- **Case Deleted** → Cleans up financial data

### 🗃️ Database Tables Created
- `cah_financial_templates` - Global financial templates
- `cah_financial_template_items` - Template items with categories
- `cah_case_financial_data` - Per-case financial data

### 📋 System Requirements
- **Core Plugin:** [Court Automation Hub](https://github.com/roshoworld/klage-click-court-automation) v1.4.8+ (REQUIRED)
- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher

### 🎯 Features
- **Template Management:** Create, edit, delete financial templates
- **Automatic MwSt:** 19% German tax calculation
- **Per-Case Customization:** Modify calculations for individual cases
- **Cost Categories:** Organize costs by type (legal fees, court fees, etc.)
- **Real-time Calculations:** Instant updates as you modify amounts
- **Export Ready:** Financial data ready for document generation

### 🚀 Production Ready
- **Test Status:** All integration tests passed
- **Clean Architecture:** Separate database with `cah_` prefix
- **WordPress Standards:** Follows WordPress coding standards
- **Security:** Nonce verification and data sanitization

### 📞 Support
- **Installation Guide:** See `README.md`
- **Core Plugin:** [klage-click-court-automation](https://github.com/roshoworld/klage-click-court-automation)
- **Issues:** Report issues on GitHub

---

**Version:** 1.0.0 (Clean Cut Implementation)  
**Status:** Production Ready  
**License:** GPL v2 or later  
**Dependencies:** Court Automation Hub Core Plugin v1.4.8+

**Complete System:** Use with [klage-click-court-automation](https://github.com/roshoworld/klage-click-court-automation) for full functionality