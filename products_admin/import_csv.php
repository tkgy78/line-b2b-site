<?php
// products_admin/import_csv.php
require_once __DIR__ . '/../db.php';
$pdo = connect();

header('Content-Type: application/json');

// 檢查是否有檔案
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

// 欄位順序固定：品牌, 類別, 系列, 名稱, SKU, 建議售價, VIP 價, VVIP 價, 批發價, 成本, EMMA 價, 庫存, 單位, 條碼, 簡述
$headers = fgetcsv($handle);
$requiredFields = ['品牌', '類別', '名稱'];

$rowCount = 0;
$errors = [];

while (($row = fgetcsv($handle)) !== false) {
  $rowCount++;
  $row = array_map('trim', $row);

  [$brandName, $categoryName, $seriesName, $name, $sku,
   $msrp, $vip, $vvip, $wholesale, $cost, $emma,
   $stock, $unit, $barcode, $short_desc] = array_pad($row, 15, '');

  // 品牌
  $brandStmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
  $brandStmt->execute([$brandName]);
  $brand = $brandStmt->fetch();
  if (!$brand) {
    $errors[] = "第 $rowCount 行：找不到品牌「$brandName」";
    continue;
  }

  // 類別
  $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
  $catStmt->execute([$categoryName]);
  $cat = $catStmt->fetch();
  if (!$cat) {
    $errors[] = "第 $rowCount 行：找不到類別「$categoryName」";
    continue;
  }

  // 系列（可為空）
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

  // 新增商品
  $insert = $pdo->prepare("INSERT INTO products
    (brand_id, category_id, series_id, name, sku, model, short_desc,
     stock_quantity, unit, barcode, cover_img, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'uploads/product_images/no-image.png', 'active', NOW(), NOW())");

  $insert->execute([
    $brand['id'], $cat['id'], $series_id,
    $name, $sku, $name, $short_desc,
    $stock ?: 0, $unit, $barcode
  ]);
  $product_id = $pdo->lastInsertId();

  // 建立價格
  $priceMap = [
    'msrp'      => $msrp,
    'vip'       => $vip,
    'vvip'      => $vvip,
    'wholesale' => $wholesale,
    'cost'      => $cost,
    'emma'      => $emma,
  ];
  $pstmt = $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at, is_latest)
                          VALUES (?, ?, ?, NOW(), 1)");
  foreach ($priceMap as $type => $val) {
    if ($val !== '') {
      $pstmt->execute([$product_id, $type, floatval($val)]);
    }
  }
}

fclose($handle);

if (count($errors)) {
  echo json_encode(['success' => false, 'message' => '部分資料匯入失敗', 'errors' => $errors]);
} else {
  echo json_encode(['success' => true, 'message' => '匯入成功']);
}