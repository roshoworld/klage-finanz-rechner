jQuery(document).ready(function($) {
    
    // Calculate totals
    function calculateTotals() {
        var grundkosten = parseFloat($('#cost-grundkosten').val()) || 0;
        var gerichtskosten = parseFloat($('#cost-gerichtskosten').val()) || 0;
        var anwaltskosten = parseFloat($('#cost-anwaltskosten').val()) || 0;
        var sonstige = parseFloat($('#cost-sonstige').val()) || 0;
        var mwstRate = parseFloat($('#mwst-rate').val()) || 19;
        
        var subtotal = grundkosten + gerichtskosten + anwaltskosten + sonstige;
        var mwstAmount = subtotal * (mwstRate / 100);
        var total = subtotal + mwstAmount;
        
        $('#calc-subtotal').text('€' + subtotal.toFixed(2).replace('.', ','));
        $('#calc-mwst').text('€' + mwstAmount.toFixed(2).replace('.', ','));
        $('#calc-total').text('€' + total.toFixed(2).replace('.', ','));
        
        return {
            subtotal: subtotal,
            mwst_amount: mwstAmount,
            total: total
        };
    }
    
    // Auto-calculate on input change
    $('#cost-grundkosten, #cost-gerichtskosten, #cost-anwaltskosten, #cost-sonstige, #mwst-rate').on('input', calculateTotals);
    
    // Manual calculate button
    $('#cah-calculate').on('click', calculateTotals);
    
    // Load template
    $('#cah-load-template').on('click', function() {
        var templateId = $('#cah-template-selector').val();
        
        if (!templateId) {
            alert('Bitte wählen Sie eine Vorlage aus.');
            return;
        }
        
        $.post(cah_financial_ajax.ajax_url, {
            action: 'get_template_data',
            template_id: templateId,
            nonce: cah_financial_ajax.nonce
        }, function(response) {
            if (response.success) {
                var template = response.data;
                $('#cost-grundkosten').val(template.cost_items.grundkosten || 0);
                $('#cost-gerichtskosten').val(template.cost_items.gerichtskosten || 0);
                $('#cost-anwaltskosten').val(template.cost_items.anwaltskosten || 0);
                $('#cost-sonstige').val(template.cost_items.sonstige || 0);
                $('#mwst-rate').val(template.mwst_rate || 19);
                
                calculateTotals();
                alert('Vorlage wurde geladen!');
            } else {
                alert('Fehler beim Laden der Vorlage.');
            }
        });
    });
    
    // Save financial data
    $('#cah-save-financial').on('click', function() {
        var caseId = $('#cah-case-id').val();
        var templateId = $('#cah-template-selector').val() || 0;
        
        var costItems = {
            grundkosten: parseFloat($('#cost-grundkosten').val()) || 0,
            gerichtskosten: parseFloat($('#cost-gerichtskosten').val()) || 0,
            anwaltskosten: parseFloat($('#cost-anwaltskosten').val()) || 0,
            sonstige: parseFloat($('#cost-sonstige').val()) || 0
        };
        
        var mwstRate = parseFloat($('#mwst-rate').val()) || 19;
        
        $.post(cah_financial_ajax.ajax_url, {
            action: 'save_case_financial_data',
            case_id: caseId,
            template_id: templateId,
            cost_items: costItems,
            mwst_rate: mwstRate,
            nonce: cah_financial_ajax.nonce
        }, function(response) {
            if (response.success) {
                calculateTotals();
                alert('Finanzielle Daten wurden erfolgreich gespeichert!');
            } else {
                alert('Fehler beim Speichern der finanziellen Daten.');
            }
        });
    });
    
    // Save as new template
    $('#cah-save-template').on('click', function() {
        var templateName = $('#new-template-name').val().trim();
        var templateDescription = $('#new-template-description').val().trim();
        
        if (!templateName) {
            alert('Bitte geben Sie einen Vorlagennamen ein.');
            return;
        }
        
        var costItems = {
            grundkosten: parseFloat($('#cost-grundkosten').val()) || 0,
            gerichtskosten: parseFloat($('#cost-gerichtskosten').val()) || 0,
            anwaltskosten: parseFloat($('#cost-anwaltskosten').val()) || 0,
            sonstige: parseFloat($('#cost-sonstige').val()) || 0
        };
        
        var mwstRate = parseFloat($('#mwst-rate').val()) || 19;
        
        $.post(cah_financial_ajax.ajax_url, {
            action: 'save_case_template',
            template_name: templateName,
            template_description: templateDescription,
            cost_items: costItems,
            mwst_rate: mwstRate,
            nonce: cah_financial_ajax.nonce
        }, function(response) {
            if (response.success) {
                alert('Vorlage wurde erfolgreich gespeichert!');
                $('#new-template-name').val('');
                $('#new-template-description').val('');
                
                // Add new template to dropdown
                var newOption = $('<option></option>')
                    .attr('value', response.data.template_id)
                    .text(templateName);
                $('#cah-template-selector').append(newOption);
            } else {
                alert('Fehler beim Speichern der Vorlage.');
            }
        });
    });
    
    // Initial calculation
    calculateTotals();
});