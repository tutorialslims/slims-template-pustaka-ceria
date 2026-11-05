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
 * Generic content page (info, news, help, librarian, detail) - Pustaka Ceria theme
 */
?>
<header class="c-header"><?php include '_navbar.php'; ?></header>
<?php include '_search-form.php'; ?>

<section class="kd-page">
    <div class="kd-container">
        <?php
        if ($_GET['p'] !== 'show_detail') {
          echo '<h2>' . $page_title . '</h2><hr>';
          if ($_GET['p'] === 'librarian') {
            echo '<div class="flex flex-row flex-wrap">' . $main_content . '</div>';
          } else {
            echo $main_content;
          }
        } else {
          echo $main_content;
        }
        ?>
    </div>
</section>
