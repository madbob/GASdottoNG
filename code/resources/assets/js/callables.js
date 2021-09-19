window.$ = window.jQuery = global.$ = global.jQuery = require('jquery');
require('bootstrap');

import utils from "./utils";
import lists from "./lists";

class Callables {
    static attachUserRole(role_id, user_id, target_id, target_class) {
        utils.postAjax({
            method: 'POST',
            url: 'roles/attach',
            data: {
                role: role_id,
                user: user_id,
                target_id: target_id,
                target_class: target_class
            },
        });
    }

    static detachUserRole(role_id, user_id, target_id, target_class) {
        utils.postAjax({
            method: 'POST',
            url: 'roles/detach',
            data: {
                role: role_id,
                user: user_id,
                target_id: target_id,
                target_class: target_class
            },
        });
    }

    static supplierAttachUser(list, user_id) {
        var supplier_id = list.attr('data-supplier-id');
        var role_id = list.attr('data-role-id');
        Callables.attachUserRole(role_id, user_id, supplier_id, 'App\\Supplier');
    }

    static supplierDetachUser(list, user_id) {
        var supplier_id = list.attr('data-supplier-id');
        var role_id = list.attr('data-role-id');
        Callables.detachUserRole(role_id, user_id, supplier_id, 'App\\Supplier');
    }

    /* data-sorting-function **************************************************/

    static getBookingRowStatus(row) {
        if (row.find('.bi-check').length) {
            return 'shipped';
        }

        if (row.find('.bi-save').length) {
            return 'saved';
        }

        return 'pending';
    }

    static sortShippingBookings(list) {
        list.find('> .accordion-item').sort(function(a, b) {
            a = $(a);
            b = $(b);

            var a_status = Callables.getBookingRowStatus(a);
            var b_status = Callables.getBookingRowStatus(b);
            var ret = 0;

            if (a_status == b_status) {
                ret = a.find('.accordion-button').text().trim().localeCompare(b.find('.accordion-button').text().trim());
            }
            else {
                if (a_status == 'pending') {
                    return -1;
                }
                else if (b_status == 'pending') {
                    return 1;
                }
                else if (a_status == 'saved') {
                    return -1;
                }
                else if (b_status == 'saved') {
                    return 1;
                }
            }

            return ret;
        }).each(function() {
            $(this).appendTo(list);
        });
    }

    /* pre-saved-function *****************************************************/

    static submitDeliveryForm(form) {
        /*
            Questo è per condensare eventuali nuovi prodotti aggiunti ma già
            presenti nella prenotazione.
        */
        form.find('.fit-add-product').not('.hidden').each(function() {
            var i = $(this).find('.booking-product-quantity input:text.number');
            if (i.length == 0) {
                return;
            }

            var product = utils.sanitizeId(i.attr('name'));
            var added_value = utils.parseFloatC(i.val());
            var existing = form.find('tr.booking-product').not('.fit-add-product').find('input:text.number[name=' + product + ']');
            if (existing.length != 0) {
                existing.val(utils.parseFloatC(existing.val()) + added_value);
                i.remove();
            }
        });
    }

    static collectFilteredUsers(form) {
        $('#credits_status_table tbody tr:visible').each(function() {
            var user_id = $(this).find('input[name^=user_id]').val();
            form.append('<input type="hidden" name="users[]" value="' + user_id + '">');
        });
    }

    static formToDownload(form) {
        var data = form.find('input, select').serializeArray();
        var url = form.attr('action') + '&' + $.param(data);
        window.open(url, '_blank');
        throw "Done!";
    }

    static passwordProtected(form)
    {
        if (form.attr('data-password-protected-verified') != '1') {
            var id = form.attr('id');
            var modal = $('#password-protection-dialog');
            modal.find('input:password').val('');
            modal.attr('data-form-target', '#' + id).modal('show');
            throw 'Check password!';
        }
    }

    /* post-saved-function ****************************************************/

