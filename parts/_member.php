<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 */

/**
 * Member area - Pustaka Ceria theme
 */
?>
<header class="c-header"><?php include '_navbar.php'; ?></header>

<?php if ($is_login) : ?>
    <div class="member-area kd-member-wrap">
        <?php echo $main_content; ?>
    </div>
<?php else: ?>
    <?php include '_search-form.php'; ?>
    <div class="kd-container kd-member-wrap page-member-area">
        <div class="row">
            <div class="col-md-8">
                <?php echo $main_content; ?>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>
<?php endif; ?>
