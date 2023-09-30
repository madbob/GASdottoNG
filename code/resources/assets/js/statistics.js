import { BarChart } from 'chartist';
import utils from "./utils";

class Statistics {
    static init(container)
    {
        setTimeout(() => {
            if ($('#stats-summary-form', container).length != 0) {
                this.runSummaryStats();

                $('#stats-summary-form').submit((event) => {
                    event.preventDefault();
                    this.runSummaryStats();
                });
            }

            if ($('#stats-supplier-form', container).length != 0) {
                this.runSupplierStats();

                $('#stats-supplier-form').submit((event) => {
                    event.preventDefault();
                    this.runSupplierStats();
                });
            }
        }, 500);
    }

    static doEmpty(target)
    {
        $(target).empty().css('height', 'auto').append($('#templates .alert').clone());
    }

    static commonGraphConfig()
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

    static doGraph(selector, data)
    {
        if (data.labels.length == 0) {
            this.doEmpty(selector);
        }
        else {
            if ($(selector).length != 0) {
                $(selector).empty().css('height', data.labels.length * 40);
                new BarChart(selector, data, this.commonGraphConfig());
            }
        }
    }

    static doGraphs(group, data)
    {
        this.doGraph('#stats-' + group + '-expenses', data.expenses);
        this.doGraph('#stats-' + group + '-users', data.users);
        this.doGraph('#stats-' + group + '-categories', data.categories);
    }

    static loadingGraphs(group)
    {
        $('#stats-' + group + '-expenses').empty().append(utils.j().makeSpinner());
        $('#stats-' + group + '-users').empty().append(utils.j().makeSpinner());
        $('#stats-' + group + '-categories').empty().append(utils.j().makeSpinner());
    }

    static runSummaryStats()
    {
        this.loadingGraphs('generic');

        $.getJSON('/stats/summary', {
            startdate: $('#stats-summary-form input[name=startdate]').val(),
            enddate: $('#stats-summary-form input[name=enddate]').val(),
            target: $('#stats-summary-form input[name=target]').val(),
            type: $('#stats-summary-form select[name=type]').val(),
            format: 'json',
        }, (data) => {
            this.doGraphs('generic', data);
        });
    }

    static runSupplierStats()
    {
        this.loadingGraphs('products');

        $.getJSON('/stats/supplier', {
            supplier: $('#stats-supplier-form select[name=supplier] option:selected').val(),
            startdate: $('#stats-supplier-form input[name=startdate]').val(),
            enddate: $('#stats-supplier-form input[name=enddate]').val(),
            target: $('#stats-supplier-form input[name=target]').val(),
            type: $('#stats-summary-form select[name=type]').val(),
            format: 'json',
        }, (data) => {
            this.doGraphs('products', data);
        });
    }
};

export default Statistics;