    static triggerPayment(form)
    {
    	/*
    		Il valore di "action" viene impostato di default nel form di consegna a
    		"shipped", ma può essere alterato dal pulsante di salvataggio delle
    		informazioni. In tal caso, non occorre visualizzare il modale di
    		pagamento.
    	*/
    	var action = form.find('input:hidden[name=action]').val();
    	if (action == 'shipped') {
    		var payment_modal = form.attr('data-reference-modal');
    		$('#' + payment_modal).appendTo('body').modal('show');
    	}
    }

    static displayRecalculatedBalances(form, data) {
        var modal = $('#display-recalculated-balance-modal');

        if (data.diffs.length != 0) {
            modal.find('a.table_to_csv').removeClass('hidden');
            var table = modal.find('.broken.hidden').removeClass('hidden').find('tbody');
            for (var name in data.diffs) {
                if (data.diffs.hasOwnProperty(name)) {
                    table.append('<tr><td>' + name + '</td><td>' + data.diffs[name][0] + '</td><td>' + data.diffs[name][1] + '</td></tr>');
                }
            }
        }
        else {
            modal.find('.fixed.hidden').removeClass('hidden');
        }

        modal.modal('show');
    }

    static refreshFilter(form) {
        var target = form.find('input:hidden[name=data-refresh-target]').val();
        if (target) {
            $('.form-filler').filter(target).find('button[type=submit]').click();
        }
        else {
            $('.form-filler').find('button[type=submit]').click();
        }
    }

    static refreshBalanceView() {
        utils.postAjax({
            method: 'GET',
            url: 'movements/balance',
            dataType: 'JSON',
            success: function(data) {
                $('.current-balance').each(function() {
                    for (var property in data) {
                        if (data.hasOwnProperty(property)) {
                            $(this).find('.' + property + ' span').text(data[property]);
                        }
                    }
                });
            }
        });
    }

    static afterAggregateChange(form, data) {
        utils.postAjax({
            method: 'GET',
            url: 'aggregates/' + data.id + '/post_feedback',
            dataType: 'JSON',
            success: function(data) {
                for (let i = 0; i < data.length; i++) {
                    utils.j().fetchRemoteModal(data[i]);
                }
            }
        });
    }

    static afterBookingSaved(form, data) {
        var modal = form.closest('.modal');

        /*
            In questo caso, ho aggiunto una prenotazione dal modale di "Aggiungi
            Utente" in fase di consegna
        */
        if (modal.length != 0) {
            /*
                Se è stato salvata una nuova prenotazione vuota, il backend
                restituisce una risposta vuota e non c'è nessuna nuova prenotazione
                da aggiungere all'elenco
            */
            if (data.hasOwnProperty('id')) {
                var list = $("button[data-target='#" + modal.attr('id') + "']").parent().find('.loadable-list');
                if (list.find('> a[data-element-id=' + data.id + ']').length == 0) {
                    data.url = data.url.replace('booking/', 'delivery/');
                    lists.appendToLoadableList(list, data, false);
                }
            }
        }
        /*
            In questo caso, ho aggiunto la prenotazione dal pannello "Prenotazioni"
        */
        else {
            lists.closeParent(form);
        }
    }

    /*
        Usato al salvataggio di un movimento contabile, per ricaricare gli elementi
        ad esso correlati (pagante e pagato) nelle eventuali liste attualmente in
        pagina.
        Usato primariamente per aggiornare la grafica delle consegne dopo il pagamento
    */
    static reloadLoadableHeaders(form, data)
    {
        var n = $('.accordion-item[data-element-id="' + form.find('input[name=sender_id]').val() + '"]');
        if (n.length != 0) {
            lists.reloadLoadableHead(n);
        }

        var n = $('.accordion-item[data-element-id="' + form.find('input[name=target_id]').val() + '"]');
        if (n.length != 0) {
            lists.reloadLoadableHead(n);
        }
    }

    static closeMainForm(form, data)
    {
        lists.closeParent(form);
    }
}

export default Callables;
