<?php
require_once __DIR__ . '/../../db.php';
$pdo = connect();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
  SELECT p.*, b.name AS brand_name, c.name AS cat_name, s.name AS series_name
    FROM products p
    LEFT JOIN brands b ON b.id = p.brand_id
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN series s ON s.id = p.series_id
   WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { http_response_code(404); echo 'Not found'; exit; }

$page_title = '商品詳情 - ' . $product['name'];
include __DIR__ . '/../_partials/header.php';

// 取得價格
$prices = $pdo->prepare("SELECT price_type,price,start_at FROM prices WHERE product_id=? ORDER BY price_type");
$prices->execute([$id]);
$priceData = $prices->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<h1><?= htmlspecialchars($product['name']) ?></h1>
<p>
  <strong>品牌：</strong><?= htmlspecialchars($product['brand_name']) ?><br>
  <strong>分類：</strong><?= htmlspecialchars($product['cat_name']) ?><br>
  <strong>系列：</strong><?= htmlspecialchars($product['series_name']) ?><br>
  <strong>SKU：</strong><?= htmlspecialchars($product['sku']) ?><br>
</p>

<?php if ($product['cover_img']): ?>
  <img src="../<?= $product['cover_img'] ?>" alt="cover" style="max-width:200px;">
<?php endif; ?>

<h5 class="mt-3">價格</h5>
<ul>
  <?php foreach ($priceData as $type=>$price): ?>
    <li><?= strtoupper($type) ?>：<?= number_format($price,2) ?></li>
  <?php endforeach; ?>
</ul>

<h5 class="mt-3">商品簡述</h5>
<div><?= nl2br($product['short_desc']) ?></div>

<a href="../index.php" class="btn btn-secondary mt-3">返回列表</a>
<?php include __DIR__ . '/../_partials/footer.php'; ?>