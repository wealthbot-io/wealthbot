$(document).ready(function(){
    $(document).on('focusin', '.jq-date', function() {
        $(this).inputmask("mask", {"mask": "99-99-9999"});

        $(this).datepicker({
            yearRange: "1900:+0",
            dateFormat: "mm-dd-yy",
            changeMonth: true,
            changeYear: true
        });
    });

    $(document).on('focusin', '.jq-us-date', function() {
        $(this).inputmask("99/99/9999");

        $(this).datepicker({
            yearRange: "1900:+0",
            dateFormat: "mm/dd/yy",
            changeMonth: true,
            changeYear: true
        });
    });

    $('.jq-ce-date').on('focusin', function() {
        $(this).inputmask("99-99-9999");

        $(this).datepicker({
            yearRange: "1900:+0",
            dateFormat: "mm-dd-yy",
            changeMonth: true,
            changeYear: true
        });
    });

    $("#reg_save_btn").click(function(){
        var form = $('form[data-save="true"]');

        // If form doesn't exist reload page
        if (form.length < 1) {
            document.location.reload();
        } else {
            // If form doesn't save automatic then submit it otherwise reload page only
            if(form.attr('data-presave') == 'false'){
                form.submit();
            }else{
                document.location.href = form.attr('action');
            }
        }
    });

    $('.models-assumption-edit-btn').on('click', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    var modalDialog = $('#modal_dialog');
                    modalDialog.find('.modal-header h3').html('Edit Global Model Parameters');
                    modalDialog.find('.modal-body').html(response.content);
                    modalDialog.modal('show');
                }
                updateAutoNumeric();
            }
        });
        event.preventDefault();
    });

    $('#edit_third_party_model_model_assumption_form').on('submit', function(event) {
        var form = $(this);

        var config = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form.replaceWith(response.content);
                    updateAutoNumeric();
                }

                if (response.status == 'success') {
                    var modalDialog = $('#modal_dialog');
                    modalDialog.modal('hide');
                }
            }
        };

        form.ajaxSubmit(config);
        event.preventDefault();
    });

    $('.save-modal-form-btn').on('click', function(event) {
        $(this).closest('#modal_dialog').find('form').submit();
        event.preventDefault();
    });

//    $('.generous-market-return').on('change', function() {
//        if (parseFloat($(this).autoNumeric('get')) < 1 || parseFloat($(this).autoNumeric('get')) >= 2) {
//            $(this).val('1.00');
//        }
//    });

    $('.low-market-return').on('change', function() {
        if (parseFloat($(this).autoNumeric('get')) < 0 || parseFloat($(this).autoNumeric('get')) >= 1) {
            $(this).val('0.00');
        }
    });

    $('.alert').alert();

    updateAutoNumeric();

    // Placeholder for IE
    $('input[placeholder], textarea[placeholder]').placeholder();

    $("#form_fees tr td input[id*='tier_top']").on('keydown', function(event){
        var e = $(this);
        var browser = $.browser;
        var key;
        var isShift;
        var plusKey;

        key = event.which;
        isShift = event.shiftKey ? true : false;

        if (browser.opera || browser.mozilla) {
            plusKey = 61;
        } else {
            plusKey = 187;
        }

        if ( (isShift && key == plusKey) || key == 107 ) {
            e.removeClass('auto-numeric');
            $('#'+ e.attr('id')).autoNumeric('destroy');
        } else if (!e.hasClass('auto-numeric')) {
            e.autoNumeric({vMax: '99999999999999.99'});
            e.addClass('auto-numeric');
        }
    });

    $('#ria_model_completion_form input[type="checkbox"]').click(function(event){
        event.preventDefault();
        var elem = $(this);
        var form = elem.closest('form');
        var li = elem.closest('li');
        var progressSelector = form.parent().find('.model-completion-progress');

        var isChecked = elem.is(':checked');
        var value     = elem.val();

        var count = $('#ria_model_completion_form input[type="checkbox"]').length;
        var checkedCount = $('#ria_model_completion_form input[type="checkbox"]:checked').length;

        var percent = Math.round(100 / count * checkedCount);

        li.append('<img class="ajax-loader pull-right" src="/img/ajax-loader.gif" />');
        var options = {
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    if (isChecked) {
                        li.addClass('done');
                        elem.attr('checked', 'checked');
                    } else {
                        elem.removeAttr('checked');
                        li.removeClass('done');
                    }

                    progressSelector.find('.progress-text').text(percent);
                    progressSelector.find('.progress .bar').css('width', percent+'%');

                    if(value == 1 && percent != 100){
                        var href = $('#ria_model_completion_form input[type="checkbox"]:not(:checked):first').attr('data-url');
                        if (typeof href === 'undefined') {
                            alert('invalid url');
                        } else {
                            document.location.href = href;
                        }
                    }

                    if(percent == 100){
                        $("#ria_model_completion_form").hide();
                        $('<p><div class="alert alert-success">We have been notified of you completing our setup process. We will review your setup and contact you shortly.</div></p>').insertBefore(".model-completion-progress");
                        $(".alert-error").remove();
                    }
                }else{
                    var href = $('#ria_model_completion_form input[type="checkbox"]:not(:checked):first').attr('data-url');
                    if (typeof href === 'undefined') {
                        alert('invalid url');
                    } else {
                        if (response.message.length > 0) {
                            alert('Error: ' + response.message);
                        } else {
                            alert('Error, please try again.');
                        }
                        if (document.location.pathname != href) {
                            document.location.pathname = href;
                        }
                    }
                }
            },
            complete: function() {
                li.find('.ajax-loader').remove();
            }
        };

        form.ajaxSubmit(options);
    });

    $('input.auto-numeric').focus(function() {
        var elem = $(this);
        var isAutoNumeric = elem.autoNumeric('getSettings') ? true : false;
        var value = parseFloat(elem.val()).toFixed(4);

        if (isAutoNumeric && value < 0.0001) {
            elem.autoNumeric('destroy');
            elem.val('');
        }
    });

    $('input.auto-numeric').blur(function() {
        var elem = $(this);
        var isAutoNumeric = elem.autoNumeric('getSettings') ? true : false;
        var value = elem.val();

        if (value.length < 1 || parseFloat(value).toFixed(4) < 0.0001) {
            elem.val('0.00');
        }

        if (!isAutoNumeric) {
            elem.autoNumeric('init', {vMax: '99999999999999.99'});
        } else {
            elem.autoNumeric('update', {vMax: '99999999999999.99'});
        }

    });
});

