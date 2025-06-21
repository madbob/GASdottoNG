(function ($) {
    $.fn.aggregator = function() {
        $(this).each(function() {
            $(this).find('div.card').each(function() {
                initAggregatorList($(this));
            });

            $(this).on('click', '.explode-aggregate', function() {
                let container = $(this).closest('.card');
                container.find('li').each(function() {
                    let cell = prependCell(container);
                    cell.find('ul').append($(this).clone());
                });
                container.remove();
            });

            $(this).submit(function(e) {
                e.preventDefault();
                let form = $(this);
                form.find('button[type=submit]').prop('disabled', false);

                let data = [];

                form.find('.card').each(function() {
                    let a = {
                        id: $(this).attr('data-aggregate-id'),
                        orders: []
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

                    success: function() {
                        location.reload();
                    }
                });
            });
        });

        function prependCell(node) {
            let cell = node.clone();
            cell.attr('data-aggregate-id', 'new').find('ul').empty();
            node.before(cell);
            initAggregatorList(cell);
            return cell;
        }

        function initAggregatorList(node) {
            let items = node.find('li').length;
            if (items < 2) {
                node.find('.explode-aggregate').hide();
            }

            node.find('ul').sortable({
                connectWith: '#orderAggregator div.card ul',
                accept: 'li',
                drop: function(event, ui) {
                    let items = $(this).find('li').length;
                    if (items == 0) {
                        prependCell($(this));
                    }
                    else if (items == 1) {
                        $(this).find('.explode-aggregate').show();
                    }

                    let source = ui.draggable.closest('.card');
                    let ex_items = source.find('li').length;
                    if (ex_items == 2) {
                        source.find('.explode-aggregate').hide();
                    }

                    ui.draggable.css('right', '').css('left', '').css('top', '').css('bottom', '').css('width', '').css('height', '');
                    $(this).find('ul').append(ui.draggable);
                }
            });
        }
    };
}(jQuery));
