(function() {
    globalThis._ = function(text) {
        let translations = $('meta[name=client_translations]').attr('content');
        translations = JSON.parse(decodeURI(translations));
        return translations[text];
    }
})();
