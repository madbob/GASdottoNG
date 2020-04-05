function loadingPlaceholder() {
    return $('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
}

function inlineFeedback(button, feedback_text) {
    var idle_text = button.text();
    button.text(feedback_text);
    setTimeout(function() {
        button.text(idle_text).prop('disabled', false);
    }, 2000);
}

function randomString(total)
{
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i = 0; i < total; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));

    return text;
}

function parseFullDate(string) {
    var components = string.split(' ');

    var month = 0;
    var months = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    for(month = 0; month < months.length; month++) {
        if (components[2] == months[month]) {
            month++;
            break;
        }
    }

    var date = components[3] + '-' + month + '-' + components[1];
    return Date.parse(date);
}

function parseFloatC(value) {
    if (typeof value === 'undefined')
        return 0;

    var ret = parseFloat(value.replace(/,/, '.'));
    if (isNaN(ret))
        ret = 0;

    return ret;
}

/*
    I valori dinamici possono essere espressi come:

    10%     calcola la percentuale del valore e la somma/sottrae

    50:10   rappresenta la proporzione di cui fare la somma/sottrazione.
            Ad esempio: il totale di un ordine sono 50 euro, il trasporto sono
            10 euro, il trasporto di questa prenotazione è value * 10 / 50

    10      fa una mera somma/sottrazione della cifra rispetto al valore
*/
function applyPercentage(value, percentage, operator) {
    var pvalue = 0;

    if (percentage.endsWith('%')) {
        var p = parseFloatC(percentage);
        pvalue = (p * value) / 100;
    }
    else if (percentage.indexOf(':') !== -1) {
        var order_values = percentage.split(':');
        pvalue = (value * order_values[1]) / order_values[0];
    }
    else {
        pvalue = parseFloatC(percentage);
    }

    if (operator == '-')
        return [value - pvalue, pvalue];
    else if (operator == '+')
        return [value + pvalue, pvalue];
}

function priceRound(price) {
    return (Math.round(price * 100) / 100).toFixed(2);
}

/*
    Il selector jQuery si lamenta quando trova un ':' ad esempio come valore di
    un attributo, questa funzione serve ad applicare l'escape necessario
*/
function sanitizeId(identifier) {
    return identifier.replace(/:/g, '\\:').replace(/\[/g, '\\[').replace(/\]/g, '\\]');
}
