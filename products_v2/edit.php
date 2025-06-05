<?php
// products_v2/edit.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

// 1. 先取得 URL 的 ?id=，如果沒有就直接導回列表
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: index.php');
  exit;
}
$productId = intval($_GET['id']);

// 2. 撈取該商品的所有欄位
$sql = "SELECT * FROM products WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
  // 找不到這筆商品，直接跳回列表
  header('Location: index.php');
  exit;
}

// 3. 撈取該商品的所有價格（prices table），
$sql2 = "SELECT price_type, price 
         FROM prices 
         WHERE product_id = :pid 
         ORDER BY start_at DESC";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute([':pid' => $productId]);
$priceRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 4. 把價格塞到一個 associative array，方便在表單中回填
$prices = [
  'msrp'      => '',
  'vip'       => '',
  'vvip'      => '',
  'wholesale' => '',
  'cost'      => '',
];
foreach ($priceRows as $r) {
  $type = $r['price_type'];
  $prices[$type] = $r['price'];
}

// 5. 取得所有品牌（讓下拉選單顯示）
$brandsStmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$allBrands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);

// 6. 取得所有分類（categories）
//    如果你有 category 功能，也要拉出來；
//    如果沒有，可以把下面註解、或取消 dropdown。
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$allCategories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// 7. 取得所有系列（series）
//    如果你有 series 功能，也拉出來；若有依品牌過濾，這邊可只拉同品牌系列。
//    為簡化範例，這邊先拉全部：
$seriesStmt = $pdo->query("SELECT id, name, brand_id FROM series ORDER BY name ASC");
$allSeries = $seriesStmt->fetchAll(PDO::FETCH_ASSOC);

// 8. 載入 header
$page_title = '編輯商品';
include __DIR__ . '/partials/header.php';
?>

<div class="container py-3">

  <h1 class="h3 mb-3">編輯商品：#<?= htmlspecialchars($product['id']) ?></h1>

  <!-- 編輯表單，送到 update.php -->
  <form action="update.php" method="post" enctype="multipart/form-data">
    <!-- 一定要帶 product id -->
    <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">

    <div class="row mb-3">
      <!-- 左半：品牌、分類、系列、SKU -->
      <div class="col-md-6">

        <!-- 品牌 -->
        <div class="mb-3">
          <label class="form-label">品牌</label>
          <select name="brand_id" class="form-select" required>
            <option value="">— 請選擇品牌 —</option>
            <?php foreach ($allBrands as $b): ?>
              <option value="<?= $b['id'] ?>"
                <?= ($product['brand_id'] == $b['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 分類 -->
        <div class="mb-3">
          <label class="form-label">分類</label>
          <select name="category_id" class="form-select">
            <option value="">— 請選擇分類 —</option>
            <?php foreach ($allCategories as $c): ?>
              <option value="<?= $c['id'] ?>"
                <?= ($product['category_id'] == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- 系列 -->
        <div class="mb-3">
          <label class="form-label">系列</label>
          <select name="series_id" class="form-select">
            <option value="">— 請選擇系列 —</option>
            <?php foreach ($allSeries as $s): ?>
              <option value="<?= $s['id'] ?>"
                <?= ($product['series_id'] == $s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- SKU -->
        <div class="mb-3">
          <label class="form-label">SKU（編號）</label>
          <input type="text" name="sku"
                 class="form-control"
                 value="<?= htmlspecialchars($product['sku']) ?>" required>
        </div>
      </div>

      <!-- 右半：型號（名稱）、上傳封面圖、庫存、狀態 -->
      <div class="col-md-6">
        <!-- 名稱 / 型號 -->
        <div class="mb-3">
          <label class="form-label">名稱 / 型號</label>
          <input type="text" name="name"
                 class="form-control"
                 value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <!-- 目前封面圖預覽 -->
        <div class="mb-3">
          <label class="form-label">目前產品圖</label>
          <div>
            <img src="/<?= htmlspecialchars($product['cover_img']) ?>"
                 onerror="this.src='/uploads/product_files/no-image.png';"
                 class="img-fluid"
                 style="max-width: 120px; height: auto; border:1px solid #ddd;">
          </div>
        </div>

        <!-- 上傳新封面圖 -->
        <div class="mb-3">
          <label class="form-label">重新選擇封面圖（選擇後會覆蓋）</label>
          <input type="file" name="cover_img" class="form-control form-control-sm">
        </div>

        <!-- 庫存 -->
        <div class="mb-3">
          <label class="form-label">庫存數量</label>
          <input type="number" name="stock_quantity"
                 class="form-control"
                 value="<?= htmlspecialchars($product['stock_quantity']) ?>">
        </div>

        <!-- 狀態：上架 / 下架 -->
        <div class="form-check form-switch mb-3">
            <?php
            $checked = ($product['status']=== 'active') ? 'checked' : '';
            ?>
          <input class="form-check-input" type="checkbox" name="status"
                 id="statusSwitch" value="active"
                 <?= ($product['status'] == "active") ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusSwitch">上架</label>
        </div>
      </div>
    </div>

    <!-- -------------------------------------------------------------------
         價格區塊：把前面 fetch 到的 prices 塞回去
         ------------------------------------------------------------------- -->
    <h5 class="mt-4 mb-3">價格設定</h5>
    <div class="row">
      <!-- 建議售價 -->
      <div class="col-md-2 mb-3">
        <label class="form-label">建議售價 (MSRP)</label>
        <input type="number" step="0.01" name="price_msrp"
               class="form-control"
               value="<?= htmlspecialchars($prices['msrp']) ?>">
      </div>
      <!-- VIP 價 -->
      <div class="col-md-2 mb-3">
        <label class="form-label">VIP 價</label>
        <input type="number" step="0.01" name="price_vip"
               class="form-control"
               value="<?= htmlspecialchars($prices['vip']) ?>">
      </div>
      <!-- VVIP 價 -->
      <div class="col-md-2 mb-3">
        <label class="form-label">VVIP 價</label>
        <input type="number" step="0.01" name="price_vvip"
               class="form-control"
               value="<?= htmlspecialchars($prices['vvip']) ?>">
      </div>
      <!-- 批發價 -->
      <div class="col-md-2 mb-3">
        <label class="form-label">批發價</label>
        <input type="number" step="0.01" name="price_wholesale"
               class="form-control"
               value="<?= htmlspecialchars($prices['wholesale']) ?>">
      </div>
      <!-- 成本 -->
      <div class="col-md-2 mb-3">
        <label class="form-label">成本價</label>
        <input type="number" step="0.01" name="price_cost"
               class="form-control"
               value="<?= htmlspecialchars($prices['cost']) ?>">
      </div>
    </div>

    <!-- -------------------------------------------------------------------
         商品簡述（textarea，可換行）
         ------------------------------------------------------------------- -->
    <div class="mb-4">
      <label class="form-label">商品簡述</label>
      <textarea name="short_desc" rows="4" class="form-control"><?= 
        htmlspecialchars($product['short_desc'] ?? '') 
      ?></textarea>
        <div class="mb-4">
        <label class="form-label" for="description">商品詳細說明</label>
        <textarea name="description" id="description" rows="10" class="form-control"><?= htmlspecialchars($product['description']) ?? '' ?></textarea>
        </div>    
    </div>

    <!-- 最後的送出按鈕 -->
    <button type="submit" class="btn btn-primary">儲存變更</button>
    <a href="index.php" class="btn btn-secondary ms-2">取消</a>
  </form>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>