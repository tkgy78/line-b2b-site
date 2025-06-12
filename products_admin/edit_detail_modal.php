<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  echo "<div class='text-danger p-3'>未提供商品 ID</div>";
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<div class='text-danger p-3'>找不到此商品</div>";
  exit;
}

// 撈詳細資料
$detailStmt = $pdo->prepare("SELECT * FROM product_details WHERE product_id = ?");
$detailStmt->execute([$id]);
$detail = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: ['detailed_desc' => '', 'spec_file' => '', 'manual_file' => ''];

// 撈圖片
$stmt = $pdo->prepare("SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY id");
$stmt->execute([$id]);
$productImages = $stmt->fetchAll();
?>

<form action="update_detail.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

  <!-- 商品說明 -->
  <div class="mb-3">
    <label class="form-label">詳細說明</label>
    <textarea name="detailed_desc" id="detailed_desc" rows="10" class="form-control"><?= htmlspecialchars($detail['detailed_desc']) ?></textarea>
  </div>

  <!-- 圖片管理 -->
  <div class="mb-3">
    <label class="form-label">新增圖片（可多選）</label>
    <input type="file" name="images[]" multiple class="form-control" accept="image/*">
  </div>
  <div class="mb-2">已上傳圖片如下：</div>
  <div class="d-flex flex-wrap gap-2">
    <?php foreach ($productImages as $img): ?>
      <div class="border p-1 text-center">
        <img src="/<?= htmlspecialchars($img['image_url']) ?>" style="width: 80px; height: 80px; object-fit: cover;">
        <div class="mt-1">
          <a href="delete_image.php?id=<?= $img['id'] ?>&pid=<?= $id ?>" class="btn btn-sm btn-outline-danger">刪除</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- PDF 區 -->
  <div class="mb-3 mt-4">
    <label class="form-label">上傳規格書 PDF</label>
    <input type="file" name="spec_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($detail['spec_file'])): ?>
      <a href="/<?= htmlspecialchars($detail['spec_file']) ?>" target="_blank" class="d-block">已上傳：<?= basename($detail['spec_file']) ?></a>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">上傳使用手冊 PDF</label>
    <input type="file" name="manual_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($detail['manual_file'])): ?>
      <a href="/<?= htmlspecialchars($detail['manual_file']) ?>" target="_blank" class="d-block">已上傳：<?= basename($detail['manual_file']) ?></a>
    <?php endif; ?>
  </div>

  <div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">儲存商品詳情</button>
  </div>
</form>

<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
  setTimeout(() => {
    if (document.querySelector('#detailed_desc')) {
      CKEDITOR.replace('detailed_desc', { height: 300 });
    }
  }, 200);
</script>