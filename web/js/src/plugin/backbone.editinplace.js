App.EditInPlaceInput = Backbone.View.extend({
    tagName: "form",

    events: {
        "submit": "save"
    },

    initialize: function (options) {
        _.extend(this, options);
    },

    render: function () {
        this.$el.html($("<input>", {
            value: this.model.get(this.field)
        }));
        return this;
    },

    save: function () {
        this.model.set(this.field, this.$el.find(this.element).val());
        return false;
    }
});

App.EditInPlaceSelect = Backbone.View.extend({});
App.EditInPlaceTextarea = Backbone.View.extend({});

App.EditInPlace = Backbone.View.extend({
    field: 'name',
    element: 'input',

    views: {
        input: App.EditInPlaceInput,
        select: App.EditInPlaceSelect,
        textarea: App.EditInPlaceTextarea
    },

    initialize: function (options) {
        _.extend(this, options);
        this.model.on('change', this.render, this);
    },

    events: {
        'click': 'edit'
    },

    render: function () {
        this.$el.html(this.model.get(this.field));
        return this;
    },

    edit: function () {
        var view = new this.views[this.element]({
            model: this.model,
            field: this.field,
            element: this.element
        }).render().el;

        this.$el.html(view);
        this.$el.find(this.element).select();
    }
});