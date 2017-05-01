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
        clearBtn: true,
    });

    $('.tagsinput').tagsinput();
    $('.addicted-table').bootstrapTable();
    $('[data-toggle="popover"]').popover();

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
        manyRowsAddDeleteButtons($(this));
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
                $.get('/booking/' + aggregate_id + '/user/' + result.id, function(form) {
                    $('.other-booking').empty().append(form);
                });
            });
        }
    });

    $('.modal.dynamic-contents').on('show.bs.modal', function(e) {
        if (typeof $.data(e.target, 'dynamic-inited') == 'undefined') {
            $.data(e.target, 'dynamic-inited', {
                done: true
            });

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
    setupPermissionsWidget();
    setupPermissionsEditor();
    testListsEmptiness();
}

function filteredSerialize(form) {
    return $(':not(.skip-on-submit)', form).serializeArray();
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

function setCellValue(cell, value) {
    string = value;
    if (cell.text().indexOf('€') != -1)
        string = priceRound(value) + ' €';
    cell.text(string);
}

/*
    Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
    un attributo, questa funzione serve ad applicare l'escape necessario
*/
function sanitizeId(identifier)
{
    return identifier.replace(/:/, '\\:');
}

function voidForm(form) {
    form.find('input[type!=hidden]').val('');
    form.find('textarea').val('');
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
    var fields = node.find('.row:not(.many-rows-header)');
    if (fields.length > 1 && node.find('.delete-many-rows').length == 0) {
        fields.each(function() {
            var button = '<div class="btn btn-danger delete-many-rows"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>';
            $(this).append(button);
        });
    } else if (fields.length == 1) {
        node.find('.delete-many-rows').remove();
    }
}

function testListsEmptiness() {
    $('.loadablelist').each(function() {
        var id = $(this).attr('id');
        var c = $(this).find('a').length;
        var alert = $('#empty-' + id);

        if (c == 0)
            alert.removeClass('hidden');
        else
            alert.addClass('hidden');
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
        var node = {
            id: unparsed_node.id,
            name: unparsed_node.text.replace(/<[^>]*>?/g, '')
        };
        node.children = parseDynamicTree(unparsed_node.children);
        data.push(node);
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
        list.append('<a data-element-id="' + data.id + '" href="' + data.url + '" class="loadable-item list-group-item">' + data.header + '</a>');
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

    var test = form.find('input[name=post-saved-function]');
    if (test.length != 0) {
        test.each(function() {
            var fn = window[$(this).val()];
            if (typeof fn === 'function')
                fn(form);
        });
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
	Prenotazioni / Consegne
*/

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

        var partitioning = $(this).find('input:hidden[name=product-partitioning]').val();
        partitioning = parseFloatC(partitioning);

        var quantity = 0;

        $(this).find('.booking-product-quantity').each(function() {
            var input = $(this).find('input');

            var q = input.val();
            if (q == '')
                q = 0;
            else
                q = parseFloatC(q);

            if (partitioning != 0)
                q = q * partitioning;

            if ($(this).hasClass('booking-variant-quantity')) {
                var offset = $(this).closest('.inline-variant-selector').find('select option:selected').attr('data-variant-price');
                current_price = price + parseFloatC(offset);
            } else {
                current_price = price;
            }

            total_price += current_price * q;
        });
    });

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

    form.find('.all-bookings-total').text(grand_total);

    /*
    	Qui aggiorno il valore totale della prenotazione nel (eventuale)
    	modale per il pagamento
    */
    var payment_modal_id = form.attr('data-reference-modal');
    var payment_modal = $('#' + payment_modal_id);

    if (payment_modal.length != 0) {
        payment_modal.find('input[name=amount]').val(grand_total);
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

function submitDeliveryForm(form) {
    var id = form.closest('.modal').attr('id');
    $('form[data-reference-modal=' + id + ']').submit();
}

function updateOrderSummary(form) {
    var main_form = form.parents('.loadable-contents').last();
    main_form.find('.order-editor input[name=id]').each(function() {
        var order_id = $(this).val();
        $.ajax({
            method: 'GET',
            url: '/orders/' + order_id,
            dataType: 'json',

            success: function(data) {
                var summary = main_form.find('.order-editor input[name=id][value="' + data.order + '"]').closest('.order-editor').find('.order-summary');

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
	Permessi
*/

function setupPermissionsWidget() {

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

    }).on('change', 'input:checkbox', function(e) {
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
            item.next().slideUp(500, function() {
                $(this).remove();
                item.removeClass('active');
            });
        }
        else {
            $(this).find('a').removeClass('active');
            var node = $('<li>').addClass('list-group-item').addClass('loadable-contents').attr('data-random-identifier', randomString(10)).append(loadingPlaceholder());
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

    $('.list-filter').on('submit', 'form', function(e) {
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

    }).on('change', 'input, select', function() {
        $(this).closest('form').submit();

    }).on('show.bs.collapse', function() {
        $(this).find('form').submit();

    }).on('click', '.btn-danger', function(e) {
        e.preventDefault();
        var panel = $(this).closest('.list-filter');
        var form = panel.find('form');

        var targetid = panel.attr('data-list-target');
        var target = $(targetid);
        target.empty().append(loadingPlaceholder());

        panel.collapse('hide');

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: {},
            dataType: 'html',

            success: function(data) {
                target.replaceWith(data);
            }
        });
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
        setTimeout(function() {
            var activated = list.find('a.loadable-item.active');
            activated.each(function() {
                $(this).click().delay(600).click();
            });
        }, 500);
    });

    $('body').on('focus', '.date[data-enforce-after]', function() {
        var select = $(this).attr('data-enforce-after');
        var target = $(this).closest('form').find(select);
        $(this).datepicker('setStartDate', target.val());
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

    $('body').on('submit', '.inner-form', function(event) {
        event.preventDefault();
        var form = $(this);
        var data = filteredSerialize(form);
        var save_button = form.find('.saving-button');

        save_button.text('Attendere').attr('disabled', 'disabled');

        $.ajax({
            method: form.attr('method'),
            url: form.attr('action'),
            data: data,
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

    $('body').on('change', '.measure-selector', function(event) {
        enforceMeasureDiscrete($(this));
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

    $('body').on('submit', '.form-filler', function(event) {
        event.preventDefault();
        var form = $(this);
        var data = form.serializeArray();
        var target = $(form.attr('data-fill-target'));

        $.ajax({
            method: 'GET',
            url: form.attr('action'),
            data: data,
            dataType: 'json',

            success: function(data) {
                target.empty().append(data);
            }
        });
    });

    /*
    	Gestione ordini
    */

    $('body').on('change', '.order-summary tr .enabling-toggle', function() {
        $(this).closest('tr').toggleClass('product-disabled');
    });

    $('body').on('change', '.order-summary tr .discount-toggle', function() {
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
                if (maximum != 0 && booked > maximum) {
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
                var booked = $(this).find('.booking-product-booked');
                if (booked.length != 0) {
                    var input = $(this).find('.booking-product-quantity input');
                    input.val(booked.text());
                }
            });

            bookingTotal($(this));
        });

        return false;
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
                    updateOrderSummary(form);
                }
            });
        }

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

    }).on('click', '.add-many-rows', function(event) {
        event.preventDefault();
        var container = $(this).closest('.many-rows');
        var row = container.find('.row:not(.many-rows-header)').first().clone();
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

    $(document).on('dnd_stop.vakata', function(e) {
        $('.dynamic-tree').jstree().open_all();
    });

    $('body').on('click', '.dynamic-tree .dynamic-tree-remove', function() {
        $(this).closest('li').remove();

    }).on('click', '.dynamic-tree-box .dynamic-tree-add', function(e) {
        e.preventDefault();
        var box = $(this).closest('.dynamic-tree-box');
        var input = box.find('input[name=new_category]');
        var name = input.val();
        var tree = box.find('.dynamic-tree');

        tree.jstree().create_node(null, {
            text: name + '<span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove" aria-hidden="true"></span></span>',
            li_attr: {
                class: 'list-group-item jstree-open'
            }
        });
        input.val('');

        return false;

    }).on('submit', '.dynamic-tree-box', function(e) {
        e.preventDefault();
        var box = $(this);
        var tree = box.find('.dynamic-tree');
        var unparsed_data = tree.jstree().get_json();
        var data = parseDynamicTree(unparsed_data);

        $.ajax({
            method: box.attr('method'),
            url: box.attr('action'),
            data: {
                serialized: data
            },
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

    }).on('submit', '.wizard_page form', function(e) {
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

    setupHelp();
    setupStatisticsForm();
});
