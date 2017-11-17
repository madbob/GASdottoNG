(function ($) {
    $.fn.aggregator = function() {
        $(this).each(function() {
            $(this).find('div.well').each(function() {
                initAggregatorList($(this));
            });

            $(this).find('ul li').draggable({
                revert: 'invalid'
            });

            $(this).on('click', '.explode-aggregate', function() {
                var container = $(this).closest('.well');
                container.find('li').each(function() {
                    var cell = prependCell(container);
                    cell.find('ul').append($(this).clone());
                });
                container.remove();
            });

            $(this).find('form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                form.find('button[type=submit]').prop('disabled', false);

                var data = new Array();

                form.find('.well').each(function() {
                    var a = {
                        id: $(this).attr('data-aggregate-id'),
                        orders: new Array()
                    };

                    $(this).find('li').each(function() {
                        a.orders.push($(this).attr('data-order-id'));
                    });

                    data.push(a);
                });

                $.ajax({
                    method: form.attr('method'),
                    url: form.attr('action'),
                    data: {
                        data: JSON.stringify(data)
                    },
                    dataType: 'json',

                    success: function(data) {
                        location.reload();
                    }
                });
            });
        });

        function prependCell(node) {
            var cell = node.clone();
            cell.attr('data-aggregate-id', 'new').find('ul').empty();
            node.before(cell);
            initAggregatorList(cell);
            return cell;
        }

        function initAggregatorList(node) {
            var items = node.find('li').length;
            if (items < 2) {
                node.find('.explode-aggregate').hide();
            }

            node.droppable({
                accept: 'li',
                drop: function(event, ui) {
                    var items = $(this).find('li').length;
                    if (items == 0) {
                        prependCell($(this));
                    }
                    else if (items == 1) {
                        $(this).find('.explode-aggregate').show();
                    }

                    var source = ui.draggable.closest('.well');
                    var ex_items = source.find('li').length;
                    if (ex_items == 2)
                        source.find('.explode-aggregate').hide();

                    ui.draggable.css('right', '').css('left', '').css('top', '').css('bottom', '').css('width', '').css('height', '');
                    $(this).find('ul').append(ui.draggable);
                }
            });
        }
    }
}(jQuery));