/**
 * Update fees table, make all fields as autonumeric.
 * Used on the admin side and ria side.
 */
function updateFees() {
    updateAutoNumeric();

    var bottom = 0;
    $("#form_fees tr").each(function () {
        var fee_selector = $(this).find("input[id*='fee_without_retirement']");
        var bottom_selector = $(this).find("input[id*='tier_bottom']");
        var top_selector = $(this).find("input[id*='tier_top']");

        fee_selector.val(fee_selector.val() ? fee_selector.val() : '.0000');
        if (top_selector.autoNumeric('getSettings')) {
            top_selector.autoNumeric('set', top_selector.autoNumeric('get'));
        }

        if (bottom_selector.autoNumeric('getSettings')) {
            bottom_selector.autoNumeric('set', bottom);
        }

        if (top_selector.autoNumeric('getSettings')) {
            bottom = parseFloat(top_selector.autoNumeric('get')) + 0.01;
            bottom = bottom ? Number(bottom).toFixed(2) : '';
        }

        if (top_selector.attr('disabled') == 'disabled') {
            top_selector.val('');
        }
    });

    $("#form_fees tr .btn-remove").hide();
    $("#form_fees tr[data-content]:last .btn-remove").show();
}

/**
 * Set number format for inputs
 */
function updateAutoNumeric() {
    $('input.auto-numeric').each(function(){
        var elem = $(this);
        var isAutoNumeric = elem.autoNumeric('getSettings') ? true : false;

        if (!isAutoNumeric) {
            elem.autoNumeric('init', {vMax: '99999999999999.99'});
        } else {
            elem.autoNumeric('update', {vMax: '99999999999999.99'});
        }
    });
}

/**
 * Update input mask
 */
function updateInputmask() {
    $('input[data-mask-type]').each(function() {
        var elem = $(this);
        var type = elem.attr('data-mask-type');
        var mask = null;

        if (type === 'phone') {
            mask = {"mask": "(999) 999-9999"};
        }

        if (mask) {
            elem.inputmask("mask", mask);
        }
    });
}

/**
 * FOR BUTTONS WHEN AJAX LOADING
 * @param state
 * @return {*}
 */
$.fn.state = function(state) {
    var d = 'disabled';
    return this.each(function () {
        var $this = $(this);
        $this[0].className = $this[0].className.replace(/\bstate-.*?\b/g, '');
        $this.html( $this.data()[state] );
        state == 'loading' ? $this.addClass(d+' state-'+state).attr(d,d) : $this.removeClass(d).removeAttr(d);
    });
};

/**
 * Parse money formatted string to float
 *
 * @param money
 * @return {String}
 */
function moneyToFloat(money) {
    var number = money.toString().replace(/\$|\,/g, '');

    if (!number || isNaN(number)) {
        number = 0;
    }

    return parseFloat(number).toFixed(2);
}

function scrollToElemId(elem_id, speed) {
    if(document.getElementById(elem_id)) {
        var destination = jQuery('#'+elem_id).offset().top-40;
        jQuery("html,body").animate({scrollTop: destination}, speed);
    }
}

function updateTotalPercent() {
    var total = 0;

    $('.percent').each(function () {
        total += parseFloat($(this).text());
    });

    $('.total-percent strong').text(total.formatMoney(2, '.', ',') + ' %');
}

function percentRound(element) {
    var value = parseFloat(element.val());
    element.val((Math.round( value * 100 ) / 100).toFixed(2));
}

