(function ($) {
    var active = false;
    var inited = false;
    var container = null;
    var nodes = null;
    var inner_text = '';

    var helpRenderer = new marked.Renderer();

    helpRenderer.heading = function(text, level) {
        inner_text = helpFillNode(nodes, inner_text);

        if (level == 2)
            container = $(text);
        else if (level == 1)
            nodes = container.find('label:contains(' + text + ')');
    };
    helpRenderer.paragraph = function(text, level) {
        if (inner_text != '')
            inner_text += '<br/>';
        inner_text += text;
    };
    helpRenderer.list = function(text, level) {
        inner_text += '<ul>' + text + '</ul>';
    };

    var helpData = null;

    $.fn.helperTrigger = function(option) {
        if (inited == false) {
            inited = true;

            $(this).click(function(e) {
                e.preventDefault();

                if ($(this).hasClass('active')) {
                    active = false;
                    $('.help-sensitive').remove();
                }
                else {
                    active = true;

                    if (helpData == null) {
                        var lang = $('html').attr('lang');

                        $.ajax({
                            url: '/help/data.' + lang + '.md',
                            method: 'GET',

                            success: function(data) {
                                helpData = data;
                                refreshInlineHelp();
                            }
                        });
                    }
                    else {
                        refreshInlineHelp();
                    }
                }

                $(this).toggleClass('active');
                return false;
            });
        }
    }

    function helpFillNode(nodes, text) {
        if (nodes != null) {
            nodes.each(function() {
                $('<small class="help-sensitive">' + text + '</small>').appendTo($(this)).animate({
                    'font-size': '87%'
                }, 500);
            });
        }

        return '';
    }

    function refreshInlineHelp() {
        marked(helpData, {
            renderer: helpRenderer
        }, function() {
            inner_text = helpFillNode(nodes, inner_text);
        });
    }

}(jQuery));
