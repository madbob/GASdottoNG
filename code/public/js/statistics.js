var graphGrownFactor = 35;

function runSummaryStats() {
    var start = $('#stats-summary-form input[name=startdate]').val();
    var end = $('#stats-summary-form input[name=enddate]').val();

    $.getJSON('/stats/summary', {
        start: start,
        end: end
    }, function(data) {
        $('#stats-generic-expenses').css('height', data.expenses.labels.length * graphGrownFactor);
        new Chartist.Bar('#stats-generic-expenses', data.expenses, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
        $('#stats-generic-users').css('height', data.users.labels.length * graphGrownFactor);
        new Chartist.Bar('#stats-generic-users', data.users, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
    });
}

function runSupplierStats() {
    var supplier = $('#stats-supplier-form select[name=supplier] option:selected').val();
    var start = $('#stats-supplier-form input[name=startdate]').val();
    var end = $('#stats-supplier-form input[name=enddate]').val();

    $.getJSON('/stats/supplier', {
        start: start,
        end: end,
        supplier: supplier
    }, function(data) {
        $('#stats-products-expenses').css('height', data.expenses.labels.length * graphGrownFactor);
        new Chartist.Bar('#stats-products-expenses', data.expenses, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
        $('#stats-products-users').css('height', data.users.labels.length * graphGrownFactor);
        new Chartist.Bar('#stats-products-users', data.users, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
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
