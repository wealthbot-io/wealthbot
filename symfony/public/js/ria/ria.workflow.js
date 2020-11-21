/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.07.13
 * Time: 15:12
 * To change this template use File | Settings | File Templates.
 */

$(function() {
    $('#modal_dialog').on('hidden', function () {
        var dialog = $(this);
        var dialogBody = dialog.find('.modal-body');

        dialog.find('.save-modal-form-btn').show();
        dialog.find('.cancel-modal-form-btn').text('Cancel');
        dialog.find('.modal-header .bbh3').text('');
        dialogBody.html('');
        dialogBody.css('min-height', '');
        dialogBody.css('height', '');
    });

    $(document).on('change','.update-workflow-status', function() {

        event.preventDefault();

        var elem = $(this);
        var workflowList = elem.closest('.workflow-list').find('tbody');
        var status = elem.val();
        var selectedOption = elem.find('option:selected');
        var color = elem.attr('data-color');
        var newColor = selectedOption.attr('class');

        elem.removeClass(color);
        elem.addClass(newColor);
        elem.attr('data-color', newColor);

        var process = function (archive) {
            archive = archive || null;
            var params = {};

            if (null !== archive) {
                params.archive = archive;
            }

            elem.attr('disabled', 'disabled');
            elem.after('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left:5px;vertical-align:baseline;" />');

            $.ajax({
                url: selectedOption.attr('data-url'),
                dataType: 'json',
                data: params,
                success: function(response) {
                    if (response.message) {
                        elem.removeClass(newColor);
                        elem.addClass(color);
                        elem.attr('data-color', color);
                        elem.find('option.' + color).attr('selected', 'selected');

                        $('.workflow-list').before(getAlertMessage(response.message, response.status));
                    }

                    if (response.status === 'success') {
                        if (response.is_archived && response.content) {
                            var archivedTab = $('#tab_archived').find('.workflow-list tbody');

                            var removedItem = elem.closest('.workflow-item');
                            removedItem.fadeOut('slow', function() {
                                $(this).remove();
                            });

                            archivedTab.before(response.content);
                            archivedTab.find('tr.empty').remove();
                        }

                        if (response.new_item) {
                            var item = $(response.new_item);

                            item.hide();
                            workflowList.append(item);
                            item.fadeIn('slow');
                        }
                    }
                },
                complete: function() {
                    elem.parent().find('.ajax-loader').remove();
                    elem.attr('disabled', false);
                }
            });
        };

        if (status == 3) {
            if (confirm('Do you want to archive this workflow?')) {
                process(1);
            } else {
                process(0);
            }

        } else {
            process(0);
        }
    });

    $(document).on('click','.archive-workflow-btn, .delete-workflow-btn', function(event) {

        event.preventDefault();

        var elem = $(this);
        var btnGroup = elem.closest('.btn-group');
        var dropdown = btnGroup.find('.workflow-actions-btn');

        dropdown.addClass('disabled');
        btnGroup.after('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left:5px;vertical-align:middle;" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    $('.workflow-list').before(getAlertMessage(response.message, response.status));
                }

                if (response.status == 'success') {
                    elem.closest('.workflow-item').remove();

                    if (response.content) {
                        if (elem.hasClass('archive-workflow-btn')) {
                            var archivedTab = $('#tab_archived').find('.workflow-list tbody');

                            archivedTab.before(response.content);
                            archivedTab.find('tr.empty').remove();
                        }
                    }
                }
            },
            complete: function() {
                btnGroup.parent().find('.ajax-loader').remove();
                dropdown.removeClass('disabled');
            }
        });

        event.preventDefault();
    });

    $(document).on('click','.view-workflow-btn', function(event) {

        event.preventDefault();

        var elem = $(this);
        var parent = elem.parent();

        parent.append('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left:5px;vertical-align:middle;" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'error' && response.message) {
                    alert(response.message);
                }

                if (response.status == 'success') {
                    if (response.content) {
                        $('#modal_dialog .modal-header h3').html('Closed accounts');
                        $('#modal_dialog .modal-body').html(response.content);
                        $('#modal_dialog').modal('show');
                        $('#modal_dialog .save-modal-form-btn').hide();
                    }

                    if (response.redirectUrl) {
                        location.href = response.redirectUrl;
                    }
                }
            },
            complete: function() {
                parent.find('.ajax-loader').remove();
            }
        });
    });

    $(document).on('click','.ajax-pagination li:not(.active) > a, table > thead > tr > th > a', function(event) {

        event.preventDefault();

        var btn = $(this);
        var block = btn.closest('.tab-pane');
        var active_tab = block.attr('data-tab');

        block.html('<img class="ajax-loader" src="/img/ajax-loader.gif">');

        $.ajax({
            url: btn.attr('href'),
            dataType: 'json',
            data: {'with_layout': 0, 'tab': active_tab },
            success: function(response) {
                if (response.status == 'success') {
                    block.html(response.content);
                }
            }
        });
    });

    $(document).on('click','.edit-workflow-note-btn', function(event) {
        event.preventDefault();

        var elem = $(this);
        var btnGroup = elem.closest('.btn-group');
        var dropDown = btnGroup.find('.workflow-actions-btn');
        var dialog = $('#modal_dialog');

        dropDown.addClass('disabled');
        btnGroup.after('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left:5px;vertical-align:middle;" />');

        $.ajax({
            url: elem.attr('href'),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    if (response.message) {
                        alert(response.message);
                    }
                }

                if (response.status === 'success') {
                    dialog.find('.modal-header h3').html('Notes');
                    dialog.find('.modal-body').html(response.content);
                    dialog.modal('show');
                }
            },
            complete: function() {
                btnGroup.parent().find('.ajax-loader').remove();
                dropDown.removeClass('disabled');
            }
        });
    });

    $(document).on('submit','#workflow_note_form', function(event) {
        event.preventDefault();
        var form = $(this);
        var dialog = $('#modal_dialog');
        var btn = dialog.find('.save-modal-form-btn');

        btn.button('loading');

        var options = {
            url: form.attr('action'),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error') {
                    if (response.message) {
                        alert(response.message);
                    }

                    if (response.content) {
                        form.replaceWith(response.content);
                    }
                }

                if (response.status === 'success') {
                    dialog.find('.modal-body').html('');
                    dialog.modal('hide');
                }
            },
            complete: function() {
                btn.button('reset');
            }
        };

        form.ajaxSubmit(options);
    });

    $(document).on('click','.delete-activity-summary', function(event) {
        event.preventDefault();
        var btn = $(this);
        var url = btn.attr('href');
        var block = btn.closest('table').parent();
        var showPagination = (block.find('.pagination').length > 0);

        btn.replaceWith('<img class="ajax-loader" src="/img/ajax-loader.gif" class="pull-right" />');

        $.ajax({
            url: url,
            data: { show_pagination: showPagination },
            dataType: 'json',
            success: function (response) {
                if(response.status == 'success') {
                    block.html(response.content);
                }
            }
        });
    });

    $(document).on('click','.show-workflow-documents-list', function (event) {
        event.preventDefault();
        var elem = $(this);
        var dialog = $('#modal_dialog');
        var dialogBody = dialog.find('.modal-body');

        dialog.find('.save-modal-form-btn').hide();
        dialog.find('.cancel-modal-form-btn').text('Close');
        dialog.find('.modal-header .bbh3').text('Envelope Applications');
        dialogBody.css('height', 'auto');
        dialogBody.css('min-height', '50px');

        dialog.modal('show');
        dialogBody.spinner32();

        $.ajax({
            url: elem.attr('data-url'),
            dataType: 'json',
            success: function(response) {
                var content;
                if (response.status == 'error') {
                    content = '<p>' + response.message + '</p>';
                } else {
                    content = response.content;
                }

                dialogBody.html(content);
            },
            complete: function() {
                dialogBody.spinner32(false);
            }
        })
    });
});

