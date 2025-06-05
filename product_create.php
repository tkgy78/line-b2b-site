<<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// product_create.php
require_once 'db.php';

// 取得品牌、分類、系列資料
$brands = $db->query("SELECT id, name FROM brands")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);
$series = $db->query("SELECT id, name FROM series")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>新增商品</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <h2>新增商品</h2>
    <form method="post" action="product_store.php" enctype="multipart/form-data">
        <div class="mb-2">
            <label>品牌</label>
            <select name="brand_id" class="form-select"><?php foreach ($brands as $b) echo "<option value='{$b['id']}'>{$b['name']}</option>"; ?></select>
        </div>
        <div class="mb-2">
            <label>分類</label>
            <select name="category_id" class="form-select"><?php foreach ($categories as $c) echo "<option value='{$c['id']}'>{$c['name']}</option>"; ?></select>
        </div>
        <div class="mb-2">
            <label>系列</label>
            <select name="series_id" class="form-select"><?php foreach ($series as $s) echo "<option value='{$s['id']}'>{$s['name']}</option>"; ?></select>
        </div>
        <div class="mb-2">
            <label>型號</label><input type="text" name="model" class="form-control">
        </div>
        <div class="mb-2">
            <label>功能簡述</label><textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-2">
            <label>狀態</label>
            <select name="status" class="form-select">
                <option value="active">上架中</option>
                <option value="inactive">暫停銷售</option>
                <option value="discontinued">已停售</option>
            </select>
        </div>
        <div class="mb-2">
            <label>庫存數量</label><input type="number" name="stock_quantity" class="form-control" value="0">
        </div>
        <div class="mb-2">
            <label>主圖</label><input type="file" name="main_image" class="form-control">
        </div>
        <div class="mb-2">
            <label>附圖（可多選）</label><input type="file" name="gallery_images[]" class="form-control" multiple>
        </div>
        <button type="submit" class="btn btn-primary">儲存商品</button>
    </form>
</body>
</html>