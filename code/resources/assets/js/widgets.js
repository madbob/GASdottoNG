require('bootstrap-datepicker');
require('select2');

import utils from "./utils";

class Widgets {
    static init(container)
    {
        this.handlingContactSelection(container);
        this.initDatesWidgets(container);

        $('select[multiple]', container).select2({
            theme: "bootstrap-5",
            dropdownParent: container,
        });

        $('.remote-select', container).each((index, node) => {
            node = $(node);
            let url = node.attr('data-remote-url');

            node.select2({
                theme: "bootstrap-5",
                dropdownParent: container,
                ajax: {
                    url: url,
                    dataType: 'JSON',
                },
            });
        });

        /*
            https://stackoverflow.com/questions/15989591/how-can-i-keep-bootstrap-popover-alive-while-the-popover-is-being-hovered
        */
        $('[data-bs-toggle="popover"]', container).popover({
            trigger: "manual",
            html: true,
            animation:false
        })
        .on("mouseenter", function () {
            let _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function () {
                $(_this).popover('hide');
            });
        }).on("mouseleave", function () {
            let _this = this;
            setTimeout(function () {
                if (!$(".popover:hover").length) {
                    $(_this).popover("hide");
                }
            }, 300);
        });

        $('input.number', container).keydown(function(e) {
            if (e.which == 13) {
                e.preventDefault();
                return;
            }

            let integer = $(this).attr('data-enforce-integer');
            if (integer && (e.key == '.' || e.key == ',')) {
                e.preventDefault();
                return;
            }

            let allow_negative = ($(this).attr('data-allow-negative') == '1');
            let minimum = $(this).attr('data-enforce-minimum');

            $(this).val(function(index, value) {
                let val = value.replaceAll(/,/g, '.');
                if (allow_negative)
                    val = val.replaceAll(/[^\-0-9\.]/g, '');
                else
                    val = val.replaceAll(/[^0-9\.]/g, '');

                if (val != '' && minimum && val < minimum)
                    val = minimum;

                return val;
            });
        })
        .focus(function() {
            let v = utils.parseFloatC($(this).val());
            if (v == 0) {
                let minimum = $(this).attr('data-enforce-minimum');
                if (minimum)
                    $(this).val(minimum);
                else
                    $(this).val('0');
            }
        })
        .blur(function() {
            $(this).val(function(index, value) {
                let v = utils.parseFloatC(value);

                let minimum = $(this).attr('data-enforce-minimum');
                if (minimum && v < minimum)
                    return minimum;
                else
                    return v;
            });
        });

        $('.trim-ddigits', container).on('blur change', function() {
            let limit = $(this).attr('data-trim-digits');
            $(this).val(function(index, value) {
                return utils.parseFloatC(value).toFixed(limit);
            });
        });

        $('input:file[data-max-size]', container).change(function() {
            if (this.files && this.files[0]) {
                let max = $(this).attr('data-max-size');
                let file = this.files[0].size;
                if (file > max) {
                    $(this).val('');
                    utils.setInputErrorText($(this), _('Il file è troppo grande!'));
                    return false;
                }
                else {
                    utils.setInputErrorText($(this), null);
                    return true;
                }
            }
        });

        $('.img-preview input:file', container).change((e) => {
            this.previewImage(e.currentTarget);
        });

        $('.simple-sum', container).change(function() {
            let sum = 0;
            let container = $(this).closest('.simple-sum-container');
            container.find('.simple-sum').each(function() {
                sum += utils.parseFloatC($(this).val());
            });
            container.find('.simple-sum-result').val(sum);
        });

        $('.selective-display', container).find('input:radio').change((e) => {
            let sel = $(e.currentTarget);
            if (sel.prop('checked') == false) {
                return;
            }

            let parent = sel.closest('form');
            let target = sel.closest('.selective-display').attr('data-target');
            let value = sel.val();

            parent.find(target).addClass('d-none').filter((i, el) => {
                let e = $(el);
                e.find('input,select,textarea').addClass('skip-on-submit');
                e.find('[required]').prop('required', false).attr('data-required', 'true');
                return e.attr('data-type').split(',').includes(value);
            }).removeClass('d-none').each((index, valid) => {
                valid = $(valid);
                valid.find('input,select,textarea').removeClass('skip-on-submit');
                valid.find('[data-required=true]').prop('required', true);
            });
        }).change();

