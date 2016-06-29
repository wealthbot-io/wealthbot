/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.06.13
 * Time: 19:08
 * To change this template use File | Settings | File Templates.
 */

$(function(){

    updateAutoNumeric();

    $('#rx_admin_model_entity_form_assetClass, #rx_admin_model_entity_form_subclass,' +
        '#rx_admin_model_entity_form_securityAssignment, #rx_admin_model_entity_form_muniSubstitution').on('change', function(){

            //TODO: Andrey + controller + update url with model-entity-id
            var elem = $(this);
            var id = elem.attr('id');

            $('input.symbol').each(function() {
                $(this).val('');
            });

//            switch (id) {
//                case 'rx_ria_model_entity_form_assetClass':
//                    $('#rx_ria_model_entity_form_subclass').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//                case 'rx_ria_model_entity_form_subclass':
//                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//                case 'rx_ria_model_entity_form_securityAssignment':
//                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
//                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
//                    break;
//            }
//
//            var form = elem.closest('form');


        var form = elem.closest('form');
        var index = elem.index('select');

        form.find('select:gt('+index+'):not(#rx_admin_model_entity_form_muniSubstitution)').attr('disabled', 'disabled');

        var options = {
            url: form.attr('data-update-url'),
            data: {
                modelSlug: form.attr('data-model-slug')
            },
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status == 'success'){
                    form.find('.form-fields').html(response.content);
                }
                if(response.status == 'error'){
                    alert(response.message);
                }
            }
        };

        form.ajaxSubmit(options);
    });

    // Add handler to create Model form
    $(document).on('submit',"#create_model_form", function(event){
        $("#create_model_form").ajaxSubmit({
            target: "#models_tab"
        });
        event.preventDefault();
    });

    // Add handler to EDIT button for selected strategy
    $(document).on('click','.edit-strategy-btn', function(event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit 3<sup>rd</sup> Party Name');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
            }
        });

        event.preventDefault();
    });

    // Add Edit strategy form handler
    $(document).on('submit','#edit_strategy_form', function(event) {
        var form = $(this);
        var config = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                }

                if (response.status == 'success') {
                    window.location.href = response.redirect_url;
                }
            }
        };
        form.ajaxSubmit(config);
        event.preventDefault();
    });

    //Model assumptions button handler
    $(document).on('click','.model-assumption-edit-btn', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit Model Assumptions');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
                updateAutoNumeric();
            }
        });
        event.preventDefault();
    });

    $(document).on('submit','#model_form', function(event){
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if (response.status == 'success'){
                    $('#models_list').find('.empty').remove();
                    $('#models_list').append(response.content);
                    updateTotalPercent();
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('submit','#edit_model_form', function(event) {
        var form = $(this);
        var modelsList = $('#models_list');
        var row = modelsList.find('tr[data-row="' + form.attr('data-edit-row') + '"]');
        var selector = form.find('.assumption-commission');

        selector.each(function(i, element) {
            if ($(element).length > 0) {
                var notAutoNumeric = $(element).autoNumeric('get');
                $(element).val(notAutoNumeric);
            }
        });

        var config = {
            dataType: 'json',
            success: function(response) {

                if (response.status == 'error') {
                    form.replaceWith(response.content);
                    updateAutoNumeric();
                }

                if (response.status == 'success') {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url
                    } else {
                        form.replaceWith(response.form);
                        row.replaceWith(response.content);

                        var percent = 0.0;
                        modelsList.find('tr').each(function(i, element){
                            percent += parseFloat($(element).find('td.percent').text());
                        });

                        modelsList.closest('.table').find('td.total-percent').html('<strong>' + percent.toFixed(2) + ' %</strong>');
                    }
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    $(document).on('click','#models_list .delete-btn', function(event){
        if(confirm('Are you sure?')) {
            var e = $(this);
            var row = e.closest('tr');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        row.remove();
                        updateTotalPercent();
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','#models_list .edit-btn', function(event){
        var e = $(this);
        var form = $('.model-entity-form');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    form.replaceWith(response.form);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.edit-model-btn', function(event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#modal_dialog .modal-header h3').html('Edit Model Name');
                    $('#modal_dialog .modal-body').html(response.content);
                    $('#modal_dialog').modal('show');
                }
                updateAutoNumeric();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-model-btn', function(event) {
        if (!confirm('Are you sure?')) {
            event.preventDefault();
        }
    });

    $(document).on('change','#rx_admin_model_entity_form_percent', function() {
        percentRound($(this));
    });
});

function percentRound(element) {
    var value = parseFloat(element.val());
    element.val((Math.round( value * 100 ) / 100).toFixed(2));
}

function updateTotalPercent() {
    var total = 0;

    $('.percent').each(function(){
        total += parseFloat($(this).text());
    });
    $('.total-percent strong').text(total.formatMoney(2, '.', ',') + ' %');
}
