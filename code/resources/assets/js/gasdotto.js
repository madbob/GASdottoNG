/*******************************************************************************
	Varie ed eventuali
*/

window.$ = window.jQuery = global.$ = global.jQuery = require('jquery');
require('bootstrap');

require('jquery-ui/ui/widgets/draggable');
require('jquery-ui/ui/widgets/droppable');
require('jquery-ui/ui/widgets/autocomplete');
require('jquery-ui-touch-punch');
require('bootstrap-datepicker');
require('select2');
require('blueimp-file-upload');
import Cookies from 'js-cookie';

require('./aggregation');
require('./cc');
require('./jquery.dynamictree');
require('./jquery.TableCSVExport');
require('./statistics');
require('./translate');
require('./password');
import utils from "./utils";
import filters from "./filters";
import Lists from "./lists";
import Modifiers from "./modifiers";
import Callables from "./callables";

var locker = false;
var absolute_url = $('meta[name=absolute_url]').attr('content');
var current_currency = $('meta[name=current_currency]').attr('content');
var current_language = $('html').attr('lang').split('-')[0];
var measure_discrete = null;

const localCallables = {
    /*
        Questa funzione viene lasciata qui, anziché essere spostata in
        callables.js, perché va a modificare la variabile globale
        measure_discrete
    */
    reloadMeasureDiscrete: function(form, data) {
        utils.postAjax({
            method: 'GET',
            url: 'measures/discretes',
            dataType: 'JSON',
            success: function(data) {
                measure_discrete = data;
            }
        });
    }
};

$.fn.tagName = function() {
    return this.prop("tagName").toLowerCase();
};

