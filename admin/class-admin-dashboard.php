<?php
/**
 * Admin Dashboard class - Dynamic Financial + CSV Import v1.0.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('Klage.Click Hub', 'court-automation-hub'),
            __('Klage.Click Hub', 'court-automation-hub'),
            'manage_options',
            'klage-click-hub',
            array($this, 'admin_page_dashboard'),
            'dashicons-hammer',
            30
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Fälle', 'court-automation-hub'),
            __('Fälle', 'court-automation-hub'),
            'manage_options',
            'klage-click-cases',
            array($this, 'admin_page_cases')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Finanz-Rechner', 'court-automation-hub'),
            __('Finanz-Rechner', 'court-automation-hub'),
            'manage_options',
            'klage-click-financial',
            array($this, 'admin_page_financial')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('CSV Import', 'court-automation-hub'),
            __('CSV Import', 'court-automation-hub'),
            'manage_options',
            'klage-click-import',
            array($this, 'admin_page_import')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Einstellungen', 'court-automation-hub'),
            __('Einstellungen', 'court-automation-hub'),
            'manage_options',
            'klage-click-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    public function admin_init() {
        register_setting('klage_click_settings', 'klage_click_n8n_url');
        register_setting('klage_click_settings', 'klage_click_n8n_key');
        register_setting('klage_click_settings', 'klage_click_debug_mode');
    }
    
    public function admin_page_dashboard() {
        global $wpdb;
        
        // Get statistics
        $total_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases") ?? 0;
        $pending_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'pending'") ?? 0;
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Dashboard', 'court-automation-hub'); ?></h1>
            
            <div class="dashboard-stats" style="display: flex; gap: 20px; margin: 30px 0;">
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;"><?php echo esc_html($total_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Gesamt Fälle</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #d63638; font-size: 28px;"><?php echo esc_html($pending_cases); ?></h3>
                    <p style="margin: 0; color: #666;">Ausstehende Fälle</p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
                    <h3 style="margin: 0 0 10px 0; color: #0073aa; font-size: 28px;">DSGVO</h3>
                    <p style="margin: 0; color: #666;">Fall-Typ</p>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">Schnellaktionen</h2>
                <div class="inside" style="padding: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary" style="margin-right: 10px;">
                        Neuen GDPR-Fall erstellen
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=klage-click-settings'); ?>" class="button button-secondary">
                        N8N konfigurieren
                    </a>
                </div>
            </div>
            
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">System Status</h2>
                <div class="inside" style="padding: 20px;">
                    <?php $this->display_system_status(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function admin_page_cases() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_case_nonce'])) {
            if (wp_verify_nonce($_POST['create_case_nonce'], 'create_case')) {
                $this->handle_case_creation();
            }
        }
        
        switch ($action) {
            case 'add':
                $this->render_add_case_form();
                break;
            case 'view':
                $this->render_view_case();
                break;
            case 'edit':
                $this->render_edit_case();
                break;
            default:
                $this->render_cases_list();
                break;
        }
    }
    
    private function render_financial_calculator() {
        global $wpdb;
        
        // Get all active financial fields
        $custom_fields = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}klage_financial_fields 
            WHERE is_active = 1 
            ORDER BY display_order ASC
        ");
        
        ?>
        <div class="wrap">
            <h1>🧮 Dynamischer Finanz-Rechner</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 Excel-ähnlicher Finanzrechner!</strong></p>
                <p>Berechnen Sie automatisch DSGVO-Forderungen mit benutzerdefinierten Feldern und Formeln.</p>
            </div>
            
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-financial'); ?>" class="button button-secondary">
                    ← Zurück zur Feldverwaltung
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                    💰 Neuen Fall mit Rechner erstellen
                </a>
            </div>
            
            <!-- Spreadsheet-like Calculator -->
            <div class="postbox">
                <h2 class="hndle">📊 Finanz-Rechner (Spreadsheet-Modus)</h2>
                <div class="inside" style="padding: 20px;">
                    
                    <!-- Standard DSGVO Fields -->
                    <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                        <h3 style="color: #0073aa; margin-top: 0;">📋 Standard DSGVO-Berechnung</h3>
                        
                        <table class="financial-calculator-table" style="width: 100%; border-collapse: collapse; background: white;">
                            <thead>
                                <tr style="background: #0073aa; color: white;">
                                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Feld</th>
                                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Typ</th>
                                    <th style="padding: 12px; text-align: right; border: 1px solid #ddd;">Wert (€)</th>
                                    <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Formel/Beschreibung</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;"><strong>💰 Grundschaden</strong></td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">Standard</td>
                                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                        <input type="number" step="0.01" value="350.00" class="calc-field" data-field="grundschaden" 
                                               style="width: 100px; text-align: right; font-weight: bold;">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd; color: #666;">DSGVO Art. 82 Schadenersatz</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;"><strong>⚖️ Anwaltskosten</strong></td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">Standard</td>
                                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                        <input type="number" step="0.01" value="96.90" class="calc-field" data-field="anwaltskosten"
                                               style="width: 100px; text-align: right; font-weight: bold;">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd; color: #666;">RVG Rechtsanwaltsgebühren</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;"><strong>📞 Kommunikation</strong></td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">Standard</td>
                                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                        <input type="number" step="0.01" value="13.36" class="calc-field" data-field="kommunikation"
                                               style="width: 100px; text-align: right; font-weight: bold;">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd; color: #666;">Porto, Telefon, etc.</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;"><strong>🏛️ Gerichtskosten</strong></td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">Standard</td>
                                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                        <input type="number" step="0.01" value="32.00" class="calc-field" data-field="gerichtskosten"
                                               style="width: 100px; text-align: right; font-weight: bold;">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd; color: #666;">Verfahrenskosten</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border: 1px solid #ddd;"><strong>📊 MwSt (19%)</strong></td>
                                    <td style="padding: 12px; border: 1px solid #ddd;">Formel</td>
                                    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                        <input type="number" step="0.01" value="87.85" class="calc-field" data-field="mwst" readonly
                                               style="width: 100px; text-align: right; font-weight: bold; background: #f0f8ff;">
                                    </td>
                                    <td style="padding: 12px; border: 1px solid #ddd; color: #666;">=(Anwaltskosten + Kommunikation) * 0.19</td>
                                </tr>
                                
                                <!-- Custom Fields -->
                                <?php if (!empty($custom_fields)): ?>
                                    <?php foreach ($custom_fields as $field): ?>
                                        <tr>
                                            <td style="padding: 12px; border: 1px solid #ddd;"><strong>🔧 <?php echo esc_html($field->field_label); ?></strong></td>
                                            <td style="padding: 12px; border: 1px solid #ddd;"><?php echo esc_html(ucfirst($field->field_type)); ?></td>
                                            <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                                <?php if ($field->field_type === 'dropdown'): ?>
                                                    <select class="calc-field" data-field="<?php echo esc_attr($field->field_name); ?>" style="width: 120px;">
                                                        <?php 
                                                        $options = explode(',', $field->field_options);
                                                        foreach ($options as $option) {
                                                            $option = trim($option);
                                                            echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                <?php elseif ($field->field_type === 'number' || $field->field_type === 'percentage'): ?>
                                                    <input type="number" step="0.01" value="<?php echo esc_attr($field->default_value); ?>" 
                                                           class="calc-field" data-field="<?php echo esc_attr($field->field_name); ?>"
                                                           style="width: 100px; text-align: right; font-weight: bold;">
                                                <?php else: ?>
                                                    <input type="text" value="<?php echo esc_attr($field->default_value); ?>" 
                                                           class="calc-field" data-field="<?php echo esc_attr($field->field_name); ?>"
                                                           style="width: 120px;">
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 12px; border: 1px solid #ddd; color: #666;">
                                                <?php echo esc_html($field->field_options ?: 'Benutzerdefiniert'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Total Row -->
                                <tr style="background: #f0f8ff; font-weight: bold; font-size: 16px;">
                                    <td style="padding: 15px; border: 2px solid #0073aa;"><strong>🎯 GESAMTSUMME</strong></td>
                                    <td style="padding: 15px; border: 2px solid #0073aa;">Auto-Berechnung</td>
                                    <td style="padding: 15px; border: 2px solid #0073aa; text-align: right;">
                                        <input type="number" step="0.01" value="548.11" id="total-amount" readonly
                                               style="width: 120px; text-align: right; font-weight: bold; font-size: 18px; 
                                                      background: #e7f3ff; border: 2px solid #0073aa; color: #0073aa;">
                                    </td>
                                    <td style="padding: 15px; border: 2px solid #0073aa; color: #0073aa;">
                                        =SUM(Alle Felder) - Automatisch berechnet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style="text-align: center; margin-top: 30px;">
                        <button type="button" class="button button-large" onclick="resetCalculator()">
                            🔄 Zurücksetzen
                        </button>
                        <button type="button" class="button button-primary button-large" onclick="saveTemplate()" style="margin-left: 15px;">
                            💾 Als Vorlage speichern
                        </button>
                        <button type="button" class="button button-secondary button-large" onclick="exportCalculation()" style="margin-left: 15px;">
                            📊 Als CSV exportieren
                        </button>
                    </div>
                    
                </div>
            </div>
            
            <!-- Quick Templates -->
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle">⚡ Schnell-Vorlagen</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button type="button" class="button" onclick="loadTemplate('dsgvo_standard')" 
                                style="padding: 15px; height: auto; text-align: center;">
                            <strong>📋 DSGVO Standard</strong><br>
                            <small>€548.11 - Basis SPAM-Fall</small>
                        </button>
                        <button type="button" class="button" onclick="loadTemplate('dsgvo_premium')" 
                                style="padding: 15px; height: auto; text-align: center;">
                            <strong>💎 DSGVO Premium</strong><br>
                            <small>€750+ - Mehrfach-Verstöße</small>
                        </button>
                        <button type="button" class="button" onclick="loadTemplate('dsgvo_business')" 
                                style="padding: 15px; height: auto; text-align: center;">
                            <strong>🏢 Business-Fall</strong><br>
                            <small>€1000+ - Firmen-Verstöße</small>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate totals when fields change
            const calcFields = document.querySelectorAll('.calc-field');
            
            calcFields.forEach(field => {
                field.addEventListener('input', calculateTotal);
            });
            
            function calculateTotal() {
                const grundschaden = parseFloat(document.querySelector('[data-field="grundschaden"]').value) || 0;
                const anwaltskosten = parseFloat(document.querySelector('[data-field="anwaltskosten"]').value) || 0;
                const kommunikation = parseFloat(document.querySelector('[data-field="kommunikation"]').value) || 0;
                const gerichtskosten = parseFloat(document.querySelector('[data-field="gerichtskosten"]').value) || 0;
                
                // Calculate MwSt (19% on lawyer fees + communication)
                const mwst = (anwaltskosten + kommunikation) * 0.19;
                document.querySelector('[data-field="mwst"]').value = mwst.toFixed(2);
                
                // Calculate custom field values
                let customTotal = 0;
                const customFields = document.querySelectorAll('.calc-field[data-field]:not([data-field="grundschaden"]):not([data-field="anwaltskosten"]):not([data-field="kommunikation"]):not([data-field="gerichtskosten"]):not([data-field="mwst"])');
                customFields.forEach(field => {
                    if (field.type === 'number') {
                        customTotal += parseFloat(field.value) || 0;
                    }
                });
                
                // Total calculation
                const total = grundschaden + anwaltskosten + kommunikation + gerichtskosten + mwst + customTotal;
                document.getElementById('total-amount').value = total.toFixed(2);
            }
            
            // Initial calculation
            calculateTotal();
        });
        
        function resetCalculator() {
            document.querySelector('[data-field="grundschaden"]').value = '350.00';
            document.querySelector('[data-field="anwaltskosten"]').value = '96.90';
            document.querySelector('[data-field="kommunikation"]').value = '13.36';
            document.querySelector('[data-field="gerichtskosten"]').value = '32.00';
            
            // Reset custom fields to default
            const customFields = document.querySelectorAll('.calc-field[data-field]:not([data-field="grundschaden"]):not([data-field="anwaltskosten"]):not([data-field="kommunikation"]):not([data-field="gerichtskosten"]):not([data-field="mwst"])');
            customFields.forEach(field => {
                if (field.tagName === 'SELECT') {
                    field.selectedIndex = 0;
                } else {
                    field.value = field.getAttribute('data-default') || '0.00';
                }
            });
            
            // Recalculate
            document.dispatchEvent(new Event('input', {bubbles: true}));
        }
        
        function loadTemplate(templateType) {
            switch(templateType) {
                case 'dsgvo_standard':
                    // Already the default
                    resetCalculator();
                    break;
                case 'dsgvo_premium':
                    document.querySelector('[data-field="grundschaden"]').value = '500.00';
                    document.querySelector('[data-field="anwaltskosten"]').value = '150.00';
                    break;
                case 'dsgvo_business':
                    document.querySelector('[data-field="grundschaden"]').value = '750.00';
                    document.querySelector('[data-field="anwaltskosten"]').value = '200.00';
                    document.querySelector('[data-field="kommunikation"]').value = '25.00';
                    break;
            }
            
            // Trigger recalculation
            document.querySelector('[data-field="grundschaden"]').dispatchEvent(new Event('input', {bubbles: true}));
        }
        
        function saveTemplate() {
            alert('💾 Vorlagen-Speicherung wird in v1.1.0 implementiert!');
        }
        
        function exportCalculation() {
            // Create CSV data
            const rows = [];
            rows.push(['Feld', 'Wert', 'Beschreibung']);
            
            document.querySelectorAll('.financial-calculator-table tbody tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 3) {
                    const field = cells[0].textContent.trim();
                    const value = cells[2].querySelector('input, select') ? 
                                 cells[2].querySelector('input, select').value : 
                                 cells[2].textContent.trim();
                    const desc = cells[3].textContent.trim();
                    rows.push([field, value, desc]);
                }
            });
            
            // Create and download CSV
            const csvContent = rows.map(row => row.join(';')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'finanz_berechnung_' + new Date().toISOString().split('T')[0] + '.csv';
            link.click();
        }
        </script>
        
        <style>
        .financial-calculator-table input[type="number"],
        .financial-calculator-table input[type="text"],
        .financial-calculator-table select {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        
        .financial-calculator-table input:focus,
        .financial-calculator-table select:focus {
            border-color: #0073aa;
            box-shadow: 0 0 0 1px #0073aa;
            outline: none;
        }
        
        .financial-calculator-table tr:hover {
            background-color: #f8f9fa;
        }
        </style>
        <?php
    }
    
    public function admin_page_import() {
        global $wpdb;
        
        // Handle import actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_action'])) {
            $this->handle_import_action();
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'upload';
        
        switch ($action) {
            case 'template':
                $this->download_import_template();
                break;
            case 'preview':
                $this->render_import_preview();
                break;
            default:
                $this->render_import_page();
                break;
        }
    }
    
    private function render_import_page() {
        ?>
        <div class="wrap">
            <h1>📊 CSV Import - Forderungen.com</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.0.9 - Bulk-Import von Forderungen!</strong></p>
                <p>Importieren Sie Fälle direkt von Ihrem Inkasso-Dienstleister Forderungen.com mit vollständigen Schuldnerdaten.</p>
            </div>
            
            <!-- Step-by-Step Process -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">1️⃣ Template herunterladen</h3>
                    <p>Laden Sie die Forderungen.com-kompatible CSV-Vorlage herunter</p>
                    <a href="<?php echo admin_url('admin.php?page=klage-click-import&action=template'); ?>" class="button button-primary">
                        📥 Template downloaden
                    </a>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">2️⃣ Daten vorbereiten</h3>
                    <p>Füllen Sie die CSV mit Ihren Forderungsdaten aus</p>
                    <div style="margin-top: 10px; color: #666; font-size: 14px;">
                        <strong>Unterstützte Felder:</strong><br>
                        Fall-ID, Mandant, Schuldner-Details, Beträge, Dokumente
                    </div>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">3️⃣ Import durchführen</h3>
                    <p>Laden Sie die CSV hoch und prüfen Sie die Vorschau</p>
                    <div style="margin-top: 10px; color: #666; font-size: 14px;">
                        <strong>Automatisch erstellt:</strong><br>
                        Fälle + Schuldner + Finanzberechnungen
                    </div>
                </div>
            </div>
            
            <!-- Upload Form -->
            <div class="postbox">
                <h2 class="hndle">📁 CSV-Datei hochladen</h2>
                <div class="inside" style="padding: 20px;">
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="import_action" value="upload_csv">
                        <?php wp_nonce_field('csv_import_action', 'csv_import_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="csv_file">CSV-Datei auswählen</label></th>
                                <td>
                                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                                    <p class="description">
                                        Unterstützte Formate: .csv (UTF-8 oder Windows-1252)<br>
                                        Trennzeichen: Semikolon (;) oder Komma (,)<br>
                                        Maximale Dateigröße: 10MB
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="delimiter">Trennzeichen</label></th>
                                <td>
                                    <select id="delimiter" name="delimiter">
                                        <option value=";">Semikolon (;) - Standard deutsch</option>
                                        <option value=",">Komma (,) - International</option>
                                        <option value="\t">Tab</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="encoding">Zeichenkodierung</label></th>
                                <td>
                                    <select id="encoding" name="encoding">
                                        <option value="UTF-8">UTF-8 (Empfohlen)</option>
                                        <option value="Windows-1252">Windows-1252 (Excel Standard)</option>
                                        <option value="ISO-8859-1">ISO-8859-1</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="import_mode">Import-Modus</label></th>
                                <td>
                                    <select id="import_mode" name="import_mode">
                                        <option value="create_new">🆕 Nur neue Fälle erstellen</option>
                                        <option value="update_existing">🔄 Bestehende Fälle aktualisieren</option>
                                        <option value="create_and_update">🚀 Neue erstellen + Bestehende aktualisieren</option>
                                    </select>
                                    <p class="description">Bei "Aktualisieren" wird anhand der Fall-ID abgeglichen</p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary button-large" value="📊 CSV hochladen & Vorschau">
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Template Structure Info -->
            <div class="postbox" style="margin-top: 30px;">
                <h2 class="hndle">📋 Forderungen.com Template-Struktur</h2>
                <div class="inside" style="padding: 20px;">
                    <p><strong>Erforderliche CSV-Spalten (basierend auf Ihrem Screenshot):</strong></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 20px 0;">
                        <div>
                            <h4 style="color: #0073aa;">📋 Fall-Informationen</h4>
                            <ul style="list-style-type: disc; margin-left: 20px;">
                                <li><strong>Fall-ID:</strong> Eindeutige Kennung (z.B. SPAM-2024-0001)</li>
                                <li><strong>Fall-Status:</strong> draft, processing, completed</li>
                                <li><strong>Brief-Status:</strong> pending, sent, delivered</li>
                                <li><strong>Mandant:</strong> Ihr Firmenname/Kunde</li>
                                <li><strong>Einreichungsdatum:</strong> YYYY-MM-DD Format</li>
                                <li><strong>Beweise:</strong> Beschreibung der Beweislage</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 style="color: #0073aa;">👤 Schuldner-Informationen</h4>
                            <ul style="list-style-type: disc; margin-left: 20px;">
                                <li><strong>Firmenname:</strong> Name der Firma (optional)</li>
                                <li><strong>Vorname:</strong> Vorname der Person</li>
                                <li><strong>Nachname:</strong> Nachname der Person</li>
                                <li><strong>Adresse:</strong> Straße und Hausnummer</li>
                                <li><strong>Postleitzahl:</strong> PLZ</li>
                                <li><strong>Stadt:</strong> Ort</li>
                                <li><strong>Land:</strong> Standard: Deutschland</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <h4 style="color: #0073aa;">💰 Automatische Finanzberechnung</h4>
                        <p>Das System berechnet automatisch die DSGVO-Standardbeträge:</p>
                        <ul style="list-style-type: disc; margin-left: 20px;">
                            <li>Grundschaden: €350.00 (DSGVO Art. 82)</li>
                            <li>Anwaltskosten: €96.90 (RVG)</li>
                            <li>Kommunikationskosten: €13.36</li>
                            <li>Gerichtskosten: €32.00</li>
                            <li>MwSt: €87.85 (19%)</li>
                            <li><strong>Gesamtsumme: €548.11</strong></li>
                        </ul>
                        <p><em>Individuelle Beträge können nach dem Import manuell angepasst werden.</em></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function download_import_template() {
        // Prevent any output before headers
        if (headers_sent()) {
            wp_die('Headers already sent. Cannot download template.');
        }
        
        // Clear any existing output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Create CSV template based on Forderungen.com structure
        $filename = 'forderungen_import_template_' . date('Y-m-d') . '.csv';
        
        // Set proper headers for file download
        header('Content-Type: application/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // Add BOM for UTF-8 (Excel compatibility)
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // CSV Header - based on Forderungen.com structure
        $header = array(
            'Fall-ID',
            'Fall-Status',
            'Brief-Status',
            'Mandant',
            'Einreichungsdatum',
            'Beweise',
            'Firmenname',
            'Vorname', 
            'Nachname',
            'Adresse',
            'Postleitzahl',
            'Stadt',
            'Land',
            'Email',
            'Telefon',
            'Notizen'
        );
        
        echo implode(';', $header) . "\n";
        
        // Add sample data
        $sample_data = array(
            array(
                'SPAM-2024-0001',
                'draft',
                'pending',
                'Musterfirma GmbH',
                '2024-01-15',
                'SPAM E-Mail ohne Einwilligung erhalten',
                '',
                'Max',
                'Mustermann',
                'Musterstraße 123',
                '12345',
                'Musterstadt',
                'Deutschland',
                'spam@example.com',
                '0123456789',
                'Mehrfache SPAM-Emails trotz Widerspruch'
            ),
            array(
                'SPAM-2024-0002',
                'processing',
                'sent',
                'Musterfirma GmbH',
                '2024-01-16',
                'Newsletter ohne Double-Opt-In',
                'Beispiel AG',
                'Erika',
                'Beispiel',
                'Beispielweg 456',
                '54321',
                'Beispielhausen',
                'Deutschland',
                'newsletter@beispiel-ag.de',
                '0987654321',
                'Firmennewsletter ohne Zustimmung'
            )
        );
        
        foreach ($sample_data as $row) {
            echo implode(';', $row) . "\n";
        }
        
        exit; // Important: Stop execution after sending CSV
    }
    
    private function handle_import_action() {
        global $wpdb;
        
        if (!wp_verify_nonce($_POST['csv_import_nonce'], 'csv_import_action')) {
            echo '<div class="notice notice-error"><p>Sicherheitsfehler. Bitte versuchen Sie es erneut.</p></div>';
            return;
        }
        
        $action = sanitize_text_field($_POST['import_action']);
        
        if ($action === 'upload_csv') {
            $this->process_csv_upload();
        }
    }
    
    private function process_csv_upload() {
        global $wpdb;
        
        // Validate file upload
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Datei konnte nicht hochgeladen werden.</p></div>';
            return;
        }
        
        $file = $_FILES['csv_file'];
        $delimiter = $_POST['delimiter'];
        $encoding = $_POST['encoding'];
        $import_mode = $_POST['import_mode'];
        
        // File validation
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Datei ist zu groß (max. 10MB).</p></div>';
            return;
        }
        
        if (pathinfo($file['name'], PATHINFO_EXTENSION) !== 'csv') {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Nur CSV-Dateien sind erlaubt.</p></div>';
            return;
        }
        
        // Read and process CSV
        $file_content = file_get_contents($file['tmp_name']);
        
        // Convert encoding if needed
        if ($encoding !== 'UTF-8') {
            $file_content = mb_convert_encoding($file_content, 'UTF-8', $encoding);
        }
        
        // Parse CSV
        $lines = str_getcsv($file_content, "\n");
        if (empty($lines)) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> CSV-Datei ist leer.</p></div>';
            return;
        }
        
        // Get header
        $header = str_getcsv($lines[0], $delimiter);
        $data_rows = array_slice($lines, 1);
        
        // Validate required columns
        $required_fields = array('Fall-ID', 'Nachname');
        foreach ($required_fields as $field) {
            if (!in_array($field, $header)) {
                echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Erforderliche Spalte fehlt: ' . esc_html($field) . '</p></div>';
                return;
            }
        }
        
        // Process data
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        
        foreach ($data_rows as $line_num => $line) {
            if (empty(trim($line))) continue;
            
            $data = str_getcsv($line, $delimiter);
            if (count($data) !== count($header)) {
                $errors[] = "Zeile " . ($line_num + 2) . ": Falsche Anzahl Spalten";
                $error_count++;
                continue;
            }
            
            $row_data = array_combine($header, $data);
            
            // Process this row
            $result = $this->import_single_case($row_data, $import_mode);
            if ($result['success']) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Zeile " . ($line_num + 2) . ": " . $result['error'];
            }
        }
        
        // Show results
        if ($success_count > 0) {
            echo '<div class="notice notice-success"><p><strong>✅ Import erfolgreich!</strong> ' . $success_count . ' Fälle wurden importiert.</p></div>';
        }
        
        if ($error_count > 0) {
            echo '<div class="notice notice-warning"><p><strong>⚠️ Teilweise Fehler:</strong> ' . $error_count . ' Fälle konnten nicht importiert werden.</p>';
            if (!empty($errors)) {
                echo '<details><summary>Fehlerdetails anzeigen</summary><ul>';
                foreach (array_slice($errors, 0, 10) as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                if (count($errors) > 10) {
                    echo '<li>... und ' . (count($errors) - 10) . ' weitere Fehler</li>';
                }
                echo '</ul></details>';
            }
            echo '</div>';
        }
    }
    
    private function import_single_case($data, $import_mode) {
        global $wpdb;
        
        try {
            // Extract case data
            $case_id = sanitize_text_field($data['Fall-ID'] ?? '');
            $case_status = sanitize_text_field($data['Fall-Status'] ?? 'draft');
            $brief_status = sanitize_text_field($data['Brief-Status'] ?? 'pending');
            $mandant = sanitize_text_field($data['Mandant'] ?? '');
            $submission_date = sanitize_text_field($data['Einreichungsdatum'] ?? '');
            $evidence = sanitize_textarea_field($data['Beweise'] ?? '');
            
            // Extract debtor data
            $company_name = sanitize_text_field($data['Firmenname'] ?? '');
            $first_name = sanitize_text_field($data['Vorname'] ?? '');
            $last_name = sanitize_text_field($data['Nachname'] ?? '');
            $address = sanitize_text_field($data['Adresse'] ?? '');
            $postal_code = sanitize_text_field($data['Postleitzahl'] ?? '');
            $city = sanitize_text_field($data['Stadt'] ?? '');
            $country = sanitize_text_field($data['Land'] ?? 'Deutschland');
            $email = sanitize_email($data['Email'] ?? '');
            $phone = sanitize_text_field($data['Telefon'] ?? '');
            $notes = sanitize_textarea_field($data['Notizen'] ?? '');
            
            if (empty($case_id) || empty($last_name)) {
                return array('success' => false, 'error' => 'Fall-ID und Nachname sind erforderlich');
            }
            
            // Check if case exists
            $existing_case = $wpdb->get_row($wpdb->prepare("
                SELECT id FROM {$wpdb->prefix}klage_cases WHERE case_id = %s
            ", $case_id));
            
            if ($existing_case && $import_mode === 'create_new') {
                return array('success' => false, 'error' => 'Fall existiert bereits (nur neue Fälle erlaubt)');
            }
            
            if (!$existing_case && $import_mode === 'update_existing') {
                return array('success' => false, 'error' => 'Fall existiert nicht (nur Updates erlaubt)');
            }
            
            // Create or find debtor
            $debtor_name = trim($first_name . ' ' . $last_name);
            $debtor_id = $wpdb->get_var($wpdb->prepare("
                SELECT id FROM {$wpdb->prefix}klage_debtors 
                WHERE debtors_name = %s OR (debtors_first_name = %s AND debtors_last_name = %s)
            ", $debtor_name, $first_name, $last_name));
            
            if (!$debtor_id) {
                // Create new debtor
                $wpdb->insert(
                    $wpdb->prefix . 'klage_debtors',
                    array(
                        'debtors_name' => $debtor_name,
                        'debtors_company' => $company_name,
                        'debtors_first_name' => $first_name,
                        'debtors_last_name' => $last_name,
                        'debtors_email' => $email,
                        'debtors_address' => $address,
                        'debtors_postal_code' => $postal_code,
                        'debtors_city' => $city,
                        'debtors_country' => $country
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                );
                $debtor_id = $wpdb->insert_id;
            }
            
            // Prepare submission date
            $submission_date_mysql = '';
            if (!empty($submission_date)) {
                $date = DateTime::createFromFormat('Y-m-d', $submission_date);
                if ($date) {
                    $submission_date_mysql = $date->format('Y-m-d');
                }
            }
            
            if ($existing_case) {
                // Update existing case
                $wpdb->update(
                    $wpdb->prefix . 'klage_cases',
                    array(
                        'case_status' => $case_status,
                        'brief_status' => $brief_status,
                        'mandant' => $mandant,
                        'submission_date' => $submission_date_mysql ?: null,
                        'case_notes' => $notes,
                        'debtor_id' => $debtor_id,
                        'case_updated_date' => current_time('mysql'),
                        'import_source' => 'forderungen_com'
                    ),
                    array('id' => $existing_case->id),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'),
                    array('%d')
                );
                $case_internal_id = $existing_case->id;
            } else {
                // Create new case
                $wpdb->insert(
                    $wpdb->prefix . 'klage_cases',
                    array(
                        'case_id' => $case_id,
                        'case_creation_date' => current_time('mysql'),
                        'case_status' => $case_status,
                        'case_priority' => 'medium',
                        'brief_status' => $brief_status,
                        'submission_date' => $submission_date_mysql ?: null,
                        'mandant' => $mandant,
                        'case_notes' => $notes,
                        'debtor_id' => $debtor_id,
                        'total_amount' => 548.11, // DSGVO standard
                        'import_source' => 'forderungen_com'
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s')
                );
                $case_internal_id = $wpdb->insert_id;
                
                // Create default financial record
                $wpdb->insert(
                    $wpdb->prefix . 'klage_financial',
                    array(
                        'case_id' => $case_internal_id,
                        'damages_loss' => 350.00,
                        'partner_fees' => 96.90,
                        'communication_fees' => 13.36,
                        'vat' => 87.85,
                        'total' => 548.11,
                        'court_fees' => 32.00
                    ),
                    array('%d', '%f', '%f', '%f', '%f', '%f', '%f')
                );
                
                // Create email evidence entry if email provided
                if (!empty($email)) {
                    $wpdb->insert(
                        $wpdb->prefix . 'klage_emails',
                        array(
                            'case_id' => $case_internal_id,
                            'emails_received_date' => current_time('Y-m-d'),
                            'emails_received_time' => current_time('H:i:s'),
                            'emails_sender_email' => $email,
                            'emails_user_email' => $email, // Default to same
                            'emails_subject' => 'SPAM - Importiert von Forderungen.com',
                            'emails_content' => $evidence ?: 'Details: ' . $notes
                        ),
                        array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
                    );
                }
            }
            
            return array('success' => true, 'case_id' => $case_internal_id);
            
        } catch (Exception $e) {
            return array('success' => false, 'error' => 'Unbekannter Fehler: ' . $e->getMessage());
        }
    }
    
    private function render_edit_case() {
        $case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$case_id) {
            echo '<div class="notice notice-error"><p>Fall-ID nicht gefunden.</p></div>';
            return;
        }
        
        global $wpdb;
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_case_nonce'])) {
            if (wp_verify_nonce($_POST['edit_case_nonce'], 'edit_case_' . $case_id)) {
                $this->handle_case_update($case_id);
            }
        }
        
        // Get case data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_cases WHERE id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Get email evidence
        $email = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_emails WHERE case_id = %d LIMIT 1
        ", $case_id));
        
        // Get financial data
        $financial = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
        ", $case_id));
        
        ?>
        <div class="wrap">
            <h1>Fall bearbeiten: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="background: #d1ecf1; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>✅ v1.0.8 - Vollständige Fall-Bearbeitung!</strong></p>
                <p>Sie können jetzt alle Fall-Details bearbeiten, Status ändern und Daten aktualisieren.</p>
            </div>
            
            <form method="post" style="max-width: 1200px;">
                <?php wp_nonce_field('edit_case_' . $case_id, 'edit_case_nonce'); ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    
                    <!-- Left Column: Case Information -->
                    <div class="postbox">
                        <h2 class="hndle">📋 Fall-Informationen</h2>
                        <div class="inside" style="padding: 20px;">
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="case_id">Fall-ID</label></th>
                                    <td>
                                        <input type="text" id="case_id" name="case_id" class="regular-text" 
                                               value="<?php echo esc_attr($case->case_id); ?>" required>
                                        <p class="description">Eindeutige Fall-Kennung</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_status">Status</label></th>
                                    <td>
                                        <select id="case_status" name="case_status" class="regular-text">
                                            <option value="draft" <?php selected($case->case_status, 'draft'); ?>>📝 Entwurf</option>
                                            <option value="processing" <?php selected($case->case_status, 'processing'); ?>>⚡ In Bearbeitung</option>
                                            <option value="pending" <?php selected($case->case_status, 'pending'); ?>>⏳ Ausstehend</option>
                                            <option value="completed" <?php selected($case->case_status, 'completed'); ?>>✅ Abgeschlossen</option>
                                            <option value="cancelled" <?php selected($case->case_status, 'cancelled'); ?>>❌ Abgebrochen</option>
                                        </select>
                                        <p class="description">Aktueller Bearbeitungsstatus</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_priority">Priorität</label></th>
                                    <td>
                                        <select id="case_priority" name="case_priority" class="regular-text">
                                            <option value="low" <?php selected($case->case_priority, 'low'); ?>>🟢 Niedrig</option>
                                            <option value="medium" <?php selected($case->case_priority, 'medium'); ?>>🟡 Medium</option>
                                            <option value="high" <?php selected($case->case_priority, 'high'); ?>>🟠 Hoch</option>
                                            <option value="urgent" <?php selected($case->case_priority, 'urgent'); ?>>🔴 Dringend</option>
                                        </select>
                                        <p class="description">Bearbeitungspriorität</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="case_notes">Notizen</label></th>
                                    <td>
                                        <textarea id="case_notes" name="case_notes" class="large-text" rows="4" 
                                                  placeholder="Interne Notizen zum Fall..."><?php echo esc_textarea($case->case_notes ?? ''); ?></textarea>
                                        <p class="description">Interne Bearbeitungsnotizen</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Right Column: Email Evidence -->
                    <div class="postbox">
                        <h2 class="hndle">📧 E-Mail Evidenz</h2>
                        <div class="inside" style="padding: 20px;">
                            <?php if ($email): ?>
                                <table class="form-table">
                                    <tr>
                                        <th scope="row"><label for="emails_sender_email">Spam-Absender</label></th>
                                        <td>
                                            <input type="email" id="emails_sender_email" name="emails_sender_email" class="regular-text" 
                                                   value="<?php echo esc_attr($email->emails_sender_email); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="emails_user_email">Betroffene E-Mail</label></th>
                                        <td>
                                            <input type="email" id="emails_user_email" name="emails_user_email" class="regular-text" 
                                                   value="<?php echo esc_attr($email->emails_user_email); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="emails_received_date">Empfangsdatum</label></th>
                                        <td>
                                            <input type="date" id="emails_received_date" name="emails_received_date" class="regular-text" 
                                                   value="<?php echo esc_attr($email->emails_received_date); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="emails_received_time">Empfangszeit</label></th>
                                        <td>
                                            <input type="time" id="emails_received_time" name="emails_received_time" class="regular-text" 
                                                   value="<?php echo esc_attr($email->emails_received_time); ?>" required>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="emails_subject">Betreff</label></th>
                                        <td>
                                            <input type="text" id="emails_subject" name="emails_subject" class="regular-text" 
                                                   value="<?php echo esc_attr($email->emails_subject); ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><label for="emails_content">E-Mail Inhalt</label></th>
                                        <td>
                                            <textarea id="emails_content" name="emails_content" class="large-text" rows="6" required><?php echo esc_textarea($email->emails_content); ?></textarea>
                                        </td>
                                    </tr>
                                </table>
                            <?php else: ?>
                                <p>Keine E-Mail-Evidenz gefunden. <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case_id); ?>">Zur Ansicht wechseln</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Information -->
                <?php if ($financial): ?>
                <div class="postbox" style="margin-top: 20px;">
                    <h2 class="hndle">💰 Finanzielle Details (DSGVO Standard)</h2>
                    <div class="inside" style="padding: 20px;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div>
                                <label for="damages_loss"><strong>Grundschaden (€)</strong></label>
                                <input type="number" step="0.01" id="damages_loss" name="damages_loss" class="regular-text" 
                                       value="<?php echo esc_attr($financial->damages_loss); ?>" required>
                                <p class="description">DSGVO Art. 82 Schadenersatz</p>
                            </div>
                            <div>
                                <label for="partner_fees"><strong>Anwaltskosten (€)</strong></label>
                                <input type="number" step="0.01" id="partner_fees" name="partner_fees" class="regular-text" 
                                       value="<?php echo esc_attr($financial->partner_fees); ?>" required>
                                <p class="description">RVG Rechtsanwaltsgebühren</p>
                            </div>
                            <div>
                                <label for="communication_fees"><strong>Kommunikationskosten (€)</strong></label>
                                <input type="number" step="0.01" id="communication_fees" name="communication_fees" class="regular-text" 
                                       value="<?php echo esc_attr($financial->communication_fees); ?>" required>
                                <p class="description">Porto, Telefon, etc.</p>
                            </div>
                            <div>
                                <label for="court_fees"><strong>Gerichtskosten (€)</strong></label>
                                <input type="number" step="0.01" id="court_fees" name="court_fees" class="regular-text" 
                                       value="<?php echo esc_attr($financial->court_fees); ?>" required>
                                <p class="description">Verfahrenskosten</p>
                            </div>
                            <div>
                                <label for="vat"><strong>MwSt (€)</strong></label>
                                <input type="number" step="0.01" id="vat" name="vat" class="regular-text" 
                                       value="<?php echo esc_attr($financial->vat); ?>" required>
                                <p class="description">19% Mehrwertsteuer</p>
                            </div>
                            <div style="background: #f0f8ff; padding: 15px; border-radius: 5px;">
                                <label for="total_amount"><strong>Gesamtsumme (€)</strong></label>
                                <input type="number" step="0.01" id="total_amount" name="total_amount" class="regular-text" 
                                       value="<?php echo esc_attr($case->total_amount); ?>" required 
                                       style="font-size: 16px; font-weight: bold; color: #0073aa;">
                                <p class="description">Forderungsgesamtbetrag</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div style="background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px;">
                    <p class="submit" style="margin: 0;">
                        <input type="submit" class="button button-primary button-large" value="💾 Fall speichern" style="margin-right: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case_id); ?>" class="button button-secondary">👁️ Zur Ansicht</a>
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">← Zur Übersicht</a>
                        
                        <span style="margin-left: 20px; color: #666;">
                            <strong>Letzte Änderung:</strong> <?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($case->case_creation_date))); ?>
                        </span>
                    </p>
                </div>
            </form>
        </div>
        
        <script>
        // Auto-calculate total when financial fields change
        document.addEventListener('DOMContentLoaded', function() {
            const financialFields = ['damages_loss', 'partner_fees', 'communication_fees', 'court_fees', 'vat'];
            const totalField = document.getElementById('total_amount');
            
            financialFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('input', calculateTotal);
                }
            });
            
            function calculateTotal() {
                let total = 0;
                financialFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value) {
                        total += parseFloat(field.value) || 0;
                    }
                });
                if (totalField) {
                    totalField.value = total.toFixed(2);
                }
            }
        });
        </script>
        <?php
    }
    
    private function handle_case_update($case_id) {
        global $wpdb;
        
        // Sanitize and validate input data
        $case_id_field = sanitize_text_field($_POST['case_id']);
        $case_status = sanitize_text_field($_POST['case_status']);
        $case_priority = sanitize_text_field($_POST['case_priority']);
        $case_notes = sanitize_textarea_field($_POST['case_notes'] ?? '');
        $total_amount = floatval($_POST['total_amount']);
        
        // Email fields
        $emails_sender = sanitize_email($_POST['emails_sender_email']);
        $emails_user = sanitize_email($_POST['emails_user_email']);
        $emails_subject = sanitize_text_field($_POST['emails_subject']);
        $emails_content = sanitize_textarea_field($_POST['emails_content']);
        $emails_date = sanitize_text_field($_POST['emails_received_date']);
        $emails_time = sanitize_text_field($_POST['emails_received_time']);
        
        // Financial fields
        $damages_loss = floatval($_POST['damages_loss']);
        $partner_fees = floatval($_POST['partner_fees']);
        $communication_fees = floatval($_POST['communication_fees']);
        $court_fees = floatval($_POST['court_fees']);
        $vat = floatval($_POST['vat']);
        
        // Update case main data
        $case_updated = $wpdb->update(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_id' => $case_id_field,
                'case_status' => $case_status,
                'case_priority' => $case_priority,
                'case_notes' => $case_notes,
                'total_amount' => $total_amount,
                'case_updated_date' => current_time('mysql')
            ),
            array('id' => $case_id),
            array('%s', '%s', '%s', '%s', '%f', '%s'),
            array('%d')
        );
        
        // Update email evidence
        $email_updated = $wpdb->update(
            $wpdb->prefix . 'klage_emails',
            array(
                'emails_received_date' => $emails_date,
                'emails_received_time' => $emails_time,
                'emails_sender_email' => $emails_sender,
                'emails_user_email' => $emails_user,
                'emails_subject' => $emails_subject,
                'emails_content' => $emails_content
            ),
            array('case_id' => $case_id),
            array('%s', '%s', '%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        // Update financial data
        $financial_updated = $wpdb->update(
            $wpdb->prefix . 'klage_financial',
            array(
                'damages_loss' => $damages_loss,
                'partner_fees' => $partner_fees,
                'communication_fees' => $communication_fees,
                'court_fees' => $court_fees,
                'vat' => $vat,
                'total' => $total_amount
            ),
            array('case_id' => $case_id),
            array('%f', '%f', '%f', '%f', '%f', '%f'),
            array('%d')
        );
        
        // Create audit log entry
        $wpdb->insert(
            $wpdb->prefix . 'klage_audit',
            array(
                'case_id' => $case_id,
                'action' => 'case_updated',
                'details' => "Fall {$case_id_field} wurde bearbeitet. Status: {$case_status}, Betrag: €{$total_amount}",
                'user_id' => get_current_user_id(),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
        
        if ($case_updated !== false && $email_updated !== false && $financial_updated !== false) {
            echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> Fall "' . esc_html($case_id_field) . '" wurde erfolgreich aktualisiert.</p></div>';
            
            // Status change notifications
            if ($case_status === 'completed') {
                echo '<div class="notice notice-info"><p><strong>🎉 Fall abgeschlossen!</strong> Der Fall ist jetzt als "Abgeschlossen" markiert.</p></div>';
            } elseif ($case_status === 'processing') {
                echo '<div class="notice notice-info"><p><strong>⚡ In Bearbeitung!</strong> Der Fall wird jetzt aktiv bearbeitet.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p><strong>❌ Fehler!</strong> Beim Speichern ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut.</p></div>';
            
            // Debug information for administrators
            if (current_user_can('administrator') && get_option('klage_click_debug_mode')) {
                echo '<div class="notice notice-warning"><p><strong>Debug Info:</strong><br>';
                echo 'Case Update: ' . ($case_updated !== false ? 'OK' : 'FAILED') . '<br>';
                echo 'Email Update: ' . ($email_updated !== false ? 'OK' : 'FAILED') . '<br>';
                echo 'Financial Update: ' . ($financial_updated !== false ? 'OK' : 'FAILED') . '<br>';
                echo 'Last DB Error: ' . $wpdb->last_error . '</p></div>';
            }
        }
    }
    
    private function handle_case_creation() {
        global $wpdb;
        
        // Sanitize input data
        $case_id = sanitize_text_field($_POST['case_id']);
        $emails_sender = sanitize_email($_POST['emails_sender_email']);
        $emails_user = sanitize_email($_POST['emails_user_email']);
        $emails_subject = sanitize_text_field($_POST['emails_subject']);
        $emails_content = sanitize_textarea_field($_POST['emails_content']);
        $emails_date = sanitize_text_field($_POST['emails_received_date']);
        $emails_time = sanitize_text_field($_POST['emails_received_time']);
        
        // Create case
        $case_result = $wpdb->insert(
            $wpdb->prefix . 'klage_cases',
            array(
                'case_id' => $case_id,
                'case_creation_date' => current_time('mysql'),
                'case_status' => 'draft',
                'case_priority' => 'high',
                'total_amount' => 548.11
            ),
            array('%s', '%s', '%s', '%s', '%f')
        );
        
        if ($case_result) {
            $case_internal_id = $wpdb->insert_id;
            
            // Create email evidence
            $wpdb->insert(
                $wpdb->prefix . 'klage_emails',
                array(
                    'case_id' => $case_internal_id,
                    'emails_received_date' => $emails_date,
                    'emails_received_time' => $emails_time,
                    'emails_sender_email' => $emails_sender,
                    'emails_user_email' => $emails_user,
                    'emails_subject' => $emails_subject,
                    'emails_content' => $emails_content
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            // Create financial record
            $wpdb->insert(
                $wpdb->prefix . 'klage_financial',
                array(
                    'case_id' => $case_internal_id,
                    'damages_loss' => 350.00,
                    'partner_fees' => 96.90,
                    'communication_fees' => 13.36,
                    'vat' => 87.85,
                    'total' => 548.11,
                    'court_fees' => 32.00
                ),
                array('%d', '%f', '%f', '%f', '%f', '%f', '%f')
            );
            
            echo '<div class="notice notice-success"><p><strong>Erfolg!</strong> Fall ' . esc_html($case_id) . ' wurde erstellt. Wert: €548.11</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Fall konnte nicht erstellt werden.</p></div>';
        }
    }
    
    private function render_add_case_form() {
        ?>
        <div class="wrap">
            <h1>Neuen GDPR Spam Fall erstellen</h1>
            <p>Standardwert pro Fall: <strong>€548.11</strong> (DSGVO Schadenersatz)</p>
            
            <form method="post">
                <?php wp_nonce_field('create_case', 'create_case_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="case_id">Fall-ID</label></th>
                        <td>
                            <input type="text" id="case_id" name="case_id" class="regular-text" 
                                   value="SPAM-<?php echo date('Y'); ?>-<?php echo str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT); ?>" required>
                            <p class="description">Eindeutige Fall-ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_sender_email">Spam-Absender E-Mail</label></th>
                        <td>
                            <input type="email" id="emails_sender_email" name="emails_sender_email" class="regular-text" required>
                            <p class="description">E-Mail-Adresse des Spam-Absenders</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_user_email">Betroffene E-Mail</label></th>
                        <td>
                            <input type="email" id="emails_user_email" name="emails_user_email" class="regular-text" required>
                            <p class="description">E-Mail-Adresse des Geschädigten</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_received_date">Empfangsdatum</label></th>
                        <td>
                            <input type="date" id="emails_received_date" name="emails_received_date" class="regular-text" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_received_time">Empfangszeit</label></th>
                        <td>
                            <input type="time" id="emails_received_time" name="emails_received_time" class="regular-text" 
                                   value="<?php echo date('H:i'); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_subject">E-Mail Betreff</label></th>
                        <td>
                            <input type="text" id="emails_subject" name="emails_subject" class="regular-text">
                            <p class="description">Betreff der Spam-E-Mail</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="emails_content">E-Mail Inhalt</label></th>
                        <td>
                            <textarea id="emails_content" name="emails_content" class="large-text" rows="10" required></textarea>
                            <p class="description">Vollständiger Inhalt der Spam-E-Mail</p>
                        </td>
                    </tr>
                </table>
                
                <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3>Automatische Berechnung (DSGVO):</h3>
                    <ul>
                        <li><strong>Grundschaden:</strong> €350.00 (Art. 82 DSGVO)</li>
                        <li><strong>Anwaltskosten:</strong> €96.90 (RVG)</li>
                        <li><strong>Kommunikation:</strong> €13.36</li>
                        <li><strong>Gerichtskosten:</strong> €32.00</li>
                        <li><strong>MwSt:</strong> €87.85</li>
                        <li><strong>Gesamtsumme:</strong> €548.11</li>
                    </ul>
                </div>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Fall erstellen (€548.11)">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function render_cases_list() {
        global $wpdb;
        
        // Handle bulk actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action_nonce'])) {
            if (wp_verify_nonce($_POST['bulk_action_nonce'], 'bulk_actions')) {
                $this->handle_bulk_actions();
            }
        }
        
        // Get filter parameters
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $priority_filter = isset($_GET['priority']) ? sanitize_text_field($_GET['priority']) : '';
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        // Build query with filters
        $where_conditions = array('1=1');
        $query_params = array();
        
        if (!empty($status_filter)) {
            $where_conditions[] = 'c.case_status = %s';
            $query_params[] = $status_filter;
        }
        
        if (!empty($priority_filter)) {
            $where_conditions[] = 'c.case_priority = %s';
            $query_params[] = $priority_filter;
        }
        
        if (!empty($search)) {
            $where_conditions[] = '(c.case_id LIKE %s OR e.emails_sender_email LIKE %s OR e.emails_subject LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $query_params[] = $search_term;
            $query_params[] = $search_term;
            $query_params[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "
            SELECT 
                c.id,
                c.case_id,
                c.case_creation_date,
                c.case_status,
                c.case_priority,
                c.total_amount,
                e.emails_sender_email,
                e.emails_subject
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_emails e ON c.id = e.case_id
            WHERE {$where_clause}
            ORDER BY c.case_creation_date DESC
        ";
        
        if (!empty($query_params)) {
            $cases = $wpdb->get_results($wpdb->prepare($query, $query_params));
        } else {
            $cases = $wpdb->get_results($query);
        }
        
        // Get statistics
        $total_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases");
        $draft_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'draft'");
        $processing_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'processing'");
        $completed_cases = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}klage_cases WHERE case_status = 'completed'");
        $total_value = $wpdb->get_var("SELECT SUM(total_amount) FROM {$wpdb->prefix}klage_cases");
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">GDPR Spam Fälle</h1>
            <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="page-title-action">
                Neuen Fall hinzufügen
            </a>
            
            <!-- Statistics Dashboard -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin: 20px 0;">
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 24px;"><?php echo esc_html($total_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Gesamt Fälle</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #ff9800; font-size: 24px;"><?php echo esc_html($draft_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Entwürfe</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #2196f3; font-size: 24px;"><?php echo esc_html($processing_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">In Bearbeitung</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #4caf50; font-size: 24px;"><?php echo esc_html($completed_cases); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Abgeschlossen</p>
                </div>
                <div style="background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="margin: 0; color: #0073aa; font-size: 20px;">€<?php echo esc_html(number_format($total_value, 2)); ?></h3>
                    <p style="margin: 5px 0 0 0; color: #666;">Gesamtwert</p>
                </div>
            </div>
            
            <!-- Filters -->
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <form method="get" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="page" value="klage-click-cases">
                    
                    <div>
                        <label for="status" style="display: block; margin-bottom: 5px; font-weight: bold;">Status:</label>
                        <select name="status" id="status">
                            <option value="">Alle Status</option>
                            <option value="draft" <?php selected($status_filter, 'draft'); ?>>📝 Entwurf</option>
                            <option value="processing" <?php selected($status_filter, 'processing'); ?>>⚡ In Bearbeitung</option>
                            <option value="pending" <?php selected($status_filter, 'pending'); ?>>⏳ Ausstehend</option>
                            <option value="completed" <?php selected($status_filter, 'completed'); ?>>✅ Abgeschlossen</option>
                            <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>❌ Abgebrochen</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="priority" style="display: block; margin-bottom: 5px; font-weight: bold;">Priorität:</label>
                        <select name="priority" id="priority">
                            <option value="">Alle Prioritäten</option>
                            <option value="low" <?php selected($priority_filter, 'low'); ?>>🟢 Niedrig</option>
                            <option value="medium" <?php selected($priority_filter, 'medium'); ?>>🟡 Medium</option>
                            <option value="high" <?php selected($priority_filter, 'high'); ?>>🟠 Hoch</option>
                            <option value="urgent" <?php selected($priority_filter, 'urgent'); ?>>🔴 Dringend</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" style="display: block; margin-bottom: 5px; font-weight: bold;">Suche:</label>
                        <input type="text" name="search" id="search" value="<?php echo esc_attr($search); ?>" 
                               placeholder="Fall-ID, E-Mail oder Betreff..." style="width: 250px;">
                    </div>
                    
                    <div>
                        <input type="submit" class="button" value="🔍 Filtern">
                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">🗑️ Zurücksetzen</a>
                    </div>
                </form>
            </div>
            
            <!-- Bulk Actions -->
            <form method="post" id="cases-filter">
                <?php wp_nonce_field('bulk_actions', 'bulk_action_nonce'); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <select name="bulk_action">
                            <option value="">Bulk-Aktionen</option>
                            <option value="status_processing">Status → In Bearbeitung</option>
                            <option value="status_completed">Status → Abgeschlossen</option>
                            <option value="priority_high">Priorität → Hoch</option>
                            <option value="export_csv">Als CSV exportieren</option>
                        </select>
                        <input type="submit" class="button action" value="Anwenden">
                    </div>
                    
                    <div class="alignright">
                        <span style="color: #666;"><?php echo count($cases); ?> von <?php echo $total_cases; ?> Fällen</span>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th>Fall-ID</th>
                            <th>Status</th>
                            <th>Priorität</th>
                            <th>Spam-Absender</th>
                            <th>Betreff</th>
                            <th>Betrag</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cases)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px;">
                                    <?php if (!empty($search) || !empty($status_filter) || !empty($priority_filter)): ?>
                                        <p>Keine Fälle gefunden, die den Filterkriterien entsprechen.</p>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Filter zurücksetzen</a>
                                    <?php else: ?>
                                        <p>Keine Fälle gefunden. Erstellen Sie Ihren ersten Fall!</p>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">
                                            Ersten Fall erstellen (€548.11)
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cases as $case): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="case_ids[]" value="<?php echo esc_attr($case->id); ?>">
                                    </th>
                                    <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                            <?php 
                                            $status_icons = array(
                                                'draft' => '📝',
                                                'processing' => '⚡',
                                                'pending' => '⏳',
                                                'completed' => '✅',
                                                'cancelled' => '❌'
                                            );
                                            echo $status_icons[$case->case_status] ?? '';
                                            echo ' ' . esc_html(ucfirst($case->case_status)); 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo esc_attr($case->case_priority); ?>">
                                            <?php 
                                            $priority_icons = array(
                                                'low' => '🟢',
                                                'medium' => '🟡',
                                                'high' => '🟠',
                                                'urgent' => '🔴'
                                            );
                                            echo $priority_icons[$case->case_priority] ?? '';
                                            echo ' ' . esc_html(ucfirst($case->case_priority)); 
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($case->emails_sender_email); ?></td>
                                    <td><?php echo esc_html(wp_trim_words($case->emails_subject, 8)); ?></td>
                                    <td><strong>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></strong></td>
                                    <td><?php echo esc_html(date_i18n('d.m.Y', strtotime($case->case_creation_date))); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=view&id=' . $case->id); ?>" 
                                           class="button button-small" title="Fall ansehen">👁️</a>
                                        <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" 
                                           class="button button-small" title="Fall bearbeiten">✏️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
            </form>
            
            <?php if (!empty($cases)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-radius: 5px;">
                    <h3>Aktuelle Auswahl:</h3>
                    <p><strong>Angezeigte Fälle:</strong> <?php echo count($cases); ?></p>
                    <p><strong>Gesamtwert (angezeigt):</strong> €<?php echo number_format(array_sum(array_column($cases, 'total_amount')), 2); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .status-badge, .priority-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-draft { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-pending { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f5c6cb; color: #721c24; }
        
        .priority-low { background: #d4edda; color: #155724; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-high { background: #ffe4b5; color: #8b4513; }
        .priority-urgent { background: #f8d7da; color: #721c24; }
        </style>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle select all checkbox
            const selectAll = document.getElementById('cb-select-all');
            const checkboxes = document.querySelectorAll('input[name="case_ids[]"]');
            
            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }
        });
        </script>
        <?php
    }
    
    private function render_view_case() {
        $case_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$case_id) {
            echo '<div class="notice notice-error"><p>Fall-ID nicht gefunden.</p></div>';
            return;
        }
        
        global $wpdb;
        
        // Get case with all related data
        $case = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, cl.users_first_name, cl.users_last_name, 
                   d.debtors_name, d.debtors_email,
                   ct.court_name
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_clients cl ON c.client_id = cl.id
            LEFT JOIN {$wpdb->prefix}klage_debtors d ON c.debtor_id = d.id
            LEFT JOIN {$wpdb->prefix}klage_courts ct ON c.court_id = ct.id
            WHERE c.id = %d
        ", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Fall nicht gefunden.</p></div>';
            return;
        }
        
        // Get email evidence
        $emails = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_emails WHERE case_id = %d
        ", $case_id));
        
        // Get financial data
        $financial = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}klage_financial WHERE case_id = %d
        ", $case_id));
        
        ?>
        <div class="wrap">
            <h1>Fall Details: <?php echo esc_html($case->case_id); ?></h1>
            
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&id=' . $case->id); ?>" class="button button-primary">
                    ✏️ Bearbeiten
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">
                    ← Zurück zur Übersicht
                </a>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                <!-- Case Information -->
                <div class="postbox">
                    <h2 class="hndle">📋 Fall-Informationen</h2>
                    <div class="inside">
                        <table class="form-table">
                            <tr>
                                <th>Fall-ID:</th>
                                <td><strong><?php echo esc_html($case->case_id); ?></strong></td>
                            </tr>
                            <tr>
                                <th>Erstellungsdatum:</th>
                                <td><?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($case->case_creation_date))); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="status-badge status-<?php echo esc_attr($case->case_status); ?>">
                                        <?php echo esc_html(ucfirst($case->case_status)); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Priorität:</th>
                                <td>
                                    <span class="priority-badge priority-<?php echo esc_attr($case->case_priority); ?>">
                                        <?php echo esc_html(ucfirst($case->case_priority)); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Gesamtbetrag:</th>
                                <td><strong>€<?php echo esc_html(number_format($case->total_amount, 2)); ?></strong></td>
                            </tr>
                            <?php if ($case->court_name): ?>
                            <tr>
                                <th>Zuständiges Gericht:</th>
                                <td><?php echo esc_html($case->court_name); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
                
                <!-- Email Evidence -->
                <div class="postbox">
                    <h2 class="hndle">📧 E-Mail Evidenz</h2>
                    <div class="inside">
                        <?php if (!empty($emails)): ?>
                            <?php foreach ($emails as $email): ?>
                                <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                                    <table class="form-table">
                                        <tr>
                                            <th style="width: 120px;">Von:</th>
                                            <td><strong><?php echo esc_html($email->emails_sender_email); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>An:</th>
                                            <td><?php echo esc_html($email->emails_user_email); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Datum/Zeit:</th>
                                            <td><?php echo esc_html($email->emails_received_date . ' ' . $email->emails_received_time); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Betreff:</th>
                                            <td><?php echo esc_html($email->emails_subject); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Inhalt:</th>
                                            <td>
                                                <div style="background: #f9f9f9; padding: 10px; border-radius: 3px; max-height: 200px; overflow-y: auto;">
                                                    <pre style="white-space: pre-wrap; font-family: inherit; margin: 0;"><?php echo esc_html($email->emails_content); ?></pre>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Keine E-Mail-Evidenz gefunden.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
            <!-- Financial Breakdown -->
            <?php if ($financial): ?>
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">💰 Finanzielle Aufschlüsselung</h2>
                <div class="inside">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div>
                            <strong>Grundschaden:</strong><br>
                            €<?php echo esc_html(number_format($financial->damages_loss, 2)); ?>
                        </div>
                        <div>
                            <strong>Anwaltskosten:</strong><br>
                            €<?php echo esc_html(number_format($financial->partner_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>Kommunikation:</strong><br>
                            €<?php echo esc_html(number_format($financial->communication_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>Gerichtskosten:</strong><br>
                            €<?php echo esc_html(number_format($financial->court_fees, 2)); ?>
                        </div>
                        <div>
                            <strong>MwSt:</strong><br>
                            €<?php echo esc_html(number_format($financial->vat, 2)); ?>
                        </div>
                        <div style="background: #f0f8ff; padding: 10px; border-radius: 5px;">
                            <strong>Gesamtsumme:</strong><br>
                            <span style="font-size: 18px; color: #0073aa;">€<?php echo esc_html(number_format($financial->total, 2)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        <?php
    }
    
    private function display_system_status() {
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        $database = new CAH_Database();
        $table_status = $database->get_table_status();
        
        echo '<table class="wp-list-table widefat">';
        echo '<thead><tr><th>Tabelle</th><th>Status</th><th>Einträge</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($table_status as $table => $status) {
            $status_icon = $status['exists'] ? '✅' : '❌';
            $status_text = $status['exists'] ? 'OK' : 'Fehlt';
            
            echo '<tr>';
            echo '<td>' . esc_html($table) . '</td>';
            echo '<td>' . $status_icon . ' ' . esc_html($status_text) . '</td>';
            echo '<td>' . esc_html($status['count']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    public function admin_page_settings() {
        // Handle manual database creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables_nonce'])) {
            if (wp_verify_nonce($_POST['create_tables_nonce'], 'create_tables')) {
                $this->force_create_tables();
            }
        }
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Klage.Click Hub Einstellungen', 'court-automation-hub'); ?></h1>
            
            <!-- Database Fix Section -->
            <div class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle" style="padding: 15px 20px; margin: 0; background: #f9f9f9;">🛠️ Datenbank Reparatur</h2>
                <div class="inside" style="padding: 20px;">
                    <p><strong>Problem:</strong> Einige Datenbank-Tabellen fehlen und müssen erstellt werden.</p>
                    
                    <div style="margin: 15px 0;">
                        <h4>Aktuelle Tabellen-Status:</h4>
                        <?php $this->show_detailed_table_status(); ?>
                    </div>
                    
                    <form method="post" style="margin-bottom: 15px;">
                        <?php wp_nonce_field('create_tables', 'create_tables_nonce'); ?>
                        <input type="submit" class="button button-primary" value="🔧 Alle Tabellen erstellen (Direkt-SQL)" 
                               onclick="return confirm('Alle fehlenden Tabellen jetzt erstellen?');">
                    </form>
                    <p class="description">Verwendet direktes SQL (nicht dbDelta) für bessere Kompatibilität.</p>
                </div>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('klage_click_settings');
                do_settings_sections('klage_click_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">N8N API URL</th>
                        <td>
                            <input type="url" name="klage_click_n8n_url" value="<?php echo esc_attr(get_option('klage_click_n8n_url')); ?>" class="regular-text" />
                            <p class="description">URL zu Ihrer N8N Instanz</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">N8N API Key</th>
                        <td>
                            <input type="password" name="klage_click_n8n_key" value="<?php echo esc_attr(get_option('klage_click_n8n_key')); ?>" class="regular-text" />
                            <p class="description">API-Schlüssel für N8N</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Debug-Modus</th>
                        <td>
                            <input type="checkbox" name="klage_click_debug_mode" value="1" <?php checked(1, get_option('klage_click_debug_mode')); ?> />
                            <label for="klage_click_debug_mode">Debug-Modus aktivieren</label>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    private function force_create_tables() {
        require_once CAH_PLUGIN_PATH . 'includes/class-database.php';
        $database = new CAH_Database();
        
        // Try advanced table creation with direct SQL
        $results = $database->create_tables_direct();
        
        if ($results['success']) {
            echo '<div class="notice notice-success"><p><strong>Erfolg!</strong> ' . $results['message'] . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> ' . $results['message'] . '</p></div>';
        }
        
        // Show detailed results
        if (get_option('klage_click_debug_mode')) {
            echo '<div class="notice notice-info"><p><strong>Debug Info:</strong><br>' . implode('<br>', $results['details']) . '</p></div>';
        }
    }
    
    private function show_detailed_table_status() {
        global $wpdb;
        
        $required_tables = array('klage_cases', 'klage_debtors', 'klage_clients', 'klage_emails', 'klage_financial', 'klage_courts', 'klage_audit', 'klage_financial_fields', 'klage_import_templates');
        
        echo '<table style="width: 100%; border-collapse: collapse;">';
        foreach ($required_tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'");
            $count = $exists ? $wpdb->get_var("SELECT COUNT(*) FROM $full_table_name") : 0;
            
            $status_icon = $exists ? '✅' : '❌';
            $status_color = $exists ? 'green' : 'red';
            
            echo '<tr>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd;">' . $status_icon . ' <strong>' . $table . '</strong></td>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd; color: ' . $status_color . ';">' . ($exists ? 'Existiert' : 'FEHLT') . '</td>';
            echo '<td style="padding: 5px; border-bottom: 1px solid #ddd;">' . $count . ' Einträge</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
    private function handle_bulk_actions() {
        global $wpdb;
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $case_ids = isset($_POST['case_ids']) ? array_map('intval', $_POST['case_ids']) : array();
        
        if (empty($case_ids)) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Keine Fälle ausgewählt.</p></div>';
            return;
        }
        
        $case_ids_placeholder = implode(',', array_fill(0, count($case_ids), '%d'));
        $affected_rows = 0;
        
        switch ($action) {
            case 'status_processing':
                $affected_rows = $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}klage_cases 
                    SET case_status = 'processing', case_updated_date = %s 
                    WHERE id IN ({$case_ids_placeholder})
                ", array_merge([current_time('mysql')], $case_ids)));
                
                if ($affected_rows > 0) {
                    echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> ' . $affected_rows . ' Fälle auf "In Bearbeitung" gesetzt.</p></div>';
                }
                break;
                
            case 'status_completed':
                $affected_rows = $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}klage_cases 
                    SET case_status = 'completed', case_updated_date = %s 
                    WHERE id IN ({$case_ids_placeholder})
                ", array_merge([current_time('mysql')], $case_ids)));
                
                if ($affected_rows > 0) {
                    echo '<div class="notice notice-success"><p><strong>🎉 Erfolg!</strong> ' . $affected_rows . ' Fälle als "Abgeschlossen" markiert.</p></div>';
                }
                break;
                
            case 'priority_high':
                $affected_rows = $wpdb->query($wpdb->prepare("
                    UPDATE {$wpdb->prefix}klage_cases 
                    SET case_priority = 'high', case_updated_date = %s 
                    WHERE id IN ({$case_ids_placeholder})
                ", array_merge([current_time('mysql')], $case_ids)));
                
                if ($affected_rows > 0) {
                    echo '<div class="notice notice-success"><p><strong>🟠 Erfolg!</strong> ' . $affected_rows . ' Fälle auf "Hohe Priorität" gesetzt.</p></div>';
                }
                break;
                
            case 'export_csv':
                $this->export_cases_csv($case_ids);
                return; // Don't show success message for export
                
            default:
                echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Unbekannte Aktion.</p></div>';
                return;
        }
        
        // Add audit log entries for bulk actions
        foreach ($case_ids as $case_id) {
            $wpdb->insert(
                $wpdb->prefix . 'klage_audit',
                array(
                    'case_id' => $case_id,
                    'action' => 'bulk_' . $action,
                    'details' => "Bulk-Aktion durchgeführt: {$action}",
                    'user_id' => get_current_user_id(),
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%d', '%s')
            );
        }
        
        if ($affected_rows === 0) {
            echo '<div class="notice notice-warning"><p><strong>⚠️ Hinweis!</strong> Keine Änderungen vorgenommen. Möglicherweise hatten die ausgewählten Fälle bereits den gewünschten Status.</p></div>';
        }
    }
    
    private function export_cases_csv($case_ids) {
        global $wpdb;
        
        $case_ids_placeholder = implode(',', array_fill(0, count($case_ids), '%d'));
        
        $cases = $wpdb->get_results($wpdb->prepare("
            SELECT 
                c.case_id,
                c.case_creation_date,
                c.case_status,
                c.case_priority,
                c.total_amount,
                c.case_notes,
                e.emails_sender_email,
                e.emails_user_email,
                e.emails_subject,
                e.emails_received_date,
                f.damages_loss,
                f.partner_fees,
                f.communication_fees,
                f.court_fees,
                f.vat
            FROM {$wpdb->prefix}klage_cases c
            LEFT JOIN {$wpdb->prefix}klage_emails e ON c.id = e.case_id
            LEFT JOIN {$wpdb->prefix}klage_financial f ON c.id = f.case_id
            WHERE c.id IN ({$case_ids_placeholder})
            ORDER BY c.case_creation_date DESC
        ", $case_ids));
        
        if (empty($cases)) {
            echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Keine Daten zum Exportieren gefunden.</p></div>';
            return;
        }
        
        // Set headers for CSV download
        $filename = 'klage_click_faelle_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV Header
        fputcsv($output, array(
            'Fall-ID',
            'Erstellungsdatum',
            'Status',
            'Priorität',
            'Gesamtbetrag',
            'Notizen',
            'Spam-Absender',
            'Betroffene E-Mail',
            'E-Mail Betreff',
            'Empfangsdatum',
            'Grundschaden',
            'Anwaltskosten',
            'Kommunikationskosten',
            'Gerichtskosten',
            'MwSt'
        ), ';');
        
        // CSV Data
        foreach ($cases as $case) {
            fputcsv($output, array(
                $case->case_id,
                $case->case_creation_date,
                $case->case_status,
                $case->case_priority,
                number_format($case->total_amount, 2, ',', '.'),
                $case->case_notes,
                $case->emails_sender_email,
                $case->emails_user_email,
                $case->emails_subject,
                $case->emails_received_date,
                number_format($case->damages_loss, 2, ',', '.'),
                number_format($case->partner_fees, 2, ',', '.'),
                number_format($case->communication_fees, 2, ',', '.'),
                number_format($case->court_fees, 2, ',', '.'),
                number_format($case->vat, 2, ',', '.')
            ), ';');
        }
        
        fclose($output);
        exit; // Important: Stop execution after sending CSV
    }
    
    public function admin_page_financial() {
        global $wpdb;
        
        // Handle financial field management
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['financial_action'])) {
            $this->handle_financial_field_action();
        }
        
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'manage';
        
        switch ($action) {
            case 'calculator':
                $this->render_financial_calculator();
                break;
            default:
                $this->render_financial_field_manager();
                break;
        }
    }
    
    private function render_financial_field_manager() {
        global $wpdb;
        
        // Get existing fields
        $fields = $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}klage_financial_fields 
            WHERE is_active = 1 
            ORDER BY display_order ASC
        ");
        
        ?>
        <div class="wrap">
            <h1>💰 Finanz-Rechner Verwaltung</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.0.9 - Dynamischer Finanz-Rechner!</strong></p>
                <p>Erstellen Sie benutzerdefinierte Finanzfelder mit Excel-ähnlicher Funktionalität und automatischen Berechnungen.</p>
            </div>
            
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=klage-click-financial&action=calculator'); ?>" class="button button-primary">
                    🧮 Rechner öffnen
                </a>
                <a href="<?php echo admin_url('admin.php?page=klage-click-import'); ?>" class="button button-secondary">
                    📊 CSV Import (Forderungen.com)
                </a>
            </div>
            
            <!-- Add New Field Form -->
            <div class="postbox" style="margin-bottom: 30px;">
                <h2 class="hndle">➕ Neues Finanzfeld hinzufügen</h2>
                <div class="inside" style="padding: 20px;">
                    <form method="post">
                        <input type="hidden" name="financial_action" value="add_field">
                        <?php wp_nonce_field('financial_field_action', 'financial_field_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="field_name">Feldname (technisch)</label></th>
                                <td>
                                    <input type="text" id="field_name" name="field_name" class="regular-text" required>
                                    <p class="description">Eindeutiger technischer Name (z.B. zusatz_kosten)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="field_label">Anzeigename</label></th>
                                <td>
                                    <input type="text" id="field_label" name="field_label" class="regular-text" required>
                                    <p class="description">Name wie er im Rechner angezeigt wird (z.B. Zusatzkosten)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="field_type">Feldtyp</label></th>
                                <td>
                                    <select id="field_type" name="field_type" required>
                                        <option value="number">💰 Betrag (Zahl)</option>
                                        <option value="percentage">% Prozentsatz</option>
                                        <option value="text">📝 Text</option>
                                        <option value="dropdown">📋 Dropdown</option>
                                        <option value="date">📅 Datum</option>
                                        <option value="formula">🧮 Formel</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="field_options">Optionen/Formel</label></th>
                                <td>
                                    <textarea id="field_options" name="field_options" class="large-text" rows="3"></textarea>
                                    <p class="description">
                                        <strong>Dropdown:</strong> Option1,Option2,Option3<br>
                                        <strong>Formel:</strong> =SUM(grundschaden,anwaltskosten) oder =grundschaden*0.19
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="default_value">Standardwert</label></th>
                                <td>
                                    <input type="text" id="default_value" name="default_value" class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="display_order">Reihenfolge</label></th>
                                <td>
                                    <input type="number" id="display_order" name="display_order" class="small-text" value="10">
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary" value="💾 Feld hinzufügen">
                        </p>
                    </form>
                </div>
            </div>
            
            <!-- Existing Fields List -->
            <div class="postbox">
                <h2 class="hndle">📋 Vorhandene Finanzfelder</h2>
                <div class="inside">
                    <?php if (empty($fields)): ?>
                        <p style="padding: 20px; text-align: center; color: #666;">
                            Keine benutzerdefinierten Felder gefunden. Erstellen Sie Ihr erstes Feld oben.
                        </p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Anzeigename</th>
                                    <th>Typ</th>
                                    <th>Standardwert</th>
                                    <th>Formel/Optionen</th>
                                    <th>Reihenfolge</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $field): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($field->field_label); ?></strong></td>
                                        <td>
                                            <?php
                                            $type_icons = array(
                                                'number' => '💰 Betrag',
                                                'percentage' => '% Prozent',
                                                'text' => '📝 Text',
                                                'dropdown' => '📋 Dropdown',
                                                'date' => '📅 Datum',
                                                'formula' => '🧮 Formel'
                                            );
                                            echo $type_icons[$field->field_type] ?? $field->field_type;
                                            ?>
                                        </td>
                                        <td><?php echo esc_html($field->default_value); ?></td>
                                        <td><?php echo esc_html(wp_trim_words($field->field_options, 10)); ?></td>
                                        <td><?php echo esc_html($field->display_order); ?></td>
                                        <td>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="financial_action" value="delete_field">
                                                <input type="hidden" name="field_id" value="<?php echo esc_attr($field->id); ?>">
                                                <?php wp_nonce_field('financial_field_action', 'financial_field_nonce'); ?>
                                                <input type="submit" class="button button-small button-link-delete" 
                                                       value="Löschen" onclick="return confirm('Feld wirklich löschen?');">
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Default Fields Info -->
            <div class="postbox" style="margin-top: 20px;">
                <h2 class="hndle">ℹ️ Standard DSGVO-Felder</h2>
                <div class="inside" style="padding: 20px;">
                    <p>Diese Felder sind immer verfügbar und können nicht gelöscht werden:</p>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li><strong>Grundschaden:</strong> €350.00 (DSGVO Art. 82)</li>
                        <li><strong>Anwaltskosten:</strong> €96.90 (RVG)</li>
                        <li><strong>Kommunikationskosten:</strong> €13.36</li>
                        <li><strong>Gerichtskosten:</strong> €32.00</li>
                        <li><strong>MwSt:</strong> €87.85 (19%)</li>
                        <li><strong>Gesamtsumme:</strong> Automatische Berechnung</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function handle_financial_field_action() {
        global $wpdb;
        
        if (!wp_verify_nonce($_POST['financial_field_nonce'], 'financial_field_action')) {
            echo '<div class="notice notice-error"><p>Sicherheitsfehler. Bitte versuchen Sie es erneut.</p></div>';
            return;
        }
        
        $action = sanitize_text_field($_POST['financial_action']);
        
        switch ($action) {
            case 'add_field':
                $field_name = sanitize_text_field($_POST['field_name']);
                $field_label = sanitize_text_field($_POST['field_label']);
                $field_type = sanitize_text_field($_POST['field_type']);
                $field_options = sanitize_textarea_field($_POST['field_options']);
                $default_value = sanitize_text_field($_POST['default_value']);
                $display_order = intval($_POST['display_order']);
                
                // Check if field name already exists
                $existing = $wpdb->get_var($wpdb->prepare("
                    SELECT id FROM {$wpdb->prefix}klage_financial_fields 
                    WHERE field_name = %s AND is_active = 1
                ", $field_name));
                
                if ($existing) {
                    echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Ein Feld mit diesem Namen existiert bereits.</p></div>';
                    return;
                }
                
                $result = $wpdb->insert(
                    $wpdb->prefix . 'klage_financial_fields',
                    array(
                        'field_name' => $field_name,
                        'field_label' => $field_label,
                        'field_type' => $field_type,
                        'field_options' => $field_options,
                        'default_value' => $default_value,
                        'display_order' => $display_order,
                        'is_permanent' => 1,
                        'is_active' => 1
                    ),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
                );
                
                if ($result) {
                    echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> Finanzfeld "' . esc_html($field_label) . '" wurde hinzugefügt.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Feld konnte nicht hinzugefügt werden.</p></div>';
                }
                break;
                
            case 'delete_field':
                $field_id = intval($_POST['field_id']);
                
                $result = $wpdb->update(
                    $wpdb->prefix . 'klage_financial_fields',
                    array('is_active' => 0),
                    array('id' => $field_id),
                    array('%d'),
                    array('%d')
                );
                
                if ($result !== false) {
                    echo '<div class="notice notice-success"><p><strong>✅ Erfolg!</strong> Finanzfeld wurde gelöscht.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p><strong>Fehler!</strong> Feld konnte nicht gelöscht werden.</p></div>';
                }
                break;
        }
    }
}