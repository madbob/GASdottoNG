/*
    Questo di fatto è una estensione di nestedSortable
*/
(function ($) {
    $.fn.dynamictree = function() {
        $(this).each(function() {
            $(this).find('.dynamic-tree').nestedSortable({
                listType: 'ul',
                items: 'li',
                toleranceElement: '> div'
            });

            $(this).off('click', '.dynamic-tree-remove', removeRow).on('click', '.dynamic-tree-remove', removeRow);
            $(this).off('click', '.dynamic-tree-add', appendRow).on('click', '.dynamic-tree-add', appendRow);
            $(this).off('submit', doSubmit).on('submit', doSubmit);
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

            tree.append('<li class="list-group-item"><div><span class="badge pull-right"><span class="glyphicon glyphicon-remove dynamic-tree-remove"></span></span><input name="names[]" class="form-control" value="' + name + '"></div><ul></ul></li>');
            tree.nestedSortable('refresh');

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
                    serialized: data
                },
                success: function() {
                    inlineFeedback(button, 'Salvato!');
                    $(this).closest('.modal').modal('hide');
                }
            });

            return false;
        }
    };
}(jQuery));
