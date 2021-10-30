import utils from "./utils";
import Modifiers from "./modifiers";

class Bookings {
    static init(container)
    {
        $('.bookingSearch', container).each((index, item) => {
            var input = $(item);

            var appendTo = 'body';
            if (input.closest('.modal').length != 0) {
                appendTo = input.closest('.modal');
            }

            input.autocomplete({
                source: utils.absoluteUrl() + '/users/search',
                appendTo: appendTo,
                select: function(event, ui) {
                    var aggregate_id = input.attr('data-aggregate');
                    var while_shipping = (input.closest('.modal.add-booking-while-shipping').length != 0);
                    var fill_target = input.closest('.fillable-booking-space').find('.other-booking');
                    fill_target.empty().append(utils.loadingPlaceholder());

                    var data = {};
                    var mode = input.attr('data-enforce-booking-mode');
                    if (mode != null) {
                        data.enforce = mode;
                    }

                    var url = while_shipping ? ('delivery/' + aggregate_id + '/user/' + ui.item.id) : ('booking/' + aggregate_id + '/user/' + ui.item.id + '?extended=true');

                    utils.postAjax({
                        url: url,
                        method: 'GET',
                        data: data,
                        dataType: 'HTML',
                        success: function(data) {
                            data = $(data);

                            if (while_shipping) {
                                var test = data.find('.booking-product:not(.fit-add-product)');
                                if (test.length != 0) {
                                    data = $('<div class="alert alert-danger">' + _('Questa prenotazione esiste già e non può essere ricreata.') + '</div>');
                                }
                            }

                            fill_target.empty().append(data);
                            utils.j().initElements(data);
                        }
                    });
                }
            });
        });

        utils.sel('.booking-product-quantity input', container).keyup((e) => {
            var editor = $(e.currentTarget).closest('.booking-editor');
            this.bookingTotal(editor);
        })
        .blur((e) => {
            var input = $(e.currentTarget);
            if (input.val() == '' || input.hasClass('is-invalid')) {
                input.val('0').removeClass('is-invalid').keyup();
            }
        })
        .focus((e) => {
            $(e.currentTarget).removeClass('.is-invalid');
        });

        $('.variants-selector select', container).change((e) => {
            var editor = $(e.currentTarget).closest('.booking-editor');
            this.bookingTotal(editor);
        });

        utils.sel('.booking-product .add-variant', container).click((e) => {
            e.preventDefault();
            var variant_selector = $(e.currentTarget).closest('.variants-selector');
            var master = variant_selector.find('.master-variant-selector').clone().removeClass('master-variant-selector');
            master.find('.skip-on-submit').removeClass('skip-on-submit');
            variant_selector.append(master);
            return false;
        });

        $('.mobile-quantity-switch button', container).click((e) => {
            e.preventDefault();

            var button = $(e.currentTarget);
            var input = button.closest('.mobile-quantity-switch').siblings('.booking-product-quantity').find('input.number');

            var original = parseFloat(input.val());
            if (button.hasClass('plus')) {
                input.val(original + 1);
            }
            else {
                input.val(Math.max(0, original - 1));
            }

            input.keyup();
        });

        $('.add-booking-product', container).click(function(e) {
            e.preventDefault();
            var table = $(this).closest('table');
            var row = table.find('.fit-add-product').first().clone().removeClass('hidden');
            utils.j().initElements(row);
            row.appendTo(table.find('tbody'));
            return false;
        });

        $('.fit-add-product-select', container).change((e) => {
            var select = $(e.currentTarget);
            var id = select.find('option:selected').val();
            var row = select.closest('tr');
            var editor = row.closest('.booking-editor');

            if (id == -1) {
                row.find('.bookable-target').empty();
                this.bookingTotal(editor);
            }
            else {
                utils.postAjax({
                    method: 'GET',
                    url: 'products/' + id,
                    data: {
                        format: 'bookable',
                        order_id: editor.attr('data-order-id')
                    },
                    dataType: 'HTML',

                    success: (data) => {
                        data = $(data);
                        utils.j().initElements(data);
                        row.find('.bookable-target').empty().append(data);
                        this.bookingTotal(editor);
                    }
                });
            }
        });

        $('.preload-quantities', container).click((e) => {
            e.preventDefault();
            var editors = $(e.currentTarget).closest('form').find('.booking-editor');

            editors.each((index, item) => {
                this.preloadQuantities($(item));
            });

            /*
                Se mi trovo in un ordine aggregato, eseguo la funzione di controllo
                e calcolo solo sul primo. Tanto comunque bookingTotal() riesegue
                sempre sull'intero form dell'aggregato
            */
            this.bookingTotal(editors.first());

            return false;
        });

        $('input.manual-total', container).change((e) => {
            var editor = $(e.currentTarget).closest('.booking-editor');
            this.bookingTotal(editor);
        });

        $('.load-other-booking', container).click((e) => {
            e.preventDefault();
            var button = $(e.currentTarget);
            var url = button.attr('data-booking-url');
            var fill_target = button.closest('.other-booking');
            utils.j().fetchNode(url, fill_target);
        });

        $('.inline-calculator button[type=submit]', container).click((e) => {
            e.preventDefault();
            var modal = $(e.currentTarget).closest('.modal');
            var quantity = 0;

            modal.find('input.number').each(function() {
                var v = $(this).val();
                if (v != '') {
                    quantity += utils.parseFloatC(v);
                }

                $(this).val('0');
            });

            /*
                Il trigger keyup() alla fine serve a forzare il ricalcolo del totale
                della consegna quando il modale viene chiuso
            */
            var identifier = modal.attr('id');
            $('[data-bs-target="#' + identifier + '"]').closest('.booking-product-quantity').find('input.number').first().val(quantity).keyup();
            modal.modal('hide');
        });

        $('.delete-booking', container).click((e) => {
            e.preventDefault();

            var form = $(e.currentTarget).closest('.inner-form');

            if (confirm(_('Sei sicuro di voler annullare questa prenotazione?'))) {
                form.find('button').prop('disabled', true);

                utils.postAjax({
                    method: 'DELETE',
                    url: form.attr('action'),
                    dataType: 'json',

                    success: (data) => {
                        form.find('button').prop('disabled', false);
                        form.find('.booking-product-quantity input').val('0');
                        form.find('.variants-selector').each(function() {
                            while ($(this).find('.row:not(.master-variant-selector)').length != 1) {
                                $(this).find('.row:not(.master-variant-selector):last').remove();
                            }
                        });

                        this.bookingTotal(form.find('.booking-editor'));
                    }
                });
            }

            return false;
        });

        /*
            Pulsante "Salva Informazioni" in pannello consegna
        */
        $('.booking-form .info-button', container).click((e) => {
            e.preventDefault();
            var form = $(e.currentTarget).closest('form');
            form.find('input:hidden[name=action]').val('saved');
            form.submit();
        });

        $('.booking-form .saving-button', container).click((e) => {
            var button = $(e.currentTarget);
            if (button.closest('.booking-form').find('input:hidden[name=action]').val() == 'shipped') {
                if (typeof button.data('total-checked') === 'undefined') {
                    e.stopPropagation();
                    var test = false;

                    button.closest('form').find('.booking-total').each(function() {
                        var total = utils.parseFloatC($(this).textVal());
                        test = (test || (total != 0));
                    });

                    if (test == false) {
                        test = confirm(_('Tutte le quantità consegnate sono a zero! Vuoi davvero procedere?'));
                    }

                    if (test == true) {
                        button.data('total-checked', 1);
                        button.click();
                    }
                }
            }
        });
    }

