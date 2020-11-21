/**
 * Created with JetBrains PhpStorm.
 * User: wealthbotdev1
 * Date: 11/8/13
 * Time: 12:33 PM
 * To change this template use File | Settings | File Templates.
 */
$(function(){
    $("#stop_tlh_form").on("submit", function(event){
        var form = $(this);
        var btn = form.find('input[type="submit"]');

        var options = {
            url: form.attr("action"),
            success: function(response){
                var message;
                if (response.status === "error") {
                    var parent = form.parent();

                    form.replaceWith(response.content);
                    message = "Some errors has occurred";

                    form = parent.find("#stop_tlh_form");
                } else {
                    message = "Data was updated successfully";
                }
                form.prepend(getAlertMessage(message, response.status));
            },
            complete: function(){
                btn.button("reset");
                btn.removeClass('loading');
                btn.hide();
            }

        };

        btn.button("loading");
        btn.addClass('loading');


        form.ajaxSubmit(options);
        event.preventDefault();
    });


    $('#stop_tlh_form_stop_tlh_value, #stop_tlh_form input[type="submit"]').on("focus blur", function(event){
        var form = $(this).closest("form");
        var btn = form.find('input[type="submit"]');
        var input = form.find('#stop_tlh_form_stop_tlh_value');

        if (!input.is(':focus') && (!btn.is(':focus') && !btn.hasClass('loading'))) {
            btn.hide();
        } else {
            btn.show();
        }
    });

});