        $('.status-selector input:radio[name*="status"]', container).change(function() {
            let field = $(this).closest('.status-selector');
            let status = $(this).val();
            let del = (status != 'deleted');
            field.find('[name=deleted_at]').prop('hidden', del).closest('.input-group').prop('hidden', del);
            let sus = (status != 'suspended');
            field.find('[name=suspended_at]').prop('hidden', sus).closest('.input-group').prop('hidden', sus);
        });

        /*
            Questo è per popolare le righe dinamicamente aggiunte in
            code/resources/views/variant/editsingle.blade.php
        */
        if ($('input[value="put_random_here"]', container).length != 0) {
            let random = 'new_' + utils.randomString(5);
            $('input[value="put_random_here"]', container).each(function() {
                if ($(this).closest('.dynamic-table').length != 0) {
                    if ($(this).closest('tbody').length != 0) {
                        $(this).val(random);
                    }
                }
            });
        }

        $('.sortable-table tbody', container).sortable({
            items: '> tr',
            handler: '.sorter',
        });
    }

    static dateEnforcePeer(node, attribute)
    {
        let select = node.attr(attribute);
        let target = node.closest('.input-group').find(select);
        if (target.length == 0) {
			target = node.closest('tr').find(select);
	        if (target.length == 0) {
            	target = node.closest('form').find(select);
			}
        }

        return target;
    }

    static initDatesWidgets(container)
    {
        $('input.date', container).datepicker({
            format: 'DD dd MM yyyy',
            autoclose: true,
            language: utils.currentLanguage(),
            clearBtn: true,
        }).each(function() {
            let input = $(this);
            input.siblings('.input-group-addon').click(function() {
                input.focus();
            });
        }).on('show', function(e) {
            /*
                Senza questo, l'evento risale e - non ho ben capito come -
                interferisce con le accordion e i modal
            */
            e.stopPropagation();
        });

        $('input.date-to-month', container).datepicker({
            format: 'dd MM',
            autoclose: true,
            language: utils.currentLanguage(),
            clearBtn: false,
            maxViewMode: 'months'
        });

        $('.date[data-enforce-after]', container).each((index, item) => {
            let current = $(item);
            let target = this.dateEnforcePeer(current, 'data-enforce-after');

            target.datepicker().on('changeDate', function() {
                let current_start = current.datepicker('getDate');
                let current_ref = target.datepicker('getDate');
                if (current_start < current_ref) {
                    current.datepicker('setDate', current_ref);
                }
            });
        }).focus((e) => {
            let current = $(e.currentTarget);
            let target = this.dateEnforcePeer(current, 'data-enforce-after');

            /*
                Problema: cercando di navigare tra i mesi all'interno del datepicker
                viene lanciato nuovamente l'evento di focus, che fa rientrare in
                questa funzione, e se setStartDate() viene incondazionatamente
                eseguita modifica a sua volta la data annullando l'operazione.
                Dunque qui la eseguo solo se non l'ho già fatto (se la data di
                inizio forzato non corrisponde a quel che dovrebbe essere), badando
                però a fare i confronti sui giusti formati
            */
            let current_start = current.datepicker('getStartDate');
            let current_ref = target.datepicker('getUTCDate');
            if (current_start.toString() != current_ref.toString()) {
                current.datepicker('setStartDate', current_ref);
            }
        });
    }

    static handlingContactSelection(container)
    {
        if (container.closest('.contacts-selection').length != 0) {
            /*
                Questo è per inizializzare le nuove righe aggiunte dinamicamente
                nella lista dei contatti
            */
            let input = container.find('input[name="contact_value[]"]');
            let typeclass = container.find('select option:selected').val();
            this.fixContactField(input, typeclass);

            $('select', container).change((e) => {
                let input = $(e.currentTarget).closest('tr').find('input[name="contact_value[]"]');
                let typeclass = $(e.currentTarget).find('option:selected').val();
                this.fixContactField(input, typeclass);
            });
        }
        else {
            $('.contacts-selection tr', container).each((index, item) => {
                let input = $(item).find('input[name="contact_value[]"]');
                let typeclass = $(item).find('select option:selected').val();
                this.fixContactField(input, typeclass);
            });
        }
    }

    static fixContactField(input, typeclass)
    {
        input.attr('class', '').addClass('form-control');

        if (typeclass == 'email') {
            input.attr('type', 'email');
        }
        else {
            input.attr('type', 'text');
            input.addClass(typeclass);
        }
    }

    static previewImage(input)
    {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            let img = $(input).closest('.img-preview').find('img');

            reader.onload = function (e) {
                img.attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
}

export default Widgets;
