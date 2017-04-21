$(function () {
    $(document).on('change','#ria_client_account_group', function (event) {
        var e = $(this);
        var form = e.closest('form');
        var errors = form.find('ul.error-list');
        errors.remove();
        var errorField = form.find('.error');
        errorField.removeClass('error');
        var fieldsSelector = form.find('.advanced-fields');

        e.parent().append('<img class="ajax-loader" style="margin-left:10px; margin-top:-10px;" src="/img/ajax-loader.gif" />');
        form.addClass('ajax-process');

        var options = {
            url: form.attr('data-update-url'),
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'error') {
                    alert(response.message);
                }
                if (response.status == 'success') {
                    fieldsSelector.html(response.content);

                    addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#ria_client_account_transferInformation_transfer_custodian_id');

                    var sasCashField = form.find('.sas-cash-field');
                    var retirementHelp = form.find('.retirement-value-help');
                    var monthlyDistField = form.find('.monthly-distributions-field');

                    var estimatedValue = form.find('#ria_client_account_value');
                    var estimatedContributions = form.find('#ria_client_account_monthly_contributions');
                    var estimatedDistributions = form.find('#ria_client_account_monthly_distributions');
                    var sasCash = form.find('#ria_client_account_sas_cash');

                    estimatedValue.val('');
                    estimatedContributions.val('');
                    estimatedDistributions.val('');
                    sasCash.val('');

                    form.find('.account-owners-fields').html('');

                    if (response.group == 'employer_retirement') {
                        sasCashField.hide();
                        monthlyDistField.hide();
                        retirementHelp.show();
                    } else {
                        sasCashField.show();
                        monthlyDistField.show();
                        retirementHelp.hide();
                    }
                }
            },
            complete: function () {
                form.removeClass('ajax-process');
                form.find('.ajax-loader').remove();
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('change','#ria_client_account_groupType, input[type="checkbox"].other-contact-owner', function (event) {
        var e = $(this);
        var form = e.closest('form');
        var errors = form.find('ul.error-list');
        errors.remove();
        var errorField = form.find('.error');
        errorField.removeClass('error');
        var fieldsSelector = form.find('.account-owners-fields');

        if (e.hasClass('other-contact-owner') && !e.is(':checked')) {
            form.find('.other-owner-fields').html('');

        } else if (!e.val().length) {
            fieldsSelector.html('');

        } else {
            e.parent().append('<img class="ajax-loader" style="margin-left:10px; margin-top:-10px;" src="/img/ajax-loader.gif" />');
            form.addClass('ajax-process');

            var options = {
                url: form.attr('data-update-owners-url'),
                dataType: 'json',
                type: 'POST',
                success: function (response) {
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                    if (response.status == 'success') {
                        fieldsSelector.html(response.content);
                    }
                },
                complete: function () {
                    form.removeClass('ajax-process');
                    form.find('.ajax-loader').remove();
                }
            };

            form.ajaxSubmit(options);
        }
    });

    $(document).on('click','.toggle-risk-answers-btn',function (event) {
        var e = $(this);
        var container = e.parent().find('.answers-table');

        container.toggle("slow", function () {
            var txt = e.html();
            if (txt == 'View Client Risk Answers') {
                e.html('Hide Client Risk Answers');
            } else {
                e.html('View Client Risk Answers');
            }

        });

        event.preventDefault();
    });

    $(document).on('click','.toggle-client-information-btn',function (event) {
        var e = $(this);
        var container = e.parent().find('.information-table');

        container.toggle("slow", function () {
            var txt = e.html();
            if (txt == 'View Client Information') {
                e.html('Hide Client Information');
            } else {
                e.html('View Client Information');
            }

        });

        event.preventDefault();
    });

    $(document).on('click','.remove-client-account-btn', function (event) {
        if (confirm('Are you sure?')) {
            var e = $(this);

            var selector = $(this).closest('tr').find('.see-investments-btn');
            hideInvestments(selector);

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        var item = e.closest('tr');
                        var accountsSelector = $('.client-accounts-list');
                        var account_id = item.attr('data-account-row');

                        accountsSelector.find('tr[data-account-row="' + account_id + '"]').remove();
                        $('.outside-accounts-list').find('tr[data-account-row="' + account_id + '"]').remove();

                        if (accountsSelector.find('tr').length < 2) {
                            accountsSelector.find('.row-total .value').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$0.00</strong>');
                            accountsSelector.find('.row-total .sas-cash').html('<strong>$0.00</strong>');

                        } else {
                            var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                            var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                            var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                            var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                            accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                            accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                            accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                            accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');
                        }
                        if (response.asset_location) {
                            if($("#asset_location")){
                                $("#asset_location").html(response.asset_location);
                            }
                        }
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                }
            });

        }

        event.preventDefault();
    });

    $(document).on('click','.edit-client-account-btn', function (event) {
        var e = $(this);

        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    var formSelector = $('.client-account-form');
                    var existForm = formSelector.find('#edit_client_account_form');
                    var editForm;

                    $('#create_client_account_form').hide();

                    if (existForm.length) {
                        existForm.replaceWith(response.form);
                        editForm = existForm;
                    } else {
                        formSelector.append(response.form);
                        editForm = formSelector.find('#edit_client_account_form');
                    }

                    updateAutoNumeric();
                    addCompleteTransferCustodianEvent('.fin-inst-typeahead', '#ria_client_account_transferInformation_transfer_custodian_id');

                    var sasCashField = editForm.find('.sas-cash-field');
                    var retirementHelp = editForm.find('.retirement-value-help');
                    var monthlyDistField = editForm.find('.monthly-distributions-field');
                    if (response.group == 'employer_retirement') {
                        sasCashField.hide();
                        monthlyDistField.hide();
                        retirementHelp.show();
                    } else {
                        sasCashField.show();
                        monthlyDistField.show();
                        retirementHelp.hide();
                    }
                    scrollToElemId('edit_client_account_form', 'slow');
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-client-account-btn', function (event) {
        $(this).closest('form').remove();
        $('#create_client_account_form').show();

        event.preventDefault();
    });

    $(document).on('submit','#edit_client_account_form', function (event) {
        var form = $(this);

        if (!form.hasClass('ajax-process')) {
            var options = {
                dataType: 'json',
                type: 'POST',
                success: function (response) {
                    if (response.status == 'success') {
                        var accountsSelector = $('.suggested-portfolio-finaccounts .client-accounts-list');
                        var item = accountsSelector.find('tr[data-account-row="' + response.account_id + '"]');
                        var see_cons_btn = item.find('.see-consolidated-accounts-btn');

                        item.replaceWith(response.content);
                        hideConsolidatedAccounts(see_cons_btn);

                        $('.client-account-form').html(response.form);
                        $('.outside-accounts-list').html(response.outside_accounts);

                        updateAutoNumeric();

                        var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                        var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                        var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                        var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                        accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                        accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                        accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                        accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');

                        if (response.outside_accounts) {
                            $('.outside-accounts-list').html(response.outside_accounts);
                            $('.outside-funds-list').html('');
                        }

                        if (response.asset_location) {
                            if($("#asset_location")){
                                $("#asset_location").html(response.asset_location);
                            }
                        }
                    }
                    if (response.status == 'error' && response.form) {
                        form.replaceWith(response.form);
                        updateAutoNumeric();
                    }
                }
            };

            form.ajaxSubmit(options);
        }

        event.preventDefault();
    });

    $(document).on('submit','#create_client_account_form', function (event) {
        var form = $(this);
        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                if (response.status == 'success') {
                    var accountsSelector = $('.suggested-portfolio-finaccounts .client-accounts-list');

                    $('.client-account-form').html(response.form);
                    if (!response.consolidator_id) {
                        accountsSelector.find('.row-total').before(response.account);
                    } else {
                        var consolidator = accountsSelector.find('tr[data-account-row="' + response.consolidator_id +'"]');
                        var see_cons_btn = consolidator.find('.see-consolidated-accounts-btn');

                        consolidator.replaceWith(response.account);
                        hideConsolidatedAccounts(see_cons_btn);
                    }

                    updateAutoNumeric();

                    var value = parseFloat(response.total.value).formatMoney(2, '.', ',');
                    var monthlyContributions = parseFloat(response.total.monthly_contributions).formatMoney(2, '.', ',');
                    var monthlyDistributions = parseFloat(response.total.monthly_distributions).formatMoney(2, '.', ',');
                    var sasCash = parseFloat(response.total.sas_cash).formatMoney(2, '.', ',');

                    accountsSelector.find('.row-total .value').html('<strong>$' + value + '</strong>');
                    accountsSelector.find('.row-total .monthly-contributions').html('<strong>$' + monthlyContributions + '</strong>');
                    accountsSelector.find('.row-total .monthly-distributions').html('<strong>$' + monthlyDistributions + '</strong>');
                    accountsSelector.find('.row-total .sas-cash').html('<strong>$' + sasCash + '</strong>');

                    if (response.outside_accounts) {
                        $('.outside-accounts-list').html(response.outside_accounts);
                        $('.outside-funds-list').html('');
                    }
                    if (response.asset_location) {
                        if($("#asset_location")){
                            $("#asset_location").html(response.asset_location);
                        }
                    }
                }
                if (response.status == 'error') {
                    $('.client-account-form').html(response.form);
                    form = $('#create_client_account_form');
                    checkSelectedAccountType(form);
                    updateAutoNumeric();
                }
            }
        };

        form.ajaxSubmit(options);

        event.preventDefault();
    });

    $(document).on('submit','#add_client_outside_fund_form, #edit_client_outside_fund_form', function (event) {
        var form = $(this);
        var options = {
            dataType: 'json',
            type: 'POST',
            success: function (response) {
                $('.outside-funds-list').html(response.content);
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.remove-client-outside-fund-btn', function (event) {
        var e = $(this);

        if (confirm('Are you sure?')) {
            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'error') {
                        alert(response.message);
                    }
                    if (response.status == 'success') {
                        e.closest('tr').remove();
                    }
                }
            });
        }

        event.preventDefault();
    });

    $('#suggested_portfolio_form_portfolio').change(function(){
        $(this).closest('form').submit();
    });

    $(document).on('click','.update-prospect-btn, .submit-final-portfolio-btn',function(){
        var elem = $(this);
        var form = $('#client_settings_form');
        var type = $('#suggested_portfolio_form_action_type');

        // Name of field in SuggestedPortfolioFormType class + []
        var unconsolidatedIdsFieldName = 'suggested_portfolio_form[unconsolidated_ids][]';

        removeUnconsolidatedIdsFields(form);

        if (elem.hasClass('update-prospect-btn')) {
            type.val('update');
            form.append(generateUnconsolidatedIdsFieldsHtml(unconsolidatedIdsFieldName));
            form.submit();
        } else {
            if (confirm('Are you sure? This action cannot be undone.')) {
                type.val('submit');
                form.append(generateUnconsolidatedIdsFieldsHtml(unconsolidatedIdsFieldName));
                form.submit();
            }

        }

        event.preventDefault();
    });

    $(document).on('click','.edit-client-outside-fund-btn', function (event) {
        var e = $(this);

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'error') {
                    alert(response.message);
                }
                if (response.status == 'success') {
                    $('#add_client_outside_fund_form').hide();

                    var editForm = $('#edit_client_outside_fund_form');
                    if (editForm.length > 0) {
                        editForm.replaceWith(response.content);
                    } else {
                        $('.outside-fund-form').append(response.content);
                    }
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.see-investments-btn', function (event) {
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideInvestments(e);
        } else {
            selector.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        $('.outside-funds-list').html(response.content);
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-investments-btn').removeClass('active').text('(See investments ▲)');
                    e.text('(Hide investments ▼)');
                    e.addClass('active');
                    scrollToElemId('outside_funds_list', 'slow');
                },
                complete: function () {
                    selector.find('.ajax-loader').remove();
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.see-consolidated-accounts-btn', function (event) {
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideConsolidatedAccounts(e);
        } else {
            selector.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        showContentInConsolidatedAccountsList(response.content);
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-consolidated-accounts-btn').removeClass('active').text('(See all accounts ▲)');
                    e.text('(Hide all accounts ▼)');
                    e.addClass('active');
                    scrollToElemId('consolidated_accounts_list', 'slow');
                },
                complete: function () {
                    selector.find('.ajax-loader').remove();
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-fund-btn', function (event) {
        $('#edit_client_outside_fund_form').remove();
        $('#add_client_outside_fund_form').show();

        event.preventDefault();
    });

    $(document).on('click','.cancel-add-fund-btn', function (event) {
        $('.outside-funds-list').html('');
        event.preventDefault();
    });

    $(document).on('click',".qualified-toggle", function(event){
        var is_qualified = $(this).attr('data-value');
        $("#suggested_portfolio_form_is_qualified").val(is_qualified);
        event.preventDefault();
        $("#client_settings_form").submit();
    });

    lockCompleteTransferCustodianCheckbox('#ria_client_account_transferInformation_is_firm_not_appear');
});


