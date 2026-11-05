<?php
/**
 * Template by          : Tutorial SLiMS
 * Template description : Integrated library catalog search theme for SLiMS 9.8.0 (ID/EN)
 * Email                : tutorialslims@gmail.com
 * Instagram            : https://instagram.com/tutorial_slims (@tutorial_slims)
 * Copyright            : (c) Tutorial SLiMS. Licensed under GPLv3.
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
    'title' => 'Library', 'icon' => 'fa-book-open', 'color' => 'var(--kd-green)', 'soft' => 'var(--kd-green-soft)', 'param' => 'location',
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
