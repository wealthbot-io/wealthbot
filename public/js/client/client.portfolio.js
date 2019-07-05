/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.02.13
 * Time: 19:23
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('click','.see-investments-btn', function(event){
        var e = $(this);
        var selector = e.closest('td');

        if (e.hasClass('active')) {
            hideInvestments(e);
        } else {

            showAjaxLoader(selector);

            $.ajax({
                url: e.attr('data-url'),
                dataType: 'json',
                success: function(response){

                    if (response.status == 'success') {
                        showContentInOutsideFundList(response.content)
                    }
                    if (response.status == 'error') {
                        alert(response.message);
                    }

                    $('.see-investments-btn').removeClass('active').text('(See investments ▲)');
                    e.text('(Hide investments ▼)');
                    e.addClass('active');
                    scrollToElemId('outside_funds_list', 'slow');
                },
                complete: function() {
                    hideAjaxLoader(selector);
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
            showAjaxLoader(selector);

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
                    hideAjaxLoader(selector);
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('change','input[type="radio"].selected-model', function(){
        var url = $(this).attr('data-url');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response){
                if (response.status == 'success') {
                    $('.model-details').html(response.content);
                }
                if (response.status == 'error') {
                    alert(response.message);
                }
            }
        });
    });

    $(document).on('click',".remove-account-btn", function(){
        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);
    });

    $(document).on('click',".edit-account-btn", function(){
        var selector = $(this).closest('tr').find('.see-investments-btn');
        hideInvestments(selector);
    });
});

function showAjaxLoader(container)
{
    $(container).append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
}

function hideAjaxLoader(container)
{
    $(container).find('.ajax-loader').remove();
}

function showContentInOutsideFundList(content)
{
    $('.outside-funds-list').html(content);
}

function hideInvestments(container)
{
    showContentInOutsideFundList('');
    container.text('(See investments ▲)');
    container.removeClass('active');
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
