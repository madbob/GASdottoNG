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

        form.on('change', 'input, select', (e) => {
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

    static removeSaveAlert(form)
    {
        form.find('.bottom-helper').last().prop('hidden', true);
    }
}

export default Forms;
