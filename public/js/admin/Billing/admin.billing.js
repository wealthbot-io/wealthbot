App.module('Wealthbot.Admin.Billing', function(Mod, App, Backbone, Marionette, $) {

    Mod.startWithParent = false;

    // Client model
    Mod.ClientModel = Backbone.Model.extend({});

    // Client collection
    Mod.ClientCollection = Backbone.Collection.extend({
        model: Mod.ClientModel,

        totalFees: function(field) {
            var total = 0;
            this.each(function(model) { total += model.get(field) });
            return total;
        }
    });

    Mod.BillingDataModel = Backbone.Model.extend({
        defaults: {
            year: 0,
            quarter: 0,
            riaFees: 0,
            adminFees: 0,
            feesCollected: 0,
            feesCollectedPercent: 0,
            feesBilled: 0
        }
    });

    // Client item view
    Mod.ClientItemView = Marionette.ItemView.extend({
        tagName: 'tr',

        template: '#tplClientItem',

        feeBilledInput: null,
        feeCollectedInput: null,

        templateHelpers: {
            getFeeBilled: function() {
                return '$' + this.feeBilled.formatMoney();
            },

            getFeeCollected: function() {
                var feeCollected = parseFloat(this.feeCollected);

                return ! feeCollected ? 'None' : '$' + feeCollected.formatMoney();
            },

            getClassName: function() {
                var feeBilled = parseFloat(this.feeBilled),
                    feeCollected = parseFloat(this.feeCollected),
                    className;

                if (feeBilled == feeCollected && feeCollected > 0) {
                    className = 'success';
                } else if (feeBilled != feeCollected && feeCollected > 0) {
                    className = 'error';
                }

                return className;
            }
        },

        events: {
            'click #btnEdit': 'clickEdit'
        },

        onRender: function() {
            this.feeBilledInput = null;
            this.feeCollectedInput = null;
        },

        clickEdit: function(e) {
            e.preventDefault();
            $(e.currentTarget).hasClass('disabled') || this.toggleEditTable(e);
        },

        toggleEditTable: function(e) {
            var self = this,
                target = $(e.currentTarget);

            if (_.isNull(this.feeBilledInput)) {
                this.feeBilledInput = this.createElement('#feeBilled', this.model.get('feeBilled'));
                this.feeCollectedInput = this.createElement('#feeCollected', this.model.get('feeCollected'));

                target.addClass('btn-success').children('i').removeClass('icon-edit').addClass('icon-ok icon-white');
            } else {
                var data = {
                    feeBilled: parseFloat(this.feeBilledInput.autoNumeric('get')),
                    feeCollected: parseFloat(this.feeCollectedInput.autoNumeric('get'))
                }

                this.feeBilledInput.autoNumeric('destroy');
                this.feeCollectedInput.autoNumeric('destroy');

                this.model.set(data);

                if (this.model.hasChanged('feeBilled') || this.model.hasChanged('feeCollected')) {
                    data['_method'] = 'PUT';

                    $.ajax({
                        url: Routing.generate('rx_ria_api_bill_item_update_fee', { id: this.model.get('billItemId') }),
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        beforeSend: function() {
                            target.addClass('js-loading disabled');
                        },
                        success: function() {
                            App.vent.trigger('billing:update-data');
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
        }
    });

    // Clients view
    Mod.ClientsView = Marionette.CompositeView.extend({
        template: '#tplClients',

        itemView: Mod.ClientItemView,

        itemViewContainer: 'tbody'
    });

    // Billing data view
    Mod.BillingDataView = Marionette.ItemView.extend({
        className: 'admin-billing-data',

        template: '#tplBillingData',

        ui: {
            quarterTitle: '#quarter',
            intervalTitle: '#interval'
        },

        initialize: function() {
            this.listenTo(this.model, 'change', this.render);
        },

        onRender: function() {
            var year = this.model.get('year'),
                quarter = this.model.get('quarter');

            if (quarter) {
                var dateTo = new Date(),
                    dateFrom = new Date(year + '/' + ((quarter - 1) * 3 + 1) + '/01 12:00:00 AM');

                dateTo.setTime(dateFrom.getTime());
                dateTo.setMonth(dateTo.getMonth() + 3);

                this.ui.quarterTitle.text('Quarter ' + quarter);
                this.ui.intervalTitle.text( dateFrom.toLocaleDateString() + ' - ' + (dateTo.setDate(0) ? dateTo.toLocaleDateString() : null)).removeClass('hide');
            } else {
                this.ui.quarterTitle.text(year);
                this.ui.intervalTitle.addClass('hide');
            }
        }
    });

    // Module layout
    Mod.Layout = Marionette.Layout.extend({
        regions: {
            clientListRegion: '#clientList',
            billingDataRegion: '#billingData'
        },

        data: {
            year: 0,
            quarter: 0
        },

        events: {
            'click .interval': 'clickQuarter',
            'change #year': 'changedYear'
        },

        initialize: function() {
            var self = this;

            this.selectedYear(parseInt(this.$('#year').eq(0).text()));

            App.vent.on('billing:update-data', function() {
                var feesBilled = Mod.clientCollection.totalFees('feeBilled'),
                    feesCollected = Mod.clientCollection.totalFees('feeCollected'),
                    feesCollectedPercent = feesBilled == 0 ?  0 : Math.round(feesCollected / feesBilled * 100);

                Mod.billingDataModel.set({
                    year: self.data.year,
                    quarter: self.data.quarter,
                    riaFees:  Mod.clientCollection.totalFees('riaFee'),
                    adminFees:  Mod.clientCollection.totalFees('adminFee'),
                    feesBilled: feesBilled,
                    feesCollected: feesCollected,
                    feesCollectedPercent: feesCollectedPercent
                });

                self.billingDataRegion.close();
                self.billingDataRegion.show(Mod.billingDataView);
            });
        },

        changedYear: function(e) {
            this.selectedYear(parseInt($(e.currentTarget).val()));
        },

        selectedYear: function(year){
            var fromDate = new Date(App.Var.fromDate),
                currentDate = new Date();

           // this.$('.quarter').hide();
            this.$('.quarter').removeClass('active');

            for (var i = 1; i <= 4; i++) {
                var date = new Date(year + '/' + (i * 3) + '/01 12:00:00 AM');
                date.setMonth(date.getMonth() + 1);

                if (date.getTime() > fromDate.getTime()) {
                    date = new Date(year + '/' + ((i - 1) * 3 + 1) + '/01 12:00:00 AM');
                    if (date.getTime() < currentDate.getTime()) {
                        this.$('.quarter').eq(i - 1).show();
                    }
                }
            }

            this.data.year = year;
        },

        clickQuarter: function(e) {
            e.preventDefault();

            var target = $(e.currentTarget);

            this.data.quarter = parseInt(target.attr('data-value'));
            this.renderClients();

            this.$('.interval').removeClass('active');
            target.addClass('active');
        },

        renderData: function(response) {
            this.clientListRegion.close();

            Mod.clientCollection.reset(response);

           // if (this.data.quarter) {
                var view = new Mod.ClientsView({ collection: Mod.clientCollection });
                this.clientListRegion.show(view);
          //  }

            // Render billing data block
            App.vent.trigger('billing:update-data');
        },

        renderClients: function() {
            var self = this;

            $.ajax({
                url: Routing.generate('rx_admin_billing_client_list', { year: this.data.year, quarter: this.data.quarter }),
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    self.$el.spinner128(true);
                },
                success: function(response) {
                    console.log(response);
                    self.renderData(response);
                },
                complete: function() {
                    self.$el.spinner128(false);
                }
            });
        }
    });

    // Module constructor
    Mod.addInitializer(function() {
        // Model
        Mod.billingDataModel = new Mod.BillingDataModel();

        // Module collection
        Mod.clientCollection = new Mod.ClientCollection();

        // View
        Mod.billingDataView = new Mod.BillingDataView({ model: Mod.billingDataModel });

        // Module layout
        Mod.layout = new Mod.Layout({ el: '#billingApp' });
    });
});