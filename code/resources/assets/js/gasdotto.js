/*******************************************************************************
	Varie ed eventuali
*/

window.$ = window.jQuery = global.$ = global.jQuery = require('jquery');
require('bootstrap');

require('jquery-ui/ui/widgets/draggable');
require('jquery-ui/ui/widgets/droppable');
require('jquery-ui/ui/widgets/autocomplete');
require('jquery-ui-touch-punch');
require('blueimp-file-upload');
import Cookies from 'js-cookie';
import { TourGuideClient } from "@sjmc11/tourguidejs";

require('./jquery.dynamictree');
require('./popovers');
require('./translate');
require('./password');
import utils from "./utils";
import Lists from "./lists";
import Forms from "./forms";
import Widgets from "./widgets";
import Triggers from "./triggers";
import Filters from "./filters";
import Products from "./products";
import Bookings from "./bookings";
import Orders from "./orders";
import Roles from "./roles";
import Modifiers from "./modifiers";
import Movements from "./movements";
import Exports from "./exports";
import Callables from "./callables";
import Statistics from "./statistics";

var locker = false;
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

    $('.completion-rows', container).each(function() {
        completionRowsInit($(this));
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

    utils.init(container);
    Modifiers.init(container);
	Products.init(container);
    Forms.init(container);
    Lists.init(container);
    Widgets.init(container);
    Bookings.init(container);
    Orders.init(container);
    Triggers.init(container);
    Filters.init(container);
    Roles.init(container);
    Movements.init(container);
    Exports.init(container);
	Statistics.init(container);
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

/*
    I form possono includere una serie di campi <input type="hidden"> che, in
    funzione dell'attributo "name", possono attivare delle funzioni speciali
    dopo il submit usando il valore ritornato
*/
function miscInnerCallbacks(form, data) {
    if (locker == true) {
        return false;
	}

    let test = false;
    locker = true;

    test = form.find('input[name=test-feedback]');
    if (test.length != 0) {
        if (data.status == 'error') {
            utils.displayServerError(form, data);
			locker = false;
            return false;
        }
    }

    Lists.innerCallbacks(form, data);

    test = form.find('input[name=update-select]');
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

    test = form.find('input[name=update-field]');
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

    test = form.find('input[name=post-saved-refetch]');
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

    test = form.find('input[name^=post-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            var function_name = $(this).val();

            var fn = localCallables[function_name] || Callables[function_name];
            if (typeof fn === 'function') {
                fn(form, data);
            }
        });
    }

    test = form.find('input[name^=reload-portion]');
    if (test.length != 0) {
        test.each(function() {
            var identifier = $(this).val();
            $(identifier).each(function() {
            	utils.j().reloadNode($(this));
            });
        });
    }

    test = form.find('input[name=void-form]');
    if (test.length != 0) {
        test.each(function() {
			form.find('input[type!=hidden]').val('');
		    form.find('textarea').val('');
		    form.find('select option:first').prop('selected', true);
		    form.find('.error-message').remove();
        });
    }

    test = form.find('input[name=close-modal]');
    if (test.length != 0) {
		test.each(function() {
			var id = $(this).val();

			var modal = $(id);
			if (modal.length == 0) {
	        	modal = form.parents('.modal');
			}

	        if (modal.length != 0) {
	            modal.modal('hide');
			}
		});
    }

    test = form.find('input[name=close-all-modal]');
    if (test.length != 0) {
        $('.modal.fade.show').modal('hide');
    }

    test = form.find('input[name=reload-whole-page]');
    if (test.length != 0) {
        location.reload();
    }

    locker = false;
	return true;
}

