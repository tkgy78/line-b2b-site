<?php
require_once __DIR__.'/../db.php';
if (!empty($_POST['name'])) {
  $pdo->prepare('INSERT INTO categories(name) VALUES (?)')->execute([trim($_POST['name'])]);
}
header('Location: index.php');
