=== Court Automation Hub ===
Contributors: klageclick
Tags: legal, automation, gdpr, spam, court, csv, import, financial
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.4.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Multi-purpose legal automation platform for German courts with AI-powered processing and financial calculator integration.

== Description ==

Court Automation Hub is a comprehensive WordPress plugin designed for legal automation in German courts, primarily for converting legal violations (e.g., GDPR spam under €1,500) into automated court proceedings.

**Core Features:**
* Case Management with full CRUD operations
* Debtor Management
* Database Schema Auto-synchronization
* CSV Import/Export capabilities
* Audit logging system
* WordPress hooks for plugin integration

**Financial Calculator Integration:**
* Seamless integration with Financial Calculator plugin
* Template-based cost management per case
* Real-time financial calculations
* Save case modifications as new templates

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/court-automation-hub/`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Klage Click menu to configure the plugin
4. Optionally install the Financial Calculator plugin for enhanced functionality

== Frequently Asked Questions ==

= Does this work with the Financial Calculator plugin? =

Yes! This plugin is designed to work seamlessly with the Court Automation Hub - Financial Calculator plugin for enhanced financial management.

= Can I manage cases without the Financial Calculator? =

Absolutely. The core plugin works independently and provides complete case management functionality.

== Changelog ==

= 1.4.9 =
* Enhanced: Financial Calculator plugin integration
* Added: Financial tab in case management (when Financial Calculator plugin is active)
* Added: Template selection dropdown in case workflows
* Fixed: PHP 8.2 dynamic property deprecation warnings
* Added: WordPress hooks for better plugin integration

= 1.4.8 =
* Enhanced: Complete case creation workflow
* Added: Separated financial calculator to dedicated plugin
* Improved: Database schema management

== Upgrade Notice ==

= 1.4.9 =
This version adds financial calculator integration capabilities. Install the Financial Calculator plugin for enhanced functionality.