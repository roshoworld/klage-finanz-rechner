/**
 * Financial Calculator Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Enhanced form validation
    $('form').on('submit', function(e) {
        var isValid = true;
        var requiredFields = $(this).find('[required]');
        
        requiredFields.each(function() {
            var $field = $(this);
            var value = $field.val().trim();
            
            if (value === '') {
                $field.addClass('error');
                showFieldError($field, 'Dieses Feld ist erforderlich');
                isValid = false;
            } else {
                $field.removeClass('error');
                hideFieldError($field);
            }
            
            // Specific validation for amount fields
            if ($field.attr('type') === 'number' && value !== '' && parseFloat(value) < 0) {
                $field.addClass('error');
                showFieldError($field, 'Betrag muss positiv sein');
                isValid = false;
            }
        });
        
        // Email validation
        $(this).find('input[type="email"]').each(function() {
            var $email = $(this);
            var email = $email.val().trim();
            
            if (email && !isValidEmail(email)) {
                $email.addClass('error');
                showFieldError($email, 'Bitte geben Sie eine gültige E-Mail-Adresse ein');
                isValid = false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotice('Bitte korrigieren Sie die Fehler im Formular', 'error');
            
            // Scroll to first error
            var firstError = $('.error').first();
            if (firstError.length) {
                $('html, body').animate({
                    scrollTop: firstError.offset().top - 100
                }, 500);
                firstError.focus();
            }
        }
    });
    
    // Real-time validation
    $('input[required], select[required], textarea[required]').on('blur', function() {
        var $field = $(this);
        var value = $field.val().trim();
        
        if (value === '') {
            $field.addClass('error');
            showFieldError($field, 'Dieses Feld ist erforderlich');
        } else {
            $field.removeClass('error');
            hideFieldError($field);
        }
    });
    
    $('input[type="number"]').on('blur', function() {
        var $field = $(this);
        var value = parseFloat($field.val());
        
        if (!isNaN(value) && value < 0) {
            $field.addClass('error');
            showFieldError($field, 'Betrag muss positiv sein');
        }
    });
    
    $('input[type="email"]').on('blur', function() {
        var $email = $(this);
        var email = $email.val().trim();
        
        if (email && !isValidEmail(email)) {
            $email.addClass('error');
            showFieldError($email, 'Bitte geben Sie eine gültige E-Mail-Adresse ein');
        } else {
            $email.removeClass('error');
            hideFieldError($email);
        }
    });
    
    // Auto-format currency inputs
    $('input[name*="amount"], input[name*="betrag"]').on('input', function() {
        var value = $(this).val();
        // Remove all non-digit and non-decimal characters
        value = value.replace(/[^\d.,]/g, '');
        // Replace comma with period for proper decimal handling
        value = value.replace(',', '.');
        $(this).val(value);
    });
    
    // Template management
    $('.duplicate-template').on('click', function(e) {
        e.preventDefault();
        var templateId = $(this).data('template-id');
        var templateName = $(this).data('template-name');
        
        var newName = prompt('Name für die Kopie:', templateName + ' (Kopie)');
        if (newName) {
            duplicateTemplate(templateId, newName);
        }
    });
    
    // Cost item sorting (if sortable is available)
    if ($.fn.sortable) {
        $('#cost-items-tbody').sortable({
            handle: '.sort-handle',
            update: function(event, ui) {
                updateSortOrder();
            }
        });
    }
    
    // Quick calculation
    $('.quick-calc').on('click', function() {
        var items = [];
        $('#cost-items-tbody tr').each(function() {
            var $row = $(this);
            var amount = parseFloat($row.find('.item-amount').text().replace(/[^\d.,]/g, '').replace(',', '.'));
            if (!isNaN(amount)) {
                items.push({ amount: amount });
            }
        });
        
        if (items.length > 0) {
            calculateQuickTotal(items);
        }
    });
    
    // Utility Functions
    function showFieldError($field, message) {
        var fieldId = $field.attr('id');
        var $error = $('#' + fieldId + '_error');
        
        if ($error.length === 0) {
            $error = $('<div class="field-error" id="' + fieldId + '_error" style="color: #dc3232; font-size: 13px; margin-top: 5px;"></div>');
            $field.after($error);
        }
        
        $error.text(message);
    }
    
    function hideFieldError($field) {
        var fieldId = $field.attr('id');
        $('#' + fieldId + '_error').remove();
    }
    
    function showNotice(message, type) {
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button></div>');
        
        $('.wrap h1').after(notice);
        
        // Handle dismiss button
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut();
        });
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function duplicateTemplate(templateId, newName) {
        $.ajax({
            url: cah_financial_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'duplicate_financial_template',
                template_id: templateId,
                new_name: newName,
                nonce: cah_financial_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Vorlage erfolgreich dupliziert', 'success');
                    location.reload();
                } else {
                    showNotice('Fehler beim Duplizieren der Vorlage', 'error');
                }
            },
            error: function() {
                showNotice('Fehler beim Duplizieren der Vorlage', 'error');
            }
        });
    }
    
    function updateSortOrder() {
        var sortOrder = [];
        $('#cost-items-tbody tr').each(function(index) {
            var itemId = $(this).data('item-id');
            if (itemId) {
                sortOrder.push({ id: itemId, order: index });
            }
        });
        
        if (sortOrder.length > 0) {
            $.ajax({
                url: cah_financial_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cost_items_order',
                    items: JSON.stringify(sortOrder),
                    nonce: cah_financial_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotice('Reihenfolge gespeichert', 'success');
                    }
                }
            });
        }
    }
    
    function calculateQuickTotal(items) {
        var subtotal = 0;
        items.forEach(function(item) {
            subtotal += item.amount;
        });
        
        var vat = subtotal * 0.19;
        var total = subtotal + vat;
        
        var resultHtml = '<div class="quick-calc-result" style="background: #f0f8ff; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #0073aa;">' +
                        '<h4 style="margin: 0 0 10px 0;">Schnellberechnung:</h4>' +
                        '<div style="display: flex; justify-content: space-between;"><span>Zwischensumme:</span><span>€ ' + subtotal.toFixed(2).replace('.', ',') + '</span></div>' +
                        '<div style="display: flex; justify-content: space-between;"><span>MwSt. (19%):</span><span>€ ' + vat.toFixed(2).replace('.', ',') + '</span></div>' +
                        '<div style="display: flex; justify-content: space-between; border-top: 2px solid #0073aa; padding-top: 5px; margin-top: 5px; font-weight: bold;"><span>Gesamtsumme:</span><span>€ ' + total.toFixed(2).replace('.', ',') + '</span></div>' +
                        '</div>';
        
        $('.quick-calc-result').remove();
        $('.quick-calc').after(resultHtml);
    }
    
    // Enhanced table interactions
    $('.wp-list-table tbody tr').hover(
        function() {
            $(this).addClass('hover');
        },
        function() {
            $(this).removeClass('hover');
        }
    );
    
    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl+S to save (prevent default browser save)
        if (e.ctrlKey && e.which === 83) {
            e.preventDefault();
            var $submitBtn = $('.button-primary[type="submit"]');
            if ($submitBtn.length) {
                $submitBtn.click();
            }
        }
        
        // Escape to cancel/close modals
        if (e.which === 27) {
            $('.notice.is-dismissible .notice-dismiss').click();
            $('.modal, .overlay').hide();
        }
    });
    
    // Auto-save functionality for forms (optional)
    if (typeof(Storage) !== "undefined") {
        var formSelector = 'form[data-autosave]';
        
        $(formSelector).find('input, textarea, select').on('input change', function() {
            var formData = $(formSelector).serialize();
            var formId = $(formSelector).attr('id') || 'financial_form';
            localStorage.setItem('cah_financial_' + formId, formData);
        });
        
        // Restore form data on page load
        var savedData = localStorage.getItem('cah_financial_' + ($(formSelector).attr('id') || 'financial_form'));
        if (savedData) {
            var params = new URLSearchParams(savedData);
            params.forEach(function(value, key) {
                var $field = $(formSelector).find('[name="' + key + '"]');
                if ($field.length) {
                    $field.val(value);
                }
            });
        }
        
        // Clear saved data on successful submit
        $(formSelector).on('submit', function() {
            var formId = $(this).attr('id') || 'financial_form';
            localStorage.removeItem('cah_financial_' + formId);
        });
    }
});

// Additional CSS for enhanced styling
jQuery(document).ready(function($) {
    $('<style type="text/css">')
        .html(`
            .error {
                border-color: #dc3232 !important;
                box-shadow: 0 0 2px rgba(220, 50, 50, 0.8);
            }
            .wp-list-table tbody tr.hover {
                background-color: #f0f8ff;
            }
            .field-error {
                animation: fadeIn 0.3s ease-in;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .sort-handle {
                cursor: move;
                color: #999;
                margin-right: 5px;
            }
            .sort-handle:hover {
                color: #0073aa;
            }
        `)
        .appendTo('head');
});