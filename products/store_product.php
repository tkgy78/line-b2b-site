<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 1. 基本欄位
$brand_id    = $_POST['brand_id'] ?? 0;
$category_id = $_POST['category_id'] ?? 0;
$series_id   = $_POST['series_id'] ?? 0;
$name        = $_POST['name'] ?? '';
$sku         = $_POST['sku'] ?? '';
$short_desc  = $_POST['short_desc'] ?? '';
$status      = isset($_POST['status']) ? 1 : 0;

// 2. 上傳封面圖
$coverPath = 'uploads/product_files/no-image.png';  // 預設
if (!empty($_FILES['cover_img']['name'])) {
    $fn   = uniqid('p_') . '.' . pathinfo($_FILES['cover_img']['name'], PATHINFO_EXTENSION);
    $dest = __DIR__ . '/../uploads/product_files/' . $fn;
    if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $dest)) {
        $coverPath = 'uploads/product_files/' . $fn;
    }
}

// 3. 寫入 products
$sql = "INSERT INTO products
        (brand_id, category_id, series_id, name, sku, short_desc, cover_img, status, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([$brand_id,$category_id,$series_id,$name,$sku,$short_desc,$coverPath,$status]);
$productId = $pdo->lastInsertId();

// 4. 價格 (如果有輸入才寫)
$price_fields = [
  'price_msrp'      => 'msrp',
  'price_vip'       => 'vip',
  'price_vvip'      => 'vvip',
  'price_wholesale' => 'wholesale',
  'price_cost'      => 'cost'
];
$priceSql = "INSERT INTO prices (product_id, price_type, price, start_at) VALUES (?,?,?,NOW())";
$pstmt = $pdo->prepare($priceSql);
foreach ($price_fields as $field => $type) {
    if (isset($_POST[$field]) && $_POST[$field] !== '') {
        $pstmt->execute([$productId, $type, $_POST[$field]]);
    }
}
header("Location: index.php?msg=" . urlencode('新增成功'));
exit;