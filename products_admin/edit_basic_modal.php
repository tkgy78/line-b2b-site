<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../db.php';
$pdo = connect();

// ✅ AJAX 請求系列清單
if (isset($_GET['brand_id_only']) && $_GET['brand_id_only'] == '1') {
  $brand_id = $_GET['brand_id'] ?? 0;
  $stmt = $pdo->prepare("SELECT id, name FROM series WHERE brand_id = ? ORDER BY display_order ASC, name");
  $stmt->execute([$brand_id]);
  echo json_encode($stmt->fetchAll());
  exit;
}

// ✅ 取得商品
$id = $_GET['id'] ?? 0;
if (!$id) {
  echo "<div class='text-danger'>未提供商品 ID</div>";
  exit;
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<div class='text-danger'>查無此商品</div>";
  exit;
}

// ✅ 價格陣列
$stmt = $pdo->prepare("SELECT price_type, price FROM prices WHERE product_id = ? AND is_latest = 1");
$stmt->execute([$id]);
$raw_prices = $stmt->fetchAll();
$prices = [];
foreach ($raw_prices as $p) {
  $prices[$p['price_type']] = $p['price'];
}

// ✅ 下拉選單
$brands     = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
?>

<form id="form-basic" class="p-3" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $product['id'] ?>">

  <!-- 品牌 / 分類 / 系列 -->
  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">品牌</label>
      <select name="brand_id" class="form-select" required>
        <option value="">--選擇--</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['id'] ?>" <?= $b['id'] == $product['brand_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['name']) ?>
          </option>
        <?php endforeach;?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">分類</label>
      <select name="category_id" class="form-select" required>
        <option value="">--選擇--</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach;?>
      </select>
    </div>

    <div class="col-md-4">
      <label class="form-label">系列</label>
      <select name="series_id" class="form-select">
        <option value="">--無--</option>
        <?php
        if (!empty($product['brand_id'])) {
          $stmt = $pdo->prepare("SELECT id, name FROM series WHERE brand_id = ? ORDER BY display_order ASC, name");
          $stmt->execute([$product['brand_id']]);
          $seriesList = $stmt->fetchAll();
          foreach ($seriesList as $s) {
            $selected = ($s['id'] == $product['series_id']) ? 'selected' : '';
            echo "<option value=\"{$s['id']}\" $selected>" . htmlspecialchars($s['name']) . "</option>";
          }
        }
        ?>
      </select>
    </div>
  </div>

  <!-- 名稱 / SKU -->
  <div class="row mb-3">
    <div class="col-md-8">
      <label class="form-label">商品名稱 (型號)</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label">SKU (編號)</label>
      <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>">
    </div>
  </div>

  <!-- 封面圖 -->
  <div class="mb-3">
    <label class="form-label">封面圖（若要更換）</label>
    <input type="file" name="cover_img" class="form-control">
    <?php if (!empty($product['cover_img'])): ?>
      <div class="mt-2">
        <img src="/<?= htmlspecialchars($product['cover_img']) ?>" class="img-thumbnail" style="max-width: 150px;">
      </div>
    <?php endif; ?>
  </div>

  <!-- 價格欄位：6 欄（含 EMMA） -->
  <div class="row mb-3">
    <div class="col-6 col-lg-2"><label class="form-label">建議售</label><input name="price_msrp" class="form-control" value="<?= $prices['msrp'] ?? '' ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label">VIP</label><input name="price_vip" class="form-control" value="<?= $prices['vip'] ?? '' ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label">VVIP</label><input name="price_vvip" class="form-control" value="<?= $prices['vvip'] ?? '' ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label">批發</label><input name="price_wholesale" class="form-control" value="<?= $prices['wholesale'] ?? '' ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label">成本</label><input name="price_cost" class="form-control" value="<?= $prices['cost'] ?? '' ?>"></div>
    <div class="col-6 col-lg-2"><label class="form-label">EMMA 價</label><input name="price_emma" class="form-control" value="<?= $prices['emma'] ?? '' ?>"></div>
  </div>

  <!-- 商品簡述 / 庫存 / 狀態 -->
  <div class="row mb-3">
    <div class="col-md-9">
      <label class="form-label">商品簡述</label>
      <textarea name="short_desc" rows="3" class="form-control"><?= htmlspecialchars($product['short_desc']) ?></textarea>
    </div>
    <div class="col-md-3">
      <label class="form-label">庫存數量</label>
      <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity'] ?>">

      <div class="form-check mt-2">
        <input type="hidden" name="status" value="inactive">
        <input class="form-check-input" type="checkbox" name="status" value="active" id="st"
          <?= $product['status'] === 'active' ? 'checked' : '' ?>>
        <label class="form-check-label" for="st">上架</label>
      </div>
    </div>
  </div>

  <div class="text-end">
    <button type="button" class="btn btn-success" id="btn-save-basic">儲存變更</button>
  </div>
</form>