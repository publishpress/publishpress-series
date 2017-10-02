/**
 * Module imports
 */
import {axios} from 'axios';

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
 * @param {Function} successCallback  This will be called on resolving the ajax request successfully.
 * @param {Function} failCallback     This will be called when the ajax request fails to resolve.
 */
export function ajax(data, successCallback, failCallback) {
    //verify and populate missing incoming data values.
    data = verifyAjaxData(data);
    axios.post(
        config.ajaxUrl,
        data
    ).then(
        typeof successCallback === 'function'
            ? successCallback(response)
            : function(response) {
                console.log(response);
            }
    ).catch(
        typeof failCallback === 'function'
            ? failCallback(error)
            : function(error) {
                console.log(error);
            }
    )
}

/** Add global error handler **/
window.addEventListener('error', function(e) {
    "use strict";
    if (e.error.stack) {
        e.error.message += '\n' + e.error.stack;
    }
    if (config.debug) {
        console.log(e.error.message);
    }
});