function helpFillNode(nodes, text) {
    if (nodes != null) {
        nodes.parent().addClass('help-sensitive').popover({
            content: text,
            placement: 'auto right',
            container: 'body',
            html: true,
            trigger: 'hover'
        });
    }

    return '';
}

$(document).ready(function() {
    $('body').on('click', '#help-trigger', function(e) {
        e.preventDefault();

        if ($(this).hasClass('active')) {
            $('.help-sensitive').removeClass('help-sensitive').popover('destroy');
        } else {
            $.ajax({
                url: '/help/data.md',
                method: 'GET',

                success: function(data) {
                    var renderer = new marked.Renderer();
                    var container = null;
                    var nodes = null;
                    var inner_text = '';

                    /*
                    	Qui abuso del renderer Markdown per
                    	filtrare i contenuti del file ed
                    	assegnarli ai vari elementi sulla pagina
                    */

                    renderer.heading = function(text, level) {
                        inner_text = helpFillNode(nodes, inner_text);

                        if (level == 2)
                            container = $(text);
                        else if (level == 1)
                            nodes = container.find(':contains(' + text + ')').last();
                    };
                    renderer.paragraph = function(text, level) {
                        if (inner_text != '')
                            inner_text += '<br/>';
                        inner_text += text;
                    };
                    renderer.list = function(text, level) {
                        inner_text += '<ul>' + text + '</ul>';
                    };

                    marked(data, {
                        renderer: renderer
                    }, function() {
                        inner_text = helpFillNode(nodes, inner_text);
                    });
                }
            });
        }

        $(this).toggleClass('active');
        return false;
    });
});
