<?php
/**
 * Financial Calculator Admin Interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAH_Financial_Admin {
    
    private $db_manager;
    private $calculator;
    private $template_manager;
    
    public function __construct() {
        $this->db_manager = new CAH_Financial_DB_Manager();
        $this->calculator = new CAH_Financial_Calculator_Engine();
        $this->template_manager = new CAH_Financial_Template_Manager();
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    public function add_admin_menu() {
        // Add financial calculator to the core hub menu
        add_submenu_page(
            'klage-click-hub',
            __('Finanz-Vorlagen', 'court-automation-hub-financial'),
            __('Finanz-Vorlagen', 'court-automation-hub-financial'),
            'manage_options',
            'cah-financial-templates',
            array($this, 'templates_page')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Kosten Items', 'court-automation-hub-financial'),
            __('Kosten Items', 'court-automation-hub-financial'),
            'manage_options',
            'cah-cost-items',
            array($this, 'cost_items_page')
        );
        
        add_submenu_page(
            'klage-click-hub',
            __('Finanzrechner', 'court-automation-hub-financial'),
            __('🧮 Finanzrechner', 'court-automation-hub-financial'),
            'manage_options',
            'cah-financial-calculator',
            array($this, 'calculator_page')
        );
    }
    
    public function handle_admin_actions() {
        if (!isset($_POST['financial_action'])) {
            return;
        }
        
        $action = sanitize_text_field($_POST['financial_action']);
        
        switch ($action) {
            case 'create_template':
                $this->handle_create_template();
                break;
            case 'update_template':
                $this->handle_update_template();
                break;
            case 'delete_template':
                $this->handle_delete_template();
                break;
            case 'create_cost_item':
                $this->handle_create_cost_item();
                break;
            case 'update_cost_item':
                $this->handle_update_cost_item();
                break;
            case 'delete_cost_item':
                $this->handle_delete_cost_item();
                break;
        }
    }
    
    public function templates_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $template_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        switch ($action) {
            case 'add':
                $this->render_add_template_form();
                break;
            case 'edit':
                $this->render_edit_template_form($template_id);
                break;
            case 'view':
                $this->render_view_template($template_id);
                break;
            default:
                $this->render_templates_list();
                break;
        }
    }
    
    public function cost_items_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;
        
        switch ($action) {
            case 'add':
                $this->render_add_cost_item_form($template_id);
                break;
            case 'edit':
                $this->render_edit_cost_item_form($item_id);
                break;
            default:
                $this->render_cost_items_list($template_id);
                break;
        }
    }
    
    public function calculator_page() {
        ?>
        <div class="wrap">
            <h1>🧮 Finanzrechner</h1>
            
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <p><strong>💰 Financial Calculator v1.0.5</strong></p>
                <p>Verwenden Sie den Finanzrechner direkt in der Fall-Bearbeitung oder verwalten Sie Ihre Vorlagen hier.</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">📋 Vorlagen verwalten</h3>
                    <p>Erstellen und bearbeiten Sie Finanz-Vorlagen für verschiedene Fall-Typen</p>
                    <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button button-primary">Vorlagen öffnen</a>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">💰 Kostenpunkte</h3>
                    <p>Verwalten Sie einzelne Kostenpunkte und deren Kategorien</p>
                    <a href="<?php echo admin_url('admin.php?page=cah-cost-items'); ?>" class="button button-primary">Kosten verwalten</a>
                </div>
                
                <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <h3 style="color: #0073aa;">📝 Fall-Integration</h3>
                    <p>Nutzen Sie den Finanzrechner direkt beim Erstellen oder Bearbeiten von Fällen</p>
                    <a href="<?php echo admin_url('admin.php?page=klage-click-cases'); ?>" class="button button-primary">Fälle öffnen</a>
                </div>
            </div>
            
            <div class="postbox">
                <h2 class="hndle">📊 Übersicht</h2>
                <div class="inside" style="padding: 20px;">
                    <?php $this->render_calculator_dashboard(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_calculator_dashboard() {
        $templates = $this->db_manager->get_templates();
        $total_templates = count($templates);
        $default_templates = count(array_filter($templates, function($t) { return $t->is_default; }));
        
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #0073aa; font-size: 24px;"><?php echo $total_templates; ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;">Gesamt Vorlagen</p>
            </div>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #ff9800; font-size: 24px;"><?php echo $default_templates; ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;">Standard Vorlagen</p>
            </div>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center;">
                <h3 style="margin: 0; color: #4caf50; font-size: 24px;"><?php echo ($total_templates - $default_templates); ?></h3>
                <p style="margin: 5px 0 0 0; color: #666;">Benutzerdefiniert</p>
            </div>
        </div>
        
        <?php if (!empty($templates)): ?>
        <h4 style="margin-top: 25px;">Zuletzt verwendete Vorlagen</h4>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>Vorlage</th>
                    <th>Typ</th>
                    <th>Erstellt</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($templates, 0, 5) as $template): ?>
                <tr>
                    <td><strong><?php echo esc_html($template->name); ?></strong></td>
                    <td>
                        <?php if ($template->is_default): ?>
                            <span style="background: #4caf50; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">Standard</span>
                        <?php else: ?>
                            <span style="background: #2196f3; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">Benutzer</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date_i18n('d.m.Y', strtotime($template->created_at)); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=view&id=' . $template->id); ?>" class="button button-small">Ansehen</a>
                        <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=edit&id=' . $template->id); ?>" class="button button-small">Bearbeiten</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        <?php
    }
    
    private function render_templates_list() {
        $templates = $this->db_manager->get_templates();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Finanz-Vorlagen</h1>
            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=add'); ?>" class="page-title-action">Neue Vorlage hinzufügen</a>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Beschreibung</th>
                        <th>Typ</th>
                        <th>Erstellt</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($templates)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <p>Keine Vorlagen gefunden.</p>
                            <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=add'); ?>" class="button button-primary">Erste Vorlage erstellen</a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $template): ?>
                        <tr>
                            <td><strong><?php echo esc_html($template->name); ?></strong></td>
                            <td><?php echo esc_html($template->description); ?></td>
                            <td>
                                <?php if ($template->is_default): ?>
                                    <span style="background: #4caf50; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Standard</span>
                                <?php else: ?>
                                    <span style="background: #2196f3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Benutzerdefiniert</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date_i18n('d.m.Y H:i', strtotime($template->created_at)); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=view&id=' . $template->id); ?>" class="button button-small">👁️</a>
                                <a href="<?php echo admin_url('admin.php?page=cah-financial-templates&action=edit&id=' . $template->id); ?>" class="button button-small">✏️</a>
                                <a href="<?php echo admin_url('admin.php?page=cah-cost-items&template_id=' . $template->id); ?>" class="button button-small">💰</a>
                                <?php if (!$template->is_default): ?>
                                <a href="#" onclick="confirmDelete(<?php echo $template->id; ?>)" class="button button-small button-link-delete">🗑️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <script>
        function confirmDelete(templateId) {
            if (confirm('Sind Sie sicher, dass Sie diese Vorlage löschen möchten?')) {
                var form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = '<input type="hidden" name="financial_action" value="delete_template">' +
                               '<input type="hidden" name="template_id" value="' + templateId + '">' +
                               '<?php wp_nonce_field('financial_action', 'financial_nonce'); ?>';
                document.body.appendChild(form);
                form.submit();
            }
        }
        </script>
        <?php
    }
    
    private function render_add_template_form() {
        ?>
        <div class="wrap">
            <h1>Neue Finanz-Vorlage erstellen</h1>
            
            <form method="post">
                <?php wp_nonce_field('financial_action', 'financial_nonce'); ?>
                <input type="hidden" name="financial_action" value="create_template">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="template_name">Name</label></th>
                        <td><input type="text" id="template_name" name="template_name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="template_description">Beschreibung</label></th>
                        <td><textarea id="template_description" name="template_description" class="large-text" rows="4"></textarea></td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button button-primary" value="Vorlage erstellen">
                    <a href="<?php echo admin_url('admin.php?page=cah-financial-templates'); ?>" class="button button-secondary">Abbrechen</a>
                </p>
            </form>
        </div>
        <?php
    }
    
    private function render_cost_items_list($template_id = 0) {
        $templates = $this->db_manager->get_templates();
        $selected_template = null;
        $cost_items = array();
        
        if ($template_id) {
            $selected_template = $this->db_manager->get_template($template_id);
            $cost_items = $this->db_manager->get_cost_items_by_template($template_id);
        }
        
        ?>
        <div class="wrap">
            <h1>Kostenpunkte verwalten</h1>
            
            <!-- Template Selection -->
            <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <form method="get" style="display: flex; gap: 15px; align-items: end;">
                    <input type="hidden" name="page" value="cah-cost-items">
                    
                    <div>
                        <label for="template_id" style="display: block; margin-bottom: 5px; font-weight: bold;">Vorlage auswählen:</label>
                        <select name="template_id" id="template_id" style="width: 300px;">
                            <option value="">Bitte wählen...</option>
                            <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template->id; ?>" <?php selected($template_id, $template->id); ?>>
                                <?php echo esc_html($template->name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <input type="submit" class="button" value="Vorlage laden">
                    </div>
                </form>
            </div>
            
            <?php if ($selected_template): ?>
            <div style="background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #0073aa;">
                <h3 style="margin: 0 0 5px 0;">📋 <?php echo esc_html($selected_template->name); ?></h3>
                <p style="margin: 0;"><?php echo esc_html($selected_template->description); ?></p>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Kostenpunkte</h2>
                <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=add&template_id=' . $template_id); ?>" class="button button-primary">+ Neuen Kostenpunkt hinzufügen</a>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Kategorie</th>
                        <th>Betrag</th>
                        <th>Beschreibung</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cost_items)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <p>Keine Kostenpunkte für diese Vorlage gefunden.</p>
                            <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=add&template_id=' . $template_id); ?>" class="button button-primary">Ersten Kostenpunkt hinzufügen</a>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $total = 0;
                        foreach ($cost_items as $item): 
                            $total += $item->amount;
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($item->name); ?></strong></td>
                            <td>
                                <span class="category-badge category-<?php echo esc_attr($item->category); ?>">
                                    <?php echo esc_html($this->calculator->get_category_names()[$item->category] ?? $item->category); ?>
                                </span>
                            </td>
                            <td><strong><?php echo $this->calculator->format_currency($item->amount); ?></strong></td>
                            <td><?php echo esc_html($item->description ?: '-'); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=cah-cost-items&action=edit&id=' . $item->id); ?>" class="button button-small">✏️</a>
                                <a href="#" onclick="confirmDeleteItem(<?php echo $item->id; ?>)" class="button button-small button-link-delete">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f0f8ff;">
                            <th colspan="2"><strong>Zwischensumme:</strong></th>
                            <th><strong><?php echo $this->calculator->format_currency($total); ?></strong></th>
                            <th colspan="2"><em>zzgl. 19% MwSt. = <?php echo $this->calculator->format_currency($total * 1.19); ?></em></th>
                        </tr>
                    </tfoot>
                </table>
            <?php else: ?>
            <div style="text-align: center; padding: 60px; background: #f9f9f9; border-radius: 5px;">
                <p style="font-size: 18px; color: #666;">Wählen Sie eine Vorlage aus, um deren Kostenpunkte zu verwalten.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .category-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .category-grundkosten { background: #4caf50; }
        .category-gerichtskosten { background: #ff9800; }
        .category-anwaltskosten { background: #2196f3; }
        .category-sonstige { background: #9c27b0; }
        </style>
        
        <script>
        function confirmDeleteItem(itemId) {
            if (confirm('Sind Sie sicher, dass Sie diesen Kostenpunkt löschen möchten?')) {
                var form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = '<input type="hidden" name="financial_action" value="delete_cost_item">' +
                               '<input type="hidden" name="item_id" value="' + itemId + '">' +
                               '<?php wp_nonce_field('financial_action', 'financial_nonce'); ?>';
                document.body.appendChild(form);
                form.submit();
            }
        }
        </script>
        <?php
    }
    
    // Handler methods
    private function handle_create_template() {
        if (!wp_verify_nonce($_POST['financial_nonce'], 'financial_action')) {
            wp_die('Security check failed');
        }
        
        $name = sanitize_text_field($_POST['template_name']);
        $description = sanitize_textarea_field($_POST['template_description']);
        
        if (empty($name)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Template-Name ist erforderlich.</p></div>';
            });
            return;
        }
        
        $result = $this->db_manager->create_template($name, $description, false);
        
        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Vorlage erfolgreich erstellt.</p></div>';
            });
            
            // Redirect to avoid form resubmission
            wp_redirect(admin_url('admin.php?page=cah-financial-templates'));
            exit;
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Fehler beim Erstellen der Vorlage.</p></div>';
            });
        }
    }
    
    private function handle_delete_template() {
        if (!wp_verify_nonce($_POST['financial_nonce'], 'financial_action')) {
            wp_die('Security check failed');
        }
        
        $template_id = intval($_POST['template_id']);
        $template = $this->db_manager->get_template($template_id);
        
        if (!$template) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Vorlage nicht gefunden.</p></div>';
            });
            return;
        }
        
        if ($template->is_default) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Standard-Vorlagen können nicht gelöscht werden.</p></div>';
            });
            return;
        }
        
        $result = $this->db_manager->delete_template($template_id);
        
        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Vorlage erfolgreich gelöscht.</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Fehler beim Löschen der Vorlage.</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=cah-financial-templates'));
        exit;
    }
}