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
 * Live search suggestions for the OPAC search box (Pustaka Ceria theme).
 *
 * GET suggest.php?q=<text>&field=<keywords|title|author|subject|isbn|callnumber|publisher>
 * Returns a JSON array (max 6) of matching, visible bibliographic records.
 */

if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}
require __DIR__ . '/../../sysconfig.inc.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$q = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$field = isset($_GET['field']) && is_string($_GET['field']) ? $_GET['field'] : 'keywords';
if (mb_strlen($q) < 2) {
  echo '[]';
  exit;
}
$q = mb_substr($q, 0, 100);

// escape for use inside LIKE '...'
$e = $dbs->escape_string($q);
$e = str_replace(['%', '_'], ['\\%', '\\_'], $e);

$byTitle = "b.title LIKE '%$e%'";
$byAuthor = "EXISTS (SELECT 1 FROM biblio_author ba INNER JOIN mst_author a ON a.author_id=ba.author_id WHERE ba.biblio_id=b.biblio_id AND a.author_name LIKE '%$e%')";
$bySubject = "EXISTS (SELECT 1 FROM biblio_topic bt INNER JOIN mst_topic t ON t.topic_id=bt.topic_id WHERE bt.biblio_id=b.biblio_id AND t.topic LIKE '%$e%')";
$byIsbn = "b.isbn_issn LIKE '$e%'";
$byCall = "b.call_number LIKE '$e%'";
$byPublisher = "EXISTS (SELECT 1 FROM mst_publisher p WHERE p.publisher_id=b.publisher_id AND p.publisher_name LIKE '%$e%')";

switch ($field) {
  case 'title':      $where = $byTitle; break;
  case 'author':     $where = $byAuthor; break;
  case 'subject':    $where = $bySubject; break;
  case 'isbn':       $where = $byIsbn; break;
  case 'callnumber': $where = $byCall; break;
  case 'publisher':  $where = $byPublisher; break;
  default:           $where = "($byTitle OR $byAuthor OR $bySubject OR $byIsbn OR $byCall)";
}

$sql = "SELECT b.biblio_id, b.title, b.publish_year, b.call_number,
    (SELECT GROUP_CONCAT(a.author_name SEPARATOR ', ') FROM biblio_author ba2 INNER JOIN mst_author a ON a.author_id=ba2.author_id WHERE ba2.biblio_id=b.biblio_id) AS authors
  FROM biblio b
  WHERE b.opac_hide=0 AND $where
  ORDER BY (b.title LIKE '$e%') DESC, b.title ASC
  LIMIT 6";

$out = [];
if ($res = $dbs->query($sql)) {
  while ($r = $res->fetch_assoc()) {
    $authors = (string)($r['authors'] ?? '');
    if (mb_strlen($authors) > 70) {
      $authors = mb_substr($authors, 0, 70) . '…';
    }
    $out[] = [
      'id' => (int)$r['biblio_id'],
      'title' => trim(strip_tags((string)$r['title'])),
      'authors' => $authors,
      'year' => (string)($r['publish_year'] ?? ''),
      'call' => (string)($r['call_number'] ?? ''),
    ];
  }
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);
