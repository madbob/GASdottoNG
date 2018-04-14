/*******************************************************************************
	Varie ed eventuali
*/

var locker = false;
var absolute_url = $('meta[name=absolute_url]').attr('content');
var current_currency = $('meta[name=current_currency]').attr('content');

$.fn.tagName = function() {
    return this.prop("tagName").toLowerCase();
};

var userBlood = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
        url: absolute_url + '/users/search?term=%QUERY',
        wildcard: '%QUERY'
    }
});

userBlood.initialize();

function generalInit() {
    $('input.date').datepicker({
        format: 'DD dd MM yyyy',
        autoclose: true,
        language: 'it',
        clearBtn: true,
    });

    $('input.date-to-month').datepicker({
        format: 'dd MM',
        autoclose: true,
        language: 'it',
        clearBtn: false,
        maxViewMode: 'months'
    });

    $('.addicted-table').bootstrapTable();
    $('#help-trigger').helperTrigger();

    /*
        https://stackoverflow.com/questions/15989591/how-can-i-keep-bootstrap-popover-alive-while-the-popover-is-being-hovered
    */
    $('[data-toggle="popover"]').popover({
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

    $('.contacts-selection .row').each(function() {
        var input = $(this).find('input:text');
        var typeclass = $(this).find('select option:selected').val();
        input.attr('class', '').addClass('form-control').addClass(typeclass);
    });

    function setupCheckboxes() {
        var checkboxes = $('input:checkbox[data-toggle=toggle]').slice(0, 200);
        if (checkboxes.length != 0) {
            checkboxes.bootstrapToggle().removeAttr('data-toggle');
            setTimeout(setupCheckboxes, 100);
        }
    }
    setupCheckboxes();

    $('.nav-tabs a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $('input:file.immediate-run').each(function() {
        var i = $(this);
        i.fileupload({
            done: function(e, data) {
                var callback = $(e.target).attr('data-run-callback');
                if (callback != null)
                    window[callback]($(e.target), data.result);
            }
        });
    });

    $('.many-rows').manyrows();
    $('.dynamic-tree-box').dynamictree();
    $('#orderAggregator').aggregator();

    $('.completion-rows').each(function() {
        completionRowsInit($(this));
    });

    $('.bookingSearch').each(function() {
        if ($(this).hasClass('tt-hint') == true) {
            return;
        }

        if ($(this).hasClass('tt-input') == false) {
            $(this).typeahead(null, {
                name: 'users',
                displayKey: 'value',
                source: userBlood.ttAdapter()
            }).on('typeahead:selected', function(obj, result, name) {
                var aggregate_id = $(this).attr('data-aggregate');
                var while_shipping = ($(this).closest('.modal.add-booking-while-shipping').length != 0);
                var fill_target = $(this).closest('.fillable-booking-space').find('.other-booking');
                fill_target.empty().append(loadingPlaceholder());

                var data = {};
                var mode = $(this).attr('data-enforce-booking-mode');
                if (mode != null)
                    data.enforce = mode;

                var url = '';

                if (while_shipping) {
                    url = absolute_url + '/delivery/' + aggregate_id + '/user/' + result.id;
                }
                else {
                    url = absolute_url + '/booking/' + aggregate_id + '/user/' + result.id;
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
                    }
                });
            });
        }
    });

    $('.modal').draggable({
        handle: '.modal-header'
    });

    $('.modal.dynamic-contents').on('show.bs.modal', function(e) {
        /*
            La callback viene chiamata anche quando mostro il popover di
            selezione di una data: questo è per evitare di ricaricare tutto un
            .modal.dynamic-contents che contiene una data
        */
        if ($(e.target).hasClass('date'))
            return;

        if ($(this).attr('data-dynamic-contents-callback-init') == 'true')
            return;
        $(this).attr('data-dynamic-contents-callback-init', 'true');

        var contents = $(this).find('.modal-content');
        contents.empty().append(loadingPlaceholder());
        var url = $(this).attr('data-contents-url');

        $.get(url, function(data) {
            contents.empty().append(data);
        });
    });

    $('.collapse.dynamic-contents').on('show.bs.collapse', function(e) {
        var contents = $(this);
        contents.empty().append(loadingPlaceholder());
        var url = $(this).attr('data-contents-url');

        $.get(url, function(data) {
            contents.empty().append(data);
        });
    });

    $('.measure-selector').each(function() {
        enforceMeasureDiscrete($(this));
    });

    $('.postponed').appendTo('#postponed').removeClass('postponed');

    $('ul[role=tablist]').each(function() {
        if ($(this).find('li.active').length == 0) {
            $(this).find('li a').first().tab('show');
        }
    });

    setupImportCsvEditor();
    setupPermissionsEditor();

    $('.loadablelist').each(function() {
        testListsEmptiness($(this));
    });
}

function randomString(total)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i = 0; i < total; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function parseFullDate(string) {
    var components = string.split(' ');

    var month = 0;
    var months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    for(month = 0; month < months.length; month++) {
        if (components[2] == months[month]) {
            month++;
            break;
        }
    }

    var date = components[3] + '-' + month + '-' + components[1];
    return Date.parse(date);
}

function parseFloatC(value) {
    if (typeof value === 'undefined')
        return 0;

    var ret = parseFloat(value.replace(/,/, '.'));
    if (isNaN(ret))
        ret = 0;

    return ret;
}

function priceRound(price) {
    return (Math.round(price * 100) / 100).toFixed(2);
}

/*
    Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
    un attributo, questa funzione serve ad applicare l'escape necessario
*/
function sanitizeId(identifier) {
    return identifier.replace(/:/g, '\\:').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
}

function voidForm(form) {
    form.find('input[type!=hidden]').val('');
    form.find('textarea').val('');
    form.find('select option:first').prop('selected', true);
    form.find('.error-message').remove();
}

function closeMainForm(form, data) {
    miscInnerCallbacks(form, data);

    var container = form.closest('.list-group-item');
    var head = container.prev();
    if (head.length == 0)
        return;

    head.removeClass('active');
    container.remove();

    $('html, body').animate({
        scrollTop: head.offset().top - 60
    }, 300);

    if (typeof(data) != 'undefined' && data.hasOwnProperty('header')) {
        head.empty().append(data.header).attr('href', data.url);
        afterListChanges(head.closest('.loadablelist'));
    }

    return head;
}

function sortingDates(a, b) {
    a = parseFullDate(a);
    b = parseFullDate(b);

    if (a == b)
        return 0;
    else if (a < b)
        return -1;
    else
        return 1;
}

function sortingValues(a, b) {
    a = parseFloatC(a);
    b = parseFloatC(b);

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
    var page = node.closest('.wizard_page');
    var parent = page.parent();
    var next = $(contents);
    parent.append(next);
    page.hide();
    next.show();
}

