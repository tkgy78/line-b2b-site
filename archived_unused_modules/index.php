<?php
// taxonomy_manager_v4 index with responsive layout
require_once '../db.php';

$brands = $db->query("SELECT id, name FROM brands")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);
$series = $db->query("SELECT s.id, s.name, b.name AS brand_name FROM series s JOIN brands b ON s.brand_id = b.id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>分類管理 V4</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="row">
    <div class="col-md-4 col-12 mb-4">
      <h5>品牌</h5>
      <ul>
        <?php foreach ($brands as $b): ?>
          <li><?= htmlspecialchars($b['name']) ?> <a href="#">編輯</a> <a href="#">刪除</a></li>
        <?php endforeach; ?>
      </ul>
      <form class="d-flex" method="post" action="#">
        <input type="text" name="brand_name" class="form-control me-2" placeholder="新增品牌">
        <button class="btn btn-primary">送出</button>
      </form>
    </div>
    <div class="col-md-4 col-12 mb-4">
      <h5>系列</h5>
      <form class="mb-2" method="post" action="#">
        <select name="brand_id" class="form-select mb-2">
          <?php foreach ($brands as $b): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="d-flex">
          <input type="text" name="series_name" class="form-control me-2" placeholder="新增系列">
          <button class="btn btn-primary">送出</button>
        </div>
      </form>
      <ul>
        <?php foreach ($series as $s): ?>
          <li><?= htmlspecialchars($s['brand_name']) ?> - <?= htmlspecialchars($s['name']) ?> <a href="#">編輯</a> <a href="#">刪除</a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="col-md-4 col-12 mb-4">
      <h5>分類</h5>
      <ul>
        <?php foreach ($categories as $c): ?>
          <li><?= htmlspecialchars($c['name']) ?> <a href="#">編輯</a> <a href="#">刪除</a></li>
        <?php endforeach; ?>
      </ul>
      <form class="d-flex" method="post" action="#">
        <input type="text" name="category_name" class="form-control me-2" placeholder="新增分類">
        <button class="btn btn-primary">送出</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
