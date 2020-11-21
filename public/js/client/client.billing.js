var billing = {};

$(function() {

    billing.curYear = null;
    billing.curQuarter = null;
    billing.is_client_view = null;
    billing.showedQuarter = null;
    billing.client_created_at = null;
    billing.container = null;

    billing.init = function() {
        var today = new Date();

        billing.curYear = today.getFullYear();
        billing.curQuarter = Math.floor((today.getMonth() + 3) / 3);
        billing.showedQuarter = billing.curYear + '-' + billing.curQuarter;
        billing.is_client_view = 0;
        billing.container = $('#billingDataContent');
        billing.client_created_at = billing.container.attr('data-client-created-at');

        billing.initQuarters();
    };

    billing.initQuarters = function(){
        var quarters = $('a[data-role="quarter"]');
        var currentDate = new Date();
        var riaCreatedAt = new Date(billing.client_created_at);

        //quarters.hide();
        //quarters.removeClass('active');

        for (var i=1; i<=4; i++) {
            var date = new Date(billing.curYear + '/' + (i * 3) + '/01 12:00:00 AM');
            date.setMonth(date.getMonth() + 1);
            if (date.getTime() > riaCreatedAt.getTime()) {
                date = new Date(billing.curYear + '/' + ((i-1) * 3 + 1) + '/01 12:00:00 AM');
                if (date.getTime() < currentDate.getTime()) {
                    quarters.eq(i-1).show();
                    if (billing.showedQuarter == billing.curYear + '-' + i) {
                        quarters.eq(i-1).addClass('active');
                    }
                }
            }
        }

    };

    billing.changeYear = function(e) {
        var self = $(this);

        billing.curYear = self.val();
        billing.initQuarters();
    };

    $(document).on('change','#years', billing.changeYear);

    billing.clickQuarter = function(e) {
        e.preventDefault();
        var self = $(this);

        $('a[data-role="quarter"]').removeClass('active');
        self.addClass('active');

        billing.curQuarter = self.data('value');

        var url = Routing.generate('wealthbot_client_billing_period', {
            year: billing.curYear,
            quarter: billing.curQuarter,
            is_client_view: billing.is_client_view
        });

        self.addClass('js-loading disabled');

        $.ajax({
            url: url,
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    billing.container.html(response.content);
                    billing.showedQuarter = billing.curYear + '-' + billing.curQuarter;
                    billing.initQuarters();
                }
            }
        });
    };

    $(document).on('click','a[data-role="quarter"]', billing.clickQuarter);

    // Start module
    billing.init();
});