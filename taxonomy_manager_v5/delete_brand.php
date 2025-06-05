<?php
require_once __DIR__.'/../db.php';
$id=(int)($_GET['id']??0);
if($id){
  $pdo->prepare('DELETE FROM brands WHERE id=?')->execute([$id]);
}
header('Location: index.php');
