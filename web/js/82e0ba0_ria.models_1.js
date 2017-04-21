/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:47
 * To change this template use File | Settings | File Templates.
 */

$(function() {

    riskRatingSlider();
    decorateDecimalInput('.low-market-return', 2);

    $(document).on('click','.edit-ria-model-btn', function (event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var modalDialog =  $('#modal_dialog');
                    modalDialog.find('.modal-header h3').html('Edit Model Parameters');
                    modalDialog.find('.modal-body').html(response.content);
                    modalDialog.modal('show');
                }
                updateAutoNumeric();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#edit_ria_model_form', function (event) {
        var form = $(this);

        var config = {
            dataType: 'json',
            success: function (response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                }

                if (response.status == 'success') {
                    var modalDialog =  $('#modal_dialog');
                    modalDialog.modal('hide');

                    $('#models_list_btns').replaceWith(response.models_list);
                    $('#model_view_row').replaceWith(response.model_view);
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    $('#rx_ria_model_entity_form_assetClass, #rx_ria_model_entity_form_subclass,' +
        '#rx_ria_model_entity_form_securityAssignment, #rx_ria_model_entity_form_muniSubstitution,' +
        '#rx_ria_model_entity_form_tax_loss_harvesting').on('change', function() {

            var elem = $(this);
            var id = elem.attr('id');

            $('input.symbol').each(function() {
                $(this).val('');
            });

            switch (id) {
                case 'rx_ria_model_entity_form_assetClass':
                    $('#rx_ria_model_entity_form_subclass').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
                case 'rx_ria_model_entity_form_subclass':
                    $('#rx_ria_model_entity_form_securityAssignment').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
                case 'rx_ria_model_entity_form_securityAssignment':
                    $('#rx_ria_model_entity_form_muniSubstitution').attr('disabled', 'disabled');
                    $('#rx_ria_model_entity_form_tax_loss_harvesting').attr('disabled', 'disabled');
                    break;
            }

            var form = elem.closest('form');
            updateEntityForm(form);
        });

    $(document).on('submit','#ria_model_form', function (event) {
        var form = $(this);

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    $('#model_entities_list').find('.empty').remove();
                    $('#model_entities_list').append(response.content);
                    updateTotalPercent();
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('change','#rx_ria_model_entity_form_percent', function() {
        percentRound($(this));
    });

    $(document).on('click','#model_entities_list .delete-btn', function (event) {
        if (confirm('Are you sure?')) {
            var e = $(this);
            var row = e.closest('tr');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        row.remove();
                        updateTotalPercent();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','#model_entities_list .edit-btn', function (event) {
        var e = $(this);
        var form = $('.ria-model-entity-form');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    form.replaceWith(response.form);
                    percentRound($('#rx_ria_model_entity_form_percent'));
                }

                if (response.status == 'error') {
                    alert(response.message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#ria_edit_model_form', function (event) {
        var form = $(this);
        var rowIndex = form.data('edit-row');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    $('#model_entities_list tr[data-row="' + rowIndex + '"]').replaceWith(response.content);
                    form.replaceWith(response.form);
                    updateTotalPercent();
                }
                if (response.status == 'error') {
                    form.replaceWith(response.form);
                }

                form.replaceWith(response.form);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('submit','#create_model_form', function(event) {
        var form = $(this);
        var button = form.find('button[type="submit"]');

        $(button).button('loading');
        $(this).ajaxSubmit({
            success: function(response){
                form.replaceWith(response.form);
                $('#models_list').html(response.models_list);
                $(button).button('reset');
            }
        });
        event.preventDefault();
    });

    $(document).on('click','#models_list a:not(:last)', function(event) {
        var btn = $(this);

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var modelView = $('#model_view_row');
                    if (modelView.length > 0) {
                        modelView.replaceWith(response.content)
                    } else {
                        $('#models_tab').append(response.content);
                    }

                    leaveModelPageEvent();
                }

                if (response.status == 'error') {
                    alert('Error: ' + response.error_message);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-ria-model-btn', function (event) {
        var elem = $(this);

        if (confirm('Are you sure?')) {
            $.ajax({
                url: elem.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        $('#model_view_row').remove();
                        $('#models_list a.active').remove();
                    }

                    if (response.status == 'error') {
                        alert('Error: ' + response.error_message);
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('change',"#q_container_id :input", function(){
        var url = $("#q_container_id").attr('data-url');
        var is_qualified = $(this).val();
        $.ajax({
            url: url,
            data: { is_qualified: is_qualified },
            dataType: 'html',
            success: function (response) {

                $("#me_container_id").html(response);
                updateEntityForm();
            }
        });
    });
});

function riskRatingSlider() {
    var form = $('#model_risk_rating_form');
    var maxRating = form.attr('data-max-rating');
    var selects = $('.risk-rating-slider');

    $(selects).each(function(){
        var select = this;

        var slider = $( "<div id='slider_" + $(select).attr('id') + "'></div>").insertAfter( select ).slider({
            min: 1,
            max: maxRating,
            range: "min",
            value: select.selectedIndex + 1,
            slide: function( event, ui ) {
                select.selectedIndex = ui.value - 1;
            }
        });

        $(select).change(function() {
            var e = this;
            slider.slider( "value", e.selectedIndex + 1 );
        });
    });
}

function updateEntityForm(form) {
    var index = form.index('select');

    form.find('select:gt(' + index + '):not(#rx_ria_model_entity_form_muniSubstitution)').attr('disabled', 'disabled');

    var options = {
        url: form.attr('data-update-url'),
        data: {
            slug: form.attr('data-slug')
        },
        dataType: 'json',
        type: 'POST',
        success: function (response) {
            if (response.status == 'success') {
                form.find('.form-fields').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    };

    form.ajaxSubmit(options);
}

function leaveModelPageEvent() {
    window.onbeforeunload = function (evt) {
        var total = 0;
        $('.percent').each(function () {
            total += parseFloat($(this).text());
        });

        if (total < 100) {
            var message = "Model does not add up to 100%";
            if (typeof evt == "undefined") {
                evt = window.event;
            }
            if (evt) {
                evt.returnValue = message;
            }

            return message;
        }
    };
}
