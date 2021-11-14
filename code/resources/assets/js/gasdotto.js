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
require('./popovers');
require('./translate');
require('./password');
import utils from "./utils";
import Lists from "./lists";
import Triggers from "./triggers";
import Filters from "./filters";
import Bookings from "./bookings";
import Roles from "./roles";
import Modifiers from "./modifiers";
import Movements from "./movements";
import Callables from "./callables";

var locker = false;
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

    setupImportCsvEditor(container);

    utils.init(container);
    Modifiers.init(container);
    Lists.init(container);
    Bookings.init(container);
    Triggers.init(container);
    Filters.init(container);
    Roles.init(container);
    Movements.init(container);
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
    var widgets = form.find('input[name=min_quantity], input[name=max_quantity], input[name=max_available]');

	if (disabled) {
		form.find('input[name=portion_quantity]').val('0.000');
		form.find('input[name=variable]').prop('checked', false).prop('disabled', true);
        node.siblings('.form-text').removeClass('d-none');

		multiple_widget.attr('data-enforce-minimum', 1).attr('data-enforce-integer', 1);

		multiple_widget.val(parseInt(multiple_widget.val()));
		if (multiple_widget.val() < 1) {
			multiple_widget.val('1.000');
        }

        widgets.attr('data-enforce-integer', 1);
	}
	else {
		form.find('input[name=weight]').val('0.000');
		form.find('input[name=variable]').prop('disabled', false);
        node.siblings('.form-text').addClass('d-none');
		multiple_widget.removeAttr('data-enforce-minimum').removeAttr('data-enforce-integer');
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

    $('body').on('change', '.contacts-selection select', function() {
        var input = $(this).closest('tr').find('input[name="contact_value[]"]');
        var typeclass = $(this).find('option:selected').val();
        fixContactField(input, typeclass);
    });

    $('body').on('change', '.status-selector input:radio[name*="status"]', function() {
        let field = $(this).closest('.status-selector');
        let status = $(this).val();
        let del = (status != 'deleted');
        field.find('[name=deleted_at]').prop('hidden', del).closest('.input-group').prop('hidden', del);
        let sus = (status != 'suspended');
        field.find('[name=suspended_at]').prop('hidden', sus).closest('.input-group').prop('hidden', sus);
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
        utils.postAjax({
            url: 'dates/query',
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

    $('body').on('click', '.supplier-future-dates li', function() {
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