function checkSelectedAccountType(form) {

    var group = $("#ria_client_account_group").val();
    var sasCashField = $(form).find('.sas-cash-field');
    var retirementHelp = $(form).find('.retirement-value-help');
    var monthlyDistField = $(form).find('.monthly-distributions-field');

    if (group == 'employer_retirement') {
        $(sasCashField).hide();
        $(monthlyDistField).hide();
        $(retirementHelp).show();
    } else {
        sasCashField.show();
        monthlyDistField.show();
        retirementHelp.hide();
    }
}

function selectRetirementAccount(url, accountId) {
    var newUrl = url.replace('account/0/outside-funds', 'account/' + accountId + '/outside-funds');

    $.ajax({
        url: newUrl,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $('.outside-funds-list').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    });
}

function selectModel(url, modelId) {
    var newUrl = url.replace('model/0/details', 'model/' + modelId + '/details');

    $.ajax({
        url: newUrl,
        dataType: 'json',
        success: function (response) {
            if (response.status == 'success') {
                $('.model-details').html(response.content);
            }
            if (response.status == 'error') {
                alert(response.message);
            }
        }
    });
}

function postToUrl(path, params, method) {
    method = method || "post";

    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function hideInvestments(container)
{
    showContentInOutsideFundList('');
    container.text('(See investments ▲)');
    container.removeClass('active');
}

function showContentInOutsideFundList(content)
{
    $('.outside-funds-list').html(content);
}

function hideConsolidatedAccounts(container)
{
    showContentInConsolidatedAccountsList('');
    container.text('(See all accounts ▲)');
    container.removeClass('active');
}

function showContentInConsolidatedAccountsList(content)
{
    $('#consolidated_accounts_list').html(content);
}

function generateUnconsolidatedIdsFieldsHtml(fieldName)
{
    var inputs = [];

    $('input[type="checkbox"].select-unconsolidate-account:checked').each(function(){
        inputs.push('<input class="unconsolidate-account-input" type="hidden" name="' + fieldName + '" value="' + $(this).val() + '">');
    });

    return inputs.join(', ');
}

function removeUnconsolidatedIdsFields(form)
{
    form.find('input[type="checkbox"].unconsolidate-account-input').each(function(){
        $(this).remove();
    });
}
/**
 * Created with JetBrains PhpStorm.
 * User: vova
 * Date: 05.02.13
 * Time: 13:01
 * To change this template use File | Settings | File Templates.
 */

$(function () {
    drawModelCharts('.pie-chart');

    $('.stats-chart').each(function (key, element) {
        drawStatsChart(element)
    });
});

function drawModelCharts(selector)
{
    $(selector).each(function (key, element) {
        drawModelChart(element);
    });
}

function getDateByTimestamp(timestamp)
{
    var date = new Date(timestamp);

    return (date.getMonth()+1)+'/'+(date.getDate())+'/'+date.getFullYear()
}

function drawModelChart(element, options) {
    options = options || {};

    var entities = $(element).attr('data-entities');
    var jsonEntities = JSON.parse(entities);
    var defaultOptions = {
        series: {
            pie: {
                show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 2/3,
                    formatter: function(label, series){
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+series.data[0][1].toFixed(2)+'%</div>';
                    },
                    threshold: 0.1
                }
            }
        },
        grid: {
            hoverable: true,
            clickable: true
        },
        tooltip: true,
        tooltipOpts: {
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: true
        },
        legend: {
            show: false
        },
        colors: ["#8e388e", "#009ACD", "#c67171", "#7ccd7c", "#8a2be2",  "#3a5fcd", "#eeb4b4", "#20b2aa", "#6495ed", "#1c86ee",
            "#cd919e", "#4f94cd", "#8470ff", "#00c957", "#cd6889", "#00c78c", "#008080", "#800080",
            "#33a1c9", "#4876ff", "#cd96cd"]
    };

    $.extend(true, defaultOptions, options);

    $.plot($(element), jsonEntities, defaultOptions);

    function pieHover(event, pos, obj)
    {
        if (!obj)
            return;
        var percent = parseFloat(obj.series.percent).toFixed(2);
        $("#flotTip").html('<span style="font-weight: bold; color: black">'+obj.series.label+' ('+percent+'%)</span>');
    }

    $(element).bind("plothover", pieHover);
}

