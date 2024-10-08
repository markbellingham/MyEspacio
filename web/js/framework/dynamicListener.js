/**
 * Including this file adds the `addDynamicEventListener` function to the global scope.
 *
 * The dynamic listener gets an extra `selector` parameter that only calls the callback
 * if the target element matches the selector.
 *
 * The listener has to be added to the container/root element and the selector must match
 * the elements that should trigger the event.
 *
 * Browser support: IE11+
 *
 * Source:
 * https://github.com/Cristy94/dynamic-listener/blob/master/dynamicListener.js
 */

/**
 * Returns a modified callback function that calls the
 * initial callback function only if the target element matches the given selector
 *
 * @param {string} selector
 * @param {function} callback
 */
function getConditionalCallback(selector, callback) {
    return function (e) {
        if (e.target && e.target.matches(selector)) {
            e.delegatedTarget = e.target;
            callback.apply(this, arguments);
            return;
        }
        // Not clicked directly, check bubble path
        const path = e.path || (e.composedPath && e.composedPath());
        if (!path) return;
        for (let i = 0; i < path.length; ++i) {
            const el = path[i];
            if (el.matches(selector)) {
                // Call callback for all elements on the path that match the selector
                e.delegatedTarget = el;
                callback.apply(this, arguments);
            }
            // We reached parent node, stop
            if (el === e.currentTarget) {
                return;
            }
        }
    };
}

/**
 *
 *
 * @param {Element} rootElement The root element to add the listener too.
 * @param {string} eventType The event type to listen for.
 * @param {string} selector The selector that should match the dynamic elements.
 * @param {function} callback The function to call when an event occurs on the given selector.
 * @param {boolean|object} options Passed as the regular `options` parameter to the addEventListener function
 *                                 Set to `true` to use capture.
 *                                 Usually used as an object to add the listener as `passive`
 */
export function addDynamicEventListener(rootElement, eventType, selector, callback, options = false) {
    rootElement.addEventListener(eventType, getConditionalCallback(selector, callback), options);
}
