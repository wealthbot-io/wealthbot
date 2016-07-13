/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 12:44
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('.select-retirement-account').change(function(event){
        var e = $(this);
        var parent = e.parent();

        parent.append('<img class="ajax-loader pull-right" src="/img/ajax-loader.gif" />');

        $.ajax({
            url: e.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    alert('response.message');
                }

                if (response.status == 'success') {
                    $('.retirement-account-funds').html(response.content);
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('click','div.pagination a', function(event) {
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status == 'success') {
                    $('#tab3').html(response.content);
                }
            }
        });

        event.preventDefault();
    });

});