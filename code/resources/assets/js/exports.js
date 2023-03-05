require('jquery-ui/ui/widgets/draggable');
require('jquery-ui/ui/widgets/droppable');

import utils from "./utils";

class Exports {
    static init(container)
    {
        this.importers(container);

        $('.export-custom-list', container).click(function(event) {
            event.preventDefault();

            var printable = new Array();
			var items = null;

            var explicit_target = $(this).attr('data-target');
            if (explicit_target) {
                items = $(explicit_target).find('.accordion-item:visible');
            }
            else {
                /*
                    Questo è per gestire il caso speciale dell'esportazione dei
                    prodotti di un fornitore, i quali potrebbero essere visualizzati
                    (e dunque filtrati) in una .loadablelist o nella tabella di
                    modifica rapida
                */
                var tab = $(this).closest('.tab-pane').find('.tab-pane.active');
				var list = tab.find('.loadable-list');
                if (list.length != 0) {
                    items = list.find('.accordion-item:visible');
                }
                else {
                    items = tab.find('.table tr:visible');
                }
            }

			if (items) {
				items.each(function() {
					printable.push($(this).attr('data-element-id'));
				});
			}

            var data = {};

            var parent_form = utils.formByButton($(this));
            if (parent_form.length != 0) {
                data = parent_form.serializeArray();
                for (var i = 0; i < printable.length; i++) {
                    data.push({
						name: 'printable[]',
						value: printable[i]
					});
                }
            }
            else {
                data = {
					printable: printable
				};
            }

            var url = $(this).attr('data-export-url') + '?' + $.param(data);
            window.open(url, '_blank');
        });
    }

	static importers(container)
	{
		$('#import_csv_sorter .im_draggable', container).each(function() {
            $(this).draggable({
                helper: 'clone',
                revert: 'invalid'
            });
        });

        $('#import_csv_sorter .im_droppable', container).droppable({
			over: function(event, ui) {
				$(this).addClass('bg-success text-white');
			},
			out: function(event, ui) {
				$(this).removeClass('bg-success text-white');
			},
            drop: function(event, ui) {
                var node = ui.draggable.clone();
                node.find('input:hidden').attr('name', 'column[]');
                $(this).removeClass('bg-success text-white').find('.column_content').empty().append(node.contents());
            }
        });
	}
}

export default Exports;
