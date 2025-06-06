<?php
require_once __DIR__ . '/../db.php';
$id = $_GET['id'] ?? null;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
?>

<form action="update_detail.php" method="post">
  <input type="hidden" name="id" value="<?= $product['id'] ?>">
  <div class="mb-3">
    <label>商品說明</label>
    <textarea name="description" id="description" rows="10" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
  </div>
  <!-- 這裡未來可加圖片上傳區塊、PDF 區塊等 -->
  <button class="btn btn-primary">儲存商品詳情</button>
</form>

<!-- CKEditor 啟用 -->
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>CKEDITOR.replace('description');</script>