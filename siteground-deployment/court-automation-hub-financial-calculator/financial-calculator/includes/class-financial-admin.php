<?php
/**
 * Financial Calculator Admin Interface
 * Manages the WordPress admin interface for financial calculator
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_ajax_cah_financial_calculate', array($this, 'ajax_calculate'));
        add_action('wp_ajax_cah_financial_save', array($this, 'ajax_save'));
    }
    
    public function add_admin_menu() {
        // Add submenu under Court Automation Hub
        add_submenu_page(
            'klage-click-hub',
            'Financial Calculator',
            '🧮 Financial Calculator',
            'manage_options',
            'klage-click-financial-calculator',
            array($this, 'admin_page')
        );
        
        // Add submenu for template management
        add_submenu_page(
            'klage-click-hub',
            'Financial Templates',
            '📊 Financial Templates',
            'manage_options',
            'klage-click-financial-templates',
            array($this, 'templates_page')
        );
    }
    
    public function admin_init() {
        register_setting('cah_financial_settings', 'cah_financial_default_template');
        register_setting('cah_financial_settings', 'cah_financial_tax_rate');
        register_setting('cah_financial_settings', 'cah_financial_currency');
    }
    
    public function admin_page() {
        $action = $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'case':
                $this->render_case_calculator();
                break;
            case 'templates':
                $this->render_templates_management();
                break;
            default:
                $this->render_dashboard();
        }
    }
    
    public function templates_page() {
        $this->render_templates_management();
    }
    
    private function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>🧮 Financial Calculator - Dashboard</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>🚀 v1.0.0 - Advanced Financial Calculator!</strong></p>
                <p>Complete CRUD system for case financial management with templates and automatic calculations.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
                <div class="postbox">
                    <h2 class="hndle">📊 Quick Actions</h2>
                    <div class="inside" style="padding: 20px;">
                        <p><a href="<?php echo admin_url('admin.php?page=klage-click-financial-templates'); ?>" class="button button-primary">Manage Templates</a></p>
                        <p><a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">View Cases</a></p>
                    </div>
                </div>
                
                <div class="postbox">
                    <h2 class="hndle">🎯 Features</h2>
                    <div class="inside" style="padding: 20px;">
                        <ul>
                            <li>✅ Global financial templates</li>
                            <li>✅ Per-case customization</li>
                            <li>✅ Automatic MwSt calculation</li>
                            <li>✅ Template management</li>
                            <li>✅ Real-time calculations</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_case_calculator() {
        $case_id = $_GET['case_id'] ?? 0;
        
        if (!$case_id) {
            echo '<div class="wrap"><h1>Error: Case ID required</h1></div>';
            return;
        }
        
        $database = new CAH_Financial_Database();
        $financial_data = $database->get_case_financial_data($case_id);
        $totals = $database->calculate_case_totals($case_id);
        
        ?>
        <div class="wrap">
            <h1>🧮 Financial Calculator - Case #<?php echo $case_id; ?></h1>
            
            <div class="postbox">
                <h2 class="hndle">Financial Items</h2>
                <div class="inside" style="padding: 20px;">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Amount (€)</th>
                                <th>Taxable</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financial_data as $item): ?>
                            <tr>
                                <td><?php echo esc_html($item->item_name); ?></td>
                                <td><?php echo esc_html($item->item_category); ?></td>
                                <td>€<?php echo number_format($item->amount, 2); ?></td>
                                <td><?php echo $item->is_taxable ? 'Yes' : 'No'; ?></td>
                                <td><?php echo esc_html($item->description); ?></td>
                                <td>
                                    <button class="button button-small" onclick="editItem(<?php echo $item->id; ?>)">Edit</button>
                                    <button class="button button-small button-link-delete" onclick="deleteItem(<?php echo $item->id; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                        <h3>Totals</h3>
                        <p><strong>Subtotal:</strong> €<?php echo number_format($totals['subtotal'], 2); ?></p>
                        <p><strong>Tax (19%):</strong> €<?php echo number_format($totals['tax_amount'], 2); ?></p>
                        <p style="font-size: 18px; color: #0073aa;"><strong>Total:</strong> €<?php echo number_format($totals['total'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_templates_management() {
        $templates = new CAH_Financial_Templates();
        $all_templates = $templates->get_all_templates();
        
        ?>
        <div class="wrap">
            <h1>📊 Financial Templates Management</h1>
            
            <div class="postbox">
                <h2 class="hndle">Available Templates</h2>
                <div class="inside" style="padding: 20px;">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Template Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_templates as $template): ?>
                            <tr>
                                <td><strong><?php echo esc_html($template->template_name); ?></strong></td>
                                <td><?php echo esc_html($template->template_type); ?></td>
                                <td><?php echo esc_html($template->description); ?></td>
                                <td><?php echo $template->is_default ? '✅ Default' : ''; ?></td>
                                <td>
                                    <button class="button button-small" onclick="viewTemplate(<?php echo $template->id; ?>)">View Items</button>
                                    <button class="button button-small" onclick="editTemplate(<?php echo $template->id; ?>)">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <script>
        function viewTemplate(templateId) {
            // TODO: Implement template viewing
            alert('Template viewing functionality will be implemented');
        }
        
        function editTemplate(templateId) {
            // TODO: Implement template editing
            alert('Template editing functionality will be implemented');
        }
        </script>
        <?php
    }
    
    public function ajax_calculate() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $case_id = $_POST['case_id'] ?? 0;
        
        if (!$case_id) {
            wp_die('Case ID required');
        }
        
        $database = new CAH_Financial_Database();
        $totals = $database->calculate_case_totals($case_id);
        
        wp_send_json_success($totals);
    }
    
    public function ajax_save() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $case_id = $_POST['case_id'] ?? 0;
        $financial_data = $_POST['financial_data'] ?? array();
        
        if (!$case_id) {
            wp_die('Case ID required');
        }
        
        $database = new CAH_Financial_Database();
        $database->save_case_financial_data($case_id, $financial_data);
        
        wp_send_json_success('Financial data saved successfully');
    }
}