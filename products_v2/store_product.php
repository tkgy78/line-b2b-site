<?php
require_once __DIR__.'/../db.php';
$pdo = connect();

$brand_id    = intval($_POST['brand_id']);
$category_id = intval($_POST['category_id']);
$series_id   = ($_POST['series_id'] !== '') ? intval($_POST['series_id']) : null;
$name        = trim($_POST['name']);
$model       = $name;              // 自動同步
$sku         = trim($_POST['sku']);
$short_desc  = $_POST['short_desc'] ?? '';
$status      = isset($_POST['status']) ? 1 : 0;

/* -------- 封面圖 -------- */
$cover = 'uploads/product_images/no-image.png';
if (!empty($_FILES['cover_img']['name'])) {
    $ext  = strtolower(pathinfo($_FILES['cover_img']['name'], PATHINFO_EXTENSION));
    $file = uniqid('p_').'.'.$ext;
    $dest = __DIR__.'/../uploads/product_images/'.$file;
    if (move_uploaded_file($_FILES['cover_img']['tmp_name'], $dest)) {
        $cover = 'uploads/product_images/'.$file;
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
  'price_cost'      => 'cost'
];
$pstmt = $pdo->prepare("INSERT INTO prices (product_id, price_type, price, start_at)
                        VALUES (?,?,?,NOW())");
foreach ($map as $f => $t) {
    if (isset($_POST[$f]) && $_POST[$f] !== '') {
        $pstmt->execute([$product_id, $t, floatval($_POST[$f])]);
    }
}

header('Location: index.php?msg='.urlencode('新增成功'));
exit;