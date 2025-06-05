<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 取下拉選單資料
$brands = $pdo->query("SELECT id,name FROM brands ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$series    = $pdo->query("SELECT id,name FROM series ORDER BY name")->fetchAll();

$page_title = '新增商品';
include __DIR__ . '/_partials/header.php';
?>
<h1 class="mb-4">新增商品</h1>

<form action="store_product.php" method="post" enctype="multipart/form-data">
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">品牌</label>
      <select name="brand_id" class="form-select" required>
        <option value="">-- 請選擇 --</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">分類</label>
      <select name="category_id" class="form-select" required>
        <option value="">-- 請選擇 --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">系列</label>
      <select name="series_id" class="form-select">
        <option value="0">-- 無 --</option>
        <?php foreach ($series as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label">商品名稱</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-3">
      <label class="form-label">SKU</label>
      <input type="text" name="sku" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">封面圖</label>
      <input type="file" name="cover_img" class="form-control">
    </div>

    <!-- 價格區 -->
    <div class="col-md-3">
      <label class="form-label">建議售價 (MSRP)</label>
      <input type="number" step="0.01" name="price_msrp" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">VIP 價</label>
      <input type="number" step="0.01" name="price_vip" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">VVIP 價</label>
      <input type="number" step="0.01" name="price_vvip" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">批發價</label>
      <input type="number" step="0.01" name="price_wholesale" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">成本價</label>
      <input type="number" step="0.01" name="price_cost" class="form-control">
    </div>

    <div class="col-md-12">
      <label class="form-label">商品簡述 (可含 HTML)</label>
      <textarea name="short_desc" class="form-control" rows="4"></textarea>
    </div>

    <div class="col-12 form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" name="status" value="1" checked>
      <label class="form-check-label">上架</label>
    </div>

    <div class="col-12">
      <button class="btn btn-success">新增商品</button>
      <a href="index.php" class="btn btn-secondary">返回列表</a>
    </div>
  </div>
</form>

<?php include __DIR__ . '/_partials/footer.php'; ?>