<?php
/**
 * Financial Calculator Integration
 * Handles integration with main Court Automation Hub plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Integration {
    
    public function __construct() {
        // Add hooks for main plugin integration
        add_action('cah_case_form_fields', array($this, 'add_financial_fields'));
        add_action('cah_case_form_save', array($this, 'save_financial_data'));
        add_action('cah_case_display', array($this, 'display_financial_summary'));
        
        // Add financial calculator to case editing
        add_action('cah_case_tabs', array($this, 'add_financial_tab'));
        add_action('cah_case_tab_content', array($this, 'render_financial_tab'));
        
        // Add hooks for main plugin to trigger
        add_action('init', array($this, 'add_main_plugin_hooks'));
    }
    
    public function add_main_plugin_hooks() {
        // Add hooks that main plugin can use
        if (function_exists('do_action')) {
            // These hooks will be called from main plugin
        }
    }
    
    public function add_financial_fields($case_id = null) {
        $templates = new CAH_Financial_Templates();
        $all_templates = $templates->get_all_templates();
        
        ?>
        <div class="postbox">
            <h2 class="hndle">💰 Financial Calculation</h2>
            <div class="inside" style="padding: 20px;">
                <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <p><strong>🧮 Advanced Financial Calculator Active!</strong></p>
                    <p>Select a template to apply financial calculations to this case.</p>
                </div>
                
                <label for="financial_template">
                    <strong>Financial Template:</strong>
                    <select name="financial_template" id="financial_template" class="regular-text">
                        <option value="">Select Template</option>
                        <?php foreach ($all_templates as $template): ?>
                        <option value="<?php echo $template->id; ?>" <?php selected($template->is_default, 1); ?>>
                            <?php echo esc_html($template->template_name); ?>
                            <?php if ($template->is_default): ?> (Default)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                
                <div id="financial-preview" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 5px; display: none;">
                    <h4>Template Preview:</h4>
                    <div id="financial-preview-content"></div>
                </div>
                
                <p class="description">
                    Financial calculations can be customized after case creation in the Financial Calculator.
                </p>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateSelect = document.getElementById('financial_template');
            const preview = document.getElementById('financial-preview');
            const previewContent = document.getElementById('financial-preview-content');
            
            templateSelect.addEventListener('change', function() {
                const templateId = this.value;
                
                if (templateId) {
                    // Show preview
                    preview.style.display = 'block';
                    previewContent.innerHTML = '<p>Loading template preview...</p>';
                    
                    // Load template preview via AJAX
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        body: new FormData(Object.assign(document.createElement('form'), {
                            innerHTML: '<input name="action" value="cah_financial_template_preview"><input name="template_id" value="' + templateId + '">'
                        }))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            previewContent.innerHTML = data.data;
                        } else {
                            previewContent.innerHTML = '<p>Error loading template preview</p>';
                        }
                    });
                } else {
                    preview.style.display = 'none';
                }
            });
        });
        </script>
        <?php
    }
    
    public function save_financial_data($case_id) {
        $template_id = $_POST['financial_template'] ?? '';
        
        if ($template_id) {
            $templates = new CAH_Financial_Templates();
            $templates->apply_template_to_case($case_id, $template_id);
        }
    }
    
    public function display_financial_summary($case_id) {
        $database = new CAH_Financial_Database();
        $financial_data = $database->get_case_financial_data($case_id);
        
        if (empty($financial_data)) {
            return;
        }
        
        $totals = $database->calculate_case_totals($case_id);
        
        ?>
        <div class="postbox">
            <h2 class="hndle">💰 Financial Summary</h2>
            <div class="inside" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong>Subtotal:</strong><br>
                        €<?php echo number_format($totals['subtotal'], 2); ?>
                    </div>
                    <div>
                        <strong>Tax (19%):</strong><br>
                        €<?php echo number_format($totals['tax_amount'], 2); ?>
                    </div>
                    <div style="background: #0073aa; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                        <strong>Total:</strong><br>
                        €<?php echo number_format($totals['total'], 2); ?>
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-financial-calculator&action=case&case_id=' . $case_id); ?>" class="button button-primary">
                        🧮 Edit Financial Details
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function add_financial_tab($case_id) {
        ?>
        <a href="#financial-tab" class="nav-tab" onclick="showTab('financial-tab', this)">
            💰 Financial Calculator
        </a>
        <?php
    }
    
    public function render_financial_tab($case_id) {
        $database = new CAH_Financial_Database();
        $financial_data = $database->get_case_financial_data($case_id);
        $totals = $database->calculate_case_totals($case_id);
        
        ?>
        <div id="financial-tab" class="tab-content" style="display: none;">
            <h3>💰 Financial Calculator</h3>
            
            <div class="postbox">
                <h2 class="hndle">Financial Items</h2>
                <div class="inside" style="padding: 20px;">
                    <div style="margin-bottom: 20px;">
                        <button class="button button-primary" onclick="addFinancialItem()">
                            ➕ Add Financial Item
                        </button>
                        <button class="button button-secondary" onclick="applyTemplate()">
                            📋 Apply Template
                        </button>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped" id="financial-items-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Amount (€)</th>
                                <th>Taxable</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financial_data as $item): ?>
                            <tr data-item-id="<?php echo $item->id; ?>">
                                <td>
                                    <input type="text" value="<?php echo esc_attr($item->item_name); ?>" 
                                           class="regular-text" data-field="item_name">
                                </td>
                                <td>
                                    <select data-field="item_category" class="regular-text">
                                        <option value="Damages" <?php selected($item->item_category, 'Damages'); ?>>Damages</option>
                                        <option value="Legal Fees" <?php selected($item->item_category, 'Legal Fees'); ?>>Legal Fees</option>
                                        <option value="Court Fees" <?php selected($item->item_category, 'Court Fees'); ?>>Court Fees</option>
                                        <option value="Communication" <?php selected($item->item_category, 'Communication'); ?>>Communication</option>
                                        <option value="Other" <?php selected($item->item_category, 'Other'); ?>>Other</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" value="<?php echo $item->amount; ?>" 
                                           class="regular-text" data-field="amount" onchange="calculateTotals()">
                                </td>
                                <td>
                                    <input type="checkbox" <?php checked($item->is_taxable, 1); ?> 
                                           data-field="is_taxable" onchange="calculateTotals()">
                                </td>
                                <td>
                                    <input type="text" value="<?php echo esc_attr($item->description); ?>" 
                                           class="regular-text" data-field="description">
                                </td>
                                <td>
                                    <button class="button button-small button-link-delete" 
                                            onclick="deleteFinancialItem(<?php echo $item->id; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 5px;">
                        <h3>Totals</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                            <div>
                                <strong>Subtotal:</strong><br>
                                €<span id="subtotal"><?php echo number_format($totals['subtotal'], 2); ?></span>
                            </div>
                            <div>
                                <strong>Tax (19%):</strong><br>
                                €<span id="tax-amount"><?php echo number_format($totals['tax_amount'], 2); ?></span>
                            </div>
                            <div style="background: #0073aa; color: white; padding: 10px; border-radius: 5px; text-align: center;">
                                <strong>Total:</strong><br>
                                €<span id="total"><?php echo number_format($totals['total'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <button class="button button-primary button-large" onclick="saveFinancialData()">
                            💾 Save Financial Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function calculateTotals() {
            let subtotal = 0;
            let taxAmount = 0;
            
            document.querySelectorAll('#financial-items-table tbody tr').forEach(function(row) {
                const amount = parseFloat(row.querySelector('[data-field="amount"]').value) || 0;
                const isTaxable = row.querySelector('[data-field="is_taxable"]').checked;
                
                subtotal += amount;
                
                if (isTaxable) {
                    taxAmount += amount * 0.19;
                }
            });
            
            const total = subtotal + taxAmount;
            
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('tax-amount').textContent = taxAmount.toFixed(2);
            document.getElementById('total').textContent = total.toFixed(2);
        }
        
        function saveFinancialData() {
            const financialData = [];
            
            document.querySelectorAll('#financial-items-table tbody tr').forEach(function(row) {
                const itemData = {
                    item_name: row.querySelector('[data-field="item_name"]').value,
                    item_category: row.querySelector('[data-field="item_category"]').value,
                    amount: parseFloat(row.querySelector('[data-field="amount"]').value) || 0,
                    is_taxable: row.querySelector('[data-field="is_taxable"]').checked ? 1 : 0,
                    description: row.querySelector('[data-field="description"]').value,
                    display_order: Array.from(row.parentNode.children).indexOf(row)
                };
                
                financialData.push(itemData);
            });
            
            // Save via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'cah_financial_save',
                    case_id: <?php echo $case_id; ?>,
                    financial_data: JSON.stringify(financialData)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Financial data saved successfully!');
                } else {
                    alert('Error saving financial data: ' + data.data);
                }
            });
        }
        
        function addFinancialItem() {
            // TODO: Implement add financial item functionality
            alert('Add financial item functionality will be implemented');
        }
        
        function applyTemplate() {
            // TODO: Implement apply template functionality
            alert('Apply template functionality will be implemented');
        }
        
        function deleteFinancialItem(itemId) {
            if (confirm('Are you sure you want to delete this financial item?')) {
                // TODO: Implement delete functionality
                alert('Delete functionality will be implemented');
            }
        }
        </script>
        <?php
    }
}