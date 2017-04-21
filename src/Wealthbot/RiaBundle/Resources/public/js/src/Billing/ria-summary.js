"use strict";
// Profile view module
App.module('Wealthbot.Billing.RiaSummary', function(Mod, App, Backbone, Marionette, $) {

    Mod.startWithParent = false;

    Mod.currentClientModel = null;

    // Client model
    Mod.ClientModel = Backbone.Model.extend({
        defaults: {
            id: 0,
            name: '',
            clientStatus: '',
            billingSpecName: '',
            paymentMethod: '',
            status: '',
            statusNumber: 0,
            billCreatedAt: '',
            portfolioValue: 0,
            cash: 0,
            selected: false,
            accounts: []
        },

        accounts: null,

        initialize: function () {
            this.fetchАccounts(this.get('accounts'));
        },

        fetchАccounts: function(accounts) {
            this.accounts = Mod.accountCollection = new Mod.AccountCollection();
            this.accounts.reset(accounts);
        },

        renderАccounts: function() {
            Mod.accountCollection = this.accounts;
            var view = new Mod.SummaryAccounts({ collection: this.accounts });
            Mod.summaryLayout.accountListRegion.close();
            Mod.summaryLayout.accountListRegion.show(view);
        },

        selectedAccounts: function() {
            return this.accounts.where({ selected: true });
        },

        selectedAccountIds: function() {
            var data = [];
            _.each(this.selectedAccounts(), function(model) { data.push(model.get('id')) });
            return data;
        },

        changeForSelected: function(callback) {
            _.each(this.selectedAccounts(), function(model) {
                callback(model);
            });
            this.calculateFee();
        },

        calculateFee: function() {
            this.set('feeBilled', this.accounts.totalFee('feeBilled'));
            this.set('feeCollected', this.accounts.totalFee('feeCollected'));
        },

        toggle: function() {
            this.set({ selected: ! this.get('selected') });
        }
    });

    // Client collection
    Mod.ClientCollection = Backbone.Collection.extend({
        model: Mod.ClientModel,

        sorting: {
            name: 'str',
            clientStatus: 'int',
            billingSpecName: 'str',
            paymentMethod: 'int',
            status: 'str',
            billCreatedAt: 'date',
            portfolioValue: 'float',
            cash: 'float',
            feeBilled: 'float',
            feeCollected: 'float'
        },

        selected: function() {
            var data = [];
            this.each(function(model) { model.get('selected') == true && data.push(model.get('id')); });
            return data;
        },

        toggleAll: function(status) {
            this.each(function(model) { model.set('selected', status); });
        }
    });

    // Client account model
    Mod.AccountModel = Backbone.Model.extend({
        defaults: {
            id: 0,
            name: '',
            type: '',
            number: '',
            status: '',
            paysFor: '',
            averageAccountValue: 0,
            daysInPortfolio: 0,
            accountValue: 0,
            cash: 0,
            billItemStatus: '',
            feeBilled: 0,
            feeCollected: 0,
            selected: false
        },

        toggle: function() {
            this.set({ selected: ! this.get('selected') });
        }
    });

    // Client account collection
    Mod.AccountCollection = Backbone.Collection.extend({
        model: Mod.AccountModel,

        sorting: {
            name: 'str',
            type: 'int',
            number: 'str',
            status: 'int',
            paysFor: 'str',
            averageAccountValue: 'float',
            daysInPortfolio: 'int',
            accountValue: 'float',
            cash: 'float',
            billItemStatus: 'int',
            feeBilled: 'float',
            feeCollected: 'float'
        },


        selected: function() {
            var data = [];
            this.each(function(model) { model.get('selected') == true && data.push(model.get('id')); });
            return data;
        },

        toggleAll: function(status) {
            this.each(function(model) { model.set('selected', status); });
        },

        totalFee: function(field) {
            var total = 0;
            this.each(function(model) { total += model.get(field) });
            return total;
        }
    });

    // Variable model (save global module var here plz)
    Mod.VarModel = Backbone.Model.extend({
        defaults: {
            curClient: null,
            curBillingYear: null,
            curBillingQuarter: null,
            clientSelected: false,
            accountSelected: false
        }
    });

    Mod.SummaryLayout = Marionette.Layout.extend({
        el: "#summary",

        template: '#tplSummaryLayout',

        events: {
            'change .year-selector': 'changedYear',
            'click .interval': 'quarterClick'
        },

        ui: {
            yearsSelect: '.year-selector',
            years: '.year-selector option',
            quarters: '.quarter',
            intervals: '.interval',
            yearButton: '.interval[data-quarter="0"]',
            datesInterval: '.dates-interval',
            intervalTitle: '.interval-title'
        },

        regions: {
            billStatusRegion: '#billStatusRegion',
            clientListRegion: '#clientListRegion',
            accountListRegion: '#accountListRegion',
            clientActionRegion: '#clientActionRegion'
        },

        billingYear: null,
        billingQuarter: null,

        initialize: function() {
            var self = this;
            Mod.varModel.set('curBillingQuarter', 0);

            this.billingQuarter = 0; // Default all quarters
            this.listenTo(this, 'quarterClick', function() { self.ui.intervals.removeClass('active');  });
        },

        afterShow: function(){
            Mod.summaryGraphs = new Mod.SummaryGraphs({model: Mod.summaryGraphsModel});
            this.billStatusRegion.show(Mod.summaryGraphs);

            Mod.summaryClients = new Mod.SummaryClients({collection: Mod.clientCollection});
            this.clientListRegion.show(Mod.summaryClients);

            // Client action render view
            Mod.clientActionView = new Mod.ClientActionView({ model: Mod.varModel });
            Mod.summaryLayout.clientActionRegion.show(Mod.clientActionView);

            var currentYear = (new Date()).getFullYear();
            var selectedYear = false;

            this.ui.years.each(function(){
                var year = parseInt($(this).text());
                if (currentYear == year) {
                    selectedYear = year;
                }
            });

            if (App.Var.currentYear !== undefined) {

                selectedYear = App.Var.currentYear;
                this.ui.yearsSelect.val(selectedYear);
                this.selectedYear(selectedYear);

                this.changeQuarter(App.Var.currentQuarter);
            } else {
                if (!selectedYear) {
                    selectedYear = this.ui.years.eq(0).text();
                }

                this.ui.yearsSelect.val(selectedYear);
                this.selectedYear(selectedYear);

                var currentQuarter = Math.floor((new Date()).getMonth() / 3) + 1;
                this.changeQuarter(currentQuarter);
            }
        },

        changeQuarter: function(quarterNum) {
            var button = this.$('.interval[data-quarter="' + quarterNum + '"]');

            if (button.length > 0) {
                this.quarterClick({currentTarget: button[0]});
            }
        },

        changedYear: function(e){
            var year = $(e.currentTarget).val();
            this.selectedYear(year);
            return false;
        },

        selectedYear: function(year){
            var currentDate = new Date();
            var riaCreatedAt = new Date(App.Var.riaCreatedAt);

            this.ui.quarters.hide();
            this.ui.quarters.removeClass('active');

            for (var i=1; i<=4; i++) {
                var date = new Date(year + '/' + (i * 3) + '/01 12:00:00 AM');
                date.setMonth(date.getMonth() + 1);
                if (date.getTime() > riaCreatedAt.getTime()) {
                    date = new Date(year + '/' + ((i-1) * 3 + 1) + '/01 12:00:00 AM');
                    if (date.getTime() < currentDate.getTime()) {
                        this.ui.quarters.eq(i-1).show();
                    }
                }
            }

            if (parseInt(year) < currentDate.getFullYear()) {
                this.ui.yearButton.show();
            }else{
                this.ui.yearButton.hide();
            }

            Mod.varModel.set('curBillingYear', year);

            this.billingYear = year;
        },

        quarterClick: function(e) {
            var self = this;
            var dateFrom, dateTo;

            var $button = $(e.currentTarget);
            var quarter = parseInt($button.attr('data-quarter'));

            Mod.accountCollection.reset();

            // Clear global vars model
            Mod.varModel.set(Mod.varModel.defaults);
            Mod.varModel.set({ curBillingYear: this.billingYear,  curBillingQuarter: quarter });

            this.billingQuarter = quarter;

            var url = Routing.generate('rx_ria_billing_summary_tab_load', { year: this.billingYear, quarter: quarter });

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    self.$el.spinner128();
                },
                success: function(response) {
                    Mod.clientCollection.reset(response.clients);

                    self.trigger('quarterClick');
                    $button.addClass('active');

                    Mod.summaryGraphsModel.set(response.graphs);
                    Mod.summaryGraphs.updateBars();
                },
                complete: function() {
                    self.$el.spinner128(false);
                }
            });

            if (quarter == 0) {
                dateFrom = new Date(this.billingYear + '/01/01 12:00:00 AM');
                dateTo = new Date((parseInt(this.billingYear) + 1) + '/01/01 12:00:00 AM');
            } else {
                dateFrom = new Date(this.billingYear + '/' + ((quarter-1) * 3 + 1) + '/01 12:00:00 AM');
                dateTo = new Date();
                dateTo.setTime(dateFrom.getTime());
                dateTo.setMonth(dateTo.getMonth() + 3);
            }

            this.ui.datesInterval.text(dateFrom.toLocaleDateString() + ' - ' + (dateTo.setDate(0) ? dateTo.toLocaleDateString() : null)); //setDate(0) - day before
        },

        reloadPage: function(){
            var url = Routing.generate('rx_ria_billing_by_quarter', {year: this.billingYear, quarter: this.billingQuarter});
            if (document.location.pathname == url) {
                document.location.reload();
            }else{
                document.location.href = url;
            }
        }
    });

    Mod.SummaryGraphsModel = Backbone.Model.extend({
        defaults: {
            billGeneratedPercent: 0,
            billGeneratedValue: 0,
            billApprovedPercent: 0,
            billApprovedValue: 0,
            billFeeGeneratedPercent: 0,
            billFeeGeneratedValue: 0,
            billCollectedPercent: 0,
            billCollectedValue: 0
        }
    });

    Mod.SummaryGraphs = Marionette.ItemView.extend({
        template: '#tplSummaryGraphs',
        ui: {
            billGeneratedBar: '.bill-generated-bar',
            billApprovedBar: '.bill-approved-bar',
            feeGeneratedBar: '.fee-generated-bar',
            billCollectedBar: '.bill-collected-bar'
        },
        updateBars: function() {
            this.render();
            this.ui.billGeneratedBar.css('width', this.model.get('billGeneratedPercent') + '%');
            this.ui.billApprovedBar.css('width', this.model.get('billApprovedPercent') + '%');
            this.ui.feeGeneratedBar.css('width', this.model.get('billFeeGeneratedPercent') + '%');
            this.ui.billCollectedBar.css('width', this.model.get('billCollectedPercent') + '%');
        }
    });

    // Client item view
    Mod.SummaryClientItem = Marionette.ItemView.extend({
        template: '#tplSummaryClientItem',

        tagName: 'tr',

        events: {
            'click': 'onClientClick',
            'click input[type="checkbox"]': 'onClientSelect'
        },

        ui: {
            checkbox: 'input[type="checkbox"]',
            feeBilled: '.feeBilled',
            feeCollected: '.feeCollected'
        },

        initialize: function(){
            var self = this;
            
            this.listenTo(App.vent, 'clickClientItem', function() { self.$el.removeClass('active') });
            this.listenTo(this.model, 'change', this.render);
        },

        onBeforeRender: function() {
            this.model.calculateFee();
        },

        onRender: function() {
            this.ui.checkbox.prop('checked', this.model.get('selected'));
        },

        onClientClick: function(e) {
            if ($(e.target).is('tr,td')) {
                e.preventDefault();

                this.model.renderАccounts();

                Mod.varModel.set('curClient', this.model.get('id'));
                Mod.currentClientModel = this.model;

                App.vent.trigger('clickClientItem');

                this.$el.addClass('active');
            }
        },

        onClientSelect: function(e) {
            this.model.toggle();

            Mod.varModel.set('clientSelected', Mod.clientCollection.selected().length > 0);
            Mod.clientActionView.render();
        }
    });

    // Clients view
    Mod.SummaryClients = Marionette.CompositeView.extend({
        template: '#tplSummaryClients',
        itemView: Mod.SummaryClientItem,
        itemViewContainer: 'tbody',

        events: {
            'click #toggleAll': 'clickToggleAll',
            'click .sortable': 'clickSortable'
        },

        clickToggleAll: function(e) {
            var checked = this.$('#toggleAll').is(':checked');
            this.collection.toggleAll(checked);
            Mod.varModel.set('clientSelected', Mod.clientCollection.selected().length > 0);
        },

        clickSortable: function(e){
            e.preventDefault();
            CollectionSorter.autoSortView(this, e);
            return false;
        }
    });

    // Client account item view
    Mod.SummaryAccountItem = Marionette.ItemView.extend({
        template: '#tplSummaryAccountItem',

        tagName: 'tr',

        events: {
            'click input[type="checkbox"]': 'onAccountSelect',
            'click #btnEdit': 'clickEdit'
        },

        ui: {
            checkbox: 'input[type="checkbox"]',
            paysFor: '.pays-for'
        },

        parentView: null,

        feeBilled: null,
        feeCollected: null,

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
            this.listenTo(App.vent, 'summary:accounts:rendered', this.afterRenderedAll);
        },

        onRender: function() {
            this.ui.checkbox.prop('checked', this.model.get('selected'));
            this.model.get('selected') ? this.$el.addClass('info') : this.$el.removeClass('info');

            this.feeBilled = null;
            this.feeCollected = null;

            if (this.parentView !== null){
                this.afterRenderedAll(this.parentView);
            }
        },

        onAccountSelect: function() {
            this.model.toggle();
            Mod.varModel.set('accountSelected', Mod.clientCollection.get(Mod.varModel.get('curClient')).selectedAccounts().length > 0);
            Mod.clientActionView.render();
        },

        clickEdit: function(e) {
            e.preventDefault();

            $(e.currentTarget).hasClass('disabled') || this.toggleEditTable(e);
        },

        toggleEditTable: function(e) {
            var self = this,
                target = $(e.currentTarget);

            if (this.feeBilled == null) {
                this.feeBilled = this.createElement('#feeBilled', this.model.get('feeBilled'));
                this.feeCollected = this.createElement('#feeCollected', this.model.get('feeCollected'));

                target.text('Save');
            } else {
                var data = {
                    feeBilled: parseFloat(this.feeBilled.autoNumeric('get')),
                    feeCollected: parseFloat(this.feeCollected.autoNumeric('get'))
                };

                this.feeBilled.autoNumeric('destroy');
                this.feeCollected.autoNumeric('destroy');

                this.model.set(data);

                if (this.model.hasChanged('feeBilled') || this.model.hasChanged('feeCollected')) {
                    // Apply changes
                    data[' _method'] = 'PUT';

                    $.ajax({
                        url: Routing.generate('rx_ria_api_bill_item_update_fee', { id: this.model.get('billItemId') }),
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        beforeSend: function() {
                            target.addClass('js-loading disabled');
                        },
                        success: function() {
                            Mod.clientCollection.get(Mod.varModel.get('curClient')).calculateFee();
                            self.render();
                        },
                        complete: function() {
                            target.removeClass('js-loading disabled');
                        }
                    });
                } else {
                    this.render();
                }
            }
        },

        createElement: function($el, value) {
            var input = $('<input />').attr({ type: 'text' }).val(value);
            this.$($el).html(input);
            return input.autoNumeric('init', {vMin: '0.00', vMax: '99999999999999.99'});
        },

        afterRenderedAll: function(view){
            this.parentView = view;
            var self = this;
            var paysFor = this.ui.paysFor.attr('data-pays-for');
            if (view.collection.length > 1) {
                var accounts = [''];
                view.collection.each(function(model){
                    accounts.push(model.get('number'));
                });
                for(var i in accounts) {
                    var caption = (accounts[i] == '' ? ' - ' : accounts[i]);
                    accounts[i] = '<option ' + (accounts[i] == paysFor ? 'selected' : '') + ' value="' + accounts[i] + '">' + caption + '</option>';
                }
                this.ui.paysFor.html('<select class="paysfor">' + accounts.join('') + '</select>');
                this.ui.paysFor.find(document).on('change','select', function(e){
                    self.paysForChange(e, view);
                });
            }
        },

        paysForChange: function(e){
            var self = this;
            var select = $(e.currentTarget);
            var wasPaysFor = select.parent().attr('data-pays-for');
            var paysFor = select.val();
            self.$el.spinner32();
            $.ajax({
                url: Routing.generate('rx_ria_billing_update_account_pays_for', {account: self.model.id}),
                type: 'PUT',
                data: {
                    paysFor: paysFor
                },
                dataType: 'json',
                success: function(response){
                    self.$el.spinner32(false);
                    self.model.set('paysFor', paysFor);
                    self.afterRenderedAll(self.parentView);
                },
                error: function(e){
                    self.$el.spinner32(false);
                    select.val(wasPaysFor);
                    alert('Error set pays-for');
                }
            });
        }
    });

    // Client accounts view
    Mod.SummaryAccounts = Marionette.CompositeView.extend({
        template: '#tplSummaryAccountsView',

        itemView: Mod.SummaryAccountItem,

        itemViewContainer: 'tbody',

        events: {
            'click #toggleAll': 'clickToggleAll',
            'click .sortable': 'clickSortableHeader'
        },

        clickToggleAll: function(e) {
            var checked = this.$('#toggleAll').is(':checked');
            this.collection.toggleAll(checked);
            Mod.varModel.set('accountSelected', Mod.accountCollection.selected().length > 0);
            Mod.clientActionView.render();
        },

        clickSortableHeader: function(e){
            e.preventDefault();
            CollectionSorter.autoSortView(this, e);
            return false;
        },

        onRender: function(){
            App.vent.trigger('summary:accounts:rendered', this);
        }
    });

    // Client action view
    Mod.ClientActionView = Marionette.ItemView.extend({
        template: '#tplClientAction',

        events: {
            'click #btnGenerateBill': 'clickGenerateBill',
            'click #btnApproveBill': 'clickApproveBill',
            'click #btnCollectedBill': 'clickCollectedBill',
            'click #btnNoBill': 'clickNoBill',
            'click #btnGenerateBillingSummary': 'clickGenerateBillingSummary',
            'click #btnGenerateCustodianFeeFile': 'clickGenerateCustodianFeeFile',
            'click #btnPDFBill': 'clickPDFBill',
            'click #btnEmailBill': 'clickEmailBill'
        },

        ui: {
            btnApproveBill: '#btnApproveBill'
        },

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },

        clickGenerateCustodianFeeFile: function(e){
            e.preventDefault();
            var self = this;
            var data = Mod.clientCollection.get(this.model.get('curClient')).selectedAccountIds();

            //do query for checking avaibility.
            var url = Routing.generate(
                'rx_ria_billing_generate_custodian_fee_file',
                {
                    year: this.model.get('curBillingYear'),
                    quarter: this.model.get('curBillingQuarter'),
                    selectedAccounts: data
                }
            );

            //test availability and download file
            $.ajax({
                url: url,
                dataType: 'json',
                type: 'POST',
                success: function(response){
                    if (response.status == 'success') {
                        document.location.href = url;
                    }
                },
                error: function(e){
                    Util.statusMessage('Error', e);
                }
            });
        },

        onRender: function(){
            //Enable menu elements
            var approveEnabled = false;
            if (Mod.currentClientModel !== null) {
                var accountCollection = Mod.currentClientModel.accounts;
                accountCollection.each(function(model){
                    if (model.get('selected') && model.get('billItemStatus') == App.Const.BillItemStatusId.STATUS_BILL_GENERATED) {
                        approveEnabled = true;
                    }
                });
            }

            if (approveEnabled) {
                this.ui.btnApproveBill.parent().removeClass('disabled');
            } else {
                this.ui.btnApproveBill.parent().addClass('disabled');
            }
        },

        // Action menu: click Generate Bill
        clickGenerateBill: function(e) {
            e.preventDefault();

            var target = $(e.currentTarget);

            if (this.isNotDisabled(target)) {
                var data = Mod.clientCollection.get(this.model.get('curClient')).selectedAccountIds();

                $.ajax({
                    url: target.attr('href'),
                    type: 'POST',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    beforeSend: function() {
                        Mod.summaryLayout.$el.spinner128();
                    },
                    success: function() {
                        Mod.summaryLayout.reloadPage();
                    },
                    error: function(e) {
                        Util.statusMessage('Error', e);
                        Mod.summaryLayout.$el.spinner128(false);
                    }
                });
            }
        },

        // Action menu: click Approve Bill
        clickApproveBill: function(e) {
            e.preventDefault();
            var self = this;
            var requestsCount = 0;
            var approveResult = null;
            var accountCollection = Mod.currentClientModel.accounts;

            accountCollection.each(function(model){
                if (model.get('selected') && model.get('billItemStatus') == App.Const.BillItemStatusId.STATUS_BILL_GENERATED) {

                    if (requestsCount == 0) {
                        Mod.summaryLayout.$el.spinner128();
                    }
                    var url = Routing.generate('rx_ria_billing_approve_bill', { account_id: model.id, year: self.model.get('curBillingYear'), quarter: self.model.get('curBillingQuarter')});
                    requestsCount += 1;
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        success: function(){
                            requestsCount -= 1;
                            if (requestsCount == 0) {
                                Mod.summaryLayout.reloadPage();
                            }
                        },
                        error: function(e){
                            Mod.summaryLayout.$el.spinner128(false);
                            Util.statusMessage('Error', e);
                        }
                    });
                }
            });

            return false;
        },

        // Action menu: click Collected Bill
        clickCollectedBill: function(e) {
            e.preventDefault();
        },

        // Action menu: click No Bill
        clickNoBill: function(e) {
            e.preventDefault();

            var target = $(e.currentTarget);
            var url = target.attr('href');

            if (this.isNotDisabled(target)) {
                var client = Mod.clientCollection.get(this.model.get('curClient')),
                    data = client.selectedAccountIds();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    beforeSend: function() {
                        Mod.summaryLayout.$el.spinner128();
                    },
                    success: function(response) {
                        if (response.status === undefined || response.status != 'success') {
                            Util.statusMessage('Wrong Response', response);
                            return;
                        }
                        client.changeForSelected(function(model) {
                            model.set({
                                billItemStatus: App.Const.BillItemStatusId.STATUS_WILL_NOT_BILL,
                                feeBilled: 0,
                                feeCollected: 0
                            });
                            model.toggle();
                        });
                    },
                    error: function(e) {
                        Util.statusMessage('Error', e);
                    },
                    complete: function() {
                        Mod.summaryLayout.$el.spinner128(false);
                    }
                });
            }
        },

        // Action menu: click PDF Bill
        clickPDFBill: function(e) {
            e.preventDefault();
            var target = $(e.currentTarget);
            if (this.isNotDisabled(target)) {
                var client = Mod.clientCollection.get(this.model.get('curClient')),
                    data = client.selectedAccountIds();

                var url = Routing.generate('rx_ria_billing_pdf_report', {
                    client_id: this.model.get('curClient'),
                    year: this.model.get('curBillingYear'),
                    quarter: this.model.get('curBillingQuarter'),
                    content: JSON.stringify(data)
                });

                window.open(url, '_blank');
            }
        },

        // Action menu: click Email Bill
        clickEmailBill: function(e) {
            e.preventDefault();

            var target = $(e.currentTarget);

            if (this.isNotDisabled(target)) {
                var client = Mod.clientCollection.get(this.model.get('curClient')),
                    data = client.selectedAccountIds();

                $.ajax({
                    url: target.attr('href'),
                    type: 'POST',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    beforeSend: function() {
                        Mod.summaryLayout.$el.spinner128();
                    },
                    success: function(response) {
                        Util.success(response.message);
                    },
                    complete: function() {
                        Mod.summaryLayout.$el.spinner128(false);
                    }
                });
            }
        },

        clickGenerateBillingSummary: function(e) {
            if (!this.isNotDisabled(e.currentTarget)) e.preventDefault();
        },

        isNotDisabled: function(el) {
            return ! $(el).parent().hasClass('disabled');
        }
    });

    // Module constructor
    Mod.addInitializer(function() {
        // Model
        Mod.varModel = new Mod.VarModel();

        // Collection
        Mod.clientCollection = new Mod.ClientCollection();
        Mod.accountCollection = new Mod.AccountCollection();
        Mod.summaryGraphsModel = new Mod.SummaryGraphsModel();

        // Layout
        Mod.summaryLayout = new Mod.SummaryLayout();
        Mod.summaryLayout.render();
        Mod.summaryLayout.afterShow();

        //for selected year and part of year request all data... or generate it for all types.
    });
});