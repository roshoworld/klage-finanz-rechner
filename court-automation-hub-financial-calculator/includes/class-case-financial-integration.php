<?php
/**
 * Case Financial Integration - Integrates financial calculator into core case management
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Case_Financial_Integration {
    
    private $db_manager;
    private $calculator;
    private $template_manager;
    
    public function __construct() {
        $this->db_manager = new CAH_Financial_DB_Manager();
        $this->calculator = new CAH_Financial_Calculator_Engine();
        $this->template_manager = new CAH_Financial_Template_Manager();
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // Hook into core case creation/update actions
        add_action('cah_case_created', array($this, 'handle_case_created'));
        add_action('cah_case_updated', array($this, 'handle_case_updated'));
        add_action('cah_case_deleted', array($this, 'handle_case_deleted'));
        
        // AJAX handlers for financial tab
        add_action('wp_ajax_load_financial_templates', array($this, 'ajax_load_templates'));
        add_action('wp_ajax_load_template_items', array($this, 'ajax_load_template_items'));
        add_action('wp_ajax_calculate_financial_totals', array($this, 'ajax_calculate_totals'));
        add_action('wp_ajax_save_case_financial', array($this, 'ajax_save_case_financial'));
        add_action('wp_ajax_save_financial_as_template', array($this, 'ajax_save_as_template'));
        add_action('wp_ajax_add_cost_item', array($this, 'ajax_add_cost_item'));
        add_action('wp_ajax_update_cost_item', array($this, 'ajax_update_cost_item'));
        add_action('wp_ajax_delete_cost_item', array($this, 'ajax_delete_cost_item'));
        
        // Enqueue scripts for financial integration
        add_action('admin_enqueue_scripts', array($this, 'enqueue_integration_scripts'));
        
        // Add financial content to case tabs
        add_action('admin_footer', array($this, 'render_financial_tab_content'));
    }
    
    public function enqueue_integration_scripts() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'klage-click-cases') !== false) {
            wp_enqueue_script('cah-case-financial', CAH_FC_PLUGIN_URL . 'assets/js/case-financial.js', array('jquery'), CAH_FC_PLUGIN_VERSION, true);
            wp_enqueue_style('cah-case-financial', CAH_FC_PLUGIN_URL . 'assets/css/case-financial.css', array(), CAH_FC_PLUGIN_VERSION);
            
            wp_localize_script('cah-case-financial', 'cah_case_financial', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cah_financial_nonce'),
                'currency_symbol' => '€',
                'vat_rate' => '19.00',
                'strings' => array(
                    'loading' => 'Laden...',
                    'error' => 'Fehler beim Laden',
                    'confirm_delete' => 'Sind Sie sicher, dass Sie diesen Kostenpunkt löschen möchten?',
                    'save_success' => 'Erfolgreich gespeichert',
                    'save_error' => 'Fehler beim Speichern'
                )
            ));
        }
    }
    
    public function render_financial_tab_content() {
        $screen = get_current_screen();
        if ($screen && strpos($screen->id, 'klage-click-cases') !== false) {
            ?>
            <script type="text/html" id="financial-tab-template">
                <div id="case-financial-content">
                    <div class="financial-section">
                        <h3>💰 Finanzielle Konfiguration</h3>
                        
                        <!-- Template Selection -->
                        <div class="template-selection">
                            <label for="financial-template-select"><strong>Vorlage auswählen:</strong></label>
                            <select id="financial-template-select" style="width: 300px; margin-left: 10px;">
                                <option value="">Bitte wählen...</option>
                            </select>
                            <button type="button" id="load-template-btn" class="button button-secondary" style="margin-left: 10px;">Vorlage laden</button>
                        </div>
                        
                        <!-- Cost Items Table -->
                        <div class="cost-items-section" style="margin-top: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h4>Kostenpunkte</h4>
                                <button type="button" id="add-cost-item-btn" class="button button-primary">+ Neuen Kostenpunkt hinzufügen</button>
                            </div>
                            
                            <table id="cost-items-table" class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Kategorie</th>
                                        <th>Betrag</th>
                                        <th>Beschreibung</th>
                                        <th>Aktionen</th>
                                    </tr>
                                </thead>
                                <tbody id="cost-items-tbody">
                                    <!-- Items will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Calculation Summary -->
                        <div class="calculation-summary" style="margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                            <h4>Berechnung</h4>
                            <div class="calculation-row">
                                <span>Zwischensumme:</span>
                                <span id="calc-subtotal">€ 0,00</span>
                            </div>
                            <div class="calculation-row">
                                <span>MwSt. (19%):</span>
                                <span id="calc-vat">€ 0,00</span>
                            </div>
                            <div class="calculation-row total-row">
                                <strong>
                                    <span>Gesamtsumme:</span>
                                    <span id="calc-total">€ 0,00</span>
                                </strong>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="financial-actions" style="margin-top: 20px;">
                            <button type="button" id="save-case-financial-btn" class="button button-primary">💾 Finanzielle Daten speichern</button>
                            <button type="button" id="save-as-template-btn" class="button button-secondary">📋 Als neue Vorlage speichern</button>
                            <button type="button" id="recalculate-btn" class="button button-secondary">🔄 Neu berechnen</button>
                        </div>
                    </div>
                </div>
            </script>
            
            <!-- Cost Item Modal -->
            <div id="cost-item-modal" style="display: none;">
                <div class="cost-item-form">
                    <h3 id="modal-title">Kostenpunkt hinzufügen</h3>
                    <form id="cost-item-form">
                        <input type="hidden" id="cost-item-id" value="">
                        <table class="form-table">
                            <tr>
                                <th><label for="item-name">Name:</label></th>
                                <td><input type="text" id="item-name" required style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <th><label for="item-category">Kategorie:</label></th>
                                <td>
                                    <select id="item-category" required style="width: 100%;">
                                        <option value="grundkosten">Grundkosten</option>
                                        <option value="gerichtskosten">Gerichtskosten</option>
                                        <option value="anwaltskosten">Anwaltskosten</option>
                                        <option value="sonstige">Sonstige Kosten</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="item-amount">Betrag:</label></th>
                                <td><input type="number" id="item-amount" step="0.01" min="0" required style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <th><label for="item-description">Beschreibung:</label></th>
                                <td><textarea id="item-description" rows="3" style="width: 100%;"></textarea></td>
                            </tr>
                        </table>
                        <div class="modal-actions">
                            <button type="submit" class="button button-primary">Speichern</button>
                            <button type="button" id="cancel-item-btn" class="button button-secondary">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Initialize financial tab when it becomes active
                $(document).on('click', 'a[href="#financial"]', function() {
                    if ($('#case-financial-integration').is(':empty')) {
                        $('#case-financial-integration').html($('#financial-tab-template').html());
                        initializeFinancialTab();
                    }
                });
                
                function initializeFinancialTab() {
                    loadTemplateOptions();
                    loadCaseFinancialData();
                }
                
                function loadTemplateOptions() {
                    $.ajax({
                        url: cah_case_financial.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'load_financial_templates',
                            nonce: cah_case_financial.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                var select = $('#financial-template-select');
                                select.find('option:not(:first)').remove();
                                
                                $.each(response.data, function(i, template) {
                                    select.append($('<option>', {
                                        value: template.id,
                                        text: template.name + ' (' + template.description + ')'
                                    }));
                                });
                            }
                        }
                    });
                }
                
                function loadCaseFinancialData() {
                    // Implementation for loading existing case financial data
                    // This would be called when editing an existing case
                }
            });
            </script>
            <?php
        }
    }
    
    // AJAX Handlers
    public function ajax_load_templates() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $templates = $this->db_manager->get_templates();
        
        wp_send_json_success($templates);
    }
    
    public function ajax_load_template_items() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $template_id = intval($_POST['template_id']);
        if (!$template_id) {
            wp_send_json_error('Invalid template ID');
        }
        
        $items = $this->db_manager->get_cost_items_by_template($template_id);
        $totals = $this->calculator->calculate_totals($items);
        
        wp_send_json_success(array(
            'items' => $items,
            'totals' => $totals
        ));
    }
    
    public function ajax_calculate_totals() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $items_data = json_decode(stripslashes($_POST['items']), true);
        if (!$items_data) {
            wp_send_json_error('Invalid items data');
        }
        
        // Convert array data to objects for calculator
        $items = array();
        foreach ($items_data as $item_data) {
            $item = (object) $item_data;
            $items[] = $item;
        }
        
        $totals = $this->calculator->calculate_totals($items);
        
        wp_send_json_success($totals);
    }
    
    public function ajax_save_case_financial() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id']);
        $template_id = intval($_POST['template_id']) ?: null;
        $items_data = json_decode(stripslashes($_POST['items']), true);
        $totals_data = json_decode(stripslashes($_POST['totals']), true);
        
        if (!$case_id) {
            wp_send_json_error('Invalid case ID');
        }
        
        // Save or update case financial record
        $existing_financial = $this->db_manager->get_case_financial($case_id);
        
        if ($existing_financial) {
            $this->db_manager->update_case_financial($case_id, array(
                'template_id' => $template_id,
                'subtotal' => $totals_data['subtotal'],
                'vat_rate' => $totals_data['vat_rate'],
                'vat_amount' => $totals_data['vat_amount'],
                'total_amount' => $totals_data['total_amount']
            ));
        } else {
            $this->db_manager->create_case_financial($case_id, $template_id);
            $this->db_manager->update_case_financial($case_id, array(
                'subtotal' => $totals_data['subtotal'],
                'vat_rate' => $totals_data['vat_rate'],
                'vat_amount' => $totals_data['vat_amount'],
                'total_amount' => $totals_data['total_amount']
            ));
        }
        
        // Delete existing case cost items and create new ones
        $this->db_manager->wpdb->delete(
            $this->db_manager->wpdb->prefix . 'cah_cost_items',
            array('case_id' => $case_id),
            array('%d')
        );
        
        if ($items_data) {
            foreach ($items_data as $item) {
                $this->db_manager->create_cost_item(
                    null,
                    $case_id,
                    $item['name'],
                    $item['category'],
                    $item['amount'],
                    $item['description'] ?: '',
                    false,
                    $item['sort_order'] ?: 0
                );
            }
        }
        
        wp_send_json_success('Financial data saved successfully');
    }
    
    public function ajax_save_as_template() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id']);
        $template_name = sanitize_text_field($_POST['template_name']);
        $template_description = sanitize_textarea_field($_POST['template_description']);
        
        if (!$case_id || !$template_name) {
            wp_send_json_error('Case ID and template name are required');
        }
        
        $new_template_id = $this->template_manager->save_case_as_template(
            $case_id,
            $template_name,
            $template_description
        );
        
        if ($new_template_id) {
            wp_send_json_success(array(
                'template_id' => $new_template_id,
                'message' => 'Template created successfully'
            ));
        } else {
            wp_send_json_error('Failed to create template');
        }
    }
    
    // Case event handlers
    public function handle_case_created($case_id) {
        // Optionally create default financial record for new cases
        // This could be based on user preferences or default template
    }
    
    public function handle_case_updated($case_id) {
        // Handle case updates if needed
    }
    
    public function handle_case_deleted($case_id) {
        // Clean up financial data when case is deleted
        $this->db_manager->delete_case_financial($case_id);
    }
}