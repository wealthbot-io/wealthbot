/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.06.13
 * Time: 19:24
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('.questions-list').sortable({
        handle: 'li',
        disableNesting: 'q-row',
        update: function(event, ui) {
            var order = $(this).sortable('serialize');

            $.ajax({
                url: '{{ path("rx_admin_risks_update_order") }}',
                data: order,
                dataType: 'json',
                success: function(response){
                }
            });
        }
    });

    $(document).on('click','.create-question-btn',function(event){
        var e = $(this);
        var questionsCount = $('.questions-list').find('li.q-row').length;

        if (questionsCount < 10) {
            var container = e.closest('.new-question');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'success') {
                        $('.q-info').show();
                        $('.q-form').remove();
                        e.hide();

                        container.append(response.content);
                    }
                }
            });
        } else {
            alert('You can only have up to 10 questions.');
        }

        event.preventDefault();
    });

    $(document).on('click','.edit-question-btn', function(event){
        var e = $(this);
        var content_selector = e.closest('.q-row');

        $.ajax({
            url: e.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    alert('Error: '+response.message);
                }
                if (response.status == 'success') {
                    $('.q-info').show();
                    $('.q-form').remove();

                    content_selector.find('.q-info').hide();
                    content_selector.append(response.content);
                }
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.delete-question-btn', function(event){
        if (confirm('Are you sure?')) {
            var e = $(this);
            var content_selector = e.closest('.q-row');

            $.ajax({
                url: e.attr('href'),
                dataType: 'json',
                success: function(response) {
                    if (response.status == 'error') {
                        alert('Error: '+response.message);
                    }
                    if (response.status == 'success') {
                        content_selector.remove();
                    }
                }
            });
        }

        event.preventDefault();
    });

    $(document).on('click','.cancel-edit-btn', function(event){
        var e = $(this);
        var form_selector = e.closest('.q-form');
        var content_selector = form_selector.parent();

        form_selector.remove();

        // for edit form
        content_selector.find('.q-info').show();
        // for create form
        content_selector.find('.create-question-btn').show();

        event.preventDefault();
    });

    $(document).on('submit','#question_form', function(event){
        var form = $(this);
        var form_container = form.closest('.q-form');

        var options = {
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error') {
                    form_container.replaceWith(response.form);
                }
                if (response.status == 'success') {
                    // for create form
                    if (response.new_row) {
                        $('.questions-list').append(response.new_row);
                    }
                    // for edit form
                    if (response.content) {
                        form_container.parent().replaceWith(response.content);
                    }

                    $('.q-info').show();
                    $('.q-form').remove();
                    $('.create-question-btn').show();
                }
            }
        };

        form.ajaxSubmit(options);
        event.preventDefault();
    });

    $(document).on('click','.btn-add', function(event) {
        var collectionHolder = $('.answers-list');
        var itemsCount = collectionHolder.find('li').length;

        if (itemsCount < 5) {
            var prototype = collectionHolder.attr('data-prototype');
            var form = prototype.replace(/__name__/g, collectionHolder.children().length);
            collectionHolder.append(form);
        } else {
            alert('You can only have up to 5 answers.')
        }

        event.preventDefault();
    });
    $(document).on('click','.btn-remove', function(event) {
        var name = $(this).attr('data-related');
        var prev = $('*[data-content="'+name+'"]').prev();
        if(prev) $(prev).find("a.btn-remove").show();
        $('*[data-content="'+name+'"]').remove();
        event.preventDefault();
    });
});