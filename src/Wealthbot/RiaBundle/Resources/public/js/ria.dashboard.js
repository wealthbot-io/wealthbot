$(function() {
    buildDashboardBoxes();
    showSecurityAnalyticsChart('.securities-analytics-chart');

    var start_sortable_box;
    $(".dashboard-box").sortable({
        placeholder: 'ria-dashboard-cart-highlight',
        items: '.dashboard-cart',
        connectWith: '.dashboard-box',
        tolerance: 'pointer',
        start: function() {
            start_sortable_box = this;
        },
        receive: function(event, ui) {
            var item = $(ui.item);
            var swap_element = $(this).find('.dashboard-cart:not(#'+item.attr('id')+')');
            $(start_sortable_box).append(swap_element);

            var boxes = {};
            $('.dashboard-box').each(function(index, element) {
                boxes[index] = {
                    'template': $(element).find('.dashboard-cart').attr('id'),
                    'sequence' : $(element).attr('data-id')
                }
            });

            $.ajax({
                url: $('#swappable_content').attr('data-url'),
                dataType: 'json',
                data: {'boxes': boxes}
            });
        }
    });

    $(document).on('click','#most_recent_activity_cart table thead a, #most_recent_activity_cart .delete-most-recent-activity', function(event) {
        var btn = $(this);
        var url = btn.attr('href');
        var block = btn.closest('.dashboard-box');

        block.html('<img class="ajax-loader" src="/img/ajax-loader.gif" style="margin-left:5px;vertical-align:middle;" />');

        $.ajax({
            url: url,
            dataType: 'json',
            data: { block: 'most_recent_activity' },
            success: function(response) {
                if (response.status == 'success') {
                    block.html(response.content);
                }
            }
        });

        event.preventDefault();
    });
});

function buildDashboardBoxes()
{
    var content = $('#swappable_content');
    var data = content.attr('data-sequence');

    if (typeof data !== 'undefined' && data.length > 0) {
        var boxesSequence = JSON.parse(data);

        for (var i=0; i < boxesSequence.length; i++) {
            var template = boxesSequence[i]['template'];
            var sequence = boxesSequence[i]['sequence'];
            var swapped_element = $('#'+template);
            var swapped_element_block = swapped_element.parent();

            var block = content.find('.dashboard-box[data-id="'+sequence+'"]');
            var tmp_html = block.html();
            block.html(swapped_element);
            swapped_element_block.html(tmp_html);

            block.html();

        }
    }

    content.show();
}

function showSecurityAnalyticsChart(selector) {
    if ($(selector).length > 0) {
        var options = {
            series: {
                pie: {
                    label: {
                        formatter: function(label, series){
                            return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+series.percent.toFixed(2)+'%</div>';
                        }
                    }
                }
            },
            legend: {
                show: true,
                labelFormatter: function(label, series) {
                    return label + "<br/> $" + series.data[0][1].formatMoney(2, '.', ',');
                },
                labelBoxBorderColor: false
            }
        };

        drawModelChart(selector, options);
    }
}