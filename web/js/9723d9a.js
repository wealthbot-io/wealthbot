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
App.module('Wealthbot.Admin.Pas.Transaction', function(Mod, App, Backbone, Marionette, $) {

    Mod.startWithParent = false;

    // Transaction model
    Mod.TransactionModel = Backbone.Model.extend({});

    // Transaction collection
    Mod.TransactionCollection = Backbone.Collection.extend({
        model: Mod.TransactionModel
    });

    Mod.TransactionModalView = Backbone.View.extend({
        el: '#transactionModal',

        events: {
            'submit form': 'formSubmit',
            'hide': 'hideModal'
        },

        onShow: function() {
            this.$el.modal('show');

            _.each(this.$('[data-role="autocomplete"]'), function(el) {
                $(el).typeahead({
                    source: function (query, process) {
                        return $.get(Routing.generate($(el).data('route')), { query: query }, function (data) {
                            return process(data);
                        });
                    },
                    items: 10,
                    minLength: 1
                });
            });
        },

        formSubmit: function(e) {
            e.preventDefault();

            var self = this;

            ErrorHandler.clearErrors();

            $.ajax({
                url: Routing.generate('rx_admin_pas_interfaces_transaction_create', { date: App.Var.date }),
                data: this.$('form').serialize(),
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    self.$('#inside').spinner32(true);
                },
                success: function (response) {
                    Util.statusMessage('Success', 'Transaction has been created.');

                    Mod.transactionCollection.add(response);
                    self.$el.modal('hide');
                    self.resetForm();
                },
                complete: function () {
                    self.$('#inside').spinner32(false);
                },
                error: function(xnr) {
                    var response = JSON.parse(xnr.responseText);
                    ErrorHandler.handle(response, self.$('form'), {});
                }
            });
        },

        resetForm: function() {
            this.$('form')[0].reset();
        },

        hideModal: function() {
            this.$el.unbind();
        }
    });

    // Transaction item view
    Mod.TransactionItemView = Marionette.ItemView.extend({
        tagName: 'tr',
        template: '#tpl_transactionItem',

        onShow: function() {
            var status = this.model.get('status');

            $("select option").filter(function() { return $(this).val() == status; }).attr('selected', true);
        }
    });

    // Transaction view
    Mod.TransactionView = Marionette.CompositeView.extend({
        template: '#tpl_transactions',
        itemView: Mod.TransactionItemView,
        itemViewContainer: 'tbody'
    });

    // Module layout
    Mod.Layout = Marionette.Layout.extend({
        template: '#tpl_transactionLayout',

        regions: {
            listRegion: '#transactionList'
        },

        events: {
            'click #btnAddTransaction': 'clickAddTransaction'
        },

        onShow: function() {
            this.listRegion.show(Mod.transactionView);
        },

        clickAddTransaction: function(e) {
            e.preventDefault();

            var view = new Mod.TransactionModalView();
            view.onShow();
        }
    });

    // Module constructor
    Mod.addInitializer(function() {
        // Collection
        Mod.transactionCollection = new Mod.TransactionCollection();

        // View
        Mod.transactionView = new Mod.TransactionView({ collection: Mod.transactionCollection });
        Mod.transactionCollection.reset(App.Var.transactions);

        // Layout
        Mod.layout = new Mod.Layout();

        App.Wealthbot.Admin.Pas.Interface.layout.transactionRegion.show(Mod.layout);
    });
});