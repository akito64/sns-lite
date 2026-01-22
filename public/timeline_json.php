<?php
require __DIR__.'/_common.php';

login_required();
$me = current_user();
$dbh = db();

// パラメータ
$limit  = max(1, min(50, (int)($_GET['limit'] ?? 10))); // 1〜50
$offset = max(0, (int)($_GET['offset'] ?? 0));

// タイムライン対象（自分 + 自分がフォローしている人）
$sql =
 'SELECT b.*, u.name user_name, u.icon_filename user_icon_filename
    FROM bbs_entries b
    JOIN users u ON b.user_id = u.id
   WHERE b.user_id = :me
      OR b.user_id IN (SELECT followee_user_id FROM user_relationships WHERE follower_user_id = :me)
   ORDER BY b.created_at DESC, b.id DESC
   LIMIT :limit OFFSET :offset';
$st = $dbh->prepare($sql);
$st->bindValue(':me', $me['id'], PDO::PARAM_INT);
$st->bindValue(':limit', $limit, PDO::PARAM_INT);
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll();

// 画像をまとめて取る（N+1回避のためID一覧で一括取得）
$entryIds = array_map(fn($r)=> (int)$r['id'], $rows);
$imagesByEntry = [];
if ($entryIds) {
  $in = implode(',', array_fill(0, count($entryIds), '?'));
  $imgSt = $dbh->prepare("SELECT entry_id, filename FROM bbs_entry_images WHERE entry_id IN ($in) ORDER BY id ASC");
  $imgSt->execute($entryIds);
  foreach ($imgSt as $img) {
    $eid = (int)$img['entry_id'];
    $imagesByEntry[$eid][] = '/image/' . rawurlencode($img['filename']);
  }
}

// 旧カラム image_filename も後方互換で反映
$result = [];
foreach ($rows as $r) {
  $imgs = $imagesByEntry[(int)$r['id']] ?? [];
  if (!empty($r['image_filename'])) {
    array_unshift($imgs, '/image/' . rawurlencode($r['image_filename']));
    $imgs = array_values(array_unique($imgs));
  }
  $result[] = [
    'id' => (int)$r['id'],
    'user_id' => (int)$r['user_id'],
    'user_name' => $r['user_name'],
    'user_icon' => $r['user_icon_filename'] ? '/image/'.rawurlencode($r['user_icon_filename']) : null,
    'user_profile_url' => '/profile.php?user_id='.(int)$r['user_id'],
    'body' => nl2br(e($r['body'] ?? '')),
    'created_at' => $r['created_at'],
    'images' => $imgs, // 0〜4枚
  ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  'entries' => $result,
  'nextOffset' => $offset + count($rows),
  'hasMore' => count($rows) === $limit,
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);