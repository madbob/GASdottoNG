const utils = require('./utils');

$(document).ready(function() {
    $('body').on('click', '.password-field .bi-eye-slash', function() {
        var i = $(this).closest('.password-field').find('input[type!=hidden]');
        if (i.attr('type') == 'password')
            i.attr('type', 'text');
        else
            i.attr('type', 'password');

        $(this).toggleClass('bi-eye').toggleClass('bi-eye-close');
    });

    $('body').on('focus', 'input.password-changer', function() {
        if ($(this).closest('.modal').length != 0) {
            return;
        }

        $(this).popover({
            content: function() {
                var input = $(this);

                var ret = '<div>\
                    <div class="row mb-2"><label for="password" class="col-4 col-form-label">' + _('Nuova Password') + '</label><div class="col-8"><input type="password" class="form-control" name="password" value="" autocomplete="off" minlength="8"></div></div>\
                    <div class="row mb-2"><label for="password_confirm" class="col-4 col-form-label">' + _('Conferma Password') + '</label><div class="col-8"><input type="password" class="form-control" name="password_confirm" value="" autocomplete="off" minlength="8"></div></div>';

                if (input.hasClass('enforcable_change')) {
                    ret += '<div class="checkbox"><label><input type="checkbox" name="enforce_change"> ' + _('Forza cambio password al prossimo login') + '</label></div><br>';
                }

                ret += '<div class="row"><div class="col-8 offset-4"><button class="btn btn-light">' + _('Annulla') + '</button> <button class="btn btn-success">' + _('Conferma') + '</button></div></div></div>';

                ret = $(ret);

                ret.find('button.btn-success').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var password = ret.find('input[name=password]').val();
                    var confirm = ret.find('input[name=password_confirm]').val();

                    if (password == confirm) {
                        if (ret.find('input[name=enforce_change]').length != 0) {
                            var enforce = ret.find('input[name=enforce_change]').prop('checked') ? 'true' : 'false';
                            input.closest('.form-group').find('input[name=enforce_password_change]').val(enforce);
                        }

                        input.val(password);
                        input.popover('dispose');
                    }
                    else {
                        alert('Le password sono diverse!');
                    }
                });

                ret.find('button.btn-light').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    input.popover('dispose');
                });

                setTimeout(function() {
                    ret.find('input[name=password]').focus();
                }, 200);

                return ret;
            },
            offset: [0, -50],
            template: '<div class="popover password-popover" role="tooltip"><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
            placement: 'left',
            html: true,
        });
    });

    $('body').on('submit', '.password-protected', function(event) {
        if ($(this).attr('data-password-protected-verified') != '1') {
            event.preventDefault();
            var id = $(this).attr('id');
            var modal = $('#password-protection-dialog');
            modal.attr('data-form-target', '#' + id);
            modal.find('input:password').val();
            modal.modal('show');
            $('#' + id).find('button:submit').prop('disabled', true);
            return false;
        }

        return true;
    })
    .on('submit', '#password-protection-dialog form', function(event) {
        event.preventDefault();
        var modal = $(this).closest('.modal');

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            data: {
                password: $(this).find('input[type=password]').val()
            },
            success: function(data) {
                if (data == 'ok') {
                    var target = modal.attr('data-form-target');
                    modal.modal('hide');
                    var form = $(target);
                    form.attr('data-password-protected-verified', '1');

                    $.ajax({
                        method: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serializeArray(),
                        dataType: 'JSON',
                        success: function(data) {
                            form.find('button:submit').prop('disabled', false);
                            miscInnerCallbacks(form, data);
                            form.attr('data-password-protected-verified', '0');
                        },
                        error: function() {
                            var button = form.find('button:submit');
                            utils.inlineFeedback(button, _('ERRORE'));
                            form.attr('data-password-protected-verified', '0');
                        }
                    });
                }
            }
        });
    });
});
