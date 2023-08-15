import utils from "./utils";

$(document).ready(function() {
    function complexPopover(input, type, content)
    {
        /*
            Questo è indispensabile per gestire il popover quando si trova
            all'interno di un modale (e.g. l'indirizzo di un Luogo di Consegna
            in fase di creazione). Altrimenti il popover viene appeso al body,
            ed il focus sugli input field viene prevenuto dagli eventi interni
            di Bootstrap sui modali
        */
        var container = input.closest('.modal');
        if (container.length == 0) {
            container = false;
        }

        input.popover({
            container: container,
            template: '<div class="popover ' + type + '-popover" role="tooltip"><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
            placement: 'left',
            html: true,
            content: content(input),
        });
    }

    $('body').on('focus', 'input.address', function() {
        complexPopover($(this), 'address', function(input) {
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
        complexPopover($(this), 'periodic', function(input) {
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
                language: utils.currentLanguage(),
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
});
