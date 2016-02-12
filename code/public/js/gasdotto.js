/*******************************************************************************
	Varie ed eventuali
*/

var userBlood = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: '/users/search?term=%QUERY',
		wildcard: '%QUERY'
	}
});

userBlood.initialize();

function generalInit() {
	$('input.date').datepicker({
		format: 'DD dd MM yyyy',
		autoclose: true,
		language: 'it',
		clearBtn: true
	});

	$('.tagsinput').tagsinput();

	$('.addicted-table').bootstrapTable();

	$('.nav-tabs a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});

	$('input:file').each(function() {
		var i = $(this);
		i.fileupload({
			done: function(e, data) {
				var callback = $(e.target).attr('data-run-callback');
				if (callback != null)
					window[callback]($(e.target), data.result);
			}
		});
	});

	$('.many-rows').each(function() {
		manyRowsAddDeleteButtons($(this));
	});

	$('.bookingSearch').each(function() {
		if($(this).hasClass('tt-hint') == true) {
			return;
		}

		if($(this).hasClass('tt-input') == false) {
			$(this).typeahead(null, {
				name: 'users',
				displayKey: 'value',
				source: userBlood.ttAdapter()
			}).on('typeahead:selected', function(obj, result, name) {
				var aggregate_id = $(this).attr('data-aggregate');
				$.get('/booking/' + aggregate_id + '/user/' + result.id, function(form) {
					$('.other-booking').empty().append(form);
				});
			});
		}
	});

	$('.modal.dynamic-contents').on('show.bs.modal', function(e) {
		if (typeof $.data(e.target, 'dynamic-inited') == 'undefined') {
			$.data(e.target, 'dynamic-inited', {done: true});

			var contents = $(this).find('.modal-content');
			contents.empty();
			var url = $(this).attr('data-contents-url');

			$.get(url, function(data) {
				contents.append(data);
			});
		}
	});

	$('.dynamic-tree').jstree({
		'core': {
			'check_callback': true
		},
		'plugins': ['dnd', 'unique', 'sort']
	});

	/*
		jstree rimuove la classe esistente sulla ul di riferimento,
		qui ce la rimetto. TODO: correggere jstree
	*/
	$('.dynamic-tree ul').addClass('list-group');

	setupVariantsEditor();
	setupImportCsvEditor();
	testListsEmptiness();
}

function filteredSerialize(form) {
	return $(':not(.skip-on-submit)', form).serializeArray();
}

function parseFloatC(value) {
	return parseFloat(value.replace(/,/, '.'));
}

function priceRound(price) {
	return (Math.round(price * 100) / 100).toFixed(2);
}

function voidForm(form) {
	form.find('input[type!=hidden]').val('');
	form.find('textarea').val('');
}

function sortList(mylist) {
	var listitems = mylist.children('a').get();
	listitems.sort(function(a, b) {
		return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
	});

	$.each(listitems, function(idx, itm) {
		mylist.append(itm);
	});
}

function closeMainForm(form) {
	var container = form.closest('.list-group-item');
	var head = container.prev();
	head.removeClass('active');
	container.remove();
	return head;
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

function wizardLoadPage(node, contents) {
	var page = node.closest('.wizard_page');
	var parent = page.parent();
	var next = $(contents);
	parent.append(next);
	page.hide();
	next.show();
}

function manyRowsAddDeleteButtons(node) {
	var fields = node.find('.row');
	if (fields.length > 1 && node.find('.delete-many-rows').length == 0) {
		fields.each(function() {
			var button = '<div class="btn btn-danger delete-many-rows"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>';
			$(this).append(button);
		});
	}
	else if (fields.length == 1) {
		node.find('.delete-many-rows').remove();
	}
}

function testListsEmptiness() {
	$('.loadablelist').each(function() {
		var id = $(this).attr('id');
		var c = $(this).find('a').length;
		var alert = $('#empty-' + id);

		if (c == 0)
			alert.show();
		else
			alert.hide();
	});
}

function loadingPlaceholder() {
	return $('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
}

function setupImportCsvEditor() {
	$('#import_csv_sorter .im_draggable').each(function() {
		$(this).draggable({
			helper: 'clone',
			revert: 'invalid'
		});
	});

	$('#import_csv_sorter .im_droppable').droppable({
		drop: function(event, ui) {
			var node = ui.draggable.clone();
			node.find('input:hidden').attr('name', 'column[]');
			$(this).find('.column_content').empty().append(node.contents());
		}
	});
}

function parseDynamicTree(unparsed_data) {
	var data = [];

	for (var i = 0; i < unparsed_data.length; i++) {
		var unparsed_node = unparsed_data[i];
		/*
			Per avere il contenuto testuale del nodo devo rimuovere
			l'HTML del pulsante di rimozione della riga
		*/
		var node = {id: unparsed_node.id, name: unparsed_node.text.replace(/<[^>]*>?/g, '')};
		node.children = parseDynamicTree(unparsed_node.children);
		data.push(node);
	}

	return data;
}

/*******************************************************************************
	Prenotazioni / Consegne
*/

function bookingTotal(editor) {
	var total_price = 0;

	editor.find('.booking-product').each(function() {
		if ($(this).hasClass('hidden'))
			return true;

		var price = $(this).find('input:hidden[name=product-price]').val();
		price = parseFloatC(price);

		var partitioning = $(this).find('input:hidden[name=product-partitioning]').val();
		partitioning = parseFloatC(partitioning);

		var quantity = 0;

		$(this).find('.booking-product-quantity input').each(function() {
			var q = $(this).val();

			if (q == '')
				q = 0;
			else
				q = parseFloatC(q);

			if (partitioning != 0)
				q = q * partitioning;

			quantity += q;
		});

		total_price += price * quantity;
	});

	var total_label = editor.find('.booking-total');
	total_label.text(priceRound(total_price));
}

function setupVariantsEditor() {
	$('.variants-editor').on('click', '.delete-variant', function() {
		var editor = $(this).closest('.variants-editor');
		var id = $(this).closest('.row').find('input:hidden[name=variant_id]').val();

		$.ajax({
			method: 'DELETE',
			url: '/variants/' + id,
			dataType: 'html',

			success: function(data) {
				editor.replaceWith(data);
			}
		});

	}).on('click', '.edit-variant', function() {
		var row = $(this).closest('.row');
		var id = row.find('input:hidden[name=variant_id]').val();
		var name = row.find('.variant_name').text().trim();
		var offset = row.find('input:hidden[name=variant_offset]').val();
		var values = row.find('.exploded_values').contents().clone();

		var form = $(this).closest('.list-group').find('.creating-variant-form');
		form.find('input:hidden[name=variant_id]').val(id);
		form.find('input[name=name]').val(name);
		form.find('.values_table').empty().append(values);

		if (offset == 'true') {
			form.find('input[name=has_offset]').attr('checked', 'checked');
			form.find('input[name*=price_offset]').closest('.form-group').show();
		}
		else {
			form.find('input[name=has_offset]').removeAttr('checked');
			form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
		}

		form.closest('.modal').modal('show');

	}).on('click', '.add-variant', function() {
		var row = $(this).closest('.list-group');
		var form = row.find('.creating-variant-form');
		var modal = row.find('.create-variant');
		form.find('input:text').val('');
		form.find('input:hidden[name=variant_id]').val('');
		form.find('input:checkbox').removeAttr('checked');

		values = form.find('.many-rows');
		values.find('.row:not(:first)').remove();
		manyRowsAddDeleteButtons(values);

		form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
		modal.modal('show');
	});

	$('.creating-variant-form').on('change', 'input:checkbox[name=has_offset]', function() {
		var has = $(this).is(':checked');
		var form = $(this).closest('form');

		if (has == true)
			form.find('input[name*=price_offset]').closest('.form-group').show();
		else
			form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();

	}).submit(function(e) {
		e.preventDefault();
		var modal = $(this).closest('.modal');
		var editor = $(this).closest('.list-group').find('.variants-editor');
		var data = $(this).serializeArray();

		editor.empty().append(loadingPlaceholder());

		$.ajax({
			method: 'POST',
			url: '/variants',
			data: data,
			dataType: 'html',

			success: function(data) {
				editor.replaceWith(data);
				modal.modal('hide');
			}
		});

		return false;
	});
}

/*******************************************************************************
	Permessi
*/

function getPermissionsEditor(node) {
	var ret = {valid: true};
	ret.editor = node.closest('.permissions-editor');
	ret.users = ret.editor.find('select[name=user]');

	var subject = ret.editor.find('select[name=subject] option:selected');
	if (subject.length != 1)
		subject = ret.editor.find('input:hidden[name=subject]');

	ret.subject = subject.val();
	if (subject.length != 1)
		ret.valid = false;

	var rule = ret.editor.find('select[name=rule]:not(.hidden) option:selected');
	if (rule.length != 1)
		rule = ret.editor.find('input:hidden[name=rule]');

	ret.rule = rule.val();
	if (rule.length != 1)
		ret.valid = false;

	ret.behaviour = ret.editor.find('input:radio[name=behaviour]:checked').val();

	return ret;
}

function loadPermissions(editor) {
	if (editor.valid == false) {
		editor.users.empty().append('<option disabled>Seleziona una regola</option>');
	}
	else {
		editor.users.empty().append('<option disabled>Caricamento...</option>');

		$.ajax('/permissions/read', {
			method: 'GET',
			data: {
				subject_id: editor.subject,
				rule_id: editor.rule
			},
			dataType: 'json',

			success: function(data) {
				var u;
				editor.users.empty();

				for(var i = 0; i < data.users.length; i++) {
					u = data.users[i];
					editor.users.append('<option value="' + u.id + '">' + u.name + '</option>');
				}

				editor.editor.find('input:radio[name=behaviour][value=' + data.behaviour + ']').prop('checked', true);
			}
		});
	}
}

function setupPermissionsEditor() {
	$('.permissions-editor').on('change', 'select[name=subject]', function() {
		var sel = $(this).find('option:selected');
		var c = sel.attr('data-permissions-class');
		if (sel.length != 1)
			c = 'none';

		$('.permissions-editor').find('select[name=rule]').each(function() {
			var cl = $(this).attr('data-permissions-class');
			if (cl == c)
				$(this).removeClass('hidden').find('option:selected').click();
			else
				$(this).addClass('hidden');
		});
	}).on('click', 'select[name=rule] option', function() {
		/*
			Questa funzione viene attivata su option.click e non su
			select.change perché quest'ultimo evento non viene
			lanciato se ho ri-selezionato una option già selezionata
			(come nel caso in cui cambio soggetto nell'elenco dei
			soggetti, nella funzione sopra)
		*/

		var editor = getPermissionsEditor($(this));
		loadPermissions(editor);

	}).on('click', '.remove-auth', function() {
		var editor = getPermissionsEditor($(this));

		if (editor.valid == true) {
			editor.users.find('option:selected').each(function() {
				var user = $(this).val();

				$.ajax('/permissions/remove', {
					method: 'POST',
					data: {
						user_id: user,
						subject_id: editor.subject,
						rule_id: editor.rule,
						behaviour: editor.behaviour
					}
				});

				$(this).remove();
			});
		}
	}).on('change', 'input:radio[name=behaviour]', function() {
		var editor = getPermissionsEditor($(this));

		if (editor.valid == true) {
			$.ajax('/permissions/change', {
				method: 'POST',
				data: {
					subject_id: editor.subject,
					rule_id: editor.rule,
					behaviour: $(this).val()
				}
			});
		}
	});

	$('.permissions-editor input:text[name=adduser]').typeahead(null, {
		name: 'users',
		displayKey: 'value',
		source: userBlood.ttAdapter()
	}).on('typeahead:selected', function(obj, result, name) {
		var editor = getPermissionsEditor($(this));
		$(this).val('');

		if (editor.valid == true) {
			$.ajax('/permissions/add', {
				method: 'POST',
				data: {
					user_id: result.id,
					subject_id: editor.subject,
					rule_id: editor.rule,
					behaviour: editor.behaviour
				},
				success: function() {
					editor.users.find('option:disabled').remove();
					editor.users.append('<option value="' + result.id + '">' + result.label + '</option>');
				}
			});
		}
	});

	$('#editPermissions').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget);
		var subject = button.data('subject');
		var rule = button.data('rule');
		var modal = $(this);

		modal.find('.modal-body input:hidden[name=subject]').val(subject);
		modal.find('.modal-body input:hidden[name=rule]').val(rule);

		var editor = getPermissionsEditor(modal.find('.permissions-editor'));
		loadPermissions(editor);
	});
}