function generalInit(container) {
    if (container == null) {
        container = $('body');
    }

    $('input.date', container).datepicker({
        format: 'DD dd MM yyyy',
        autoclose: true,
        language: current_language,
        clearBtn: true,
    }).each(function() {
        var input = $(this);
        input.siblings('.input-group-addon').click(function() {
            input.focus();
        });
    }).on('show', function(e) {
        /*
            Senza questo, l'evento risale e - non ho ben capito come -
            interferisce con le accordion e i modal
        */
        e.stopPropagation();
    });

    $('input.date-to-month', container).datepicker({
        format: 'dd MM',
        autoclose: true,
        language: current_language,
        clearBtn: false,
        maxViewMode: 'months'
    });

    $('select[multiple]', container).select2({
        theme: "bootstrap-5",
        dropdownParent: container,
    });

    /*
        https://stackoverflow.com/questions/15989591/how-can-i-keep-bootstrap-popover-alive-while-the-popover-is-being-hovered
    */
    $('[data-bs-toggle="popover"]', container).popover({
        trigger: "manual",
        html: true,
        animation:false
    })
    .on("mouseenter", function () {
        var _this = this;
        $(this).popover("show");
        $(".popover").on("mouseleave", function () {
            $(_this).popover('hide');
        });
    }).on("mouseleave", function () {
        var _this = this;
        setTimeout(function () {
            if (!$(".popover:hover").length) {
                $(_this).popover("hide");
            }
        }, 300);
    });

    if (container.closest('.contacts-selection').length != 0) {
        var input = container.find('input[name="contact_value[]"]');
        var typeclass = container.find('select option:selected').val();
        fixContactField(input, typeclass);
    }
    else {
        $('.contacts-selection tr', container).each(function() {
            var input = $(this).find('input[name="contact_value[]"]');
            var typeclass = $(this).find('select option:selected').val();
            fixContactField(input, typeclass);
        });
    }

    $('.nav-tabs a', container).click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $('input:file.immediate-run', container).each(function() {
        var i = $(this);
        i.fileupload({
            done: function(e, data) {
                wizardLoadPage($(e.target), data.result);
            }
        });
    });

    $('.dynamic-tree-box', container).dynamictree();
    $('#orderAggregator', container).aggregator();

    $('.completion-rows', container).each(function() {
        completionRowsInit($(this));
    });

    $('.bookingSearch', container).each(function() {
        if ($(this).hasClass('tt-hint') == true) {
            return;
        }

        if ($(this).hasClass('tt-input') == false) {
            var appendTo = 'body';
            if ($(this).closest('.modal').length != 0) {
                appendTo = $(this).closest('.modal');
            }

            $(this).autocomplete({
                source: absolute_url + '/users/search',
                appendTo: appendTo,
                select: function(event, ui) {
                    var aggregate_id = $(this).attr('data-aggregate');
                    var while_shipping = ($(this).closest('.modal.add-booking-while-shipping').length != 0);
                    var fill_target = $(this).closest('.fillable-booking-space').find('.other-booking');
                    fill_target.empty().append(utils.loadingPlaceholder());

                    var data = {};
                    var mode = $(this).attr('data-enforce-booking-mode');
                    if (mode != null)
                        data.enforce = mode;

                    var url = '';

                    if (while_shipping) {
                        url = absolute_url + '/delivery/' + aggregate_id + '/user/' + ui.item.id;
                    }
                    else {
                        url = absolute_url + '/booking/' + aggregate_id + '/user/' + ui.item.id + '?extended=true';
                    }

                    $.ajax({
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
        }
    });

    if (container.hasClass('modal')) {
        container.draggable({
            handle: '.modal-header'
        });
    }
    else {
        $('.modal', container).draggable({
            handle: '.modal-header'
        });
    }

    function listenMeasureSelectors(mselectors) {
        mselectors.each(function() {
            enforceMeasureDiscrete($(this));
        }).on('change', function(event) {
            enforceMeasureDiscrete($(this));
        });
    }

    var mselectors = $('.measure-selector', container);
    if (mselectors.length != 0) {
        if (measure_discrete == null) {
            utils.postAjax({
                method: 'GET',
                url: 'measures/discretes',
                dataType: 'JSON',
                success: function(data) {
                    measure_discrete = data;
                    listenMeasureSelectors(mselectors);
                }
            });
        }
        else {
            listenMeasureSelectors(mselectors);
        }
    }

    $('.collapse_trigger', container).each(function() {
        triggerCollapse($(this));
    });

	/*
		Per ignoti motivi, capita che l'HTML che arriva in modo asincrono dal
		server sia riformattato ed i nodi che dovrebbero stare nel nodo
		principale siano messi dentro ad altri.
		Questo è problematico, in particolare per i modali dotati di form che
		vengono messi dentro ad altri form (rompendo il comportamento in fase di
		submit).
		Pertanto qui esplicitamente ed improrogabilmente sposto i contenuti
		marcati come .postponed nel nodo #postponed, che sta al fondo della
		pagina, rimettendo ordine nella gerarchia del DOM.
	*/
    $('.postponed', container).appendTo('#postponed').removeClass('postponed');

    $('ul[role=tablist]', container).each(function() {
        if ($(this).find('li.active').length == 0) {
            $(this).find('li a').first().tab('show');
        }
    });

    $('.date[data-enforce-after]', container).each(function() {
        var current = $(this);
        var select = current.attr('data-enforce-after');
        var target = current.closest('.input-group').find(select);
		if (target.length == 0) {
			target = current.closest('form').find(select);
        }

		target.datepicker().on('changeDate', function() {
            var current_start = current.datepicker('getDate');
            var current_ref = target.datepicker('getDate');
            if (current_start < current_ref) {
                current.datepicker('setDate', current_ref);
            }
        });
    });

    $('.csv_movement_type_select', container).each(function() {
        enforcePaymentMethod($(this));
    });

    setupImportCsvEditor(container);
    setupPermissionsEditor(container);

    Modifiers.init(container);
    Lists.init(container);
}

function voidForm(form) {
    form.find('input[type!=hidden]').val('');
    form.find('textarea').val('');
    form.find('select option:first').prop('selected', true);
    form.find('.error-message').remove();
}

function fixContactField(input, typeclass) {
    input.attr('class', '').addClass('form-control');

    if (typeclass == 'email') {
        input.attr('type', 'email');
    }
    else {
        input.attr('type', 'text');
        input.addClass(typeclass);
    }
}

function sortingDates(a, b) {
    a = utils.parseFullDate(a);
    b = utils.parseFullDate(b);

    if (a == b)
        return 0;
    else if (a < b)
        return -1;
    else
        return 1;
}

function sortingValues(a, b) {
    a = utils.parseFloatC(a);
    b = utils.parseFloatC(b);

    if (a == b)
        return 0;
    else if (a < b)
        return -1;
    else
        return 1;
}

function checkboxSorter(a, b) {
    var ah = $(a).is(':checked');
    var bh = $(b).is(':checked');

    if (ah == bh)
        return 0;
    if (ah == true)
        return -1;
    else
        return 1;
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        var img = $(input).closest('.img-preview').find('img');

        reader.onload = function (e) {
            img.attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function wizardLoadPage(node, contents) {
    try {
        let previous = node.closest('.modal');
        previous.modal('hide');

        let next = $(contents);
        utils.j().initElements(next);
        next.modal('show');
    }
    catch(error) {
        console.log(error);
        var json = JSON.parse(contents);
        var modal = $('#service-modal');
        modal.find('.modal-body').empty().append('<p>' + json.message + '</p>');
        modal.modal('show');
    }
}

function completionRowsInit(node) {
    $(node).find('input:text').each(function() {
        if ($(this).hasClass('ui-autocomplete-input') == true) {
            return;
        }

        var source = $(this).closest('.completion-rows').attr('data-completion-source');

        $(this).autocomplete({
            source: source,
            appendTo: $(this).closest('.completion-rows'),
            select: function(event, ui) {
                var row = $(this).closest('li');
                row.before('<li class="list-group-item" data-object-id="' + ui.item.id + '">' + ui.item.label + '<div class="btn btn-xs btn-danger float-end"><i class="bi-x-lg"></i></div></li>');

                var container = row.closest('.completion-rows');
                var fn = Callables[container.attr('data-callback-add')];
                if (typeof fn === 'function') {
                    fn(container, ui.item.id);
                }
            }
        });
    });

    $(node).on('click', '.btn-danger', function() {
        var row = $(this).closest('li');

        var container = row.closest('.completion-rows');
        var fn = Callables[container.attr('data-callback-remove')];
        if (typeof fn === 'function') {
            fn(container, row.attr('data-object-id'));
        }

        row.remove();
    });
}

function setupImportCsvEditor(container) {
    $('#import_csv_sorter .im_draggable', container).each(function() {
        $(this).draggable({
            helper: 'clone',
            revert: 'invalid'
        });
    });

    $('#import_csv_sorter .im_droppable', container).droppable({
        drop: function(event, ui) {
            var node = ui.draggable.clone();
            node.find('input:hidden').attr('name', 'column[]');
            $(this).find('.column_content').empty().append(node.contents());
        }
    });
}

/*
    I form possono includere una serie di campi <input type="hidden"> che, in
    funzione dell'attributo "name", possono attivare delle funzioni speciali
    dopo il submit usando il valore ritornato
*/
function miscInnerCallbacks(form, data) {
    if (locker == true)
        return;

    locker = true;

    var test = form.find('input[name=test-feedback]');
    if (test.length != 0) {
        if (data.status == 'error') {
            utils.displayServerError(form, data);
            return;
        }
    }

    Lists.innerCallbacks(form, data);

    var test = form.find('input[name=update-select]');
    if (test.length != 0) {
        var selectname = test.val();
        $('select[name=' + selectname + ']').each(function() {
            var o = $('<option value="' + data.id + '" selected="selected">' + data.name + '</option>');
            if (data.hasOwnProperty('parent') && data.parent != null) {
                var parent = $(this).find('option[value=' + data.parent + ']').first();
                var pname = parent.text().replace(/&nbsp;/g, ' ');
                var indent = '&nbsp;&nbsp;';

                for (var i = 0; i < pname.length; i++) {
                    if (pname[i] == ' ')
                        indent += '&nbsp;';
                    else
                        break;
                }

                o.prepend(indent);
                parent.after(o);
            } else {
                var reserved = ['id', 'name', 'status'];
                for (var property in data)
                    if (data.hasOwnProperty(property) && reserved.indexOf(property) < 0)
                        o.attr('data-' + property, data[property]);

                var trigger = $(this).find('option[value=run_modal]');
                if (trigger.length != 0)
                    trigger.before(o);
                else
                    $(this).append(0);
            }
        });
    }

    var test = form.find('input[name=update-field]');
    if (test.length != 0) {
        test.each(function() {
            var identifier_holder = utils.sanitizeId($(this).val());

            var node = $('[data-updatable-name=' + identifier_holder + ']');
            var field = node.attr('data-updatable-field');
            if (field == null)
                field = identifier_holder;

            var value = data[field];

            if (node.is('input:hidden'))
                node.val(value);
            else
                node.html(value);
        });
    }

    var test = form.find('input[name=post-saved-refetch]');
    if (test.length != 0) {
        test.each(function() {
            var target = utils.sanitizeId($(this).val());
            var box = $(target);

            var url = box.attr('data-fetch-url');
            if (url == null) {
                url = $(this).attr('data-fetch-url');
            }

            utils.j().fetchNode(url, box);
        });
    }

    var test = form.find('input[name^=post-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            var function_name = $(this).val();

            var fn = localCallables[function_name] || Callables[function_name];
            if (typeof fn === 'function') {
                fn(form, data);
            }
        });
    }

    var test = form.find('input[name=reload-portion]');
    if (test.length != 0) {
        test.each(function() {
            var identifier = $(this).val();
            $(identifier).each(function() {
                utils.j().reloadNode($(this));
            });
        });
    }

    var test = form.find('input[name=void-form]');
    if (test.length != 0) {
        test.each(function() {
            voidForm(form);
        });
    }

    var test = form.find('input[name=close-modal]');
    if (test.length != 0) {
        var modal = form.parents('.modal');
        if (modal.length != 0)
            modal.modal('hide');
    }

    var test = form.find('input[name=close-all-modal]');
    if (test.length != 0) {
        $('.modal.fade.show').modal('hide');
    }

    var test = form.find('input[name=reload-whole-page]');
    if (test.length != 0) {
        location.reload();
    }

    locker = false;
}

function miscInnerModalCallbacks(modal) {
    var test = modal.find('input[name=reload-portion]');
    if (test.length != 0) {
        test.each(function() {
            var identifier = $(this).val();
            utils.j().reloadNode($(identifier));
        });
    }
}

/*
    Questo è per gestire campi diversi di cui almeno uno è obbligatorio
*/
function reviewRequired(panel)
{
    panel.find('input[data-alternative-required]').each(function() {
        var alternative = $(this).attr('data-alternative-required');
        if (alternative) {
            var alt = panel.find('[name="' + alternative + '"]');
            if (alt.val() != '') {
                $(this).prop('required', false);
            }
            else {
                $(this).prop('required', true);
            }
        }
    });
}

function triggerCollapse(trigger)
{
    var name = trigger.attr('name');
    var display = trigger.prop('checked');

    /*
        L'evento show.bs.collapse va espressamente bloccato, altrimenti se
        il widget si trova all'interno di una accordion risale fino a quella
        e scattano anche le callback per le async-accordion
    */

    $('.collapse[data-triggerable=' + name + ']').one('show.bs.collapse', function(e) {
        e.stopPropagation();
    }).collapse(display ? 'show' : 'hide').find('.required_when_triggered').prop('required', display);

    $('.collapse[data-triggerable-reverse=' + name + ']').one('show.bs.collapse', function(e) {
        e.stopPropagation();
    }).collapse(display ? 'hide' : 'show').find('.required_when_triggered').prop('required', display == false);

    var panel = null;

    if (display) {
        panel = $('.collapse[data-triggerable=' + name + ']');
    }
    else {
        panel = $('.collapse[data-triggerable-reverse=' + name + ']');
    }

    reviewRequired(panel);
}

/*******************************************************************************
	Contabilità
*/

/*
    Questa è per forzare i metodi di pagamento disponibili nel modale di
    importazione dei movimenti contabili
*/
function enforcePaymentMethod(node) {
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

                if (default_payment == v) {
                    $(this).prop('selected', true);
                }
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

/*******************************************************************************
	Prodotti
*/

function enforceMeasureDiscrete(node) {
    var form = node.closest('form');
    var selected = node.find('option:selected').val();
    var discrete = measure_discrete[selected];
    var disabled = (discrete == '1');

    form.find('input[name=portion_quantity]').prop('disabled', disabled);
	form.find('input[name=weight]').prop('disabled', !disabled);
	var multiple_widget = form.find('input[name=multiple]');
    var min_quantity_widget = form.find('input[name=min_quantity]');
    var max_quantity_widget = form.find('input[name=max_quantity]');
    var max_available_widget = form.find('input[name=max_available]');

	if (disabled) {
		form.find('input[name=portion_quantity]').val('0.000');
		form.find('input[name=variable]').prop('checked', false).prop('disabled', true);
        node.siblings('.form-text').removeClass('d-none');

		multiple_widget.attr('data-enforce-minimum', 1);
		multiple_widget.attr('data-enforce-integer', 1);

		multiple_widget.val(parseInt(multiple_widget.val()));
		if (multiple_widget.val() < 1) {
			multiple_widget.val('1.000');
        }

        min_quantity_widget.attr('data-enforce-integer', 1);
        max_quantity_widget.attr('data-enforce-integer', 1);
        max_available_widget.attr('data-enforce-integer', 1);
	}
	else {
		form.find('input[name=weight]').val('0.000');
		form.find('input[name=variable]').prop('disabled', false);
        node.siblings('.form-text').addClass('d-none');
		multiple_widget.removeAttr('data-enforce-minimum').removeAttr('data-enforce-integer');
        min_quantity_widget.removeAttr('data-enforce-integer');
        max_quantity_widget.removeAttr('data-enforce-integer');
        max_available_widget.removeAttr('data-enforce-integer');
	}
}

/*******************************************************************************
	Ordini
*/

function setCellValue(cell, value) {
    string = value;

    if (cell.text().indexOf(current_currency) != -1)
        string = utils.priceRound(value) + ' ' + current_currency;

    cell.text(string);
}

/*******************************************************************************
	Prenotazioni / Consegne
*/

function bookingTotal(editor) {
	var form = $(editor).closest('form');

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

	var url = form.attr('data-dynamic-url');

	$.ajax({
		url: url,
		method: 'GET',
		data: data,
		dataType: 'JSON',
		success: function(data) {
			if (Object.entries(data.bookings).length == 0) {
				$('.booking-product-price span', form).text(utils.priceRound(0));
				$('.booking-modifier', container).text(utils.priceRound(0));
				$('.booking-total', container).text(utils.priceRound(0));
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

					for (let [product_id, product_meta] of Object.entries(booking_data.products)) {
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
                                varinputbox.toggleClass('is-invalid', variant.quantity == 0 && utils.parseFloatC(inputbox.val()) != 0);

                                if (action == 'shipped') {
                                    varinputbox.closest('tr').find('.booking-product-price span').text(utils.priceRound(variant.total));
                                }
                            }
                        }
                        else {
                            inputbox.toggleClass('is-invalid', product_meta.quantity == 0 && utils.parseFloatC(inputbox.val()) != 0);
                        }
					}

                    Modifiers.updateBookingModifiers(booking_data.modifiers, container);

					var t = utils.priceRound(booking_data.total);
					$('.booking-total', container).text(t);
					grand_total += parseFloat(t);
					status[booking_id] = booking_data.total;
				}

				form.find('.all-bookings-total').text(utils.priceRound(grand_total));

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
		}
	});
}

/*******************************************************************************
	Permessi
*/

function setupPermissionsEditor(container) {
    $('.roleAssign', container).each(function() {
        if ($(this).hasClass('tt-hint') == true) {
            return;
        }

        if ($(this).hasClass('tt-input') == false) {
            $(this).autocomplete({
                source: absolute_url + '/users/search',
                select: function(event, ui) {
                    var text = $(this);
                    var role_id = Lists.currentLoadableLoaded(this);
                    var group = $(this).closest('.accordion-body');
                    var user_id = ui.item.id;

                    var label = ui.item.label;
                    $.ajax({
                        method: 'POST',
                        url: absolute_url + '/roles/attach',
                        dataType: 'HTML',
                        data: {
                            role: role_id,
                            user: user_id,
                        },
                        success: function(data) {
                            var panel = $(data);
                            var identifier = $(panel).attr('id');
                            utils.j().initElements(panel);
                            group.find('.tab-content').append(panel);

                            var tab = $('<li class="nav-item" data-user="' + user_id + '"><button type="button" class="nav-link" data-bs-target="#' + identifier + '" data-bs-toggle="tab">' + label + '</button></li>');
                            group.find('[role=tablist]').find('.last-tab').before(tab);
                            text.val('');
                        }
                    });
                }
            });
        }
    });

    $('.role-editor', container).on('change', 'input:checkbox[data-role]', function(e) {
        var check = $(this);
        check.removeClass('saved-checkbox saved-left-feedback');

        var url = '';
        if (check.is(':checked') == true) {
            url = absolute_url + '/roles/attach';
        }
        else {
            url = absolute_url + '/roles/detach';
        }

        var data = {};
        data.role = check.attr('data-role');
        data.action = check.attr('data-action');
        data.user = check.attr('data-user');
        data.target_id = check.attr('data-target-id');
        data.target_class = check.attr('data-target-class');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            success: function() {
                check.addClass('saved-checkbox saved-left-feedback');
            }
        });

    }).on('click', '.remove-role', function(e) {
        e.preventDefault();

        if(confirm(_('Sei sicuro di voler revocare questo ruolo?'))) {
            var button = $(this);

            var data = {
                role: button.attr('data-role'),
                user: button.attr('data-user')
            };

            var userid = data.user;

            $.ajax({
                method: 'POST',
                url: absolute_url + '/roles/detach',
                data: data,
                success: function() {
                    var panel = button.closest('.accordion-body');
                    var tab = panel.find('[data-user=' + userid + ']');
                    panel.find(tab.find('button').attr('data-bs-target')).remove();
                    tab.remove();
                }
            });
        }
    });
}

/*******************************************************************************
	Core
*/

$(document).ready(function() {
    utils.j().init({
        initFunction: function(container) {
            generalInit(container);
        }
    });

    /*
        Poiché l'altezza della navbar è estremamente variabile, a seconda delle
        funzioni abilitate, calcolo lo spazio da lasciare sopra al body in modo
        dinamico
    */
    var navbar = $('.navbar').first();
    $('body').css('padding-top', (navbar.height() * 2) + 'px');

    $('#preloader').remove();

    $.ajaxSetup({
        cache: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ajaxError(function(event, jqXHR) {
        if (jqXHR.status == 401)
            window.location.href = '/login';
    });

    /*
        Per gestire i deep link diretti agli elementi delle liste
    */
    if (location.hash != '') {
        setTimeout(function() {
            var id = location.hash;
            if (id.charAt(0) === '#')
                id = id.substr(1);
            $('.accordion-item[data-element-id=' + id + ']').find('.accordion-button').first().click();
        }, 100);
    }

    if ($('#actual-calendar').length != 0) {
        $('#actual-calendar').ContinuousCalendar({
			days: translated_days,
			months: translated_months,
			rows: 4,
            events: dates_events
        });
    }

    $('#prompt-message-modal').modal('show');

    $('#home-notifications .alert').on('closed.bs.alert', function() {
        var id = $(this).find('input:hidden[name=notification_id]').val();
        $.post('notifications/markread/' + id);

        if ($('#home-notifications .alert').length == 0) {
            $('#home-notifications').hide('fadeout');
        }
    });

	$('.remember-checkbox').each(function() {
		var attr = $(this).attr('data-attribute');
		var value = Cookies.get(attr);

		if (typeof value !== 'undefined') {
			$(this).prop('checked', value == 'true');
		}
		else {
			$(this).prop('checked', $(this).attr('data-attribute-default') == 'true');
		}
	}).change(function() {
		var attr = $(this).attr('data-attribute');
		var value = $(this).prop('checked') ? 'true' : 'false';
		Cookies.set(attr, value);
	});

    $('body').on('keydown', 'input.number', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            return;
        }

		var integer = $(this).attr('data-enforce-integer');
		if (integer && (e.key == '.' || e.key == ',')) {
			e.preventDefault();
			return;
		}

        var allow_negative = ($(this).attr('data-allow-negative') == '1');
		var minimum = $(this).attr('data-enforce-minimum');

        $(this).val(function(index, value) {
            var val = value.replace(/,/g, '.');
            if (allow_negative)
                val = val.replace(/[^\-0-9\.]/g, '');
            else
                val = val.replace(/[^0-9\.]/g, '');

			if (val != '' && minimum && val < minimum)
				val = minimum;

            return val;
        });
    })
    .on('focus', 'input.number', function(e) {
        var v = utils.parseFloatC($(this).val());
        if (v == 0) {
			var minimum = $(this).attr('data-enforce-minimum');
			if (minimum)
				$(this).val(minimum);
			else
				$(this).val('0');
		}
    })
    .on('blur', 'input.number', function(e) {
        $(this).val(function(index, value) {
			var v = utils.parseFloatC(value);

			var minimum = $(this).attr('data-enforce-minimum');
			if (minimum && v < minimum)
				return minimum;
			else
				return v;
        });
    });

	$('body').on('click', '.table-sorter a', function(e) {
		e.preventDefault();
		var target = $($(this).closest('.table-sorter').attr('data-table-target'));
		var attribute = $(this).attr('data-sort-by');
		var target_body = target.find('tbody');

		target_body.find('> .table-sorting-header').addClass('d-none').filter('[data-sorting-' + attribute + ']').removeClass('d-none');

		target_body.find('> tr[data-sorting-' + attribute + ']').filter(':not(.table-sorting-header)').sort(function(a, b) {
			var attr_a = $(a).attr('data-sorting-' + attribute);
			var attr_b = $(b).attr('data-sorting-' + attribute);
			return attr_a.localeCompare(attr_b);
		}).each(function() {
			$(this).appendTo(target_body);
		});

		target_body.find('> tr.do-not-sort').each(function() {
			$(this).appendTo(target_body);
		});
	});

    $('body').on('blur', '.trim-2-ddigits', function() {
        $(this).val(function(index, value) {
            return utils.parseFloatC(value).toFixed(2);
        });
    })
    .on('blur', '.trim-3-ddigits', function() {
        $(this).val(function(index, value) {
            return utils.parseFloatC(value).toFixed(3);
        });
    });

    $('body').on('change', '.triggers-all-checkbox', function() {
        $(this).prop('disabled', true);

        var form = $(this).closest('form');
        var target = $(this).attr('data-target-class');
        var new_status = $(this).prop('checked');

        form.find('.' + target).each(function() {
            $(this).prop('checked', new_status);
        });

        $(this).prop('disabled', false);
    })
    .on('click', '.triggers-all-radio label', function() {
        var form = $(this).closest('form');
        var target = $(this).attr('data-target-class');
        form.find('.' + target).button('toggle');
    })
    .on('change', '.triggers-all-selects', function() {
        var form = $(this).closest('form');
        var target = $(this).attr('data-target-class');
        var value = $(this).find('option:selected').val();
        var t = form.find('.' + target).not($(this));
        t.find('option[value=' + value + ']').prop('selected', true);
        t.change();
    });

    $('body').on('change', 'input[data-alternative-required]', function() {
        reviewRequired($(this).closest('form'));
    });

    $('body').on('click', '.reloader', function(event) {
        var listid = $(this).attr('data-reload-target');

        if (listid == null) {
            location.reload();
        }
        else {
            /*
                Nel caso in cui il tasto sia dentro ad un modale, qui ne forzo la
                chiusura (che non e' implicita, se questo non viene fatto resta
                l'overlay grigio in sovraimpressione)
            */
            var modal = $(this).closest('.modal').first();
            if (modal != null) {
                modal.on('hidden.bs.modal', function() {
                    Lists.reloadCurrentLoadable(listid);
                });
                modal.modal('hide');
            }
            else {
                Lists.reloadCurrentLoadable(listid);
            }
        }
    });

    $('body').on('focus', '.date[data-enforce-after]', function() {
        var select = $(this).attr('data-enforce-after');
        var target = $(this).closest('.input-group').find(select);
		if (target.length == 0) {
			target = $(this).closest('form').find(select);
        }

        /*
            Problema: cercando di navigare tra i mesi all'interno del datepicker
            viene lanciato nuovamente l'evento di focus, che fa rientrare in
            questa funzione, e se setStartDate() viene incondazionatamente
            eseguita modifica a sua volta la data annullando l'operazione.
            Dunque qui la eseguo solo se non l'ho già fatto (se la data di
            inizio forzato non corrisponde a quel che dovrebbe essere), badando
            però a fare i confronti sui giusti formati
        */
        var current_start = $(this).datepicker('getStartDate');
        var current_ref = target.datepicker('getUTCDate');
        if (current_start.toString() != current_ref.toString()) {
            $(this).datepicker('setStartDate', current_ref);
        }
    });

    $('body').on('change', '.select-fetcher', function(event) {
        var targetid = $(this).attr('data-fetcher-target');
        var target = $(this).parent().find(targetid);
        target.empty();

		var id = $(this).find('option:selected').val();
		var url = $(this).attr('data-fetcher-url');
		url = url.replace('XXX', id);

        target.append(utils.loadingPlaceholder());
        $.get(url, function(data) {
            target.empty().append(data);
        });
    });

    $('body').on('click', '.object-details', function() {
        var url = $(this).attr('data-show-url');
        var modal = $('#service-modal');
        modal.find('.modal-body').empty().append(utils.loadingPlaceholder());
        modal.modal('show');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'HTML',
            success: function(data) {
                data = $(data);
                modal.find('.modal-body').empty().append(data);
                utils.j().initElements(data);
            }
        });
    });

    $('body').on('submit', '.main-form', function(event) {
        event.preventDefault();

        var form = $(this);
        form.find('button[type=submit]').prop('disabled', true);

        utils.postAjax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: new FormData(this),
            dataType: 'JSON',
            processData: false,
            contentType: false,

            success: function(data) {
                if (data.hasOwnProperty('status') && data.status == 'error') {
                    utils.displayServerError(form, data);
                    form.find('button[type=submit]').prop('disabled', false);
                }
                else {
                    miscInnerCallbacks(form, data);
                    Lists.closeParent(form);
                }
            }
        });
    });

    $('body').on('click', '.main-form .delete-button', function(event) {
        event.preventDefault();
        var form = $(this).closest('.main-form');

        /*
        	TODO: visualizzare nome dell'elemento che si sta rimuovendo
        */

        if (confirm(_('Sei sicuro di voler eliminare questo elemento?'))) {
            form.find('button').prop('disabled', true);

            $.ajax({
                method: 'DELETE',
                url: form.attr('action'),
                dataType: 'json',

                success: function(data) {
                    var upper = Lists.closeParent(form);
                    var list = upper.closest('.loadable-list');
                    upper.remove();
                    Lists.testListsEmptiness(list);
                }
            });
        }
    });

    $('body').on('click', '.icons-legend button, .icons-legend a', function(e) {
        e.preventDefault();
        filters.iconsLegendTrigger($(this), '.icons-legend');
    });

    $('body').on('click', '.table-icons-legend button, .table-icons-legend a', function(e) {
        e.preventDefault();
        filters.iconsLegendTrigger($(this), '.table-icons-legend');
    });

    $('body').on('keyup', '.table-text-filter', function() {
        filters.tableFilters($(this).attr('data-table-target'));
    });

    $('body').on('keyup', '.table-number-filters input.table-number-filter', function() {
        filters.tableFilters($(this).closest('.table-number-filters').attr('data-table-target'));
    });

    $('body').on('change', '.table-number-filters input[name=filter_mode]', function() {
        $(this).closest('.input-group').find('input.table-number-filter').keyup();
    });

    $('body').on('change', '.table-filters input:radio', function() {
        filters.tableFilters($(this).closest('.table-filters').attr('data-table-target'));
    });

    $('body').on('change', 'input:file[data-max-size]', function() {
        if (this.files && this.files[0]) {
            var max = $(this).attr('data-max-size');
            var file = this.files[0].size;
            if (file > max) {
                $(this).val('');
                utils.setInputErrorText($(this), _('Il file è troppo grande!'));
                return false;
            }
            else {
                utils.setInputErrorText($(this), null);
                return true;
            }
        }
    });

    $('body').on('change', '.img-preview input:file', function() {
        previewImage(this);
    });

    $('body').on('submit', '.inner-form', function(event) {
        event.preventDefault();
        var form = $(this);

        var proceed = true;

        var test = form.find('input[name^=pre-saved-function]');
        if (test.length != 0) {
            test.each(function() {
                var fn = Callables[$(this).val()];
                if (typeof fn === 'function') {
                    /*
                        Se una pre-saved-function solleva una eccezione, il form
                        non viene effettivamente eseguito
                    */
                    try {
                        fn(form);
                    }
                    catch(error) {
                        proceed = false;
                    }
                }
            });
        }

        if (proceed == false) {
            return;
        }

        var submit_button = utils.submitButton(form);

        submit_button.each(function() {
            var idle_text = $(this).text();
            $(this).attr('data-idle-text', idle_text).empty().append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
        });

        var params = {
            method: form.attr('method'),
            url: form.attr('action'),
            dataType: 'JSON',

            success: function(data) {
                submit_button.each(function() {
                    utils.inlineFeedback($(this), _('Salvato!'));
                });

                miscInnerCallbacks(form, data);
            },

            error: function(data) {
                submit_button.each(function() {
                    utils.inlineFeedback($(this), _('ERRORE!'));
                });
            }
        };

        if (form.find('input[type="file"]').length) {
            params.data = utils.j().serializeFormData(form);
            params.processData = false;
            params.contentType = false;
        }
        else {
            params.data = utils.j().serializeForm(form);
        }

        utils.postAjax(params);
    });

    $('body').on('hide.bs.modal', '.inner-modal', function(event) {
        miscInnerModalCallbacks($(this));
    });

    $('body').on('change', '.auto-submit select', function(event) {
        var form = $(this).closest('form');

        var data = new FormData(form.get(0));
        var method = form.attr('method').toUpperCase();
        if (method == 'PUT') {
            method = 'POST';
            data.append('_method', 'PUT');
        }

        $.ajax({
            method: method,
            url: form.attr('action'),
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function(data) {
                miscInnerCallbacks(form, data);
            }
        });
    });

	$('body').on('change', '.simple-sum', function() {
		var sum = 0;
		var container = $(this).closest('.simple-sum-container');
		container.find('.simple-sum').each(function() {
			sum += utils.parseFloatC($(this).val());
		});
		container.find('.simple-sum-result').val(sum);
	});

    $('body').on('click', '.spare-delete-button', function(event) {
        event.preventDefault();

        if (confirm('Sei sicuro?')) {
            var form = $(this).closest('form');

            $.ajax({
                url: $(this).attr('data-delete-url'),
                method: 'DELETE',
				dataType: 'json',
                success: function(data) {
                    miscInnerCallbacks(form, data);
                }
            });
        }
    });

    $('body').on('click', '.spare-modal-delete-button', function(event) {
        event.preventDefault();
        var modal = $('#delete-confirm-modal');
        modal.find('form').attr('action', $(this).attr('data-delete-url'));
        modal.modal('show');
    });

    $('body').on('click', '.table_to_csv', function(e) {
        e.preventDefault();
        var target = $(this).attr('data-target');
        var data = $(target).TableCSVExport({
            delivery: 'download',
            filename: _('bilanci_ricalcolati.csv')
        });
    });

    $('body').on('click', '.link-button', function(e) {
        e.preventDefault();
        var url = $(this).attr('data-link');
        window.open(url, '_blank');
    });

    $('body').on('change', '.contacts-selection select', function() {
        var input = $(this).closest('tr').find('input[name="contact_value[]"]');
        var typeclass = $(this).find('option:selected').val();
        fixContactField(input, typeclass);
    });

    $('body').on('focus', 'input.address', function() {
        utils.complexPopover($(this), 'address', function(input) {
            var ret = $('<div>\
                <div class="row mb-2">\
                    <label for="street" class="col-4 col-form-label">' + _('Indirizzo') + '</label>\
                    <div class="col-8"><input type="text" class="form-control" name="street" value="" autocomplete="off"></div>\
                </div>\
                <div class="row mb-2">\
                    <label for="city" class="col-4 col-form-label">' + _('Città') + '</label>\
                    <div class="col-sm-8"><input type="text" class="form-control" name="city" value="" autocomplete="off"></div>\
                </div>\
                <div class="row mb-2">\
                    <label for="cap" class="col-4 col-form-label">' + _('CAP') + '</label>\
                    <div class="col-sm-8"><input type="text" class="form-control" name="cap" value="" autocomplete="off"></div>\
                </div>\
                <div class="row mb-2">\
                    <div class="col-8 offset-4"><button class="btn btn-light">' + _('Annulla') + '</button> <button class="btn btn-success">' + _('Salva') + '</button></div>\
                </div>\
            </div>');

            var value = input.val();
            if (value != '') {
                var values = value.split(',');
                for(var i = values.length; i < 3; i++)
                    values[i] = '';
                ret.find('input[name=street]').val(values[0].trim());
                ret.find('input[name=city]').val(values[1].trim());
                ret.find('input[name=cap]').val(values[2].trim());
            }

            ret.find('button.btn-success').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var street = ret.find('input[name=street]').val().trim().replace(',', '');
                var city = ret.find('input[name=city]').val().trim().replace(',', '');
                var cap = ret.find('input[name=cap]').val().trim().replace(',', '');

                if (street == '' && city == '' && cap == '')
                    input.val('');
                else
                    input.val(street + ', ' + city + ', ' + cap);

                input.popover('dispose');
            });

            ret.find('button.btn-light').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                input.popover('dispose');
            });

            setTimeout(function() {
                ret.find('input[name=street]').focus();
            }, 200);

            return ret;
        });
    });

    $('body').on('focus', 'input.periodic', function() {
        utils.complexPopover($(this), 'periodic', function(input) {
            var ret = $('<div>\
                <div class="row mb-2">\
                    <label for="day" class="col-4 col-form-label">' + _('Giorno') + '</label>\
                    <div class="col-8">\
                        <select class="form-select" name="day" value="" autocomplete="off">\
                            <option value="monday">' + _('Lunedì') + '</option>\
                            <option value="tuesday">' + _('Martedì') + '</option>\
                            <option value="wednesday">' + _('Mercoledì') + '</option>\
                            <option value="thursday">' + _('Giovedì') + '</option>\
                            <option value="friday">' + _('Venerdì') + '</option>\
                            <option value="saturday">' + _('Sabato') + '</option>\
                            <option value="sunday">' + _('Domenica') + '</option>\
                        </select>\
                    </div>\
                </div>\
                <div class="row mb-2">\
                    <label for="cycle" class="col-4 col-form-label">' + _('Periodicità') + '</label>\
                    <div class="col-8">\
                        <select class="form-select" name="cycle" value="" autocomplete="off">\
                            <option value="all">' + _('Tutti') + '</option>\
                            <option value="biweekly">' + _('Ogni due Settimane') + '</option>\
                            <option value="month_first">' + _('Primo del Mese') + '</option>\
                            <option value="month_second">' + _('Secondo del Mese') + '</option>\
                            <option value="month_third">' + _('Terzo del Mese') + '</option>\
                            <option value="month_fourth">' + _('Quarto del Mese') + '</option>\
                            <option value="month_last">' + _('Ultimo del Mese') + '</option>\
                        </select>\
                    </div>\
                </div>\
                <div class="row mb-2">\
                    <label for="day" class="col-4 col-form-label">' + _('Dal') + '</label>\
                    <div class="col-8"><input type="text" class="date form-control" name="from" value="" autocomplete="off"></div>\
                </div>\
                <div class="row mb-2">\
                    <label for="day" class="col-4 col-form-label">' + _('Al') + '</label>\
                    <div class="col-8"><input type="text" class="date form-control" name="to" value="" autocomplete="off"></div>\
                </div>\
                <div class="row mb-2">\
                    <div class="col-8 offset-4"><button class="btn btn-light">' + _('Annulla') + '</button> <button class="btn btn-success">' + _('Salva') + '</button></div>\
                </div>\
            </div>');

            $('input.date', ret).datepicker({
                format: 'DD dd MM yyyy',
                autoclose: true,
                language: current_language,
                clearBtn: true,
            });

            var value = input.val();
            if (value != '') {
                var values = value.split(' - ');
                for(var i = values.length; i < 4; i++)
                    values[i] = '';

                ret.find('select[name=day] option').filter(function() {
                    return $(this).html() == values[0];
                }).prop('selected', true);

                ret.find('select[name=cycle] option').filter(function() {
                    return $(this).html() == values[1];
                }).prop('selected', true);

                ret.find('input[name=from]').val(values[2].trim());
                ret.find('input[name=to]').val(values[3].trim());
            }

            ret.find('button.btn-success').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                var day = ret.find('select[name=day] option:selected').text();
                var cycle = ret.find('select[name=cycle] option:selected').text();
                var from = ret.find('input[name=from]').val().trim().replace(',', '');
                var to = ret.find('input[name=to]').val().trim().replace(',', '');
                input.val(day + ' - ' + cycle + ' - ' + from + ' - ' + to).change();
                input.popover('dispose');
            });

            ret.find('button.btn-light').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                input.popover('dispose');
            });

            setTimeout(function() {
                ret.find('select[name=day]').focus();
            }, 200);

            return ret;
        });
    });

    $('body').on('change', '.status-selector input:radio[name*="status"]', function() {
        let field = $(this).closest('.status-selector');
        let status = $(this).val();
        let del = (status != 'deleted');
        field.find('[name=deleted_at]').prop('hidden', del).closest('.input-group').prop('hidden', del);
        let sus = (status != 'suspended');
        field.find('[name=suspended_at]').prop('hidden', sus).closest('.input-group').prop('hidden', sus);
    });

    $('body').on('change', '.movement-modal input[name=method]', function() {
        if ($(this).prop('checked') == false) {
            return;
        }

        var method = $(this).val();
        var method_string = 'when-method-' + method;
        var modal = $(this).closest('.movement-modal');
        modal.find('[class*="when-method-"]').each(function() {
            $(this).toggleClass('hidden', ($(this).hasClass(method_string) == false));
        });
    })
    .on('change', '.movement-modal input[name=amount]', function() {
        var status = $(this).closest('.movement-modal').find('.sender-credit-status');
        if (status.length) {
            var amount = utils.parseFloatC($(this).val());
            var current = utils.parseFloatC(status.find('.current-sender-credit').text());
            if (amount > current)
                status.removeClass('alert-success').addClass('alert-danger');
            else
                status.removeClass('alert-danger').addClass('alert-success');
        }
    });

    $('body').on('change', '.movement-type-selector', function(event) {
        var type = $(this).find('option:selected').val();
        var selectors = $(this).closest('form').find('.selectors');
        selectors.empty().append(utils.loadingPlaceholder());

        $.ajax({
            method: 'GET',
            url: absolute_url + '/movements/create',
            dataType: 'html',
            data: {
                type: type
            },

            success: function(data) {
                data = $(data);
                selectors.empty().append(data);
                utils.j().initElements(data);
            }
        });
    });

    $('body').on('change', '.movement-type-editor select[name=sender_type], .movement-type-editor select[name=target_type]', function() {
        var editor = $(this).closest('.movement-type-editor');
        var sender = editor.find('select[name=sender_type] option:selected').val();
        var target = editor.find('select[name=target_type] option:selected').val();
        var table = editor.find('table');

        table.find('tbody tr').each(function() {
            var type = $(this).attr('data-target-class');
            /*
                Le righe relative al GAS non vengono mai nascoste, in quanto
                molti tipi di movimento vanno ad incidere sui saldi globali
                anche quando il GAS non è direttamente coinvolto
            */
            $(this).toggleClass('hidden', (type != 'App\\Gas' && type != sender && type != target));
        });

        table.find('thead input[data-active-for]').each(function() {
            var type = $(this).attr('data-active-for');
            if(type != '' && type != sender && type != target)
                $(this).prop('checked', false).prop('disabled', true).change();
            else
                $(this).prop('disabled', false);
        });
    })
    .on('change', '.movement-type-editor table thead input:checkbox', function() {
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
                var cell = $(this).find('td:nth-child(' + (index + 1) + ')');
                cell.find('label, input').prop('disabled', false);
            });
        }
    });

    $('body').on('click', '.form-filler button[type=submit]', function(event) {
        event.preventDefault();
        var form = $(this).closest('.form-filler');
        var target = $(form.attr('data-fill-target'));
        var data = form.find('input, select').serialize();
        target.empty().append(utils.loadingPlaceholder());

        $.ajax({
            method: 'GET',
            url: form.attr('data-action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                data = $(data);
                target.empty().append(data);
                utils.j().initElements(data);
            }
        });
    })
    .on('click', '.form-filler a.form-filler-download', function(event) {
        event.preventDefault();
        var data = $(this).closest('.form-filler').find('input, select').serializeArray();
        var url = $(this).attr('href') + '&' + $.param(data);
        window.open(url, '_blank');
    });

    $('body').on('change', '#dates-in-range input.date, #dates-in-range input.periodic', function() {
        if ($(this).val() == '') {
            return;
        }

        var row = $(this).closest('tr');
        if ($(this).hasClass('date')) {
            row.find('.periodic').val('');
        }
        else {
            row.find('.date').val('');
        }
    });

    $('body').on('change', '.collapse_trigger', function() {
        triggerCollapse($(this));
    });

    /*
        Gestione fornitori
    */

    $('body').on('click', '.variants-editor .delete-variant', function() {
        var editor = $(this).closest('.variants-editor');
        var id = $(this).closest('tr').attr('data-variant-id');

        $.ajax({
            method: 'DELETE',
            url: absolute_url + '/variants/' + id,
            success: function() {
                utils.j().reloadNode(editor);
            }
        });
    });

    $('body').on('click', '.export-custom-list', function(event) {
        event.preventDefault();

        var printable = new Array();

        var explicit_target = $(this).attr('data-target');
        if (explicit_target) {
            $(explicit_target).find('.accordion-item:visible').each(function() {
                printable.push($(this).attr('data-element-id'));
            });
        }
        else {
            /*
                Questo è per gestire il caso speciale dell'esportazione dei
                prodotti di un fornitore, i quali potrebbero essere visualizzati
                (e dunque filtrati) in una .loadablelist o nella tabella di
                modifica rapida
            */
            var tab = $(this).closest('.tab-pane').find('.tab-pane.active');

            if (tab.hasClass('details-list')) {
                tab.find('.loadable-list .accordion-item:visible').each(function() {
                    printable.push($(this).attr('data-element-id'));
                });
            }
            else {
                tab.find('.table tr:visible').each(function() {
                    printable.push($(this).attr('data-element-id'));
                });
            }
        }

        var data = {};

        var parent_form = utils.formByButton($(this));
        if (parent_form.length != 0) {
            data = parent_form.serializeArray();
            for (var i = 0; i < printable.length; i++) {
                data.push({name: 'printable[]', value: printable[i]});
            }
        }
        else {
            data = {printable: printable};
        }

        var url = $(this).attr('data-export-url') + '?' + $.param(data);
        window.open(url, '_blank');
    });

    /*
    	Gestione ordini
    */

    $('body').on('click', '.order-columns-selector .dropdown-menu', function(e) {
        e.stopPropagation();
    })
    .on('change', '.order-columns-selector input:checkbox', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var name = $(this).val();
        var show = $(this).prop('checked');
        $(this).closest('.btn-group').closest('form').find('.order-summary').first().find('.order-cell-' + name).toggleClass('hidden', !show);
    });

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
    .on('change', '.order-summary tr .discount-toggle', function() {
        var p = $(this).closest('tr').find('.product-price');
        p.find('.full-price, .product-discount-price').toggleClass('hidden');

        /*
        	TODO: aggiornare i prezzi totali nella tabella dell'ordine
        */
    })
    .on('change', '.order-document-download-modal input[name=send_mail]', function() {
        var status = $(this).prop('checked');
        var form = $(this).closest('.order-document-download-modal').find('form');
        var submit = utils.submitButton(form);

        if (status) {
            submit.text(_('Invia Mail'));
        }
        else {
            submit.text(_('Salva'));
        }

        form.toggleClass('inner-form', status);
    });

    $('body').on('change', '[id^="createOrder"] select[name^=supplier_id]', function() {
        $.ajax({
            url: absolute_url + '/dates/query',
            method: 'GET',
            data: {
                supplier_id: $(this).val()
            },
            dataType: 'HTML',
            success: function(data) {
                data = $(data);
                $('[id^="createOrder"] .supplier-future-dates').empty().append(data);
                utils.j().initElements(data);
            }
        });
    });

    $('body').on('click', '.suggested-dates li', function() {
        var date = $(this).text();
        $(this).closest('form').find('input[name=shipping]').val(date);
    });

    /*
    	Interazioni dinamiche sul pannello prenotazioni
    */

    $('body').on('click', '.send-order-notifications', function(e) {
        e.preventDefault();

        var button = $(this);
        button.prop('disabled', true);
        var id = button.attr('data-aggregate-id');
        var date = button.closest('form').find('.last-date');

        $.ajax({
            url: absolute_url + '/aggregates/notify/' + id,
            method: 'POST',
            success: function(data) {
                date.text(data);
                button.prop('disabled', false);
            },
            error: function() {
                button.prop('disabled', false);
            }
        });
    });

    $('body').on('keyup', '.booking-product-quantity input', function(e) {
        var editor = $(this).closest('.booking-editor');
        bookingTotal(editor);

    }).on('change', '.variants-selector select', function() {
        var editor = $(this).closest('.booking-editor');
        bookingTotal(editor);

    }).on('blur', '.booking-product-quantity input', function() {
        if ($(this).val() == '' || $(this).hasClass('is-invalid')) {
            $(this).val('0').keyup();
        }

    }).on('focus', '.booking-product-quantity input', function() {
        $(this).removeClass('.is-invalid');

    }).on('click', '.booking-product .add-variant', function(e) {
        e.preventDefault();
        var variant_selector = $(this).closest('.variants-selector');
        var master = variant_selector.find('.master-variant-selector').clone().removeClass('master-variant-selector');
        master.find('.skip-on-submit').removeClass('skip-on-submit');
        variant_selector.append(master);
        return false;
    });

    $('body').on('click', '.mobile-quantity-switch button', function(e) {
        e.preventDefault();

        var input = $(this).closest('.mobile-quantity-switch').siblings('.booking-product-quantity').find('input.number');

        var original = parseFloat(input.val());
        if ($(this).hasClass('plus')) {
            input.val(original + 1);
        }
        else {
            if (original == 0)
                return;
            input.val(original - 1);
        }

        input.keyup();
    });

    $('body').on('click', '.add-booking-product', function(e) {
        e.preventDefault();
        var table = $(this).closest('table');
        $(this).closest('table').find('.fit-add-product').first().clone().removeClass('hidden').appendTo(table.find('tbody'));
        return false;
    });

    $('body').on('change', '.fit-add-product .fit-add-product-select', function(e) {
        var id = $(this).find('option:selected').val();
        var row = $(this).closest('tr');
        var editor = row.closest('.booking-editor');

        if (id == -1) {
            row.find('.bookable-target').empty();
            bookingTotal(editor);
        } else {
            $.ajax({
                method: 'GET',
                url: absolute_url + '/products/' + id,
                data: {
                    format: 'bookable',
                    order_id: editor.attr('data-order-id')
                },
                dataType: 'HTML',

                success: function(data) {
                    data = $(data);
                    row.find('.bookable-target').empty().append(data);
                    utils.j().initElements(data);
                    bookingTotal(editor);
                }
            });
        }
    });

    $('body').on('click', '.preload-quantities', function(e) {
        e.preventDefault();

        var editor = $(this).closest('form').find('.booking-editor').each(function() {
            $(this).find('tbody .booking-product').each(function() {
                var booked = $(this).find('input:hidden[name=booking-product-real-booked]');
                if (booked.length != 0) {
                    var input = $(this).find('.booking-product-quantity input');
                    input.val(booked.val());
                }
            });
        });

        /*
            Se mi trovo in un ordine aggregato, eseguo la funzione di controllo
            e calcolo solo sul primo. Tanto comunque bookingTotal() riesegue
            sempre sull'intero form dell'aggregato
        */
        bookingTotal($(this).closest('form').find('.booking-editor').first());

        return false;
    });

    $('body').on('click', '.load-other-booking', function(e) {
        e.preventDefault();
        var url = $(this).attr('data-booking-url');

        var fill_target = $(this).closest('.other-booking');
	    fill_target.empty().append(utils.loadingPlaceholder());

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'HTML',
            success: function(data) {
                data = $(data);
                fill_target.empty().append(data);
                utils.j().initElements(data);
            }
        });
    });

    /*
        Multi-GAS
    */

    $('body').on('change', '.multigas-editor input:checkbox[data-gas]', function(e) {
        var check = $(this);
        check.removeClass('saved-checkbox');

        var url = '';
        if (check.is(':checked') == true) {
            url = absolute_url + '/multigas/attach';
        }
        else {
            url = absolute_url + '/multigas/detach';
        }

        var data = {};
        data.gas = check.attr('data-gas');
        data.target_id = check.attr('data-target-id');
        data.target_type = check.attr('data-target-type');

        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            success: function() {
                check.addClass('saved-checkbox');
            }
        });
    });

    /*
        Pulsante "Salva Informazioni" in pannello consegna
    */
    $('body').on('click', '.booking-form .info-button', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        form.find('input:hidden[name=action]').val('saved');
        form.submit();
    });

    $('body').on('click', '.booking-form .saving-button', function(e) {
        if ($(this).closest('.booking-form').find('input:hidden[name=action]').val() == 'shipped') {
            if (typeof $(this).data('total-checked') === 'undefined') {
                e.stopPropagation();
                var test = false;

                $(this).closest('form').find('.booking-total').each(function() {
                    var total = utils.parseFloatC($(this).text());
                    test = (test || (total != 0));
                });

                if (test == false) {
                    test = confirm(_('Tutte le quantità consegnate sono a zero! Vuoi davvero procedere?'));
				}

                if (test == true) {
                    $(this).data('total-checked', 1);
                    $(this).click();
                }
            }
        }
    });

    $('body').on('click', '.inline-calculator button[type=submit]', function(e) {
        e.preventDefault();
        var modal = $(this).closest('.modal');
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

    $('body').on('click', '.delete-booking', function(e) {
        e.preventDefault();

        var form = $(this).closest('.inner-form');

        if (confirm(_('Sei sicuro di voler annullare questa prenotazione?'))) {
            form.find('button').prop('disabled', true);

            $.ajax({
                method: 'DELETE',
                url: form.attr('action'),
                dataType: 'json',

                success: function(data) {
                    form.find('button').prop('disabled', false);
                    form.find('.booking-product-quantity input').val('0');
                    form.find('.variants-selector').each(function() {
                        while ($(this).find('.row:not(.master-variant-selector)').length != 1) {
                            $(this).find('.row:not(.master-variant-selector):last').remove();
                        }
                    });

                    bookingTotal(form.find('.booking-editor'));
                }
            });
        }

        return false;
    });

    /*
        Contabilità
    */

    $('body').on('change', '.orders-in-invoice-candidate input:checkbox', function() {
        var table = $(this).closest('table');
        var total_taxable = 0;
        var total_tax = 0;
        var grand_total = 0;

        table.find('.orders-in-invoice-candidate').each(function() {
            if ($(this).find('input:checkbox').prop('checked')) {
                total_taxable += utils.parseFloatC($(this).find('.taxable label').text());
                total_tax += utils.parseFloatC($(this).find('.tax label').text());
                grand_total += utils.parseFloatC($(this).find('.total label').text());
            }
        });

        var totals_row = table.find('.orders-in-invoice-total');
        totals_row.find('.taxable label').text(utils.priceRound(total_taxable));
        totals_row.find('.tax label').text(utils.priceRound(total_tax));
        totals_row.find('.total label').text(utils.priceRound(grand_total));
    });

    $('body').on('change', '.csv_movement_type_select', function() {
        enforcePaymentMethod($(this));
    });

    /*
        Notifiche
    */

    $('body').on('change', '.notification-type-switch input', function() {
        if ($(this).prop('checked') == false) {
            return;
        }

        var form = $(this).closest('form');
        form.find('[name^=users]').closest('.row').toggle();
        form.find('[name=end_date]').closest('.row').toggle();
        form.find('[name=mailed]').closest('.row').toggle();
		form.find('[name=file]').closest('.row').toggle();
    });

    /*
        Widget generico wizard
    */

    $('body').on('show.bs.modal', '.modal.wizard', function(e) {
        $(this).find('.wizard_page:not(:first)').hide();
        $(this).find('.wizard_page:first').show();

    }).on('submit', '.wizard_page form', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var form = $(this);
        var data = form.serializeArray();
        form.find('button[type=submit]').prop('disabled', true);

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                wizardLoadPage(form, data);
            }
        });

        return false;
    });

    $('body').on('submit', '.modal.close-on-submit form', function(event) {
        $(this).closest('.modal').modal('hide');
    });
});
