import utils from "./utils";

class Triggers {
    static init(container)
    {
        $('.collapse_trigger', container).each((index, item) => {
            this.triggerCollapse($(item));
        }).change((e) => {
            this.triggerCollapse($(e.currentTarget));
        });

        $('.triggers-all-checkbox', container).change(function() {
            $(this).prop('disabled', true);

            var form = $(this).closest('form');
            var target = $(this).attr('data-target-class');
            var new_status = $(this).prop('checked');

            form.find('.' + target).each(function() {
                $(this).prop('checked', new_status);
            });

            $(this).prop('disabled', false);
        })

        $('.triggers-all-radio label', container).click(function() {
            var form = $(this).closest('form');
            var target = $(this).attr('data-target-class');
            form.find('.' + target).button('toggle');
        })

        $('.triggers-all-selects', container).change(function() {
            var form = $(this).closest('form');
            var target = $(this).attr('data-target-class');
            var value = $(this).find('option:selected').val();
            var t = form.find('.' + target).not($(this));
            t.find('option[value=' + value + ']').prop('selected', true);
            t.change();
        });
    }

    static triggerCollapse(trigger)
    {
        var name = trigger.attr('name');
        var display = trigger.prop('checked');

        /*
            L'evento show.bs.collapse va espressamente bloccato, altrimenti se
            il widget si trova all'interno di una accordion risale fino a quella
            e scattano anche le callback per le async-accordion
        */

        $('.collapse[data-triggerable=' + name + ']').one('show.bs.collapse', function(e) {
            e.stopPropagation();
        }).collapse(display ? 'show' : 'hide').find('.required_when_triggered').prop('required', display);

        $('.collapse[data-triggerable-reverse=' + name + ']').one('show.bs.collapse', function(e) {
            e.stopPropagation();
        }).collapse(display ? 'hide' : 'show').find('.required_when_triggered').prop('required', display == false);

        var panel = null;

        if (display) {
            panel = $('.collapse[data-triggerable=' + name + ']');
        }
        else {
            panel = $('.collapse[data-triggerable-reverse=' + name + ']');
        }

        utils.reviewRequired(panel);
    }
}

export default Triggers;