function drawStatsChart(element)
{
    var values = $(element).attr('data-values');
    var jsonValues = JSON.parse(values);

    if (jsonValues.length > 0) {
        var data_maximum = parseFloat($(element).attr('data-maximum'));
        var minDateTimestamp = jsonValues[0][0];
        var maxDateTimestamp = jsonValues[jsonValues.length-1][0];
        var minDate = getDateByTimestamp(minDateTimestamp);
        var maxDate = getDateByTimestamp(maxDateTimestamp);

        var plot = $.plot(element, [ { data: jsonValues, label: "Your portfolio"} ], {
            series: {
                lines: {
                    show: true,
                    lineWidth: 3,
                    fill: true,
                    fillColor: {
                        colors: [ { opacity: 0.08 }, { opacity: 0.01 } ]
                    }
                },
                shadowSize: 2
            },
            grid: {
                hoverable: true,
                clickable: true,
                tickColor: "#eee",
                borderWidth: 0
            },
            colors: ["#0088cc"],
            xaxis: {
                ticks: [ [minDateTimestamp, minDate], [maxDateTimestamp, maxDate] ],
                labelWidth: 1
            },
            yaxis: {
                tickSize: data_maximum/4-(data_maximum*0.02)
            },
            crosshair: {
                mode: "x",
                color: "rgba(0, 0, 0, 0.80)"
            },
            legend: {
                show: false
            }
        });

        function updateCrosshairPosition(pos)
        {
            var data = plot.getData();

            var series = data[0];

            var axes = plot.getAxes();
            var xaxisValue = $("#xaxis_value");
            var yaxisValue = $("#yaxis_value");

            if (pos.x < axes.xaxis.min) {
                xaxisValue.text(getDateByTimestamp(series.data[0][0]));
                yaxisValue.text(parseFloat(series.data[0][1]).formatMoney(2,'.',','));
                return;
            } else if (pos.x > axes.xaxis.max) {
                xaxisValue.text(getDateByTimestamp(series.data[series.data.length-1][0]));
                yaxisValue.text(parseFloat(series.data[series.data.length-1][1]).formatMoney(2,'.',','));
                return;
            } else if (pos.y < axes.yaxis.min || pos.y > axes.yaxis.max){
                return;
            }

            for (var j = 0; j < series.data.length; ++j) {
                if (series.data[j][0] > pos.x) {
                    break;
                }
            }

            var y,
                p1 = series.data[j - 1],
                p2 = series.data[j];

            if (p1 == null) {
                y = p2[1];
            } else if (p2 == null) {
                y = p1[1];
            } else {
                y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
            }

            xaxisValue.text(getDateByTimestamp(pos.x));
            yaxisValue.text(parseFloat(y.toFixed(2)).formatMoney(2,'.',','));
        }

        var previousPoint = null;
        $(element).bind("plothover", function (event, pos, item) {

            updateCrosshairPosition(pos);

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#tooltip").remove();

                    var x = getDateByTimestamp(item.datapoint[0]),
                        y = parseFloat(item.datapoint[1].toFixed(2)).formatMoney(2,'.',',');

                    showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = $" + y);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });

    } else {
        $(element).append("<div class='non-stats-chart-initial-message'>Track the growth of your portfolio here after you've been on our system for a month.</div>");
    }
}

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 5,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#dfeffc',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
}