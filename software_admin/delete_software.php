<?php
// software_admin/delete_software.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;

if (!$id) {
  echo "未提供軟體 ID。";
  exit;
}

// 軟體若已被產品綁定，請先移除關聯（可改進為顯示提示）
$stmt = $pdo->prepare("DELETE FROM softwares WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;