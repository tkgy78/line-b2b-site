<?php
require_once __DIR__.'/../db.php';
if (!empty($_POST['name']) && !empty($_POST['brand_id'])) {
  $stmt=$pdo->prepare('INSERT INTO series(name,brand_id) VALUES(?,?)');
  $stmt->execute([trim($_POST['name']), (int)$_POST['brand_id']]);
}
header('Location: index.php');
