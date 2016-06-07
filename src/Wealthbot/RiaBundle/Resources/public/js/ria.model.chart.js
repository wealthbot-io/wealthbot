/**
 * Created with JetBrains PhpStorm.
 * User: vova
 * Date: 05.02.13
 * Time: 13:01
 * To change this template use File | Settings | File Templates.
 */

$(function () {
    drawModelCharts('.pie-chart');

    $('.stats-chart').each(function (key, element) {
        drawStatsChart(element)
    });
});

function drawModelCharts(selector)
{
    $(selector).each(function (key, element) {
        drawModelChart(element);
    });
}

function getDateByTimestamp(timestamp)
{
    var date = new Date(timestamp);

    return (date.getMonth()+1)+'/'+(date.getDate())+'/'+date.getFullYear()
}

function drawModelChart(element, options) {
    options = options || {};

    var entities = $(element).attr('data-entities');
    var jsonEntities = JSON.parse(entities);
    var defaultOptions = {
        series: {
            pie: {
                show: true,
                radius: 1,
                label: {
                    show: true,
                    radius: 2/3,
                    formatter: function(label, series){
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;">'+series.data[0][1].toFixed(2)+'%</div>';
                    },
                    threshold: 0.1
                }
            }
        },
        grid: {
            hoverable: true,
            clickable: true
        },
        tooltip: true,
        tooltipOpts: {
            shifts: {
                x: 20,
                y: 0
            },
            defaultTheme: true
        },
        legend: {
            show: false
        },
        colors: ["#8e388e", "#009ACD", "#c67171", "#7ccd7c", "#8a2be2",  "#3a5fcd", "#eeb4b4", "#20b2aa", "#6495ed", "#1c86ee",
            "#cd919e", "#4f94cd", "#8470ff", "#00c957", "#cd6889", "#00c78c", "#008080", "#800080",
            "#33a1c9", "#4876ff", "#cd96cd"]
    };

    $.extend(true, defaultOptions, options);

    $.plot($(element), jsonEntities, defaultOptions);

    function pieHover(event, pos, obj)
    {
        if (!obj)
            return;
        var percent = parseFloat(obj.series.percent).toFixed(2);
        $("#flotTip").html('<span style="font-weight: bold; color: black">'+obj.series.label+' ('+percent+'%)</span>');
    }

    $(element).bind("plothover", pieHover);
}

function drawStatsChart(element)
{
    var values = $(element).attr('data-values');
    var jsonValues = JSON.parse(values);

    if (jsonValues.length > 0) {
        var data_maximum = parseFloat($(element).attr('data-maximum'));
        var minDateTimestamp = jsonValues[0][0];
        var maxDateTimestamp = jsonValues[jsonValues.length-1][0];
        var minDate = getDateByTimestamp(minDateTimestamp);
        var maxDate = getDateByTimestamp(maxDateTimestamp);

        var plot = $.plot(element, [ { data: jsonValues, label: "Your portfolio"} ], {
            series: {
                lines: {
                    show: true,
                    lineWidth: 3,
                    fill: true,
                    fillColor: {
                        colors: [ { opacity: 0.08 }, { opacity: 0.01 } ]
                    }
                },
                shadowSize: 2
            },
            grid: {
                hoverable: true,
                clickable: true,
                tickColor: "#eee",
                borderWidth: 0
            },
            colors: ["#0088cc"],
            xaxis: {
                ticks: [ [minDateTimestamp, minDate], [maxDateTimestamp, maxDate] ],
                labelWidth: 1
            },
            yaxis: {
                tickSize: data_maximum/4-(data_maximum*0.02)
            },
            crosshair: {
                mode: "x",
                color: "rgba(0, 0, 0, 0.80)"
            },
            legend: {
                show: false
            }
        });

        function updateCrosshairPosition(pos)
        {
            var data = plot.getData();

            var series = data[0];

            var axes = plot.getAxes();
            var xaxisValue = $("#xaxis_value");
            var yaxisValue = $("#yaxis_value");

            if (pos.x < axes.xaxis.min) {
                xaxisValue.text(getDateByTimestamp(series.data[0][0]));
                yaxisValue.text(parseFloat(series.data[0][1]).formatMoney(2,'.',','));
                return;
            } else if (pos.x > axes.xaxis.max) {
                xaxisValue.text(getDateByTimestamp(series.data[series.data.length-1][0]));
                yaxisValue.text(parseFloat(series.data[series.data.length-1][1]).formatMoney(2,'.',','));
                return;
            } else if (pos.y < axes.yaxis.min || pos.y > axes.yaxis.max){
                return;
            }

            for (var j = 0; j < series.data.length; ++j) {
                if (series.data[j][0] > pos.x) {
                    break;
                }
            }

            var y,
                p1 = series.data[j - 1],
                p2 = series.data[j];

            if (p1 == null) {
                y = p2[1];
            } else if (p2 == null) {
                y = p1[1];
            } else {
                y = p1[1] + (p2[1] - p1[1]) * (pos.x - p1[0]) / (p2[0] - p1[0]);
            }

            xaxisValue.text(getDateByTimestamp(pos.x));
            yaxisValue.text(parseFloat(y.toFixed(2)).formatMoney(2,'.',','));
        }

        var previousPoint = null;
        $(element).bind("plothover", function (event, pos, item) {

            updateCrosshairPosition(pos);

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;
                    $("#tooltip").remove();

                    var x = getDateByTimestamp(item.datapoint[0]),
                        y = parseFloat(item.datapoint[1].toFixed(2)).formatMoney(2,'.',',');

                    showTooltip(item.pageX, item.pageY, item.series.label + " of " + x + " = $" + y);
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });

    } else {
        $(element).append("<div class='non-stats-chart-initial-message'>Track the growth of your portfolio here after you've been on our system for a month.</div>");
    }
}

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y + 5,
        left: x + 5,
        border: '1px solid #fdd',
        padding: '2px',
        'background-color': '#dfeffc',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
}