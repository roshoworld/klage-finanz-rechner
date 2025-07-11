/**
 * Admin JavaScript for Court Automation Hub
 */

jQuery(document).ready(function($) {
    
    // Case form handling
    $('#add-case-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'cah_create_case');
        formData.append('nonce', cah_ajax.nonce);
        
        // Show loading state
        $(this).addClass('loading');
        
        $.ajax({
            url: cah_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotice('Fall erfolgreich erstellt!', 'success');
                    
                    // Redirect to cases list after 2 seconds
                    setTimeout(function() {
                        window.location.href = cah_ajax.cases_url;
                    }, 2000);
                } else {
                    showNotice('Fehler beim Erstellen des Falls: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('AJAX-Fehler beim Erstellen des Falls', 'error');
            },
            complete: function() {
                $('#add-case-form').removeClass('loading');
            }
        });
    });
    
    // Auto-generate case ID
    $('#case_id').on('focus', function() {
        if ($(this).val() === '') {
            var caseId = generateCaseId();
            $(this).val(caseId);
        }
    });
    
    // Damage calculation
    $('#calculate-damages').on('click', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: cah_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cah_calculate_damages',
                nonce: cah_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDamagesDisplay(response.data);
                } else {
                    showNotice('Fehler bei der Schadensberechnung', 'error');
                }
            }
        });
    });
    
    // Case status update
    $('.status-update').on('change', function() {
        var caseId = $(this).data('case-id');
        var newStatus = $(this).val();
        
        $.ajax({
            url: cah_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'cah_update_case_status',
                case_id: caseId,
                new_status: newStatus,
                nonce: cah_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Status erfolgreich aktualisiert', 'success');
                    location.reload();
                } else {
                    showNotice('Fehler beim Aktualisieren des Status', 'error');
                }
            }
        });
    });
    
    // Bulk actions
    $('#doaction').on('click', function(e) {
        var action = $('#bulk-action-selector-top').val();
        if (action === '-1') {
            e.preventDefault();
            showNotice('Bitte wählen Sie eine Aktion aus', 'warning');
            return;
        }
        
        var checkedCases = $('input[name="case[]"]:checked');
        if (checkedCases.length === 0) {
            e.preventDefault();
            showNotice('Bitte wählen Sie mindestens einen Fall aus', 'warning');
            return;
        }
        
        if (!confirm('Sind Sie sicher, dass Sie diese Aktion ausführen möchten?')) {
            e.preventDefault();
        }
    });
    
    // Helper functions
    function generateCaseId() {
        var year = new Date().getFullYear();
        var random = Math.floor(Math.random() * 9000) + 1000;
        return 'SPAM-' + year + '-' + random;
    }
    
    function updateDamagesDisplay(damages) {
        if (damages.base_damage) {
            $('#base-damage').text('€' + damages.base_damage.toFixed(2));
        }
        if (damages.legal_fees) {
            $('#legal-fees').text('€' + damages.legal_fees.toFixed(2));
        }
        if (damages.total) {
            $('#total-amount').text('€' + damages.total.toFixed(2));
        }
    }
    
    function showNotice(message, type) {
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    // Initialize tooltips if needed
    $('.tooltip').hover(
        function() {
            $(this).attr('title', $(this).data('tooltip'));
        }
    );
    
    // Form validation
    $('form').on('submit', function() {
        var requiredFields = $(this).find('[required]');
        var isValid = true;
        
        requiredFields.each(function() {
            if ($(this).val() === '') {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            showNotice('Bitte füllen Sie alle erforderlichen Felder aus', 'error');
            return false;
        }
    });
    
    // Email validation
    $('input[type="email"]').on('blur', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('error');
            showNotice('Bitte geben Sie eine gültige E-Mail-Adresse ein', 'warning');
        } else {
            $(this).removeClass('error');
        }
    });
    
    // Date validation
    $('input[type="date"]').on('change', function() {
        var selectedDate = new Date($(this).val());
        var today = new Date();
        
        if (selectedDate > today) {
            $(this).addClass('error');
            showNotice('Das Datum kann nicht in der Zukunft liegen', 'warning');
        } else {
            $(this).removeClass('error');
        }
    });
});