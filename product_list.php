<?php
// product_list.php
require_once 'db.php';

$products = $db->query("SELECT p.*, b.name AS brand, c.name AS category FROM products p
LEFT JOIN brands b ON p.brand_id = b.id
LEFT JOIN categories c ON p.category_id = c.id
ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>商品清單</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">
    <h2>商品清單</h2>
    <a href="product_create.php" class="btn btn-success mb-3">新增商品</a>
    <table class="table table-bordered">
        <thead><tr><th>品牌</th><th>型號</th><th>分類</th><th>狀態</th><th>庫存</th></tr></thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['brand'] ?></td>
                <td><?= $p['model'] ?></td>
                <td><?= $p['category'] ?></td>
                <td><?= $p['status'] ?></td>
                <td><?= $p['stock_quantity'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>