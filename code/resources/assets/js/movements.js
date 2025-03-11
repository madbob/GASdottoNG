import utils from "./utils";

class Movements {
    static init(container)
    {
        $('.csv_movement_type_select', container).each((index, item) => {
            this.enforcePaymentMethod($(item));
        }).change((e) => {
            this.enforcePaymentMethod($(e.currentTarget));
        });

        if (container.hasClass('movement-modal')) {
            this.initModals(container);
        }
        else {
            container.find('.movement-modal').each((i, e) => {
                this.initModals(e);
            });
        }

        $('.movement-type-selector', container).change((e) => {
            var selector = $(e.currentTarget);
            var type = selector.find('option:selected').val();
            var selectors = selector.closest('form').find('.selectors');
            utils.fetchNode('movements/create?type=' + type, selectors);
        });

        if (container.hasClass('movement-type-editor')) {
            this.movementTypeEditor(container);
        }
    }

    static initModals(container)
    {
        $('input[name=amount]', container).change(function() {
            var status = $(this).closest('.movement-modal').find('.sender-credit-status');
            if (status.length) {
                var amount = utils.parseFloatC($(this).val());
                var current = utils.parseFloatC(status.find('.current-sender-credit').text());

                if (amount > current) {
                    status.removeClass('alert-success').addClass('alert-danger');
                }
                else {
                    status.removeClass('alert-danger').addClass('alert-success');
                }
            }
        });
    }

    static movementTypeEditor(container)
    {
        $('select[name=sender_type], select[name=target_type]', container).change(function() {
            var editor = $(this).closest('.movement-type-editor');
            var sender = editor.find('select[name=sender_type] option:selected').val();
            var target = editor.find('select[name=target_type] option:selected').val();
            var table = editor.find('table');
            var manages_gas = (sender == 'App\\Gas' || target == 'App\\Gas');

            table.find('tbody tr').each(function() {
                var type = $(this).attr('data-target-class');
                /*
                    Le righe relative al GAS non vengono mai nascoste, in quanto
                    molti tipi di movimento vanno ad incidere sui saldi globali
                    anche quando il GAS non è direttamente coinvolto
                */
                if (type == 'master-App\\Gas') {
                    $(this).prop('hidden', manages_gas);
                }
                else {
                    $(this).prop('hidden', type != 'sender-' + sender && type != 'target-' + target);
                }
            });

            table.find('thead input[data-active-for]').each(function() {
                var type = $(this).attr('data-active-for');
                if(type != '' && type != sender && type != target) {
                    $(this).prop('checked', false).prop('disabled', true).change();
                }
                else {
                    $(this).prop('disabled', false);
                }
            });
        });

        $('table thead input:checkbox', container).change(function() {
            var active = $(this).prop('checked');
            var index = $(this).closest('th').index();

            if (active == false) {
                $(this).closest('table').find('tbody tr').each(function() {
                    var cell = $(this).find('td:nth-child(' + (index + 1) + ')');
                    cell.find('input[value=ignore]').click();
                    cell.find('label, input').prop('disabled', true);
                });
            }
            else {
                $(this).closest('table').find('tbody tr').each(function() {
                    $(this).find('td:nth-child(' + (index + 1) + ')').find('label, input').prop('disabled', false);
                });
            }
        });
    }

    /*
        Questa è per forzare i metodi di pagamento disponibili nel modale di
        importazione dei movimenti contabili
    */
    static enforcePaymentMethod(node)
    {
        var selected = node.find('option:selected').val();
        var default_payment = null;
        var payments = null;

        JSON.parse(node.closest('.modal').find('input[name=matching_methods_for_movement_types]').val()).forEach(function(iter) {
            if (iter.method == selected) {
                default_payment = iter.default_payment;
                payments = iter.payments;
                return false;
            }
        });

        if (payments != null) {
            node.closest('tr').find('.csv_movement_method_select').find('option').each(function() {
                var v = $(this).val();
                if (payments.indexOf(v) >= 0) {
                    $(this).prop('disabled', false);
                    $(this).prop('selected', default_payment == v);
                }
                else {
                    $(this).prop('disabled', true);
                }
            });
        }
        else {
            node.closest('tr').find('.csv_movement_method_select').find('option').prop('disabled', false);
        }
    }
}

export default Movements;
