import utils from "./utils";
import Modifiers from "./modifiers";

class Bookings
{
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
                    fill_target.empty().append(utils.j().makeSpinner());

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

        container.on('change', '.variants-selector select', (e) => {
            /*
                Cambiando una variante, se la quantità prenotata risulta già non
                essere a 0 eseguo - come in tutti gli altri casi - il ricalcolo
                nel pannello con bookingTotal(), altrimenti vado a pescare il
                prezzo di quella singola variante modificata ed aggiorno la
                visualizzazione
            */

            var row = $(e.currentTarget).closest('.inline-variant-selector');
            var editor = row.closest('.booking-editor');
            var quantity = utils.parseFloatC(row.find('.booking-product-quantity input').val());

            if (quantity == 0) {
                var variant = [];

                row.find('.form-select').each(function() {
                    variant.push($(this).find(':selected').val());
                });

                utils.postAjax({
                    method: 'GET',
                    url: 'products/price',
                    dataType: 'JSON',
                    data: {
                        id: row.closest('tr').find('input:hidden').first().attr('name'),
                        order_id: editor.attr('data-order-id'),
                        variant: variant,
                    },
                    success: function(data) {
                        var index = row.index();
                        var block = row.closest('tr').find('.prices_block');
                        var newrow = block.find('.row').first().clone();
                        newrow.find('small').text(data.price);

                        if (block.find('.row').length > index) {
                            block.find('.row').eq(index).replaceWith(newrow);
                        }
                        else {
                            block.append(newrow);
                        }
                    }
                })
            }
            else {
                this.bookingTotal(editor);
            }
        });

        $('.add-booking-product', container).click(function(e) {
            e.preventDefault();
            var table = $(this).closest('table');
            var row = table.find('.fit-add-product').first().clone().removeClass('hidden');
            utils.j().initElements(row);
            row.appendTo(table.find('tbody'));
            return false;
        });

        $('.alt_price_selector input', container).change((e) => {
            var radio = $(e.currentTarget);
            if (radio.prop('checked')) {
                var editor = radio.closest('.booking-editor');
                this.bookingTotal(editor);
            }
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
                this.preloadQuantities($(item), false);
            });

            /*
                Se mi trovo in un ordine aggregato, eseguo la funzione di controllo
                e calcolo solo sul primo. Tanto comunque bookingTotal() riesegue
                sempre sull'intero form dell'aggregato
            */
            this.bookingTotal(editors.first());

