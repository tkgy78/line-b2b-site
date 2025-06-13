<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();
header('Content-Type: application/json');

if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode(['success' => false, 'message' => '請選擇要匯入的 CSV 檔案']);
  exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');
if (!$handle) {
  echo json_encode(['success' => false, 'message' => '無法讀取上傳的檔案']);
  exit;
}

function safe_utf8($text) {
  return mb_convert_encoding($text, 'UTF-8', 'UTF-8, BIG-5, ISO-8859-1');
}

$headers = fgetcsv($handle);
$rowCount = 0;
$inserted = 0;
$updated = 0;
$skipped = 0;
$priceChanges = 0;
$errors = [];

$insertedItems = [];
$updatedItems = [];
$priceChangeDetails = [];
$skippedRows = [];

while (($row = fgetcsv($handle)) !== false) {
  $rowCount++;
  $row = array_map('trim', array_map('safe_utf8', $row));

  [$brandName, $categoryName, $seriesName, $name, $sku,
   $short_desc, $unit, $barcode, $stock,
   $msrp, $vip, $vvip, $wholesale, $cost, $emma] = array_pad($row, 15, '');

  if ($sku === '') {
    $skipped++;
    $skippedRows[] = "【{$brandName} / {$name}】SKU 為空，略過";
    continue;
  }

  // 取得品牌
  $brandStmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
  $brandStmt->execute([$brandName]);
  $brand = $brandStmt->fetch();
  if (!$brand) {
    $errors[] = "【{$brandName} / {$name}】找不到品牌「{$brandName}」";
    $skipped++;
    continue;
  }
  $brand_id = $brand['id'];

  // 取得分類
  $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
  $catStmt->execute([$categoryName]);
  $cat = $catStmt->fetch();
  if (!$cat) {
    $errors[] = "【{$brandName} / {$name}】找不到分類「{$categoryName}」";
    $skipped++;
    continue;
  }
  $category_id = $cat['id'];

  // 取得系列（只依 brand_id）
  $series_id = null;
  if ($seriesName !== '') {
    $seriesStmt = $pdo->prepare("SELECT id FROM series WHERE name = ? AND brand_id = ?");
    $seriesStmt->execute([$seriesName, $brand_id]);
    $series = $seriesStmt->fetch();
    if (!$series) {
      $errors[] = "【{$brandName} / {$name}】找不到系列「{$seriesName}」";
      $skipped++;
      continue;
    }
    $series_id = $series['id'];
  }

  // 查找產品是否存在
  $productStmt = $pdo->prepare("SELECT * FROM products WHERE brand_id = ? AND name = ? AND sku = ?");
  $productStmt->execute([$brand_id, $name, $sku]);
  $existing = $productStmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    // 更新
    $updateStmt = $pdo->prepare("UPDATE products SET category_id=?, series_id=?, short_desc=?, unit=?, barcode=?, stock_quantity=?, updated_at=NOW() WHERE id=?");
    $updateStmt->execute([
      $category_id, $series_id, $short_desc, $unit, $barcode, $stock ?: 0, $existing['id']
    ]);
    $updated++;
    $updatedItems[] = "【{$brandName} / {$name}】更新商品基本資料";
    $product_id = $existing['id'];
  } else {
    // 新增
    $insertStmt = $pdo->prepare("INSERT INTO products (brand_id, category_id, series_id, name, sku, model, short_desc, stock_quantity, unit, barcode, cover_img, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'uploads/product_images/no-image.png', 'active', NOW(), NOW())");
    $insertStmt->execute([
      $brand_id, $category_id, $series_id,
      $name, $sku, $name, $short_desc,
      $stock ?: 0, $unit, $barcode
    ]);
    $product_id = $pdo->lastInsertId();
    $inserted++;
    $insertedItems[] = "【{$brandName} / {$name}】新增商品";
  }

  // 處理價格
  $priceMap = [
    'msrp' => $msrp,
    'vip' => $vip,
    'vvip' => $vvip,
    'wholesale' => $wholesale,
    'cost' => $cost,
    'emma' => $emma,
  ];

  foreach ($priceMap as $type => $val) {
    if ($val === '') continue;
    $val = floatval($val);

    $checkStmt = $pdo->prepare("SELECT id, price FROM prices WHERE product_id = ? AND price_type = ? AND is_latest = 1");
    $checkStmt->execute([$product_id, $type]);
    $old = $checkStmt->fetch();

    if ($old && floatval($old['price']) === $val) continue;

    if ($old) {
      $pdo->prepare("UPDATE prices SET is_latest = 0, end_at = NOW() WHERE id = ?")->execute([$old['id']]);
    }

    $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at, is_latest) VALUES (?, ?, ?, NOW(), 1)")
        ->execute([$product_id, $type, $val]);

    $priceChanges++;
    $oldPrice = $old ? $old['price'] : '無';
    $priceChangeDetails[] = "【{$brandName} / {$name}】{$type} 價格由 {$oldPrice} → {$val}";
  }
}

fclose($handle);

// 回傳 JSON 結果
echo json_encode([
  'success' => true,
  'message' => '商品匯入完成',
  'summary' => [
    'total_rows' => $rowCount,
    'inserted' => $inserted,
    'updated' => $updated,
    'price_changes' => $priceChanges,
    'skipped' => $skipped
  ],
  'inserted_items' => $insertedItems,
  'updated_items' => $updatedItems,
  'price_changes_detail' => $priceChangeDetails,
  'skipped_rows' => $skippedRows,
  'errors' => $errors
], JSON_UNESCAPED_UNICODE);