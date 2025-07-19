/**
 * Case Financial Integration JavaScript
 */

jQuery(document).ready(function($) {
    let currentCaseId = null;
    let costItems = [];
    let currentTemplate = null;
    
    // Initialize when financial tab is clicked
    $(document).on('click', 'a[href="#financial"]', function() {
        initializeFinancialTab();
    });
    
    function initializeFinancialTab() {
        if ($('#case-financial-integration').is(':empty')) {
            $('#case-financial-integration').html($('#financial-tab-template').html());
            setupEventHandlers();
            loadTemplateOptions();
            loadExistingCaseData();
        }
    }
    
    function setupEventHandlers() {
        // Template selection
        $(document).on('change', '#financial-template-select', function() {
            const templateId = $(this).val();
            if (templateId) {
                $('#load-template-btn').prop('disabled', false);
            } else {
                $('#load-template-btn').prop('disabled', true);
            }
        });
        
        // Load template button
        $(document).on('click', '#load-template-btn', function() {
            const templateId = $('#financial-template-select').val();
            if (templateId) {
                loadTemplateItems(templateId);
            }
        });
        
        // Add cost item button
        $(document).on('click', '#add-cost-item-btn', function() {
            showCostItemModal();
        });
        
        // Cost item form submission
        $(document).on('submit', '#cost-item-form', function(e) {
            e.preventDefault();
            saveCostItem();
        });
        
        // Cancel cost item
        $(document).on('click', '#cancel-item-btn', function() {
            hideCostItemModal();
        });
        
        // Edit cost item
        $(document).on('click', '.edit-cost-item', function() {
            const itemIndex = $(this).data('index');
            editCostItem(itemIndex);
        });
        
        // Delete cost item
        $(document).on('click', '.delete-cost-item', function() {
            const itemIndex = $(this).data('index');
            if (confirm(cah_case_financial.strings.confirm_delete)) {
                deleteCostItem(itemIndex);
            }
        });
        
        // Save case financial
        $(document).on('click', '#save-case-financial-btn', function() {
            saveCaseFinancial();
        });
        
        // Save as template
        $(document).on('click', '#save-as-template-btn', function() {
            showSaveAsTemplateDialog();
        });
        
        // Recalculate
        $(document).on('click', '#recalculate-btn', function() {
            recalculateTotals();
        });
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
                    const select = $('#financial-template-select');
                    select.find('option:not(:first)').remove();
                    
                    $.each(response.data, function(i, template) {
                        select.append($('<option>', {
                            value: template.id,
                            text: template.name + (template.description ? ' - ' + template.description : '')
                        }));
                    });
                } else {
                    showNotice('Fehler beim Laden der Vorlagen', 'error');
                }
            },
            error: function() {
                showNotice('Fehler beim Laden der Vorlagen', 'error');
            }
        });
    }
    
    function loadTemplateItems(templateId) {
        showLoading();
        
        $.ajax({
            url: cah_case_financial.ajax_url,
            type: 'POST',
            data: {
                action: 'load_template_items',
                template_id: templateId,
                nonce: cah_case_financial.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    costItems = response.data.items || [];
                    currentTemplate = templateId;
                    renderCostItems();
                    updateCalculation(response.data.totals);
                    showNotice('Vorlage erfolgreich geladen', 'success');
                } else {
                    showNotice('Fehler beim Laden der Vorlage', 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotice('Fehler beim Laden der Vorlage', 'error');
            }
        });
    }
    
    function loadExistingCaseData() {
        // Try to get case ID from form or URL
        currentCaseId = $('input[name="case_id"]').val() || getUrlParameter('id');
        
        if (currentCaseId && currentCaseId !== 'new') {
            // Load existing case financial data
            // This would be implemented to load saved financial data for existing cases
        }
    }
    
    function renderCostItems() {
        const tbody = $('#cost-items-tbody');
        tbody.empty();
        
        if (costItems.length === 0) {
            tbody.append('<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">Keine Kostenpunkte vorhanden. Fügen Sie einen Kostenpunkt hinzu oder laden Sie eine Vorlage.</td></tr>');
            return;
        }
        
        $.each(costItems, function(index, item) {
            const row = $('<tr>').html(
                '<td><strong>' + escapeHtml(item.name) + '</strong></td>' +
                '<td><span class="category-badge category-' + item.category + '">' + getCategoryName(item.category) + '</span></td>' +
                '<td><strong>' + formatCurrency(item.amount) + '</strong></td>' +
                '<td>' + escapeHtml(item.description || '-') + '</td>' +
                '<td>' +
                    '<button type="button" class="button button-small edit-cost-item" data-index="' + index + '">✏️</button> ' +
                    '<button type="button" class="button button-small button-link-delete delete-cost-item" data-index="' + index + '">🗑️</button>' +
                '</td>'
            );
            tbody.append(row);
        });
    }
    
    function showCostItemModal(editIndex = null) {
        const modal = $('#cost-item-modal');
        const form = $('#cost-item-form')[0];
        
        // Reset form
        form.reset();
        
        if (editIndex !== null) {
            // Edit mode
            const item = costItems[editIndex];
            $('#modal-title').text('Kostenpunkt bearbeiten');
            $('#cost-item-id').val(editIndex);
            $('#item-name').val(item.name);
            $('#item-category').val(item.category);
            $('#item-amount').val(item.amount);
            $('#item-description').val(item.description || '');
        } else {
            // Add mode
            $('#modal-title').text('Kostenpunkt hinzufügen');
            $('#cost-item-id').val('');
        }
        
        modal.show();
    }
    
    function hideCostItemModal() {
        $('#cost-item-modal').hide();
    }
    
    function saveCostItem() {
        const form = $('#cost-item-form')[0];
        const editIndex = $('#cost-item-id').val();
        
        const item = {
            name: $('#item-name').val(),
            category: $('#item-category').val(),
            amount: parseFloat($('#item-amount').val()),
            description: $('#item-description').val(),
            is_percentage: false,
            sort_order: costItems.length
        };
        
        // Validate
        if (!item.name || !item.category || isNaN(item.amount)) {
            showNotice('Bitte füllen Sie alle erforderlichen Felder aus', 'error');
            return;
        }
        
        if (editIndex !== '') {
            // Edit existing item
            costItems[parseInt(editIndex)] = item;
        } else {
            // Add new item
            costItems.push(item);
        }
        
        renderCostItems();
        recalculateTotals();
        hideCostItemModal();
        
        showNotice('Kostenpunkt ' + (editIndex !== '' ? 'aktualisiert' : 'hinzugefügt'), 'success');
    }
    
    function editCostItem(index) {
        showCostItemModal(index);
    }
    
    function deleteCostItem(index) {
        costItems.splice(index, 1);
        renderCostItems();
        recalculateTotals();
        showNotice('Kostenpunkt gelöscht', 'success');
    }
    
    function recalculateTotals() {
        if (costItems.length === 0) {
            updateCalculation({ subtotal: 0, vat_amount: 0, total_amount: 0, vat_rate: 19.00 });
            return;
        }
        
        $.ajax({
            url: cah_case_financial.ajax_url,
            type: 'POST',
            data: {
                action: 'calculate_financial_totals',
                items: JSON.stringify(costItems),
                nonce: cah_case_financial.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateCalculation(response.data);
                }
            }
        });
    }
    
    function updateCalculation(totals) {
        $('#calc-subtotal').text(formatCurrency(totals.subtotal));
        $('#calc-vat').text(formatCurrency(totals.vat_amount));
        $('#calc-total').text(formatCurrency(totals.total_amount));
    }
    
    function saveCaseFinancial() {
        if (!currentCaseId) {
            showNotice('Fall muss zuerst gespeichert werden', 'error');
            return;
        }
        
        const totals = {
            subtotal: parseFloat($('#calc-subtotal').text().replace(/[€\s,]/g, '').replace('.', '')/100),
            vat_rate: 19.00,
            vat_amount: parseFloat($('#calc-vat').text().replace(/[€\s,]/g, '').replace('.', '')/100),
            total_amount: parseFloat($('#calc-total').text().replace(/[€\s,]/g, '').replace('.', '')/100)
        };
        
        showLoading();
        
        $.ajax({
            url: cah_case_financial.ajax_url,
            type: 'POST',
            data: {
                action: 'save_case_financial',
                case_id: currentCaseId,
                template_id: currentTemplate,
                items: JSON.stringify(costItems),
                totals: JSON.stringify(totals),
                nonce: cah_case_financial.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showNotice(cah_case_financial.strings.save_success, 'success');
                } else {
                    showNotice(cah_case_financial.strings.save_error, 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotice(cah_case_financial.strings.save_error, 'error');
            }
        });
    }
    
    function showSaveAsTemplateDialog() {
        const templateName = prompt('Name für neue Vorlage:', '');
        if (templateName) {
            const templateDescription = prompt('Beschreibung (optional):', '');
            
            saveAsTemplate(templateName, templateDescription);
        }
    }
    
    function saveAsTemplate(name, description) {
        if (!currentCaseId) {
            showNotice('Fall muss zuerst gespeichert werden', 'error');
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: cah_case_financial.ajax_url,
            type: 'POST',
            data: {
                action: 'save_financial_as_template',
                case_id: currentCaseId,
                template_name: name,
                template_description: description,
                nonce: cah_case_financial.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showNotice('Vorlage erfolgreich erstellt', 'success');
                    loadTemplateOptions(); // Refresh template list
                } else {
                    showNotice('Fehler beim Erstellen der Vorlage', 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotice('Fehler beim Erstellen der Vorlage', 'error');
            }
        });
    }
    
    // Utility functions
    function formatCurrency(amount) {
        return cah_case_financial.currency_symbol + ' ' + parseFloat(amount).toFixed(2).replace('.', ',');
    }
    
    function getCategoryName(category) {
        const names = {
            'grundkosten': 'Grundkosten',
            'gerichtskosten': 'Gerichtskosten',
            'anwaltskosten': 'Anwaltskosten',
            'sonstige': 'Sonstige'
        };
        return names[category] || category;
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.toString().replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }
    
    function showNotice(message, type) {
        const noticeClass = 'notice-' + type;
        const notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    function showLoading() {
        // Simple loading implementation
        if (!$('#cah-loading').length) {
            $('body').append('<div id="cah-loading" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.8); color: white; padding: 20px; border-radius: 5px; z-index: 9999;">' + cah_case_financial.strings.loading + '</div>');
        }
    }
    
    function hideLoading() {
        $('#cah-loading').remove();
    }
    
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }
});