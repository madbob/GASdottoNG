import utils from "./utils";

function complexPopover(input, type, content)
{
    /*
        Questo è indispensabile per gestire il popover quando si trova
        all'interno di un modale (e.g. l'indirizzo di un Luogo di Consegna
        in fase di creazione). Altrimenti il popover viene appeso al body,
        ed il focus sugli input field viene prevenuto dagli eventi interni
        di Bootstrap sui modali
    */
    let container = input.closest('.modal');
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

$(document).ready(function() {
    $('body').on('focus', 'input.address', function() {
        complexPopover($(this), 'address', function(input) {
            let ret = $(`<div>
                <div class="row mb-2">
                    <label for="street" class="col-4 col-form-label">${_('texts.user.address_elements.street')}</label>
                    <div class="col-8"><input type="text" class="form-control" name="street" value="" autocomplete="off"></div>
                </div>
                <div class="row mb-2">
                    <label for="city" class="col-4 col-form-label">${_('texts.user.address_elements.city')}</label>
                    <div class="col-sm-8"><input type="text" class="form-control" name="city" value="" autocomplete="off"></div>
                </div>
                <div class="row mb-2">
                    <label for="cap" class="col-4 col-form-label">${_('texts.user.address_elements.zip')}</label>
                    <div class="col-sm-8"><input type="text" class="form-control" name="cap" value="" autocomplete="off"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-8 offset-4"><button class="btn btn-light">${_('texts.generic.cancel')}</button> <button class="btn btn-success">${_('texts.generic.save')}</button></div>
                </div>
            </div>`);

            let value = input.val();
            if (value != '') {
                let values = value.split(',');
                for(let i = values.length; i < 3; i++) {
                    values[i] = '';
                }

                ret.find('input[name=street]').val(values[0].trim());
                ret.find('input[name=city]').val(values[1].trim());
                ret.find('input[name=cap]').val(values[2].trim());
            }

            ret.find('button.btn-success').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                let street = ret.find('input[name=street]').val().trim().replace(',', '');
                let city = ret.find('input[name=city]').val().trim().replace(',', '');
                let cap = ret.find('input[name=cap]').val().trim().replace(',', '');

                if (street == '' && city == '' && cap == '') {
                    input.val('');
                }
                else {
                    input.val(street + ', ' + city + ', ' + cap);
                }

                input.change();
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
            let ret = $(`<div>
                <div class="row mb-2">
                <label for="day" class="col-4 col-form-label">${_('texts.generic.day')}</label>
                    <div class="col-8">
                        <select class="form-select" name="day" value="" autocomplete="off">
                            <option value="monday">${_('texts.generic.days.monday')}</option>
                            <option value="tuesday">${_('texts.generic.days.tuesday')}</option>
                            <option value="wednesday">${_('texts.generic.days.wednesday')}</option>
                            <option value="thursday">${_('texts.generic.days.thursday')}</option>
                            <option value="friday">${_('texts.generic.days.friday')}</option>
                            <option value="saturday">${_('texts.generic.days.saturday')}</option>
                            <option value="sunday">${_('texts.generic.days.sunday')}</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                <label for="cycle" class="col-4 col-form-label">${_('texts.notifications.cycle_param')}</label>
                    <div class="col-8">
                        <select class="form-select" name="cycle" value="" autocomplete="off">
                            <option value="all">${_('texts.generic.all')}</option>
                            <option value="biweekly">${_('texts.notifications.cycle.two_weeks')}</option>
                            <option value="month_first">${_('texts.notifications.cycle.first_of_month')}</option>
                            <option value="month_second">${_('texts.notifications.cycle.second_of_month')}</option>
                            <option value="month_third">${_('texts.notifications.cycle.third_of_month')}</option>
                            <option value="month_fourth">${_('texts.notifications.cycle.fourth_of_month')}</option>
                            <option value="month_last">${_('texts.notifications.cycle.last_of_month')}</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2">
                <label for="day" class="col-4 col-form-label">${_('texts.generic.since')}</label>
                    <div class="col-8"><input type="text" class="date form-control" name="from" value="" autocomplete="off"></div>
                </div>
                <div class="row mb-2">
                <label for="day" class="col-4 col-form-label">${_('texts.generic.to')}</label>
                    <div class="col-8"><input type="text" class="date form-control" name="to" value="" autocomplete="off"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-8 offset-4"><button class="btn btn-light">${_('texts.generic.cancel')}</button> <button class="btn btn-success">${_('texts.generic.save')}</button></div>
                </div>
            </div>`);

            $('input.date', ret).datepicker({
                format: 'DD dd MM yyyy',
                autoclose: true,
                language: utils.currentLanguage(),
                clearBtn: true,
            });

            let value = input.val();
            if (value != '') {
                let values = value.split(' - ');
                for(let i = values.length; i < 4; i++) {
                    values[i] = '';
                }

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
                let day = ret.find('select[name=day] option:selected').text();
                let cycle = ret.find('select[name=cycle] option:selected').text();
                let from = ret.find('input[name=from]').val().trim().replace(',', '');
                let to = ret.find('input[name=to]').val().trim().replace(',', '');
                input.val(day + ' - ' + cycle + ' - ' + from + ' - ' + to).change();
                input.change();
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

        let row = $(this).closest('tr');
        if ($(this).hasClass('date')) {
            row.find('.periodic').val('');
        }
        else {
            row.find('.date').val('');
        }
    });
});
