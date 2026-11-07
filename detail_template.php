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

// availability summary: total copies minus copies currently on loan (from database)
$kd_total = (int)$kd_db->query('SELECT COUNT(*) FROM item WHERE biblio_id=' . (int)$biblio_id)->fetch_row()[0];
$kd_lent = (int)$kd_db->query('SELECT COUNT(*) FROM loan AS l INNER JOIN item AS i ON l.item_code=i.item_code WHERE l.is_lent=1 AND l.is_return=0 AND i.biblio_id=' . (int)$biblio_id)->fetch_row()[0];
$kd_avail = max(0, $kd_total - $kd_lent);

// copies (items) of this record, straight from the database
$kd_items = [];
$kd_iq = $kd_db->query('SELECT i.item_code, i.call_number, l.location_name, ct.coll_type_name, st.item_status_name, st.no_loan,
        (SELECT COUNT(*) FROM loan lo WHERE lo.item_code=i.item_code AND lo.is_lent=1 AND lo.is_return=0) AS on_loan
    FROM item i
    LEFT JOIN mst_location l ON l.location_id=i.location_id
    LEFT JOIN mst_coll_type ct ON ct.coll_type_id=i.coll_type_id
    LEFT JOIN mst_item_status st ON st.item_status_id=i.item_status_id
    WHERE i.biblio_id=' . (int)$biblio_id . ' ORDER BY i.item_code');
if ($kd_iq) {
  while ($r = $kd_iq->fetch_assoc()) {
    if ((int)$r['on_loan'] > 0) { $r['_cls'] = 'loan'; $r['_label'] = __('On Loan'); }
    elseif (!empty($r['item_status_name'])) { $r['_cls'] = 'no'; $r['_label'] = $r['item_status_name']; }
    else { $r['_cls'] = 'ok'; $r['_label'] = __('Available'); }
    $kd_items[] = $r;
  }
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

            <div class="kd-cover">
                <?= $image; ?>
                <div class="kd-avail-box <?= $kd_avail > 0 ? 'ok' : 'no' ?>">
                    <span><?= __('Availability') ?></span>
                    <strong><?= $kd_avail === 1 ? __('1 Item') : sprintf(__('%d Items'), $kd_avail) ?></strong>
                </div>
                <a class="kd-side-btn openPopUp citationLink" href="index.php?p=cite&id=<?= (int)$biblio_id ?>" title="<?= str_replace('{title}', htmlspecialchars(substr(strip_tags($title), 0, 50)), __('Citation for: {title}')) ?>" target="_blank"><?= __('Cite This Collection') ?></a>
                <a class="kd-side-btn" href="index.php?p=show_detail&id=<?= (int)$biblio_id ?>&MARC=true" target="_blank"><?= __('Download MARC') ?></a>
                <a class="kd-side-btn" href="index.php?p=show_detail&id=<?= (int)$biblio_id ?>&JSONLD=true" target="_blank"><?= __('View JSON Record') ?></a>
            </div>

            <h1><?= stripslashes($title); ?></h1>
            <div class="kd-byline"><?= str_replace('<br />', '; ', $authors); ?></div>

            <div class="kd-detail-actions">
                <a href="#" data-id="<?= $biblio_id ?>" data-detail="true" class="bookMarkBook <?= $setBookmarked ?>">
                    <i class="fas fa-bookmark"></i> <label id="label-<?= $biblio_id ?>" style="margin:0;cursor:pointer"><?= in_array($biblio_id, $_SESSION['bookmark'] ?? []) ? __('Bookmarked') : __('Bookmark') ?></label>
                </a>
                <a href="javascript:void(0)" data-toggle="modal" data-id="<?= $biblio_id ?>" data-title="<?= $title ?>" data-target="#mediaSocialModal"><i class="fas fa-share-alt"></i> <?= __('Share') ?></a>
                <a href="index.php?p=show_detail&id=<?= $biblio_id ?>&MARC=true" target="_blank">MARC</a>
            </div>

            <div class="kd-avail-left">
                <div class="kd-note-box"><?= trim(strip_tags((string)$notes)) !== '' ? $notes : __('No additional notes / abstract for this collection.') ?></div>
                <div class="kd-sub-h"><?= __('Availability'); ?></div>
                <?php if ($kd_items): ?>
                <div class="kd-items-wrap">
                    <table class="kd-items">
                        <thead><tr>
                            <th><?= __('Barcode / Item Code') ?></th><th><?= __('Call No.') ?></th><th><?= __('Location & Collection') ?></th><th><?= __('Status') ?></th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($kd_items as $it): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($it['item_code']) ?></code></td>
                                <td><?= htmlspecialchars($it['call_number'] ?: ($call_number ?? '')) ?></td>
                                <td><?= htmlspecialchars($it['location_name'] ?: '-') ?><?= $it['coll_type_name'] ? ' (' . htmlspecialchars($it['coll_type_name']) . ')' : '' ?></td>
                                <td><span class="kd-st <?= $it['_cls'] ?>"><?= htmlspecialchars($it['_label']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted"><?= __('No copy data') ?></p>
                <?php endif; ?>
            </div>
            <div style="clear:both"></div>

            <div class="kd-label-h"><?= __('Detail'); ?></div>
            <?php
            // every field is always shown; empty values become "-"
            $kd_v = function ($v) {
              $t = trim(strip_tags((string)$v));
              return $t === '' ? '-' : htmlspecialchars($t);
            };
            $kd_place_year = trim(trim((string)$publish_place) . ((string)$publish_year !== '' ? ', ' . $publish_year : ''), ', ');
            $kd_loc = '';
            foreach ($kd_items as $it) { if (trim((string)$it['location_name']) !== '') { $kd_loc = $it['location_name']; break; } }
            $kd_lu_q = $kd_db->query('SELECT last_update FROM biblio WHERE biblio_id=' . (int)$biblio_id);
            $kd_lu = $kd_lu_q ? ($kd_lu_q->fetch_row()[0] ?? '') : '';
            $kd_fields = [
              ['Series Title', $kd_v($series_title)],
              ['Call Number', trim((string)$call_number) !== '' ? '<span class="kd-mono-b">' . $kd_v($call_number) . '</span>' : '-'],
              ['Publisher', $kd_v($publisher_name)],
              ['Place & Year Published', $kd_v($kd_place_year)],
              ['Edition', $kd_v($edition)],
              ['Physical Description (Collation)', $kd_v($collation)],
              ['ISBN / ISSN', trim((string)$isbn_issn) !== '' ? '<span class="kd-mono-n">' . $kd_v($isbn_issn) . '</span>' : '-'],
              ['Classification', $kd_v($classification)],
              ['Language', $kd_v($language_name)],
              ['Shelf Location', $kd_v($kd_loc)],
              ['Specific Detail Info', $kd_v($spec_detail_info)],
              ['Last Updated', $kd_v($kd_lu)],
            ];
            ?>
            <div class="kd-fields">
                <?php foreach ($kd_fields as $f): ?>
                    <div class="kd-field"><span><?= __($f[0]) ?></span><b><?= $f[1] ?></b></div>
                <?php endforeach; ?>
            </div>

            <dl class="kd-dl">
                <?php
                $kd_row(__('Subject(s)'), $subjects ? '<div class="s-subject">' . $subjects . '</div>' : '');
                $kd_row(__('Content Type'), $content_type);
                $kd_row(__('Media Type'), $media_type);
                $kd_row(__('Carrier Type'), $carrier_type);
                $kd_row(__('Statement of Responsibility'), $sor);
                if (!empty($biblio_custom)) {
                  foreach ($biblio_custom as $item) $kd_row($item['label'], $item['value']);
                }
                ?>
            </dl>

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
