require('./aggregation');

import utils from "./utils";

class Orders
{
    static init(container)
    {
        $('.dates-for-orders', container).each((index, item) => {
            /*
                Questo è per fare in modo che le date siano sempre coerenti tra
                di loro, e che l'apertura venga sempre prima della chiusura, e
                la chiusura prima della consegna
            */
            $(item).on('change', 'input[name^=first_offset]', (e) => {
                let i = $(e.currentTarget);
                let row = i.closest('tr');
                let peer = row.find('input[name^=second_offset]');
                let action = row.find('select[name^=action]').val();

                if (action == 'ship') {
                    let max = parseInt(i.val()) - 1;
                    peer.attr('max', max);
                    peer.removeAttr('min');

                    if (peer.val() > max) {
                        peer.val(max);
                    }
                }
                else {
                    let min = parseInt(i.val()) + 1;
                    peer.attr('min', min);
                    peer.removeAttr('max');

                    if (peer.val() < min) {
                        peer.val(min);
                    }
                }
            });

            $(item).on('change', 'select[name^=action]', (e) => {
                this.updateLabelsInDates($(e.currentTarget));
            });

            $(item).find('select[name^=action]').change();
        });

        $('#orderAggregator', container).aggregator();
    }

    static initOnce()
    {
        $('body').on('click', '.order-summary .toggle-product-abilitation', function() {
            $('.order-summary tr.product-disabled').toggle();
        })
        .on('change', '.order-summary tr .enabling-toggle', function() {
            var row = $(this).closest('tr');

            if ($(this).prop('checked') == false) {
                var quantity = utils.parseFloatC(row.find('.order-summary-product-price').text());
                if (quantity != 0) {
                    if (confirm(_('Ci sono prenotazioni attive per questo prodotto. Sei sicuro di volerlo disabilitare?')) == false) {
                        $(this).prop('checked', true);
                        return;
                    }
                }
            }

            row.toggleClass('product-disabled');
        })
        .on('change', '.order-document-download-modal input[name=send_mail]', function() {
            var status = $(this).prop('checked');
            var form = $(this).closest('.order-document-download-modal').find('form');
            var submit = utils.j().submitButton(form);

            if (status) {
                submit.text(_('Invia Mail'));
            }
            else {
                submit.text(_('Salva'));
            }

            form.toggleClass('inner-form', status);
        });

        $('body').on('change', '#createOrder select[name^=supplier_id]', function() {
            utils.postAjax({
                url: 'dates/query',
                method: 'GET',
                data: {
                    supplier_id: $(this).val()
                },
                dataType: 'HTML',
                success: function(data) {
                    data = $(data);
                    $('#createOrder .supplier-future-dates').empty().append(data);
                    utils.j().initElements(data);
                }
            });
        });

        $('body').on('click', '.supplier-future-dates li', function() {
            var date = $(this).text();
            $(this).closest('form').find('input[name=shipping]').val(date);
        });
    }

    static updateLabelsInDates(select)
    {
        let action = select.val();

        select.closest('tr').find('input').attrBegins('data-prelabel-').each(function() {
            let i = $(this);
            let prelabel = i.attr('data-prelabel-' + action);
            let postlabel = i.attr('data-postlabel-' + action);
            i.prev('.input-group-text').text(prelabel);
            i.next('.input-group-text').text(postlabel);
            i.change();
        });
    }
}

export default Orders;
