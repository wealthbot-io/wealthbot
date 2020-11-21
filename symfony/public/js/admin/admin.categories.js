/**
 * Created with JetBrains PhpStorm.
 * User: maksim
 * Date: 12.04.13
 * Time: 12:27
 * To change this template use File | Settings | File Templates.
 */

// Element where we store prototypes for assets and subclasses. Also it is holder of elements.
var assetCollectionHolder = $('#asset_collections');
var isUpdated = false;

$(function(){
    window.onbeforeunload = function (evt) {
        if (isUpdated) {
            var message = "Categories were not saved. Are you sure want to leave this page?";
            if (typeof evt == "undefined") {
                evt = window.event;
            }
            if (evt) {
                evt.returnValue = message;
            }

            return message;
        }
    };

    $(document).on('click','a.add-asset', function(event){
        event.preventDefault();
        addAsset();
    });

    $(document).on('submit',"#categories_form", function(event){
        event.preventDefault();
        var button = $(this).find("input[type=submit]");
        button.button('loading');
        $(this).ajaxSubmit({
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    window.onbeforeunload = function(evt) { evt = window.event };
                    location.href = response.success_url;
                }
                if(response.status == 'form'){
                    $("#categories_tab").html(response.content);
                }
            }
        });
    });
});


function addAsset()
{
    isUpdated = true;
    var assetCollectionHolder = $('#asset_collections');
    var prototype = assetCollectionHolder.attr('data-prototype');
    var form = prototype.replace(/__name__/g, assetCollectionHolder.find('.assets').length);
    assetCollectionHolder.append(form);
}

function addSubclass(holder, assetIndex)
{
    isUpdated = true;
    var assetCollectionHolder = $('#asset_collections');
    holder = $(holder).closest('tr');
    var index = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]').length;
    index = searchSubclassFreeIndex(assetIndex, index);
    var prototype = assetCollectionHolder.attr('data-subclass-prototype');
    form = prototype.replace(/__name__/g, assetIndex);
    form = form.replace(/__index__/g, index);

    if(index != 0){
        var lastSubclassRow = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]').last();
        lastSubclassRow.after(form);
    }else{
        holder.after(form);
    }

    return false;
}

function deleteAsset(holder, assetIndex, is_exist)
{
    isUpdated = true;
    var url = $(holder).attr('href');

    holder = $(holder).closest('tr');
    subclasses = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_"]');

    holder.remove();
    subclasses.remove();
    return false;
}

function deleteSubclass(holder, is_exist)
{
    isUpdated = true;
    var url = $(holder).attr('href');
    holder = $(holder).closest('tr');

    holder.remove();
    return false;
}

function searchSubclassFreeIndex(assetIndex, index)
{
    var subclassRow = assetCollectionHolder.find('tr[id^="asset_'+ assetIndex +'_subclass_'+index+'"]');
    if(subclassRow.length){
        return searchSubclassFreeIndex(assetIndex, ++index);
    }
    return index;
}

function rebuildPriority(element) {
    $('.subclass-priority').find('option').remove();

    var priorities = [];

    $('.account-type-input').each(function(i, element) {
        var current_account_type = $(element).selected().val();

        if (priorities[current_account_type] == undefined) {
            priorities[current_account_type] = 1;
        } else {
            priorities[current_account_type]++;
        }
    });

    var priorities_select = [];
    $('.account-type-input').each(function(i, element) {
        var current_account_type = $(element).selected().val();
        if (priorities_select[current_account_type] == undefined) {
            priorities_select[current_account_type] = 1;
        } else {
            priorities_select[current_account_type]++;
        }

        for(var j=1;j<=priorities[current_account_type];j++) {
            var html;
            if (priorities_select[current_account_type] == j) {
                html = '<option value="'+j+'" selected="selected">'+j+'</option>';
            } else {
                html = '<option value="'+j+'">'+j+'</option>';
            }

            $(element).closest('tr').find('.subclass-priority').append(html);
        }
    });
}

function buildSortedDomForSecurity(dom, accountTypes, currentValue) {
    for (var i=0;i<accountTypes.length;i++) {
        if (accountTypes[i][2] == currentValue) {
            if (dom[accountTypes[i][0]] == undefined) {
                dom[accountTypes[i][0]] = [];
            }

            var html = '<tr id="asset_'+accountTypes[i][0]+'_subclass_'+accountTypes[i][1]+'_row" class="asset-'+accountTypes[i][0]+' subclass" data-asset-index="'+accountTypes[i][0]+'" data-subclass-index="'+accountTypes[i][1]+'">';
            html += $('#asset_'+accountTypes[i][0]+'_subclass_'+accountTypes[i][1]+'_row').html();
            html += '</tr>';

            dom[accountTypes[i][0]].push(html);
        }
    }
    return dom;
}

function orderByAccountType(btn) {
    var accountTypes = [];
    var order = $(btn).attr('data-order');

    $('.subclass').each(function(i, element) {
        var asset_index = parseInt($(element).attr('data-asset-index'));
        var subclass_index = parseInt($(element).attr('data-subclass-index'));
        var account = $(element).find('.account-type-input');
        var account_type = parseInt(account.selected().val());

        if (isNaN(account_type)) {
            account_type = 0;
        }

        var row = [asset_index, subclass_index, account_type];
        accountTypes.push(row);

    });

    var dom = [];
    if (order == 'ASC') {
        for (var j=0; j<4; j++) {
            dom = buildSortedDomForSecurity(dom, accountTypes, j);
        }
        $(btn).attr('data-order', 'DESC');
    } else {
        for (var j=3; j>=0; j--) {
            dom = buildSortedDomForSecurity(dom, accountTypes, j);
        }
        $(btn).attr('data-order', 'ASC');
    }

    for (var i in dom) {
        $('.asset-'+i).remove();
        var html = '';
        for (var j=0;j<dom[i].length;j++) {
            html += dom[i][j];
        }
        $('#categories_assets_'+i+'_name').closest('tr').after(html);
    }
}