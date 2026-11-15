import utils from "./utils";

class Modifiers {
    static modifiers_strings = null;

    static getConfigValue(container, name)
    {
        let checked = container.find('input:radio[name=' + name + ']:checked').first();
        if (checked) {
            return checked.val();
        }
        else {
            return container.find('[name=' + name + ']').val();
        }
    }

    static async init(container)
    {
        if (container.hasClass('modifier-modal')) {
            const form = container.find('form');
            utils.spinBlock(form);

            await utils.postAjax({
                method: 'GET',
                url: container.attr('data-strings-source'),
                dataType: 'JSON',
                success: function(data) {
                    Modifiers.modifiers_strings = data;

                    const form = container.find('form');
                    utils.unspinBlock(form);
                }
            });

            container.on('change', 'input:radio', function() {
                /*
                    L'ordine degli elementi usati per costruire l'indice delle stringhe
                    deve combaciare con quello in modifier/edit.blade.php
                */
                let container = $(this).closest('.modifier-modal');
                let model_type = container.attr('data-target-type');
                let arithmetic = Modifiers.getConfigValue(container, 'arithmetic');
                let value = Modifiers.getConfigValue(container, 'value');
                let applies_type = Modifiers.getConfigValue(container, 'applies_type');
                let scale = Modifiers.getConfigValue(container, 'scale');
                let applies_target = Modifiers.getConfigValue(container, 'applies_target');
                let distribution_type = Modifiers.getConfigValue(container, 'distribution_type');
                let distribution_type_selection = container.find('.distribution_type_selection');

                if (model_type == 'product') {
                    if (applies_type == 'none') {
                        container.find('input:radio[name=applies_target][value=product]').click();
                        container.find('input:radio[name=applies_target][value!=product]').next('label').addClass('disabled');
                        applies_target = 'product';

                        container.find('input:radio[name=value][value=absolute]').click();
                        container.find('input:radio[name=value][value=price]').next('label').addClass('disabled');
                        value = 'absolute';
                    }
                    else {
                        container.find('input:radio[name=applies_target]').next('label').removeClass('disabled');
                    }
                }

                if (value == 'price') {
                    container.find('.arithmetic_type_selection').addClass('d-none').find('input:radio[value=apply]').click();
                    arithmetic = 'apply';
                    container.find('.distribution_type_selection').addClass('d-none').find('input:radio[value=none]').click();
                    distribution_type = 'none';
                }
                else {
                    container.find('.arithmetic_type_selection').removeClass('d-none');

                    if (arithmetic == 'apply') {
                        container.find('.arithmetic_type_selection').find('input:radio[value=sum]').click();
                        arithmetic = 'sum';
                    }

                    if (applies_target != 'order' && applies_target != 'order_aggregate') {
                        distribution_type_selection.addClass('d-none').find('input:radio[value=none]').click();
                        distribution_type = 'none';
                    }
                    else {
                        distribution_type_selection.removeClass('d-none');
                        if (distribution_type == 'none') {
                            distribution_type_selection.find('input:radio[value=quantity]').click();
                            distribution_type = 'quantity';
                        }
                    }
                }

                let key = applies_type + ',' + model_type + ',' + applies_target + ',' + scale + ',' + applies_type + ',' + arithmetic + ',' + applies_target + ',' + value + ',' + distribution_type;
                let labels = Modifiers.modifiers_strings[key];

                let simplified = container.find('.simplified_input');
                let advanced = container.find('.advanced_input');
                simplified.toggleClass('d-none', applies_type != 'none');
                advanced.toggleClass('d-none', applies_type == 'none');

                if (applies_type != 'none') {
                    container.find('input:radio[name=value][value=price]').next('label').removeClass('disabled');
                    let table = advanced.find('.dynamic-table');

                    table.find('tr').each(function() {
                        if ($(this).find('.add-row').length != 0) {
                            return true;
                        }

                        $(this).find('td:nth-child(1) .form-control-plaintext').text(labels[0]);
                        $(this).find('td:nth-child(2) .input-group-text').text(labels[1]);
                        $(this).find('td:nth-child(3) .form-control-plaintext').text(labels[2]);
                        $(this).find('td:nth-child(4) .input-group-text').text(labels[3]);
                        $(this).find('td:nth-child(5) .form-control-plaintext').text(labels[4]);
                    });

                    if (table.find('tbody tr').length == 1) {
                        table.find('.add-row').click();
                    }
                }
                else {
                    simplified.find('.form-control-static').eq(0).text(labels[2]);
                    simplified.find('.input-group-text').text(labels[3]);
                    simplified.find('.form-control-static').eq(1).text(labels[4]);
                }
            });

            container.find('input:radio').change();
        }
    }

    static updateBookingModifiers(dynamic_modifiers, container)
    {
        $('input[name^="modifier-"]', container).each(function() {
            let modid = Number.parseInt($(this).attr('name').split('-')[1]);
            if (modid == 0) {
                return;
            }

            for (let [modifier_id, modifier_meta] of Object.entries(dynamic_modifiers)) {
                if (modid == modifier_id) {
                    $(this).parent().find('span').text(utils.priceRound(modifier_meta.amount));
                    delete dynamic_modifiers[modifier_id];
                    return;
                }
            }

            $(this).closest('.modifier-row').remove();
        });

        for (let [modifier_id, modifier_meta] of Object.entries(dynamic_modifiers)) {
            let template = $('.modifier-row.hidden', container);
            let new_row = template.clone();

            new_row.removeClass('hidden').find('.name').text(modifier_meta.label);

            if (modifier_meta.variable) {
                new_row.find('.mutable').removeClass('hidden');
            }

            if (modifier_meta.url != '') {
                new_row.find('.details-button-wrapper').empty().append(utils.detailsButton(modifier_meta.url));
            }

            new_row.find('input[name="modifier-0"]').attr('name', 'modifier-' + modifier_id).parent().find('span').text(utils.priceRound(modifier_meta.amount));
            template.before(new_row);
            utils.j().initElements(new_row);
        }
    }
}

export default Modifiers;
