/**
 * Copyright (c) 2015, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2015 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

// Define the "Nosto" namespace if not already defined.
if (typeof Nosto === "undefined") {
    var Nosto = {};
}

/**
 * Nosto iframe API.
 *
 * @param {Object} options
 */
Nosto.iframe = function(options) {
    var TYPE_NEW_ACCOUNT = "newAccount",
        TYPE_CONNECT_ACCOUNT = "connectAccount",
        TYPE_SYNC_ACCOUNT = "syncAccount",
        TYPE_REMOVE_ACCOUNT = "removeAccount";

    /**
     * @type {Object}
     */
    var settings = {
        origin: "",
        iframeId: "nosto_iframe",
        urls: {
            createAccount: "",
            connectAccount: "",
            syncAccount: "",
            deleteAccount: ""
        },
        xhrParams: {}
    };

    /**
     * Window.postMessage() event handler for catching messages from nosto.
     *
     * Supported messages must come from nosto.com and be formatted according
     * to the following example:
     *
     * '[Nosto]{ "type": "the message action", "params": {} }'
     *
     * @param {Object} event
     */
    function receiveMessage(event)
    {
        // Check the origin to prevent cross-site scripting.
        var originRegexp = new RegExp(settings.origin);
        if (!originRegexp.test(event.origin)) {
            return;
        }
        // If the message does not start with "[Nosto]", then it is not for us.
        if ((""+event.data).substr(0, 7) !== "[Nosto]") {
            return;
        }

        var json = (""+event.data).substr(7);
        var data = JSON.parse(json);
        if (typeof data === "object" && data.type) {
            switch (data.type) {
                case TYPE_NEW_ACCOUNT:
                    xhr(settings.urls.createAccount, {
                        data: {email: data.params.email},
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.redirect_url) {
                                getIframeElement().src = response.redirect_url;
                            } else {
                                throw new Error("Nosto: failed to handle account creation.");
                            }
                        }
                    });
                    break;

                case TYPE_CONNECT_ACCOUNT:
                    xhr(settings.urls.connectAccount, {
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.redirect_url) {
                                if (response.success && response.success === true) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    getIframeElement().src = response.redirect_url;
                                }
                            } else {
                                throw new Error("Nosto: failed to handle account connection.");
                            }
                        }
                    });
                    break;

                case TYPE_SYNC_ACCOUNT:
                    xhr(settings.urls.syncAccount, {
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.redirect_url) {
                                if (response.success && response.success === true) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    getIframeElement().src = response.redirect_url;
                                }
                            } else {
                                throw new Error("Nosto: failed to handle account sync.");
                            }
                        }
                    });
                    break;

                case TYPE_REMOVE_ACCOUNT:
                    xhr(settings.urls.deleteAccount, {
                        success: function (e) {
                            /** @type {{success: Boolean}, {redirect_url: String}} response */
                            var response = JSON.parse(e.target.response);
                            if (response.redirect_url) {
                                getIframeElement().src = response.redirect_url;
                            } else {
                                throw new Error("Nosto: failed to handle account deletion.");
                            }
                        }
                    });
                    break;

                default:
                    throw new Error("Nosto: invalid postMessage `type`.");
            }
        }
    }

    /**
     * Creates a new XMLHttpRequest.
     *
     * Usage example:
     *
     * xhr("http://localhost/target.html", {
     *      "method": "POST",
     *      "data": {"key": "value"},
     *      "success": function (e) { // handle success request },
     *      "error": function (e) { // handle failure request }
     * });
     *
     * @param {String} url the url to call.
     * @param {Object} params optional params.
     */
    function xhr(url, params) {
        var options = extendObject({
            method: "POST",
            async: true,
            data: {}
        }, params);

        extendObject(options.data, settings.xhrParams);
        var payload = buildParams(options.data);

        var oReq = new XMLHttpRequest();
        if (typeof options.success === "function") {
            oReq.addEventListener("load", options.success, false);
        }
        if (typeof options.error === "function") {
            oReq.addEventListener("error", options.error, false);
        }
        oReq.open(options.method, decodeURIComponent(url), options.async);
        oReq.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        oReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        oReq.send(payload);
    }

    /**
     * Extends a literal object with data from the other object.
     *
     * @param {Object} obj1 the object to extend.
     * @param {Object} obj2 the object to extend from.
     * @returns {Object}
     */
    function extendObject(obj1, obj2) {
        for (var key in obj2) {
            if (obj2.hasOwnProperty(key)) {
                obj1[key] = obj2[key];
            }
        }
        return obj1;
    }

    /**
     * Builds a query string based on params.
     *
     * @param {Object} params the params to turn into a query string.
     * @returns {string} the built query string.
     */
    function buildParams(params) {
        var queryString = "";
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                if (queryString !== "") {
                    queryString += "&";
                }
                queryString += encodeURIComponent(key)+"="+encodeURIComponent(params[key]);
            }
        }
        return queryString;
    }

    /**
     * Returns the iframe html element associated with this iframe API.
     *
     * @returns {HTMLElement} the element.
     */
    function getIframeElement()
    {
        return document.getElementById(settings.iframeId);
    }

    // Configure the iframe API.
    extendObject(settings, options);

    // Register event handler for window.postMessage() messages from nosto.
    window.addEventListener("message", receiveMessage, false);
};