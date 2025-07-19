/**
 * Admin JavaScript for Klage.Click Hub
 */

jQuery(document).ready(function($) {
    
    // Auto-generate case ID
    $('#case_id').on('focus', function() {
        if ($(this).val() === '') {
            var caseId = generateCaseId();
            $(this).val(caseId);
        }
    });
    
    // Helper function to generate case ID
    function generateCaseId() {
        var year = new Date().getFullYear();
        var random = Math.floor(Math.random() * 9000) + 1000;
        return 'SPAM-' + year + '-' + random;
    }
    
    // Show success/error messages
    function showNotice(message, type) {
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
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
});