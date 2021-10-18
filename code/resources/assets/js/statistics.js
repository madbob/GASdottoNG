import Chartist from 'chartist';
import utils from "./utils";

function doEmpty(target) {
    $(target).empty().css('height', 'auto').append($('#templates .alert').clone());
}

function commonGraphConfig()
{
    return {
        horizontalBars: true,
        axisX: {
            onlyInteger: true
        },
        axisY: {
            offset: 220
        },
    };
}

function doGraph(selector, data)
{
    if (data.labels.length == 0) {
        doEmpty(selector);
    }
    else {
        $(selector).empty().css('height', data.labels.length * 40);
        new Chartist.Bar(selector, data, commonGraphConfig());
    }
}

function doGraphs(group, data)
{
    doGraph('#stats-' + group + '-expenses', data.expenses);
    doGraph('#stats-' + group + '-users', data.users);
    doGraph('#stats-' + group + '-categories', data.categories);
}

function loadingGraphs(group)
{
    $('#stats-' + group + '-expenses').empty().append(utils.loadingPlaceholder());
    $('#stats-' + group + '-users').empty().append(utils.loadingPlaceholder());
    $('#stats-' + group + '-categories').empty().append(utils.loadingPlaceholder());
}

function runSummaryStats() {
    loadingGraphs('generic');

    $.getJSON('/stats/summary', {
        start: $('#stats-summary-form input[name=startdate]').val(),
        end: $('#stats-summary-form input[name=enddate]').val(),
    }, function(data) {
        doGraphs('generic', data);
    });
}

function runSupplierStats() {
    loadingGraphs('products');

    $.getJSON('/stats/supplier', {
        supplier: $('#stats-supplier-form select[name=supplier] option:selected').val(),
        start: $('#stats-supplier-form input[name=startdate]').val(),
        end: $('#stats-supplier-form input[name=enddate]').val(),
    }, function(data) {
        doGraphs('products', data);
    });
}

$(document).ready(function() {
    if ($('#stats-summary-form').length != 0) {
        runSummaryStats();

        $('#stats-summary-form').submit(function(event) {
            event.preventDefault();
            runSummaryStats();
        });
    }

    if ($('#stats-supplier-form').length != 0) {
        runSupplierStats();

        $('#stats-supplier-form').submit(function(event) {
            event.preventDefault();
            runSupplierStats();
        });
    }
});
