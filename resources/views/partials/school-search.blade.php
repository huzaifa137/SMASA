{{--
    School search-as-you-type field.
    Posts the chosen school's id as `school_id` (same value the old <select> sent).
    The school list is never rendered into the page; it is fetched from
    route('auth.schools.search') only after the visitor types 2+ characters.

    JS API (window.SchoolSearch): .value(), .reset(), .setError(msg)
    Optional: pass $inputLabel to override the label text.
--}}
<style>
    .ss-wrap { position: relative; }
    .ss-field { position: relative; display: flex; align-items: center; }
    .ss-field .ss-icon {
        position: absolute; left: 1.2rem; color: var(--gray-500, #71717a);
        font-size: 1.05rem; pointer-events: none; z-index: 2;
    }
    .ss-input {
        width: 100%; padding: 1rem 2.8rem 1rem 3rem;
        border: 1.5px solid var(--gray-300, #d4d4d8); border-radius: 40px;
        font-size: 0.95rem; background: #fff; transition: all .2s ease;
    }
    .ss-input:focus {
        outline: none; border-color: var(--orange, #2C29CA);
        box-shadow: 0 0 0 4px rgba(44, 41, 202, 0.08);
    }
    .ss-input.has-logo { padding-left: 3.4rem; }
    .ss-selected-logo {
        position: absolute; left: .8rem; width: 28px; height: 28px; border-radius: 50%;
        object-fit: contain; background: #fff; border: 1px solid var(--gray-300, #d4d4d8);
        z-index: 3; display: none;
    }
    .ss-clear {
        position: absolute; right: 1rem; background: none; border: none; cursor: pointer;
        color: var(--gray-500, #71717a); font-size: 1rem; display: none; padding: 4px; z-index: 3;
    }
    .ss-list {
        position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 10000;
        background: #fff; border: 1.5px solid var(--orange, #2C29CA); border-radius: 12px;
        box-shadow: 0 20px 40px -12px rgba(44, 41, 202, 0.18); overflow: hidden;
        max-height: 320px; overflow-y: auto; display: none;
    }
    .ss-list.open { display: block; }
    .ss-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 14px; cursor: pointer;
        font-size: 0.92rem; color: var(--gray-900, #18181b);
    }
    .ss-item img {
        width: 36px; height: 36px; border-radius: 50%; object-fit: contain; flex: none;
        background: #fff; border: 1px solid var(--gray-300, #d4d4d8);
    }
    .ss-item.active, .ss-item:hover { background: var(--orange, #2C29CA); color: #fff; }
    .ss-item mark { background: transparent; color: inherit; font-weight: 800; text-decoration: underline; }
    .ss-msg { padding: 14px; font-size: 0.85rem; color: var(--gray-500, #71717a); text-align: center; }
</style>

<div class="ss-wrap" id="ssWrap">
    <div class="ss-field">
        <i class="fas fa-school ss-icon" id="ssIcon"></i>
        <img class="ss-selected-logo" id="ssSelectedLogo" alt="">
        <input type="text" id="school_search" class="ss-input" autocomplete="off"
            placeholder="Type your school name to search..." role="combobox"
            aria-expanded="false" aria-controls="ssList" aria-autocomplete="list">
        <button type="button" class="ss-clear" id="ssClear" aria-label="Clear school">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <input type="hidden" name="school_id" id="school_id" value="">
    <div class="ss-list" id="ssList" role="listbox"></div>
</div>

<script>
    (function () {
        const url = @json(route('auth.schools.search'));
        const input = document.getElementById('school_search');
        const hidden = document.getElementById('school_id');
        const list = document.getElementById('ssList');
        const clearBtn = document.getElementById('ssClear');
        const selLogo = document.getElementById('ssSelectedLogo');
        const icon = document.getElementById('ssIcon');
        const MIN = 2;
        let items = [], active = -1, timer = null, ctrl = null, placeholder = '';

        const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        const escRe = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        function open(html) { list.innerHTML = html; list.classList.add('open'); input.setAttribute('aria-expanded', 'true'); }
        function close() { list.classList.remove('open'); input.setAttribute('aria-expanded', 'false'); active = -1; }

        function setError(msg) {
            const el = document.getElementById('school_id-error');
            if (el) el.textContent = msg || '';
        }

        function select(item) {
            hidden.value = item.id;
            input.value = item.name;
            selLogo.src = item.logo;
            selLogo.onerror = function () { this.onerror = null; this.src = placeholder; };
            selLogo.style.display = 'block';
            icon.style.display = 'none';
            input.classList.add('has-logo');
            clearBtn.style.display = 'block';
            setError('');
            close();
        }

        function reset() {
            hidden.value = '';
            input.value = '';
            selLogo.style.display = 'none';
            icon.style.display = '';
            input.classList.remove('has-logo');
            clearBtn.style.display = 'none';
            items = [];
            close();
        }

        function render(q) {
            if (!items.length) { open('<div class="ss-msg">No school found for “' + esc(q) + '”</div>'); return; }
            const re = new RegExp('(' + escRe(q) + ')', 'ig');
            open(items.map((it, i) =>
                '<div class="ss-item" role="option" data-i="' + i + '">' +
                '<img src="' + esc(it.logo) + '" alt="" onerror="this.onerror=null;this.src=\'' + esc(placeholder) + '\'">' +
                '<span>' + esc(it.name).replace(re, '<mark>$1</mark>') + '</span></div>'
            ).join(''));
            active = -1;
        }

        function highlight() {
            list.querySelectorAll('.ss-item').forEach((el, i) => el.classList.toggle('active', i === active));
            const el = list.querySelector('.ss-item.active');
            if (el) el.scrollIntoView({ block: 'nearest' });
        }

        function search(q) {
            if (ctrl) ctrl.abort();
            ctrl = new AbortController();
            open('<div class="ss-msg"><i class="fas fa-spinner fa-spin"></i> Searching...</div>');
            fetch(url + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' }, signal: ctrl.signal
            })
                .then(r => r.json())
                .then(res => { placeholder = res.placeholder || placeholder; items = res.data || []; render(q); })
                .catch(e => { if (e.name !== 'AbortError') open('<div class="ss-msg">Could not load schools. Try again.</div>'); });
        }

        input.addEventListener('input', function () {
            // Typing after a selection invalidates it.
            if (hidden.value) {
                hidden.value = '';
                selLogo.style.display = 'none';
                icon.style.display = '';
                input.classList.remove('has-logo');
            }
            clearBtn.style.display = input.value ? 'block' : 'none';
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < MIN) { if (ctrl) ctrl.abort(); close(); return; }
            timer = setTimeout(() => search(q), 250);
        });

        input.addEventListener('keydown', function (e) {
            if (!list.classList.contains('open') || !items.length) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % items.length; highlight(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + items.length) % items.length; highlight(); }
            else if (e.key === 'Enter') {
                if (active >= 0) { e.preventDefault(); select(items[active]); }
                else if (items.length === 1) { e.preventDefault(); select(items[0]); }
            }
            else if (e.key === 'Escape') close();
        });

        list.addEventListener('mousedown', function (e) {
            const el = e.target.closest('.ss-item');
            if (el) { e.preventDefault(); select(items[+el.dataset.i]); }
        });

        clearBtn.addEventListener('click', function () { reset(); input.focus(); });
        document.addEventListener('click', e => { if (!document.getElementById('ssWrap').contains(e.target)) close(); });

        window.SchoolSearch = { value: () => hidden.value, reset: reset, setError: setError };
    })();
</script>
