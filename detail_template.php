<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
 */

/**
 * Detail template - Pustaka Ceria theme
 */
// the detail is rendered before index_template, so make sure extra translations are loaded
include_once __DIR__ . '/lang.php';

$setBookmarked = trim(isset($_SESSION['bookmark'][$biblio_id]) ? 'bg-success text-white' : '');

// back link: return to previous search when keywords are known
$kd_back = 'index.php';
if (!empty($_GET['keywords'])) {
  $kd_back = 'index.php?keywords=' . urlencode($_GET['keywords']) . '&search=search';
}

// similar documents: share at least one subject with this record
$kd_similar = [];
$kd_db = isset($this) && isset($this->db) ? $this->db : $GLOBALS['dbs'];
$kd_sq = $kd_db->query('SELECT b.biblio_id, b.title, b.publish_year,
        (SELECT GROUP_CONCAT(a.author_name SEPARATOR "; ") FROM biblio_author ba INNER JOIN mst_author a ON a.author_id=ba.author_id WHERE ba.biblio_id=b.biblio_id) AS authors
    FROM biblio b
    INNER JOIN biblio_topic bt ON bt.biblio_id=b.biblio_id
    WHERE b.biblio_id<>' . (int)$biblio_id . ' AND bt.topic_id IN (SELECT topic_id FROM biblio_topic WHERE biblio_id=' . (int)$biblio_id . ')
    GROUP BY b.biblio_id ORDER BY COUNT(*) DESC, b.biblio_id DESC LIMIT 5');
if ($kd_sq) {
  while ($r = $kd_sq->fetch_assoc()) $kd_similar[] = $r;
}

$kd_row = function ($label, $value, $class = '') {
  if ($value === null || trim(strip_tags((string)$value)) === '') return;
  echo '<dt>' . $label . '</dt><dd class="' . $class . '">' . $value . '</dd>';
};
?>

<div class="kd-container">
    <div class="kd-detail">
        <article class="kd-card">
            <a class="kd-back" href="<?= $kd_back ?>">&larr; <?= __('Back to search results'); ?></a>
            <div class="kd-tags" style="margin-bottom:6px">
                <?php if (!empty($gmd_name)): ?><span class="kd-tag"><?= $gmd_name ?></span><?php endif; ?>
                <?php if (!empty($publish_year)): ?><span class="kd-tag plain"><?= $publish_year ?></span><?php endif; ?>
            </div>

            <div class="kd-cover"><?= $image; ?></div>

            <h1><?= stripslashes($title); ?></h1>
            <div class="kd-byline"><?= str_replace('<br />', '; ', $authors); ?></div>

            <div class="kd-detail-actions">
                <a href="#" data-id="<?= $biblio_id ?>" data-detail="true" class="bookMarkBook <?= $setBookmarked ?>">
                    <i class="fas fa-bookmark"></i> <label id="label-<?= $biblio_id ?>" style="margin:0;cursor:pointer"><?= in_array($biblio_id, $_SESSION['bookmark'] ?? []) ? __('Bookmarked') : __('Bookmark') ?></label>
                </a>
                <a href="javascript:void(0)" data-toggle="modal" data-id="<?= $biblio_id ?>" data-title="<?= $title ?>" data-target="#mediaSocialModal"><i class="fas fa-share-alt"></i> <?= __('Share') ?></a>
                <a href="index.php?p=show_detail&id=<?= $biblio_id ?>&MARC=true" target="_blank">MARC</a>
            </div>

            <div style="clear:both"></div>
            <div class="kd-label-h"><?= __('Detail'); ?></div>
            <dl class="kd-dl">
                <?php
                $kd_row(__('Author'), str_replace('<br />', '<br>', $authors));
                $kd_row(__('Year'), $publish_year);
                $kd_row(__('Type'), $gmd_name);
                $kd_row(__('Series Title'), $series_title);
                $kd_row(__('Edition'), $edition);
                $kd_row(__('Publisher'), trim(($publish_place ? $publish_place . ' : ' : '') . $publisher_name));
                $kd_row(__('Collation'), $collation);
                $kd_row(__('Language'), $language_name);
                $kd_row(__('Subject(s)'), $subjects ? '<div class="s-subject">' . $subjects . '</div>' : '');
                $kd_row(__('Abstract / Description'), $notes, 'abs');
                $kd_row(__('Call Number'), $call_number);
                $kd_row(__('Classification'), $classification);
                $kd_row(__('Content Type'), $content_type);
                $kd_row(__('Media Type'), $media_type);
                $kd_row(__('Carrier Type'), $carrier_type);
                $kd_row(__('Specific Detail Info'), $spec_detail_info);
                $kd_row(__('Statement of Responsibility'), $sor);
                $kd_row(__('ISBN/ISSN'), $isbn_issn ? '<span class="kd-mono">' . $isbn_issn . '</span>' : '');
                if (!empty($biblio_custom)) {
                  foreach ($biblio_custom as $item) $kd_row($item['label'], $item['value']);
                }
                ?>
            </dl>

            <div class="kd-sub-h"><?= __('Availability'); ?></div>
            <?= ($availability) ? $availability : '<p class="text-muted">' . __('No copy data') . '</p>'; ?>

            <?php if (!empty($related)): ?>
                <div class="kd-sub-h"><?= __('Other version/related'); ?></div>
                <div><?= $related ?></div>
            <?php endif; ?>

            <div class="kd-sub-h" id="attachment"><?= __('File Attachment'); ?></div>
            <div itemprop="associatedMedia"><?= !$file_att ? '<i>' . __('No Data') . '</i>' : $file_att; ?></div>

            <div class="kd-sub-h" id="comment"><?= __('Comments'); ?></div>
            <?php echo showComment($biblio_id); ?>
            <?php if (!isset($_SESSION['mid']) && $sysconf['comment']['enable']) : ?>
                <hr>
                <a href="index.php?p=member" class="btn btn-outline-primary"><?= __('You must be logged in to post a comment'); ?></a>
            <?php endif; ?>
        </article>

        <aside>
            <?php if ($kd_similar): ?>
                <div class="kd-card plain kd-related">
                    <div class="kd-label-h" style="margin-top:0;border:0"><?= __('Similar Documents'); ?></div>
                    <?php foreach ($kd_similar as $s): ?>
                        <div class="it">
                            <a class="t" href="index.php?p=show_detail&id=<?= $s['biblio_id'] ?>"><?= htmlspecialchars($s['title']) ?></a>
                            <small><?= htmlspecialchars(trim(($s['authors'] ?? '') . ($s['publish_year'] ? ' · ' . $s['publish_year'] : ''), ' ·')) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
