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

$headers = fgetcsv($handle);
$rowCount = 0;
$errors = [];

while (($row = fgetcsv($handle)) !== false) {
  $rowCount++;
  $row = array_map('trim', $row);

  [$brandName, $categoryName, $seriesName, $name, $sku,
   $short_desc, $unit, $barcode, $stock,
   $msrp, $vip, $vvip, $wholesale, $cost, $emma] = array_pad($row, 15, '');

  // 品牌
  $brandStmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
  $brandStmt->execute([$brandName]);
  $brand = $brandStmt->fetch();
  if (!$brand) {
    $errors[] = "第 $rowCount 行：找不到品牌「$brandName」";
    continue;
  }
  $brand_id = $brand['id'];

  // 分類
  $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
  $catStmt->execute([$categoryName]);
  $cat = $catStmt->fetch();
  if (!$cat) {
    $errors[] = "第 $rowCount 行：找不到分類「$categoryName」";
    continue;
  }
  $category_id = $cat['id'];

  // 系列（可空）
  $series_id = null;
  if ($seriesName !== '') {
    $seriesStmt = $pdo->prepare("SELECT id FROM series WHERE name = ?");
    $seriesStmt->execute([$seriesName]);
    $series = $seriesStmt->fetch();
    if (!$series) {
      $errors[] = "第 $rowCount 行：找不到系列「$seriesName」";
      continue;
    }
    $series_id = $series['id'];
  }

  // 檢查是否已存在商品（用品牌、名稱、SKU 判斷）
  $productStmt = $pdo->prepare("SELECT * FROM products WHERE brand_id = ? AND name = ? AND sku = ?");
  $productStmt->execute([$brand_id, $name, $sku]);
  $existing = $productStmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    // 更新商品（如資料有異動）
    $updateStmt = $pdo->prepare("UPDATE products SET category_id=?, series_id=?, short_desc=?, unit=?, barcode=?, stock_quantity=?, updated_at=NOW() WHERE id=?");
    $updateStmt->execute([
      $category_id, $series_id, $short_desc, $unit, $barcode, $stock ?: 0, $existing['id']
    ]);
    $product_id = $existing['id'];
  } else {
    // 新增商品
    $insert = $pdo->prepare("INSERT INTO products
      (brand_id, category_id, series_id, name, sku, model, short_desc,
       stock_quantity, unit, barcode, cover_img, status, created_at, updated_at)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'uploads/product_images/no-image.png', 'active', NOW(), NOW())");
    $insert->execute([
      $brand_id, $category_id, $series_id,
      $name, $sku, $name, $short_desc,
      $stock ?: 0, $unit, $barcode
    ]);
    $product_id = $pdo->lastInsertId();
  }

  // 價格比對與新增歷史
  $priceMap = [
    'msrp'      => $msrp,
    'vip'       => $vip,
    'vvip'      => $vvip,
    'wholesale' => $wholesale,
    'cost'      => $cost,
    'emma'      => $emma,
  ];
  foreach ($priceMap as $type => $val) {
    if ($val === '') continue;
    $val = floatval($val);

    // 更新原 is_latest = 0
    $pdo->prepare("UPDATE prices SET is_latest = 0, end_at = NOW() WHERE product_id = ? AND price_type = ? AND is_latest = 1")
        ->execute([$product_id, $type]);

    // 新增新價格
    $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at, is_latest)
                   VALUES (?, ?, ?, NOW(), 1)")
        ->execute([$product_id, $type, $val]);
  }
}

fclose($handle);

if (count($errors)) {
  echo json_encode(['success' => false, 'message' => '部分資料匯入失敗', 'errors' => $errors]);
} else {
  echo json_encode(['success' => true, 'message' => '匯入成功']);
}