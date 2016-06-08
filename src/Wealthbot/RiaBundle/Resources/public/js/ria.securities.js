/**
 * Created with JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.07.13
 * Time: 14:30
 * To change this template use File | Settings | File Templates.
 */

$(function(){
    $('.account-type-input').live('change', function() {
        var is_show_subclasses_priority = $('#categories_tab').attr('data-is-show-subclasses-priority');
        if (is_show_subclasses_priority) {
            rebuildPriority(this);
        }

        var tmp = $(this).val();
        $(this).closest('.account-type-input').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $('.subclass-priority').live('change', function() {
        var tmp = $(this).val();
        $(this).closest('.subclass-priority').find('option').removeAttr('selected');
        $(this).find('option[value="'+tmp+'"]').attr('selected', 'selected');
    });

    $('.subclass input').live('change', function() {
        $(this).attr('value', $(this).val());
    });
});