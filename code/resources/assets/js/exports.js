require('jquery-ui/ui/widgets/draggable');
require('jquery-ui/ui/widgets/droppable');
require('./jquery.TableCSVExport');

import utils from "./utils";

class Exports {
    static init(container)
    {
        $('#import_csv_sorter .im_draggable', container).each(function() {
            $(this).draggable({
                helper: 'clone',
                revert: 'invalid'
            });
        });

        $('#import_csv_sorter .im_droppable', container).droppable({
            drop: function(event, ui) {
                var node = ui.draggable.clone();
                node.find('input:hidden').attr('name', 'column[]');
                $(this).find('.column_content').empty().append(node.contents());
            }
        });

        $('.table_to_csv', container).click(function(e) {
            e.preventDefault();
            var target = $(this).attr('data-target');
            var data = $(target).TableCSVExport({
                delivery: 'download',
                filename: _('bilanci_ricalcolati.csv')
            });
        });

        $('.export-custom-list', container).click(function(event) {
            event.preventDefault();

            var printable = new Array();

            var explicit_target = $(this).attr('data-target');
            if (explicit_target) {
                $(explicit_target).find('.accordion-item:visible').each(function() {
                    printable.push($(this).attr('data-element-id'));
                });
            }
            else {
                /*
                    Questo è per gestire il caso speciale dell'esportazione dei
                    prodotti di un fornitore, i quali potrebbero essere visualizzati
                    (e dunque filtrati) in una .loadablelist o nella tabella di
                    modifica rapida
                */
                var tab = $(this).closest('.tab-pane').find('.tab-pane.active');

                if (tab.hasClass('details-list')) {
                    tab.find('.loadable-list .accordion-item:visible').each(function() {
                        printable.push($(this).attr('data-element-id'));
                    });
                }
                else {
                    tab.find('.table tr:visible').each(function() {
                        printable.push($(this).attr('data-element-id'));
                    });
                }
            }

            var data = {};

            var parent_form = utils.formByButton($(this));
            if (parent_form.length != 0) {
                data = parent_form.serializeArray();
                for (var i = 0; i < printable.length; i++) {
                    data.push({name: 'printable[]', value: printable[i]});
                }
            }
            else {
                data = {printable: printable};
            }

            var url = $(this).attr('data-export-url') + '?' + $.param(data);
            window.open(url, '_blank');
        });
    }
}

export default Exports;
