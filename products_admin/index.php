<?php
// products_admin/index.php

require_once __DIR__ . '/../db.php';  // 載入 db.php，請確保 connect() 函式正確
$pdo = connect();

// --------------------------------------------------
// 1. 取得所有品牌，用來產生篩選下拉清單
// --------------------------------------------------
$brandsStmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$allBrands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------------------------
// 2. 處理「品牌篩選」參數（由 GET 傳入）
//    如果有指定 brand_id，就把該品牌商品排序在最前面
// --------------------------------------------------
$filterBrandId = isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])
               ? intval($_GET['brand_id'])
               : null;

// --------------------------------------------------
// 3. 撈取商品 + 價格資料
//    LEFT JOIN prices，可得到各種 price_type
//    ORDER BY (brand_id = 篩選品牌) DESC, p.id DESC
// --------------------------------------------------
$sql = "
  SELECT 
    p.id,
    p.sku,
    p.name AS product_name,
    p.cover_img,
    p.brand_id,
    b.name AS brand_name,
    p.stock_quantity,
    p.status,
    pr.price_type,
    pr.price
  FROM products p
  JOIN brands b ON b.id = p.brand_id
  LEFT JOIN prices pr ON pr.product_id = p.id
  " . (
    $filterBrandId
    ? "ORDER BY (p.brand_id = {$filterBrandId}) DESC, p.id DESC"
    : "ORDER BY p.id DESC"
  );

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --------------------------------------------------
// 4. 將價格拆分到 $products 陣列，sample keys: msrp, vip, vvip, wholesale, cost
// --------------------------------------------------
$products = [];
foreach ($rows as $r) {
    $id = $r['id'];
    if (!isset($products[$id])) {
        $products[$id] = [
            'id'         => $r['id'],
            'sku'        => $r['sku'],
            'brand_id'   => $r['brand_id'],
            'brand_name' => $r['brand_name'],
            'name'       => $r['product_name'],
            'cover_img'  => $r['cover_img'],          // 圖片路徑
            'stock'      => $r['stock_quantity'] ?? 0,
            'status'     => $r['status'],             // 1 或 0
            // 預設都先填 "-"
            'msrp'       => '-', 
            'vip'        => '-', 
            'vvip'       => '-', 
            'wholesale'  => '-', 
            'cost'       => '-',
        ];
    }
    // 如果這筆有 price_type，就填對應欄
    if (!empty($r['price_type'])) {
        $ptype = $r['price_type'];
        $products[$id][$ptype] = number_format($r['price'], 2);
    }
}

// --------------------------------------------------
// 5. 頁面標題 / 載入 header
//    你需要有 products_v2/partials/header.php 跟 footer.php
//    header.php 要載入 Bootstrap CSS & JS
// --------------------------------------------------
$page_title = '商品列表';
include __DIR__ . '/partials/header.php';
?>

