window.$ = window.jQuery = global.$ = global.jQuery = require('jquery');
require('bootstrap');

import utils from "./utils";

class Products {
    static init(container)
    {
        /*
            Questo mostra/nasconde il trigger addizionale per modificare in
            blocco l'ordinabilità dei prodotti, nella griglia della modifica
            rapida
        */
        if (container.hasClass('products-grid')) {
            container.on('change', '.product-select', (e) => {
                let count = container.find('.product-select').filter(':checked').length;
                container.find('.massive-actions').toggleClass('hidden', count == 0);
            });

            container.on('change', '.massive-actions select', (e) => {
                let sel = $(e.currentTarget);
                let value = sel.val();
                let offset = sel.closest('th').index();

                container.find('.product-select').filter(':checked').each((index, item) => {
                    $(item).closest('tr').find('td').eq(offset).find('select').val(value);
                });
            });

            container.on('change', '.massive-actions input[type=checkbox]', (e) => {
                let check = $(e.currentTarget);
                let value = check.prop('checked');
                let offset = check.closest('th').index();

                container.find('.product-select').filter(':checked').each((index, item) => {
                    $(item).closest('tr').find('td').eq(offset).find('input[type=checkbox]').prop('checked', value);
                });
            });

            container.on('click', '.massive-actions .remove_all', (e) => {
                e.preventDefault();

                container.find('.product-select').filter(':checked').each((index, item) => {
                    let row = $(item).closest('tr');
                    let id = row.find('input[type=hidden][name="id[]"]').val();
                    let remove = $('<input>').attr('type', 'hidden').attr('name', 'remove[]').attr('value', id);
                    row.addClass('table-danger').find('td').eq(1).append(remove);
                });
            });
        }
    }
}

export default Products;
