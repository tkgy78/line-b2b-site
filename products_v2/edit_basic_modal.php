<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  http_response_code(400);
  echo "Missing ID";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

$stmt = $pdo->prepare("SELECT price_type, price FROM prices WHERE product_id = ?");
$stmt->execute([$id]);
$prices = [];
while ($row = $stmt->fetch()) {
  $prices[$row['price_type']] = $row['price'];
}
?>

<form id="editBasicForm" action="update_basic.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $product['id'] ?>">

  <div class="mb-3">
    <label>SKU</label>
    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>">
  </div>

  <div class="mb-3">
    <label>商品名稱</label>
    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>">
  </div>

  <div class="mb-3">
    <label>商品簡述</label>
    <textarea name="short_desc" rows="4" class="form-control"><?= htmlspecialchars($product['short_desc'] ?? '') ?></textarea>
  </div>

  <div class="text-end">
    <button type="submit" class="btn btn-primary">儲存變更</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
  </div>
</form>