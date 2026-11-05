<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 */

/**
 * Search result page - Pustaka Ceria theme
 */
?>
<header class="c-header"><?php include '_navbar.php'; ?></header>
<?php include '_search-form.php'; ?>

<div class="kd-container">
    <div class="kd-results">
        <aside class="kd-side">
            <h4 style="margin-bottom:4px"><?= __('Filter by') ?></h4>
            <?= $engine->getFilter($opac, true) ?>
        </aside>

        <section>
            <div class="kd-summary">
                <div>
                    <?php
                    $keywords_info = '<span class="search-keyword-info" title="' . htmlentities($keywords) . '">' . ((strlen($keywords) > 30) ? substr($keywords, 0, 30) . '...' : $keywords) . '</span>';
                    $search_result_info = '<div class="search-found-info">';
                    $search_result_info .= __('Found <strong>{biblio_list->num_rows}</strong> from your keywords') . ': <strong class="search-found-info-keywords">' . $keywords_info . '</strong>';
                    $search_result_info .= '</div>';
                    echo str_replace('{biblio_list->num_rows}', $engine->getNumRows(), $search_result_info);
                    ?>
                </div>
                <div class="form-inline">
                    <label class="mr-2 font-weight-bold" for="search-order"><?= __('Sort by') ?></label>
                    <select id="search-order"><?= $sort_select ?></select>
                    <form class="ml-2" method="POST" action="<?= $_SERVER['PHP_SELF'] . '?' . http_build_query(array_filter($_GET, fn($key) => $key !== 'csrf_token', ARRAY_FILTER_USE_KEY)) ?>">
                        <input type="hidden" name="csrf_token" value="<?= $opac->getCsrf() ?>"/>
                        <?php if (($_SESSION['LIST_VIEW'] ?? 'list') === 'list'): ?>
                            <input type="hidden" name="view" value="grid"/>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= __('Grid view') ?>"><i class="fas fa-th-large"></i></button>
                        <?php else: ?>
                            <input type="hidden" name="view" value="list"/>
                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="<?= __('List view') ?>"><i class="fas fa-list"></i></button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="wrapper kd-grid-wrap">
                <?php
                if (ENVIRONMENT == 'development' && !empty($engine->getError())) echo '<div class="alert alert-danger mt-2 text-center">' . $engine->getError() . '</div>';
                if (trim(strip_tags($main_content)) === '') {
                  echo '<div class="kd-empty"><img src="' . assets('images/empty.svg') . '" /><p><strong>' . __('No Result') . '.</strong> ' . __('Please try again') . '</p></div>';
                } else {
                  echo $main_content;
                }
                ?>
            </div>
        </section>
    </div>
</div>

<?php if (($_SESSION['LIST_VIEW'] ?? 'list') === 'grid'): ?>
    <script>
        $(document).ready(function () {
            var loaded = 0, total = $(".grid-item .img-thumbnail").length;
            if (!total) { done(); return; }
            $(".grid-item .img-thumbnail").each(function (idx, img) {
                $("<img>").on("load error", function () { if (++loaded == total) done(); }).attr("src", $(img).attr("src"));
            });
            function done() {
                $('.biblioResult').addClass('row').masonry({itemSelector: '.grid-item', columnWidth: '.grid-item'});
                $('.dropdown-toggle').dropdown();
            }
        });
    </script>
<?php endif; ?>
