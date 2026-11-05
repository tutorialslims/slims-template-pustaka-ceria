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
  'publisher' => __('Publisher'),
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
    document.querySelectorAll('.kd-search-form').forEach(function (form) {
        var sel = form.querySelector('.kd-field'), inp = form.querySelector('.kd-keyword');
        sel.addEventListener('change', function () { inp.name = sel.value; inp.focus(); });
    });
</script>
