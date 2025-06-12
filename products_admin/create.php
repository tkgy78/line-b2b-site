<?php
require_once __DIR__.'/../db.php';
$pdo = connect();

/* 下拉資料 */
$brands     = $pdo->query("SELECT id,name FROM brands ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();

$page_title = '新增商品';
include __DIR__.'/partials/header.php';
?>
<h1 class="mb-3">新增商品</h1>

<form action="store_product.php" method="post" enctype="multipart/form-data">
  <!-- 品牌 / 分類 / 系列 -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">品牌</label>
      <select name="brand_id" class="form-select" required>
        <option value="">--選擇--</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">分類</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach;?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">系列</label>
      <select name="series_id" class="form-select">
        <option value="">--無--</option>
        <!-- 將由 JS 根據品牌載入 -->
      </select>
    </div>
  </div>

  <!-- 名稱 / SKU -->
  <div class="row mb-3">
    <div class="col-md-8">
      <label class="form-label">商品名稱 (型號)</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">SKU (編號)</label>
      <input type="text" name="sku" class="form-control">
    </div>
  </div>

  <!-- 六欄價格一行 -->
  <div class="row mb-3">
    <div class="col-6 col-lg-2"><label class="form-label">建議售價</label><input name="price_msrp" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label">VIP</label><input name="price_vip" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label">VVIP</label><input name="price_vvip" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label">批發</label><input name="price_wholesale" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label">成本</label><input name="price_cost" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label">EMMA 價</label><input name="price_emma" class="form-control"></div>
  </div>

  <!-- 圖片 / 狀態 -->
  <div class="row mb-3">
    <div class="col-md-6"><label class="form-label">封面圖</label><input type="file" name="cover_img" class="form-control"></div>
    <div class="col-md-3 d-flex align-items-end">
      <div class="form-check">
        <input type="checkbox" name="status" value="active" id="st">
        <label for="st" class="form-check-label">上架</label>
      </div>
    </div>
  </div>

  <!-- 商品簡述 -->
  <div class="mb-3">
    <label class="form-label">商品簡述</label>
    <textarea name="short_desc" rows="4" class="form-control"></textarea>
  </div>

  <button class="btn btn-success">提交</button>
  <a href="index.php" class="btn btn-secondary">返回</a>
</form>

<!-- ✅ 加入 JS 放在 footer.php 前或內部 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const brandSelect = document.querySelector('[name="brand_id"]');
  const seriesSelect = document.querySelector('[name="series_id"]');

  brandSelect.addEventListener('change', function () {
    const brandId = this.value;
    seriesSelect.innerHTML = '<option value="">--無--</option>';

    if (!brandId) return;

    fetch(`edit_basic_modal.php?brand_id_only=1&brand_id=${brandId}`)
      .then(res => res.json())
      .then(seriesList => {
        seriesList.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.name;
          seriesSelect.appendChild(opt);
        });
      });
  });
});
</script>

<?php include __DIR__.'/partials/footer.php'; ?>