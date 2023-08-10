import utils from "./utils";

class Filters {
    static init(container)
    {
        $('.icons-legend button, .icons-legend a', container).click((e) => {
            e.preventDefault();
            this.iconsLegendTrigger($(e.currentTarget), '.icons-legend');
        });

        $('.table-icons-legend button, .table-icons-legend a', container).click((e) => {
            e.preventDefault();
            this.iconsLegendTrigger($(e.currentTarget), '.table-icons-legend');
        });

        $('.table-text-filter', container).keyup((e) => {
            this.tableFilters($(e.currentTarget).attr('data-table-target'));
        });

        $('.table-number-filters input.table-number-filter', container).keyup((e) => {
            this.tableFilters($(e.currentTarget).closest('.table-number-filters').attr('data-table-target'));
        });

        $('.table-number-filters input[name=filter_mode]', container).change((e) => {
            $(e.currentTarget).closest('.input-group').find('input.table-number-filter').keyup();
        });

        $('.table-filters input:radio', container).change((e) => {
            this.tableFilters($(e.currentTarget).closest('.table-filters').attr('data-table-target'));
        });

        $('.table-sorter a', container).click(function(e) {
    		e.preventDefault();
    		var target = $($(this).closest('.table-sorter').attr('data-table-target'));
    		var attribute = $(this).attr('data-sort-by');
            var is_numeric = $(this).attr('data-numeric-sorting') ? true : false;

            target.each(function() {
                var target_body = $(this).find('tbody');
                target_body.find('> .table-sorting-header').addClass('d-none').filter('[data-sorting-' + attribute + ']').removeClass('d-none');

                target_body.find('> tr[data-sorting-' + attribute + '], .table-sorting-header:visible').sort(function(a, b) {
                    var attr_a = $(a).attr('data-sorting-' + attribute);
                    var attr_b = $(b).attr('data-sorting-' + attribute);

                    if (is_numeric) {
                        return parseFloat(attr_a) - parseFloat(attr_b);
                    }
                    else {
                         return attr_a.localeCompare(attr_b);
                     }
                }).each(function() {
                    $(this).appendTo(target_body);
                });

                target_body.find('> tr.do-not-sort').each(function() {
                    $(this).appendTo(target_body);
                });
            });
    	});

        $('.form-filler button[type=submit]', container).click(function(event) {
            event.preventDefault();

			var button = $(this);
			button.addClass('disabled');
            var form = button.closest('.form-filler');
            var target = $(form.attr('data-fill-target'));
            var data = form.find('input, select').serialize();

			var url = button.attr('data-action');
			if (url == null) {
				url = form.attr('data-action');
			}

            target.empty().append(utils.loadingPlaceholder());

            $.ajax({
                method: 'GET',
                url: url,
                data: data,
                dataType: 'html',

                success: function(data) {
					button.removeClass('disabled');
                    data = $(data);
                    target.empty().append(data);
                    utils.j().initElements(data);
                }
            });
        });
    }

    static compactFilter(master_selector, child_selector, explode_all)
    {
        if (explode_all) {
            $(master_selector).each(function() {
                $(this).closest('.filter-master-block').show();
            });
        }
        else {
            setTimeout(() => {
                $(master_selector).each(function() {
                    let master_block = $(this).closest('.filter-master-block');
                    if (master_block.length != 0) {
                        let children = $(this).find(child_selector);
                        let hidden = children.filter('.hidden');
                        if (hidden.length == children.length) {
                            master_block.hide();
                        }
                        else {
                            master_block.show();
                        }
                    }
                });
            }, 100);
        }
    }

    static tableFilters(table_id)
    {
        var filters = $('[data-table-target="' + table_id + '"]');

        $('table' + table_id + ' tbody tr').each(function() {
            var display = true;
            var row = $(this);

            filters.each(function() {
                if ($(this).hasClass('table-number-filters')) {
                    /*
                        Filtro numerico: composto da un valore numerico e da una
                        modalità di confronto
                    */
                    let text = $(this).find('input.table-number-filter').val().toLowerCase();
                    if (text == '') {
                        display = display && true;
                    }
                    else {
                        let number = parseFloat(text);
                        let mode = $(this).find('input[name=filter_mode]:checked').val();
                        let cell = row.find('.text-filterable-cell');
                        let val = parseFloat(cell.text());

                        if (mode == 'min' && val <= number) {
                            display = display && true;
                        }
                        else if (mode == 'max' && val >= number) {
                            display = display && true;
                        }
                        else {
                            display = display && false;
                        }
                    }
                }
                else if ($(this).hasClass('table-filters')) {
                    /*
                        Filtro univoco: attiva le righe a seconda del radio
                        button selezionato
                    */
                    let selected = $(this).find('input:radio:checked');
                    let value = selected.val();

                    if (value == 'all') {
                        display = display && true;
                    }
                    else {
                        let attribute = selected.attr('name');
                        let attr = row.attr('data-filtered-' + attribute);
                        display = display && (attr == value);
                    }
                }
                else if ($(this).hasClass('table-text-filter')) {
                    /*
                        Filtro testuale: ricerca una stringa tra tutte le celle
                        filtrabili della riga
                    */
                    let text = $(this).val().toLowerCase();
                    if (text == '') {
                        display = display && true;
                    }
                    else {
                        display = false;

                        row.find('.text-filterable-cell').each(function() {
                            if ($(this).text().toLowerCase().indexOf(text) != -1) {
                                display = true;
                                return false;
                            }
                        });
                    }
                }

                if (display == false) {
                    return false;
                }
            });

            row.toggleClass('hidden', (display == false));
        });

        this.compactFilter('table' + table_id, 'tbody tr', false);
    }

    static iconsLegendTrigger(node, legend_class)
    {
        if (node.hasClass('dropdown-toggle')) {
            return;
        }

        let legend = node.closest(legend_class);
        let target = legend.attr('data-list-target');

        let master_selector = '';
        let child_selector = '';

        if (legend_class == '.icons-legend') {
            master_selector = '.loadable-list' + target;
            child_selector = '.accordion-item';
        }
        else {
            master_selector = '.table' + target;
            child_selector = 'tbody tr';
        }

        let iter_selector = master_selector + ' ' + child_selector;

        if (node.hasClass('active')) {
            node.removeClass('active');
            if (node.is('a')) {
                node.closest('.dropdown-menu').siblings('.dropdown-toggle').removeClass('active');
            }

            $(iter_selector).toggleClass('hidden', false);
            this.compactFilter(master_selector, child_selector, true);
        }
        else {
            /*
                Qui devo considerare la somma di tutti i filtri che sono stati
                attivati: se un elemento risulterebbe nascosto a fronte del
                click su un attributo, potrebbero essercene altri che lo
                mantengono visibile
            */
            legend.find('button, a').removeClass('active');

            node.addClass('active');
            if (node.is('a')) {
                node.closest('.dropdown-menu').siblings('.dropdown-toggle').addClass('active');
            }

            var c = node.find('i').attr('class');

            $(iter_selector).each(function() {
                var show = false;

                $(this).find('i').each(function() {
                    var icons = $(this).attr('class');
                    show = (icons == c);
                    if (show) {
                        return false;
                    }
                });

                $(this).toggleClass('hidden', show == false);
            });

            this.compactFilter(master_selector, child_selector, false);
        }
    }
}

export default Filters;
