
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require 'config.php';

$role_id = $_SESSION['user']['role_id'];

$stmt = $pdo->prepare("
    SELECT p.brand, p.model, p.product_code, p.description, pp.price
    FROM products p
    LEFT JOIN product_prices pp ON p.id = pp.product_id AND pp.role_id = ?
");
$stmt->execute([$role_id]);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>商品目錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>歡迎，<?= htmlspecialchars($_SESSION['user']['name']) ?></h2>
    <a href="logout.php" class="btn btn-sm btn-outline-secondary mb-4">登出</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>品牌</th>
                <th>型號</th>
                <th>商品編號</th>
                <th>描述</th>
                <th>價格</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['brand']) ?></td>
                <td><?= htmlspecialchars($p['model']) ?></td>
                <td><?= htmlspecialchars($p['product_code']) ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
                <td><?= $p['price'] !== null ? '$' . number_format($p['price'], 2) : '無價格' ?></td>
            </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</body>
</html>
