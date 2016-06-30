"use strict";
// Profile view module
App.module('Wealthbot.Billing.RiaSpecs', function(Mod, App, Backbone, Marionette, $) {

    Mod.BillingExtendedSpecModel = Backbone.Model.extend({
        defaults: {
            name: "",
            master: (App.Var.straightProcessing? true : false),
            minimalFee: 0
        },
        sync: function(method, model, options) {
            var that = this;
            switch (method) {
                case 'create':
                    var data = model.toJSON();
                    data._token = App.csrf;
                    if (data.fees !== undefined && data.fees.length > 0) {
                        _.last(data.fees).tier_top = 1000000000000;
                    }
                    if (data.type == App.Const.BILLING_SPEC_TYPE_FLAT && data.fees[0].tier_top) {
                        delete data.fees[0].tier_top;
                    }
                    data.master? true: delete data.master;

                    that.trigger('syncRequest');
                    $.ajax({
                        url: Routing.generate('rx_ria_api_billing_specs_rest'),
                        type: 'POST',
                        data: {
                            billing_spec: data
                        },
                        success: function() {
                            that.trigger('syncEnd');
                            that.trigger('syncSuccess');

                            Mod.billingSpecCollection.fetch();
                        },
                        error: function(xnr) {
                            var response = JSON.parse(xnr.responseText);

                            that.trigger('syncEnd');

                            ErrorHandler.handle(response, '.billing form', {
                                fees: {
                                    fieldSelector: '.tierRegion table tbody tr',
                                    type: 'collection'
                                }
                            });
                        }
                    });
                    break;
                case 'read':
                    //New model should not be requested from server
                    if(model.isNew()) {
                        App.vent.trigger('extendedModel:fetch', model);
                        return false;
                    }

                    that.trigger('syncRequest');
                    $.ajax({
                        url: Routing.generate('rx_ria_api_billing_specs_rest_item', {'id': model.get("id")}),
                        type: 'GET',

                        success: function(data) {
                            _.each(JSON.parse(data), function(v, i) {
                                model.attributes[i] = v;
                            });

                            that.trigger('syncEnd');
                            that.trigger('syncSuccess');

                            App.vent.trigger('extendedModel:fetch', model);
                        },
                        error: function(xnr) {
                            var response = JSON.parse(xnr.responseText);

                            that.trigger('syncEnd');

                            ErrorHandler.handle(response, '.billing form', {
                                fees: {
                                    fieldSelector: '.tierRegion table tbody tr',
                                    type: 'collection'
                                }
                            });
                        }

                    });
                    break;
                case 'delete':
                    that.trigger('syncRequest');

                    if (!model.isNew()) {

                        $.ajax({
                            url: Routing.generate('rx_ria_api_billing_specs_rest_item', {'id': model.get("id")}),
                            type: 'DELETE',

                            success: function(data) {
                                that.trigger('syncEnd');
                                that.trigger('syncSuccess');

                                Mod.billingSpecCollection.fetch();
                            },
                            error: function(xnr) {
                                var response = JSON.parse(xnr.responseText);

                                that.trigger('syncEnd');

                                ErrorHandler.handle(response, '.billing form', {
                                    fees: {
                                        fieldSelector: '.tierRegion table tbody tr',
                                        type: 'collection'
                                    }
                                });
                            }
                        });
                    }
                    break;
                case 'update':
                    var data = model.toJSON();
                    delete data.id;
                    data._token = App.csrf;

                    data.master? true: delete data.master;
                    if (data.fees !== undefined && data.fees.length > 0) {
                        _.last(data.fees).tier_top = 1000000000000;
                    }
                    if (data.type == App.Const.BILLING_SPEC_TYPE_FLAT && data.fees[0].tier_top) {
                        delete data.fees[0].tier_top;
                    }

                    that.trigger('syncRequest');
                    $.ajax({
                        url: Routing.generate('rx_ria_api_billing_specs_rest_item', {'id': model.get("id")}),
                        type: 'PUT',
                        data: {
                            billing_spec: data
                        },
                        success: function(data) {
                            that.trigger('syncEnd');
                            that.trigger('syncSuccess');

                            Mod.billingSpecCollection.fetch();
                        },
                        error: function(xnr) {
                            var response = JSON.parse(xnr.responseText);

                            that.trigger('syncEnd');

                            ErrorHandler.handle(response, '.billing form', {
                                fees: {
                                    fieldSelector: '.tierRegion table tbody tr',
                                    type: 'collection'
                                }
                            });
                        }
                    });
                    break;
            }
        }

    });

    Mod.TierModel = Backbone.Model.extend({
        defaults: {
            fee_without_retirement: 0,
            tier_bottom: 0,
            tier_top: 0,
            is_last: 1
        }

    });

    // Feedback collection
    Mod.BillingSpecCollection = Backbone.Collection.extend({
        model:  Mod.BillingExtendedSpecModel,
        url: Routing.generate('rx_ria_api_billing_specs_rest'),

        checkName: function(name) {
            var was = false;
            this.each(function(model){
                if (model.get('name').toLowerCase() == name.toLowerCase()) {
                    was = true;
                }
            });
            return was;
        }
    });

    // Feedback collection
    Mod.TierCollection = Backbone.Collection.extend({
        model:  Mod.TierModel,
        getTopTier: function() {
            if(typeof this.last() == "undefined") {
                return 0.01;
            } else  {
                //Convert from formatted
                return parseFloat(this.last().get("tier_top"))+0.01;
            }
        },
        clearJSON: function()
        {
            var clearJSON = [];
            _.each(this.toJSON(), function(i){

                delete i.is_last;
                delete i.tier_bottom;
                delete i.id;
                clearJSON.push(i);
            });

            return clearJSON;
        }
    });

    Mod.TierItemView = Marionette.ItemView.extend({
        template: "#tplTierRow",
        tagName: 'tr',
        parentView: null,
        ui: {
            fee: '.fee_input',
            tierTop: '.top_tier_input'
        },
        initialize: function(options) {
            var self = this;
            this.model.on('change', function() {
                self.render();
            });
            this.parentView = options.parentView;
        },
        events: {
            'click .btn-remove': 'destroyMe',
            'keyup .input': 'niceLook',
            'keyup .top_tier_input': 'updateNextTier',
            'change': 'viewChanged',
            'change .fee_input': 'feeChange'
        },
        feeChange: function(e){
            var input = $(e.currentTarget);
            input.val(input.val().formatFee());
        },
        hideFinal: function() {
            this.$('.is-final-tier-checkbox').hide();
        },
        showFinal: function() {
            this.$('.is-final-tier-checkbox').show();
        },
        enableTopTier: function() {
            this.$('.top_tier_input').removeAttr('disabled');
        },
        disableTopTier: function() {
            this.$('.top_tier_input').attr('disabled', 'disabled');
            this.$('.top_tier_input').val('');
        },
        onRender: function() {
            if (this.model.get('is_last') == true) {
                this.disableTopTier();
                this.showFinal();
                this.$(".btn-remove").show();
            } else {
                this.enableTopTier();
                this.hideFinal();
                this.$(".btn-remove").hide();
            }

            this.$('.for-auto-numeric').each(function(){
                var $this = $(this);
                if ($this.data('autoNumeric') === undefined) {
                    $this.autoNumeric({aSep: ',', aDec: '.', vMax: '99999999999999.99'});
                }
            });

            this.ui.fee.trigger('change');
        },
        updateNextTier: function() {
            Mod.billingSpecCollectionView.trigger('update:top_tier', this, this);

        },
        viewChanged: function() {
            var model_index = this.parentView.collection.indexOf(this.model),
                is_last_model = model_index == (this.parentView.collection.length -1);

            if (!is_last_model) {
                this.parentView.collection.at(model_index +1).set('tier_bottom', parseFloat(this.model.get("tier_top")) + 0.01);
            }

            if (!this.parentView.wasShowed) {
                return;
            }

            var previousTop = 0;
            var topTier = parseFloat(this.$('.top_tier_input').val().moneyToFloat());
            if (model_index > 0) {
                previousTop = parseFloat(this.parentView.collection.at(model_index - 1).get('tier_top'));
                if (!is_last_model && previousTop + 0.01 > topTier){
                    var fId = model_index;
                    var e = {
                        "fees":{}
                    };
                    e.fees[fId] = {
                        "tier_top":["Top Tier can't be lower than Bottom Tier"]
                    };
                    ErrorHandler.handle(e, '.billing form', {
                        fees: {
                            fieldSelector: '.tierRegion table tbody tr',
                            type: 'collection'
                        }
                    });
                }
            }


            this.model.set({
                fee_without_retirement: this.$('.fee_input').val().feeToFloat(),
                tier_bottom: this.$('.bottom_tier_input').val().moneyToFloat(),
                tier_top: topTier,
                is_last: is_last_model
            });
        },
        destroyMe: function() {
            var model_index = App.tierCollectionView.collection.indexOf(this.model),
                is_last_model = model_index == (App.tierCollectionView.collection.length -1);

            this.model.set({
                id: null
            });
            this.model.destroy();
            
            if (is_last_model) {
                App.tierCollectionView.collection.last().set({
                    is_last: true,
                    tier_top: 0
                });
            }
        }
    });

    Mod.BillingSpecItemView = Marionette.ItemView.extend({
        template: "#tpl-billing-spec",
        className: "btn btn-default edit-spec",
        events: {
            'click': 'onClick'
        },

        initialize: function(){
            this.listenTo(App.vent, 'spec-item:clicked', function(){
                this.$el.removeClass('active');
            });
        },
        selected: function(){
            App.vent.trigger('spec-item:clicked');
            this.$el.addClass('active');
            Mod.billingSpecCollectionView.wasSelectedName = this.model.get('name');
        },
        onClick: function() {
            var that = this;

            this.selected();

            if (!this.model.has('fees') && !this.model.isNew()) {
                var loadingModel = new Mod.BillingExtendedSpecModel({id: this.model.get("id")});
                App.extModel = loadingModel;
                loadingModel.fetch();

                var n = Mod.billingSpecCollection.indexOf(this.model);
                Mod.billingSpecCollection.models[n] = loadingModel;

                if (typeof Mod.billingSpecLayout.specFormRegion.$el != 'undefined') {
                    Mod.billingSpecLayout.specFormRegion.$el.spinner128();
                    App.vent.once("extendedModel:fetch", function(){
                        Mod.billingSpecLayout.specFormRegion.$el.spinner128(false);
                    });
                }

                that.$el.spinner16();
                App.vent.once("extendedModel:fetch", function(){
                    that.$el.spinner16(false);
                    that.model = loadingModel;
                });
                //Following logic triggered by "extendedModel:fetch" event, see it there.
            } else {
                App.vent.trigger("extendedModel:fetch", that.model);
            }
        },
        onShow: function() {
            /* Mark master spec as green
            if(this.model.get('master') == true) {
                this.$el.addClass('btn-success');
            }*/
        }
    });


    Mod.TierCollectionView = Marionette.CompositeView.extend({
        events: {
            'click .add-tier': 'addBlankTier'
        },
        itemView: Mod.TierItemView,
        itemViewContainer: "tbody",
        itemViewOptions: {},
        template: "#tplTierDataForm",
        templateHelpers: {
            isNew: function(){
                return _.isUndefined(App.currentModel.get('id'));
            }
        },
        wasShowed: false,

        initialize: function(){
            var that = this;
            this.itemViewOptions.parentView = this;
            that.on('after:item:added', function(){
                that.checkLast();
            });
        },
        addBlankTier: function(e) {
            e.preventDefault();

            //Set pre-last top tier
            if (this.collection.length > 1) {
                this.collection.at(this.collection.length -1).set('tier_top', 0);
            }

            var tier = new Mod.TierModel({
                fee_without_retirement: 0,
                tier_bottom: App.tierCollectionView.collection.getTopTier(),
                tier_top: ""
            });

            this.collection.add(tier);
        },
        appendHtml: function(collectionView, itemView, index){
            collectionView.$("tbody").append(itemView.el);
        },
        checkLast: function(){
            var that = this;
            that.collection.each(function(e, i) {
                if (that.collection.length < 2 || i == (that.collection.length-1)) {
                    e.set('is_last', true);
                } else {
                    e.set('is_last', false);
                }
            });
            that.collection.last().set({
                tier_top: 0
            });
        },
        onShow: function(){
            this.wasShowed = true;
        }
    });

    //This is list of user billing specs
    Mod.BillingSpecCollectionView = Marionette.CompositeView.extend({
        template: "#tplSpecCollection",
        itemView: Mod.BillingSpecItemView,
        itemViewContainer: ".specs",
        events: {
            'click .add-new': 'addNewSpec',
            'update:top_tier': 'onUpdateTopTier',
            'create:blankModel': 'onModelCreated'
        },
        ui: {
            itemContainer: '.specs',
            addButton: '.add-new'
        },
        wasSelectedName: '',

        initialize: function() {
            var that = this;
            $('.pop').popover();

            this.collection.on('error', function(){
                that.$el.spinnerFill(false);
            });

            this.collection.on('sync', function(){
                that.$el.spinnerFill(false);
                if (App.Var.straightProcessing) {
                    var first = true;
                    that.collection.reset(that.collection.filter(function(){
                        if (first) {
                            first = false;
                            return true;
                        }
                        return false;
                    }));
                }
                that.render();
                if (that.wasSelectedName) {
                    that.collection.each(function(model){
                        if (model.get('name') == that.wasSelectedName){
                            that.children.findByModel(model).onClick();
                        }
                    });
                }
            });

            this.collection.on('add,remove', function(){
                that.updateView();
            });

            this.collection.on('request', function(){
                if (!that.isClosed) {
                    that.$el.spinnerFill();
                }
            });
        },
        onRender: function(){
            this.updateView();
        },
        updateView: function(){
            if (App.Var.straightProcessing) {
                if (this.collection.length > 0) {
                    this.ui.addButton.hide();
                }else{
                    this.ui.addButton.show();
                }
            }
        },
        onShow: function(){
            this.$el.spinnerFill();
        }
    });

    Mod.BillingSpecFlatForm = Marionette.ItemView.extend({
        template: "#tplFlatFeeForm",
        templateHelpers: {
            isNew: function(){
                return _.isUndefined(this.id);
            },
            getFee: function() {
                try {
                    if (this.fees[0].fee_without_retirement === undefined) {
                        return 0;
                    }
                    return this.fees[0].fee_without_retirement;
                } catch (e) {
                    return 0
                }
            }
        },
        events: {
            'click .save-spec': 'submitForm',
            'click .delete-spec': 'destroy'
        },
        ui:{
            fee: '.fee',
            name: '.name',
            saveButton: '.save-spec',
            form: 'form',
            master: '.is_master'
        },
        submitForm: function(e) {
            e.preventDefault();
            var that = this;

            ErrorHandler.clearErrors();
            if (!this.ui.master.is(":checked") && App.Var.straightProcessing) {
                this.ui.master.attr('checked', true);
            }

            this.model.set({
                master: this.ui.master.is(":checked")?1:null,
                fees: [{
                    'fee_without_retirement': Math.floor(this.$(".fee").val().moneyToFloat())
                    }],
                type: App.Const.BILLING_SPEC_TYPE_FLAT
            });

            this.model.save();
        },
        destroy: function(e) {
            e.preventDefault();

            if (this.model.isNew()) {
                Mod.billingSpecLayout.specFormRegion.close();
            } else {
                this.listenTo(this.model, 'syncSuccess', function(){
                    Mod.billingSpecLayout.specFormRegion.close();
                });
            }
            this.model.destroy();
        },
        onShow: function(){
            var that = this;
            that.listenTo(this.model, 'syncRequest', function(){
                that.ui.form.spinner128();
                that.listenTo(that.model, 'syncEnd', function(){
                    that.ui.form.spinner128(false);
                    that.stopListening(that.model, 'syncEnd');
                });
            });

            this.ui.fee.autoNumeric({mDec: '0', aSep: ',', aDec: '.', vMax: '99999999999999'});
        }
    });

    Mod.BillingSpecTierFormTopPart = Marionette.ItemView.extend({
        template: "#tplTierList",
        ui: {
            minimalFee: 'input.minimal-fee',
            name: 'input.name',
            master: 'input.is_master'
        },
        templateHelpers: {
            isNew: function(){
                return _.isUndefined(this.id);
            }
        },
        events:{
            'change': 'onChange'
        },
        onShow: function(){
            this.ui.minimalFee.autoNumeric({mDec: '0', aSep: ',', aDec: '.', vMax: '99999999999999.99'});
        },
        onChange: function(e){
            if (!this.ui.master.is(":checked") && App.Var.straightProcessing) {
                this.ui.master.attr('checked', true);
            }
            this.model.set({
                minimalFee: Math.floor(this.ui.minimalFee.val().moneyToFloat()),
                master: this.ui.master.is(":checked")?1:null
            });
        }
    });

    Mod.BillingSpecTierForm = Marionette.Layout.extend({
        regions: {
            dataRegion: '.dataRegion',
            tierRegion: '.tierRegion'
        },
        template: "#tplTierFeeForm",
        templateHelpers: {
            isNew: function(){
                return _.isUndefined(this.id);
            }
        },
        ui: {
            saveButton: '.save-spec',
            form: 'form',
            controls: '.form-group button'
        },

        events: {
            'click .save-spec': 'submitForm',
            'click .preview-fee': 'showFeePreview',
            'click .delete-spec': 'destroy'
        },

        submitForm: function(e)
        {
            e.preventDefault();
            var that = this;

            var hasError = false;
            App.tierCollectionView.children.each(function(tierItemView){
                if (tierItemView.ui.tierTop.hasClass('has-error')) {
                    hasError = true;
                }
            });

            if (hasError) {
                alert('You have to fix errors before save Billing Spec');
                return;
            }

            ErrorHandler.clearErrors();
            this.model.set({
                fees: App.tierCollectionView.collection.clearJSON(),
                type: App.Const.BILLING_SPEC_TYPE_TIER
            });

            this.model.save();
        },

        initialize: function() {

        },

        destroy: function(e){
            e.preventDefault();

            if (this.model.isNew()){
                Mod.billingSpecLayout.specFormRegion.close();
            } else {
                this.listenTo(this.model, 'syncSuccess', function(){
                    Mod.billingSpecLayout.specFormRegion.close();
                });
            }
            this.model.destroy();
        },

        showFeePreview: function(e) {
            e.stopPropagation();

            $(".modal-body").html("");
            $('#fee_preview').modal();
            $(".modal-body").spinner128();

            $.ajax({
               'url':  Routing.generate('rx_ria_billing_fee_preview'),
                data: {fees: App.tierCollectionView.collection.toJSON()},
                success: function(data){
                    $(".modal-body").html(data);
                    $(".modal-body").spinner128(false);

                }
            });

            return false;
        },
        onShow: function() {

            var that = this;
            that.listenTo(this.model, 'syncRequest', function(){
                that.$el.spinner128();
                that.listenTo(that.model, 'syncEnd', function(){
                    that.$el.spinner128(false);
                    that.stopListening(that.model, 'syncEnd');
                });
            });

            this.dataRegion.show(new Mod.BillingSpecTierFormTopPart({ model: this.model }));

            var tierCollection = new Mod.TierCollection();
            tierCollection.reset(this.model.get('fees'));

            if (tierCollection.length == 0) {
                var tier1 = new Mod.TierModel();
                tier1.set('fee_without_retirement', 0);
                tier1.set('tier_top', 0);
                tier1.set('is_last', 0);

                var tier2 = new Mod.TierModel();
                tier2.set('fee_without_retirement', 0);
                //tier2.set('tier_bottom', 0);
                tier2.set('is_last', 1);

                tierCollection.add(tier1);
                tierCollection.add(tier2);
            }

            App.tierCollectionView = new Mod.TierCollectionView({collection: tierCollection});

            this.tierRegion.show(App.tierCollectionView);
        }
    });

    Mod.CreateSpecNameForm = Marionette.ItemView.extend({
        template: "#tplCreateSpecName",
        events: {
            'click .create-spec': 'createSpec'
        },
        ui: {
            name: 'input.name',
            type: 'input[name="type"]'
        },
        createSpec: function(){
            ErrorHandler.clearErrors();
            var name = this.ui.name.val();

            if (name.length<2) {
                var e = {
                    "name":["This value is too short. It should have 2 characters or more."]
                };
                ErrorHandler.handle(e, '.billing form', {
                    fees: {
                        fieldSelector: '.tierRegion table tbody tr',
                        type: 'collection'
                    }
                });
                return false;
            }

            if (Mod.billingSpecCollection.checkName(name)) {
                var e = {
                    "name":["This name is already used, please specify another one."]
                };
                ErrorHandler.handle(e, '.billing form', {
                    fees: {
                        fieldSelector: '.tierRegion table tbody tr',
                        type: 'collection'
                    }
                });
                return false;
            }

            var model = new Mod.BillingExtendedSpecModel();

            model.set('name', name);
            model.set('type', this.ui.type.filter(':checked').val());

            Mod.billingSpecCollection.add(model);
            Mod.billingSpecCollectionView.children.last().selected();

            App.vent.trigger('extendedModel:fetch', model);
            this.ui.name.val('');
        }
    });


    Mod.BillingSpecLayout = Marionette.Layout.extend({
        regions: {
            newFormRegion: '#newFormRegion',
            specListRegion: '#specListRegion',
            specFormRegion: '#specFormRegion'
        },
        initialize: function(){

            this.specListRegion.show(Mod.billingSpecCollectionView);

            this.newFormRegion.show(new Mod.CreateSpecNameForm());
        }
    });

    // Module constructor
    Mod.addInitializer(function() {

        Mod.billingSpecCollection = new Mod.BillingSpecCollection;

        Mod.billingSpecCollectionView = new Mod.BillingSpecCollectionView({collection: Mod.billingSpecCollection});

        Mod.billingSpecLayout = new Mod.BillingSpecLayout({ el: "#mainApp" });

        Mod.billingSpecCollection.fetch({reset: true});

        App.billingSpecCollection = Mod.billingSpecCollection;

        App.vent.on('extendedModel:fetch', function(model){
            App.currentModel = model;
            if (model.get("type") == App.Const.BILLING_SPEC_TYPE_FLAT) {
                Mod.billingSpecLayout.specFormRegion.show(new Mod.BillingSpecFlatForm({model: model}));
            } else {
                Mod.billingSpecLayout.specFormRegion.show(new Mod.BillingSpecTierForm({model: model}));
            }

        });

    });

});