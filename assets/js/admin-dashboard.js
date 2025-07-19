jQuery(document).ready(function($) {
    
    // Tab functionality
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');
        
        // Show corresponding content
        var target = $(this).attr('href');
        $(target).addClass('active');
    });
    
    // Case form submission
    $('#case-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'cah_save_case',
            nonce: cah_dashboard_ajax.nonce,
            case_number: $('input[name="case_number"]').val(),
            debtor_name: $('input[name="debtor_name"]').val(),
            amount: $('input[name="amount"]').val(),
            status: $('select[name="status"]').val(),
            description: $('textarea[name="description"]').val()
        };
        
        // Add case_id if editing
        var caseId = $('input[name="case_id"]').val();
        if (caseId) {
            formData.case_id = caseId;
        }
        
        $.post(cah_dashboard_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
                // Redirect to cases list
                window.location.href = 'admin.php?page=klage-click-cases';
            } else {
                alert('Error saving case');
            }
        });
    });
    
    // Delete case function (global)
    window.deleteCase = function(caseId) {
        if (!confirm('Are you sure you want to delete this case?')) {
            return;
        }
        
        $.post(cah_dashboard_ajax.ajax_url, {
            action: 'cah_delete_case',
            nonce: cah_dashboard_ajax.nonce,
            case_id: caseId
        }, function(response) {
            if (response.success) {
                alert(response.data.message);
                location.reload();
            } else {
                alert('Error deleting case');
            }
        });
    };
});