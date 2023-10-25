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
            container.on('active-filter', (e) => {
                container.find('.category-toggle').removeClass('hidden');
            });

            container.on('inactive-filter', (e) => {
                container.find('.category-toggle').addClass('hidden');
            });
        }
    }
}

export default Products;