function preSaveForm(form) {
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

    return proceed;
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

/*******************************************************************************
	Prodotti
*/

function enforceMeasureDiscrete(node) {
    var form = node.closest('form');
    var selected = node.find('option:selected').val();
    var discrete = measure_discrete[selected];
    var disabled = (discrete == '1');

    form.find('input[name=portion_quantity]').prop('disabled', disabled);
	var multiple_widget = form.find('input[name=multiple]');
    var widgets = form.find('input[name=min_quantity], input[name=max_quantity], input[name=max_available]');

	if (disabled) {
		form.find('input[name=portion_quantity]').val('0.000');
        node.siblings('.form-text').removeClass('d-none');

		multiple_widget.attr('data-enforce-minimum', 1);

		multiple_widget.val(parseInt(multiple_widget.val()));
		if (multiple_widget.val() < 1) {
			multiple_widget.val('1.000');
        }

        widgets.attr('data-enforce-integer', 1);
	}
	else {
		let weight = form.find('input[name=weight]');
		if (parseFloat(weight.val()) == 0) {
			weight.val('1');
		}

        node.siblings('.form-text').addClass('d-none');
		multiple_widget.removeAttr('data-enforce-minimum');
        widgets.removeAttr('data-enforce-integer');
	}
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

    $('#preloader').hide();

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

    $('body').on('submit', '.main-form', function(event) {
        event.preventDefault();

        var form = $(this);

        var proceed = preSaveForm(form);
        if (proceed == false) {
            return;
        }

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

    $('body').on('submit', '.inner-form', function(event) {
        event.preventDefault();
        var form = $(this);

        var proceed = preSaveForm(form);
        if (proceed == false) {
            return;
        }

		utils.spinSubmitButton(form);

        var params = {
            method: form.attr('method'),
            url: form.attr('action'),
            dataType: 'JSON',

            success: function(data) {
				if (miscInnerCallbacks(form, data) == true) {
	                utils.j().submitButton(form).each(function() {
	                    utils.inlineFeedback($(this), _('Salvato!'));
	                });
				}
				else {
					utils.formErrorFeedback(form);
				}
            },

            error: function(data) {
                utils.formErrorFeedback(form);
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

    /*
        Gestione fornitori
    */

    $('body').on('click', '.variants-editor .delete-variant', function() {
        var editor = $(this).closest('.variants-editor');
        var id = $(this).closest('tr').attr('data-variant-id');

        utils.postAjax({
            method: 'DELETE',
            url: 'variants/' + id,
            success: function() {
                utils.j().reloadNode(editor);
            }
        });
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

        utils.postAjax({
            url: 'aggregates/notify/' + id,
            success: function(data) {
                date.text(data);
                button.prop('disabled', false);
            },
            error: function() {
                button.prop('disabled', false);
            }
        });
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

		utils.spinSubmitButton(form);

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                wizardLoadPage(form, data);
            },

			error: function(data) {
                utils.formErrorFeedback(form);
            }
        });

        return false;
    });

    $('body').on('submit', '.modal.close-on-submit form', function(event) {
        $(this).closest('.modal').modal('hide');
    });

    Bookings.initOnce();
    Orders.initOnce();

	/*
		Onboarding
	*/

	let needs_tour = $('meta[name=needs_tour]').attr('content');
	if (needs_tour == '1') {
		utils.postAjax({
			method: 'GET',
			url: 'users/tour/start',
			dataType: 'JSON',
			success: (data) => {
				/*
					Quando si registra una nuova istanza, viene spesso mostrata
					la raccomandazione di modificare la propria password (di
					default, username e password combaciano). Ma il modale si
					sovrappone al menu, oggetto primario del tour. Dunque qui,
					per ogni evenienza, chiudo tale modale: eventualmente
					l'avviso sarà nuovamente mostrato quando l'utente si
					autenticherà di nuovo
				*/
				$('#prompt-message-modal').modal('hide');

				/*
					Su mobile, devo esplicitamente aprire il menu affinché le
					diverse voci siano evidenziate passo passo dal tour
				*/
				if (utils.isMobile()) {
					$('.navbar-toggler').click();
				}

				const tg = new TourGuideClient(data);

				tg.onFinish(() => {
					utils.postAjax({
			            method: 'GET',
			            url: 'users/tour/finish',
			        });

					if (utils.isMobile()) {
						$('.navbar-toggler').click();
					}
				});

				tg.start();
			}
		});
	}
});
