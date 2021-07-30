require('./jquery.mjs.nestedSortable');
import utils from "./utils";

/*
    Questo di fatto è una estensione di nestedSortable
*/
(function ($) {
    $.fn.dynamictree = function() {
        $(this).each(function() {
            $(this).find('.dynamic-tree').nestedSortable({
                listType: 'ul',
                items: 'li',
                toleranceElement: '> div',
                isTree: true,
                startCollapsed: true
            });

            $(this).on('click', '.dynamic-tree-remove', removeRow);
            $(this).on('click', '.dynamic-tree-add', appendRow);
            $(this).on('click', '.dynamic-tree-expand', expandRow);
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

        function removeRow(event) {
            $(event.target).closest('li').remove();
        }

        function appendRow(event) {
            event.preventDefault();
            var box = $(event.target).closest('.dynamic-tree-box');
            var input = box.find('input[name=new_category]');
            var name = input.val();
            var tree = box.find('.dynamic-tree');

            tree.append('<li class="list-group-item mjs-nestedSortable-branch mjs-nestedSortable-collapsed"> \
                <div> \
                    <div class="btn btn-danger float-end dynamic-tree-remove"><i class="bi-x-lg"></i></div> \
                    <div class="btn btn-warning float-end dynamic-tree-expand"><i class="bi-plus-lg expanding-icon"></i></div> \
                    <input name="names[]" class="form-control" value="' + name + '"> \
                </div> \
                <ul></ul> \
            </li>');

            tree.nestedSortable('refresh');

            input.val('');
            return false;
        }

        function expandRow(event) {
            $(event.target).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
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
