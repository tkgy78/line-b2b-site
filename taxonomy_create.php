<?php
require_once 'db.php';

// Handle insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = trim($_POST['name']);
    $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;

    if (!empty($type) && !empty($name)) {
        if ($type === 'brand') {
            $stmt = $db->prepare("INSERT INTO brands (name) VALUES (?)");
            $stmt->bind_param("s", $name);
        } elseif ($type === 'category') {
            $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
        } elseif ($type === 'series' && $brand_id) {
            $stmt = $db->prepare("INSERT INTO series (name, brand_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $brand_id);
        }
        if (isset($stmt)) $stmt->execute();
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int)$_GET['delete'];
    $type = $_GET['type'];
    if ($type === 'brand') {
        $db->query("DELETE FROM brands WHERE id = $id");
    } elseif ($type === 'category') {
        $db->query("DELETE FROM categories WHERE id = $id");
    } elseif ($type === 'series') {
        $db->query("DELETE FROM series WHERE id = $id");
    }
}

// Load data
$brands = $db->query("SELECT * FROM brands")->fetch_all(MYSQLI_ASSOC);
$categories = $db->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$series = $db->query("SELECT series.*, brands.name AS brand_name FROM series LEFT JOIN brands ON series.brand_id = brands.id")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>分類管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>新增分類</h2>
    <form method="post" class="row g-3 mb-4">
        <div class="col-auto">
            <select name="type" class="form-select" required>
                <option value="">請選擇類型</option>
                <option value="brand">品牌</option>
                <option value="category">類別</option>
                <option value="series">系列</option>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" name="name" class="form-control" placeholder="名稱" required>
        </div>
        <div class="col-auto">
            <select name="brand_id" class="form-select">
                <option value="">所屬品牌（系列用）</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">新增</button>
        </div>
    </form>

    <h4>品牌</h4>
    <ul class="list-group mb-4">
        <?php foreach ($brands as $b): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($b['name']) ?>
                <a href="?delete=<?= $b['id'] ?>&type=brand" class="btn btn-sm btn-danger">刪除</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h4>類別</h4>
    <ul class="list-group mb-4">
        <?php foreach ($categories as $c): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($c['name']) ?>
                <a href="?delete=<?= $c['id'] ?>&type=category" class="btn btn-sm btn-danger">刪除</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h4>系列</h4>
    <ul class="list-group mb-4">
        <?php foreach ($series as $s): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($s['brand_name']) ?> - <?= htmlspecialchars($s['name']) ?>
                <a href="?delete=<?= $s['id'] ?>&type=series" class="btn btn-sm btn-danger">刪除</a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
