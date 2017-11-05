(function ($) {
    $.fn.manyrows = function(option) {
        if (option == 'refresh') {
            $(this).each(function() {
                while($(this).find('.row').length > 1)
                    $(this).find('.row:first').remove();
                addDeleteButtons($(this));
            });
        }
        else {
            $(this).each(function() {
                $(this).off('click', '.delete-many-rows', removeRow).on('click', '.delete-many-rows', removeRow);
                $(this).off('click', '.add-many-rows', appendRow).on('click', '.add-many-rows', appendRow);
                addDeleteButtons($(this));
            });
        }

        function removeRow(event) {
            event.preventDefault();
            var button = $(event.target);
            var container = button.closest('.many-rows');
            button.closest('.row').remove();
            addDeleteButtons(container);
            return false;
        }

        function appendRow(event) {
            event.preventDefault();
            var button = $(event.target);
            var container = button.closest('.many-rows');
            var row = container.find('.row:not(.many-rows-header)').first().clone();
            container.find('.add-many-rows').before(row);
            initRow(row, true);
            addDeleteButtons(container);
            return false;
        }

        function addDeleteButtons(node) {
            var fields = node.find('.row:not(.many-rows-header)');
            if (fields.length > 1 && node.find('.delete-many-rows').length == 0) {
                fields.each(function() {
                    var button = '<div class="col-md-2"><div class="btn btn-danger delete-many-rows pull-right"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div></div>';
                    $(this).append(button);
                });
            } else if (fields.length == 1) {
                node.find('.delete-many-rows').each(function() {
                    $(this).closest('.col-md-2').remove();
                });
            }
        }

        function initRow(row, fresh) {
            if (fresh) {
                row.find('input').val('');
                row.find('.customized-cell').empty();
            }
        }
    };
}(jQuery));
