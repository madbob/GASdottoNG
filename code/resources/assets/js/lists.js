window.$ = window.jQuery = global.$ = global.jQuery = require('jquery');
require('bootstrap');

import utils from "./utils";
import callables from "./callables";

class Lists {
    static init(container)
    {
        $('.loadable-list', container).each(function() {
            Lists.testListsEmptiness($(this));
        });

        container.on('hide.bs.collapse', '.loadable-list > .accordion-item > .accordion-collapse', function(event) {
            event.stopPropagation();
            let head = $(this).closest('.accordion-item');
            Lists.reloadLoadableHead(head);
        });

        container.on('show.bs.collapse', '.loadable-list > .accordion-item > .accordion-collapse', function(event) {
            event.stopPropagation();
            $(this).find('.accordion-body').animate({
                'min-height': '150px'
            }, 600);
        });

        /*
            Questa animazione viene effettuata solo dopo la visualizzazione del
            contenuto dell'accordion, altrimenti l'offset superiore viene
            calcolato tenendo in considerazione anche l'accordion
            precedentemente aperto (che comunque viene chiuso contestualmete
            all'apertura di questo) ed il posizionamento viene alterato
        */
        container.on('shown.bs.collapse', '.loadable-list > .accordion-item > .accordion-collapse', function(event) {
            event.stopPropagation();
            var node = $(this);
            $('html, body').animate({
                scrollTop: node.closest('.accordion-item').offset().top
            }, 300);
        });
    }

    static listRow(list, id, url, header) {
        var domid = Math.random().toString(36).substring(2);

        return $('<div class="async-accordion accordion-item" data-accordion-url="' + url + '" data-element-id="' + id + '"> \
            <h2 class="accordion-header" id="head-accordionitem-' + domid +'"> \
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#accordionitem-' + domid +'" aria-expanded="false" aria-controls="accordionitem-4P3rexIa1E">' + header + '</button> \
            </h2> \
            <div id="accordionitem-' + domid +'" class="accordion-collapse collapse" aria-labelledby="head-accordionitem-' + domid +'" data-bs-parent="#' + list.attr('id') + '" "=""> \
                <div class="accordion-body"></div> \
            </div> \
        </div>');
    }

    static appendToLoadableList(list, data, open) {
        var node = Lists.listRow(list, data.id, data.url, data.header);
        list.append(node);
        utils.j().handleAsyncAccordion(node);
        Lists.afterListChanges(list);

        if (open) {
            node.find('.accordion-button').click();
        }
    }

    static reloadLoadableHead(item)
    {
        utils.postAjax({
            method: 'GET',
            url: item.attr('data-accordion-url') + '/header',
            dataType: 'json',
            success: function(data) {
                item.find('> .accordion-header > .accordion-button').empty().append(data.header);
                item.attr('data-accordion-url', data.url);
                Lists.afterListChanges(item.closest('.loadable-list'));
            }
        });
    }

    static closeParent(node) {
        var container = node.closest('.accordion-item');
        container.find('> .accordion-header > .accordion-button').click();
        return container;
    }

    static afterListChanges(list) {
        var sorting = list.attr('data-sorting-function');
        if (sorting != null) {
            callables[sorting](list);
        }

        Lists.testListsEmptiness(list);
    }

    static testListsEmptiness(list) {
        var id = list.attr('id');
        var c = list.find('> .accordion-item').length;
        $('#empty-' + id).toggleClass('d-none', (c != 0));
    }
}

export default Lists;
