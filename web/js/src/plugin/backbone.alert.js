// Alerts
App.AlertView = Backbone.View.extend({
    el: '#jsAlert',

    template: null,

    timer: null,
    timeout: 8000,

    events: {
        'click [data-dismiss="js-alert"]': 'clickAlertClose'
    },

    initialize: function () {
        this.template = _.template($('#tpl_alertMessage').html());
        this.setTimer();
    },

    setTimer: function () {
        var self = this;

        window.clearTimeout(this.timer);

        this.timer = window.setTimeout(function () {
            self.$el.hide();
        }, this.alertTimeout);
    },

    render: function (options) {
        this.$el.html(this.template({
            'type': options.type,
            'message': options.message
        }));

        this.$el.show();
        this.setTimer();

        return this;
    },

    clickAlertClose: function (e) {
        e.preventDefault();
        this.$el.hide();
        window.clearTimeout(this.timer);
    }
});

App.alert = function (type, message) {
    if (_.isUndefined(App.alertView)) {
        App.alertView = new App.AlertView();
    }

    App.alertView.render({'type': type, 'message': message}).el;
};

// Warning flush message
App.warning = function (message) { App.alert('warning', message) };

// Info flush message
App.info = function (message) { App.alert('info', message) };

// Error flush message
App.error = function (message) { App.alert('error', message) };

// Success flush message
App.success = function (message) { App.alert('success', message) };