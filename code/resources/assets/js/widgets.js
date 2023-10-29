require('bootstrap-datepicker');
require('select2');

import utils from "./utils";

class Widgets {
    static init(container)
    {
        this.handlingContactSelection(container);

        $('input.date', container).datepicker({
            format: 'DD dd MM yyyy',
            autoclose: true,
            language: utils.currentLanguage(),
            clearBtn: true,
        }).each(function() {
            var input = $(this);
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
            var current = $(item);
            var target = this.dateEnforcePeer(current, 'data-enforce-after');

            target.datepicker().on('changeDate', function() {
                var current_start = current.datepicker('getDate');
                var current_ref = target.datepicker('getDate');
                if (current_start < current_ref) {
                    current.datepicker('setDate', current_ref);
                }
            });
        }).focus((e) => {
			var current = $(e.currentTarget);
            var target = this.dateEnforcePeer(current, 'data-enforce-after');

            /*
                Problema: cercando di navigare tra i mesi all'interno del datepicker
                viene lanciato nuovamente l'evento di focus, che fa rientrare in
                questa funzione, e se setStartDate() viene incondazionatamente
                eseguita modifica a sua volta la data annullando l'operazione.
                Dunque qui la eseguo solo se non l'ho già fatto (se la data di
                inizio forzato non corrisponde a quel che dovrebbe essere), badando
                però a fare i confronti sui giusti formati
            */
            var current_start = current.datepicker('getStartDate');
            var current_ref = target.datepicker('getUTCDate');
            if (current_start.toString() != current_ref.toString()) {
                current.datepicker('setStartDate', current_ref);
            }
        });

		$('input[data-enforce-more]', container).each((index, item) => {
            var current = $(item);
            var target = this.dateEnforcePeer(current, 'data-enforce-more');

            target.on('change', function() {
                var current_start = current.val();
                var current_ref = target.val();
                if (current_start < current_ref) {
                    current.val(current_ref);
                }
            });
        }).focus((e) => {
			var current = $(e.currentTarget);
            var current_ref = this.dateEnforcePeer(current, 'data-enforce-more').val();
            current.attr('min', current_ref);
        });

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
            var _this = this;
            $(this).popover("show");
            $(".popover").on("mouseleave", function () {
                $(_this).popover('hide');
            });
        }).on("mouseleave", function () {
            var _this = this;
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

            var integer = $(this).attr('data-enforce-integer');
            if (integer && (e.key == '.' || e.key == ',')) {
                e.preventDefault();
                return;
            }

            var allow_negative = ($(this).attr('data-allow-negative') == '1');
            var minimum = $(this).attr('data-enforce-minimum');

            $(this).val(function(index, value) {
                var val = value.replace(/,/g, '.');
                if (allow_negative)
                    val = val.replace(/[^\-0-9\.]/g, '');
                else
                    val = val.replace(/[^0-9\.]/g, '');

                if (val != '' && minimum && val < minimum)
                    val = minimum;

                return val;
            });
        })
        .focus(function(e) {
            var v = utils.parseFloatC($(this).val());
            if (v == 0) {
                var minimum = $(this).attr('data-enforce-minimum');
                if (minimum)
                    $(this).val(minimum);
                else
                    $(this).val('0');
            }
        })
        .blur(function(e) {
            $(this).val(function(index, value) {
                var v = utils.parseFloatC(value);

                var minimum = $(this).attr('data-enforce-minimum');
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
                var max = $(this).attr('data-max-size');
                var file = this.files[0].size;
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
            var sum = 0;
            var container = $(this).closest('.simple-sum-container');
            container.find('.simple-sum').each(function() {
                sum += utils.parseFloatC($(this).val());
            });
            container.find('.simple-sum-result').val(sum);
        });

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
            var random = 'new_' + utils.randomString(5);
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
        var select = node.attr(attribute);
        var target = node.closest('.input-group').find(select);
        if (target.length == 0) {
			target = node.closest('tr').find(select);
	        if (target.length == 0) {
            	target = node.closest('form').find(select);
			}
        }

        return target;
    }

    static handlingContactSelection(container) {
        if (container.closest('.contacts-selection').length != 0) {
            /*
                Questo è per inizializzare le nuove righe aggiunte dinamicamente
                nella lista dei contatti
            */
            var input = container.find('input[name="contact_value[]"]');
            var typeclass = container.find('select option:selected').val();
            this.fixContactField(input, typeclass);

            $('select', container).change((e) => {
                var input = $(e.currentTarget).closest('tr').find('input[name="contact_value[]"]');
                var typeclass = $(e.currentTarget).find('option:selected').val();
                this.fixContactField(input, typeclass);
            });
        }
        else {
            $('.contacts-selection tr', container).each((index, item) => {
                var input = $(item).find('input[name="contact_value[]"]');
                var typeclass = $(item).find('select option:selected').val();
                this.fixContactField(input, typeclass);
            });
        }
    }

    static fixContactField(input, typeclass) {
        input.attr('class', '').addClass('form-control');

        if (typeclass == 'email') {
            input.attr('type', 'email');
        }
        else {
            input.attr('type', 'text');
            input.addClass(typeclass);
        }
    }

    static previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            var img = $(input).closest('.img-preview').find('img');

            reader.onload = function (e) {
                img.attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
}

export default Widgets;
