<?php
require_once __DIR__ . '/../../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
if (!$id || !is_numeric($id)) {
  echo "<div class='text-danger p-3'>無效的商品 ID</div>";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<div class='text-danger p-3'>找不到此商品</div>";
  exit;
}

function getName($pdo, $table, $id) {
  $stmt = $pdo->prepare("SELECT name FROM {$table} WHERE id = ?");
  $stmt->execute([$id]);
  return $stmt->fetchColumn();
}

$brandName  = getName($pdo, 'brands', $product['brand_id']);
$catName    = getName($pdo, 'categories', $product['category_id']);
$seriesName = getName($pdo, 'series', $product['series_id']);

$priceStmt = $pdo->prepare("SELECT price_type, price FROM prices WHERE product_id = ?");
$priceStmt->execute([$id]);
$prices = [];
foreach ($priceStmt->fetchAll() as $row) {
  $prices[$row['price_type']] = number_format($row['price'], 2);
}

$mainImage = $product['cover_img'] ?? '';
$gallery = [];
try {
  // 修正欄位名稱 image_url
  $imgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
  $imgStmt->execute([$id]);
  $gallery = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
  $gallery = [];
}

// 撈產品說明
$detailStmt = $pdo->prepare("SELECT detail_html FROM product_details WHERE product_id = ?");
$detailStmt->execute([$id]);
$detailHtml = $detailStmt->fetchColumn() ?? '';

// 撈綁定軟體
$softwares = $pdo->prepare("
  SELECT s.name AS software_name, v.version, v.file_path, v.changelog
  FROM product_software ps
  JOIN softwares s ON ps.software_id = s.id
  JOIN software_versions v ON v.software_id = s.id
  WHERE ps.product_id = ? AND v.is_latest = 1
  ORDER BY s.name
");
$softwares->execute([$id]);
$softwareRows = $softwares->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($product['name']) ?> - 商品詳情</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .product-card { background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    .product-title { font-size: 1.75rem; font-weight: 600; }
    .product-brand { font-size: 1.2rem; color: #333; font-weight: 500; }
    .product-category { font-size: 0.95rem; color: #777; }
    .price { font-size: 1.5rem; color: #d9230f; font-weight: bold; }
    .sub-price { font-size: 1rem; color: #333; }
    .thumb { width: 80px; height: 80px; object-fit: cover; cursor: pointer; border: 1px solid #ccc; border-radius: 5px; }
    .badge-stock { font-size: 0.9rem; }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
        <?php
          $mainImagePath = !empty($mainImage) ? htmlspecialchars($mainImage) : 'uploads/product_files/no-image.png';
        ?>
        <img src="/line_b2b/<?= $mainImagePath ?>" class="img-fluid mb-3" alt="主圖">
        <div class="d-flex flex-wrap gap-2">
        <?php foreach ($gallery as $img): ?>
          <img src="/line_b2b/<?= htmlspecialchars($img) ?>" class="thumb" alt="圖">
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="product-card p-4">
        <div class="product-brand mb-1"><?= htmlspecialchars($brandName) ?></div>
        <div class="product-title mb-2"><?= htmlspecialchars($product['name']) ?></div>
        <div class="product-category mb-3"><?= htmlspecialchars($catName) ?><?= $seriesName ? ' - ' . htmlspecialchars($seriesName) : '' ?></div>

        <div class="price mb-2">建議售價：NT$ <?= $prices['msrp'] ?? '－' ?></div>
        <?php if (!empty($prices['vip'])): ?>
          <div class="sub-price">VIP 價：NT$ <?= $prices['vip'] ?></div>
        <?php endif; ?>
        <?php if (!empty($prices['vvip'])): ?>
          <div class="sub-price">VVIP 價：NT$ <?= $prices['vvip'] ?></div>
        <?php endif; ?>

        <div class="mt-4">
          <p class="mb-1"><strong>庫存：</strong>
            <?php if ((int)$product['stock_quantity'] > 0): ?>
              <span class="badge bg-success badge-stock">有庫存</span>
            <?php else: ?>
              <span class="badge bg-secondary badge-stock">暫無庫存</span>
            <?php endif; ?>
          </p>
          <?php if (!empty($product['short_desc'])): ?>
            <p class="mt-3"><?= nl2br(htmlspecialchars($product['short_desc'])) ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($detailHtml)): ?>
    <div class="row mt-5">
      <div class="col">
        <div class="card">
          <div class="card-header bg-dark text-white">產品說明</div>
          <div class="card-body"><?= $detailHtml ?></div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php if (count($softwareRows) > 0): ?>
    <div class="row mt-5">
      <div class="col">
        <div class="card">
          <div class="card-header bg-primary text-white">軟體下載</div>
          <div class="card-body">
            <ul class="list-group">
              <?php foreach ($softwareRows as $s): ?>
                <li class="list-group-item">
                  <strong><?= htmlspecialchars($s['software_name']) ?> - <?= htmlspecialchars($s['version']) ?></strong>
                  <?php if (!empty($s['changelog'])): ?>
                    <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($s['changelog'])) ?></p>
                  <?php endif; ?>
                  <a href="/<?= htmlspecialchars($s['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>下載</a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>