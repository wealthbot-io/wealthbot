// NEW REALISATION
$(function(){

    updateFees();

    hideAllIsFinalTierCheckbox();
    showLastIsFinalTierCheckbox();

    $('#step_three_form, #billing_n_accounts_form').submit(function (event) {
        if(!isValidateFees()){
            alert("Fee should be more than 0 and less then 1.");
            event.preventDefault();
        }

        if(!isValidateTiers()){
            alert("Please enter the valid tier top value.");
            event.preventDefault();
        }

        validateIsOnlyOneTier();
    });

    $(document).on('click','.is-final-tier-checkbox input[type="checkbox"]', function (event) {
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

    $(document).on('click',".fees-form-block", function(event){
        $("#fees_alert_id").show();
    });

    $(document).on('click','.btn-add', function (event) {
        $('.btn-remove').hide();
        hideAllIsFinalTierCheckbox();

        var collectionHolder = $('#' + $(this).attr('data-target'));
        var lastIndex = collectionHolder.find('tr').index();
        var prototype = collectionHolder.attr('data-prototype');
        var form = prototype.replace(/__name__/g, collectionHolder.children().length);
        collectionHolder.append(form);

        updateFees();
        showLastIsFinalTierCheckbox();
        event.preventDefault();
    });

    decorateDecimalInput("#form_fees tr td input[id*='fee_without_retirement']", 4);

    //Trigger for change Tier Bottom when Tier Top was changed
    $(document).on('change',"#form_fees tr td input[id*='tier_top']", function () {
        updateFees();
    });


    $(document).on('click','.btn-remove', function (event) {
        var name = $(this).attr('data-related');
        var prev = $('*[data-content="' + name + '"]').prev();

        $('*[data-content="' + name + '"]').find('.auto-numeric').autoNumeric('destroy');
        if (prev) $(prev).find("a.btn-remove").show();
        $('*[data-content="' + name + '"]').remove();

        showLastIsFinalTierCheckbox();
        event.preventDefault();
    });


    $(document).on('click','.btn-preview-fee', function (event) {
        var data = {};
        var href = $(this).attr('href');

        $("#form_fees tr input").each(function () {
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

        $.ajax({
            url: href,
            data: data,
            success: function (data) {
                $('#fee_preview .modal-body').html(data);
                $('#fee_preview').modal('show');
            }
        });
        event.preventDefault();
    });
});

function isValidateFees()
{
    var validFlag = true;
    $("#form_fees tr td input[id*='fee_without_retirement']").each(function () {
        if(validateIsEmptyFee(this, 4)){
            validFlag = false;
            $(this).addClass('error');
        }else{
            $(this).removeClass('error');
        }
    });
    return validFlag;
}

function validateIsEmptyTier(input)
{
    var e = $(input);

    var value;
    if (e.autoNumeric('getSettings')) {
        value = parseFloat(e.autoNumeric('get')).toFixed(4);
    } else {
        value = parseFloat(e.val()).toFixed(4);
    }

    if ( !value || value <= 0 ) {
        return true;
    }
    return false;
}

function isLastTier(input)
{
    var last_tier = $("#form_fees tr:last td input[id*='tier_top']");
    if(input.attr('id') == last_tier.attr('id')) {
        return true;
    }
    return false;
}

function isValidateTiers()
{
    var validFlag = true;
    $("#form_fees tr").each(function () {
        var fee_selector = $(this).find("input[id*='fee_without_retirement']");
        var bottom_selector = $(this).find("input[id*='tier_bottom']");
        var top_selector = $(this).find("input[id*='tier_top']");
        // Check empty values
        if( top_selector && validateIsEmptyTier( top_selector ) && !isLastTier(top_selector) ) {
            validFlag = false;
            top_selector.addClass('error');
        } else {
            if(top_selector.hasClass('error')){
                top_selector.removeClass('error');
            }
        }
        // Check if bottom less then top
        var parsedBottomVal, parsedTopVal;

        if (bottom_selector.autoNumeric('getSettings')) {
            parsedBottomVal = parseFloat(bottom_selector.autoNumeric('get')).toFixed(4);
        } else {
            parsedBottomVal = parseFloat(bottom_selector.val()).toFixed(4);
        }

        if (top_selector.autoNumeric('getSettings')) {
            parsedTopVal = parseFloat(top_selector.autoNumeric('get')).toFixed(4);
        } else {
            parsedTopVal = parseFloat(top_selector.val()).toFixed(4);
        }

        if( ( parseFloat(parsedBottomVal) > parseFloat(parsedTopVal) ) && !isLastTier(top_selector) ) {
            validFlag = false;
            top_selector.addClass('error');
            bottom_selector.addClass('error');
        } else {
            if( top_selector.hasClass('error') && bottom_selector.hasClass('error') ) {
                top_selector.removeClass('error');
                bottom_selector.removeClass('error');
            }
        }
    });
    return validFlag;
}

function validateIsOnlyOneTier()
{

    $("#form_fees tr:last td input[id*='is_final_tier']").attr('checked', 'checked');
    var element = $("#form_fees tr:last td input[id*='tier_top']");
    element.attr('disabled', 'disabled');
    element.val('');
}

/**
 * Show final tier checkbox
 */
function showLastIsFinalTierCheckbox() {
    var e = $('#form_fees tr:last-child').find('.is-final-tier-checkbox');
    var tierTop = e.closest('.tier').find('input[id*="tier_top"]');
    var checkBox = e.find('input[type="checkbox"]');

    if(checkBox.attr('checked') == 'checked'){
        tierTop.attr('data-value', tierTop.val());
        tierTop.attr('disabled', 'disabled');
        tierTop.val('');
    } else {
        if(!parseFloat(tierTop.val())){
            checkBox.attr('checked', 'checked');
            tierTop.attr('data-value', tierTop.val());
            tierTop.attr('disabled', 'disabled');
            tierTop.val('');
        }
    }

    if ($('.is-final-tier-checkbox').length == 1) {
        var container = checkBox.parent().find('span');
        container.text('Is this your only tier?');
    }

    e.show();
}

/**
 * Hide final tier checkbox
 */
function hideAllIsFinalTierCheckbox() {
    $('.is-final-tier-checkbox').each(function () {
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