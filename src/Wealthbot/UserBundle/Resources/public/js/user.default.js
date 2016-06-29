$(function(){
    $(document).on('submit','#search_ria_form', function(event){
        var form = $(this);

        form.spinner128();
        var options = {
            dataType: 'json',
            type: 'POST',
            success: function(response){
                form.spinner128(false);
                if(response.status == 'success'){
                    $('#ria_search_results').html(response.content);
                } else {
                    $('#ria_search_results').html('');
                }
            },
            error: function(){
                form.spinner128(false);
                alert('Something bad with response :(');
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });


    var selectCheck = function(){
        if ($('#form_is_not_locate').is(':checked')) {
            $('select[name="form[state]"]').attr('disabled', true);
        }else{
            $('select[name="form[state]"]').attr('disabled', false);
        }
    };

    $(document).on('click','#form_is_not_locate',function(){
        selectCheck();
        $(this).closest('form').submit();
    });
    selectCheck();
});
