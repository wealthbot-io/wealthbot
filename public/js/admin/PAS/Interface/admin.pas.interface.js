$(function(){

    var updateStatuses = function(){
        var allFiles = $('.admin-pas-interfaces .pas-data .pas-all-files tbody');
        var fields = allFiles.find('td[data-type="status"]');
        fields.each(function() {
            var $field = $(this);
            if ($field.text() == 'Received') {
                $field.css('background', '#AAFFBB');
            } else {
                $field.css('background', '#FFAABB');
            }
        });
    };

    updateStatuses();

    var dateUpdated = function(){
        var date = $(this).val();
        if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.exec(date)) {
            date = date.replace(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/, "$3-$1-$2");
            document.location.href = Routing.generate(App.Var.curRoute, {date: date});
        } else {
            $(this).css('border', 'solid 2px red');
        }
    };

    $(document).on('change','#on_date', dateUpdated);
});


App.module('Wealthbot.Admin.Pas.Interface', function(Mod, App, Backbone, Marionette, $) {

    Mod.startWithParent = false;

    // Module layout
    Mod.Layout = Marionette.Layout.extend({
        regions: {
            transactionRegion: '#transactionApp'
        }
    });

    // Module constructor
    Mod.addInitializer(function() {
        // Module layout
        Mod.layout = new Mod.Layout({ el: '#pasInterfaceApp' });
    });

});