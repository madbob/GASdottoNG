var graphGrownFactor = 35;

function doEmpty(target) {
    $(target).empty().css('height', 'auto').append($('#templates .alert').clone());
}

function runSummaryStats() {
    var start = $('#stats-summary-form input[name=startdate]').val();
    var end = $('#stats-summary-form input[name=enddate]').val();

    $.getJSON('/stats/summary', {
        start: start,
        end: end
    }, function(data) {
        if (data.expenses.labels.length == 0) {
            doEmpty('#stats-generic-expenses');
        }
        else {
            $('#stats-generic-expenses').empty().css('height', data.expenses.labels.length * graphGrownFactor);
            new Chartist.Bar('#stats-generic-expenses', data.expenses, {
                horizontalBars: true,
                axisX: {
                    onlyInteger: true
                },
                axisY: {
                    offset: 220
                },
            });
        }

        if (data.users.labels.length == 0) {
            doEmpty('#stats-generic-users');
        }
        else {
            $('#stats-generic-users').empty().css('height', data.users.labels.length * graphGrownFactor);
            new Chartist.Bar('#stats-generic-users', data.users, {
                horizontalBars: true,
                axisX: {
                    onlyInteger: true
                },
                axisY: {
                    offset: 220
                },
            });
        }
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
        if (data.expenses.labels.length == 0) {
            doEmpty('#stats-products-expenses');
        }
        else {
            $('#stats-products-expenses').empty().css('height', data.expenses.labels.length * graphGrownFactor);
            new Chartist.Bar('#stats-products-expenses', data.expenses, {
                horizontalBars: true,
                axisX: {
                    onlyInteger: true
                },
                axisY: {
                    offset: 220
                },
            });
        }

        if (data.users.labels.length == 0) {
            doEmpty('#stats-products-users');
        }
        else {
            $('#stats-products-users').empty().css('height', data.users.labels.length * graphGrownFactor);
            new Chartist.Bar('#stats-products-users', data.users, {
                horizontalBars: true,
                axisX: {
                    onlyInteger: true
                },
                axisY: {
                    offset: 220
                },
            });
        }
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
