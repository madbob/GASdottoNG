(function ($) {
    $.fn.aggregator = function() {
        $(this).each(function() {
            $(this).find('ul').each(function() {
                initAggregatorList($(this));
            });

            $(this).find('ul li').draggable({
                revert: 'invalid'
            });

            $(this).find('form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                form.find('button[type=submit]').prop('disabled', false);

                var data = new Array();

                form.find('ul').each(function() {
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

        function initAggregatorList(node) {
            node.droppable({
                accept: 'li',
                drop: function(event, ui) {
                    var items = $(this).find('li').length;
                    if (items == 0) {
                        var cell = $(this).clone();
                        $(this).before(cell);
                        initAggregatorList(cell);
                    }
                    ui.draggable.css('right', '').css('left', '').css('top', '').css('bottom', '').css('width', '').css('height', '');
                    $(this).append(ui.draggable);
                }
            });
        }
    }
}(jQuery));
