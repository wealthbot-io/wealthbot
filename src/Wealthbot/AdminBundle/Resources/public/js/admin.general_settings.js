/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.06.13
 * Time: 17:40
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    updateFees();


    function removeBtn() {
        $(document).on('click','.btn-remove', function (event) {
            var name = $(this).attr('data-related');
            var prev = $('*[data-content="' + name + '"]').prev();
            if (prev) $(prev).find("a.btn-remove").show();
            $('*[data-content="' + name + '"]').remove();

            showLastIsFinalTierCheckbox();
            event.preventDefault();
        });
    };

    removeBtn();
    
    var lastTierCHeckbox = $('#form_fees tr.fee-row:last-child').find('.is-final-tier-checkbox');
    if ($('.is-final-tier-checkbox').length == 1) {
        lastTierCHeckbox.find('span').text('Is this your only tier?');
    }

    lastTierCHeckbox.show();


    $(document).on('click','.is-final-tier-checkbox input[type="checkbox"]', function(event){
        var e = $(this);
        var tierTop = e.closest('.tier').find('input[id*="tier_top"]');

        if (e.is(':checked')) {
            tierTop.attr('data-value', tierTop.val());
            tierTop.attr('disabled', 'disabled');
            tierTop.val('');
        } else {
            tierTop.val(tierTop.attr('data-value'));
            tierTop.attr('data-value', '');
            tierTop.attr('disabled', false);
        }
    });

    //Trigger for change Tier Bottom when Tier Top was changed
    $(document).on('change',"#form_fees tr td input[id*='tier_top']", function(){
        updateFees();
    });

    $(document).on('click','.admin-fees-list form .btn-save', function(event){
        var btn = $(this);
        var form = btn.closest('form');
        var data = {};

        btn.button('loading');

        form.find('input').each(function(){
            var e = $(this);
            var name = e.attr('name');
            var value = '';

            if (e.attr('type') == 'checkbox') {
                value = e.is(':checked') ? 1 : 0;
            } else {
                value = e.autoNumeric('getSettings') ? e.autoNumeric('get') : e.val();
            }

            data[name] = value;
        });

        var options = {
            dataType: 'json',
            data: data,
            success: function(response){
                if(response.content.length > 0){
                    $('.admin-fees-list').html(response.content);
                    updateFees();

                    var lastTierCHeckbox = $('#form_fees tr.fee-row:last-child').find('.is-final-tier-checkbox');
                    if ($('.is-final-tier-checkbox').length == 1) {
                        lastTierCHeckbox.find('span').text('Is this your only tier?');
                    }
                    lastTierCHeckbox.show();
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.btn-add', function(event) {
        $('.btn-remove').hide();
        hideAllIsFinalTierCheckbox();

        var collectionHolder = $('#' + $(this).attr('data-target'));
        var prototype = collectionHolder.attr('data-prototype');
        var form = prototype.replace(/__name__/g, collectionHolder.children().length);
        collectionHolder.append(form);

        updateFees();
        showLastIsFinalTierCheckbox();

        $('tr.fee-row:last-child').find('.a.btn-remove').show();

        removeBtn();
        event.preventDefault();
    });

    $("#form_fees tr td input[id*='fee_without_retirement'], #form_fees tr td input[id*='fee_with_retirement']").on('focus', function(){
        if($(this).val() == '.0000'){
            $(this).val('.');
        }
    });
    $("#form_fees tr td input[id*='fee_without_retirement'], #form_fees tr td input[id*='fee_with_retirement']").on('blur', function(){
        if($(this).val() == '.'){
            $(this).val('.0000');
        }
    });

    // Validate Fee value
    $(document).on('change',"#form_fees tr td input[id*='fee_without_retirement'], #form_fees tr td input[id*='fee_with_retirement']", function(){
        var e = $(this);
        var value = e.val();

        if(parseFloat(value) >= 1){
            alert('Please insert value more than 0 and less then 1.');
            e.val('.0000');
        }else {
            if(!value){
                e.val('.0000');
            } else {
                e.val(parseFloat(value).toFixed(4));
            }
        }
    });

    $(document).on('click','.upload-document-btn', function(event) {
        var elem = $(this);
        var form = $('#documents_tab').attr('data-form');
        var dialog = $('#modal_dialog');
        var item = elem.closest('[data-document-type]');
        var type = item.attr('data-document-type');
        var title = 'Please choose ' + item.attr('data-document-title') + ' to upload.';

        dialog.find('.modal-header').html(title);
        dialog.find('.modal-body').html(form);
        $('#document_type').val(type);

        if (type == 'adv') {
            $('#document_file').attr('accept', '.pdf');
        }

        dialog.modal('show');

        event.preventDefault();
    });

    $(document).on('submit','#document_form', function(event) {
        var form = $(this);
        var dialog = $('#modal_dialog');
        var btn = dialog.find('.save-modal-form-btn');

        btn.button('loading');

        form.ajaxSubmit({
            //target: "#documents_tab",
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    dialog.find('.modal-header').html('');
                    dialog.find('.modal-body').html('');
                    dialog.modal('hide');
                    $('#documents_tab').html(response.content);
                } else {
                    $('#document_form').replaceWith(response.content);
                }
            },
            complete: function() {
                btn.button('reset')
            }
        });
        event.preventDefault();
    });
});

function showLastIsFinalTierCheckbox() {
    var e = $('#form_fees tr.fee-row:last-child').find('.is-final-tier-checkbox');
    var tierTop = e.closest('.tier').find('input[id*="tier_top"]');

    tierTop.attr('data-value', tierTop.val());
    tierTop.attr('disabled', 'disabled');
    tierTop.val('');

    var checkBox =  e.find('input[type="checkbox"]');
    if ($('.is-final-tier-checkbox').length == 1) {
        var container = checkBox.parent().find('span');

        container.text('Is this your only tier?');
    }
    checkBox.attr('checked', 'checked');


    e.show();
}

function hideAllIsFinalTierCheckbox() {
    $('.is-final-tier-checkbox').each(function(){
        var e = $(this);
        var tierTop = e.closest('.tier').find('input[id*="tier_top"]');

        if (tierTop.attr('disabled') == 'disabled') {
            tierTop.val(tierTop.attr('data-value'));
            tierTop.attr('data-value', '');
            tierTop.attr('disabled', false);
        }

        e.find('input[type="checkbox"]').attr('checked', false);
        e.hide();
    });
}