            return false;
        });

        $('input.manual-total', container).keyup((e) => {
            let i = $(e.currentTarget);
            if (parseFloat(i.val()) > 0) {
                i.addClass('is-changed');
            }
            else {
                i.removeClass('is-changed');
            }
        }).change((e) => {
            let editor = $(e.currentTarget).closest('.booking-editor');
            this.bookingTotal(editor);
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
                Il trigger blur() alla fine serve a forzare il ricalcolo del
                totale della consegna quando il modale viene chiuso
            */
            var identifier = modal.attr('id');
            $('[data-bs-target="#' + identifier + '"]').closest('.booking-product-quantity').find('input.number').first().val(quantity.toFixed(3)).blur();
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
    }

    static initOnce()
    {
        /*
            Questi eventi li aggancio sempre, direttamente al body, altrimenti
            sarebbe un po' complicato...

            Devo considerare:
            - le righe che vengono dinamicamente aggiunte quanto aggiungo una
            variante, che devono essere reattive come le altre
            - il fatto che in alcune circostanze (e.g. aggiunta di un prodotto
            ad una prenotazione in fase di consegna) i selettori qui di seguito
            non sono validi se riferiti al container, dunque gli eventi non sono
            agganciati
        */
        $('body').on('blur', '.booking-product-quantity input', (e) => {
            var editor = $(e.currentTarget).closest('.booking-editor');
            this.bookingTotal(editor);
        })
        .on('focus', '.booking-product-quantity input', (e) => {
            $(e.currentTarget).removeClass('is-invalid');
        })
        .on('click', '.booking-product .add-variant', (e) => {
            e.preventDefault();
            var variant_selector = $(e.currentTarget).closest('.variants-selector');
            var template = variant_selector.find('.master-variant-selector');
            var master = template.clone().removeClass('master-variant-selector');
            master.find('.skip-on-submit').removeClass('skip-on-submit');
            template.before(master);

            /*
                Questo è per forzare il caricamento del prezzo della nuova
                variante introdotta
            */
            master.find('select').first().change();

            return false;
        });
    }

    static preloadQuantities(container, reload)
    {
        container.find('.booking-product').each(function() {
            var booked = $(this).find('input:hidden[name=booking-product-real-booked]');
            if (booked.length != 0) {
                $(this).find('.booking-product-quantity input').val(booked.val());
            }
        });

        if (reload) {
            this.bookingTotal(container.closest('.booking-editor'));
        }
    }

    static serializeBooking(form)
    {
        /*
            Qui aggiungo temporaneamente la classe skip-on-submit a tutti gli
            input a 0, in modo da ridurre la quantità di dati spediti al server
            per il controllo dinamico, salvo poi toglierla a operazione
            conclusa.
            Da tale procedura escludo però le righe con le quantità delle
            varianti, altrimenti perdo l'allineamento rispetto all'array coi
            valori delle varianti selezionate (e se ad esempio ci sono più
            varianti attive e la prima viene messa a quantità 0, non mi tornano
            più le associazioni con le altre quantità e le varianti selezionate)
        */
        form.find('textarea').addClass('skip-on-submit restore-after-serialize');

        form.find('.booking-product-quantity input').filter(function() {
            return $(this).closest('.master-variant-selector').length == 0;
        }).filter(function() {
            return $(this).attr('name').startsWith('variant_quantity_') == false;
        }).each(function() {
            $(this).toggleClass('skip-on-submit restore-after-serialize', $(this).val() == '0');
        });

        let manual = form.find('.manual-total');
        if (manual.length) {
            /*
                Si deve tenere traccia dello stato della input box per le
                consegne manuali senza quantità: se non ne viene modificato il
                contenuto funziona come visualizzazione del totale manualmente
                calcolato, altrimenti come effettivo totale manuale (e bloccato,
                che sovrascrive quello automatico).
                Se non è stato manualmente valorizzato (ma c'è comunque un
                valore, settato da un precedente calcolo automatico, ad esempio
                se è stata immessa la quantità di un prodotto) qui lo si svuota,
                altrimenti il valore accompagnerebbe la richiesta verso il
                server e sarebbe interpretato come un totale manuale (e, dunque,
                bloccato)
            */
            if (manual.hasClass('is-changed') == false) {
                manual.val('');
            }
        }

    	var data = form.find(':not(.skip-on-submit)').serialize();
        form.find('.restore-after-serialize').removeClass('skip-on-submit restore-after-serialize');
        return data;
    }

    static checkInvalidFeedback(input, condition, message)
    {
        if (condition) {
            input.toggleClass('is-invalid', true);
            input.toggleClass('is-annotated', false);
            input.val('0');
        }
        else {
            input.toggleClass('is-invalid', false);
            input.toggleClass('is-annotated', message != '');
        }

        input.siblings('.invalid-feedback').text(message);
    }

    static priceRow(value)
    {
        return '<div class="row"><div class="col"><label class="static-label form-control-plaintext"><small>' + value + '</small></label></div></div>';
    }

    static updateBookingQuantities(dynamic_data, container, action)
    {
        for (let [product_id, product_meta] of Object.entries(dynamic_data)) {
            var inputbox = $('input[name="' + product_id + '"]', container);
            inputbox.closest('tr').find('.booking-product-price span').text(utils.priceRound(product_meta.total));

            if (product_meta.variants.length != 0) {
                /*
                    Attenzione: qui mi baso sul fatto che le varianti
                    rappresentate nel feedback server-side siano ordinate nello
                    stesso modo rispetto al pannello. Potrei usare i components
                    come riferimento, ma possono esserci più varianti con gli
                    stessi componenti e dovrei intuire qual è quella da
                    eventualmente invalidare
                */

                /*
                    Il pannello delle consegne è organizzato in modo un po'
                    diverso rispetto a quello delle prenotazioni, soprattutto
                    per quanto riguarda le varianti
                */

                if (action == 'shipped') {
                    for (let i = 0; i < product_meta.variants.length; i++) {
                        let variant = product_meta.variants[i];

                        let varinputbox = $('input[name="variant_quantity_' + product_id + '[]"]', container).eq(i);
                        if (varinputbox.length == 0) {
                            break;
                        }

                        varinputbox.val(variant.quantity);
                        varinputbox.closest('tr').find('.booking-product-price span').text(variant.total.toFixed(2));
                    }

                    let total_rows = $('input[name="variant_quantity_' + product_id + '[]"]', container);
                    if (total_rows.length > product_meta.variants.length) {
                        for (let i = product_meta.variants.length; i < total_rows.length; i++) {
                            let varinputbox = total_rows.eq(i);
                            varinputbox.val(0);
                            varinputbox.closest('tr').find('.booking-product-price span').text('0.00');
                        }
                    }
                }
                else {
                    let pricesbox = [];
                    let populated_index = 0;

                    for (let i = 0; i < product_meta.variants.length; i++) {
                        var variant = product_meta.variants[i];
                        var varinputbox = null;
                        var varinputboxvalue = 0;

                        do {
                            varinputbox = $('input[name="variant_quantity_' + product_id + '[]"]', container).filter(':not(.skip-on-submit)').eq(populated_index);
                            if (varinputbox.length == 0) {
                                break;
                            }

                            populated_index++;
                            varinputboxvalue = utils.parseFloatC(varinputbox.val());

                            if (varinputboxvalue == 0) {
                                pricesbox.push(this.priceRow('&nbsp;'));
                            }
                        } while(varinputboxvalue == 0);

                        this.checkInvalidFeedback(varinputbox, variant.quantity == 0 && varinputboxvalue != 0, variant.message);
                        pricesbox.push(this.priceRow(variant.unitprice_human));
                    }

                    inputbox.closest('tr').find('.prices_block').empty().append(pricesbox);
                }
            }
            else {
                this.checkInvalidFeedback(inputbox, product_meta.quantity == 0 && utils.parseFloatC(inputbox.val()) != 0, product_meta.message);
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
            utils.j().submitButton(form).each(function() {
                $(this).prop('disabled', grand_total > max_bookable);
            });
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
                this.preloadQuantities(container, true);
            }
        }
    }

    static bookingTotal(editor) {
    	var form = $(editor).closest('form');
        var data = this.serializeBooking(form);
    	var url = form.attr('data-dynamic-url');

        if (this.dynamicBookingRequest) {
            this.dynamicBookingRequest.abort();
        }

    	this.dynamicBookingRequest = $.ajax({
    		url: url,
    		method: 'GET',
    		data: data,
    		dataType: 'JSON',
    		success: (data) => {
                if (data.hasOwnProperty('status') && data.status == 'error') {
                    utils.displayServerError(null, data);
                    return;
                }

    			if (Object.entries(data.bookings).length == 0) {
    				$('.booking-product-price span', form).text(utils.priceRound(0));
    				$('.booking-modifier, .booking-total', container).textVal(utils.priceRound(0));
                    $('.all-bookings-total', form).text(utils.priceRound(0));
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

                    $('.booking-bottom-helper', container).removeClass('bg-success').addClass('bg-danger');

    				for (let [booking_id, booking_data] of Object.entries(data.bookings)) {
    					var container = $('input[value="' + booking_id + '"]').closest('table').first();
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

                this.dynamicBookingRequest = null;
    		},
            error: function(data) {
                utils.displayServerError(null, data.responseJSON);
            }
    	});
    }
}

export default Bookings;
