Util.alertTimeout = 8000;

// Alerts
Util.AlertView = Backbone.View.extend({
    el: '#jsAlert',

    template: null,

    timer: null,

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
        }, Util.alertTimeout);
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

Util.alert = function (type, message) {
    if (_.isUndefined(alertView)) {
        var alertView = new Util.AlertView();
    }

    alertView.render({'type': type, 'message': message}).el;
};

// Warning flush message
Util.warning = function (message) { Util.alert('warning', message) };

// Info flush message
Util.info = function (message) { Util.alert('info', message) };

// Error flush message
Util.error = function (message) { Util.alert('error', message) };

// Success flush message
Util.success = function (message) { Util.alert('success', message) };