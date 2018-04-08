/**
 * Internal Imports
 */
import domReady from '@wordpress/dom-ready';

/**
 * External Imports.
 */
import $ from 'jquery';
import * as os from 'osjs';
import {noticeBuilder} from "./modules/wp-notice-builder";

const ACTION_ACTIVATION = 'activation';
const ACTION_DEACTIVATION = 'deactivation';
const CONTAINER_NOTICES_CLASS = '.os-license-notices';

domReady(function(){
    $('.os-license-key-container').on('click', '.js-license-submit', function(e){
        e.preventDefault();
        e.stopPropagation();
        let buttonData = $(this).data(),
            activationAction = $(this).hasClass('activation-button') ? ACTION_ACTIVATION : ACTION_DEACTIVATION,
            nonceField = $('#os_license_key_nonce_' + buttonData.extension),
            data = {
                action : 'os_license_key_' + activationAction,
                nonce : nonceField.val(),
                license_key : $('#os-license-key-' + buttonData.extension).val(),
                extension : buttonData.extension
            },
            containerSelector = '#os-license-key-container-' + buttonData.extension,
            container = $(containerSelector),
            spinnerSelector = containerSelector + ' ' + '.spinner';
        if (os.config.debug) {
            console.log(data);
        }
        os.toggleAjaxSpinner(spinnerSelector);
            os.ajax(
                data,
                true,
                function(response) {
                    os.toggleAjaxSpinner(spinnerSelector);
                    handleLicenseKeySuccess(container, activationAction, nonceField, response);
                },
                function(error) {
                    os.toggleAjaxSpinner(spinnerSelector);
                    handleLicenseKeyError(container, activationAction, error);
                }
            );
    });

    /**
     * Handles the ajax success for license key activation/deactivation.
     * @param {Object} container  jQuery container object for where the html content will be output.
     * @param {String} activationAction  What type of activation this is (activation or deactivation).
     * @param {Object} nonceField jQuery container object for the nonce field
     * @param {Object} response   @see axios response schema.
     */
    function handleLicenseKeySuccess(container, activationAction, nonceField, response) {
        let licenseMetaContainer = $('.os-license-key-meta', container);
        if (response.data.content) {
            licenseMetaContainer.html(response.data.content);
        }
        if (response.data.success) {
            switchButton(activationAction, container);
        }
        if (response.data.notices) {
            showNotices(response.data.notices, container);
        }
        if (response.data.nonce) {
            replaceNonce(response.data.nonce, nonceField);
        }
    }

    /**
     * Handles the ajax fail for license key activation/deactivation.
     * @param {Object} container  jQuery container object for where the html content will be output.
     * @param {String} activationAction
     * @param {Object} error   @see axios error schema.
     */
    function handleLicenseKeyError(container, activationAction, error) {
        let message = '',
            type = 'error';
        if (error.response) {
            message = typeof error.response.data.content !== 'undefined'
                ? error.response.data.content
                : 'There was a problem with the ajax request. Returned status of: ' + response.status;
        } else if (error.request) {
            console.log(error.request);
            message = error.request.response;
        } else {
            message = error.message;
        }
        showNotices(noticeBuilder(type, message, false), container);
    }


    /**
     * Switch all the elements for the activation/deactivation button.
     * @param {string} activationAction
     * @param {Object} container  jQuery selector for the container containing the button.
     */
    function switchButton(activationAction, container) {
        if (os.config.debug) {
            console.log(activationAction);
        }
        let currentButton = $('.js-license-submit', container),
            classReplaced = ACTION_ACTIVATION === activationAction
                ? ACTION_ACTIVATION + '-button'
                : ACTION_DEACTIVATION + '-button',
            classAdded = ACTION_ACTIVATION === activationAction
                ? ACTION_DEACTIVATION + '-button'
                : ACTION_ACTIVATION + '-button',
            buttonNameAttr = ACTION_ACTIVATION === activationAction
                ? 'os_license_key_deactivate'
                : 'os_license_key_activate',
            buttonText = ACTION_ACTIVATION === activationAction
                ? os.i18n.deactivateButtonText
                : os.i18n.activateButtonText;
        currentButton.removeClass(classReplaced).addClass(classAdded).attr('name', buttonNameAttr).val(buttonText);
    }


    /**
     * Adds the incoming notices (expected html string) to the notices container.
     * @param notices
     * @param container
     */
    function showNotices(notices, container)
    {
        if (typeof notices !== 'string') {
            if (os.config.debug) {
                console.log(notices);
            }
            throw new TypeError('Invalid notices type. Expected a string.');
        }
        /** @todo left off here trying to figure out why this isn't showing the notices!)
         *  - also need to figure out why our global error handler in os-common.js isn't working as expected.
         */
        $(CONTAINER_NOTICES_CLASS, container).html(notices);
    }


    /**
     * Replaces the nonce for this license key update with the given value.
     * @param {String} nonce       New nonce.
     * @param {Object} nonceField  jQuery container for the nonce field
     */
    function replaceNonce(nonce, nonceField) {
        nonceField.val(nonce);
    }
});