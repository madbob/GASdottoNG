class Forms {
    static init(container)
    {
        if (container.hasClass('main-form')) {
            this.listenChanges(container);
        }
        else {
            container.find('.main-form').each((index, form) => {
                this.listenChanges($(form));
            });
        }
    }

    static getInputValue(item)
    {
        let value = item.val();

        if (item.is(':checkbox') || item.is(':radio')) {
            value = item.prop('checked') ? 'true' : 'false';
        }

        return value;
    }

    static listenChanges(form)
    {
        form.find('input, select').each((index, item) => {
            let it = $(item);
            let value = this.getInputValue(it);
            it.attr('data-original-value', value);
        });

        form.on('change', 'input, select', () => {
            let changed = false;

            form.find('input, select').each((index, item) => {
                let it = $(item);
                let value = this.getInputValue(it);
                let original = it.attr('data-original-value');

                if (value != original) {
                    changed = true;
                    this.appendSaveAlert(form);
                }
            });

            if (changed == false) {
                this.removeSaveAlert(form);
            }
        });
    }

    static appendSaveAlert(form)
    {
        form.find('.bottom-helper').last().prop('hidden', false);
    }

    /**
     * Dato un form ed un evento di submit, verifica se è stato attivato da un
     * button che ha un name e un value. Se sì, appende i relativi valori al
     * form stesso in modo che possano essere serializzati nel payload
     * https://stackoverflow.com/a/77001045/3135371
     *
     * TODO: rendere implicito questo comportamento nella funzione
     * serializeForm() di jBob
     */
    static appendButtonValue(form, event)
    {
        if (event.originalEvent.submitter) {
            let button = $(event.originalEvent.submitter);
            let name = button.attr('name');
            let value = button.attr('value');
            if (name && value) {
                let hidden = form.find('input[type=hidden][name=' + name + ']');
                if (hidden.length == 0) {
                    hidden = $('<input type="hidden" name="' + name + '">');
                    form.append(hidden);
                }

                hidden.attr('value', value);
            }
        }
    }

    static removeSaveAlert(form)
    {
        form.find('.bottom-helper').last().prop('hidden', true);
    }
}

export default Forms;
