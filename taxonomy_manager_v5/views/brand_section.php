<?php
// 取品牌
$stmt = $pdo->query("SELECT * FROM brands ORDER BY id DESC");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="col-12 col-md-4">
  <h4>品牌</h4>
  <ul class="list-group mb-3">
  <?php foreach($brands as $b): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center">
  <!-- 左側：品牌名稱 -->
  <span><?= htmlspecialchars($b['name']); ?></span>

  <!-- 右側：Logo + 按鈕群 -->
  <span class="d-flex align-items-center gap-2">
      <!-- (1) Logo 縮圖   -->
      <img
        src="/line_b2b/uploads/brand_logos/<?= htmlspecialchars($b['logo'] ?: 'no-logo.png'); ?>"
        alt="logo"
        class="logo-thumb">

      <!-- (2) 編輯 / 刪除按鈕 -->
      <a href="edit_brand.php?id=<?= $b['id']; ?>"  class="btn btn-sm btn-outline-primary">編輯</a>
      <a href="delete_brand.php?id=<?= $b['id']; ?>" class="btn btn-sm btn-outline-danger">刪除</a>
  </span>
</li>
  <?php endforeach; ?>
  </ul>
  <form class="mb-4" method="POST" action="store_brand.php" enctype="multipart/form-data">
    <div class="mb-2">
      <input type="text" name="name" class="form-control" placeholder="品牌名稱" required>
    </div>
    <div class="mb-2">
      <input type="file" name="logo" class="form-control" accept="image/*">
    </div>
    <button class="btn btn-success w-100">新增品牌</button>
  </form>
</div>
