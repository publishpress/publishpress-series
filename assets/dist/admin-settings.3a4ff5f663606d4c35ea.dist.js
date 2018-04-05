webpackJsonp([0],{

/***/ "./assets/src/admin-settings.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/***/ }),

/***/ "./assets/src/license-management.js":
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _domReady = __webpack_require__("./node_modules/@wordpress/dom-ready/build-module/index.js");

var _domReady2 = _interopRequireDefault(_domReady);

var _jquery = __webpack_require__("jquery");

var _jquery2 = _interopRequireDefault(_jquery);

var _osjs = __webpack_require__("osjs");

var os = _interopRequireWildcard(_osjs);

var _wpNoticeBuilder = __webpack_require__("./assets/src/modules/wp-notice-builder.js");

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
var CONTAINER_NOTICES_CLASS = '.os-license-notices';

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
        if (os.config.debug) {
            console.log(activationAction);
        }
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
        if (typeof notices !== 'string') {
            if (os.config.debug) {
                console.log(notices);
            }
            throw new TypeError('Invalid notices type. Expected a string.');
        }
        /** @todo left off here trying to figure out why this isn't showing the notices!)
         *  - also need to figure out why our global error handler in os-common.js isn't working as expected.
         */
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

/***/ "./assets/src/modules/wp-notice-builder.js":
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

/***/ }),

/***/ "./node_modules/@wordpress/dom-ready/build-module/index.js":
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

/***/ 1:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__("./assets/src/admin-settings.js");
module.exports = __webpack_require__("./assets/src/license-management.js");


/***/ }),

/***/ "jquery":
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),

