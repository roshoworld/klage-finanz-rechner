<?php
/**
 * Admin Dashboard Class
 * Enhanced with Financial Calculator Integration v1.5.0
 */

if (!class_exists('CAH_Admin_Dashboard')) {
    class CAH_Admin_Dashboard {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_cah_save_case', array($this, 'ajax_save_case'));
        add_action('wp_ajax_cah_delete_case', array($this, 'ajax_delete_case'));
        add_action('wp_ajax_cah_get_case', array($this, 'ajax_get_case'));
    }
    
    public function admin_menu() {
        add_menu_page(
            'Klage Click Hub',
            'Klage Click',
            'manage_options',
            'klage-click-hub',
            array($this, 'admin_page_main'),
            'dashicons-hammer',
            30
        );
        
        add_submenu_page(
            'klage-click-hub',
            'Cases',
            'Cases',
            'manage_options',
            'klage-click-cases',
            array($this, 'admin_page_cases')
        );
        
        add_submenu_page(
            'klage-click-hub',
            'Debtors',
            'Debtors',
            'manage_options',
            'klage-click-debtors',
            array($this, 'admin_page_debtors')
        );
    }
    
    public function enqueue_admin_scripts() {
        wp_enqueue_script('cah-admin-dashboard', CAH_PLUGIN_URL . 'assets/js/admin-dashboard.js', array('jquery'), CAH_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-admin-dashboard', CAH_PLUGIN_URL . 'assets/css/admin-dashboard.css', array(), CAH_PLUGIN_VERSION);
        
        wp_localize_script('cah-admin-dashboard', 'cah_dashboard_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cah_dashboard_nonce')
        ));
    }
    
    public function admin_page_main() {
        ?>
        <div class="wrap">
            <h1>🏛️ Klage Click Hub v1.4.9</h1>
            
            <div class="notice notice-success">
                <p><strong>Core Plugin Ready!</strong> Financial Calculator integration enabled for case management.</p>
            </div>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">Quick Actions</h2>
                    <div class="inside">
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-primary button-large">Manage Cases</a>
                            <a href="<?php echo admin_url('admin.php?page=klage-click-debtors'); ?>" class="button button-secondary">Manage Debtors</a>
                        </p>
                    </div>
                </div>
                
                <!-- Financial Calculator moved to separate plugin -->
                <div class="postbox">
                    <h2 class="hndle">💰 Financial Calculator Integration</h2>
                    <div class="inside">
                        <div class="notice notice-info">
                            <p><strong>🧮 Financial Calculator Plugin Required</strong><br>
                            Install and activate the "Court Automation Hub - Financial Calculator" plugin to enable financial management in case workflows.</p>
                        </div>
                        
                        <?php if (class_exists('CAH_Case_Financial_Integration')): ?>
                            <div class="notice notice-success">
                                <p><strong>✅ Financial Calculator Plugin Active!</strong> Financial tabs are now available in case management.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function admin_page_cases() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        $case_id = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;
        
        switch ($action) {
            case 'edit':
                $this->edit_case_page($case_id);
                break;
            case 'add':
                $this->add_case_page();
                break;
            default:
                $this->list_cases_page();
                break;
        }
    }
    
    private function list_cases_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'klage_cases';
        $cases = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50");
        
        ?>
        <div class="wrap">
            <h1>📋 Cases Management</h1>
            
            <p>
                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=add'); ?>" class="button button-primary">Add New Case</a>
            </p>
            
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Case Number</th>
                        <th>Debtor</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cases): ?>
                        <?php foreach ($cases as $case): ?>
                        <tr>
                            <td><?php echo $case->id; ?></td>
                            <td><?php echo esc_html($case->case_number ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($case->debtor_name ?? 'N/A'); ?></td>
                            <td>€<?php echo number_format($case->amount ?? 0, 2, ',', '.'); ?></td>
                            <td><?php echo esc_html($case->status ?? 'Open'); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($case->created_at)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=klage-click-cases&action=edit&case_id=' . $case->id); ?>" class="button button-small">Edit</a>
                                <a href="#" class="button button-small button-link-delete" onclick="deleteCase(<?php echo $case->id; ?>)">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No cases found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    private function add_case_page() {
        ?>
        <div class="wrap">
            <h1>📋 Add New Case</h1>
            
            <form id="case-form" method="post">
                <?php wp_nonce_field('save_case', 'case_nonce'); ?>
                
                <div id="case-tabs">
                    <ul class="nav-tab-wrapper">
                        <li><a href="#basic-info" class="nav-tab nav-tab-active">Basic Info</a></li>
                        <?php if (class_exists('CAH_Case_Financial_Integration')): ?>
                            <li><a href="#financial" class="nav-tab">💰 Financial</a></li>
                        <?php endif; ?>
                    </ul>
                    
                    <div id="basic-info" class="tab-content active">
                        <h3>Case Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Case Number</th>
                                <td><input type="text" name="case_number" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Debtor Name</th>
                                <td><input type="text" name="debtor_name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Amount (€)</th>
                                <td><input type="number" name="amount" step="0.01" min="0" class="regular-text"></td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <select name="status" class="regular-text">
                                        <option value="Open">Open</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Closed">Closed</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Description</th>
                                <td><textarea name="description" class="large-text" rows="5"></textarea></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (class_exists('CAH_Case_Financial_Integration')): ?>
                    <div id="financial" class="tab-content">
                        <?php
                        $integration = new CAH_Case_Financial_Integration();
                        $integration->render_case_financial_tab(0); // 0 for new case
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Save Case">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function edit_case_page($case_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'klage_cases';
        $case = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $case_id));
        
        if (!$case) {
            echo '<div class="notice notice-error"><p>Case not found.</p></div>';
            $this->list_cases_page();
            return;
        }
        
        ?>
        <div class="wrap">
            <h1>📋 Edit Case #<?php echo esc_html($case->case_number); ?></h1>
            
            <form id="case-form" method="post">
                <?php wp_nonce_field('save_case', 'case_nonce'); ?>
                <input type="hidden" name="case_id" value="<?php echo $case->id; ?>">
                
                <div id="case-tabs">
                    <ul class="nav-tab-wrapper">
                        <li><a href="#basic-info" class="nav-tab nav-tab-active">Basic Info</a></li>
                        <?php if (class_exists('CAH_Case_Financial_Integration')): ?>
                            <li><a href="#financial" class="nav-tab">💰 Financial</a></li>
                        <?php endif; ?>
                    </ul>
                    
                    <div id="basic-info" class="tab-content active">
                        <h3>Case Information</h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Case Number</th>
                                <td><input type="text" name="case_number" class="regular-text" value="<?php echo esc_attr($case->case_number); ?>" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Debtor Name</th>
                                <td><input type="text" name="debtor_name" class="regular-text" value="<?php echo esc_attr($case->debtor_name); ?>" required></td>
                            </tr>
                            <tr>
                                <th scope="row">Amount (€)</th>
                                <td><input type="number" name="amount" step="0.01" min="0" class="regular-text" value="<?php echo esc_attr($case->amount); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <select name="status" class="regular-text">
                                        <option value="Open" <?php selected($case->status, 'Open'); ?>>Open</option>
                                        <option value="In Progress" <?php selected($case->status, 'In Progress'); ?>>In Progress</option>
                                        <option value="Closed" <?php selected($case->status, 'Closed'); ?>>Closed</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Description</th>
                                <td><textarea name="description" class="large-text" rows="5"><?php echo esc_textarea($case->description); ?></textarea></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (class_exists('CAH_Case_Financial_Integration')): ?>
                    <div id="financial" class="tab-content">
                        <?php
                        $integration = new CAH_Case_Financial_Integration();
                        $integration->render_case_financial_tab($case->id);
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button button-primary" value="Update Case">
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    public function admin_page_debtors() {
        ?>
        <div class="wrap">
            <h1>👤 Debtors Management</h1>
            <p>Debtor management functionality - simplified for v1.4.9</p>
        </div>
        <?php
    }
    
    // AJAX handlers
    public function ajax_save_case() {
        check_ajax_referer('cah_dashboard_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'klage_cases';
        
        $case_data = array(
            'case_number' => sanitize_text_field($_POST['case_number']),
            'debtor_name' => sanitize_text_field($_POST['debtor_name']),
            'amount' => floatval($_POST['amount']),
            'status' => sanitize_text_field($_POST['status']),
            'description' => sanitize_textarea_field($_POST['description'])
        );
        
        if (isset($_POST['case_id']) && $_POST['case_id'] > 0) {
            // Update existing case
            $case_id = intval($_POST['case_id']);
            $wpdb->update($table_name, $case_data, array('id' => $case_id));
            
            // Fire hook for financial plugin
            do_action('cah_case_updated', $case_id);
            
            wp_send_json_success(array('message' => 'Case updated successfully', 'case_id' => $case_id));
        } else {
            // Create new case
            $case_data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $case_data);
            $case_id = $wpdb->insert_id;
            
            // Fire hook for financial plugin
            do_action('cah_case_created', $case_id);
            
            wp_send_json_success(array('message' => 'Case created successfully', 'case_id' => $case_id));
        }
    }
    
    public function ajax_delete_case() {
        check_ajax_referer('cah_dashboard_nonce', 'nonce');
        
        $case_id = intval($_POST['case_id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'klage_cases';
        $wpdb->delete($table_name, array('id' => $case_id));
        
        // Fire hook for financial plugin
        do_action('cah_case_deleted', $case_id);
        
        wp_send_json_success(array('message' => 'Case deleted successfully'));
    }
}
} // End class_exists check