    static preloadQuantities(container)
    {
        container.find('.booking-product').each(function() {
            var booked = $(this).find('input:hidden[name=booking-product-real-booked]');
            if (booked.length != 0) {
                $(this).find('.booking-product-quantity input').val(booked.val());
            }
        });

        this.bookingTotal(container.closest('.booking-editor'));
    }

    static serializeBooking(form)
    {
        /*
            Qui aggiungo temporaneamente la classe skip-on-submit a tutti gli input
            a 0, in modo da ridurre la quantità di dati spediti al server per il
            controllo dinamico, salvo poi toglierla a operazione conclusa
        */
        form.find('textarea').addClass('skip-on-submit restore-after-serialize');

        form.find('.booking-product-quantity input').filter(function() {
            return $(this).closest('.master-variant-selector').length == 0;
        }).each(function() {
            $(this).toggleClass('skip-on-submit restore-after-serialize', $(this).val() == '0');
        });

    	var data = form.find(':not(.skip-on-submit)').serialize();
        form.find('.restore-after-serialize').removeClass('skip-on-submit restore-after-serialize');
        return data;
    }

    static updateBookingQuantities(dynamic_data, container, action)
    {
        for (let [product_id, product_meta] of Object.entries(dynamic_data)) {
            var inputbox = $('input[name="' + product_id + '"]', container);
            inputbox.closest('tr').find('.booking-product-price span').text(utils.priceRound(product_meta.total));

            var modifiers = '';
            for (let [modifier_id, modifier_meta] of Object.entries(product_meta.modifiers)) {
                modifiers += '<br>' + modifier_meta.label + ': ' + utils.priceRound(modifier_meta.amount) + current_currency;
            }

            inputbox.closest('tr').find('.modifiers').html(modifiers);

            if (product_meta.variants.length != 0) {
                /*
                    Attenzione: qui mi baso sul fatto che le
                    varianti rappresentate nel feedback server-side
                    siano ordinate nello stesso modo rispetto al
                    pannello. Potrei usare i components come
                    riferimento, ma possono esserci più varianti con
                    gli stessi componenti e dovrei intuire qual è
                    quella da eventualmente invalidare
                */
                for (let i = 0; i < product_meta.variants.length; i++) {
                    var variant = product_meta.variants[i];
                    var varinputbox = $('input[name="variant_quantity_' + product_id + '[]"]', container).filter(':not(.skip-on-submit)').eq(i);
                    utils.inputInvalidFeedback(varinputbox, variant.quantity == 0 && utils.parseFloatC(varinputbox.val()) != 0, variant.message);

                    if (action == 'shipped') {
                        varinputbox.closest('tr').find('.booking-product-price span').text(utils.priceRound(variant.total));
                    }
                }
            }
            else {
                utils.inputInvalidFeedback(inputbox, product_meta.quantity == 0 && utils.parseFloatC(inputbox.val()) != 0, product_meta.message);
            }
        }
    }

