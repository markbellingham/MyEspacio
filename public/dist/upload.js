/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./web/js/framework/Notify.js":
/*!************************************!*\
  !*** ./web/js/framework/Notify.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"Notify\": () => (/* binding */ Notify)\n/* harmony export */ });\n/**\n * Adapted from https://www.codingnepalweb.com/toast-notification-html-css-javascript/\n */\nclass Notify {\n    constructor(type, text) {\n        this.notifications = document.querySelector('.notifications');\n        this.timer = 5000;\n        this.createToast(type, text);\n    }\n\n    createToast(type, text) {\n        const icon = this.selectIcon(type);\n        const toast = document.createElement(\"li\");\n        toast.className = `toast ${type} show`;\n        // Setting the inner HTML for the toast\n        toast.innerHTML = `<div class=\"column\">\n                             ${icon}\n                             <span>${text}</span> \n                          </div>\n                          <i class=\" bi bi-x\" onclick=\"this.removeToast(this.parentElement)\"></i>`;\n        this.notifications.appendChild(toast);\n        toast.timeoutId = setTimeout(() => this.removeToast(toast), this.timer);\n    }\n\n    removeToast(toast) {\n        toast.classList.add(\"hide\");\n        if (toast.timeoutId) clearTimeout(toast.timeoutId); // Clearing the timeout for the toast\n        setTimeout(() => toast.remove(), 500); // Removing the toast after 500ms\n    }\n\n    selectIcon(icon) {\n        switch (icon) {\n            case 'success':\n                return '<i class=\"bi bi-check-circle-fill\"></i>';\n            case 'error':\n                return '<i class=\"bi bi-x-circle-fill\"></i>';\n            case 'warning':\n                return '<i class=\"bi bi-exclamation-circle-fill\"></i>';\n            case 'info':\n            default:\n                return '<i class=\"bi bi-info-circle-fill\"></i>';\n        }\n    }\n}\n\n//# sourceURL=webpack://personly/./web/js/framework/Notify.js?");

/***/ }),

/***/ "./web/js/framework/RequestHeaders.js":
/*!********************************************!*\
  !*** ./web/js/framework/RequestHeaders.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\nclass RequestHeaders {\n    html()\n    {\n        const headers = new Headers();\n        const token = $('#layout-token').value;\n        headers.append('X-Layout', token);\n        return headers;\n    }\n\n    json()\n    {\n        const headers = new Headers();\n        headers.append('Accept', 'application/json');\n        return headers;\n    }\n\n    jsonWithToken()\n    {\n        const headers = new Headers();\n        const token = $('#layout-token').value;\n        headers.append('X-Layout', token);\n        headers.append('Accept', 'application/json');\n        return headers;\n    }\n}\n\nconst requestHeaders = new RequestHeaders();\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (requestHeaders);\n\n//# sourceURL=webpack://personly/./web/js/framework/RequestHeaders.js?");

/***/ }),

/***/ "./web/js/framework/dom-selectors.js":
/*!*******************************************!*\
  !*** ./web/js/framework/dom-selectors.js ***!
  \*******************************************/
/***/ (() => {

eval("window.$ = document.querySelector.bind(document);\nwindow.$$ = document.querySelectorAll.bind(document);\n\n//# sourceURL=webpack://personly/./web/js/framework/dom-selectors.js?");

/***/ }),

/***/ "./web/js/pictures/upload.js":
/*!***********************************!*\
  !*** ./web/js/pictures/upload.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _framework_dom_selectors_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../framework/dom-selectors.js */ \"./web/js/framework/dom-selectors.js\");\n/* harmony import */ var _framework_dom_selectors_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_framework_dom_selectors_js__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _framework_RequestHeaders_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../framework/RequestHeaders.js */ \"./web/js/framework/RequestHeaders.js\");\n/* harmony import */ var _framework_Notify_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../framework/Notify.js */ \"./web/js/framework/Notify.js\");\n\n\n\n\n$('#photo-upload-submit').addEventListener('click', function(e) {\n    e.preventDefault();\n    const form = this.form;\n    const url = new URL(form.action);\n    const formData = new FormData(form);\n    fetch(url, {\n        method: form.method,\n        headers: _framework_RequestHeaders_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"].json(),\n        credentials: 'include',\n        body: formData\n    })\n        .then(response => response.json())\n        .then(data => {\n            if (data.success) {\n                new _framework_Notify_js__WEBPACK_IMPORTED_MODULE_2__.Notify('success', data.message);\n                form.reset();\n            } else {\n                new _framework_Notify_js__WEBPACK_IMPORTED_MODULE_2__.Notify('error', data.message);\n            }\n        });\n    });\n\n//# sourceURL=webpack://personly/./web/js/pictures/upload.js?");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	var __webpack_exports__ = __webpack_require__("./web/js/pictures/upload.js");
/******/ 	
/******/ })()
;