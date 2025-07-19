<?php
/**
 * Case Financial Integration
 * Handles integration between case management and financial data
 */

class CAH_Case_Financial_Integration {
    
    public function __construct() {
        // Add hooks for case management integration
        add_action('admin_enqueue_scripts', array($this, 'enqueue_case_financial_scripts'));
        add_action('wp_ajax_get_case_financial_data', array($this, 'ajax_get_case_financial_data'));
        add_action('wp_ajax_save_case_financial_data', array($this, 'ajax_save_case_financial_data'));
        add_action('wp_ajax_save_case_template', array($this, 'ajax_save_case_template'));
        add_action('wp_ajax_get_template_data', array($this, 'ajax_get_template_data'));
    }
    
    public function enqueue_case_financial_scripts() {
        if (isset($_GET['page']) && $_GET['page'] === 'klage-click-cases') {
            wp_enqueue_script('cah-case-financial', CAH_FINANCIAL_PLUGIN_URL . 'assets/js/case-financial.js', array('jquery'), CAH_FINANCIAL_PLUGIN_VERSION, true);
            wp_enqueue_style('cah-case-financial', CAH_FINANCIAL_PLUGIN_URL . 'assets/css/case-financial.css', array(), CAH_FINANCIAL_PLUGIN_VERSION);
            
            wp_localize_script('cah-case-financial', 'cah_financial_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cah_financial_nonce')
            ));
        }
    }
    
    // Get financial data for a case
    public function ajax_get_case_financial_data() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id']);
        $db_manager = new CAH_Financial_DB_Manager();
        
        $financial_data = $db_manager->get_case_financial_data($case_id);
        
        if ($financial_data) {
            $financial_data->cost_items = json_decode($financial_data->cost_items, true);
        }
        
        wp_send_json_success($financial_data);
    }
    
    // Save financial data for a case
    public function ajax_save_case_financial_data() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id']);
        $template_id = intval($_POST['template_id']);
        $cost_items = $_POST['cost_items'];
        $mwst_rate = floatval($_POST['mwst_rate']);
        
        $calculator = new CAH_Financial_Calculator_Engine();
        $calculation = $calculator->calculate_totals($cost_items, $mwst_rate);
        
        $financial_data = array(
            'template_id' => $template_id,
            'cost_items' => json_encode($cost_items),
            'subtotal' => $calculation['subtotal'],
            'mwst_amount' => $calculation['mwst_amount'],
            'mwst_rate' => $mwst_rate,
            'total' => $calculation['total']
        );
        
        $db_manager = new CAH_Financial_DB_Manager();
        $db_manager->update_case_financial_data($case_id, $financial_data);
        
        wp_send_json_success(array(
            'calculation' => $calculation,
            'message' => 'Financial data saved successfully'
        ));
    }
    
    // Save case modifications as new template
    public function ajax_save_case_template() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $template_name = sanitize_text_field($_POST['template_name']);
        $template_description = sanitize_textarea_field($_POST['template_description']);
        $cost_items = $_POST['cost_items'];
        $mwst_rate = floatval($_POST['mwst_rate']);
        
        $template_data = array(
            'name' => $template_name,
            'description' => $template_description,
            'is_default' => 0,
            'cost_items' => json_encode($cost_items),
            'mwst_rate' => $mwst_rate
        );
        
        $template_manager = new CAH_Financial_Template_Manager();
        $template_id = $template_manager->save_template($template_data);
        
        wp_send_json_success(array(
            'template_id' => $template_id,
            'message' => 'Template saved successfully'
        ));
    }
    
    // Get template data for dropdown population
    public function ajax_get_template_data() {
        check_ajax_referer('cah_financial_nonce', 'nonce');
        
        $template_id = intval($_POST['template_id']);
        $template_manager = new CAH_Financial_Template_Manager();
        $template = $template_manager->get_template($template_id);
        
        if ($template) {
            $template->cost_items = json_decode($template->cost_items, true);
        }
        
        wp_send_json_success($template);
    }
    
    // Generate financial tab content for case management
    public function render_case_financial_tab($case_id) {
        $template_manager = new CAH_Financial_Template_Manager();
        $templates = $template_manager->get_all_templates();
        $db_manager = new CAH_Financial_DB_Manager();
        $financial_data = $db_manager->get_case_financial_data($case_id);
        
        if ($financial_data) {
            $cost_items = json_decode($financial_data->cost_items, true);
        } else {
            $cost_items = array(
                'grundkosten' => 0,
                'gerichtskosten' => 0,
                'anwaltskosten' => 0,
                'sonstige' => 0
            );
        }
        
        ?>
        <div id="cah-financial-tab" class="cah-financial-container">
            <h3>💰 Finanzielle Daten</h3>
            
            <!-- Template Selection -->
            <div class="cah-financial-section">
                <h4>Vorlage auswählen (optional)</h4>
                <select id="cah-template-selector" style="width: 300px;">
                    <option value="">-- Keine Vorlage --</option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo $template->id; ?>" <?php selected($financial_data ? $financial_data->template_id : 0, $template->id); ?>>
                            <?php echo esc_html($template->name); ?> (Total: €<?php 
                                $template_costs = json_decode($template->cost_items, true);
                                $calculator = new CAH_Financial_Calculator_Engine();
                                $calc = $calculator->calculate_totals($template_costs, $template->mwst_rate);
                                echo number_format($calc['total'], 2, ',', '.');
                            ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="cah-load-template" class="button">Vorlage laden</button>
            </div>
            
            <!-- Cost Items -->
            <div class="cah-financial-section">
                <h4>Kosten Details</h4>
                <table class="form-table">
                    <tr>
                        <th>Grundkosten (€)</th>
                        <td><input type="number" id="cost-grundkosten" step="0.01" min="0" value="<?php echo esc_attr($cost_items['grundkosten'] ?? 0); ?>" style="width: 120px;"></td>
                    </tr>
                    <tr>
                        <th>Gerichtskosten (€)</th>
                        <td><input type="number" id="cost-gerichtskosten" step="0.01" min="0" value="<?php echo esc_attr($cost_items['gerichtskosten'] ?? 0); ?>" style="width: 120px;"></td>
                    </tr>
                    <tr>
                        <th>Anwaltskosten (€)</th>
                        <td><input type="number" id="cost-anwaltskosten" step="0.01" min="0" value="<?php echo esc_attr($cost_items['anwaltskosten'] ?? 0); ?>" style="width: 120px;"></td>
                    </tr>
                    <tr>
                        <th>Sonstige (€)</th>
                        <td><input type="number" id="cost-sonstige" step="0.01" min="0" value="<?php echo esc_attr($cost_items['sonstige'] ?? 0); ?>" style="width: 120px;"></td>
                    </tr>
                    <tr>
                        <th>MwSt Satz (%)</th>
                        <td><input type="number" id="mwst-rate" step="0.01" min="0" max="100" value="<?php echo esc_attr($financial_data ? $financial_data->mwst_rate : 19.00); ?>" style="width: 80px;"></td>
                    </tr>
                </table>
                
                <button type="button" id="cah-calculate" class="button">Berechnen</button>
                <button type="button" id="cah-save-financial" class="button button-primary">Finanzielle Daten speichern</button>
            </div>
            
            <!-- Calculation Results -->
            <div class="cah-financial-section">
                <h4>Berechnung</h4>
                <table class="form-table">
                    <tr>
                        <th>Zwischensumme:</th>
                        <td><strong id="calc-subtotal">€<?php echo $financial_data ? number_format($financial_data->subtotal, 2, ',', '.') : '0,00'; ?></strong></td>
                    </tr>
                    <tr>
                        <th>MwSt:</th>
                        <td><strong id="calc-mwst">€<?php echo $financial_data ? number_format($financial_data->mwst_amount, 2, ',', '.') : '0,00'; ?></strong></td>
                    </tr>
                    <tr style="border-top: 2px solid #ddd;">
                        <th>Gesamtsumme:</th>
                        <td><strong id="calc-total" style="font-size: 16px; color: #0073aa;">€<?php echo $financial_data ? number_format($financial_data->total, 2, ',', '.') : '0,00'; ?></strong></td>
                    </tr>
                </table>
            </div>
            
            <!-- Save as Template -->
            <div class="cah-financial-section">
                <h4>Als neue Vorlage speichern</h4>
                <p>
                    <input type="text" id="new-template-name" placeholder="Vorlagenname" style="width: 200px;">
                    <input type="text" id="new-template-description" placeholder="Beschreibung (optional)" style="width: 300px;">
                    <button type="button" id="cah-save-template" class="button">Als Vorlage speichern</button>
                </p>
            </div>
            
            <input type="hidden" id="cah-case-id" value="<?php echo $case_id; ?>">
        </div>
        
        <style>
        .cah-financial-container { margin: 20px 0; }
        .cah-financial-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
        .cah-financial-section h4 { margin-top: 0; color: #0073aa; }
        #calc-total { font-weight: bold; font-size: 18px; }
        </style>
        <?php
    }
}