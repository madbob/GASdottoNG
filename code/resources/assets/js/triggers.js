import utils from "./utils";

class Triggers {
    static init(container)
    {
        $('.triggers-all-checkbox', container).change(function() {
            $(this).prop('disabled', true);

            var form = $(this).closest('form');
            var target = $(this).attr('data-target-class');
            var new_status = $(this).prop('checked');

            form.find('.' + target).filter(':visible').each(function() {
                $(this).prop('checked', new_status).change();
            });

            $(this).prop('disabled', false);
        });

        $('table thead .toggleall', container).change(function() {
            var col = $(this).closest('th').index();
            var value = $(this).val();

            var is_radio = ($(this).attr('type') == 'radio');
            if (is_radio && $(this).prop('checked') == false) {
                return;
            }

            $(this).closest('table').find('tbody tr td:nth-child(' + (col + 1) + ')').each(function() {
                if (is_radio) {
                    $(this).find('input[value=' + value + ']').prop('checked', true);
                }
                else {
                    $(this).find('input').val(value);
                }
            });
        });

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
