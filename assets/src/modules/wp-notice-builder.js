"use strict";
/**
 * Builds a notice container
 * @param {String} type
 * @param {String} message
 * @param {Boolean} dismissible
 * @return {String}
 */
export function noticeBuilder(type, message, dismissible = false) {
    let isDismissible = dismissible ? ' is-dismissible' : '';
    if (typeof message !== 'string') {
        throw new TypeError('Incoming {message} variable should be a string.');
    }
    if (typeof type !== 'string') {
        throw new TypeError('Incoming {type} variable should be a string.')
    }
    return '<div class="notice ' + type + isDismissible + '"><p>' + message + '</p></div>';
}