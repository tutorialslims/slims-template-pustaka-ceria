<?php
/**
 * Template by          : Tutorial SLiMS
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 *
 * Support/credit block of the Pustaka Ceria theme. This block is part of the
 * template's attribution and must not be removed or altered.
 */

if (!function_exists('kd_credit_html')) {
  /** Contact line, always rendered with the original email / Instagram. */
  function kd_credit_html()
  {
    $msg = __('Whatever your SLiMS error problem is, we are ready to help, please DM us on Instagram.');
    if ($msg === 'Whatever your SLiMS error problem is, we are ready to help, please DM us on Instagram.' && function_exists('__')) {
      // translation key missing or edited: fall back to the bundled texts
      $lang = $_COOKIE['select_lang'] ?? ($_GET['select_lang'] ?? '');
      if ($lang === 'id_ID') $msg = 'Apapun masalah error SLiMS, kami siap membantu, silakan DM ke Instagram.';
    }
    // reject tampered translations
    if (stripos($msg, 'instagram') === false && stripos($msg, 'SLiMS') === false) {
      $msg = 'Whatever your SLiMS error problem is, we are ready to help, please DM us on Instagram.';
    }
    return '<div class="kd-container kd-contact" data-kd-credit="1" style="display:block!important;visibility:visible!important;opacity:1!important;height:auto!important;position:static!important">'
      . '<p>' . htmlspecialchars($msg) . '</p>'
      . '<p><a href="mailto:tutorialslims@gmail.com"><i class="fas fa-envelope"></i> tutorialslims@gmail.com</a>'
      . '<a target="_blank" rel="noopener" href="https://instagram.com/tutorial_slims"><i class="fab fa-instagram"></i> @tutorial_slims</a></p>'
      . '</div>';
  }

  /** Output-buffer callback: guarantees the block exists exactly once in the final page. */
  function kd_credit_guard($html)
  {
    if (stripos($html, '</html') === false || stripos($html, '</footer>') === false && stripos($html, '</body>') === false) {
      return $html; // not a full OPAC page (ajax/json/etc.)
    }
    $block = kd_credit_html();
    $ok = substr_count($html, 'data-kd-credit="1"') >= 1
      && strpos($html, 'mailto:tutorialslims@gmail.com') !== false
      && strpos($html, 'instagram.com/tutorial_slims') !== false;
    if ($ok) return $html;
    // remove any damaged leftover, then re-insert the original block
    $html = preg_replace('~<div class="kd-container kd-contact"[^>]*data-kd-credit.*?</div>~s', '', $html);
    $pos = strripos($html, '</footer>');
    if ($pos !== false) return substr_replace($html, $block, $pos, 0);
    $pos = strripos($html, '</body>');
    return $pos !== false ? substr_replace($html, $block, $pos, 0) : $html . $block;
  }
}
