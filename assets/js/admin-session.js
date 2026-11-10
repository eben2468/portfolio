/**
 * Nadics Digital Solution — Admin session timeout
 *
 * - Counts down to the server's idle timeout.
 * - Typing, clicking or scrolling in the page extends the session (throttled),
 *   so a long form being filled in isn't lost.
 * - Warns 60 seconds before sign-out, then sends the admin to the login page.
 * - Before signing out, asks the server, so activity in another tab counts.
 */
(function () {
    var cfg = window.ADMIN_SESSION;
    if (!cfg) return;

    var WARN_AT = 60;          // seconds before expiry to show the warning
    var PING_EVERY = 60;       // at most one keep-alive per minute

    var expiresAt = Date.now() + cfg.secondsLeft * 1000;
    var lastPing = Date.now();
    var pending = false;
    var finished = false;

    var box = document.getElementById('sessionWarning');
    var count = document.getElementById('sessionWarningCount');
    var stay = document.getElementById('sessionStay');

    function secondsLeft() {
        return Math.max(0, Math.round((expiresAt - Date.now()) / 1000));
    }

    function apply(data) {
        if (!data || !data.active) { return signOut(); }
        expiresAt = Date.now() + data.seconds_left * 1000;
        if (secondsLeft() > WARN_AT && box) box.hidden = true;
    }

    function request(method) {
        if (pending || finished) return;
        pending = true;
        fetch(cfg.endpoint, {
            method: method,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(apply)
            .catch(function () { /* network hiccup: try again on next tick */ })
            .then(function () { pending = false; });
    }

    function keepAlive() {
        lastPing = Date.now();
        request('POST');
    }

    function signOut() {
        if (finished) return;
        finished = true;
        window.location.href = cfg.loginUrl;
    }

    // Activity in the page extends the session (unless the warning is up,
    // where only the button counts, so a stray mouse move doesn't hide it).
    ['keydown', 'mousedown', 'scroll', 'touchstart', 'input'].forEach(function (evt) {
        document.addEventListener(evt, function () {
            if (box && !box.hidden) return;
            if (Date.now() - lastPing > PING_EVERY * 1000) keepAlive();
        }, { passive: true, capture: true });
    });

    if (stay) stay.addEventListener('click', keepAlive);

    setInterval(function () {
        if (finished) return;
        var left = secondsLeft();

        if (left <= 0) {
            // Check with the server first: another tab may have kept it alive.
            request('GET');
            return;
        }

        if (left <= WARN_AT && box) {
            if (box.hidden) {
                box.hidden = false;
                request('GET'); // refresh from server in case another tab was active
            }
            if (count) count.textContent = left;
        }
    }, 1000);
})();
