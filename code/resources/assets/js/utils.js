import jBob from "./jbob";
import Lists from "./lists";

class Utils {
    static init(container)
    {
        $('.reloader', container).click(function(e) {
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
                        Lists.reloadCurrentLoadable(listid);
                    });
                    modal.modal('hide');
                }
                else {
                    Lists.reloadCurrentLoadable(listid);
                }
            }
        });

        $('.date[data-enforce-after]', container).each((index, item) => {
            var current = $(item);
            var target = this.dateEnforcePeer(current);

            target.datepicker().on('changeDate', function() {
                var current_start = current.datepicker('getDate');
                var current_ref = target.datepicker('getDate');
                if (current_start < current_ref) {
                    current.datepicker('setDate', current_ref);
                }
            });
        }).focus((e) => {
            var target = this.dateEnforcePeer($(e.currentTarget));

            /*
                Problema: cercando di navigare tra i mesi all'interno del datepicker
                viene lanciato nuovamente l'evento di focus, che fa rientrare in
                questa funzione, e se setStartDate() viene incondazionatamente
                eseguita modifica a sua volta la data annullando l'operazione.
                Dunque qui la eseguo solo se non l'ho già fatto (se la data di
                inizio forzato non corrisponde a quel che dovrebbe essere), badando
                però a fare i confronti sui giusti formati
            */
            var current_start = $(e.currentTarget).datepicker('getStartDate');
            var current_ref = target.datepicker('getUTCDate');
            if (current_start.toString() != current_ref.toString()) {
                $(e.currentTarget).datepicker('setStartDate', current_ref);
            }
        });

        $('.select-fetcher', container).change((e) => {
            var fetcher = $(e.currentTarget);
            var targetid = fetcher.attr('data-fetcher-target');
            var target = fetcher.parent().find(targetid);
            target.empty().append(this.loadingPlaceholder());

            var id = fetcher.find('option:selected').val();
            var url = fetcher.attr('data-fetcher-url').replace('XXX', id);

            $.get(url, function(data) {
                target.empty().append(data);
            });
        });

        $('.object-details', container).click((e) => {
            var url = $(e.currentTarget).attr('data-show-url');
            var modal = $('#service-modal');
            modal.find('.modal-body').empty().append(this.loadingPlaceholder());
            modal.modal('show');

            this.postAjax({
                url: url,
                method: 'GET',
                dataType: 'HTML',
                success: (data) => {
                    data = $(data);
                    modal.find('.modal-body').empty().append(data);
                    this.j().initElements(data);
                }
            });
        });

        $('input[data-alternative-required]', container).change((e) => {
            this.reviewRequired($(e.currentTarget).closest('form'));
        });

        $('.link-button', container).click(function(e) {
            e.preventDefault();
            var url = $(this).attr('data-link');
            window.open(url, '_blank');
        });
    }

    static j()
    {
        if (typeof Utils.jbob == 'undefined') {
            Utils.jbob = new jBob();
        }

        return Utils.jbob;
    }

    static absoluteUrl()
    {
        if (typeof Utils.absolute_url == 'undefined') {
            Utils.absolute_url = $('meta[name=absolute_url]').attr('content');
            console.log(Utils.absolute_url);
        }

        return Utils.absolute_url;
    }

    static loadingPlaceholder()
    {
        return $('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
    }

    static inlineFeedback(button, feedback_text)
    {
        var idle_text = button.attr('data-idle-text');
        if (!idle_text) {
            idle_text = button.text();
        }

        button.text(feedback_text);
        setTimeout(function() {
            button.text(idle_text).prop('disabled', false);
        }, 2000);
    }

    static displayServerError(form, data)
    {
        if (data.target != '') {
            Utils.inlineFeedback(form.find('button[type=submit]'), 'Errore!');
            var input = form.find('[name=' + data.target + ']');
            Utils.setInputErrorText(input, data.message);
        }
    }

    static detailsButton(url)
    {
        return '<button type="button" class="btn btn-xs btn-info object-details d-none d-md-inline-block" data-show-url="' + url + '"><i class="bi-zoom-in"></i></button>';
    }

    static complexPopover(input, type, content)
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

    static setInputErrorText(input, message)
    {
        if (message == null) {
            input.closest('.form-group').removeClass('has-error');
            input.closest('div').find('.help-block.error-message').remove();
        }
        else {
            input.closest('.form-group').addClass('has-error');
            input.closest('div').append('<span class="help-block error-message">' + message + '</span>');
        }
    }

    static randomString(total)
    {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for( var i = 0; i < total; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    static parseFullDate(string)
    {
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

    static dateEnforcePeer(node)
    {
        var select = node.attr('data-enforce-after');
        var target = node.closest('.input-group').find(select);
		if (target.length == 0) {
			target = node.closest('form').find(select);
        }

        return target;
    }

    static parseFloatC(value)
    {
        if (typeof value === 'undefined')
            return 0;

        var ret = parseFloat(value.replace(/,/, '.'));
        if (isNaN(ret))
            ret = 0;

        return ret;
    }

    static sel(selector, container)
    {
        var classes = selector.split(' ');
        if (container.is(classes[0])) {
            return container.find(classes.slice(1).join(' '));
        }
        else {
            return $(selector, container);
        }
    }

    static priceRound(price)
    {
        return (Math.round(price * 100) / 100).toFixed(2);
    }

    static postAjax(params)
    {
        params.data = params.data || {};
        params.method = params.method || 'POST';

        if (params.method != 'GET' && params.method != 'POST') {
            params.data._method = params.method;
            params.method = 'POST';
        }

        if (params.url.startsWith('http') == false) {
            params.url = this.absoluteUrl() + '/' + params.url;
        }

        // params.data._token = $('meta[name="csrf-token"]').attr('content');
        $.ajax(params);
    }

    /*
        Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
        un attributo, questa funzione serve ad applicare l'escape necessario
    */
    static sanitizeId(identifier)
    {
        return identifier.replace(/:/g, '\\:').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
    }

    static formByButton(button)
    {
        var parent_form = button.closest('form');

        if (parent_form.length == 0) {
            var id = button.attr('form');
            if (id) {
                parent_form = $('#' + id);
            }
        }

        return parent_form;
    }

    static inputInvalidFeedback(input, is_invalid, message)
    {
        input.toggleClass('is-invalid', is_invalid);
        if (is_invalid == true) {
            input.siblings('.invalid-feedback').text(message);
        }
    }

    /*
        Questo è per gestire campi diversi di cui almeno uno è obbligatorio
    */
    static reviewRequired(panel)
    {
        panel.find('input[data-alternative-required]').each(function() {
            var alternative = $(this).attr('data-alternative-required');
            if (alternative) {
                var alt = panel.find('[name="' + alternative + '"]');
                if (alt.val() != '') {
                    $(this).prop('required', false);
                }
                else {
                    $(this).prop('required', true);
                }
            }
        });
    }

    static submitButton(form)
    {
        let ret = form.find('button[type=submit]');
        if (ret.length == 0) {
            let id = form.attr('id');
            if (id) {
                ret = $('button[type=submit][form=' + id + ']')
            }
        }

        return ret;
    }
}

$.fn.textVal = function(value) {
    if (typeof value == 'undefined') {
        if (this.is('input')) {
            return this.val();
        }
        else {
            return this.text();
        }
    }
    else {
        if (this.is('input')) {
            return this.val(value);
        }
        else {
            return this.text(value);
        }
    }
};

export default Utils;
