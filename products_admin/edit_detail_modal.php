<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  echo "<div class='text-danger p-3'>未提供商品 ID</div>";
  exit;
}

// 撈主商品
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<div class='text-danger p-3'>找不到此商品</div>";
  exit;
}

// 撈圖片
$stmt = $pdo->prepare("SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY id");
$stmt->execute([$id]);
$productImages = $stmt->fetchAll();
?>

<!-- 分頁選單 -->
<ul class="nav nav-tabs mb-3" id="tab-detail">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#desc">商品說明</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#images">圖片管理</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#pdf">PDF 規格與手冊</a></li>
</ul>

<form action="update_detail.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $product['id'] ?>">

  <div class="tab-content">
    <!-- 商品說明 -->
    <div class="tab-pane fade show active" id="desc">
      <div class="mb-3">
        <label class="form-label">詳細說明</label>
        <textarea name="detailed_desc" id="detailed_desc" rows="10" class="form-control"><?= htmlspecialchars($product['detailed_desc'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- 圖片 -->
    <div class="tab-pane fade" id="images">
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
    </div>

    <!-- PDF 區 -->
    <div class="tab-pane fade" id="pdf">
      <div class="mb-3">
        <label class="form-label">上傳規格書 PDF</label>
        <input type="file" name="spec_file" accept="application/pdf" class="form-control mb-2">
        <?php if (!empty($product['spec_file'])): ?>
          <a href="/<?= htmlspecialchars($product['spec_file']) ?>" target="_blank" class="d-block">
            已上傳：<?= basename($product['spec_file']) ?>
          </a>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <label class="form-label">上傳使用手冊 PDF</label>
        <input type="file" name="manual_file" accept="application/pdf" class="form-control mb-2">
        <?php if (!empty($product['manual_file'])): ?>
          <a href="/<?= htmlspecialchars($product['manual_file']) ?>" target="_blank" class="d-block">
            已上傳：<?= basename($product['manual_file']) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">儲存商品詳情</button>
  </div>
</form>

<!-- CKEditor 與分頁 JS -->
<script src="https://cdn.ckeditor.com/4.25.1-lts/standard/ckeditor.js"></script>
<script>
  // CKEditor 初始化
  setTimeout(() => {
    if (document.querySelector('#detailed_desc')) {
      CKEDITOR.replace('detailed_desc', { height: 300 });
    }
  }, 200);

  // 分頁切換強制啟用（Modal 下有時失效）
  const tabs = document.querySelectorAll('#tab-detail a');
  tabs.forEach(tab => {
    tab.addEventListener('click', function (e) {
      e.preventDefault();
      new bootstrap.Tab(this).show();
    });
  });
</script>