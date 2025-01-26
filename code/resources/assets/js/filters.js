import utils from "./utils";

class Filters {
    static init(container)
    {
        $('.icons-legend button, .icons-legend a', container).click((e) => {
            e.preventDefault();
            this.iconsLegendTrigger($(e.currentTarget), '.icons-legend');
        });

        /*
            Questo serve ad intercettare tutti i pulsanti dei filtri nelle
            tabelle, sia le icone che i dropdown con scelte multiple
        */
        $('.table-icons-legend button, .table-icons-legend a', container).click((e) => {
            e.preventDefault();

            let button = $(e.currentTarget);
            if (button.attr('data-bs-toggle')) {
                let expanded = button.attr('aria-expanded') == 'true';
                button.toggleClass('active', expanded);
                if (expanded === false) {
                    let collapse_id = button.attr('href');
                    $(collapse_id).find('.active').click();
                }
            }
            else {
                this.iconsLegendTrigger(button, '.table-icons-legend');
            }
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
            let target = $($(this).closest('.table-sorter').attr('data-table-target'));
            let attribute = $(this).attr('data-sort-by');
            let is_numeric = $(this).attr('data-numeric-sorting') ? true : false;

            target.each(function() {
                let target_body = $(this).find('tbody');
                target_body.find('> .table-sorting-header').addClass('d-none').filter('[data-sorting-' + attribute + ']').removeClass('d-none');

                target_body.find('> tr[data-sorting-' + attribute + '], .table-sorting-header:visible').sort(function(a, b) {
                    let attr_a = $(a).attr('data-sorting-' + attribute);
                    let attr_b = $(b).attr('data-sorting-' + attribute);

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

            let button = $(this);
			button.addClass('disabled');
            let form = button.closest('.form-filler');
            let target = $(form.attr('data-fill-target'));
            let data = form.find('input, select').serialize();

            let url = button.attr('data-action');
			if (url == null) {
				url = form.attr('data-action');
			}

            target.empty().append(utils.j().makeSpinner());

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

        $('.columns-selector', container).on('click', '.dropdown-menu', (e) => {
            e.stopPropagation();
        }).on('change', 'input:checkbox', (e) => {
            e.preventDefault();
            e.stopPropagation();
            let check = $(e.currentTarget);
            let table_id = $(check).closest('.columns-selector').attr('data-target');
            let name = $(check).val();
            let show = $(check).prop('checked');
            $('#' + table_id).find('.order-cell-' + name).toggleClass('hidden', !show);
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
        let filters = $('[data-table-target="' + table_id + '"]');
        let table = $('table' + table_id);
        let elements = table.find('tbody tr');

        elements.each(function() {
            let display = true;
            let row = $(this);

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

                /*
                    Se almeno una condizione del filtro determina che l'elemento
                    non deve essere visualizzato, evito di valutare le altre ed
                    esco subito dal ciclo each()
                */
                if (display === false) {
                    return false;
                }
            });

            row.toggleClass('hidden', (display === false));
        });

        this.compactFilter('table' + table_id, 'tbody tr', false);
    }

    static iconsLegendTrigger(node, legend_class)
    {
        /*
            Se clicco l'intestazione di un dropdown, passo oltre. Qui interviene
            il JS di Bootstrap per aprire e chiudere il dropdown stesso
        */
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
        let iterables = $(iter_selector).filter(':not(.do-not-sort)');

        if (node.hasClass('active')) {
            node.removeClass('active');
            if (node.is('a')) {
                node.closest('.dropdown-menu').siblings('.dropdown-toggle').removeClass('active');
            }

            iterables.toggleClass('hidden', false);
            this.compactFilter(master_selector, child_selector, true);
            $(master_selector).trigger('inactive-filter');
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

            if (node.hasClass('show-all')) {
                iterables.toggleClass('hidden', false);
                this.compactFilter(master_selector, child_selector, true);
            }
            else {
                let c = node.find('i').attr('class');
                let count_hidden = 0;

                iterables.each(function() {
                    let show = false;

                    $(this).find('i').each(function() {
                        let icons = $(this).attr('class');
                        show = (icons == c);
                        if (show) {
                            return false;
                        }
                    });

                    $(this).toggleClass('hidden', show === false);

                    if (show === false) {
                        count_hidden++;
                    }
                });

                this.compactFilter(master_selector, child_selector, false);

                if (count_hidden == 0) {
                    $(master_selector).trigger('inactive-filter');
                }
                else {
                    $(master_selector).trigger('active-filter');
                }
            }
        }
    }
}

export default Filters;
