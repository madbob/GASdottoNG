/*******************************************************************************
	Varie ed eventuali
*/

import './boot';

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
    reloadMeasureDiscrete: function() {
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
        let i = $(this);
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
        }).on('change', function() {
            enforceMeasureDiscrete($(this));
        });
    }

    let mselectors = $('.measure-selector', container);
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
        let json = JSON.parse(contents);
        let modal = $('#service-modal');
        modal.find('.modal-body').empty().append('<p>' + json.message + '</p>');
        modal.modal('show');
    }
}

function completionRowsInit(node) {
    $(node).find('input:text').each(function() {
        if ($(this).hasClass('ui-autocomplete-input') == true) {
            return;
        }

        let source = $(this).closest('.completion-rows').attr('data-completion-source');

        $(this).autocomplete({
            source: source,
            appendTo: $(this).closest('.completion-rows'),
            select: function(event, ui) {
                let row = $(this).closest('li');
                row.before('<li class="list-group-item" data-object-id="' + ui.item.id + '">' + ui.item.label + '<div class="btn btn-xs btn-danger float-end"><i class="bi-x-lg"></i></div></li>');

                let container = row.closest('.completion-rows');
                let fn = Callables[container.attr('data-callback-add')];
                if (typeof fn === 'function') {
                    fn(container, ui.item.id);
                }
            }
        });
    });

    $(node).on('click', '.btn-danger', function() {
        let row = $(this).closest('li');
        let container = row.closest('.completion-rows');
        let fn = Callables[container.attr('data-callback-remove')];
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
        let selectname = test.val();
        $('select[name=' + selectname + ']').each(function() {
            let o = $('<option value="' + data.id + '" selected="selected">' + data.name + '</option>');
            if (data.hasOwnProperty('parent') && data.parent != null) {
                let parent = $(this).find('option[value=' + data.parent + ']').first();
                let pname = parent.text().replace(/&nbsp;/g, ' ');
                let indent = '&nbsp;&nbsp;';

                for (let i = 0; i < pname.length; i++) {
                    if (pname[i] == ' ') {
                        indent += '&nbsp;';
                    }
                    else {
                        break;
                    }
                }

                o.prepend(indent);
                parent.after(o);
            } else {
                let reserved = ['id', 'name', 'status'];
                for (let property in data) {
                    if (data.hasOwnProperty(property) && reserved.indexOf(property) < 0) {
                        o.attr('data-' + property, data[property]);
                    }
                }

                let trigger = $(this).find('option[value=run_modal]');
                if (trigger.length != 0) {
                    trigger.before(o);
                }
                else {
                    $(this).append(0);
                }
            }
        });
    }

    test = form.find('input[name=update-field]');
    if (test.length != 0) {
        test.each(function() {
            let identifier_holder = utils.sanitizeId($(this).val());
            let node = $('[data-updatable-name=' + identifier_holder + ']');

            let field = node.attr('data-updatable-field');
            if (field == null) {
                field = identifier_holder;
            }

            let value = data[field];

            if (node.is('input:hidden')) {
                node.val(value);
            }
            else {
                node.html(value);
            }
        });
    }

    test = form.find('input[name=post-saved-refetch]');
    if (test.length != 0) {
        test.each(function() {
            let target = utils.sanitizeId($(this).val());
            let box = $(target);

            let url = box.attr('data-fetch-url');
            if (url == null) {
                url = $(this).attr('data-fetch-url');
            }

            utils.j().fetchNode(url, box);
        });
    }

    test = form.find('input[name^=post-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            let function_name = $(this).val();

            let fn = localCallables[function_name] || Callables[function_name];
            if (typeof fn === 'function') {
                fn(form, data);
            }
        });
    }

    test = form.find('input[name^=reload-portion]');
    if (test.length != 0) {
        test.each(function() {
            let identifier = $(this).val();
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
			let id = $(this).val();

			let modal = $(id);
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
    let proceed = true;

    let test = form.find('input[name^=pre-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            let fn = Callables[$(this).val()];
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
    let test = modal.find('input[name=reload-portion]');
    if (test.length != 0) {
        test.each(function() {
            let identifier = $(this).val();
            utils.j().reloadNode($(identifier));
        });
    }
}

/*******************************************************************************
	Prodotti
*/

function enforceMeasureDiscrete(node) {
    let form = node.closest('form');
    let selected = node.find('option:selected').val();
    let discrete = measure_discrete[selected];
    let disabled = (discrete == '1');

    form.find('input[name=portion_quantity]').prop('disabled', disabled);
	let multiple_widget = form.find('input[name=multiple]');
    let widgets = form.find('input[name=min_quantity], input[name=max_quantity], input[name=max_available]');

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
	let navbar = $('.navbar').first();
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
            let id = location.hash;
            if (id.charAt(0) === '#')
                id = id.substr(1);
            $('.accordion-item[data-element-id=' + id + ']').find('.accordion-button').first().click();
        }, 100);
    }

    $('#prompt-message-modal').modal('show');

    $('#home-notifications .alert').on('closed.bs.alert', function() {
        let id = $(this).find('input:hidden[name=notification_id]').val();
        $.post('notifications/markread/' + id);

        if ($('#home-notifications .alert').length == 0) {
            $('#home-notifications').hide('fadeout');
        }
    });

	$('.remember-checkbox').each(function() {
		let attr = $(this).attr('data-attribute');
		let value = Cookies.get(attr);

		if (typeof value !== 'undefined') {
			$(this).prop('checked', value == 'true');
		}
		else {
			$(this).prop('checked', $(this).attr('data-attribute-default') == 'true');
		}
	}).change(function() {
		let attr = $(this).attr('data-attribute');
		let value = $(this).prop('checked') ? 'true' : 'false';
		Cookies.set(attr, value);
	});

    $('body').on('submit', '.main-form', function(event) {
        event.preventDefault();

        let form = $(this);

        let proceed = preSaveForm(form);
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
        let form = $(this).closest('.main-form');

        if (confirm(_('Sei sicuro di voler eliminare questo elemento?'))) {
            form.find('button').prop('disabled', true);

            $.ajax({
                method: 'DELETE',
                url: form.attr('action'),
                dataType: 'json',

                success: function() {
                    let upper = Lists.closeParent(form);
                    let list = upper.closest('.loadable-list');
                    upper.remove();
                    Lists.testListsEmptiness(list);
                }
            });
        }
    });

    $('body').on('submit', '.inner-form', function(event) {
        event.preventDefault();
        let form = $(this);

        let proceed = preSaveForm(form);
        if (proceed == false) {
            return;
        }

		utils.spinSubmitButton(form);

        let params = {
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

            error: function() {
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

    $('body').on('hide.bs.modal', '.inner-modal', function() {
        miscInnerModalCallbacks($(this));
    });

    $('body').on('change', '.auto-submit select', function() {
        let form = $(this).closest('form');

        let data = new FormData(form.get(0));
        let method = form.attr('method').toUpperCase();
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

    $('body').on('click', '.variants-editor .delete-variant', function(e) {
        $(e.currentTarget).prop('disabled', true);
        let editor = $(this).closest('.variants-editor');
        let id = $(this).closest('[data-variant-id]').attr('data-variant-id');

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

        let button = $(this);
        button.prop('disabled', true);
        let id = button.attr('data-aggregate-id');
        let date = button.closest('form').find('.last-date');

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
        let table = $(this).closest('table');
        let total_taxable = 0;
        let total_tax = 0;
        let grand_total = 0;

        table.find('.orders-in-invoice-candidate').each(function() {
            if ($(this).find('input:checkbox').prop('checked')) {
                total_taxable += utils.parseFloatC($(this).find('.taxable').text());
                total_tax += utils.parseFloatC($(this).find('.tax').text());
                grand_total += utils.parseFloatC($(this).find('.total').text());
            }
        });

        let totals_row = table.find('.orders-in-invoice-total');
        totals_row.find('.taxable').text(utils.priceRound(total_taxable));
        totals_row.find('.tax').text(utils.priceRound(total_tax));
        totals_row.find('.total').text(utils.priceRound(grand_total));
    });

    /*
        Notifiche
    */

    $('body').on('change', '.notification-type-switch input', function() {
        if ($(this).prop('checked') == false) {
            return;
        }

        let form = $(this).closest('form');
        form.find('[name^=users]').closest('.row').toggle();
        form.find('[name=end_date]').closest('.row').toggle();
        form.find('[name=mailed]').closest('.row').toggle();
		form.find('[name=file]').closest('.row').toggle();
    });

    /*
        Widget generico wizard
    */

    $('body').on('show.bs.modal', '.modal.wizard', function() {
        $(this).find('.wizard_page:not(:first)').hide();
        $(this).find('.wizard_page:first').show();

    }).on('submit', '.wizard_page form', function(e) {
        e.preventDefault();
        e.stopPropagation();

        let form = $(this);
        let data = form.serializeArray();

		utils.spinSubmitButton(form);

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                wizardLoadPage(form, data);
            },

			error: function() {
                utils.formErrorFeedback(form);
            }
        });

        return false;
    });

    $('body').on('submit', '.modal.close-on-submit form', function() {
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
