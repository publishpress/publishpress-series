/**
 * Module imports
 */
import axios from 'axios';
import qs from 'qs';

/**
 * Populated with the data object prepped by the server (or defaults)
 * @type {Object}
 */
export let config = _osConfig || {
    url: '',
    ajaxUrl: '',
    debug: true
};
export let i18n = _osi18n || {};

/**
 * Validates that the incoming data object has required values for wp ajax request.
 * @param {Object} data
 * @throws {String}
 * @return {Object}
 */
function verifyAjaxData(data) {
    if (typeof data === 'undefined') {
        throw 'Incoming data object for ajax request is not defined.';
    }
    if (typeof data.action === 'undefined' || data.action === '') {
        throw 'Incoming data object requires an "action" property';
    }
    return data;
}


/**
 * This does a wp ajax request using axios.
 * @param {Object}   data
 * @param {Boolean} formUrlEncoded    Whether the data should be sent as formEncoded or not.
 * @param {Function} successCallback  This will be called on resolving the ajax request successfully.
 * @param {Function} failCallback     This will be called when the ajax request fails to resolve.
 */
export function ajax(data, formUrlEncoded = false, successCallback, failCallback) {
    //verify and populate missing incoming data values.
    data = verifyAjaxData(data);
    if (formUrlEncoded) {
        data = qs.stringify(data);
    }
    if (config.debug) {
        console.log(data);
    }
    axios.post(
        config.ajaxUrl,
        data
    ).then(function (response) {
        typeof successCallback === 'function'
            ? successCallback(response)
            : console.log(response)
    }).catch(function (error) {
        typeof failCallback === 'function'
            ? failCallback(error)
            : console.log(error);
    });
}

/**
 * Toggles the js spinner (WP spinner class) active or inactive.
 * @param {String} el_selector  the css selector for the spinner element.
 */
export function toggleAjaxSpinner(el_selector) {
    let el = document.querySelector(el_selector),
        className = 'is-active';
    if (el !== null) {
        if (el.classList.contains(className)) {
            el.classList.remove(className);
        } else {
            el.classList.add(className);
        }
    }
}

/** Add global error handler **/
window.addEventListener('error', function(e) {
    "use strict";
    if (e.error.stack) {
        e.error.message += '\n' + e.error.stack;
    }
    if (config.debug) {
        console.error(e.error.message);
    }
});