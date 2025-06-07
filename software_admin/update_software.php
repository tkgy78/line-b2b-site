<?php
// software_admin/update_software.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? 0;
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if (!$id || $name === '') {
    echo "資料不完整。";
    exit;
  }

  $stmt = $pdo->prepare("UPDATE softwares SET name = ?, description = ?, updated_at = NOW() WHERE id = ?");
  $stmt->execute([$name, $description, $id]);

  header("Location: index.php");
  exit;
} else {
  echo "非法請求。";
}