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
 * Footer - Pustaka Ceria theme
 */
?>

<footer class="kd-footer">
    <div class="kd-container">
        <div class="kd-brand-f">
            <?php
            if (isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && $imagesDisk->isExists($path = 'default/' . $sysconf['logo_image'])) {
              echo '<img src="' . SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=350" alt="logo">';
            } elseif (file_exists(__DIR__ . '/../assets/images/logo.png')) {
              echo '<img src="' . assets('images/logo.png') . '" alt="logo">';
            }
            ?>
            <div>
                <strong><?= htmlspecialchars($sysconf['library_name']); ?></strong>
                <small><?= htmlspecialchars($sysconf['library_subname'] ?? ''); ?></small>
            </div>
        </div>
        <div class="kd-right">
            &copy; <?= date('Y'); ?> <?= htmlspecialchars($sysconf['library_name']); ?><br>
            <?= __('Powered by '); ?><a target="_blank" href="https://slims.web.id">SLiMS</a>
            &middot; <?= __('Template by'); ?> <strong>Tutorial SLiMS</strong>
        </div>
    </div>
    <div class="kd-container kd-contact">
        <p><?= __('Whatever your SLiMS error problem is, we are ready to help, please DM us on Instagram.'); ?></p>
        <p>
            <a href="mailto:tutorialslims@gmail.com"><i class="fas fa-envelope"></i> tutorialslims@gmail.com</a>
            <a target="_blank" rel="noopener" href="https://instagram.com/tutorial_slims"><i class="fab fa-instagram"></i> @tutorial_slims</a>
        </p>
    </div>
</footer>

<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <div id="show-pchat2" style="position: fixed; bottom: 16px; right: 16px" class="shadow rounded">
        <button title="Chat" class="btn btn-primary"><i class="fas fa-comments mr-2"></i><?= __('Chat'); ?></button>
    </div>
<?php endif; ?>

<?php
// Chat Engine
include LIB . "contents/chat.php"; ?>

<!-- // Load modal -->
<?php include "_modal_topic.php"; ?>
<?php include "_modal_advanced.php"; ?>
<?php include "_modal_social_media.php"; ?>

<!-- // Load highlight -->
<script src="<?= JWB; ?>highlight.js"></script>
<?php if (isset($engine) && $searchableInJsArray = $this->generateKeywords($engine->searchable_fields)) : ?>
    <script>
        $('.kd-item *').highlight(<?= $searchableInJsArray ?>);
    </script>
<?php endif; ?>

<!-- // load our vue app.js -->
<script src="<?php echo assets(v('js/app.js')); ?>"></script>
<script src="<?php echo assets(v('js/app_jquery.js')); ?>"></script>
<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <script>
        $('#show-pchat').click(() => {
            $('.s-chat').hide()
            $('#show-pchat2').show()
        })
        $('#show-pchat2').click(() => {
            $('.s-chat').show(300, () => {
                $('#show-pchat2').hide()
            })
        })
    </script>
<?php endif; ?>
</body>
</html>
