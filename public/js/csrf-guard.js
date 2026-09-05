"use strict";

(function () {
    function getToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute("content") : "";
    }

    function setToken(token) {
        if (!token) {
            return;
        }
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.setAttribute("content", token);
        }
        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });
        if (window.jQuery) {
            jQuery.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": token
                }
            });
        }
    }

    function applyAjaxSetup() {
        if (!window.jQuery) {
            return;
        }
        jQuery.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": getToken()
            }
        });
    }

    function refreshToken(callback) {
        var done = typeof callback === "function" ? callback : function () {};
        var tokenUrlMeta = document.querySelector('meta[name="csrf-token-url"]');
        var tokenUrl = tokenUrlMeta ? tokenUrlMeta.getAttribute("content") : "csrf-token";
        fetch(tokenUrl, {
            method: "GET",
            credentials: "same-origin",
            headers: {
                "Accept": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data && data.csrf_token) {
                    setToken(data.csrf_token);
                }
                done(true);
            })
            .catch(function () {
                done(false);
            });
    }

    function hidePageLoader() {
        document.querySelectorAll(".loader-bg").forEach(function (el) {
            el.remove();
        });
    }

    document.addEventListener("DOMContentLoaded", hidePageLoader);
    window.addEventListener("load", hidePageLoader);
    setTimeout(hidePageLoader, 1200);

    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            refreshToken();
        }
    });

    if (window.jQuery) {
        applyAjaxSetup();

        jQuery(document).ajaxSend(function (event, jqxhr) {
            var token = getToken();
            if (token) {
                jqxhr.setRequestHeader("X-CSRF-TOKEN", token);
            }
        });

        jQuery(document).ajaxError(function (event, jqxhr, settings) {
            if (jqxhr.status !== 419 || settings._csrfRetry) {
                return;
            }
            settings._csrfRetry = true;
            refreshToken(function (ok) {
                if (!ok) {
                    window.location.reload();
                    return;
                }
                jQuery.ajax(settings);
            });
        });
    }

    document.addEventListener("submit", function () {
        var token = getToken();
        if (!token) {
            return;
        }
        document.querySelectorAll('input[name="_token"]').forEach(function (input) {
            input.value = token;
        });
    }, true);

    setInterval(refreshToken, 10 * 60 * 1000);

    window.HrmCsrf = {
        getToken: getToken,
        setToken: setToken,
        refreshToken: refreshToken,
        hidePageLoader: hidePageLoader
    };
})();