function testListsEmptiness(list) {
    var id = list.attr('id');
    var c = list.find('a').length;
    var alert = $('#empty-' + id);

    if (c == 0)
        alert.removeClass('hidden');
    else
        alert.addClass('hidden');
}

function afterListChanges(list) {
    var sorting = list.attr('data-sorting-function');
    if (sorting != null)
        window[sorting](list);

    testListsEmptiness(list);
}

function completionRowsInit(node) {
    $(node).find('input:text').each(function() {
        if ($(this).hasClass('tt-hint') == true) {
            return;
        }

        if ($(this).hasClass('tt-input') == false) {
            var source = $(this).closest('.completion-rows').attr('data-completion-source');

            $(this).typeahead(null, {
                name: 'users',
                displayKey: 'value',
                source: window[source].ttAdapter()
            }).on('typeahead:selected', function(obj, result, name) {
                var row = $(this).closest('li');
                row.before('<li class="list-group-item" data-object-id="' + result.id + '">' + result.label + '<div class="btn btn-xs btn-danger pull-right"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div></li>');

                var container = row.closest('.completion-rows');
                var fn = window[container.attr('data-callback-add')];
                if (typeof fn === 'function')
                    fn(container, result.id);
            });
        }
    });

    $(node).on('click', '.btn-danger', function() {
        var row = $(this).closest('li');

        var container = row.closest('.completion-rows');
        var fn = window[container.attr('data-callback-remove')];
        if (typeof fn === 'function')
            fn(container, row.attr('data-object-id'));

        row.remove();
    });
}

function loadingPlaceholder() {
    return $('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
}

function refreshFilter() {
    $('.form-filler').find('button[type=submit]').click();
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

function addPanelToTabs(group, panel, label) {
    var identifier = $(panel).attr('id');
    $(group + '.tab-content').append(panel);

    var list = $(group + '[role=tablist]');
    var tab = $('<li class="presentation"><a href="#' + identifier + '" aria-controls="#' + identifier + '" role="tab" data-toggle="tab">' + label + '</a></li>');

    var last = list.find('.last-tab');
    if (last.length != 0)
        last.before(tab);
    else
        list.append(tab);

    tab.find('a').click();
}

function listRow(id, url, header) {
    return $('<a data-element-id="' + id + '" href="' + url + '" class="loadable-item list-group-item">' + header + '</a>');
}

function appendToLoadableList(list, data, open) {
    var node = listRow(data.id, data.url, data.header);
    list.append(node);
    afterListChanges(list.closest('.loadablelist'));

    if (open)
        node.click();
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

    var test = form.find('input[name=update-list]');
    if (test.length != 0) {
        var listname = test.val();
        var list = $('#' + listname);
        appendToLoadableList(list, data, true);
    }

    var test = form.find('input[name=append-list]');
    if (test.length != 0) {
        var listname = test.val();
        var list = $('#' + listname);
        appendToLoadableList(list, data, false);
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
            var identifier_holder = sanitizeId($(this).val());

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
            var target = sanitizeId($(this).val());
            var box = $(target);

            var url = box.attr('data-fetch-url');
            if (url == null)
                url = $(this).attr('data-fetch-url');

            $.get(url, function(data) {
                box.empty().append(data);
            });
        });
    }

    var test = form.find('input[name^=post-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            var fn = window[$(this).val()];
            if (typeof fn === 'function')
                fn(form, data);
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
        $('.modal.fade.in').modal('hide');
    }

    var test = form.find('input[name=reload-whole-page]');
    if (test.length != 0) {
        location.reload();
    }

    locker = false;
}

function displayServerError(form, data) {
    if (data.target != '') {
        inlineFeedback(form.find('.main-form-buttons button[type=submit]'), 'Errore!');
        var input = form.find('[name=' + data.target + ']');
        input.closest('.form-group').addClass('has-error');
        input.closest('div').append('<span class="help-block error-message">' + data.message + '</span>');
    }
}

function creatingFormCallback(form, data) {
    if (data.status == 'success') {
        voidForm(form);

        var modal = form.parents('.modal');
        if (modal.length != 0) {
            modal.one('hidden.bs.modal', function() {
                miscInnerCallbacks(form, data);
            });
            modal.modal('hide');
        }
        else {
            miscInnerCallbacks(form, data);
        }
    }
    else if (data.status == 'error') {
        displayServerError(form, data);
    }
}

function currentLoadableUniqueSelector(target)
{
    var identifier = $(target).closest('div.list-group-item').attr('data-random-identifier');
    return 'div.list-group-item[data-random-identifier=' + identifier + ']';
}

function currentLoadableTrigger(target)
{
    return $(target).closest('.list-group-item').prev('a');
}

function currentLoadableLoaded(target)
{
    return currentLoadableTrigger(target).attr('data-element-id');
}

function reloadCurrentLoadable(listid)
{
    var list = $(listid);
    var activated = list.find('a.loadable-item.active');
    activated.each(function() {
        var r = $(this);
        r.click();
        setTimeout(function() {
            r.click();
        }, 600);
    });
}

/*******************************************************************************
	Prodotti
*/

function enforceMeasureDiscrete(node) {
    var form = node.closest('form');
    var discrete = node.find('option:selected').attr('data-discrete');
    var disabled = (discrete == '1');
    form.find('input[name=portion_quantity]').prop('disabled', disabled);
    form.find('input[name=variable]').prop('disabled', disabled);
}

/*******************************************************************************
	Ordini
*/

function setCellValue(cell, value) {
    string = value;

    if (cell.text().indexOf(current_currency) != -1)
        string = priceRound(value) + ' ' + current_currency;

    cell.text(string);
}

