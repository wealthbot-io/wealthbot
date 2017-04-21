/**
 * Created by amalyuhin on 14.01.14.
 */
$(function() {
    updateManageAccountSettings();
});

function updateManageAccountSettings() {
    var container = $('#result_container');
    container.spinner32();

    $.ajax({
        url: Routing.generate('rx_client_change_profile_your_portfolio'),
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                container.html(response.content);
                drawModelCharts('.pie-chart');
            }
        },
        complete: function() {
            container.spinner32(false);
        }
    });
}