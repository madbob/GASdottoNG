import utils from "./utils";
import Lists from "./lists";

class Roles {
    static init(container)
    {
        this.setupPermissionsEditor(container);

        $('.multigas-editor input:checkbox[data-gas]', container).change((e) => {
            var check = $(e.currentTarget);
            check.removeClass('saved-checkbox');
            var url = check.is(':checked') ? 'multigas/attach' : 'multigas/detach';

            utils.postAjax({
                url: url,
                data: {
                    gas: check.attr('data-gas'),
                    target_id: check.attr('data-target-id'),
                    target_type: check.attr('data-target-type'),
                },
                success: function() {
                    check.addClass('saved-checkbox');
                }
            });
        });
    }

    static setupPermissionsEditor(container) {
        $('.roleAssign', container).each(function() {
            if ($(this).hasClass('tt-hint') == true) {
                return;
            }

            if ($(this).hasClass('tt-input') == false) {
                $(this).autocomplete({
                    source: utils.absoluteUrl() + '/users/search',
                    select: function(event, ui) {
                        var text = $(this);
                        var group = $(this).closest('.accordion-body');
                        var user_id = ui.item.id;

                        var label = ui.item.label;
                        utils.postAjax({
                            url: 'roles/attach',
                            dataType: 'HTML',
                            data: {
                                role: Lists.currentLoadableLoaded(this),
                                user: user_id,
                            },
                            success: function(data) {
                                var panel = $(data);
                                var identifier = $(panel).attr('id');
                                utils.j().initElements(panel);
                                group.find('.tab-content').append(panel);

                                var tab = $('<li class="nav-item" data-user="' + user_id + '"><button type="button" class="nav-link" data-bs-target="#' + identifier + '" data-bs-toggle="tab">' + label + '</button></li>');
                                group.find('[role=tablist]').find('.last-tab').before(tab);
                                tab.find('button').click();
                                text.val('');
                            }
                        });
                    }
                });
            }
        });

        $('.role-editor', container).on('change', 'input:checkbox[data-role]', function(e) {
            var check = $(this);
            check.removeClass('saved-checkbox saved-left-feedback');
            var url = check.is(':checked') ? 'roles/attach' : 'roles/detach';

            utils.postAjax({
                url: url,
                data: {
                    role: check.attr('data-role'),
                    action: check.attr('data-action'),
                    user: check.attr('data-user'),
                    target_id: check.attr('data-target-id'),
                    target_class: check.attr('data-target-class'),
                },
                success: function() {
                    check.addClass('saved-checkbox saved-left-feedback');
                }
            });

        }).on('click', '.remove-role', function(e) {
            e.preventDefault();

            if(confirm(_('Sei sicuro di voler revocare questo ruolo?'))) {
                var button = $(this);
                var userid = button.attr('data-user');

                utils.postAjax({
                    url: 'roles/detach',
                    data: {
                        role: button.attr('data-role'),
                        user: button.attr('data-user')
                    },
                    success: function() {
                        var panel = button.closest('.accordion-body');
                        var tab = panel.find('[data-user=' + userid + ']');
                        panel.find(tab.find('button').attr('data-bs-target')).remove();
                        tab.remove();
                    }
                });
            }
        });
    }
}

export default Roles;
