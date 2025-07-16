# Court Automation Hub - Financial Calculator Plugin v1.0.0

## 💰 Advanced Financial Calculator

This is the **standalone financial calculator plugin** that provides advanced financial calculations and template management for the Court Automation Hub.

## ✅ Features

- **Template Management:** Create and manage financial calculation templates
- **CRUD Operations:** Full Create, Read, Update, Delete for financial data
- **Automatic Calculations:** 19% MwSt, subtotals, and grand totals
- **WordPress Integration:** Seamless integration with core plugin using hooks
- **Database Separation:** Uses `cah_` prefix for clean separation
- **Admin Interface:** Dedicated admin pages under "Klage.Click Hub" menu

## 📊 Default Templates

### GDPR Standard Template (€548.11)
- **Grundschaden:** €350.00
- **Anwaltskosten:** €96.90 (taxable)
- **Kommunikationskosten:** €13.36 (taxable)
- **Gerichtskosten:** €32.00
- **Total with 19% MwSt:** €548.11

### Contract Dispute Template (€843.50)
- **Vertragsverletzung:** €500.00
- **Anwaltskosten:** €150.00 (taxable)
- **Gerichtskosten:** €75.00
- **Total with 19% MwSt:** €843.50

## 🔗 WordPress Integration

This plugin integrates with the core plugin using WordPress hooks:

- **Listens to:** `cah_case_created` - Applies default template to new cases
- **Listens to:** `cah_case_updated` - Updates financial data when needed
- **Listens to:** `cah_case_deleted` - Cleans up financial data

## 🗃️ Database Tables

Creates 3 dedicated tables:
- `cah_financial_templates` - Global financial templates
- `cah_financial_template_items` - Template items with categories
- `cah_case_financial_data` - Per-case financial data

## 🔧 Installation

1. **Prerequisites:** Install and activate "Court Automation Hub" core plugin first
2. Upload this folder to `/wp-content/plugins/`
3. Activate "Court Automation Hub - Financial Calculator" from WordPress admin
4. Verify financial calculator appears in "Klage.Click Hub" menu

## 📋 Dependencies

- **Core Plugin:** Court Automation Hub v1.4.8 or higher (required)
- **PHP:** 7.4 or higher
- **WordPress:** 5.0 or higher
- **Database:** MySQL 5.7 or higher

## 🔌 Plugin Structure

```
court-automation-hub-financial-calculator.php (Main plugin file)
financial-calculator/
├── includes/
│   ├── class-financial-database.php
│   ├── class-financial-admin.php
│   ├── class-financial-templates.php
│   ├── class-financial-integration.php
│   └── class-financial-calculator.php
```

## 📊 Version History

- **v1.0.0:** Initial release, complete financial calculator with CRUD operations

---

**Requires:** Court Automation Hub Core Plugin v1.4.8+
**Status:** Production Ready