/*******************************************************************************
	Varie ed eventuali
*/

$.fn.tagName = function() {
    return this.prop("tagName").toLowerCase();
};

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

    $('.trim-2-ddigits').blur(function() {
        $(this).val(parseFloatC($(this).val()).toFixed(2));
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

    $('.middle-tabs').on('click', '.btn', function() {
        var target = $(this).attr('data-target');
        $(this).addClass('active').siblings().removeClass('active');
        $(target).addClass('active').siblings().removeClass('active');
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

    $('.many-rows').each(function() {
        manyRowsInit($(this));
    });

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

                /*
                    Cfr. BookingHandler::bookingUpdate();
                */
                var while_shipping = ($(this).closest('.modal.add-booking-while-shipping').length != 0);

                $.ajax({
                    url: '/booking/' + aggregate_id + '/user/' + result.id,
                    method: 'GET',
                    data: {
                        'booking-on-shipping': while_shipping ? 1 : 0,
                    },
                    dataType: 'HTML',
                    success: function(data) {
                        if(while_shipping) {
                            data = $(data);
                            data.append('<input type="hidden" name="booking-on-shipping" value="1">');
                        }

                        $('.other-booking').empty().append(data);
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

    $('.dynamic-tree').nestedSortable({
        listType: 'ul',
        items: 'li',
        toleranceElement: '> div'
    });

    $('#orderAggregator ul').droppable({
        accept: 'li',
        drop: function(event, ui) {
            ui.draggable.css('right', '').css('left', '').css('top', '').css('bottom', '').css('width', '').css('height', '');
            $(this).append(ui.draggable);
        }
    });

    $('#orderAggregator ul li').draggable({
        revert: 'invalid'
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

    setupVariantsEditor();
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

function parseFloatC(value) {
    return parseFloat(value.replace(/,/, '.'));
}

function priceRound(price) {
    return (Math.round(price * 100) / 100).toFixed(2);
}

/*
    Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
    un attributo, questa funzione serve ad applicare l'escape necessario
*/
function sanitizeId(identifier) {
    return identifier.replace(/:/, '\\:');
}

function voidForm(form) {
    form.find('input[type!=hidden]').val('');
    form.find('textarea').val('');
}

function closeMainForm(form, data) {
    var container = form.closest('.list-group-item');
    var head = container.prev();
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

function manyRowsAddDeleteButtons(node) {
    var fields = node.find('.row:not(.many-rows-header)');
    if (fields.length > 1 && node.find('.delete-many-rows').length == 0) {
        fields.each(function() {
            var button = '<div class="col-md-2"><div class="btn btn-danger delete-many-rows pull-right"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div></div>';
            $(this).append(button);
        });
    } else if (fields.length == 1) {
        node.find('.delete-many-rows').each(function() {
            $(this).closest('.col-md-2').remove();
        });
    }
}

function manyRowsInitRow(row, fresh) {
    if (fresh) {
        row.find('input').val('');
        row.find('.customized-cell').empty();
    }
}

function manyRowsInit(node) {
    manyRowsAddDeleteButtons(node);

    node.find('.row').each(function() {
        manyRowsInitRow($(this), false);
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

function parseDynamicTree(tree) {
    var data = [];
    var index = 1;

    while(true) {
        var n = tree.find('> li:nth-child(' + index + ')');
        if (n.length == 0)
            break;

        var node = {
            id: n.attr('id'),
            name: n.find('input:text').val()
        };

        node.children = parseDynamicTree(n.find('ul'));
        data.push(node);
        index++;
    }

    return data;
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

/*
    I form possono includere una serie di campi <input type="hidden"> che, in
    funzione dell'attributo "name", possono attivare delle funzioni speciali
    dopo il submit usando il valore ritornato
*/
function miscInnerCallbacks(form, data) {
    var test = form.find('input[name=update-list]');
    if (test.length != 0) {
        var listname = test.val();
        var list = $('#' + listname);
        var node = $('<a data-element-id="' + data.id + '" href="' + data.url + '" class="loadable-item list-group-item">' + data.header + '</a>');
        list.append(node);
        afterListChanges(list.closest('.loadablelist'));
        node.click();
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
}

function creatingFormCallback(form, data) {
    if (data.status == 'success') {
        voidForm(form);

        var modal = form.parents('.modal');
        if (modal.length != 0)
            modal.modal('hide');

        miscInnerCallbacks(form, data);
    }
}

function currentLoadableUniqueSelector(target)
{
    var identifier = $(target).closest('li.list-group-item').attr('data-random-identifier');
    return 'li.list-group-item[data-random-identifier=' + identifier + ']';
}

function currentLoadableLoaded(target)
{
    return $(target).closest('li.list-group-item').prev('a').attr('data-element-id');
}

function reloadCurrentLoadable(target)
{
    listid = currentLoadableUniqueSelector(target);
    var current = $(listid);
    var toggle = current.prev('.loadable-item');
    current.slideUp(500, function() {
        $(this).remove();
        toggle.removeClass('active').click();
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
    if (cell.text().indexOf('€') != -1)
        string = priceRound(value) + ' €';
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
            url: '/orders/recalculate/' + order_id,
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

$('body').on('keyup', '.order-summary input', function() {
    updateOrderSummary($(this));
});

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
        var list = $("button[data-target='#" + modal.attr('id') + "']").parent().find('.loadablelist');
        var url = data.url.replace('booking/', 'delivery/');
        list.append('<a data-element-id="' + data.id + '" href="' + url + '" class="loadable-item list-group-item">' + data.header + '</a>');
        afterListChanges(list);
        modal.modal('hide');
    }
    /*
        In questo caso, ho aggiunto la prenotazione dal pannello "Prenotazioni"
    */
    else {
        closeMainForm(form);
    }
}

function bookingTotal(editor) {
    var total_price = 0;

    editor.find('.booking-product').each(function() {
        if ($(this).hasClass('hidden'))
            return true;

        var product_price = $(this).find('input:hidden[name=product-price]');
        if (product_price.length == 0)
            return true;

        var price = product_price.val();
        price = parseFloatC(price);

        var quantity = 0;
        var row_p = 0;

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
        });

        $(this).closest('tr').find('.booking-product-price').text(priceRound(row_p) + ' €');
        total_price += row_p;
    });

    var transport = editor.find('.booking-transport');
    if (transport.length != 0)
        total_price += parseFloatC(transport.find('span').text());

    total_price = priceRound(total_price);

    var total_label = editor.find('.booking-total');
    total_label.text(total_price);

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
        } else {
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
    var modal = form.closest('.modal');
    var id = modal.attr('id');
    var mainform = $('form[data-reference-modal=' + id + ']');

    modal.on('hidden.bs.modal', function() {
        mainform.submit();
    });
    modal.modal('hide');
}

/*******************************************************************************
	Permessi
*/

function attachUserRole(role_id, user_id, target_id, target_class, callback) {
    $.ajax({
        method: 'POST',
        url: '/roles/attach',
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
        url: '/roles/detach',
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
                    url: '/roles/attach',
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
            url = '/roles/attach';
        else
            url = '/roles/detach';

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

        if(confirm('Sei sicuro di voler revocare questo ruolo?')) {
            var button = $(this);

            var data = {
                role: button.attr('data-role'),
                user: button.attr('data-user')
            };

            var userid = data.user;

            $.ajax({
                method: 'POST',
                url: '/roles/detach',
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

function refreshBalanceView() {
    $.ajax({
        method: 'GET',
        url: '/movements/balance',
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
	Statistiche
*/

function runSummaryStats() {
    var start = $('#stats-summary-form input[name=startdate]').val();
    var end = $('#stats-summary-form input[name=enddate]').val();

    $.getJSON('/stats/summary', {
        start: start,
        end: end
    }, function(data) {
        new Chartist.Pie('#stats-generic-expenses', data.expenses, {
            labelDirection: 'explode',
            labelOffset: 40,
            chartPadding: 20,
        });
        new Chartist.Bar('#stats-generic-users', data.users, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
    });
}

function runSupplierStats() {
    var supplier = $('#stats-supplier-form select[name=supplier] option:selected').val();
    var start = $('#stats-supplier-form input[name=startdate]').val();
    var end = $('#stats-supplier-form input[name=enddate]').val();

    $.getJSON('/stats/supplier', {
        start: start,
        end: end,
        supplier: supplier
    }, function(data) {
        new Chartist.Pie('#stats-products-expenses', data.expenses, {
            labelDirection: 'explode',
            labelOffset: 40,
            chartPadding: 20,
        });
        new Chartist.Bar('#stats-products-users', data.users, {
            horizontalBars: true,
            axisX: {
                onlyInteger: true
            },
            axisY: {
                offset: 220
            },
        });
    });
}

function setupStatisticsForm() {
    if ($('#stats-summary-form').length != 0) {
        runSummaryStats();

        $('#stats-summary-form').submit(function(event) {
            event.preventDefault();
            runSummaryStats();
        });
    }

    if ($('#stats-supplier-form').length != 0) {
        runSupplierStats();

        $('#stats-supplier-form').submit(function(event) {
            event.preventDefault();
            runSupplierStats();
        });
    }
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
        } else {
            $.ajax({
                url: '/help/data.md',
                method: 'GET',

                success: function(data) {
                    var renderer = new marked.Renderer();
                    var container = null;
                    var nodes = null;
                    var inner_text = '';

                    /*
                    	Qui abuso del renderer Markdown per
                    	filtrare i contenuti del file ed
                    	assegnarli ai vari elementi sulla pagina
                    */

                    renderer.heading = function(text, level) {
                        inner_text = helpFillNode(nodes, inner_text);

                        if (level == 2)
                            container = $(text);
                        else if (level == 1)
                            nodes = container.find(':contains(' + text + ')').last();
                    };
                    renderer.paragraph = function(text, level) {
                        if (inner_text != '')
                            inner_text += '<br/>';
                        inner_text += text;
                    };
                    renderer.list = function(text, level) {
                        inner_text += '<ul>' + text + '</ul>';
                    };

                    marked(data, {
                        renderer: renderer
                    }, function() {
                        inner_text = helpFillNode(nodes, inner_text);
                    });
                }
            });
        }

        $(this).toggleClass('active');
        return false;
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

    $(document).ajaxComplete(function(event) {
        generalInit();
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

            content.slideUp(500, function() {
                $(this).remove();
                item.removeClass('active');
            });
        }
        else {
            var node = $('<li>').addClass('list-group-item').addClass('loadable-contents').attr('data-random-identifier', randomString(10)).append(loadingPlaceholder());
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
    });

    $('body').on('click', '.reloader', function(event) {
        var listid = $(this).attr('data-reload-target');
        var list = $(listid);

        /*
            Nel caso in cui il tasto sia dentro ad un modale, qui ne forzo la
            chiusura (che non e' implicita, se questo non viene fatto resta
            l'overlay grigio in sovraimpressione)
        */
        var modal = $(this).closest('.modal').first();
        if (modal != null)
            modal.modal('hide');

        var activated = list.find('a.loadable-item.active');
        activated.each(function() {
            var r = $(this);
            r.click();
            setTimeout(function() {
                r.click();
            }, 600);
        });
    });

    $('body').on('shown.bs.modal', '.modal', function() {
        $(this).find('[data-default-value]').each(function() {
            var value = $(this).attr('data-default-value');
            $(this).val(value);
        });

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
        form.find('.main-form-buttons button').attr('disabled', 'disabled');

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
                var h = closeMainForm(form);
                h.empty().append(data.header).attr('href', data.url);
                afterListChanges(h.closest('.loadablelist'));
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
        save_button.text('Attendere').attr('disabled', 'disabled');

        $.ajax({
            method: method,
            url: form.attr('action'),
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json',

            success: function(data) {
                save_button.text('Salvato!');
                setInterval(function() {
                    save_button.text('Salva').removeAttr('disabled');
                }, 2000);

                miscInnerCallbacks(form, data);
            }
        });
    });

    $('body').on('submit', '.creating-form', function(event) {
        if (event.isDefaultPrevented())
            return;

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

    $('body').on('change', '.measure-selector', function(event) {
        enforceMeasureDiscrete($(this));
    });

    $('body').on('change', '.contacts-selection select', function() {
        $(this).closest('.row').find('input:text').attr('class', '').addClass('form-control').addClass($(this).find('option:selected').val());
    });

    $('body').on('focus', 'input.address', function() {
        $(this).popover({
            content: function() {
                var input = $(this);

                var ret = $('<div>\
                    <div class="form-group">\
                        <label for="street" class="col-sm-4 control-label">Indirizzo</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="street" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <label for="city" class="col-sm-4 control-label">Città</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="city" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <label for="cap" class="col-sm-4 control-label">CAP</label>\
                        <div class="col-sm-8"><input type="text" class="form-control" name="cap" value="" autocomplete="off"></div>\
                    </div>\
                    <div class="form-group">\
                        <div class="col-sm-8 col-sm-offset-4"><button class="btn btn-default">Annulla</button> <button class="btn btn-success">Salva</button></div>\
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
            template: '<div class="popover address-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
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
            url: '/movements/create',
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
    })
    /*
        In Bootstrap i button group disabilitati non vengono visualizzati come
        selezionati, dunque per rappresentare comunque il comportamento dei tipi
        movimento di sistema pur senza permettere di modificarli ne inibisco qui
        gli eventi di click
    */
    .on('click', '.movement-type-editor table.system-type label, .movement-type-editor table.system-type input', function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('body').on('click', '.form-filler button[type=submit]', function(event) {
        event.preventDefault();
        form = $(this).closest('.form-filler');
        var data = form.find('input, select').serialize();
        var target = $(form.attr('data-fill-target'));

        $.ajax({
            method: 'GET',
            url: form.attr('data-action'),
            data: data,
            dataType: 'html',

            success: function(data) {
                target.empty().append(data);
            }
        });
    });

    $('body').on('submit', '.password-protected', function(event) {
        if ($(this).attr('data-password-protected-verified') != '1') {
            event.preventDefault();
            var id = $(this).attr('id');
            var modal = $('#password-protection-dialog');
            modal.attr('data-form-target', '#' + id);
            modal.find('input:password').val();
            modal.modal('show');
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
                        success: function(data) {
                            miscInnerCallbacks(form, data);
                            form.attr('data-password-protected-verified', '0');
                        }
                    });
                }
            }
        });
    });

    /*
    	Gestione ordini
    */

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
    });

    /*
    	Aggregazione ordini
    */

    $('#orderAggregator form').submit(function(e) {
        e.preventDefault();
        var form = $(this);

        var data = new Array();

        form.find('ul').each(function() {
            var a = {
                id: $(this).attr('data-aggregate-id'),
                orders: new Array()
            };

            $(this).find('li').each(function() {
                a.orders.push($(this).attr('data-order-id'));
            });

            data.push(a);
        });

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: {
                data: JSON.stringify(data)
            },
            dataType: 'json',

            success: function(data) {
                location.reload();
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

        $.ajax({
            url: '/aggregates/notify/' + id,
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
        else if (tab.hasClass('fast-shippable-bookings')) {
            var id = tab.closest('.aggregate-bookings').find('input:hidden[name=aggregate_id]').val();
            tab.empty().append(loadingPlaceholder());

            $.ajax({
                method: 'GET',
                url: '/deliveries/' + id + '/fast',
                dataType: 'html',

                success: function(data) {
                    tab.empty().append(data);
                }
            });
        }
    });

    $('body').on('keyup', '.booking-product-quantity input', function() {
        var booked = 0;
        var wrong = false;

        var row = $(this).closest('.booking-product');
        row.find('.booking-product-quantity input').each(function() {
            var v = $(this).val();
            if (v != '')
                booked += parseFloatC(v);
        });

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

                if (maximum != 0) {
                    var in_booked = booked;

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
                url: '/products/' + id,
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

    $('body').on('click', '.booking-form .info-button', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        form.find('input:hidden[name=action]').val('saved');
        form.submit();
    });

    $('body').on('click', '.inline-calculator button[type=submit]', function(e) {
        e.preventDefault();
        var modal = $(this).closest('.modal');
        var quantity = 0;

        modal.find('input[type=number]').each(function() {
            var v = $(this).val();
            if (v != '')
                quantity += parseFloatC(v);

            $(this).val('0');
        });

        /*
            Il trigger keyup() alla fine serve a forzare il ricalcolo del totale
            della consegna quando il modale viene chiuso
        */
        modal.closest('.booking-product-quantity').find('input[type=number]').first().val(quantity).keyup();
        modal.modal('hide');
    });

    $('body').on('click', '.delete-booking', function(e) {
        e.preventDefault();

        var form = $(this).closest('.inner-form');

        if (confirm('Sei sicuro di voler annullare questa prenotazione?')) {
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
        Configurazioni GAS
    */

    $('.gas-editor').on('change', 'input[name=mailaddress]', function() {
        var email = $(this).val();
        var panel = $(this).closest('.well');

        panel.find('input').prop('disabled', true);

        $.ajax({
            method: 'GET',
            url: '/gas/configmail',
            data: {
                email: email
            },
            dataType: 'JSON',

            success: function(data) {
                panel.find('input').prop('disabled', false);

                if (data.hasOwnProperty('hostname')) {
                    panel.find('input[name=mailusername]').val(data.username);
                    panel.find('input[name=mailserver]').val(data.hostname);
                    panel.find('input[name=mailport]').val(data.port);

                    var val = '';

                    switch(data.socketType) {
                        case 'SSL':
                            val = 'ssl';
                            break;
                        case 'STARTTLS':
                            val = 'tls';
                    }

                    panel.find('select[name=mailssl] option[value=' + val + ']').prop('selected', true);
                }
            },
            error: function() {
                panel.find('input').prop('disabled', false);
            }
        });
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

    }).on('click', '.add-many-rows', function(event) {
        event.preventDefault();
        var container = $(this).closest('.many-rows');
        var row = container.find('.row:not(.many-rows-header)').first().clone();
        container.find('.add-many-rows').before(row);
        manyRowsInitRow(row, true);
        manyRowsAddDeleteButtons(container);
        return false;
    });

    /*
    	Widget albero gerarchico dinamico
    */

    $('body').on('click', '.dynamic-tree .dynamic-tree-remove', function() {
        $(this).closest('li').remove();

    }).on('click', '.dynamic-tree-box .dynamic-tree-add', function(e) {
        e.preventDefault();
        var box = $(this).closest('.dynamic-tree-box');
        var input = box.find('input[name=new_category]');
        var name = input.val();
        var tree = box.find('.dynamic-tree');

        tree.append('<li class="list-group-item"><div><span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove"></span></span><input name="names[]" class="form-control" value="' + name + '"></div><ul></ul></li>');
        tree.nestedSortable('refresh');

        input.val('');

        return false;

    }).on('submit', '.dynamic-tree-box', function(e) {
        e.preventDefault();
        var box = $(this);
        var tree = box.find('.dynamic-tree');
        var data = parseDynamicTree(tree);

        $.ajax({
            method: box.attr('method'),
            url: box.attr('action'),
            data: {
                serialized: data
            },
        });

        return false;
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

    $('body').on('submit', '.modal form', function(event) {
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

    setupHelp();
    setupStatisticsForm();
});