    static testMaxBookable(form, grand_total)
    {
        /*
            Se è attivo il limite di prenotazioni sul credito, controllo
            che non sia stato raggiunto e nel caso disabilito il
            pulsante di invio
        */
        var max_bookable = form.find('input:hidden[name="max-bookable"]');
        if (max_bookable.length != 0) {
            max_bookable = parseFloat(max_bookable.val());
            form.find('button[type=submit]').prop('disabled', grand_total > max_bookable);
        }
    }

    static updatePayment(form, grand_total, status)
    {
        /*
            Qui aggiorno il valore totale della prenotazione nel (eventuale)
            modale per il pagamento
        */
        var payment_modal_id = form.attr('data-reference-modal');
        var payment_modal = $('#' + payment_modal_id);

        if (payment_modal.length != 0) {
            payment_modal.find('input[name=amount]').val(grand_total.toFixed(2)).change();
            payment_modal.find('input[name=delivering-status]').val(JSON.stringify(status));
        }
    }

    static verifyManualTotal(container, data)
    {
        /*
            Se tutte le quantità nella prenotazione risultano a 0, ma è stato
            definito un valore manuale per la consegna, carico comunque tutte le
            quantità prenotate come consegnate. In questo modo salvo almeno
            qualche quantità per le consegne, che non saranno quella reali ma
            quantomeno una approssimazione
        */
        if (data.products.length == 0) {
            var manual = $('input.manual-total', container);
            if (manual.length != 0 && manual.val() != 0) {
                this.preloadQuantities(container);
            }
        }
    }

    static bookingTotal(editor) {
    	var form = $(editor).closest('form');
        var data = this.serializeBooking(form);
    	var url = form.attr('data-dynamic-url');

    	$.ajax({
    		url: url,
    		method: 'GET',
    		data: data,
    		dataType: 'JSON',
    		success: (data) => {
    			if (Object.entries(data.bookings).length == 0) {
    				$('.booking-product-price span', form).text(utils.priceRound(0));
    				$('.booking-modifier, .booking-total', container).textVal(utils.priceRound(0));
    			}
    			else {
                    var action = $('input:hidden[name=action]', form).val();
    				var grand_total = 0;

    				/*
    					Questa variabile contiene i totali di ogni prenotazione
    					coinvolta nel pannello, ed in fase di consegna viene spedita
    					al server sotto il nome di 'delivering-status'.
    					Viene usata in MovementType per gestire i movimenti
    					contabili
    				*/
    				var status = {};

    				for (let [booking_id, booking_data] of Object.entries(data.bookings)) {
    					var container = $('input[value="' + booking_id + '"]').closest('table');
    					$('.booking-product-price span', container).text(utils.priceRound(0));

                        this.updateBookingQuantities(booking_data.products, container, action);
                        Modifiers.updateBookingModifiers(booking_data.modifiers, container);

    					var t = utils.priceRound(booking_data.total);
    					$('.booking-total', container).textVal(t);
    					grand_total += parseFloat(t);
    					status[booking_id] = booking_data.total;

                        this.verifyManualTotal(container, booking_data);
    				}

    				form.find('.all-bookings-total').text(utils.priceRound(grand_total));
                    this.testMaxBookable(form, grand_total);
                    this.updatePayment(form, grand_total, status);
    			}
    		}
    	});
    }
}

export default Bookings;
