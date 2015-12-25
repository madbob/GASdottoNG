function generalInit() {
	$('input.date').datepicker({
		format: 'DD dd MM yyyy',
		autoclose: true,
		language: 'it',
		clearBtn: true
	});

	$('.tagsinput').tagsinput();

	$('.nav-tabs a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});

	$('.many-rows').each(function() {
		manyRowsAddDeleteButtons($(this));
	});

	testListsEmptiness();
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
	var container = form.parents('.list-group-item').first();
	var head = container.prev();
	head.removeClass('active');
	container.remove();
	return head;
}

function manyRowsAddDeleteButtons(node) {
	if (node.find('.delete-many-rows').length == 0) {
		var fields = node.find('.row');
		if (fields.length > 1) {
			fields.each(function() {
				var button = '<div class="btn btn-danger delete-many-rows"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>';
				$(this).append(button);
			});
		}
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

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$(document).ajaxComplete(function() {
		generalInit();
	});

	generalInit();

	$('body').on('click', '.loadablelist a.loadable-item', function(event) {
		event.preventDefault();

		if ($(this).hasClass('active')) {
			$(this).removeClass('active').next().remove();
		}
		else {
			$(this).find('a').removeClass('active');
			var node = $('<li>').addClass('list-group-item').append('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
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
		var form = $(this).parents('.main-form').first();
		form.find('.main-form-buttons button').attr('disabled', 'disabled');
		closeMainForm(form);
	});

	$('body').on('click', '.main-form-buttons .delete-button', function(event) {
		event.preventDefault();
		var form = $(this).parents('.main-form').first();

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
			}
		});
	});

	$('body').on('click', '.delete-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).parents('.many-rows').first();
		$(this).parents('.row').first().remove();
		if (container.find('.row').length <= 1)
			container.find('.delete-many-rows').remove();
		return false;
	});

	$('body').on('click', '.add-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).parents('.many-rows').first();
		var row = container.find('.row').first().clone();
		row.find('input').val('');

		/*
			Questo è per forzare l'aggiornamento di eventuali campi
			tags all'interno del widget multiriga (cfr. varianti in
			un prodotto)
		*/
		row.find('.bootstrap-tagsinput').remove();
		row.find('.tagsinput').tagsinput();

		container.find('.add-many-rows').before(row);
		manyRowsAddDeleteButtons(container);
		return false;
	});
});