/***/ "osjs":
/***/ (function(module, exports) {

module.exports = osjs;

/***/ })

},[1]);
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL2xpY2Vuc2UtbWFuYWdlbWVudC5qcyIsIndlYnBhY2s6Ly8vLi9hc3NldHMvc3JjL21vZHVsZXMvd3Atbm90aWNlLWJ1aWxkZXIuanMiLCJ3ZWJwYWNrOi8vLy4vbm9kZV9tb2R1bGVzL0B3b3JkcHJlc3MvZG9tLXJlYWR5L2J1aWxkLW1vZHVsZS9pbmRleC5qcyIsIndlYnBhY2s6Ly8vZXh0ZXJuYWwgXCJqUXVlcnlcIiIsIndlYnBhY2s6Ly8vZXh0ZXJuYWwgXCJvc2pzXCIiXSwibmFtZXMiOlsib3MiLCJBQ1RJT05fQUNUSVZBVElPTiIsIkFDVElPTl9ERUFDVElWQVRJT04iLCJDT05UQUlORVJfTk9USUNFU19DTEFTUyIsIm9uIiwiZSIsInByZXZlbnREZWZhdWx0Iiwic3RvcFByb3BhZ2F0aW9uIiwiYnV0dG9uRGF0YSIsImRhdGEiLCJhY3RpdmF0aW9uQWN0aW9uIiwiaGFzQ2xhc3MiLCJub25jZUZpZWxkIiwiZXh0ZW5zaW9uIiwiYWN0aW9uIiwibm9uY2UiLCJ2YWwiLCJsaWNlbnNlX2tleSIsImNvbnRhaW5lclNlbGVjdG9yIiwiY29udGFpbmVyIiwic3Bpbm5lclNlbGVjdG9yIiwiY29uZmlnIiwiZGVidWciLCJjb25zb2xlIiwibG9nIiwidG9nZ2xlQWpheFNwaW5uZXIiLCJhamF4IiwicmVzcG9uc2UiLCJoYW5kbGVMaWNlbnNlS2V5U3VjY2VzcyIsImVycm9yIiwiaGFuZGxlTGljZW5zZUtleUVycm9yIiwibGljZW5zZU1ldGFDb250YWluZXIiLCJjb250ZW50IiwiaHRtbCIsInN1Y2Nlc3MiLCJzd2l0Y2hCdXR0b24iLCJub3RpY2VzIiwic2hvd05vdGljZXMiLCJyZXBsYWNlTm9uY2UiLCJtZXNzYWdlIiwidHlwZSIsInN0YXR1cyIsInJlcXVlc3QiLCJjdXJyZW50QnV0dG9uIiwiY2xhc3NSZXBsYWNlZCIsImNsYXNzQWRkZWQiLCJidXR0b25OYW1lQXR0ciIsImJ1dHRvblRleHQiLCJpMThuIiwiZGVhY3RpdmF0ZUJ1dHRvblRleHQiLCJhY3RpdmF0ZUJ1dHRvblRleHQiLCJyZW1vdmVDbGFzcyIsImFkZENsYXNzIiwiYXR0ciIsIlR5cGVFcnJvciIsIm5vdGljZUJ1aWxkZXIiLCJkaXNtaXNzaWJsZSIsImlzRGlzbWlzc2libGUiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7QUFHQTs7OztBQUtBOzs7O0FBQ0E7O0lBQVlBLEU7O0FBQ1o7Ozs7OztBQVZBOzs7QUFZQSxJQUFNQyxvQkFBb0IsWUFBMUI7O0FBUEE7Ozs7QUFRQSxJQUFNQyxzQkFBc0IsY0FBNUI7QUFDQSxJQUFNQywwQkFBMEIscUJBQWhDOztBQUVBLHdCQUFTLFlBQVU7QUFDZiwwQkFBRSwyQkFBRixFQUErQkMsRUFBL0IsQ0FBa0MsT0FBbEMsRUFBMkMsb0JBQTNDLEVBQWlFLFVBQVNDLENBQVQsRUFBVztBQUN4RUEsVUFBRUMsY0FBRjtBQUNBRCxVQUFFRSxlQUFGO0FBQ0EsWUFBSUMsYUFBYSxzQkFBRSxJQUFGLEVBQVFDLElBQVIsRUFBakI7QUFBQSxZQUNJQyxtQkFBbUIsc0JBQUUsSUFBRixFQUFRQyxRQUFSLENBQWlCLG1CQUFqQixJQUF3Q1YsaUJBQXhDLEdBQTREQyxtQkFEbkY7QUFBQSxZQUVJVSxhQUFhLHNCQUFFLDJCQUEyQkosV0FBV0ssU0FBeEMsQ0FGakI7QUFBQSxZQUdJSixPQUFPO0FBQ0hLLG9CQUFTLG9CQUFvQkosZ0JBRDFCO0FBRUhLLG1CQUFRSCxXQUFXSSxHQUFYLEVBRkw7QUFHSEMseUJBQWMsc0JBQUUscUJBQXFCVCxXQUFXSyxTQUFsQyxFQUE2Q0csR0FBN0MsRUFIWDtBQUlISCx1QkFBWUwsV0FBV0s7QUFKcEIsU0FIWDtBQUFBLFlBU0lLLG9CQUFvQiwrQkFBK0JWLFdBQVdLLFNBVGxFO0FBQUEsWUFVSU0sWUFBWSxzQkFBRUQsaUJBQUYsQ0FWaEI7QUFBQSxZQVdJRSxrQkFBa0JGLG9CQUFvQixHQUFwQixHQUEwQixVQVhoRDtBQVlBLFlBQUlsQixHQUFHcUIsTUFBSCxDQUFV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QUFwQixFQUE4QjtBQUMxQixjQUFNLElBQUljLFNBQUosQ0FBYyw4Q0FBZCxDQUFOO0FBQ0g7QUFDRCxXQUFPLHdCQUF3QmQsSUFBeEIsR0FBK0JpQixhQUEvQixHQUErQyxPQUEvQyxHQUF5RGxCLE9BQXpELEdBQW1FLFlBQTFFO0FBQ0gsQzs7Ozs7Ozs7QUNqQkQ7QUFBQTtBQUNBO0FBQ0E7QUFDQSxXQUFXLFNBQVM7QUFDcEI7QUFDQSxhQUFhO0FBQ2I7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBOztBQUVBLHlFOzs7Ozs7Ozs7Ozs7Ozs7O0FDZkEsd0I7Ozs7Ozs7QUNBQSxzQiIsImZpbGUiOiJhZG1pbi1zZXR0aW5ncy4zYTRmZjVmNjYzNjA2ZDRjMzVlYS5kaXN0LmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBJbnRlcm5hbCBJbXBvcnRzXG4gKi9cbmltcG9ydCBkb21SZWFkeSBmcm9tICdAd29yZHByZXNzL2RvbS1yZWFkeSc7XG5cbi8qKlxuICogRXh0ZXJuYWwgSW1wb3J0cy5cbiAqL1xuaW1wb3J0ICQgZnJvbSAnanF1ZXJ5JztcbmltcG9ydCAqIGFzIG9zIGZyb20gJ29zanMnO1xuaW1wb3J0IHtub3RpY2VCdWlsZGVyfSBmcm9tIFwiLi9tb2R1bGVzL3dwLW5vdGljZS1idWlsZGVyXCI7XG5cbmNvbnN0IEFDVElPTl9BQ1RJVkFUSU9OID0gJ2FjdGl2YXRpb24nO1xuY29uc3QgQUNUSU9OX0RFQUNUSVZBVElPTiA9ICdkZWFjdGl2YXRpb24nO1xuY29uc3QgQ09OVEFJTkVSX05PVElDRVNfQ0xBU1MgPSAnLm9zLWxpY2Vuc2Utbm90aWNlcyc7XG5cbmRvbVJlYWR5KGZ1bmN0aW9uKCl7XG4gICAgJCgnLm9zLWxpY2Vuc2Uta2V5LWNvbnRhaW5lcicpLm9uKCdjbGljaycsICcuanMtbGljZW5zZS1zdWJtaXQnLCBmdW5jdGlvbihlKXtcbiAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICBlLnN0b3BQcm9wYWdhdGlvbigpO1xuICAgICAgICBsZXQgYnV0dG9uRGF0YSA9ICQodGhpcykuZGF0YSgpLFxuICAgICAgICAgICAgYWN0aXZhdGlvbkFjdGlvbiA9ICQodGhpcykuaGFzQ2xhc3MoJ2FjdGl2YXRpb24tYnV0dG9uJykgPyBBQ1RJT05fQUNUSVZBVElPTiA6IEFDVElPTl9ERUFDVElWQVRJT04sXG4gICAgICAgICAgICBub25jZUZpZWxkID0gJCgnI29zX2xpY2Vuc2Vfa2V5X25vbmNlXycgKyBidXR0b25EYXRhLmV4dGVuc2lvbiksXG4gICAgICAgICAgICBkYXRhID0ge1xuICAgICAgICAgICAgICAgIGFjdGlvbiA6ICdvc19saWNlbnNlX2tleV8nICsgYWN0aXZhdGlvbkFjdGlvbixcbiAgICAgICAgICAgICAgICBub25jZSA6IG5vbmNlRmllbGQudmFsKCksXG4gICAgICAgICAgICAgICAgbGljZW5zZV9rZXkgOiAkKCcjb3MtbGljZW5zZS1rZXktJyArIGJ1dHRvbkRhdGEuZXh0ZW5zaW9uKS52YWwoKSxcbiAgICAgICAgICAgICAgICBleHRlbnNpb24gOiBidXR0b25EYXRhLmV4dGVuc2lvblxuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGNvbnRhaW5lclNlbGVjdG9yID0gJyNvcy1saWNlbnNlLWtleS1jb250YWluZXItJyArIGJ1dHRvbkRhdGEuZXh0ZW5zaW9uLFxuICAgICAgICAgICAgY29udGFpbmVyID0gJChjb250YWluZXJTZWxlY3RvciksXG4gICAgICAgICAgICBzcGlubmVyU2VsZWN0b3IgPSBjb250YWluZXJTZWxlY3RvciArICcgJyArICcuc3Bpbm5lcic7XG4gICAgICAgIGlmIChvcy5jb25maWcuZGVidWcpIHtcbiAgICAgICAgICAgIGNvbnNvbGUubG9nKGRhdGEpO1xuICAgICAgICB9XG4gICAgICAgIG9zLnRvZ2dsZUFqYXhTcGlubmVyKHNwaW5uZXJTZWxlY3Rvcik7XG4gICAgICAgICAgICBvcy5hamF4KFxuICAgICAgICAgICAgICAgIGRhdGEsXG4gICAgICAgICAgICAgICAgdHJ1ZSxcbiAgICAgICAgICAgICAgICBmdW5jdGlvbihyZXNwb25zZSkge1xuICAgICAgICAgICAgICAgICAgICBvcy50b2dnbGVBamF4U3Bpbm5lcihzcGlubmVyU2VsZWN0b3IpO1xuICAgICAgICAgICAgICAgICAgICBoYW5kbGVMaWNlbnNlS2V5U3VjY2Vzcyhjb250YWluZXIsIGFjdGl2YXRpb25BY3Rpb24sIG5vbmNlRmllbGQsIHJlc3BvbnNlKTtcbiAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgIGZ1bmN0aW9uKGVycm9yKSB7XG4gICAgICAgICAgICAgICAgICAgIG9zLnRvZ2dsZUFqYXhTcGlubmVyKHNwaW5uZXJTZWxlY3Rvcik7XG4gICAgICAgICAgICAgICAgICAgIGhhbmRsZUxpY2Vuc2VLZXlFcnJvcihjb250YWluZXIsIGFjdGl2YXRpb25BY3Rpb24sIGVycm9yKTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICApO1xuICAgIH0pO1xuXG4gICAgLyoqXG4gICAgICogSGFuZGxlcyB0aGUgYWpheCBzdWNjZXNzIGZvciBsaWNlbnNlIGtleSBhY3RpdmF0aW9uL2RlYWN0aXZhdGlvbi5cbiAgICAgKiBAcGFyYW0ge09iamVjdH0gY29udGFpbmVyICBqUXVlcnkgY29udGFpbmVyIG9iamVjdCBmb3Igd2hlcmUgdGhlIGh0bWwgY29udGVudCB3aWxsIGJlIG91dHB1dC5cbiAgICAgKiBAcGFyYW0ge1N0cmluZ30gYWN0aXZhdGlvbkFjdGlvbiAgV2hhdCB0eXBlIG9mIGFjdGl2YXRpb24gdGhpcyBpcyAoYWN0aXZhdGlvbiBvciBkZWFjdGl2YXRpb24pLlxuICAgICAqIEBwYXJhbSB7T2JqZWN0fSBub25jZUZpZWxkIGpRdWVyeSBjb250YWluZXIgb2JqZWN0IGZvciB0aGUgbm9uY2UgZmllbGRcbiAgICAgKiBAcGFyYW0ge09iamVjdH0gcmVzcG9uc2UgICBAc2VlIGF4aW9zIHJlc3BvbnNlIHNjaGVtYS5cbiAgICAgKi9cbiAgICBmdW5jdGlvbiBoYW5kbGVMaWNlbnNlS2V5U3VjY2Vzcyhjb250YWluZXIsIGFjdGl2YXRpb25BY3Rpb24sIG5vbmNlRmllbGQsIHJlc3BvbnNlKSB7XG4gICAgICAgIGxldCBsaWNlbnNlTWV0YUNvbnRhaW5lciA9ICQoJy5vcy1saWNlbnNlLWtleS1tZXRhJywgY29udGFpbmVyKTtcbiAgICAgICAgaWYgKHJlc3BvbnNlLmRhdGEuY29udGVudCkge1xuICAgICAgICAgICAgbGljZW5zZU1ldGFDb250YWluZXIuaHRtbChyZXNwb25zZS5kYXRhLmNvbnRlbnQpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChyZXNwb25zZS5kYXRhLnN1Y2Nlc3MpIHtcbiAgICAgICAgICAgIHN3aXRjaEJ1dHRvbihhY3RpdmF0aW9uQWN0aW9uLCBjb250YWluZXIpO1xuICAgICAgICB9XG4gICAgICAgIGlmIChyZXNwb25zZS5kYXRhLm5vdGljZXMpIHtcbiAgICAgICAgICAgIHNob3dOb3RpY2VzKHJlc3BvbnNlLmRhdGEubm90aWNlcywgY29udGFpbmVyKTtcbiAgICAgICAgfVxuICAgICAgICBpZiAocmVzcG9uc2UuZGF0YS5ub25jZSkge1xuICAgICAgICAgICAgcmVwbGFjZU5vbmNlKHJlc3BvbnNlLmRhdGEubm9uY2UsIG5vbmNlRmllbGQpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogSGFuZGxlcyB0aGUgYWpheCBmYWlsIGZvciBsaWNlbnNlIGtleSBhY3RpdmF0aW9uL2RlYWN0aXZhdGlvbi5cbiAgICAgKiBAcGFyYW0ge09iamVjdH0gY29udGFpbmVyICBqUXVlcnkgY29udGFpbmVyIG9iamVjdCBmb3Igd2hlcmUgdGhlIGh0bWwgY29udGVudCB3aWxsIGJlIG91dHB1dC5cbiAgICAgKiBAcGFyYW0ge1N0cmluZ30gYWN0aXZhdGlvbkFjdGlvblxuICAgICAqIEBwYXJhbSB7T2JqZWN0fSBlcnJvciAgIEBzZWUgYXhpb3MgZXJyb3Igc2NoZW1hLlxuICAgICAqL1xuICAgIGZ1bmN0aW9uIGhhbmRsZUxpY2Vuc2VLZXlFcnJvcihjb250YWluZXIsIGFjdGl2YXRpb25BY3Rpb24sIGVycm9yKSB7XG4gICAgICAgIGxldCBtZXNzYWdlID0gJycsXG4gICAgICAgICAgICB0eXBlID0gJ2Vycm9yJztcbiAgICAgICAgaWYgKGVycm9yLnJlc3BvbnNlKSB7XG4gICAgICAgICAgICBtZXNzYWdlID0gdHlwZW9mIGVycm9yLnJlc3BvbnNlLmRhdGEuY29udGVudCAhPT0gJ3VuZGVmaW5lZCdcbiAgICAgICAgICAgICAgICA/IGVycm9yLnJlc3BvbnNlLmRhdGEuY29udGVudFxuICAgICAgICAgICAgICAgIDogJ1RoZXJlIHdhcyBhIHByb2JsZW0gd2l0aCB0aGUgYWpheCByZXF1ZXN0LiBSZXR1cm5lZCBzdGF0dXMgb2Y6ICcgKyByZXNwb25zZS5zdGF0dXM7XG4gICAgICAgIH0gZWxzZSBpZiAoZXJyb3IucmVxdWVzdCkge1xuICAgICAgICAgICAgY29uc29sZS5sb2coZXJyb3IucmVxdWVzdCk7XG4gICAgICAgICAgICBtZXNzYWdlID0gZXJyb3IucmVxdWVzdC5yZXNwb25zZTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIG1lc3NhZ2UgPSBlcnJvci5tZXNzYWdlO1xuICAgICAgICB9XG4gICAgICAgIHNob3dOb3RpY2VzKG5vdGljZUJ1aWxkZXIodHlwZSwgbWVzc2FnZSwgZmFsc2UpLCBjb250YWluZXIpO1xuICAgIH1cblxuXG4gICAgLyoqXG4gICAgICogU3dpdGNoIGFsbCB0aGUgZWxlbWVudHMgZm9yIHRoZSBhY3RpdmF0aW9uL2RlYWN0aXZhdGlvbiBidXR0b24uXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGFjdGl2YXRpb25BY3Rpb25cbiAgICAgKiBAcGFyYW0ge09iamVjdH0gY29udGFpbmVyICBqUXVlcnkgc2VsZWN0b3IgZm9yIHRoZSBjb250YWluZXIgY29udGFpbmluZyB0aGUgYnV0dG9uLlxuICAgICAqL1xuICAgIGZ1bmN0aW9uIHN3aXRjaEJ1dHRvbihhY3RpdmF0aW9uQWN0aW9uLCBjb250YWluZXIpIHtcbiAgICAgICAgaWYgKG9zLmNvbmZpZy5kZWJ1Zykge1xuICAgICAgICAgICAgY29uc29sZS5sb2coYWN0aXZhdGlvbkFjdGlvbik7XG4gICAgICAgIH1cbiAgICAgICAgbGV0IGN1cnJlbnRCdXR0b24gPSAkKCcuanMtbGljZW5zZS1zdWJtaXQnLCBjb250YWluZXIpLFxuICAgICAgICAgICAgY2xhc3NSZXBsYWNlZCA9IEFDVElPTl9BQ1RJVkFUSU9OID09PSBhY3RpdmF0aW9uQWN0aW9uXG4gICAgICAgICAgICAgICAgPyBBQ1RJT05fQUNUSVZBVElPTiArICctYnV0dG9uJ1xuICAgICAgICAgICAgICAgIDogQUNUSU9OX0RFQUNUSVZBVElPTiArICctYnV0dG9uJyxcbiAgICAgICAgICAgIGNsYXNzQWRkZWQgPSBBQ1RJT05fQUNUSVZBVElPTiA9PT0gYWN0aXZhdGlvbkFjdGlvblxuICAgICAgICAgICAgICAgID8gQUNUSU9OX0RFQUNUSVZBVElPTiArICctYnV0dG9uJ1xuICAgICAgICAgICAgICAgIDogQUNUSU9OX0FDVElWQVRJT04gKyAnLWJ1dHRvbicsXG4gICAgICAgICAgICBidXR0b25OYW1lQXR0ciA9IEFDVElPTl9BQ1RJVkFUSU9OID09PSBhY3RpdmF0aW9uQWN0aW9uXG4gICAgICAgICAgICAgICAgPyAnb3NfbGljZW5zZV9rZXlfZGVhY3RpdmF0ZSdcbiAgICAgICAgICAgICAgICA6ICdvc19saWNlbnNlX2tleV9hY3RpdmF0ZScsXG4gICAgICAgICAgICBidXR0b25UZXh0ID0gQUNUSU9OX0FDVElWQVRJT04gPT09IGFjdGl2YXRpb25BY3Rpb25cbiAgICAgICAgICAgICAgICA/IG9zLmkxOG4uZGVhY3RpdmF0ZUJ1dHRvblRleHRcbiAgICAgICAgICAgICAgICA6IG9zLmkxOG4uYWN0aXZhdGVCdXR0b25UZXh0O1xuICAgICAgICBjdXJyZW50QnV0dG9uLnJlbW92ZUNsYXNzKGNsYXNzUmVwbGFjZWQpLmFkZENsYXNzKGNsYXNzQWRkZWQpLmF0dHIoJ25hbWUnLCBidXR0b25OYW1lQXR0cikudmFsKGJ1dHRvblRleHQpO1xuICAgIH1cblxuXG4gICAgLyoqXG4gICAgICogQWRkcyB0aGUgaW5jb21pbmcgbm90aWNlcyAoZXhwZWN0ZWQgaHRtbCBzdHJpbmcpIHRvIHRoZSBub3RpY2VzIGNvbnRhaW5lci5cbiAgICAgKiBAcGFyYW0gbm90aWNlc1xuICAgICAqIEBwYXJhbSBjb250YWluZXJcbiAgICAgKi9cbiAgICBmdW5jdGlvbiBzaG93Tm90aWNlcyhub3RpY2VzLCBjb250YWluZXIpXG4gICAge1xuICAgICAgICBpZiAodHlwZW9mIG5vdGljZXMgIT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICBpZiAob3MuY29uZmlnLmRlYnVnKSB7XG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2cobm90aWNlcyk7XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICB0aHJvdyBuZXcgVHlwZUVycm9yKCdJbnZhbGlkIG5vdGljZXMgdHlwZS4gRXhwZWN0ZWQgYSBzdHJpbmcuJyk7XG4gICAgICAgIH1cbiAgICAgICAgLyoqIEB0b2RvIGxlZnQgb2ZmIGhlcmUgdHJ5aW5nIHRvIGZpZ3VyZSBvdXQgd2h5IHRoaXMgaXNuJ3Qgc2hvd2luZyB0aGUgbm90aWNlcyEpXG4gICAgICAgICAqICAtIGFsc28gbmVlZCB0byBmaWd1cmUgb3V0IHdoeSBvdXIgZ2xvYmFsIGVycm9yIGhhbmRsZXIgaW4gb3MtY29tbW9uLmpzIGlzbid0IHdvcmtpbmcgYXMgZXhwZWN0ZWQuXG4gICAgICAgICAqL1xuICAgICAgICAkKENPTlRBSU5FUl9OT1RJQ0VTX0NMQVNTLCBjb250YWluZXIpLmh0bWwobm90aWNlcyk7XG4gICAgfVxuXG5cbiAgICAvKipcbiAgICAgKiBSZXBsYWNlcyB0aGUgbm9uY2UgZm9yIHRoaXMgbGljZW5zZSBrZXkgdXBkYXRlIHdpdGggdGhlIGdpdmVuIHZhbHVlLlxuICAgICAqIEBwYXJhbSB7U3RyaW5nfSBub25jZSAgICAgICBOZXcgbm9uY2UuXG4gICAgICogQHBhcmFtIHtPYmplY3R9IG5vbmNlRmllbGQgIGpRdWVyeSBjb250YWluZXIgZm9yIHRoZSBub25jZSBmaWVsZFxuICAgICAqL1xuICAgIGZ1bmN0aW9uIHJlcGxhY2VOb25jZShub25jZSwgbm9uY2VGaWVsZCkge1xuICAgICAgICBub25jZUZpZWxkLnZhbChub25jZSk7XG4gICAgfVxufSk7XG5cblxuLy8gV0VCUEFDSyBGT09URVIgLy9cbi8vIC4vYXNzZXRzL3NyYy9saWNlbnNlLW1hbmFnZW1lbnQuanMiLCJcInVzZSBzdHJpY3RcIjtcbi8qKlxuICogQnVpbGRzIGEgbm90aWNlIGNvbnRhaW5lclxuICogQHBhcmFtIHtTdHJpbmd9IHR5cGVcbiAqIEBwYXJhbSB7U3RyaW5nfSBtZXNzYWdlXG4gKiBAcGFyYW0ge0Jvb2xlYW59IGRpc21pc3NpYmxlXG4gKiBAcmV0dXJuIHtTdHJpbmd9XG4gKi9cbmV4cG9ydCBmdW5jdGlvbiBub3RpY2VCdWlsZGVyKHR5cGUsIG1lc3NhZ2UsIGRpc21pc3NpYmxlID0gZmFsc2UpIHtcbiAgICBsZXQgaXNEaXNtaXNzaWJsZSA9IGRpc21pc3NpYmxlID8gJyBpcy1kaXNtaXNzaWJsZScgOiAnJztcbiAgICBpZiAodHlwZW9mIG1lc3NhZ2UgIT09ICdzdHJpbmcnKSB7XG4gICAgICAgIHRocm93IG5ldyBUeXBlRXJyb3IoJ0luY29taW5nIHttZXNzYWdlfSB2YXJpYWJsZSBzaG91bGQgYmUgYSBzdHJpbmcuJyk7XG4gICAgfVxuICAgIGlmICh0eXBlb2YgdHlwZSAhPT0gJ3N0cmluZycpIHtcbiAgICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcignSW5jb21pbmcge3R5cGV9IHZhcmlhYmxlIHNob3VsZCBiZSBhIHN0cmluZy4nKVxuICAgIH1cbiAgICByZXR1cm4gJzxkaXYgY2xhc3M9XCJub3RpY2UgJyArIHR5cGUgKyBpc0Rpc21pc3NpYmxlICsgJ1wiPjxwPicgKyBtZXNzYWdlICsgJzwvcD48L2Rpdj4nO1xufVxuXG5cbi8vIFdFQlBBQ0sgRk9PVEVSIC8vXG4vLyAuL2Fzc2V0cy9zcmMvbW9kdWxlcy93cC1ub3RpY2UtYnVpbGRlci5qcyIsIi8qKlxuICogU3BlY2lmeSBhIGZ1bmN0aW9uIHRvIGV4ZWN1dGUgd2hlbiB0aGUgRE9NIGlzIGZ1bGx5IGxvYWRlZC5cbiAqXG4gKiBAcGFyYW0ge0Z1bmN0aW9ufSBjYWxsYmFjayBBIGZ1bmN0aW9uIHRvIGV4ZWN1dGUgYWZ0ZXIgdGhlIERPTSBpcyByZWFkeS5cbiAqXG4gKiBAcmV0dXJucyB7dm9pZH1cbiAqL1xudmFyIGRvbVJlYWR5ID0gZnVuY3Rpb24gZG9tUmVhZHkoY2FsbGJhY2spIHtcbiAgaWYgKGRvY3VtZW50LnJlYWR5U3RhdGUgPT09ICdjb21wbGV0ZScpIHtcbiAgICByZXR1cm4gY2FsbGJhY2soKTtcbiAgfVxuXG4gIGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoJ0RPTUNvbnRlbnRMb2FkZWQnLCBjYWxsYmFjayk7XG59O1xuXG5leHBvcnQgZGVmYXVsdCBkb21SZWFkeTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyAuL25vZGVfbW9kdWxlcy9Ad29yZHByZXNzL2RvbS1yZWFkeS9idWlsZC1tb2R1bGUvaW5kZXguanNcbi8vIG1vZHVsZSBpZCA9IC4vbm9kZV9tb2R1bGVzL0B3b3JkcHJlc3MvZG9tLXJlYWR5L2J1aWxkLW1vZHVsZS9pbmRleC5qc1xuLy8gbW9kdWxlIGNodW5rcyA9IDAiLCJtb2R1bGUuZXhwb3J0cyA9IGpRdWVyeTtcblxuXG4vLy8vLy8vLy8vLy8vLy8vLy9cbi8vIFdFQlBBQ0sgRk9PVEVSXG4vLyBleHRlcm5hbCBcImpRdWVyeVwiXG4vLyBtb2R1bGUgaWQgPSBqcXVlcnlcbi8vIG1vZHVsZSBjaHVua3MgPSAwIiwibW9kdWxlLmV4cG9ydHMgPSBvc2pzO1xuXG5cbi8vLy8vLy8vLy8vLy8vLy8vL1xuLy8gV0VCUEFDSyBGT09URVJcbi8vIGV4dGVybmFsIFwib3Nqc1wiXG4vLyBtb2R1bGUgaWQgPSBvc2pzXG4vLyBtb2R1bGUgY2h1bmtzID0gMCJdLCJzb3VyY2VSb290IjoiIn0=