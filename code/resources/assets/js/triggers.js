import utils from "./utils";

class Triggers {
    static init(container)
    {
        $('.triggers-all-checkbox', container).change(function() {
            $(this).prop('disabled', true);

            let form = $(this).closest('form');
            let target = $(this).attr('data-target-class');
            let new_status = $(this).prop('checked');

            form.find('.' + target).filter(':visible').each(function() {
                $(this).prop('checked', new_status).change();
            });

            $(this).prop('disabled', false);
        });

        $('table thead .toggleall', container).change(function() {
            let col = $(this).closest('th').index();
            let value = $(this).val();

            let is_radio = ($(this).attr('type') == 'radio');
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
            let form = $(this).closest('form');
            let target = $(this).attr('data-target-class');
            let value = $(this).find('option:selected').val();
            let t = form.find('.' + target).not($(this));
            t.find('option[value=' + value + ']').prop('selected', true);
            t.change();
        });

        $('.async-modal', container).on('jb-before-async-fetch', (e) => {
            let node = $(e.currentTarget);
            let spinner = $(utils.j().makeSpinner());
            spinner.find('.spinner-border').addClass('spinner-border-sm');
            let contents = node.html();
            node.attr('data-old-contents', contents);
            node.css('width', node.outerWidth()).prop('disabled', true).empty().append(spinner);
        }).on('jb-after-async-fetch', (e, success) => {
            let node = $(e.currentTarget);
            let contents = node.attr('data-old-contents');
            node.css('width', 'auto').prop('disabled', false).empty().append(contents);

            if (success == false) {
                utils.inlineFeedback(node, _('ERRORE!'));
            }
        });
    }
}

export default Triggers;
