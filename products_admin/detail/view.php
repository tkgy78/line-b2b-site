<?php
// products_v2/detail/view.php

require_once __DIR__ . '/../../db.php';
$pdo = connect();

// 1. 取得 URL 的 ?id=（商品編號）
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}
$productId = intval($_GET['id']);

// 2. 從 products 表抓出該筆商品
$sql = "SELECT *
        FROM products
        WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: ../index.php');
    exit;
}

// 3. 抓品牌名稱
$brandName = '';
if (!empty($product['brand_id'])) {
    $bstmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
    $bstmt->execute([$product['brand_id']]);
    $brandName = $bstmt->fetchColumn();
}

// 4. 抓分類名稱（若有 categories table）
$catName = '';
if (!empty($product['category_id'])) {
    $cstmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cstmt->execute([$product['category_id']]);
    $catName = $cstmt->fetchColumn();
}

// 5. 抓系列名稱（若有 series table）
$seriesName = '';
if (!empty($product['series_id'])) {
    $sstmt = $pdo->prepare("SELECT name FROM series WHERE id = ?");
    $sstmt->execute([$product['series_id']]);
    $seriesName = $sstmt->fetchColumn();
}

// 6. 抓這筆商品所有價格（僅顯示建議售價，MSRP）
$priceStmt = $pdo->prepare("SELECT price_type, price 
                            FROM prices 
                            WHERE product_id = :pid 
                            ORDER BY start_at DESC");
$priceStmt->execute([':pid' => $productId]);
$allPrices = $priceStmt->fetchAll(PDO::FETCH_ASSOC);

// 先預設只有 msrp，其他欄位不顯示
$msrp = '';
foreach ($allPrices as $r) {
    if ($r['price_type'] === 'msrp') {
        $msrp = number_format($r['price'], 2);
        break;
    }
}

// 7. 抓「商品多圖」資料（若你有 product_images table，下面範例示意）
//    product_images table 欄位結構：
//      id, product_id, img_path (varchar), created_at
//
$gallery = [];
try {
    $gstmt = $pdo->prepare("SELECT img_path 
                            FROM product_images 
                            WHERE product_id = :pid 
                            ORDER BY created_at ASC");
    $gstmt->execute([':pid' => $productId]);
    $gallery = $gstmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // 如果沒有 product_images table，這裡就會拋錯。catch 掉就行，
    // 前端只顯示主圖 (cover_img)。
    $gallery = [];
}

// 8. 下載區塊：假設 products 表有 spec_file、manual_file 欄位
$specFile   = $product['spec_file']   ?? '';   // 規格 PDF
$manualFile = $product['manual_file'] ?? '';   // 使用手冊 PDF

$page_title = $product['name'] ?? '商品詳情';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Bootstrap 5 CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <!-- Bootstrap Icons（選擇性，用於下載圖示等） -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
    rel="stylesheet"
  >

  <style>
    /* 自訂一些電商詳情頁常用樣式 */
    .product-title { font-size: 1.5rem; font-weight: 600; }
    .price-msrp { font-size: 1.4rem; color: #e60012; }
    .badge-stock-in { background-color: #28a745; }   /* 綠色：有庫存 */
    .badge-stock-out { background-color: #dc3545; }  /* 紅色：無庫存 */

    /* 圖片走馬燈高度限制 */
    .carousel-item img {
      object-fit: contain;
      max-height: 500px;
      width: 100%;
    }

    /* 商品說明區內文 */
    .product-desc { line-height: 1.6; }

    /* 規格＆手冊下載按鈕 */
    .download-btn i { margin-right: .3rem; }
  </style>
</head>
<body class="bg-light">

  <!-- 假設這是你前台的共用導覽列 -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
    <div class="container">
      <a class="navbar-brand" href="/">MyShop</a>
      <button class="navbar-toggler" type="button" 
              data-bs-toggle="collapse" 
              data-bs-target="#navbarNav"
              aria-controls="navbarNav" 
              aria-expanded="false" 
              aria-label="切換導覽">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="/">主頁</a></li>
          <li class="nav-item"><a class="nav-link" href="/products_v2/index.php">後台列表</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mb-5">
    <div class="row">
      <!-- 左側：商品圖片走馬燈 -->
      <div class="col-lg-6">
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner">
            <?php
            // 把主圖（cover_img）放在第一張
            $mainImg = $product['cover_img'] ?: 'uploads/product_files/no-image.png';
            ?>
            <div class="carousel-item active">
              <img src="/<?= htmlspecialchars($mainImg) ?>" class="d-block w-100" alt="主要圖片">
            </div>
            <?php foreach ($gallery as $idx => $imgPath): ?>
              <div class="carousel-item">
                <img src="/<?= htmlspecialchars($imgPath) ?>" class="d-block w-100" alt="相片<?= $idx+1 ?>">
              </div>
            <?php endforeach; ?>
          </div>
          <?php if (count($gallery) + 1 > 1): ?>
            <!-- 只有多於一張時才顯示上一頁/下一頁控制 -->
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">上一張</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">下一張</span>
            </button>
          <?php endif; ?>
        </div>

        <!-- 下面顯示小縮圖（點擊切換走馬燈） -->
        <?php if (count($gallery) + 1 > 1): ?>
          <div class="mt-3 d-flex justify-content-center flex-wrap gap-2">
            <!-- 主圖縮圖 -->
            <div class="border" style="width: 80px; height: 80px; overflow:hidden; cursor:pointer;"
                 data-bs-target="#productCarousel" data-bs-slide-to="0">
              <img src="/<?= htmlspecialchars($mainImg) ?>" style="object-fit:cover; width:100%; height:100%;">
            </div>
            <?php foreach ($gallery as $idx => $imgPath): ?>
              <div class="border" style="width: 80px; height: 80px; overflow:hidden; cursor:pointer;"
                   data-bs-target="#productCarousel" data-bs-slide-to="<?= $idx+1 ?>">
                <img src="/<?= htmlspecialchars($imgPath) ?>" style="object-fit:cover; width:100%; height:100%;">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- 右側：商品資訊 -->
      <div class="col-lg-6">
        <!-- 產品名稱 -->
        <h2 class="product-title mb-2"><?= htmlspecialchars($product['name']) ?></h2>

        <!-- 品牌／分類／系列（可選擇性顯示） -->
        <p class="text-muted mb-3">
          <?php if ($brandName): ?>
            <span class="me-2"><strong>品牌：</strong><?= htmlspecialchars($brandName) ?></span>
          <?php endif; ?>
          <?php if ($catName): ?>
            <span class="me-2"><strong>分類：</strong><?= htmlspecialchars($catName) ?></span>
          <?php endif; ?>
          <?php if ($seriesName): ?>
            <span><strong>系列：</strong><?= htmlspecialchars($seriesName) ?></span>
          <?php endif; ?>
        </p>

        <!-- 價格區塊 -->
        <div class="mb-3">
          <?php if ($msrp !== '' && $msrp !== '-'): ?>
            <span class="price-msrp me-3">NT$ <?= $msrp ?></span>
          <?php else: ?>
            <span class="text-danger">價格待更新</span>
          <?php endif; ?>
          <!-- 如果未來要加 VIP 價、VVIP 價，只要把下面解開即可：
          <?php if ($prices['vip'] !== ''): ?>
            <span class="text-secondary text-decoration-line-through me-2">
              NT$ <?= number_format($prices['msrp'],2) ?>
            </span>
            <span class="text-danger">NT$ <?= number_format($prices['vip'],2) ?></span>
          <?php endif; ?>
          -->
        </div>

        <!-- 簡要描述 -->
        <?php if (!empty($product['short_desc'])): ?>
          <p class="product-desc mb-4"><?= nl2br(htmlspecialchars($product['short_desc'])) ?></p>
        <?php endif; ?>

        <!-- 庫存狀態 -->
        <div class="mb-4">
          <?php if ($product['stock_quantity'] > 0): ?>
            <span class="badge badge-stock-in">有庫存 (<?= intval($product['stock_quantity']) ?> 件)</span>
          <?php else: ?>
            <span class="badge badge-stock-out">暫時缺貨</span>
          <?php endif; ?>
        </div>

        <!-- 加入購物車按鈕（示意）-->
        <div class="mb-4">
          <button class="btn btn-primary btn-lg">🛒 加入購物車</button>
        </div>

        <!-- 規格 & 下載 -->
        <div class="mb-5">
          <h5>規格與下載</h5>
          <ul class="list-group">
            <?php if (!empty($product['short_desc'])): ?>
              <li class="list-group-item"><strong>簡述：</strong> <?= htmlspecialchars(substr($product['short_desc'], 0, 50)) ?>…</li>
            <?php endif; ?>

            <?php if (!empty($specFile)): ?>
              <li class="list-group-item">
                <a class="download-btn" href="/<?= htmlspecialchars($specFile) ?>" download>
                  <i class="bi bi-file-earmark-pdf"></i> 下載規格書 (PDF)
                </a>
              </li>
            <?php endif; ?>

            <?php if (!empty($manualFile)): ?>
              <li class="list-group-item">
                <a class="download-btn" href="/<?= htmlspecialchars($manualFile) ?>" download>
                  <i class="bi bi-file-earmark-pdf"></i> 下載使用手冊 (PDF)
                </a>
              </li>
            <?php endif; ?>

            <?php if (empty($specFile) && empty($manualFile)): ?>
              <li class="list-group-item text-muted">目前無可下載之檔案</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- 下面可以放更詳細的文字說明、技術規格表（表格）、問與答等，也可以用 Tabs 或 Accordion -->
    <div class="row">
      <div class="col-12">
        <h4 class="mb-3">產品說明</h4>
        <div class="card mb-4">
          <div class="card-body product-desc">
            <?= nl2br(htmlspecialchars($product['detailed_desc'] ?? '這裡可以放更詳細的產品描述……')) ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 假設前台還有頁尾 -->
  <footer class="bg-white border-top text-center py-4 mt-5">
    © 2025 MyShop 電商平台
  </footer>

  <!-- Bootstrap 5 JS（含 Popper）-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>