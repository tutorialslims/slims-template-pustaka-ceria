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
 * Home page - Pustaka Ceria theme
 */

// ---------------------------------------------------------------------------
// data: total collection, category groups, popular subjects
// ---------------------------------------------------------------------------
$kd_total = 0;
$kd_groups = [];
$kd_subjects = [];

$kd_q = $dbs->query('SELECT COUNT(*) FROM biblio');
if ($kd_q) $kd_total = (int)$kd_q->fetch_row()[0];

$kd_defs = [
  [
    'title' => 'Type of Material', 'icon' => 'fa-file-alt', 'color' => 'var(--kd-blue)', 'soft' => 'var(--kd-blue-soft)', 'param' => 'gmd',
    'sql' => "SELECT g.gmd_name, COUNT(*) c FROM biblio b INNER JOIN mst_gmd g ON g.gmd_id=b.gmd_id GROUP BY g.gmd_id ORDER BY c DESC LIMIT 12",
  ],
  [
    'title' => 'Repository Name', 'icon' => 'fa-university', 'color' => 'var(--kd-red)', 'soft' => 'var(--kd-red-soft)', 'param' => 'colltype',
    'sql' => "SELECT t.coll_type_name, COUNT(DISTINCT i.biblio_id) c FROM item i INNER JOIN mst_coll_type t ON t.coll_type_id=i.coll_type_id GROUP BY t.coll_type_id ORDER BY c DESC LIMIT 12",
  ],
  [
    'title' => 'Library', 'icon' => 'fa-book', 'color' => 'var(--kd-green)', 'soft' => 'var(--kd-green-soft)', 'param' => 'location',
    'sql' => "SELECT l.location_name, COUNT(DISTINCT i.biblio_id) c FROM item i INNER JOIN mst_location l ON l.location_id=i.location_id GROUP BY l.location_id ORDER BY c DESC LIMIT 12",
  ],
];
foreach ($kd_defs as $def) {
  $rows = [];
  if ($q = $dbs->query($def['sql'])) {
    while ($r = $q->fetch_row()) $rows[] = $r;
  }
  $def['rows'] = $rows;
  $kd_groups[] = $def;
}

$kd_sq = $dbs->query("SELECT t.topic, COUNT(*) c FROM biblio_topic bt INNER JOIN mst_topic t ON t.topic_id=bt.topic_id GROUP BY t.topic_id ORDER BY c DESC, t.topic ASC LIMIT 18");
if ($kd_sq) {
  while ($r = $kd_sq->fetch_row()) $kd_subjects[] = $r;
}

// DDC main classes (000-900): collection count per class, taken from call numbers in the database
$kd_ddc_counts = array_fill(0, 10, 0);
if ($q = $dbs->query("SELECT LEFT(call_number,1) d, COUNT(*) c FROM biblio WHERE call_number REGEXP '^[0-9]' GROUP BY d")) {
  while ($r = $q->fetch_row()) $kd_ddc_counts[(int)$r[0]] = (int)$r[1];
}
$kd_ddc = [
  [0, 'Computers & General Works',    'Coding, Informatics, Encyclopedias',        'fa-code',  '#2563eb', '#eef5ff', '#cfe0ff'],
  [1, 'Psychology & Character',      'Self-development, Ethics, Motivation',       'fa-lightbulb',        '#7c3aed', '#f8f2ff', '#e5d4ff'],
  [2, 'Religion & Character',       'Religious Education & Noble Character',          'fa-moon',       '#059669', '#ecfdf3', '#b7ebc9'],
  [3, 'Social Sciences & Education', 'Social Studies, Economics, Law, Scouting',             'fa-users',        '#b45309', '#fff8e6', '#fde8a8'],
  [4, 'Language & Dictionaries',    'Indonesian, English, Regional Languages',        'fa-language',     '#e11d48', '#fff1f2', '#fecdd3'],
  [5, 'Science & Mathematics',     'Mathematics, Physics, Biology, Chemistry',       'fa-flask',        '#0d9488', '#effdfa', '#b4efe3'],
  [6, 'Technology & Applied Sciences', 'Entrepreneurship, Health, Engineering',         'fa-microchip',    '#4f46e5', '#eef0ff', '#c9ceff'],
  [7, 'Arts, Music & Sports',       'Arts & Culture, Crafts, Physical Education',         'fa-paint-brush',      '#db2777', '#fff0f7', '#fbcfe8'],
  [8, 'Literature, Novels & Short Stories', 'Teen Novels, Poetry, Educational Comics',       'fa-book',  '#ea580c', '#fff4ec', '#fed7b8'],
  [9, 'History, Maps & Biographies', 'Archipelago History, National Heroes',        'fa-globe',   '#0891b2', '#ecfcff', '#b5ecf7'],
];

