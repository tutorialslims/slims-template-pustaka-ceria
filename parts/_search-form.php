<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 * Template ini merupakan ide dari Hendro Wicaksono | Email : hendro.wicaksono@gmail.com
 */

/**
 * Search form - Pustaka Ceria theme
 * Set $kd_home = true before including to render the big hero variant (no wrapper).
 */
$kd_fields = [
  'keywords' => __('All fields'),
  'title' => __('Title'),
  'author' => __('Author'),
  'subject' => __('Subject'),
  'isbn' => __('ISBN/ISSN'),
  'callnumber' => __('Call Number'),
  'publisher' => __('Publisher'),
];
// chips shown in the suggestion popup: field => [label, icon]
$kd_chips = [
  'keywords' => [__('All (ALL)'), 'fa-th-large'],
  'title' => [__('Book Title'), 'fa-book'],
  'author' => [__('Author'), 'fa-user'],
  'subject' => [__('Topic / Subject'), 'fa-tag'],
  'isbn' => [__('ISBN/ISSN'), 'fa-barcode'],
  'callnumber' => [__('Shelf Call Number'), 'fa-list'],
];
$kd_current_field = 'keywords';
$kd_current_value = '';
foreach (array_keys($kd_fields) as $f) {
  if (isset($_GET[$f]) && is_string($_GET[$f]) && $_GET[$f] !== '') {
    $kd_current_field = $f;
    $kd_current_value = trim(strip_tags($_GET[$f]));
    break;
  }
}
$kd_form = '
<form class="kd-search-form" action="index.php" method="get">
    <input type="hidden" name="search" value="search">
    <div class="kd-sgwrap">
    <div class="kd-searchbox">
        <select class="kd-field" aria-label="' . __('Search by :') . '">';
foreach ($kd_fields as $k => $label) {
  $kd_form .= '<option value="' . $k . '"' . ($k === $kd_current_field ? ' selected' : '') . '>' . $label . '</option>';
}
$kd_form .= '</select>
        <input type="text" class="kd-keyword" name="' . $kd_current_field . '" value="' . htmlspecialchars($kd_current_value, ENT_QUOTES) . '" autocomplete="off"
               placeholder="' . __('Enter keyword to search collection...') . '">
        <button type="submit"><i class="fas fa-search"></i> ' . __('Search') . '</button>
    </div>
    <div class="kd-sg" hidden>
        <div class="kd-sg-head">
            <span class="kd-sg-title"><i class="fas fa-crosshairs"></i> ' . __('Search by column:') . '</span>
            <button type="button" class="kd-sg-close" aria-label="' . __('Close') . '"><i class="fas fa-times-circle"></i></button>
        </div>
        <div class="kd-sg-chips">';
foreach ($kd_chips as $k => $c) {
  $kd_form .= '<button type="button" class="kd-sg-chip' . ($k === $kd_current_field ? ' on' : '') . '" data-field="' . $k . '"><i class="fas ' . $c[1] . '"></i> ' . $c[0] . '</button>';
}
$kd_form .= '<button type="button" class="kd-sg-chip adv" data-toggle="modal" data-target="#adv-modal"><i class="fas fa-magic"></i> ' . __('Advanced Search') . '</button>
        </div>
        <div class="kd-sg-sec kd-sg-matches" hidden>
            <div class="kd-sg-label"><i class="fas fa-bolt"></i> ' . __('Matching collections in') . ' <code>search_biblio</code>:</div>
            <div class="kd-sg-list"></div>
        </div>
        <div class="kd-sg-sec kd-sg-recent" hidden>
            <div class="kd-sg-label"><i class="fas fa-history"></i> ' . __('Your recent searches:') . ' <button type="button" class="kd-sg-clear">' . __('Clear History') . '</button></div>
            <div class="kd-sg-hist"></div>
        </div>
    </div>
    </div>
</form>';
?>
<?php if (!empty($kd_home)): ?>
    <?= $kd_form ?>
    <div class="kd-links">
        <button type="button" class="kd-adv-toggle" data-toggle="modal" data-target="#adv-modal">
            <i class="fas fa-sliders-h"></i> <?= __('Advanced Search'); ?> <small>&#9662;</small>
        </button>
        <a href="index.php?keywords=&search=search"><?= sprintf(__('%s documents from all sources'), number_format((int)($kd_total ?? 0))); ?> &rarr;</a>
    </div>
