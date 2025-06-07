<?php
require_once __DIR__.'/../db.php';
$pdo = connect();

if (!empty($_POST['name']) && !empty($_POST['brand_id'])) {
  $brand_id = (int)$_POST['brand_id'];
  $name = trim($_POST['name']);

  // 抓目前該品牌的最大 display_order
  $stmt = $pdo->prepare("SELECT MAX(display_order) FROM series WHERE brand_id = ?");
  $stmt->execute([$brand_id]);
  $maxOrder = (int)$stmt->fetchColumn();

  $stmt = $pdo->prepare('INSERT INTO series(name, brand_id, display_order) VALUES (?, ?, ?)');
  $stmt->execute([$name, $brand_id, $maxOrder + 1]);
}
header('Location: index.php');