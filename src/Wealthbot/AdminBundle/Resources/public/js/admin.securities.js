/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 13:19
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('change','#wealthbot_admin_security_type_assetClass', function(){
        var form = $(this).closest('form');
        var subclassSelector = form.find('#wealthbot_admin_security_type_subclass');

        subclassSelector.attr('disabled', 'disabled');

        var options = {
            url: form.attr('data-update-subclass-url'),
            dataType: 'json',
            type: 'POST',
            success: function(response){
                subclassSelector.closest('td').html(response.content);
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('submit','#edit_security_form', function(event){
        var form = $(this);
        var form_container = $('.security-form');
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                if(response.status == 'success'){
                    var id = response.id;
                    var container = $('.securities-list tr.security-'+id);
                    var index = parseInt(container.find('.index').text());

                    container.replaceWith(response.content);
                    $('.securities-list tr.security-'+id).find('.index').text(index);

                    form_container.html(response.form);
                }
                if(response.status == 'error'){
                    form_container.html(response.form);
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.delete-security-btn', function(event){
        if(confirm('Are you sure?')) {
            var e = $(this);
            var parent = e.parent();

            parent.append('<img src="/img/ajax-loader.gif" class="ajax-loader" style="margin-left:5px;">');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function(response){
                    if(response.status == 'success'){
                        e.closest('tr').remove();
                    }
                    if(response.status == 'error'){
                        alert(response.message);
                    }
                },
                complete: function() {
                    parent.find('.ajax-loader').remove();
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.edit-security-btn', function(event){
        var e = $(this);
        var parent = e.parent();
        var form_container = $('.security-form');

        parent.append('<img src="/img/ajax-loader.gif" class="ajax-loader" style="margin-left:5px;">');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    form_container.html(response.form);
                    scrollToElemId('edit_security_form', 'slow');
                }
                if(response.status == 'error'){
                    alert(response.message);
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-security-btn', function(event){
        var elem = $(this);
        var form_container = $('.security-form');

        elem.button('loading');

        $.ajax({
            url: elem.attr('data-url'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    form_container.html(response.form);
                }
                if(response.status == 'error'){
                    alert(response.message);
                }
            },
            complete: function() {
                elem.button('reset');
            }
        });

        event.preventDefault();
    });

    /*$(document).on('click','.show-price-history-btn', function(event) {
        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img src="/img/ajax-loader.gif" class="ajax-loader" style="margin-left:5px;">');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('.price-history').html(response.content);
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });*/

    $(document).on('click','.security-price-history-batch-btn', function(event) {
        var btn = $(this);
        var parent = btn.parent();
        var form = btn.closest('form');
        var container = form.find('.price-history-list');

        var process = function() {
            parent.append('<img src="/img/ajax-loader.gif" class="ajax-loader" style="margin-left:5px;">');

            var options = {
                url: btn.attr('data-url'),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        container.replaceWith(response.content);
                    }
                },
                complete: function() {
                    parent.find('.ajax-loader').remove();
                }
            };

            form.ajaxSubmit(options);
        };

        if (btn.hasClass('delete-btn')) {
            if (confirm('Are you sure?')) {
                process();
            }
        } else {
            process();
        }

        event.preventDefault();
    });
});