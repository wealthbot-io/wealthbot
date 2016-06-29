/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:30
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $(document).on('change','.account-type-input', function() {
        var is_show_subclasses_priority = $('#categories_tab').attr('data-is-show-subclasses-priority');
        if (is_show_subclasses_priority) {
            rebuildPriority(this);
        }

        var tmp = $(this).val();
        $(this).closest('.account-type-input').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $(document).on('change','.subclass-priority', function() {
        var tmp = $(this).val();
        $(this).closest('.subclass-priority').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $(document).on('change','.subclass input', function() {
        $(this).attr('value', $(this).val());
    });
});