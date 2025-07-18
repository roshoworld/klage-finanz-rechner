<?php
/**
 * Financial Admin Interface
 * Handles admin pages and interfaces for financial calculator
 */

class CAH_Financial_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'klage-click-hub',
            'Financial Calculator',
            'Financial Calculator',
            'manage_options',
            'cah-financial-calculator',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'klage-click-hub',
            'Financial Templates',
            'Financial Templates',
            'manage_options',
            'cah-financial-templates',
            array($this, 'templates_page')
        );
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('cah-financial-admin', CAH_FINANCIAL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CAH_FINANCIAL_PLUGIN_VERSION, true);
        wp_enqueue_style('cah-financial-admin', CAH_FINANCIAL_PLUGIN_URL . 'assets/css/admin.css', array(), CAH_FINANCIAL_PLUGIN_VERSION);
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🧮 Financial Calculator</h1>
            
            <div class="notice notice-success">
                <p><strong>Financial Calculator Plugin Active!</strong> The financial calculator has been successfully separated from the core plugin.</p>
            </div>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">💰 Case Financial Management</h2>
                    <div class="inside">
                        <p>This plugin provides advanced financial calculation capabilities for Court Automation Hub cases.</p>
                        
                        <h3>Features:</h3>
                        <ul>
                            <li>✅ Automatic financial calculations with MwSt</li>
                            <li>✅ Template-based cost management</li>
                            <li>✅ Case-specific financial tracking</li>
                            <li>✅ Integration with core plugin via hooks</li>
                        </ul>
                        
                        <h3>Quick Actions:</h3>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button button-primary">Manage Templates</a>
                            <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-secondary">View Cases</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function templates_page() {
        $template_manager = new CAH_Financial_Template_Manager();
        $templates = $template_manager->get_all_templates();
        
        ?>
        <div class="wrap">
            <h1>📋 Financial Templates</h1>
            
            <div class="metabox-holder">
                <div class="postbox">
                    <h2 class="hndle">Available Templates</h2>
                    <div class="inside">
                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Default</th>
                                    <th>MwSt Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                <tr>
                                    <td><?php echo esc_html($template->name); ?></td>
                                    <td><?php echo esc_html($template->description); ?></td>
                                    <td><?php echo $template->is_default ? '✅ Yes' : 'No'; ?></td>
                                    <td><?php echo $template->mwst_rate; ?>%</td>
                                    <td>
                                        <a href="#" class="button button-small">Edit</a>
                                        <?php if (!$template->is_default): ?>
                                            <a href="#" class="button button-small button-link-delete">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <p>
                            <a href="#" class="button button-primary">Add New Template</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}