<?php else: ?>
    <div class="kd-band">
        <div class="kd-container">
            <?= $kd_form ?>
            <button type="button" class="kd-adv-toggle" data-toggle="modal" data-target="#adv-modal">
                <i class="fas fa-sliders-h"></i> <?= __('Advanced Search'); ?> <small>&#9662;</small>
            </button>
        </div>
    </div>
<?php endif; ?>
<script>
(function () {
    var ENDPOINT = <?= json_encode(CURRENT_TEMPLATE_DIR . 'suggest.php') ?>;
    var KEY = 'kd_recent_searches', MAX = 6;

    function readHist() { try { return JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch (e) { return []; } }
    function writeHist(a) { try { localStorage.setItem(KEY, JSON.stringify(a.slice(0, MAX))); } catch (e) {} }
    function pushHist(v) {
        v = (v || '').trim(); if (!v) return;
        var a = readHist().filter(function (x) { return x.toLowerCase() !== v.toLowerCase(); });
        a.unshift(v); writeHist(a);
    }
    function el(tag, cls, text) { var n = document.createElement(tag); if (cls) n.className = cls; if (text != null) n.textContent = text; return n; }

    document.querySelectorAll('.kd-search-form').forEach(function (form) {
        var sel = form.querySelector('.kd-field'), inp = form.querySelector('.kd-keyword');
        var box = form.querySelector('.kd-sg'), wrap = form.querySelector('.kd-sgwrap');
        var chips = form.querySelectorAll('.kd-sg-chip[data-field]');
        var secM = form.querySelector('.kd-sg-matches'), list = form.querySelector('.kd-sg-list');
        var secR = form.querySelector('.kd-sg-recent'), hist = form.querySelector('.kd-sg-hist');
        var timer = null, seq = 0;

        function setField(f) {
            sel.value = f; inp.name = f;
            chips.forEach(function (c) { c.classList.toggle('on', c.getAttribute('data-field') === f); });
        }
        function open() { box.hidden = false; renderHist(); }
        function close() { box.hidden = true; }

        function renderHist() {
            var a = readHist(); hist.innerHTML = '';
            secR.hidden = a.length === 0;
            a.forEach(function (t) {
                var b = el('button', 'kd-sg-h'); b.type = 'button';
                b.appendChild(el('i', 'far fa-clock')); b.appendChild(document.createTextNode(' ' + t));
                b.addEventListener('click', function () { inp.value = t; form.submit(); });
                hist.appendChild(b);
            });
        }
        function renderMatches(rows) {
            list.innerHTML = '';
            secM.hidden = rows.length === 0;
            rows.forEach(function (r) {
                var a = el('a', 'kd-sg-item'); a.href = 'index.php?p=show_detail&id=' + r.id;
                var left = el('div', 'kd-sg-main');
                left.appendChild(el('strong', '', r.title));
                var meta = el('small');
                meta.appendChild(el('i', 'fas fa-user'));
                meta.appendChild(document.createTextNode(' ' + (r.authors || '-') + (r.year ? ' • ' + r.year : '')));
                left.appendChild(meta);
                a.appendChild(left);
                if (r.call) a.appendChild(el('code', 'kd-sg-call', r.call));
                list.appendChild(a);
            });
        }
        function fetchMatches() {
            var q = inp.value.trim();
            if (q.length < 2) { renderMatches([]); return; }
            var my = ++seq;
            fetch(ENDPOINT + '?q=' + encodeURIComponent(q) + '&field=' + encodeURIComponent(sel.value), { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (rows) { if (my === seq) renderMatches(Array.isArray(rows) ? rows : []); })
                .catch(function () { if (my === seq) renderMatches([]); });
        }

        sel.addEventListener('change', function () { setField(sel.value); inp.focus(); fetchMatches(); });
        chips.forEach(function (c) {
            c.addEventListener('click', function () { setField(c.getAttribute('data-field')); inp.focus(); fetchMatches(); });
        });
        inp.addEventListener('focus', open);
        inp.addEventListener('click', open);
        inp.addEventListener('input', function () { open(); clearTimeout(timer); timer = setTimeout(fetchMatches, 220); });
        form.querySelector('.kd-sg-close').addEventListener('click', close);
        form.querySelector('.kd-sg-clear').addEventListener('click', function () { writeHist([]); renderHist(); });
        form.querySelector('.kd-sg-chip.adv').addEventListener('click', close);
        form.addEventListener('submit', function () { pushHist(inp.value); });
        document.addEventListener('mousedown', function (e) { if (!wrap.contains(e.target)) close(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    });
})();
</script>
