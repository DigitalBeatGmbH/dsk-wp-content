/** @package OMGF Pro | Remove Async Google Fonts @author Daan van den Bergh @copyright © 2022 Daan.dev */
var head = document.getElementsByTagName('head')[0],
    insertBefore = head.insertBefore,
    appendChild = head.appendChild,
    append = head.append;

head.insertBefore = function (newElem, refElem) {
    return runInterception(newElem, refElem, 'insertBefore');
}

head.appendChild = function (newElem, refElem) {
    return runInterception(newElem, refElem, 'appendChild');
}

head.append = function (newElem, refElem) {
    return runInterception(newElem, refElem, 'append');
}

function runInterception(newElem, refElem, callback) {
    if (newElem.href && (newElem.href.includes('//fonts.googleapis.com/css') || newElem.href.includes('//fonts.gstatic.com/s/') || newElem.href.includes('//fonts.googleapis.com/icon'))) {
        console.log('OMGF Pro blocked request to ' + newElem.href);

        return;
    }

    return eval(callback).call(head, newElem, refElem);
}
