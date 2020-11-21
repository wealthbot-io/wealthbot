/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:10
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('click','table.prospects > thead a', function(event) {
        var btn = $(this);

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status = 'success') {
                    $('#tab_prospects').html(response.content);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#invite_prospect_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');
        var prospectTab = $('#tab_prospects');

        btn.button('loading');

        form.ajaxSubmit({
            dataType: 'json',
            success: function(response) {

                var status_html = getAlertMessage(response.status_message, response.status);
                form.html(status_html + response.content);

                if (response.prospectsList) {
                    prospectTab.html(response.prospectsList);
                }
            },
            complete: function() {
                btn.button('reset');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','a.create-client-lnk',function(event){
        var elem = $(this);

        changeProgressStatus(true);
        $.ajax({
            url: elem.attr('data-is-can-create-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $.ajax({
                        url: elem.attr('data-url'),
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                $('#tab3 .create-prospect-content').html(response.content);
                                changeProgressStatus(false);
                                createClientAjaxForm();
                            } else {
                                $('#tab3 .create-prospect-content').html('error');
                                changeProgressStatus(false);
                            }
                        }
                    });
                } else {
                    $('#tab3 .create-prospect-content').html(response.message);
                    changeProgressStatus(false);
                }
            }
        });

        event.preventDefault();
    });

    $('#delete_clients_batch_form').submit(function (event) {

        event.preventDefault();

        if (confirm('Are you sure?')) {
            var form = $(this);
            var btn = form.find('input[type="submit"]');

            btn.button('loading');

            form.ajaxSubmit({
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        form.find('input.delete-client-batch-checkbox:checked').each(function(key, element) {
                            $(element).closest('tr').remove();
                        });
                    }
                },
                complete: function() {
                    btn.button('reset');
                }
            });
        }
    });
});

function changeProgressStatus(isProgress) {
    if (isProgress) {
        $('#tab3 .progress').show();
        $('#tab3 .create-prospect-content').hide();
    } else {
        $('#tab3 .progress').hide();
        $('#tab3 .create-prospect-content').show();
    }
}

function createClientAjaxForm() {
    $("#create_client_form_id").ajaxForm({
        method: 'post',
        dataType: 'html',
        beforeSubmit: function(arr, form, options) {
            $(".form-actions .btn").hide();
            $(".form-actions .progress").show();
        },
        success: function(response){
            $("#create_client_form_id").replaceWith(response);
        }
    });
}
