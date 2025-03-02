import utils from "./utils";
require('jquery-ui/ui/widgets/sortable');

(function ($) {
    $.fn.dynamictree = function() {
        $(this).each(function() {
            $(this).find('.dynamic-tree').sortable({
                items: 'li',
                connectWith: '.dynamic-tree ul',
                start: function() {
                    $('.dynamic-tree ul').css('min-height', '50px');
                },
                stop: function() {
                    $('.dynamic-tree ul').css('min-height', '0');
                },
                receive: function(e, ui) {
                    initTopLevel(ui.item);
                },
            });

            $(this).find('.dynamic-tree ul').sortable({
                items: 'li',
                connectWith: '.dynamic-tree, .dynamic-tree ul',
                receive: function(e, ui) {
                    var mainlist = e.target.closest('ul');

                    var children = ui.item.find('ul').find('li');
                    children.each(function() {
                        mainlist.append(this);
                    });

                    ui.item.find('ul').remove();
                },
            });

            $(this).on('click', '.dynamic-tree-remove', removeRow);
            $(this).on('click', '.dynamic-tree-add', appendRow);
            $(this).on('submit', doSubmit);
        });

        function parseDynamicTree(tree) {
            var data = [];
            var index = 1;

            while(true) {
                var n = tree.find('> li:nth-child(' + index + ')');
                if (n.length == 0)
                    break;

                var node = {
                    id: n.attr('id'),
                    name: n.find('input:text').val()
                };

                node.children = parseDynamicTree(n.find('ul'));
                data.push(node);
                index++;
            }

            return data;
        }

        function initTopLevel(node) {
            if (node.find('ul').length == 0) {
                node.append('<ul></ul>');
            }

            node.find('ul').sortable({
                items: 'li',
                connectWith: '.dynamic-tree, .dynamic-tree ul',
            });
        }

        function removeRow(event) {
            $(event.target).closest('li').remove();
        }

        function appendRow(event) {
            event.preventDefault();
            var box = $(event.target).closest('.dynamic-tree-box');
            var input = box.find('input[name=new_category]');
            var name = input.val();
            var tree = box.find('.dynamic-tree');
            var new_node = $(`<li class="list-group-item ui-sortable-handle">
                <div>
                    <div class="btn btn-danger float-end dynamic-tree-remove"><i class="bi-x-lg"></i></div>
                    <input name="names[]" class="form-control" value="${name}">
                </div>
            </li>`);

            tree.append(new_node);
            tree.sortable('refresh');
            initTopLevel(new_node);

            input.val('');
            return false;
        }

        function doSubmit(event) {
            event.preventDefault();
            var box = $(event.target);
            var button = box.find('button[type=submit]');
            button.prop('disabled', true);
            var tree = box.find('.dynamic-tree');
            var data = parseDynamicTree(tree);

            $.ajax({
                method: box.attr('method'),
                url: box.attr('action'),
                data: {
                    _token: box.find('input[name=_token]').val(),
                    _method: box.find('input[name=_method]').val(),
                    serialized: data
                },
                success: function() {
                    utils.inlineFeedback(button, _('Salvato!'));
                    box.closest('.modal').modal('hide');
                }
            });

            return false;
        }
    };
}(jQuery));
