require('./aggregation');

class Orders
{
    static init(container)
    {
        $('.dates-for-orders', container).each((index, item) => {
            $(item).on('change', 'tr td select[name^=action]', (e) => {
                this.updateLabelsInDates($(e.currentTarget));
            });

            $(item).find('tr td select[name^=action]').change();
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
            let prelabel = $(this).attr('data-prelabel-' + action);
            let postlabel = $(this).attr('data-postlabel-' + action);
            $(this).prev('.input-group-text').text(prelabel);
            $(this).next('.input-group-text').text(postlabel);
        });
    }
}

export default Orders;
