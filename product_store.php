<?php
// product_store.php
require_once 'db.php';

$brand_id = $_POST['brand_id'];
$category_id = $_POST['category_id'];
$series_id = $_POST['series_id'];
$model = $_POST['model'];
$description = $_POST['description'];
$status = $_POST['status'];
$stock_quantity = $_POST['stock_quantity'];

// 插入商品資料
$stmt = $db->prepare("INSERT INTO products (brand_id, category_id, series_id, model_number, description, status, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiisssi", $brand_id, $category_id, $series_id, $model, $description, $status, $stock_quantity);
$stmt->execute();
$product_id = $stmt->insert_id;

// 上傳主圖
if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
    $ext = pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION);
    $main_image = "uploads/main_" . time() . ".$ext";
    move_uploaded_file($_FILES['main_image']['tmp_name'], $main_image);

    $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
    $stmt->bind_param("is", $product_id, $main_image);
    $stmt->execute();
}

// 上傳附圖
if (!empty($_FILES['gallery_images']['name'][0])) {
    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp) {
        if ($_FILES['gallery_images']['error'][$key] == 0) {
            $ext = pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION);
            $filename = "uploads/gallery_" . time() . "_$key.$ext";
            move_uploaded_file($tmp, $filename);
            $stmt = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 0)");
            $stmt->bind_param("is", $product_id, $filename);
            $stmt->execute();
        }
    }
}

header("Location: product_list.php");
exit;
?>