/*******************************************************************************
	Help
*/

function helpFillNode(nodes, text) {
	if (nodes != null) {
		nodes.parent().addClass('help-sensitive').popover({
			content: text,
			placement: 'auto right',
			container: 'body',
			html: true,
			trigger: 'hover'
		});
	}

	return '';
}

function setupHelp() {
	$('body').on('click', '#help-trigger', function(e) {
		e.preventDefault();

		if ($(this).hasClass('active')) {
			$('.help-sensitive').removeClass('help-sensitive').popover('destroy');
		}
		else {
			$.get('/help/data.md', function(data) {
				var renderer = new marked.Renderer();
				var container = null;
				var nodes = null;
				var inner_text = '';

				/*
					Qui abuso del renderer Markdown per
					filtrare i contenuti del file ed
					assegnarli ai vari elementi sulla pagina
				*/

				renderer.heading = function (text, level) {
					inner_text = helpFillNode(nodes, inner_text);

					if (level == 2)
						container = $(text);
					else if (level == 1)
						nodes = container.find(':contains(' + text + ')').last();
				};
				renderer.paragraph = function (text, level) {
					if (inner_text != '')
						inner_text += '<br/>';
					inner_text += text;
				};
				renderer.list = function (text, level) {
					inner_text += '<ul>' + text + '</ul>';
				};

				marked(data, {renderer: renderer}, function() {
					inner_text = helpFillNode(nodes, inner_text);
				});
			});
		}

		$(this).toggleClass('active');
		return false;
	});
}

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$(document).ajaxComplete(function() {
		generalInit();
	});

	$('#bottom-stop').offset({left: 0, top: $(document).height() - 1});
	$(document).scroll(function() {
		var h = $(document).height();
		var b = $('#bottom-stop').offset();
		if (h < b.top)
			$(document).height(b.top);
		else
			$('#bottom-stop').offset({left: 0, top: $(document).height() - 1});
	});

	generalInit();

	$('#home-notifications .alert').on('closed.bs.alert', function() {
		var id = $(this).find('input:hidden[name=notification_id]').val();
		$.post('notifications/markread/' + id);

		if ($('#home-notifications .alert').length == 0)
			$('#home-notifications').hide('fadeout');
	});

	$('body').on('click', '.loadablelist a.loadable-item', function(event) {
		event.preventDefault();

		if ($(this).hasClass('active')) {
			$(this).removeClass('active').next().remove();
		}
		else {
			$(this).find('a').removeClass('active');
			var node = $('<li>').addClass('list-group-item').append(loadingPlaceholder());
			$(this).addClass('active').after(node);

			$.ajax({
				method: 'GET',
				url: $(this).attr('href'),

				success: function(data) {
					node.empty().append(data);
				},
				error: function() {
					node.empty().append();
				}
			});
		}

		return false;
	});

	$('body').on('click', '.reloader', function(event) {
		var listid = $(this).attr('data-reload-target');
		var list = $(listid);

		/*
			Per qualche motivo, se .reloader è anche il tasto di
			chiusura di un modale, il modale viene nascosto ma non
			definitivamente chiuso. Introducendo questo delay sembra
			funzionare, ma non so perché
		*/
		setTimeout (function() {
			var activated = list.find('a.loadable-item.active');
			activated.each(function() {
				$(this).click().click();
			});
		}, 200);
	});

	$('body').on('change', '.select-fetcher', function(event) {
		var url = $(this).find('option:selected').val();
		var targetid = $(this).attr('data-fetcher-target');
		var target = $(this).parent().find('.' + targetid);
		target.empty();

		if (url != 'none') {
			target.append(loadingPlaceholder());
			$.get(url, function(data) {
				target.empty().append(data);
			});
		}
	});

	$('body').on('change', 'select.triggers-modal', function(event) {
		var val = $(this).find('option:selected').val();
		if (val == 'run_modal') {
			var modal = $(this).attr('data-trigger-modal');
			$('#' + modal).modal('show');
		}
	});

	$('body').on('submit', '.main-form', function(event) {
		event.preventDefault();
		var form = $(this);
		var data = form.serializeArray();

		form.find('.main-form-buttons button').attr('disabled', 'disabled');

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				var h = closeMainForm(form);
				h.empty().append(data.header).attr('href', data.url);
			}
		});
	});

	$('body').on('click', '.main-form-buttons .close-button', function(event) {
		event.preventDefault();
		var form = $(this).closest('.main-form');
		form.find('.main-form-buttons button').attr('disabled', 'disabled');
		closeMainForm(form);
	});

	$('body').on('click', '.main-form-buttons .delete-button', function(event) {
		event.preventDefault();
		var form = $(this).closest('.main-form');

		/*
			TODO: visualizzare nome dell'elemento che si sta rimuovendo
		*/

		if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
			form.find('.main-form-buttons button').attr('disabled', 'disabled');

			$.ajax({
				method: 'DELETE',
				url: form.attr('action'),
				dataType: 'json',

				success: function(data) {
					var upper = closeMainForm(form);
					upper.remove();
					testListsEmptiness();
				}
			});
		}
	});

	$('body').on('submit', '.inner-form', function(event) {
		event.preventDefault();
		var form = $(this);
		var data = filteredSerialize(form);

		form.find('button[type=submit]').text('Attendere').attr('disabled', 'disabled');;

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				form.find('button[type=submit]').text('Salvato!');
				setInterval(function() {
					form.find('button[type=submit]').text('Salva').removeAttr('disabled');
				}, 2000);
			}
		});
	});

	$('body').on('submit', '.creating-form', function(event) {
		if (event.isDefaultPrevented())
			return;

		event.preventDefault();
		var form = $(this);
		var data = form.serializeArray();

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				voidForm(form);

				var modal = form.parents('.modal');
				if(modal.length != 0)
					modal.modal('hide');

				var test = form.find('input[name=update-list]');
				if (test.length != 0) {
					var listname = test.val();
					var list = $('#' + listname);
					list.append('<a href="' + data.url + '" class="loadable-item list-group-item">' + data.header + '</a>');
					sortList(list);
					testListsEmptiness();
				}

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
						}
						else {
							var trigger = $(this).find('option[value=run_modal]');
							if (trigger.length != 0)
								trigger.before(o);
							else
								$(this).append(0);
						}
					});
				}
			}
		});
	});

	/*
		Interazioni dinamiche sul pannello prenotazioni
	*/

	$('body').on('shown.bs.tab', '.aggregate-bookings a[data-toggle="tab"]', function(e) {
		var t = e.target.hash;
		var tab = $(t);

		if (tab.hasClass('shippable-bookings')) {
			var id = tab.closest('.aggregate-bookings').find('input:hidden[name=aggregate_id]').val();
			tab.empty().append(loadingPlaceholder());

			$.ajax({
				method: 'GET',
				url: '/booking/' + id + '/user',
				dataType: 'html',

				success: function(data) {
					tab.empty().append(data);
				}
			});
		}
	});

	$('body').on('keyup', '.booking-product-quantity input', function() {
		var v = $(this).val();
		var booked;
		var wrong = false;

		if (v == '')
			booked = 0;
		else
			booked = parseFloatC(v);

		var row = $(this).closest('.booking-product');

		if (booked != 0) {
			var m = row.find('input:hidden[name=product-multiple]');
			if (m.length != 0) {
				var multiple = parseFloatC(m.val());
				if (multiple != 0 && booked % multiple != 0) {
					row.addClass('has-error');
					booked = 0;
					wrong = true;
				}
			}

			var m = row.find('input:hidden[name=product-minimum]');
			if (m.length != 0) {
				var minimum = parseFloatC(m.val());
				if (minimum != 0 && booked < minimum) {
					row.addClass('has-error');
					booked = 0;
					wrong = true;
				}
			}

			if (wrong == false)
				row.removeClass('has-error');
		}

		var editor = row.closest('.booking-editor');
		bookingTotal(editor);

	}).on('blur', '.booking-product-quantity input', function() {
		var v = $(this).val();
		var row = $(this).closest('.booking-product');
		if (v == '' || row.hasClass('has-error'))
			$(this).val('0');

	}).on('focus', '.booking-product-quantity input', function() {
		$(this).closest('.booking-product').removeClass('.has-error');

	}).on('click', '.booking-product .add-variant', function(e) {
		e.preventDefault();
		var variant_selector = $(this).closest('.variants-selector');
		var master = variant_selector.find('.master-variant-selector').clone().removeClass('master-variant-selector');
		master.find('.skip-on-submit').removeClass('skip-on-submit');
		variant_selector.append(master);
		return false;
	});

	$('body').on('click', '.add-booking-product', function(e) {
		e.preventDefault();
		var table = $(this).closest('table');
		$(this).closest('table').find('.fit-add-product').first().clone().removeClass('hidden').appendTo(table.find('tbody'));
		return false;
	});

	$('body').on('change', '.fit-add-product select', function(e) {
		var id = $(this).find('option:selected').val();
		var row = $(this).closest('tr');
		var editor = row.closest('.booking-editor');

		if (id == -1) {
			row.find('.booking-product-quantity input').val('0').attr('name', '');
			row.find('.booking-product-quantity .input-group-addon').text('?');
			bookingTotal(editor);
		}
		else {
			$.ajax({
				method: 'GET',
				url: '/products/' + id,
				data: {format: 'json'},
				dataType: 'json',

				success: function(data) {
					row.find('input:hidden[name=product-partitioning]').val(data.partitioning);
					row.find('input:hidden[name=product-price]').val(data.price);
					row.find('.booking-product-quantity input').attr('name', data.id);
					row.find('.booking-product-quantity .input-group-addon').text(data.printableMeasure);
					bookingTotal(editor);
				}
			});
		}
	});

	$('body').on('click', '.preload-quantities', function(e) {
		e.preventDefault();
		var editor = $(this).closest('form').find('.booking-editor');

		editor.find('tbody .booking-product').each(function() {
			var booked = $(this).find('.booking-product-booked');
			if (booked.length != 0) {
				var input = $(this).find('.booking-product-quantity input');
				input.val(booked.text());
			}
		});

		bookingTotal(editor);
		return false;
	});

	/*
		Widget generico multiriga
	*/

	$('body').on('click', '.delete-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).closest('.many-rows');
		$(this).closest('.row').remove();
		manyRowsAddDeleteButtons(container);
		return false;
	});

	$('body').on('click', '.add-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).closest('.many-rows');
		var row = container.find('.row').first().clone();
		row.find('input').val('');

		/*
			Questo è per forzare l'aggiornamento di eventuali campi
			tags all'interno del widget multiriga
		*/
		row.find('.bootstrap-tagsinput').remove();
		row.find('.tagsinput').tagsinput();

		container.find('.add-many-rows').before(row);
		manyRowsAddDeleteButtons(container);
		return false;
	});

	/*
		Widget albero gerarchico dinamico
	*/

	$('body').on('click', '.dynamic-tree .dynamic-tree-remove', function() {
		$(this).closest('li').remove();
	});

	$(document).on('dnd_stop.vakata', function(e) {
		$('.dynamic-tree').jstree().open_all();
	});

	$('body').on('click', '.dynamic-tree-box .dynamic-tree-add', function(e) {
		e.preventDefault();
		var box = $(this).closest('.dynamic-tree-box');
		var input = box.find('input[name=new_category]');
		var name = input.val();
		var tree = box.find('.dynamic-tree');

		tree.jstree().create_node(null, {
			text: name + '<span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove" aria-hidden="true"></span></span>',
			li_attr: {class: 'list-group-item jstree-open'}
		});
		input.val('');

		return false;
	});

	$('body').on('submit', '.dynamic-tree-box', function(e) {
		e.preventDefault();
		var box = $(this);
		var tree = box.find('.dynamic-tree');
		var unparsed_data = tree.jstree().get_json();
		var data = parseDynamicTree(unparsed_data);

		$.ajax({
			method: box.attr('method'),
			url: box.attr('action'),
			data: {serialized: data},
			dataType: 'json',

			success: function(data) {
				box.closest('.modal').modal('hide');
			}
		});

		return false;
	});

	/*
		Widget generico wizard
	*/

	$('body').on('show.bs.modal', '.modal.wizard', function(e) {
		$(this).find('.wizard_page:not(:first)').hide();
	});

	$('body').on('submit', '.wizard_page form', function(e) {
		e.preventDefault();

		var form = $(this);
		var data = form.serializeArray();

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

	setupHelp();
	setupPermissionsEditor();
});
