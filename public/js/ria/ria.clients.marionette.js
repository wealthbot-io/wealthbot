"use strict";

var App = new Backbone.Marionette.Application();

App.module('Wealthbot.Clients', function(Mod, App, Backbone, Marionette, $) {

    // Client account model
    Mod.AccountModel = Backbone.Model.extend({
        defaults: {
            id: 0,
            status: '',
            lastName: '',
            firstName: '',
            accountType: '',
            number: '',
            ceModels: '',
            totalValue: 0
        }
    });

    // Client account collection
    Mod.AccountCollection = Backbone.Collection.extend({
        model: Mod.AccountModel,

        sorting: {
            status: 'str',
            lastName: 'str',
            firstName: 'str',
            accountType: 'str',
            number: 'str',
            custodian: 'str',
            ceModels: 'str',
            totalValue: 'float'
        }
    });

    // Client model
    Mod.ClientModel = Backbone.Model.extend({
        defaults: {
            id: 0,
            status: '',
            lastName: '',
            firstName: '',
            advisorSet: '',
            billingSpec: 0,
            totalValue: 0,
            custodian: '',
            hasClosedAccounts: false,
            ceModels: '',
            accounts: []
        },

        initialize: function () {
            var accounts = new Mod.AccountCollection(this.get('accounts'));
            this.set('accounts', accounts);
        }
    });

    // Client collection
    Mod.ClientCollection = Backbone.Collection.extend({
        model: Mod.ClientModel,
        url: Routing.generate('rx_ria_dashboard_ajax_clients'),

        sorting: {
            status: 'str',
            lastName: 'str',
            firstName: 'str',
            advisorSet: 'str',
            billingSpec: 'str',
            ceModels: 'str',
            totalValue: 'float'
        }

    });

    // Client account item view
    Mod.AccountView = Marionette.ItemView.extend({
        template: '#tplAccountView',
        tagName: 'tr',
        className: 'account',

        events: {
            'click .account-transactions': 'accountTransactions',
            'click .account-settings': 'accountSettings'
        },

        accountTransactions: function(event) {
            event.preventDefault();
            window.location.href = Routing.generate('rx_ria_dashboard_show_client', {
                client_id: $(event.currentTarget).data('client-id'),
                account_id: $(event.currentTarget).data('id'),
                action: 'Transactions',
                showBreadcrumbs: 1
            });
            return false;
        },

        accountSettings: function(event) {
            event.preventDefault();
            $('.modal-account-settings').modal();

            $('.modal-account-settings .clientName').html(
                '<span class="h3">' + this.model.get('firstName') + ' ' +
                this.model.get('lastName') + '</span>&nbsp;&nbsp;' +
                '<span class="h4">' + this.model.get('accountType') + '</span>&nbsp;&nbsp;'
            );

            var params = {'account_id': this.model.get('id')};
            $('#account-settings').load(Routing.generate('rx_ria_dashboard_account_settings', params));

            return false;
        },

        initialize: function() {
            this.$el.addClass(this.model.get('status'));
        }
    });

    // Client item view
    Mod.ClientView = Marionette.CompositeView.extend({
        template: '#tplClientView',
        itemView: Mod.AccountView,
        tagName: 'tbody',

        events: {
            'click .client-dashboard': 'clientDashboard',
            'click .client-settings': 'clientSettings'
        },

        clientDashboard: function(event) {
            event.preventDefault();
            window.location.href = Routing.generate('rx_ria_dashboard_show_client', {
                client_id: $(event.currentTarget).data('id'),
                showBreadcrumbs: 1
            });
            return false;
        },

        clientSettings: function(event) {
            event.preventDefault();
            $('.modal-client-settings').modal();

            var params = {'client_id': this.model.id};
            $('a[href="#personal"]').tab('show');
            $('#household-close').load(Routing.generate('rx_ria_dashboard_household_close', params));
            $('#personal')
                .addClass('active')
                .load(Routing.generate('rx_ria_dashboard_household_settings_personal', params), function() {
                    $('#client_personal_settings_employmentStatus input:radio:checked,' +
                        '#household_spouse_form_employmentType input:radio:checked,' +
                        '#client_personal_settings_maritalStatus')
                        .trigger('change');
                });
            $('#contact').load(Routing.generate('rx_ria_dashboard_household_settings_contact', params));
            $('#billing').load(Routing.generate('rx_ria_dashboard_household_settings_billing', params));
            $('#portfolio').load(Routing.generate('rx_ria_dashboard_household_settings_portfolio', params));

            return false;
        },

        initialize: function() {
            var that = this;
            var accounts = this.model.get('accounts');
            accounts.each(function(account) {
                account.set('client_id', that.model.get('id'));
                if (account.get('status') == 'Closed account') {
                    that.$el.addClass('Visible');
                }
            });

            this.collection = accounts;
        }
    });

    // Clients view
    Mod.ClientCollectionView = Marionette.CompositeView.extend({
        template: '#tplClientsTable',
        itemView: Mod.ClientView,
        itemViewContainer: 'table',

        events: {
            'click .sortable': 'clickSortable'
        },

        clickSortable: function(event){
            event.preventDefault();

            var $el = $(event.currentTarget);
            var name = $el.attr('data-sortable');

            CollectionSorter.autoSortView(this, event);

            this.collection.each(function(client) {
                var accounts = client.get('accounts');
                CollectionSorter.sort(accounts, name);
            });

            return false;
        }
    });

    Mod.HeaderView = Marionette.CompositeView.extend({
        template: '#tplClientsHeader',

        events: {
            'click #filter-by-client-name + i': 'clearFilter',
            'change #filter-by-type': 'filterByType',
            'change #filter-by-status': 'filterByStatus'
        },

        clearFilter: function() {
            $('#filter-by-client-name').val('').trigger('keyup').focus();
            Mod.filterByClientName();
            $('#client-name').text('');
        },

        filterByType: function (event) {
            $('#clients')
                .removeClass('clients accounts')
                .addClass($('#filter-by-type').val());
            if ($('#filter-by-client-name').val() != '') {
                this.filterByClientName(event);
            }
        },

        filterByStatus: function () {
            var status = $('#filter-by-status').val();

            $('#clients')
                .removeClass('active closed')
                .addClass(status);
            if ($('#filter-by-client-name').val() != '') {
                this.filterByClientName(event);
            }
        }
    });

    Mod.getClients = function()
    {
        var url = Routing.generate('rx_ria_dashboard_ajax_clients');

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            beforeSend: function() {
                $('#clients').spinner128();
            },
            success: function(json) {
                Mod.clients = json;
                Mod.clientCollection.reset(json);
            },
            complete: function() {
                $('#clients').spinner128(false);
            }
        });
    };

    Mod.showSearch = function()
    {
        var $input = $('#filter-by-client-name'),
            $i = $input.siblings('i'),
            name = $input.val();

        if ('' == name) {
            $i.hide();
        } else {
            $i.show();
        }
    };

    Mod.filterByClientName = function() {
        var $input = $('#filter-by-client-name'),
            names = $input.val().split(' '),

            filterByName = function(client) {

                function contains(haystack, needle) {
                    return haystack.toLowerCase().indexOf(needle.toLowerCase()) != -1;
                }

                var condition = true;
                for (var i in names) {
                    if (names.hasOwnProperty(i)) {
                        var name = names[i].replace(/\W/g,'');
                        if (name != '')
                        {
                            condition = condition &&
                            (contains(client.get('firstName'), name) ||
                            contains(client.get('lastName'), name));
                        }
                    }
                }
                return condition;
            };

        Mod.clientCollection.reset(Mod.clients);
        var clients = Mod.clientCollection.filter(filterByName);
        Mod.clientCollection.reset(clients);
    };

    // Module constructor
    Mod.addInitializer(function() {
        Mod.clientCollection = new Mod.ClientCollection();
        Mod.getClients();

        Mod.clientCollectionView = new Mod.ClientCollectionView({
            collection: Mod.clientCollection
        });

        App.addRegions({
            clients: '#clients',
            header: '#header'
        });
        App.clients.show(Mod.clientCollectionView);
        App.header.show(new Mod.HeaderView);

        addCompleteTransferCustodianEvent(
            '#filter-by-client-name',
            '',
            function(item)
            {
                $('#filter-by-client-name').val(item.name);
                $('#client-name').text(item.name);
                Mod.filterByClientName();
            }
        );

        $('#filter-by-client-name').keyup(function() {
            Mod.showSearch();
            Mod.filterByClientName();
        });

        $(document).on('click','.modal-account-settings .save-modal-form-btn',function (event)
        {
            event.preventDefault();
            var activeModalForm = $(this)
                .parents('.modal')
                .find('form');
            if (activeModalForm.length) {
                var submitButton = $(this);

                submitButton
                    .button('loading')
                    .append('<img class="ajax-loader" src="/img/ajax-loader.gif">');

                activeModalForm.ajaxSubmit({
                    target: $('#account-settings'),
                    success: function ()
                    {
                        Mod.getClients();
                    },
                    complete: function() {
                        submitButton
                            .empty().text('Save');
                    }
                });
            }

            return false;
        });

        $(document).on('click','.modal-client-settings .save-modal-form-btn',function (event)
        {
            event.preventDefault();
            var $closeHouseholdForm = $('#household-close form');
            var $activeModalForm = $(this)
                .parents('.modal')
                .find('.tab-pane.active form');
            if ($activeModalForm.length) {
                var submitButton = $(this);

                submitButton
                    .button('loading')
                    .append('<img class="ajax-loader" src="/img/ajax-loader.gif">');

                $closeHouseholdForm.ajaxSubmit({
                    target: $closeHouseholdForm.parent()
                });

                $activeModalForm.ajaxSubmit({
                    target: $('.modal-client-settings .tab-pane.active'),
                    success: function ()
                    {
                        $('#client_personal_settings_employmentStatus input:radio:checked,' +
                            '#household_spouse_form_employmentType input:radio:checked,' +
                            '#client_personal_settings_maritalStatus')
                            .trigger('change');
                        Mod.getClients();
                    },
                    complete: function() {
                        submitButton
                            .empty().text('Save');
                    }
                });
            }

            return false;
        });

        $(document).on('change','#client_personal_settings_employmentStatus input:radio', function() {
            var employed = ('Employed' == $(this).val() || 'Self-Employed' == $(this).val());
            var employmentDiv = $('#user-employment');

            if (employed) {
                employmentDiv.show();
            } else {
                employmentDiv.hide();
            }
        });

        $(document).on('change','#household_spouse_form_employmentType input:radio', function() {
            var employed = ('Employed' == $(this).val() || 'Self-Employed' == $(this).val());
            var spouseEmploymentDiv = $('#spouse-employment');

            if (employed) {
                spouseEmploymentDiv.show();
            } else {
                spouseEmploymentDiv.hide();
            }
        });

        $(document).on('change','#client_personal_settings_maritalStatus', function() {
            var married = 'Married' == $('#client_personal_settings_maritalStatus').val();
            var spouseDataDiv = $('#spouse-data');

            if (married) {
                spouseDataDiv.show();
            } else {
                spouseDataDiv.hide();
            }
        });

        $('.jq-ce-date').on('focusin', function() {
            $(this).inputmask("99-99-9999");

            $(this).datepicker({
                yearRange: "1900:+0",
                dateFormat: "mm-dd-yyyy",
                changeMonth: true,
                changeYear: true
            });
        });
    });
});

if ($('#tplClientsTable').length > 0) {
    App.start();
}