function updateOrderSummary(form) {
    /*
        Ricalcolare l'intero valore dell'ordine client-side sarebbe complesso,
        data la quantità di fattori da considerare, sicché ad ogni modifica
        chiedo al server di rifare i calcoli (utilizzando di fatto gli algoritmi
        già esistenti) passandogli i valori temporanei dei prezzi dei prodotti
    */
    var main_form = form.parents('.loadable-contents').last();
    main_form.find('.order-editor').each(function() {
        var identifier = $(this).find('input[name=order_id]');
        if (identifier.length == 0)
            return;

        var order_id = identifier.val();
        var data = $(this).serializeArray();

        $.ajax({
            method: 'POST',
            url: absolute_url + '/orders/recalculate/' + order_id,
            data: data,
            dataType: 'json',

            success: function(data) {
                var summary = main_form.find('.order-editor input[name=order_id][value="' + data.order + '"]').closest('.order-editor').find('.order-summary');

                for (var info in data) {
                    if (data.hasOwnProperty(info)) {
                        if (info == 'products') {
                            for (var pid in data.products) {
                                if (data.products.hasOwnProperty(pid)) {
                                    var row = summary.find('tr[data-product-id="' + pid + '"]');
                                    if (row != null) {
                                        var p = data.products[pid];
                                        for (var attr in p) {
                                            if (p.hasOwnProperty(attr)) {
                                                var cell = row.find('.order-summary-product-' + attr);
                                                if (cell != null)
                                                    setCellValue(cell, p[attr]);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            var cell = summary.find('.order-summary-order-' + info);
                            if (cell != null)
                                setCellValue(cell, data[info]);
                        }
                    }
                }
            }
        });
    });
}

/*******************************************************************************
	Prenotazioni / Consegne
*/

function afterBookingSaved(form, data) {
    var modal = form.closest('.modal');

    /*
        In questo caso, ho aggiunto una prenotazione dal modale di "Aggiunti
        Utente" in fase di consegna
    */
    if (modal.length != 0) {
        /*
            Se è stato salvata una nuova prenotazione vuota, il backend
            restituisce una risposta vuota e non c'è nessuna nuova prenotazione
            da aggiungere all'elenco
        */
        if (data.hasOwnProperty('id')) {
            var list = $("button[data-target='#" + modal.attr('id') + "']").parent().find('.loadablelist');
            if (list.find('> a[data-element-id=' + data.id + ']').length == 0) {
                data.url = data.url.replace('booking/', 'delivery/');
                appendToLoadableList(list, data, false);
            }
        }
        modal.modal('hide');
    }
    /*
        In questo caso, ho aggiunto la prenotazione dal pannello "Prenotazioni"
    */
    else {
        closeMainForm(form, data);
    }
}

function bookingTotal(editor) {
    var total_price = 0;

    var total_transport_tag = editor.find('input:hidden[name=global-transport-price]');
    var total_transport = 0;
    if (total_transport_tag.length != 0)
        total_transport = parseFloatC(total_transport_tag.val());

    var total_discount_tag = editor.find('input:hidden[name=global-discount]');
    var total_discount = 0;
    if (total_discount_tag.length != 0)
        total_discount = parseFloatC(total_discount_tag.val());

    editor.find('.booking-product').each(function() {
        if ($(this).hasClass('hidden'))
            return true;

        var product_price = $(this).find('input:hidden[name=product-price]');
        if (product_price.length == 0)
            return true;

        var price = product_price.val();
        price = parseFloatC(price);

        var product_transport = $(this).find('input:hidden[name=product-transport]');
        if (product_transport.length == 0) {
            transport = 0;
        }
        else {
            var transport = product_transport.val();
            transport = parseFloatC(transport);
        }

        var quantity = 0;
        var row_p = 0;
        var row_t = 0;

        $(this).find('.booking-product-quantity').each(function() {
            var input = $(this).find('input');

            var q = input.val();
            if (q == '')
                q = 0;
            else
                q = parseFloatC(q);

            if ($(this).hasClass('booking-variant-quantity')) {
                var offset = $(this).closest('.inline-variant-selector').find('select option:selected').attr('data-variant-price');
                current_price = price + parseFloatC(offset);
            } else {
                current_price = price;
            }

            row_p += current_price * q;
            row_t += transport * q;
        });

        $(this).closest('tr').find('.booking-product-price').text(priceRound(row_p) + ' ' + current_currency);
        total_price += row_p;
        total_transport += row_t;
    });

    editor.find('.booking-transport .booking-transport-price span').text(priceRound(total_transport));

    total_price += total_transport;
    total_price += total_discount;
    editor.find('.booking-total').text(priceRound(total_price));

    var form = editor.closest('form');
    var grand_total = 0;
    var status = {};

    form.find('.booking-editor').each(function() {
        var t = parseFloatC($('.booking-total', this).text());
        grand_total += t;
        status[$(this).attr('data-booking-id')] = t;
    });

    form.find('.all-bookings-total').text(priceRound(grand_total));

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

function getBookingRowStatus(row) {
    if (row.find('.glyphicon-ok').length)
        return 'shipped';
    if (row.find('.glyphicon-download-alt').length)
        return 'saved';
    return 'pending';
}

function sortShippingBookings(list) {
    list.find('> a').sort(function(a, b) {
        a = $(a);
        b = $(b);

        var a_status = getBookingRowStatus(a);
        var b_status = getBookingRowStatus(b);

        if (a_status == b_status) {
            return a.text().localeCompare(b.text());
        }

        if (a_status == 'pending')
            return -1;
        if (b_status == 'pending')
            return 1;
        if (a_status == 'saved')
            return -1;
        if (b_status == 'saved')
            return 1;

        return -1;
    }).each(function() {
        $(this).remove();
        $(this).appendTo(list);
    });
}

function submitDeliveryForm(form) {
    var id = form.closest('.modal').attr('id');
    var mainform = $('form[data-reference-modal=' + id + ']');

    /*
        Questo è per condensare eventuali nuovi prodotti aggiunti ma già
        presenti nella prenotazione.
    */
    mainform.find('.fit-add-product').not('.hidden').each(function() {
        var i = $(this).find('.booking-product-quantity input:text.number');
        if (i.length == 0)
            return;

        var product = sanitizeId(i.attr('name'));
        var added_value = parseFloatC(i.val());
        var existing = mainform.find('tr.booking-product').not('.fit-add-product').find('input:text.number[name=' + product + ']');
        if (existing.length != 0) {
            existing.val(parseFloatC(existing.val()) + added_value);
            i.remove();
        }
    });

    mainform.submit();
}

/*******************************************************************************
	Permessi
*/

function attachUserRole(role_id, user_id, target_id, target_class, callback) {
    $.ajax({
        method: 'POST',
        url: absolute_url + '/roles/attach',
        data: {
            role: role_id,
            user: user_id,
            target_id: target_id,
            target_class: target_class
        },
        success: function(data) {
            if (callback != null)
                window[callback](data);
        }
    });
}

function detachUserRole(role_id, user_id, target_id, target_class, callback) {
    $.ajax({
        method: 'POST',
        url: absolute_url + '/roles/detach',
        data: {
            role: role_id,
            user: user_id,
            target_id: target_id,
            target_class: target_class
        },
        success: function(data) {
            if (callback != null)
                window[callback](data);
        }
    });
}

function setupPermissionsEditor() {
    if ($('.role-editor:not(.general-inited)').length == 0)
        return;

    $('.role-editor:not(.general-inited)').each(function() {
        $(this).addClass('general-inited');
    });

    $('.roleAssign').each(function() {
        if ($(this).hasClass('tt-hint') == true) {
            return;
        }

        if ($(this).hasClass('tt-input') == false) {
            $(this).typeahead(null, {
                name: 'users',
                displayKey: 'value',
                source: userBlood.ttAdapter()
            }).on('typeahead:selected', function(obj, result, name) {
                var text = $(this);
                var role_id = currentLoadableLoaded(this);
                var selector = currentLoadableUniqueSelector(this);

                var label = result.label;
                $.ajax({
                    method: 'POST',
                    url: absolute_url + '/roles/attach',
                    dataType: 'HTML',
                    data: {
                        role: role_id,
                        user: result.id,
                    },
                    success: function(data) {
                        addPanelToTabs(selector + ' .role-users', $(data), label);
                        text.val('');
                    }
                });
            });
        }
    });

    $('.role-editor').on('submit', '#permissions-none form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serializeArray();

        var name = form.find('input[name=name]');
        var role_name = name.val();
        name.val('');

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                var panel = $(data);
                addPanelToTabs('.roles-list', panel, role_name);
            }
        });

    }).on('change', 'input:checkbox[data-role]', function(e) {
        var check = $(this);

        var url = '';
        if (check.is(':checked') == true)
            url = absolute_url + '/roles/attach';
        else
            url = absolute_url + '/roles/detach';

        var data = {};
        data.role = check.attr('data-role');
        data.action = check.attr('data-action');
        data.user = check.attr('data-user');
        data.target_id = check.attr('data-target-id');
        data.target_class = check.attr('data-target-class');

        $.ajax({
            method: 'POST',
            url: url,
            data: data
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
                success() {
                    button.closest('.loadable-contents').find('.role-users').find('[data-user=' + userid + ']').remove();
                }
            });
        }
    });
}

function supplierAttachUser(list, user_id) {
    var supplier_id = list.attr('data-supplier-id');
    var role_id = list.attr('data-role-id');
    attachUserRole(role_id, user_id, supplier_id, 'App\\Supplier', null);
}

function supplierDetachUser(list, user_id) {
    var supplier_id = list.attr('data-supplier-id');
    var role_id = list.attr('data-role-id');
    detachUserRole(role_id, user_id, supplier_id, 'App\\Supplier', null);
}

/*******************************************************************************
	Contabilità
*/

function displayRecalculatedBalances(form, data) {
    var modal = $('#display-recalculated-balance-modal');

    if (data.diffs.length != 0) {
        modal.find('a.table_to_csv').removeClass('hidden');
        var table = modal.find('.broken.hidden').removeClass('hidden').find('tbody');
        for (var name in data.diffs) {
            if (data.diffs.hasOwnProperty(name))
                table.append('<tr><td>' + name + '</td><td>' + data.diffs[name][0] + '</td><td>' + data.diffs[name][1] + '</td></tr>');
        }
    }
    else {
        modal.find('.fixed.hidden').removeClass('hidden');
    }

    modal.modal('show');
}

function refreshBalanceView() {
    $.ajax({
        method: 'GET',
        url: absolute_url + '/movements/balance',
        dataType: 'JSON',
        success: function(data) {
            $('.current-balance').each(function() {
                for (var property in data)
                    if (data.hasOwnProperty(property))
                        $(this).find('.' + property + ' span').text(data[property]);
            });
        }
    });
}

/*******************************************************************************
	Core
*/

$(document).ready(function() {
    $('#preloader').remove();

    $.ajaxSetup({
        cache: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ajaxSuccess(function(event) {
        generalInit();
    });

    $(document).ajaxError(function(event, jqXHR) {
        if (jqXHR.status == 401)
            window.location.href = '/login';
    });

    if (location.hash != '') {
        setTimeout(function() {
            var id = location.hash;
            if (id.charAt(0) === '#')
                id = id.substr(1);
            $('.loadablelist').find('a[data-element-id=' + id + ']').click();
        }, 100);
    }

    $('#bottom-stop').offset({
        left: 0,
        top: $(document).height() - 1
    });

    $(document).scroll(function() {
        var h = $(document).height();
        var b = $('#bottom-stop').offset();
        if (h < b.top)
            $(document).height(b.top);
        else
            $('#bottom-stop').offset({
                left: 0,
                top: $(document).height() - 1
            });
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
            var item = $(this);
            var content = item.next();

            var form = content.find('.main-form').first();
            $.ajax({
                method: 'GET',
                url: $(this).attr('href') + '/header',
                dataType: 'json',

                success: function(data) {
                    item.empty().append(data.header).attr('href', data.url);
                    afterListChanges(item.closest('.loadablelist'));
                }
            });

            content.slideUp(200, function() {
                $(this).remove();
                item.removeClass('active');
            });
        }
        else {
            var list = $(this).closest('.loadablelist');
            if (list.attr('data-sorting-function') != null) {
                list.find('> a.active').each(function() {
                    $(this).removeClass('active').next().remove();
                });
            }

            var node = $('<div>').addClass('list-group-item').addClass('loadable-contents').attr('data-random-identifier', randomString(10)).append(loadingPlaceholder());
            $(this).addClass('active').after(node);

            $('html, body').animate({
                scrollTop: node.offset().top - 120
            }, 300);

            $.ajax({
                method: 'GET',
                url: $(this).attr('href'),

                success: function(data) {
                    node.empty().append(data);
                },
                error: function() {
                    node.empty();
                }
            });
        }

        return false;
    });

    $('body').on('click', '.password-field .glyphicon', function() {
        var i = $(this).closest('.password-field').find('input');
        if (i.attr('type') == 'password')
            i.attr('type', 'text');
        else
            i.attr('type', 'password');

        $(this).toggleClass('glyphicon-eye-open').toggleClass('glyphicon-eye-close');
    });

    $('body').on('submit', '.list-filter form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serializeArray();

        var targetid = $(this).closest('.list-filter').attr('data-list-target');
        var target = $(targetid);
        target.empty().append(loadingPlaceholder());

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                target.replaceWith(data);
            }
        });

    }).on('change', '.list-filter input, .list-filter select', function() {
        $(this).closest('form').submit();

    }).on('show.bs.collapse', '.list-filter', function() {
        $(this).find('form').submit();

    }).on('click', '.list-filter .btn-danger', function(e) {
        e.preventDefault();
        var panel = $(this).closest('.list-filter');
        var form = panel.find('form');

        var targetid = panel.attr('data-list-target');
        var target = $(targetid);
        target.empty().append(loadingPlaceholder());

        panel.collapse('hide');

        var data = {};
        form.find('.enforce_filter').each(function() {
            data[$(this).attr('name')] = $(this).val();
        });

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                target.replaceWith(data);
            }
        });
    });

    $('body').on('click', '[data-toggle="modal"]', function(e) {
        e.preventDefault();
    });

    $('body').on('show.bs.popover', '.async-popover', function(e) {
        if (typeof $.data(e.target, 'dynamic-inited') == 'undefined') {
            $.data(e.target, 'dynamic-inited', {
                done: true
            });

            var pop = $(this);
            var url = pop.attr('data-contents-url');
            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'HTML',

                success: function(data) {
                    pop.attr('data-content', data);
                    pop.popover('show');
                }
            });
        }
    });

    $('body').on('keyup', 'input.number', function(e) {
        if (e.which >= 37 && e.which <= 40)
            return;

        var allow_negative = ($(this).attr('data-allow-negative') == '1');

        $(this).val(function(index, value) {
            var val = value.replace(/,/g, '.');
            if (allow_negative)
                val = val.replace(/[^\-0-9\.]/g, '');
            else
                val = val.replace(/[^0-9\.]/g, '');

            return val;
        });
    })
    .on('focus', 'input.number', function(e) {
        var v = parseFloatC($(this).val());
        if (v == 0)
            $(this).val('0');
    })
    .on('blur', 'input.number', function(e) {
        $(this).val(function(index, value) {
            return parseFloatC(value);
        });
    });

    $('body').on('blur', '.trim-2-ddigits', function() {
        $(this).val(function(index, value) {
            return parseFloatC(value).toFixed(2);
        });
    })
    .on('blur', '.trim-3-ddigits', function() {
        $(this).val(function(index, value) {
            return parseFloatC(value).toFixed(3);
        });
    });

    $('body').on('change', '.triggers-all-checkbox', function() {
        $(this).prop('disabled', true);

        var form = $(this).closest('form');
        var target = $(this).attr('data-target-class');
        var new_status = $(this).prop('checked');

        /*
            Le checkbox in oggetto possono essere "lisce" o gestite con
            bootstrapToggle, e vanno cambiate in modo diverso
        */
        form.find('.' + target).each(function() {
            if ($(this).parent().attr('data-toggle') == 'toggle')
                $(this).bootstrapToggle(new_status ? 'on' : 'off');
            else
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
        form.find('.' + target).find('option[value=' + value + ']').prop('selected', true);
    });

    $('body').on('click', '.decorated_radio label', function() {
        $(this).siblings('input[type=radio]').prop('checked', true);
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
                    reloadCurrentLoadable(listid);
                });
                modal.modal('hide');
            }
            else {
                reloadCurrentLoadable(listid);
            }
        }
    });

    $('body').on('shown.bs.modal', '.modal', function(e) {
        $(this).find('[data-default-value]').each(function() {
            if ($(this).val() == '') {
                var value = $(this).attr('data-default-value');
                $(this).val(value);
            }
        });

        $(this).parents('.modal-dialog').css('height', '100%');
        $(this).find('[data-empty-on-modal=true]').empty();
    });

    $('body').on('focus', '.date[data-enforce-after]', function() {
        var select = $(this).attr('data-enforce-after');
        var target = $(this).closest('form').find(select);

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
        if (current_start.toString() != current_ref.toString())
            $(this).datepicker('setStartDate', current_ref);
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

    $('body').on('click', '.object-details', function() {
        var url = $(this).attr('data-show-url');
        var modal = $('#service-modal');
        modal.find('.modal-body').empty().append(loadingPlaceholder());
        modal.modal('show');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'HTML',
            success: function(data) {
                var modal = $('#service-modal');
                modal.find('.modal-body').empty().append(data);
            }
        });
    });

    $('body').on('submit', '.main-form', function(event) {
        event.preventDefault();

        var form = $(this);
        form.find('.main-form-buttons button').prop('disabled', true);

        /*
            Problema di origine sconosciuta: i dati in multipart/form-data
            inviati con una PUT non vengono letti
            https://github.com/laravel/framework/issues/13457
        */
        var data = new FormData(this);
        var method = form.attr('method').toUpperCase();
        if (method == 'PUT') {
            method = 'POST';
            data.append('_method', 'PUT');
        }

        $.ajax({
            method: method,
            url: form.attr('action'),
            data: data,
            dataType: 'json',
            processData: false,
            contentType: false,

            success: function(data) {
                if (data.hasOwnProperty('status') && data.status == 'error') {
                    displayServerError(form, data);
                    form.find('.main-form-buttons button').prop('disabled', false);
                }
                else {
                    closeMainForm(form, data);
                }
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

        if (confirm(_('Sei sicuro di voler eliminare questo elemento?'))) {
            form.find('.main-form-buttons button').attr('disabled', 'disabled');

            $.ajax({
                method: 'DELETE',
                url: form.attr('action'),
                dataType: 'json',

                success: function(data) {
                    var upper = closeMainForm(form);
                    var list = upper.closest('.loadablelist');
                    upper.remove();
                    testListsEmptiness(list);
                }
            });
        }
    });

    $('body').on('click', '.icons-legend button', function() {
        var legend = $(this).closest('.icons-legend');
        var target = legend.attr('data-list-target');

        if ($(this).hasClass('active')) {
            $(this).removeClass('active');

            $('.loadablelist' + target + ' a').each(function() {
                $(this).show().next('li').show();
            });
        }
        else {
            /*
                Qui devo considerare la somma di tutti i filtri che sono stati
                attivati: se un elemento risulterebbe nascosto a fronte del
                click su un attributo, potrebbero essercene altri che lo
                mantengono visibile
            */
            legend.find('button').removeClass('active');
            $(this).addClass('active');
            var c = $(this).find('span.glyphicon').attr('class');

            $('.loadablelist' + target + ' a').each(function() {
                var show = false;

                $(this).find('span.glyphicon').each(function() {
                    var icons = $(this).attr('class');
                    show = (icons == c);
                    if (show)
                        return false;
                });

                if (show)
                    $(this).show().next('li').show();
                else
                    $(this).hide().next('li').hide();
            });
        }
    });

    $('body').on('click', '.list-filters button', function() {
        var filter = $(this).closest('.list-filters');
        var target = filter.attr('data-list-target');
        var attribute = $(this).attr('data-filter-attribute');

        $('.loadablelist' + target + ' a[data-filtered-' + attribute + '=true]').each(function() {
            $(this).toggleClass('hidden').next('li').toggleClass('hidden');
        });
    });

    $('body').on('keyup', '.list-text-filter', function() {
        var text = $(this).val().toLowerCase();
        var target = $(this).attr('data-list-target');

        if (text == '') {
            $('.loadablelist' + target + ' .loadable-item').show();
        }
        else {
            $('.loadablelist' + target + ' .loadable-item').each(function() {
                if ($(this).text().toLowerCase().indexOf(text) == -1)
                    $(this).hide();
                else
                    $(this).show();
            });
        }
    });

    $('body').on('change', '.table-filters input:radio', function() {
        var filter = $(this).closest('.table-filters');
        var target = filter.attr('data-table-target');
        var attribute = $(this).attr('name');
        var value = $(this).val();
        var table = $(target + ' table');

        if (value == 'all') {
            table.find('tr').removeClass('hidden');
        }
        else {
            table.find('tr[data-filtered-' + attribute + ']').each(function() {
                var attr = $(this).attr('data-filtered-' + attribute);
                if (attr == value)
                    $(this).removeClass('hidden');
                else
                    $(this).addClass('hidden');
            });
        }
    });

    $('body').on('change', '.link-filters input:radio', function() {
        var filter = $(this).closest('.table-filters');
        var target = filter.attr('data-link-target');
        var link = $(target);
        var attribute = $(this).attr('name');
        var value = $(this).val();

        var parsed = new URL(link.attr('href'));
        var url = parsed.protocol + '//' + parsed.host + parsed.pathname + '?' + attribute + '=' + value;
        link.attr('href', url);
    });

    $('body').on('change', '.img-preview input:file', function() {
        previewImage(this);
    });

    $('body').on('submit', '.inner-form', function(event) {
        event.preventDefault();
        var form = $(this);

        var data = new FormData(this);
        var method = form.attr('method').toUpperCase();
        if (method == 'PUT') {
            method = 'POST';
            data.append('_method', 'PUT');
        }

        var save_button = form.find('.saving-button');
        save_button.prop('disabled', true);

        $.ajax({
            method: method,
            url: form.attr('action'),
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function(data) {
                inlineFeedback(save_button, _('Salvato!'));
                miscInnerCallbacks(form, data);
            }
        });
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

    $('body').on('submit', '.creating-form', function(event) {
        if (event.isDefaultPrevented())
            return;

        var save_button = $(this).find('button[type=submit]');
        save_button.prop('disabled', true);

        event.preventDefault();
        var form = $(this);
        var disabled = form.find(':disabled').removeAttr('disabled');

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: new FormData(this),
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(data) {
                creatingFormCallback(form, data);
                save_button.prop('disabled', false);
            }
        });

        disabled.attr('disabled', 'disabled');
    });

    $('body').on('click', '.spare-delete-button', function(event) {
        event.preventDefault();

        if (confirm('Sei sicuro?')) {
            var form = $(this).closest('form');

            $.ajax({
                url: $(this).attr('data-delete-url'),
                method: 'DELETE',
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

    $('body').on('click', '.async-modal', function(event) {
        event.preventDefault();

        $.ajax({
            url: $(this).attr('data-target-url'),
            method: 'GET',
            dataType: 'html',
            success: function(data) {
                $(data).modal().on('shown.bs.modal', function() {
                    generalInit();
                }).on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            }
        });
    });

    $('body').on('click', '.table_to_csv', function(e) {
        e.preventDefault();
        var target = $(this).attr('data-target');
        var data = $(target).TableCSVExport({
            delivery: 'download',
            filename: _('bilanci_ricalcolati.csv')
        });
    });

    $('body').on('change', '.measure-selector', function(event) {
        enforceMeasureDiscrete($(this));
    });

    $('body').on('change', '.contacts-selection select', function() {
        $(this).closest('.row').find('input:text').attr('class', '').addClass('form-control').addClass($(this).find('option:selected').val());
    });

    $('body').on('focus', 'input.password-changer', function() {
        $(this).popover({
            content: function() {
                var input = $(this);

                var ret = $('<div>\
                    <div class="form-group">\
                        <label for="password" class="col-sm-4 control-label">' + _('Nuova Password') + '</label>\
                        <div class="col-sm-8"><input type="password" class="form-control" name="password" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <label for="password_confirm" class="col-sm-4 control-label">' + _('Conferma Password') + '</label>\
                        <div class="col-sm-8"><input type="password" class="form-control" name="password_confirm" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <div class="col-sm-8 col-sm-offset-4"><button class="btn btn-default">' + _('Annulla') + '</button> <button class="btn btn-success">' + _('Salva') + '</button></div>\
                    </div>\
                </div>');

                ret.find('button.btn-success').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var password = ret.find('input[name=password]').val();
                    var confirm = ret.find('input[name=password_confirm]').val();

                    if (password == confirm) {
                        input.val(password);
                        input.popover('destroy');
                    }
                    else {
                        alert('Le password sono diverse!');
                    }
                });

                ret.find('button.btn-default').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.popover('destroy');
                });

                return ret;
            },
            template: '<div class="popover password-popover" role="tooltip"><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            placement: 'left',
            html: true,
        });
    });

    $('body').on('focus', 'input.address', function() {
        $(this).popover({
            content: function() {
                var input = $(this);

                var ret = $('<div>\
                    <div class="form-group">\
                        <label for="street" class="col-sm-4 control-label">' + _('Indirizzo') + '</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="street" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <label for="city" class="col-sm-4 control-label">' + _('Città') + '</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="city" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <label for="cap" class="col-sm-4 control-label">' + _('CAP') + '</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="cap" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <div class="col-sm-8 col-sm-offset-4"><button class="btn btn-default">' + _('Annulla') + '</button> <button class="btn btn-success">' + _('Salva') + '</button></div>\
                    </div>\
                </div>');

                var value = $(this).val();
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

                    input.popover('destroy');
                });

                ret.find('button.btn-default').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.popover('destroy');
                });

                return ret;
            },
            template: '<div class="popover address-popover" role="tooltip"><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            placement: 'left',
            html: true,
        });
    });

    $('body').on('change', '.movement-modal input[name=method]', function() {
        if ($(this).prop('checked') == false)
            return;

        var method = $(this).val();
        var method_string = 'when-method-' + method;
        var modal = $(this).closest('.movement-modal');
        modal.find('[class*="when-method-"]').each(function() {
            if ($(this).hasClass(method_string))
                $(this).removeClass('hidden');
            else
                $(this).addClass('hidden');
        });
    })
    .on('change', '.movement-modal input[name=amount]', function() {
        var status = $(this).closest('.movement-modal').find('.sender-credit-status');
        if (status.length) {
            var amount = parseFloatC($(this).val());
            var current = parseFloatC(status.find('.current-sender-credit').text());
            if (amount > current)
                status.removeClass('alert-success').addClass('alert-danger');
            else
                status.removeClass('alert-danger').addClass('alert-success');
        }
    });

    $('body').on('change', '.movement-type-selector', function(event) {
        var type = $(this).find('option:selected').val();
        var selectors = $(this).closest('form').find('.selectors');
        selectors.empty().append(loadingPlaceholder());

        $.ajax({
            method: 'GET',
            url: absolute_url + '/movements/create',
            dataType: 'html',
            data: {
                type: type
            },

            success: function(data) {
                selectors.empty().append(data);
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
            if(type != 'App\\Gas' && type != sender && type != target)
                $(this).addClass('hidden');
            else
                $(this).removeClass('hidden');
        });

        table.find('thead input[data-active-for]').each(function() {
            var type = $(this).attr('data-active-for');
            if(type != '' && type != sender && type != target)
                $(this).bootstrapToggle('off').bootstrapToggle('disable').change();
            else
                $(this).bootstrapToggle('enable');
        });
    })
    .on('change', '.movement-type-editor table thead input:checkbox', function() {
        var active = $(this).prop('checked');
        var index = $(this).closest('th').index();

        if (active == false) {
            $(this).closest('table').find('tbody tr').each(function() {
                var cell = $(this).find('td:nth-child(' + (index + 1) + ')');
                cell.find('input[value=ignore]').click();
                cell.find('label, input').attr('disabled', 'disabled');
            });
        }
        else {
            $(this).closest('table').find('tbody tr').each(function() {
                var cell = $(this).find('td:nth-child(' + (index + 1) + ')');
                cell.find('label, input').removeAttr('disabled');
            });
        }
    });

    $('body').on('click', '.form-filler button[type=submit]', function(event) {
        event.preventDefault();
        var form = $(this).closest('.form-filler');
        var target = $(form.attr('data-fill-target'));
        var data = form.find('input, select').serialize();
        target.empty().append(loadingPlaceholder());

        $.ajax({
            method: 'GET',
            url: form.attr('data-action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                target.empty().append(data);
            }
        });
    })
    .on('click', '.form-filler a.form-filler-download', function(event) {
        event.preventDefault();
        var data = $(this).closest('.form-filler').find('input, select').serializeArray();
        var url = $(this).attr('href') + '&' + $.param(data);
        window.open(url, '_blank');
    });

    $('body').on('submit', '.password-protected', function(event) {
        if ($(this).attr('data-password-protected-verified') != '1') {
            event.preventDefault();
            var id = $(this).attr('id');
            var modal = $('#password-protection-dialog');
            modal.attr('data-form-target', '#' + id);
            modal.find('input:password').val();
            modal.modal('show');

            $('#' + id).find('button:submit').prop('disabled', true);

            return false;
        }

        return true;
    })
    .on('submit', '#password-protection-dialog form', function(event) {
        event.preventDefault();
        var modal = $(this).closest('.modal');

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            data: {
                password: $(this).find('input[type=password]').val()
            },
            success: function(data) {
                if (data == 'ok') {
                    var target = modal.attr('data-form-target');
                    modal.modal('hide');
                    var form = $(target);
                    form.attr('data-password-protected-verified', '1');

                    $.ajax({
                        method: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serializeArray(),
                        dataType: 'JSON',
                        success: function(data) {
                            form.find('button:submit').prop('disabled', false);
                            miscInnerCallbacks(form, data);
                            form.attr('data-password-protected-verified', '0');
                        },
                        error: function() {
                            var button = form.find('button:submit');
                            inlineFeedback(button, _('ERRORE'));
                            form.attr('data-password-protected-verified', '0');
                        }
                    });
                }
            }
        });
    });

    /*
        Gestione fornitori
    */

    $('body').on('click', '.product-editor .duplicate-product', function(e) {
        e.preventDefault();
        var id = currentLoadableLoaded(this);
        var list = $(this).closest('.loadablelist');
        var original = currentLoadableTrigger(this);
        $.ajax({
            url: absolute_url + '/products',
            method: 'POST',
            dataType: 'JSON',
            data: {
                duplicate_id: id
            },
            success: function(data) {
                original.click();
                setTimeout(function() {
                    appendToLoadableList(list, data, true);
                }, 200);
            }
        });
    });

    $('body').on('click', '.variants-editor .delete-variant', function() {
        var editor = $(this).closest('.variants-editor');
        var id = $(this).closest('.row').find('input:hidden[name=variant_id]').val();

        $.ajax({
            method: 'DELETE',
            url: absolute_url + '/variants/' + id,
            dataType: 'html',

            success: function(data) {
                editor.replaceWith(data);
            }
        });

    }).on('click', '.variants-editor .edit-variant', function() {
        var row = $(this).closest('.row');
        var id = row.find('input:hidden[name=variant_id]').val();
        var name = row.find('span.variant_name').text().trim();
        var offset = row.find('input:hidden[name=variant_offset]').val();
        var values = row.find('.exploded_values').contents().clone();

        var form = $(this).closest('.list-group').find('.creating-variant-form');
        form.find('input:hidden[name=variant_id]').val(id);
        form.find('input[name=name]').val(name);
        form.find('.values_table').empty().append(values);
        form.find('.many-rows').manyrows();

        if (offset == '1') {
            form.find('input[name=has_offset]').bootstrapToggle('on');
            form.find('input[name*=price_offset]').closest('.form-group').show();
        } else {
            form.find('input[name=has_offset]').bootstrapToggle('off');
            form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
        }

        form.closest('.modal').modal('show');

    }).on('click', '.variants-editor .add-variant', function() {
        var row = $(this).closest('.list-group');
        var form = row.find('.creating-variant-form');
        var modal = row.find('.create-variant');
        form.find('.many-rows').manyrows('refresh');
        form.find('input:text').val('');
        form.find('input:hidden[name=variant_id]').val('');
        form.find('input:checkbox').bootstrapToggle('off');
        form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
        modal.modal('show');

    }).on('change', '.creating-variant-form input:checkbox[name=has_offset]', function() {
        var has = $(this).is(':checked');
        var form = $(this).closest('form');

        if (has == true)
            form.find('input[name*=price_offset]').closest('.form-group').show();
        else
            form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();

    }).on('submit', '.creating-variant-form', function(e) {
        e.preventDefault();
        var modal = $(this).closest('.modal');
        var editor = $(this).closest('.list-group').find('.variants-editor');
        var data = $(this).serializeArray();

        editor.empty().append(loadingPlaceholder());

        $.ajax({
            method: 'POST',
            url: absolute_url + '/variants',
            data: data,
            dataType: 'html',

            success: function(data) {
                editor.replaceWith(data);
                modal.modal('hide');
            }
        });

        return false;
    });

    /*
        Gestione utenti
    */

    $('body').on('change', '.user-editor input:radio[name=status], .supplier-editor input:radio[name=status]', function() {
        var date = $(this).closest('.form-group').find('input.date').closest('.status-date');
        if ($(this).val() == 'deleted')
            date.removeClass('hidden');
        else
            date.addClass('hidden');
    });

    /*
    	Gestione ordini
    */

    $('body').on('click', '.order-columns-selector a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var table = $(this).closest('.btn-group').siblings('.order-summary').first();
        var box = $(this).find('input:checkbox');
        var name = box.val();
        box.prop('checked', !box.prop('checked'));
        var show = box.prop('checked');

        if (show) {
            table.find('.order-cell-' + name).removeClass('hidden');
        }
        else {
            table.find('.order-cell-' + name).addClass('hidden');
        }
    });

    $('body').on('keyup', '.order-summary input', function() {
        updateOrderSummary($(this));
    });

    $('body').on('click', '.order-summary .toggle-product-abilitation', function() {
        $('.order-summary tr.product-disabled').toggle();
    })
    .on('change', '.order-summary tr .enabling-toggle', function() {
        $(this).closest('tr').toggleClass('product-disabled');
    })
    .on('change', '.order-summary tr .discount-toggle', function() {
        var p = $(this).closest('tr').find('.product-price');
        p.find('.full-price, .product-discount-price').toggleClass('hidden');

        /*
        	TODO: aggiornare i prezzi totali nella tabella dell'ordine
        */
    })
    .on('shown.bs.modal', '.order-document-download-modal', function() {
        $(this).find('input[name=send_mail]').bootstrapToggle('off');
        $(this).find('.order_document_body_mail').hide();
        $(this).find('.order_document_recipient_mail').hide();
    })
    .on('change', '.order-document-download-modal input[name=send_mail]', function() {
        var status = $(this).prop('checked');
        var form = $(this).closest('.order-document-download-modal').find('form');
        var textarea = form.find('.order_document_body_mail');
        var recipient = form.find('.order_document_recipient_mail');
        var submit = form.find('.btn-success');

        if (status) {
            textarea.show();
            recipient.show();
            submit.text(_('Invia Mail'));
            form.removeClass('direct-submit');
        }
        else {
            textarea.hide();
            recipient.hide();
            submit.text(_('Download'));
            form.addClass('direct-submit');
        }
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

    $('body').on('shown.bs.tab', 'a[data-toggle="tab"][data-async-load]', function(e) {
        var t = e.target.hash;
        var tab = $(t);
        tab.empty().append(loadingPlaceholder());

        $.ajax({
            method: 'GET',
            url: $(this).attr('data-async-load'),
            dataType: 'html',

            success: function(data) {
                tab.empty().append(data);
            }
        });
    });

    $('body').on('keyup', '.booking-product-quantity input', function() {
        var booked = parseFloatC($(this).val());
        var row = $(this).closest('.booking-product');
        var wrong = false;

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

            var m = row.find('input:hidden[name=product-maximum]');
            if (m.length != 0) {
                var maximum = parseFloatC(m.val());
                if (maximum != 0 && booked > maximum) {
                    row.addClass('has-warning');
                    wrong = true;
                }
            }

            var m = row.find('input:hidden[name=product-available]');
            if (m.length != 0) {
                var maximum = parseFloatC(m.val());

                /*
                    I controlli li faccio sul contenuto della singola
                    casella, ma la disponibilità è complessiva (vedasi: il
                    caso di un prodotto di cui ordino diverse varianti con
                    diverse quantità)
                */
                var in_booked = 0;
                row.find('.booking-product-quantity input').each(function() {
                    var v = $(this).val();
                    if (v != '')
                    in_booked += parseFloatC(v);
                });

                var m = row.find('input:hidden[name=product-partitioning]');
                if (m.length != 0) {
                    var portion = parseFloatC(m.val());
                    if (portion != 0)
                        in_booked = in_booked * portion;
                }

                if (in_booked > maximum) {
                    row.addClass('has-error');
                    booked = 0;
                    wrong = true;
                }
            }

            if (wrong == false)
                row.removeClass('has-error').removeClass('has-warning');
        }

        var editor = row.closest('.booking-editor');
        bookingTotal(editor);

    }).on('change', '.variants-selector select', function() {
        var editor = $(this).closest('.booking-editor');
        bookingTotal(editor);

    }).on('blur', '.booking-product-quantity input', function() {
        var v = $(this).val();
        var row = $(this).closest('.booking-product');
        if (v == '' || row.hasClass('has-error'))
            $(this).val('0');

    }).on('focus', '.booking-product-quantity input', function() {
        $(this).closest('.booking-product').removeClass('.has-error').removeClass('has-warning');

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
                    row.find('.bookable-target').empty().append(data);
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

            bookingTotal($(this));
        });

        return false;
    });

    /*
        Multi-GAS
    */

    $('body').on('change', '.multigas-editor input:checkbox[data-gas]', function(e) {
        var check = $(this);

        var url = '';
        if (check.is(':checked') == true)
            url = absolute_url + '/multigas/attach';
        else
            url = absolute_url + '/multigas/detach';

        var data = {};
        data.gas = check.attr('data-gas');
        data.target_id = check.attr('data-target-id');
        data.target_type = check.attr('data-target-type');

        $.ajax({
            method: 'POST',
            url: url,
            data: data
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
                    var total = parseFloatC($(this).text());
                    test = (test || (total != 0));
                });

                if (test == false)
                    test = confirm(_('Tutte le quantità consegnate sono a zero! Vuoi davvero procedere?'));

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
            if (v != '')
                quantity += parseFloatC(v);

            $(this).val('0');
        });

        /*
            Il trigger keyup() alla fine serve a forzare il ricalcolo del totale
            della consegna quando il modale viene chiuso
        */
        modal.closest('.booking-product-quantity').find('input.number').first().val(quantity).keyup();
        modal.modal('hide');
    });

    $('body').on('click', '.delete-booking', function(e) {
        e.preventDefault();

        var form = $(this).closest('.inner-form');

        if (confirm(_('Sei sicuro di voler annullare questa prenotazione?'))) {
            form.find('.main-form-buttons button').attr('disabled', 'disabled');

            $.ajax({
                method: 'DELETE',
                url: form.attr('action'),
                dataType: 'json',

                success: function(data) {
                    form.find('.main-form-buttons button').removeAttr('disabled');
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
        var total_transport = 0;
        var grand_total = 0;

        table.find('.orders-in-invoice-candidate').each(function() {
            if ($(this).find('input:checkbox').prop('checked')) {
                total_taxable += parseFloatC($(this).find('.taxable label').text());
                total_tax += parseFloatC($(this).find('.tax label').text());
                total_transport += parseFloatC($(this).find('.transport label').text());
                grand_total += parseFloatC($(this).find('.total label').text());
            }
        });

        var totals_row = table.find('.orders-in-invoice-total');
        totals_row.find('.taxable label').text(priceRound(total_taxable) + ' ' + current_currency);
        totals_row.find('.tax label').text(priceRound(total_tax) + ' ' + current_currency);
        totals_row.find('.transport label').text(priceRound(total_transport) + ' ' + current_currency);
        totals_row.find('.total label').text(priceRound(grand_total) + ' ' + current_currency);
    });

    /*
    	Widget generico wizard
    */

    $('body').on('show.bs.modal', '.modal.wizard', function(e) {
        $(this).find('.wizard_page:not(:first)').hide();

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

    $('body').on('submit', '.modal form:not(.direct-submit)', function(event) {
        if (event.isDefaultPrevented())
            return;

        event.preventDefault();
        var form = $(this);
        var data = form.serializeArray();

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,

            success: function(data) {
                /* dummy */
            }
        });
    });
});
