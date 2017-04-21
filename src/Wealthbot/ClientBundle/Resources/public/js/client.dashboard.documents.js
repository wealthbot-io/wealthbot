$(function() {
    $(document).on('submit','#client_document_upload_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({
            dataType: 'json',
            type: 'POST',
            success: function(response) {
                $('#ria_dashboard_client_content').html(response.content);
            },
            complete: function() {
                btn.button('reset');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.client-document-delete-btn, #ria_dashboard_client_content table thead a', function(event) {
        var btn = $(this);

        btn.html('<img class="ajax-loader" src="/img/ajax-loader.gif">');
        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            success: function(response){
                if(response.status == 'success'){
                    $('#ria_dashboard_client_content').html(response.content);
                }
                if(response.status == 'error'){
                    btn.html('X');
                    alert('Error: '+response.message);

                }
            }
        });

        event.preventDefault();
    });
});