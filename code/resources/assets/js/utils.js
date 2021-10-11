import jBob from "./jbob";

class Utils {
    static j()
    {
        if (typeof Utils.jbob == 'undefined') {
            Utils.jbob = new jBob();
        }

        return Utils.jbob;
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

    static parseFloatC(value)
    {
        if (typeof value === 'undefined')
            return 0;

        var ret = parseFloat(value.replace(/,/, '.'));
        if (isNaN(ret))
            ret = 0;

        return ret;
    }

    static priceRound(price)
    {
        return (Math.round(price * 100) / 100).toFixed(2);
    }

    static postAjax(params)
    {
        let absolute_url = $('meta[name=absolute_url]').attr('content');
        params.data = params.data || {};
        params.method = params.method || 'POST';

        if (params.method != 'GET' && params.method != 'POST') {
            params.method = 'POST';
            params.data._method = params.method;
        }

        if (params.url.startsWith('http') == false) {
            params.url = absolute_url + '/' + params.url;
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

export default Utils;
