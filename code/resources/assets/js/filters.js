class Filters {
    static tableFilters(table_id) {
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
    }

    static iconsLegendTrigger(node, legend_class) {
        if (node.hasClass('dropdown-toggle')) {
            return;
        }

        var legend = node.closest(legend_class);
        var target = legend.attr('data-list-target');

        var iter_selector = '';
        if (legend_class == '.icons-legend') {
            iter_selector = '.loadable-list' + target + ' .accordion-item';
        }
        else {
            iter_selector = '.table' + target + ' tbody tr';
        }

        if (node.hasClass('active')) {
            node.removeClass('active');
            if (node.is('a')) {
                node.closest('.dropdown-menu').siblings('.dropdown-toggle').removeClass('active');
            }

            $(iter_selector).each(function() {
                $(this).show();
            });
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

                if (show) {
                    $(this).show();
                }
                else {
                    $(this).hide();
                }
            });
        }
    }
}

export default Filters;
