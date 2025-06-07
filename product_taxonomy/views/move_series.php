<?php
require_once __DIR__.'/../../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
$dir = $_GET['direction'] ?? '';

if (!$id || !in_array($dir, ['up', 'down'])) {
  header('Location: ./index.php');
  exit;
}

// 找當前資料
$stmt = $pdo->prepare("SELECT * FROM series WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();

if (!$current) {
  header('Location: ./index.php');
  exit;
}

// 找相鄰的項目
$compareStmt = $pdo->prepare("
  SELECT * FROM series 
  WHERE brand_id = ? AND display_order " . ($dir === 'up' ? '<' : '>') . " ? 
  ORDER BY display_order " . ($dir === 'up' ? 'DESC' : 'ASC') . " 
  LIMIT 1
");
$compareStmt->execute([$current['brand_id'], $current['display_order']]);
$target = $compareStmt->fetch();

if ($target) {
  // 交換兩者的排序值
  $pdo->prepare("UPDATE series SET display_order = ? WHERE id = ?")
      ->execute([$target['display_order'], $current['id']]);
  $pdo->prepare("UPDATE series SET display_order = ? WHERE id = ?")
      ->execute([$current['display_order'], $target['id']]);
}

header('Location: ../index.php');
exit;