// quick shortcuts: the biggest collection types in the database
$kd_ctypes = [];
if ($q = $dbs->query("SELECT t.coll_type_name, COUNT(DISTINCT i.biblio_id) c FROM mst_coll_type t LEFT JOIN item i ON i.coll_type_id=t.coll_type_id GROUP BY t.coll_type_id ORDER BY c DESC, t.coll_type_name ASC LIMIT 5")) {
  while ($r = $q->fetch_row()) $kd_ctypes[] = $r;
}
$kd_ct_icons = ['fa-book', 'fa-bookmark', 'fa-leaf', 'fa-tablet-alt', 'fa-graduation-cap'];

// popular collections: most borrowed titles (falls back to newest when nothing was borrowed yet)
$kd_pop = [];
$kd_pq = $dbs->query("SELECT b.biblio_id, b.title, b.call_number, b.publish_year, b.image,
    (SELECT GROUP_CONCAT(a.author_name SEPARATOR ', ') FROM biblio_author ba INNER JOIN mst_author a ON a.author_id=ba.author_id WHERE ba.biblio_id=b.biblio_id) AS authors,
    (SELECT COUNT(*) FROM item i WHERE i.biblio_id=b.biblio_id) AS eks,
    (SELECT t.coll_type_name FROM item i INNER JOIN mst_coll_type t ON t.coll_type_id=i.coll_type_id WHERE i.biblio_id=b.biblio_id LIMIT 1) AS ctype,
    (SELECT COUNT(*) FROM loan l INNER JOIN item i ON i.item_code=l.item_code WHERE i.biblio_id=b.biblio_id) AS loans
  FROM biblio b WHERE b.opac_hide=0 ORDER BY loans DESC, b.last_update DESC, b.biblio_id DESC LIMIT 6");
if ($kd_pq) {
  while ($r = $kd_pq->fetch_assoc()) $kd_pop[] = $r;
}
$kd_card = function ($b, $mod = 4) {
    $bid = (int)$b['biblio_id'];
    $btitle = trim(strip_tags($b['title']));
    $img_ok = !empty($b['image']) && is_file(__DIR__ . '/../../../images/docs/' . $b['image']);
    $detail = 'index.php?p=show_detail&id=' . $bid;
    $bookmarked = in_array($bid, $_SESSION['bookmark'] ?? []);
    ob_start(); ?>
                <article class="kd-pop-item" id="pop-<?= $bid ?>">
                    <a class="kd-pop-cover kd-pg<?= $bid % $mod ?><?= $img_ok ? ' has-img' : '' ?>" href="<?= $detail ?>"<?= $img_ok ? ' style="background-image:url(\'./lib/minigalnano/createthumb.php?filename=' . urlencode('images/docs/' . $b['image']) . '&width=240\')"' : '' ?>>
                        <?php if (!$img_ok): ?>
                            <span class="cv-top"><b><?= htmlspecialchars($b['call_number'] ?: '') ?></b><i class="fas fa-bookmark"></i></span>
                            <span class="cv-title"><?= htmlspecialchars($btitle) ?></span>
                            <span class="cv-year"><?= htmlspecialchars($b['publish_year'] ?: '') ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="kd-pop-body">
                        <div class="kd-pop-badges">
                            <?php if (!empty($b['ctype'])): ?><span class="b-type"><?= htmlspecialchars($b['ctype']) ?></span><?php endif; ?>
                            <?php if ((int)$b['eks'] > 0): ?><span class="b-eks"><?= (int)$b['eks'] === 1 ? __('1 Copy') : sprintf(__('%d Copies'), (int)$b['eks']) ?></span><?php endif; ?>
                        </div>
                        <a class="kd-pop-title" href="<?= $detail ?>"><?= htmlspecialchars($btitle) ?></a>
                        <?php if (!empty($b['authors'])): ?><div class="kd-pop-author"><i class="fas fa-users"></i> <span><?= htmlspecialchars($b['authors']) ?></span></div><?php endif; ?>
                        <div class="kd-pop-foot">
                            <span class="kd-pop-call"><i class="fas fa-th-list"></i> <?= htmlspecialchars($b['call_number'] ?: '-') ?></span>
                            <span class="kd-pop-acts">
                                <a href="#" class="kd-pop-bm bookMarkBook<?= $bookmarked ? ' bg-success text-white' : '' ?>" data-id="<?= $bid ?>" title="<?= __('Bookmark') ?>"><i class="fas fa-bookmark"></i><label id="label-<?= $bid ?>" class="sr-only"><?= __('Bookmark') ?></label></a>
                                <a href="<?= $detail ?>" class="kd-pop-detail"><?= __('Detail') ?></a>
                            </span>
                        </div>
                    </div>
                </article>
    <?php return ob_get_clean();
};

// newest / recently updated collections (excluding the ones already shown above)
$kd_new = [];
$kd_ex = array_map(function ($r) { return (int)$r['biblio_id']; }, $kd_pop);
$kd_exsql = $kd_ex ? 'AND b.biblio_id NOT IN (' . implode(',', $kd_ex) . ')' : '';
$kd_nq = $dbs->query("SELECT b.biblio_id, b.title, b.call_number, b.publish_year, b.image,
    (SELECT GROUP_CONCAT(a.author_name SEPARATOR ', ') FROM biblio_author ba INNER JOIN mst_author a ON a.author_id=ba.author_id WHERE ba.biblio_id=b.biblio_id) AS authors,
    (SELECT COUNT(*) FROM item i WHERE i.biblio_id=b.biblio_id) AS eks,
    (SELECT t.coll_type_name FROM item i INNER JOIN mst_coll_type t ON t.coll_type_id=i.coll_type_id WHERE i.biblio_id=b.biblio_id LIMIT 1) AS ctype
  FROM biblio b WHERE b.opac_hide=0 $kd_exsql ORDER BY b.last_update DESC, b.biblio_id DESC LIMIT 6");
if ($kd_nq) {
  while ($r = $kd_nq->fetch_assoc()) $kd_new[] = $r;
}
?>

<header class="c-header"><?php include '_navbar.php'; ?></header>

<section class="kd-hero">
    <i class="kd-deco dots"></i>
    <i class="kd-deco fas fa-star star1"></i>
    <i class="kd-deco fas fa-star star2"></i>
    <i class="kd-deco dot"></i>
    <i class="kd-deco ring"></i>
    <i class="kd-deco fas fa-book-open book"></i>
    <i class="kd-deco fas fa-pencil-alt pencil"></i>
    <div class="kd-container">
        <span class="kd-chip"><i class="fas fa-star"></i> <?= __('Public School Library Catalog'); ?></span>
        <h1><?= __("Let's explore the world"); ?> <span><?= __('through reading!'); ?></span></h1>
        <p class="kd-sub"><?= __('One-stop search for school library collections'); ?></p>

        <div class="kd-search-card">
            <?php $kd_home = true; include '_search-form.php'; $kd_home = false; ?>
        </div>

        <div class="kd-quote">
            <i class="fas fa-quote-left"></i>
            <span><?= __('Reading is a window to the world. Every book opens a new adventure.'); ?></span>
        </div>
    </div>
</section>

<main class="kd-main-pad">
    <div class="kd-container">
        <section class="kd-section">
            <h2><?= __('Explore Collection Categories'); ?></h2>
            <span class="kd-underline"></span>
            <p class="kd-desc"><?= __('Choose the type of collection, source, or school library you want to explore.'); ?></p>
            <div class="kd-cats">
                <?php foreach ($kd_groups as $g): ?>
                    <div class="kd-cat" style="--c: <?= $g['color'] ?>; --cs: <?= $g['soft'] ?>">
                        <div class="kd-cat-head">
                            <span class="kd-cat-icon"><i class="fas <?= $g['icon'] ?>"></i></span>
                            <div>
                                <strong><?= __($g['title']) ?></strong>
                                <small><?= count($g['rows']) === 1 ? __('1 category') : sprintf(__('%d categories'), count($g['rows'])) ?></small>
                            </div>
                        </div>
                        <ul>
                            <?php foreach ($g['rows'] as $row): ?>
                                <li>
                                    <a href="index.php?<?= $g['param'] ?>=<?= urlencode($row[0]) ?>&search=search">
                                        <?= htmlspecialchars($row[0]) ?> <span class="kd-count"><?= (int)$row[1] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <?php if (!$g['rows']): ?><li><a href="#"><?= __('No data yet'); ?></a></li><?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="kd-ddc-wrap">
            <div class="kd-ddc-card">
                <span class="kd-ddc-chip"><i class="fas fa-th-large"></i> <?= __('Browse DDC Classification') ?></span>
                <div class="kd-ddc-head">
                    <div>
                        <h2><?= __('Pick Your Favorite Topics & Subjects') ?></h2>
                        <p><?= __('Click a category below to browse the shelves directly.') ?></p>
                    </div>
                    <a class="kd-ddc-all" href="index.php?keywords=&amp;search=search"><?= __('View All Classes (000-900)') ?> <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="kd-ddc-grid">
                    <?php foreach ($kd_ddc as $d): ?>
                        <a class="kd-ddc-item" style="--a: <?= $d[4] ?>; --b: <?= $d[5] ?>; --l: <?= $d[6] ?>" href="index.php?callnumber=<?= $d[0] ?>&amp;search=search">
                            <span class="kd-ddc-top">
                                <span class="kd-ddc-ico"><i class="fas <?= $d[3] ?>"></i></span>
                                <span class="kd-ddc-code">DDC <?= $d[0] ?>00</span>
                            </span>
                            <strong><?= __($d[1]) ?></strong>
                            <small><?= __($d[2]) ?></small>
                            <em><?= sprintf(__('%s collections'), number_format($kd_ddc_counts[$d[0]])) ?></em>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($kd_ctypes): ?>
            <div class="kd-ddc-pills">
                <?php foreach ($kd_ctypes as $i => $ct): ?>
                    <a href="index.php?colltype=<?= urlencode($ct[0]) ?>&amp;search=search">
                        <span class="kd-pill-ico"><i class="fas <?= $kd_ct_icons[$i % 5] ?>"></i></span>
                        <span><strong><?= htmlspecialchars($ct[0]) ?></strong><small><?= __('Browse Collection') ?><?= (int)$ct[1] > 0 ? ' (' . number_format((int)$ct[1]) . ')' : '' ?> &rarr;</small></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <?php if ($kd_pop): ?>
        <section class="kd-pop">
            <span class="kd-pop-chip"><i class="fas fa-star"></i> <?= __('Librarian Recommendations') ?></span>
            <div class="kd-pop-head">
                <div>
                    <h2><?= __('Popular Collections & Student Textbooks') ?></h2>
                    <p><?= __('Books most often borrowed and used as main study references in our library.') ?></p>
                </div>
                <a class="kd-ddc-all" href="index.php?keywords=&amp;search=search"><?= __('View All Collections') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="kd-pop-grid">
                <?php foreach ($kd_pop as $b) echo $kd_card($b, 4); ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($kd_new): ?>
        <section class="kd-pop kd-new">
            <span class="kd-pop-chip kd-new-chip"><i class="fas fa-leaf"></i> <?= __('Just Arrived') ?></span>
            <div class="kd-pop-head">
                <div>
                    <h2><?= __('Newest & Recently Updated Collections') ?></h2>
                    <p><?= __('Books and academic works that were just processed and are ready to read today.') ?></p>
                </div>
                <a class="kd-ddc-all kd-new-all" href="index.php?keywords=&amp;search=search"><?= __('Sort by Newest') ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="kd-pop-grid">
                <?php foreach ($kd_new as $b) echo $kd_card($b, 5); ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($kd_subjects): ?>
            <section class="kd-section">
                <h2><?= __('Popular Subjects'); ?></h2>
                <span class="kd-underline"></span>
                <p class="kd-desc"><?= __('Favorite topics with the most items available. Start your reading adventure here!'); ?></p>
                <div class="kd-topics">
                    <?php foreach ($kd_subjects as $i => $s): ?>
                        <a class="kd-topic kd-t<?= $i % 5 ?>" href="index.php?subject=<?= urlencode($s[0]) ?>&search=search">
                            <?= htmlspecialchars($s[0]) ?> <b><?= (int)$s[1] ?></b>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>
</main>
