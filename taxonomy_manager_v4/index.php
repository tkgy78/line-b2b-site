<?php
require_once '../db.php';

$brands = $db->query("SELECT * FROM brands")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$series = $db->query("SELECT s.*, b.name AS brand_name FROM series s JOIN brands b ON s.brand_id = b.id")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>分類管理 V4</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h4>品牌</h4>
        <ul class="list-group mb-3">
          <?php foreach ($brands as $b): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($b['name']) ?>
              <span>
                <a href="edit_brand.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">編輯</a>
                <a href="delete_brand.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger">刪除</a>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
        <form class="input-group" method="POST" action="store_brand.php">
          <input type="text" name="name" class="form-control" placeholder="新增品牌" required>
          <button class="btn btn-success" type="submit">送出</button>
        </form>
      </div>

      <div class="col-md-4">
        <h4>系列</h4>
        <form class="mb-2" method="POST" action="store_series.php">
          <select class="form-select mb-2" name="brand_id" required>
            <option value="">選擇品牌</option>
            <?php foreach ($brands as $b): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="input-group">
            <input type="text" name="name" class="form-control" placeholder="新增系列" required>
            <button class="btn btn-success" type="submit">送出</button>
          </div>
        </form>
        <ul class="list-group">
          <?php foreach ($series as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($s['brand_name']) ?> - <?= htmlspecialchars($s['name']) ?>
              <span>
                <a href="edit_series.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">編輯</a>
                <a href="delete_series.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger">刪除</a>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="col-md-4">
        <h4>分類</h4>
        <ul class="list-group mb-3">
          <?php foreach ($categories as $c): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <?= htmlspecialchars($c['name']) ?>
              <span>
                <a href="edit_category.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">編輯</a>
                <a href="delete_category.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger">刪除</a>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
        <form class="input-group" method="POST" action="store_category.php">
          <input type="text" name="name" class="form-control" placeholder="新增分類" required>
          <button class="btn btn-success" type="submit">送出</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
