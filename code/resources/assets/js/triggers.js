import utils from "./utils";

class Triggers {
    static init(container)
    {
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
}

export default Triggers;
