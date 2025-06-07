<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  header("Location: index.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

include __DIR__ . '/partials/header.php';
?>

<!-- 這是分頁導覽 -->
<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link" href="edit_basic.php?id=<?= $id ?>">基本資料</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="edit_detail.php?id=<?= $id ?>">商品詳情</a>
  </li>
</ul>

<form action="update_detail.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $id ?>">

  <!-- 詳細說明 CKEditor -->
  <div class="mb-4">
    <label class="form-label">商品詳細說明</label>
    <textarea name="detailed_desc" id="detailed_desc" rows="10" class="form-control"><?= htmlspecialchars($product['detailed_desc']) ?? '' ?></textarea>
  </div>

  <!-- 圖片上傳（多圖） -->
  <div class="mb-4">
    <label class="form-label">新增圖片（可多張）</label>
    <input type="file" name="images[]" multiple class="form-control">
    <small class="text-muted">已上傳圖片如下：</small>
    <div class="mt-2 d-flex flex-wrap gap-2">
      <?php
      $stmt = $pdo->prepare("SELECT id, img_path FROM product_images WHERE product_id = ? ORDER BY created_at");
      $stmt->execute([$id]);
      foreach ($stmt->fetchAll() as $img):
      ?>
        <div class="border p-1">
          <img src="/<?= htmlspecialchars($img['img_path']) ?>" style="width: 80px; height: 80px; object-fit:cover;">
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- PDF 上傳 -->
  <div class="mb-4">
    <label class="form-label">上傳規格書 PDF</label>
    <input type="file" name="spec_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($product['spec_file'])): ?>
      <a href="/<?= $product['spec_file'] ?>" target="_blank">已上傳：<?= basename($product['spec_file']) ?></a>
    <?php endif; ?>
  </div>

  <div class="mb-4">
    <label class="form-label">上傳使用手冊 PDF</label>
    <input type="file" name="manual_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($product['manual_file'])): ?>
      <a href="/<?= $product['manual_file'] ?>" target="_blank">已上傳：<?= basename($product['manual_file']) ?></a>
    <?php endif; ?>
  </div>

  <!-- 儲存按鈕 -->
  <button type="submit" class="btn btn-primary">儲存詳情</button>
  <a href="index.php" class="btn btn-secondary ms-2">取消</a>
</form>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
<script>
  CKEDITOR.replace('detailed_desc');
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>