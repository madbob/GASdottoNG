require('continous-calendar');

import jBob from "jbob";
import Lists from "./lists";

class Utils {
    static init(container)
    {
        $('.reloader', container).click(function() {
            let listid = $(this).attr('data-reload-target');

            if (listid == null) {
                location.reload();
            }
            else {
                /*
                    Nel caso in cui il tasto sia dentro ad un modale, qui ne forzo la
                    chiusura (che non e' implicita, se questo non viene fatto resta
                    l'overlay grigio in sovraimpressione)
                */
                let modal = $(this).closest('.modal').first();
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

        $('.select-fetcher', container).change((e) => {
            let fetcher = $(e.currentTarget);
            let targetid = fetcher.attr('data-fetcher-target');
            let target = fetcher.parent().find(targetid);
            target.empty().append(this.j().makeSpinner());

            let id = fetcher.find('option:selected').val();
            let url = fetcher.attr('data-fetcher-url').replace('XXX', id);

            $.get(url, function(data) {
                target.empty().append(data);
            });
        });

        $('.object-details', container).click((e) => {
            let url = $(e.currentTarget).attr('data-show-url');
            let modal = $('#service-modal');
            this.j().fetchNode(url, modal.find('.modal-body'));
            modal.modal('show');
        });

        $('input[data-alternative-required]', container).change((e) => {
            this.reviewRequired($(e.currentTarget).closest('form'));
        });

        $('.prevent-default', container).click(function(e) {
            e.preventDefault();
        });

        $('.link-button', container).click(function(e) {
            e.preventDefault();
            let url = $(this).attr('data-link');
            window.open(url, '_blank');
        });

        $('.form-download', container).click(function(event) {
            event.preventDefault();
            let parent = $(this).closest('form');
            if (parent.length == 0) {
                parent = $(this).closest('.form-filler');
            }

            let data = parent.find('input, select').serializeArray();

            let url = $(this).attr('href');
            if (url.indexOf('?') == -1) {
                url = url + '?' + $.param(data);
            }
            else {
                url = url + '&' + $.param(data);
            }

            window.open(url, '_blank');
        });

        $('.actual-calendar', container).each(function() {
            setTimeout(() => {
                $(this).ContinuousCalendar({
        			days: JSON.parse(atob($(this).attr('data-days'))),
        			months: JSON.parse(atob($(this).attr('data-months'))),
        			rows: 4,
                    events: JSON.parse(atob($(this).attr('data-events'))),
                });
            }, 300);
        });

        $('.collapse', container).filter(':not(.show)').find('.required_when_triggered').prop('required', false);

        /*
            Questo è per evitare che gli eventi di show/hide si propaghino a
            sproposito all'accordion / alla tab padre
        */
        $('.collapse', container).on('show.bs.collapse hide.bs.collapse', function(e) {
            e.stopPropagation();
		}).on('shown.bs.collapse hidden.bs.collapse', function() {
            $(this).find('.required_when_triggered').each(function() {
				let target = $(this);
				let active = $(this).closest('.collapse').hasClass('show');
				target.prop('required', active);
			});
        });
    }

    static j()
    {
        if (typeof Utils.jbob == 'undefined') {
            Utils.jbob = new jBob();
        }

        return Utils.jbob;
    }

    static currentLanguage()
    {
        if (typeof Utils.current_language == 'undefined') {
            Utils.current_language = $('html').attr('lang').split('-')[0];
        }

        return Utils.current_language;
    }

    static absoluteUrl()
    {
        if (typeof Utils.absolute_url == 'undefined') {
            Utils.absolute_url = $('meta[name=absolute_url]').attr('content');
        }

        return Utils.absolute_url;
    }

    static normalizeUrl(url)
    {
        if (url.startsWith('http') == false) {
            url = this.absoluteUrl() + '/' + url;
        }

        return url;
    }

    static spinSubmitButton(form)
    {
        let submit_button = this.j().submitButton(form);

        submit_button.each(function() {
            let idle_text = $(this).text();
            $(this).attr('data-idle-text', idle_text).empty().append('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
        });
    }

    static formErrorFeedback(form)
    {
        let submit_button = this.j().submitButton(form);

        submit_button.each((index, button) => {
            let btn = $(button);
            this.inlineFeedback(btn, _('ERRORE!'));
            btn.prop('disabled', true);
        });
    }

    static inlineFeedback(button, feedback_text)
    {
        let idle_text = button.attr('data-idle-text');
        if (!idle_text) {
            idle_text = button.html();
        }

        button.empty().append(feedback_text);
        setTimeout(function() {
            button.empty().append(idle_text).prop('disabled', false);
        }, 2000);
    }

    static displayServerError(form, data)
    {
        if (data) {
            if (data.target != '') {
                Utils.j().submitButton(form).each(function() {
                    Utils.inlineFeedback($(this), _('ERRORE!'));
                });

                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.help-block.error-message').remove();

                let input = form.find('[name=' + data.target + ']');
                Utils.setInputErrorText(input, data.message);
            }
            else {
                alert(data.message);
            }
        }
    }

    static detailsButton(url)
    {
        /*
            Questo deve essere coerente col template
            code/resources/views/commons/detailsbutton.blade.php
        */
        return '<button type="button" class="btn btn-xs btn-icon btn-info object-details d-none d-md-inline-block" data-show-url="' + url + '"><i class="bi-zoom-in"></i></button>';
    }

    static setInputErrorText(input, message)
    {
        if (message == null) {
            input.removeClass('is-invalid');
            input.closest('div').find('.help-block.error-message').remove();
        }
        else {
            input.addClass('is-invalid');
            input.closest('div').append('<span class="help-block error-message">' + message + '</span>');
        }
    }

    static randomString(total)
    {
        let text = "";
        let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for( let i = 0; i < total; i++ ) {
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }

        return text;
    }

    static parseFullDate(string)
    {
        let components = string.split(' ');
        let month = 0;
        let months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
        for(month = 0; month < months.length; month++) {
            if (components[2] == months[month]) {
                month++;
                break;
            }
        }

        let date = components[3] + '-' + month + '-' + components[1];
        return Date.parse(date);
    }

    static parseFloatC(value)
    {
        if (typeof value === 'undefined') {
            return 0;
        }

        let ret = parseFloat(value.replace(/,/, '.'));
        if (isNaN(ret)) {
            ret = 0;
        }

        return ret;
    }

    static sel(selector, container)
    {
        let classes = selector.split(' ');
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

        params.url = this.normalizeUrl(params.url);

        $.ajax(params);
    }

    static fetchNode(url, node)
    {
        url = this.normalizeUrl(url);
        this.j().fetchNode(url, node);
    }

    /*
        Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
        un attributo, questa funzione serve ad applicare l'escape necessario
    */
    static sanitizeId(identifier)
    {
        return identifier.replace(/:/g, '\\:').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
    }

    static isMobile()
    {
        return window.screen.width <= 992;
    }

    static formByButton(button)
    {
        let parent_form = button.closest('form');

        if (parent_form.length == 0) {
            let id = button.attr('form');
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
            let alternative = $(this).attr('data-alternative-required');
            if (alternative) {
                let alt = panel.find('[name="' + alternative + '"]');
                if (alt.val() != '') {
                    $(this).prop('required', false);
                }
                else {
                    $(this).prop('required', true);
                }
            }
        });
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

$.fn.attrBegins = function(s) {
    let matched = [];

    this.each(function(index, elem) {
        $.each(elem.attributes, function(index, attr) {
            if (attr.name.indexOf(s) === 0) {
               matched.push(elem);
            }
        });
    });

    return $(matched);
};

export default Utils;