function decorateDecimalInput(inputSelector, decimalsCount) {
    var decimal = strPad('', decimalsCount);

    $(inputSelector).on('focus', function () {
        if ($(this).val() == '.'+decimal || $(this).val() == '0.'+decimal) {
            $(this).val('.');
        }
    });
    $(inputSelector).on('blur', function () {
        if ($(this).val() == '.') {
            $(this).val('.'+decimal);
        }
    });

    $(inputSelector).on('change', function () {
        validateIsEmptyFee(this, decimalsCount);
    });
}

function validateIsEmptyFee(input, decimalsCount)
{
    var e = $(input);
    var value = e.val();
    var parsedVal = parseFloat(value);
    var decimal = strPad('', decimalsCount);

    if (!value || (parsedVal >= 1 || parsedVal == 0)) {
        e.val('.'+decimal);
    } else {
        e.val(parsedVal.toFixed(decimalsCount));
        return false;
    }
    return true;
}

function strPad(input, length, string) {
    string = string || '0'; input = input + '';
    return input.length >= length ? input : new Array(length - input.length + 1).join(string) + input;
}

function getAlertMessage(message, type) {
    type = type || '';
    var alertClass = (type === '') ? '' : 'alert-' + type;

    return '<div class="alert ' + alertClass + '">' +
        '<button type="button" class="close" data-dismiss="alert">×</button>' + message + '</div>';
}

/**
 * Formats number value as money
 *
 * @param c Number of fractional digits
 * @param d Decimal separator
 * @param t Thousands separator
 * @return {string}
 */
Number.prototype.formatMoney = function(c, d, t){
    var n = this, c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

String.prototype.moneyToFloat = function(deciamlSeparator, thousandSeparator){
    deciamlSeparator = deciamlSeparator == undefined ? "." : deciamlSeparator;
    thousandSeparator = thousandSeparator == undefined ? "," : thousandSeparator;
    var s = this.toString().replace(thousandSeparator, '').replace(deciamlSeparator, '.').replace(/^[^\d]+|[^\d]+$/, '');
    if (!s || isNaN(s)) {
        return 0;
    }
    return s.parseFloat(s).toFixed(2);
};

//---- Financial institution for client transfer account. Used in client intake and ria prospects pages ---//
function addCompleteTransferCustodianEvent(inputSelector, custodianIdSelector, callback, ajaxSuccessCallback)
{
    var input = $(inputSelector);
    custodianIdSelector = custodianIdSelector || '';
    callback = callback || null;
    ajaxSuccessCallback = ajaxSuccessCallback || null;

    if (!(input.length > 0)) return;
    var map = [];
    var transferCustodianInput = $(custodianIdSelector);

    input.typeahead({
        source: function (query, process) {
            if (window.event) {
                var which = window.event.which;

                if (which != 32 && which != 46 && which != 8 &&
                    !(which >= 65 && which <= 90) &&
                    !(which >= 96 && which <= 112) &&
                    !(which >= 48 && which <= 57)) {
                    return;
                }
            }

            if (!input.hasClass('typeahead')) return;

            var browsedAjax = $.data(input[0], 'xhr_id');
            if (browsedAjax) {
                browsedAjax.abort();
            }

            var xhrId = $.ajax({
                url: input.attr('data-complete-url'),
                data: { query: query },
                dataType: 'json',
                beforeSend: function() {
                    if (!input.hasClass('loading')) {
                        input.addClass('loading');
                        input.after('<img class="ajax-loader" src="/img/ajax-loader.gif"/>');
                    }
                },
                complete: function() {
                    input.removeClass('loading');
                    input.next('.ajax-loader').remove();
                },
                success: function(data) {
                    var items = [];
                    $.each(data, function (i, record) {
                        map[record.name] = record;
                        items.push(record.name);
                    });

                    if (ajaxSuccessCallback) {
                        ajaxSuccessCallback();
                    }


                    return process(items);
                }
            });

            $.data(input[0], 'xhr_id', xhrId);
        },
        updater: function(item) {
            var object = map[item];
            input.val(object.name);

            if (transferCustodianInput.length) {
                transferCustodianInput.val(object.id);
            }

            if (callback) {
                callback(object);
            }

            return item;
        }
    });

    addBoldClosedTextInOption();
}

function lockCompleteTransferCustodianCheckbox(checkboxSelector)
{
    $(checkboxSelector).on('click', function(){
        var elem = $(this);
        var isChecked = elem.is(':checked');
        var input = $('input.fin-inst-typeahead');

        if (isChecked) {
            input.removeClass('ajaxed');
            input.addClass('warn-placeholder');
            input.attr('placeholder', 'Enter the name of the unavailable firm.');
        } else {
            input.addClass('ajaxed');
            input.removeAttr('placeholder');
            input.removeClass('warn-placeholder');
        }
    });
}

function addBoldClosedTextInOption()
{
    $('.account-option-closed').each(function(index, element) {
        $(element).prepend('<b>(Closed) - </b>')
    });
}
//-----------------------------------------------------------------------------------------------------//