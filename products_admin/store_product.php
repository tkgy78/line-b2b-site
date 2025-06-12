<?php
require_once __DIR__.'/../db.php';
$pdo = connect();

$brand_id    = intval($_POST['brand_id']);
$category_id = intval($_POST['category_id']);
$series_id   = ($_POST['series_id'] !== '') ? intval($_POST['series_id']) : null;
$name        = trim($_POST['name']);
$model       = $name;
$sku         = trim($_POST['sku']);
$short_desc  = $_POST['short_desc'] ?? '';
$status      = isset($_POST['status']) ? 'active' : 'inactive';

/* -------- 封面圖處理 -------- */
$cover = 'uploads/product_images/no-image.png'; // 預設圖片
if (!empty($_FILES['cover_img']['name'])) {
    $ext  = strtolower(pathinfo($_FILES['cover_img']['name'], PATHINFO_EXTENSION));
    $file = uniqid('cover_').'.'.$ext;
    $destFolder = __DIR__.'/../uploads/products_cover/';
    if (!is_dir($destFolder)) mkdir($destFolder, 0777, true); // 若資料夾不存在則建立
    $dest = $destFolder . $file;

    if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $dest)) {
        $cover = 'uploads/products_cover/'.$file;
    }
}

/* -------- 寫入 products -------- */
$sql = "INSERT INTO products
        (brand_id, category_id, series_id, name, sku, model,
         short_desc, cover_img, status, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())";
$pdo->prepare($sql)->execute([
    $brand_id, $category_id, $series_id,
    $name, $sku, $model,
    $short_desc, $cover, $status
]);

$product_id = $pdo->lastInsertId();

/* -------- 寫入價格 -------- */
$map = [
  'price_msrp'      => 'msrp',
  'price_vip'       => 'vip',
  'price_vvip'      => 'vvip',
  'price_wholesale' => 'wholesale',
  'price_cost'      => 'cost',
  'price_emma'      => 'emma'  // ✅ 加入 EMMA 價格支援
];

$pstmt = $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at)
                        VALUES (?,?,?,NOW())");

foreach ($map as $formField => $type) {
    if (isset($_POST[$formField]) && $_POST[$formField] !== '') {
        $pstmt->execute([$product_id, $type, floatval($_POST[$formField])]);
    }
}

header('Location: index.php?msg='.urlencode('新增成功'));
exit;