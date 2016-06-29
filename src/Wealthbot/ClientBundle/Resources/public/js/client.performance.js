var performance = {};

$(function() {

    performance.curPeriod = null;
    performance.container = null;
    performance.is_client_view = null;

    performance.init = function() {
        performance.container = $('#performanceDataContent');
        performance.is_client_view = 0;
    };

    performance.clickPeriod = function(e) {
        e.preventDefault();

        var self = $(this);

        $('a[data-role="period"]').removeClass('active');
        self.addClass('active');

        performance.curPeriod = self.data('value');

        var url = Routing.generate('wealthbot_client_performance_period', {
            period: performance.curPeriod,
            account_id: $('#select_account_type').val(),
            is_client_view: performance.is_client_view
        });

        self.addClass('js-loading disabled');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    performance.container.html(response.content);
                    self.removeClass('js-loading disabled');
                }
            }
        });
    };

    $(document).on('click','a[data-role="period"]', performance.clickPeriod);

    // Start module
    performance.init();
});