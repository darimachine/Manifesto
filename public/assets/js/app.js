/**
 * Manifesto — vanilla JS helpers (frontend only, no libraries).
 */

/* Confirm destructive forms: <form data-confirm="Delete this host?"> */
document.addEventListener('submit', function (e) {
    var msg = e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) {
        e.preventDefault();
    }
});

/* Dynamic child rows (ports / env vars / volumes on the Service form).
   Container: <div class="child-rows" id="X"> with a <template> inside.
   Add button: <button data-add-row="X"> · Remove: <button class="row-remove"> */
document.addEventListener('click', function (e) {
    var addTarget = e.target.getAttribute && e.target.getAttribute('data-add-row');
    if (addTarget) {
        e.preventDefault();
        var container = document.getElementById(addTarget);
        var template = container.querySelector('template');
        if (container && template) {
            container.appendChild(template.content.cloneNode(true));
        }
    }
    if (e.target.classList && e.target.classList.contains('row-remove')) {
        e.preventDefault();
        var row = e.target.closest('.child-row');
        if (row) { row.remove(); }
    }
});

/* Modal toggles: <button data-modal-open="id">, <button data-modal-close> */
document.addEventListener('click', function (e) {
    var openId = e.target.getAttribute && e.target.getAttribute('data-modal-open');
    if (openId) {
        e.preventDefault();
        var m = document.getElementById(openId);
        if (m) { m.classList.add('open'); }
    }
    if (e.target.hasAttribute && e.target.hasAttribute('data-modal-close')) {
        e.preventDefault();
        var backdrop = e.target.closest('.modal-backdrop');
        if (backdrop) { backdrop.classList.remove('open'); }
    }
});

/* Copy-to-clipboard: <button data-copy="elementId"> */
document.addEventListener('click', function (e) {
    var copyId = e.target.getAttribute && e.target.getAttribute('data-copy');
    if (copyId) {
        e.preventDefault();
        var el = document.getElementById(copyId);
        if (!el) { return; }
        var text = el.value !== undefined ? el.value : el.textContent;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            /* offline/older-browser fallback */
            if (el.select) { el.select(); document.execCommand('copy'); }
        }
        var original = e.target.textContent;
        e.target.textContent = 'Copied!';
        setTimeout(function () { e.target.textContent = original; }, 1200);
    }
});
