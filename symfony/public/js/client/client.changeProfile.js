$(function(){
    $(document).on('click','#your_information input[type="submit"]', function(event) {
        var btn = $(this);
        var form = btn.closest('form');

        btn.button('loading');

        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                form.replaceWith(response.form);

                init();
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);

        event.preventDefault();
    });

    $(document).on('click','#update_password .btn-ajax, #manage_users .btn-ajax',function(event){
        var button = this;
        var form = $(button).closest('form');

        $(button).button('loading');

        $(form).ajaxSubmit({
            target:  form.closest('.tab-pane'),
            success: function(responseText, statusText, xhr, $form){
                $(".btn").button('reset');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.manage-users-edit-btn, .manage-users-delete-btn, .manage-users-cancel-edit-btn', function(event){
        var button = $(this);

        button.button('loading');

        $.ajax({
            url: button.attr('href'),
            success: function(response) {
                button.button('reset');
                button.closest('.tab-pane').html(response);
            }
        });
        event.preventDefault();

    });

    $(document).on('click','.change-profile-temp-rebalance-btn, .show-previous-client-portfolio-btn', function(event) {
        var btn = $(this);
        btn.parent().append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: btn.attr('href'),
            success: function(response) {
                if (response.status == 'success') {
                    btn.closest('#your_portfolio').html(response.content);
                    drawModelChart('.pie-chart');
                }

                if (response.status == 'error') {
                    alert('Error: ' + response.content);
                }

                $('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    });

    $(document).on('submit','#choose_another_portfolio_form', function(event) {
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        btn.button('loading');

        form.ajaxSubmit({
            target: $('#your_portfolio'),
            success: function() {
                drawModelChart('.pie-chart');
            }
        });

        event.preventDefault();
    });

});

function ajaxChangeProfile(url) {
    window.counter = 0;
    var elem = $('.actions-list .change-profile-btn:first');
    if (url && elem.length > 0) {
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" />');
        window.counter++;

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#result_container').html(response.content);
                    drawModelCharts('.pie-chart');
                }
            },
            complete: function(){
                parent.find('.ajax-loader').remove();
            }
        });

        event.preventDefault();
    }
}