<div class="container py-3">
  <div class="row mb-3 align-items-center">
    <div class="col-6 col-md-4">
      <h1 class="h3 mb-0">商品列表</h1>
    </div>
    <div class="col-6 col-md-4 text-end d-none d-md-block">
      <!-- 桌機版顯示「新增商品」按鈕，手機版隱藏 -->
      <a href="create.php" class="btn btn-success">
        <i class="bi bi-plus-lg"></i> 新增商品
      </a>
    </div>
    <div class="col-12 col-md-4 mt-2 mt-md-0 text-md-end">
      <!-- 品牌篩選下拉選單 -->
      <form class="d-inline" method="get" action="index.php">
        <select name="brand_id" class="form-select form-select-sm d-inline w-auto"
                onchange="this.form.submit()">
          <option value="">— 全部品牌 —</option>
          <?php foreach ($allBrands as $b): ?>
            <option value="<?= $b['id'] ?>"
              <?= ($filterBrandId !== null && $filterBrandId == $b['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <!-- ☆ 手機用 Modal：顯示完整資料 & 編輯／詳情按鈕 -->
  <div class="modal fade" id="moreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">商品詳細</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- JS 動態塞資料 -->
        </div>
        <div class="modal-footer">
          <!-- JS 動態塞按鈕 -->
        </div>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th class="d-none d-md-table-cell">SKU</th>
          <th class="d-none d-md-table-cell" style="width: 80px;">產品圖</th><!-- 手機隱藏 -->
          <th>品牌</th>
          <th class="tbl-name">名稱／型號</th>
          <th class="tbl-price">建議售價</th>
          <th class="d-none d-md-table-cell">VIP 價</th>
          <th class="d-none d-md-table-cell">VVIP 價</th>
          <th class="d-none d-md-table-cell">批發價</th>
          <th class="d-none d-md-table-cell">成本</th>
          <th class="d-none d-md-table-cell">狀態</th>
          <th>庫存</th>
          <th style="width: 150px;">動作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <!-- SKU（桌機）-->
            <td class="d-none d-md-table-cell"><?= htmlspecialchars($p['sku']) ?></td>

            <!-- 產品圖（桌機）-->
            <td class="d-none d-md-table-cell">
              <img src="/<?= htmlspecialchars($p['cover_img']) ?>"
                   class="img-fluid img-thumb"
                   style="max-width: 60px; height: auto;">
            </td>

            <!-- 品牌 -->
            <td><?= htmlspecialchars($p['brand_name']) ?></td>

            <!-- 名稱／型號 -->
            <td><?= htmlspecialchars($p['name']) ?></td>

            <!-- 建議售價 -->
            <td><?= $p['msrp'] ?></td>

            <!-- VIP 價（桌機）-->
            <td class="d-none d-md-table-cell"><?= $p['vip'] ?></td>

            <!-- VVIP 價（桌機）-->
            <td class="d-none d-md-table-cell"><?= $p['vvip'] ?></td>

            <!-- 批發價（桌機）-->
            <td class="d-none d-md-table-cell"><?= $p['wholesale'] ?></td>

            <!-- 成本（桌機）-->
            <td class="d-none d-md-table-cell"><?= $p['cost'] ?></td>

            <!-- 狀態（桌機）-->
            <td class="d-none d-md-table-cell">
              <?= $p['status'] ? '<span class="badge bg-success">上架</span>'
                              : '<span class="badge bg-secondary">下架</span>' ?>
            </td>

            <!-- 庫存 -->
            <td><?= $p['stock'] ?></td>

            <!-- 動作 -->
            <td class="text-nowrap">
              <!-- 桌機版：編輯／詳情／刪除 -->
              <div class="d-none d-md-inline">
                <a href="javascript:void(0);"
                  class="btn btn-sm btn-warning btn-edit-modal"
                  data-id="<?= $p['id'] ?>">
                  編輯
                </a>
                <a href="edit_detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">詳情</a>
                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確認要刪除這項商品嗎？')">刪除</a>
              </div>

              <!-- 手機版：更多按鈕 -->
              <button class="btn btn-sm btn-secondary d-md-none"
                      data-bs-toggle="modal"
                      data-bs-target="#moreModal"
                      data-product='<?= json_encode($p, JSON_HEX_TAG|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>'>
                更多
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ☆ JS：把點「更多」後的資料塞進 Modal Body ＆ Footer -->
<script>
  const moreModal = document.getElementById('moreModal');
  moreModal.addEventListener('show.bs.modal', e => {
    const data = JSON.parse(e.relatedTarget.dataset.product);
    const body = moreModal.querySelector('.modal-body');
    const foot = moreModal.querySelector('.modal-footer');
    body.innerHTML = `
      <ul class="list-group">
        <li class="list-group-item"><strong>SKU：</strong>${data.sku}</li>
        <li class="list-group-item"><strong>品牌：</strong>${data.brand_name}</li>
        <li class="list-group-item"><strong>建議售價：</strong>${data.msrp}</li>
        <li class="list-group-item"><strong>VIP：</strong>${data.vip}</li>
        <li class="list-group-item"><strong>VVIP：</strong>${data.vvip}</li>
        <li class="list-group-item"><strong>批發價：</strong>${data.wholesale}</li>
        <li class="list-group-item"><strong>成本：</strong>${data.cost}</li>
        <li class="list-group-item"><strong>狀態：</strong>${data.status ? '上架' : '下架'}</li>
        <li class="list-group-item"><strong>庫存：</strong>${data.stock}</li>
      </ul>`;
    foot.innerHTML = `
      <a href="javascript:void(0);" 
        class="btn btn-sm btn-warning btn-edit-basic" 
        data-id="<?= $p['id'] ?>">
        編輯
      </a>
      <a href="detail/view.php?id=${data.id}" class="btn btn-info">詳情</a>
      <button class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>`;
  });
</script>

<?php
// --------------------------------------------------
// 載入共用 footer（如果有必要）
// --------------------------------------------------
include __DIR__ . '/partials/footer.php';