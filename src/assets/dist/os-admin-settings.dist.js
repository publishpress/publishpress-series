/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */,
/* 1 */,
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(3);
module.exports = __webpack_require__(4);


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _domReady = __webpack_require__(5);

var _domReady2 = _interopRequireDefault(_domReady);

var _jquery = __webpack_require__(6);

var _jquery2 = _interopRequireDefault(_jquery);

var _osjs = __webpack_require__(7);

var os = _interopRequireWildcard(_osjs);

var _wpNoticeBuilder = __webpack_require__(8);

function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj.default = obj; return newObj; } }

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Internal Imports
 */
var ACTION_ACTIVATION = 'activation';

/**
 * External Imports.
 */

var ACTION_DEACTIVATION = 'deactivation';
var CONTAINER_NOTICES_CLASS = 'os-license-notices';

(0, _domReady2.default)(function () {
    (0, _jquery2.default)('.os-license-key-container').on('click', '.js-license-submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var buttonData = (0, _jquery2.default)(this).data(),
            activationAction = (0, _jquery2.default)(this).hasClass('activation-button') ? ACTION_ACTIVATION : ACTION_DEACTIVATION,
            nonceField = (0, _jquery2.default)('#os_license_key_nonce_' + buttonData.extension),
            data = {
            action: 'os_license_key_' + activationAction,
            nonce: nonceField.val(),
            license_key: (0, _jquery2.default)('#os-license-key-' + buttonData.extension).val(),
            extension: buttonData.extension
        },
            containerSelector = '#os-license-key-container-' + buttonData.extension,
            container = (0, _jquery2.default)(containerSelector),
            spinnerSelector = containerSelector + ' ' + '.spinner';
        if (os.config.debug) {
            console.log(data);
        }
        os.toggleAjaxSpinner(spinnerSelector);
        os.ajax(data, true, function (response) {
            os.toggleAjaxSpinner(spinnerSelector);
            handleLicenseKeySuccess(container, activationAction, nonceField, response);
        }, function (error) {
            os.toggleAjaxSpinner(spinnerSelector);
            handleLicenseKeyError(container, activationAction, error);
        });
    });

    /**
     * Handles the ajax success for license key activation/deactivation.
     * @param {Object} container  jQuery container object for where the html content will be output.
     * @param {String} activationAction  What type of activation this is (activation or deactivation).
     * @param {Object} nonceField jQuery container object for the nonce field
     * @param {Object} response   @see axios response schema.
     */
    function handleLicenseKeySuccess(container, activationAction, nonceField, response) {
        var licenseMetaContainer = (0, _jquery2.default)('.os-license-key-meta', container);
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
        var message = '',
            type = 'error';
        if (error.response) {
            message = typeof error.response.data.content !== 'undefined' ? error.response.data.content : 'There was a problem with the ajax request. Returned status of: ' + response.status;
        } else if (error.request) {
            console.log(error.request);
            message = error.request.response;
        } else {
            message = error.message;
        }
        showNotices((0, _wpNoticeBuilder.noticeBuilder)(type, message, false), container);
    }

    /**
     * Switch all the elements for the activation/deactivation button.
     * @param {string} activationAction
     * @param {Object} container  jQuery selector for the container containing the button.
     */
    function switchButton(activationAction, container) {
        var currentButton = (0, _jquery2.default)('.js-license-submit', container),
            classReplaced = ACTION_ACTIVATION === activationAction ? ACTION_ACTIVATION + '-button' : ACTION_DEACTIVATION + '-button',
            classAdded = ACTION_ACTIVATION === activationAction ? ACTION_DEACTIVATION + '-button' : ACTION_ACTIVATION + '-button',
            buttonNameAttr = ACTION_ACTIVATION === activationAction ? 'os_license_key_deactivate' : 'os_license_key_activate',
            buttonText = ACTION_ACTIVATION === activationAction ? os.i18n.deactivateButtonText : os.i18n.activateButtonText;
        currentButton.removeClass(classReplaced).addClass(classAdded).attr('name', buttonNameAttr).val(buttonText);
    }

    /**
     * Adds the incoming notices (expected html string) to the notices container.
     * @param notices
     * @param container
     */
    function showNotices(notices, container) {
        if (typeof notices !== 'undefined' || typeof notices !== 'string') {
            throw new TypeError('Invalid notices type. Expected a string.');
        }
        (0, _jquery2.default)(CONTAINER_NOTICES_CLASS, container).html(notices);
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

/***/ }),
/* 5 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/**
 * Specify a function to execute when the DOM is fully loaded.
 *
 * @param {Function} callback A function to execute after the DOM is ready.
 *
 * @returns {void}
 */
var domReady = function domReady(callback) {
  if (document.readyState === 'complete') {
    return callback();
  }

  document.addEventListener('DOMContentLoaded', callback);
};

/* harmony default export */ __webpack_exports__["default"] = (domReady);

/***/ }),
/* 6 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 7 */
/***/ (function(module, exports) {

module.exports = osjs;

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

/**
 * Builds a notice container
 * @param {String} type
 * @param {String} message
 * @param {Boolean} dismissible
 * @return {String}
 */

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.noticeBuilder = noticeBuilder;
function noticeBuilder(type, message) {
    var dismissible = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

    var isDismissible = dismissible ? ' is-dismissible' : '';
    if (typeof message !== 'string') {
        throw new TypeError('Incoming {message} variable should be a string.');
    }
    if (typeof type !== 'string') {
        throw new TypeError('Incoming {type} variable should be a string.');
    }
    return '<div class="notice ' + type + isDismissible + '"><p>' + message + '</p></div>';
}

/***/ })
/******/ ]);