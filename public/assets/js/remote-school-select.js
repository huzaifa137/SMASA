/*
 * Remote school picker.
 *
 * Turns <select data-remote-schools="URL"> into a type-ahead that asks the
 * server (admin.schools.lookup) for matches instead of rendering every school
 * as an <option>. The native <select> stays in the DOM and remains the source
 * of truth, so the form posts exactly as before.
 *
 *   <select name="school_id" data-remote-schools="{{ route('admin.schools.lookup') }}"
 *           data-placeholder="National (All Schools)">
 *       <option value="">National (All Schools)</option>
 *       <option value="12" selected>Already-chosen school</option>
 *   </select>
 */
(function () {
    'use strict';

    function enhance(select) {
        var url = select.getAttribute('data-remote-schools');
        if (!url || select.dataset.rssReady) return;
        select.dataset.rssReady = '1';

        var placeholder = select.getAttribute('data-placeholder') || 'Search school…';
        var wrap = document.createElement('div');
        wrap.style.position = 'relative';
        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        select.style.display = 'none';

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control';
        input.autocomplete = 'off';
        input.placeholder = placeholder;

        var list = document.createElement('div');
        list.className = 'list-group';
        list.style.cssText = 'position:absolute;left:0;right:0;top:100%;z-index:1050;max-height:260px;overflow-y:auto;display:none;box-shadow:0 8px 24px rgba(0,0,0,.15)';

        wrap.appendChild(input);
        wrap.appendChild(list);

        function currentLabel() {
            var o = select.selectedOptions[0];
            return o && o.value !== '' ? o.textContent.replace(/\s+/g, ' ').trim() : '';
        }
        input.value = currentLabel();

        function item(text, value, muted) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'list-group-item list-group-item-action' + (muted ? ' text-muted' : '');
            b.textContent = text;
            b.addEventListener('mousedown', function (e) {
                e.preventDefault();
                choose(value, text);
            });
            return b;
        }

        function choose(value, text) {
            select.innerHTML = '';
            var o = document.createElement('option');
            o.value = value;
            o.textContent = text;
            o.selected = true;
            select.appendChild(o);
            input.value = value === '' ? '' : text;
            list.style.display = 'none';
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        var seq = 0, timer = null;
        function load(term) {
            var mine = ++seq;
            fetch(url + '?q=' + encodeURIComponent(term.trim()), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
            .then(function (d) {
                if (mine !== seq) return;
                list.innerHTML = '';
                list.appendChild(item(placeholder, '', true));
                d.results.forEach(function (r) { list.appendChild(item(r.text, String(r.id))); });
                if (d.more) {
                    var more = document.createElement('div');
                    more.className = 'list-group-item small text-muted';
                    more.textContent = 'Showing the first ' + d.results.length + ' — type to narrow down';
                    list.appendChild(more);
                } else if (!d.results.length) {
                    var none = document.createElement('div');
                    none.className = 'list-group-item small text-muted';
                    none.textContent = 'No matches found';
                    list.appendChild(none);
                }
                list.style.display = 'block';
            })
            .catch(function () {});
        }

        input.addEventListener('focus', function () { input.select(); load(''); });
        input.addEventListener('input', function () {
            clearTimeout(timer);
            var v = input.value;
            timer = setTimeout(function () { load(v); }, 250);
        });
        input.addEventListener('blur', function () {
            list.style.display = 'none';
            input.value = currentLabel();     // typed text that was never chosen is discarded
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { list.style.display = 'none'; input.blur(); }
        });
    }

    function init() {
        document.querySelectorAll('select[data-remote-schools]').forEach(enhance);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
