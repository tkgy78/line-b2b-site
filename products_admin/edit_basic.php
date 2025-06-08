<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  header("Location: index.php");
  exit;
}

// 撈資料
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

$stmt = $pdo->prepare("SELECT price_type, price FROM prices WHERE product_id = ?");
$stmt->execute([$id]);
$prices = [];
while ($row = $stmt->fetch()) {
  $prices[$row['price_type']] = $row['price'];
}

include __DIR__ . '/partials/header.php';
?>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link active" href="edit_basic.php?id=<?= $id ?>">基本資料</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="edit_detail.php?id=<?= $id ?>">商品詳情</a>
  </li>
</ul>

<form action="update_basic.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $id ?>">

  <!-- 以下為商品基本欄位（品牌、型號、價格等） -->
  <div class="mb-3">
    <label>SKU</label>
    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>">
  </div>

  <div class="mb-3">
    <label>商品名稱</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>">
  </div>

  <!-- 更多欄位自行加入... -->

  <div class="mb-4">
    <label>商品簡述</label>
    <textarea name="short_desc" rows="4" class="form-control"><?= htmlspecialchars($product['short_desc'] ?? '') ?></textarea>
  </div>

  <button type="submit" class="btn btn-primary">儲存變更</button>
  <a href="index.php" class="btn btn-secondary ms-2">取消</a>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>