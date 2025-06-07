<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$software_id = $_GET['id'] ?? 0;
if (!$software_id) {
  die('缺少軟體 ID');
}

// 撈軟體
$stmt = $pdo->prepare("SELECT * FROM softwares WHERE id = ?");
$stmt->execute([$software_id]);
$software = $stmt->fetch();
if (!$software) {
  die('找不到軟體');
}

// 撈品牌和分類清單
$brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// 篩選參數
$brand_id = $_GET['brand_id'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$keyword = trim($_GET['keyword'] ?? '');

// 撈商品清單
$sql = "SELECT p.id, p.name, b.name AS brand_name 
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        WHERE 1";
$params = [];

if ($brand_id !== '') {
  $sql .= " AND p.brand_id = ?";
  $params[] = $brand_id;
}
if ($category_id !== '') {
  $sql .= " AND p.category_id = ?";
  $params[] = $category_id;
}
if ($keyword !== '') {
  $sql .= " AND p.name LIKE ?";
  $params[] = '%' . $keyword . '%';
}

$sql .= " ORDER BY p.name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allProducts = $stmt->fetchAll();

// 撈已綁定商品
$stmt = $pdo->prepare("SELECT product_id FROM product_software WHERE software_id = ?");
$stmt->execute([$software_id]);
$linkedProductIds = array_column($stmt->fetchAll(), 'product_id');

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pdo->prepare("DELETE FROM product_software WHERE software_id = ?")->execute([$software_id]);
  if (!empty($_POST['product_ids']) && is_array($_POST['product_ids'])) {
    $insert = $pdo->prepare("INSERT INTO product_software (software_id, product_id) VALUES (?, ?)");
    foreach ($_POST['product_ids'] as $pid) {
      $insert->execute([$software_id, $pid]);
    }
  }
  header("Location: index.php");
  exit;
}

include 'partials/header.php';
?>

<div class="container py-4">
  <h2>綁定商品：<?= htmlspecialchars($software['name']) ?></h2>

  <!-- 搜尋表單 -->
  <form class="row g-2 mb-4" method="get">
    <input type="hidden" name="id" value="<?= $software_id ?>">
    <div class="col-md-3">
      <select name="brand_id" class="form-select">
        <option value="">-- 所有品牌 --</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b['id'] ?>" <?= $brand_id == $b['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="category_id" class="form-select">
        <option value="">-- 所有分類 --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" name="keyword" class="form-control" placeholder="搜尋型號或名稱" value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">搜尋</button>
    </div>
  </form>

  <!-- 勾選清單 -->
  <form method="post">
    <div class="mb-3 border rounded p-3" style="max-height: 500px; overflow-y: auto;">
      <?php if (count($allProducts) === 0): ?>
        <div class="text-muted">查無符合條件的商品</div>
      <?php endif; ?>
      <?php foreach ($allProducts as $p): ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>"
                 id="prod<?= $p['id'] ?>" <?= in_array($p['id'], $linkedProductIds) ? 'checked' : '' ?>>
          <label class="form-check-label" for="prod<?= $p['id'] ?>">
            <?= htmlspecialchars($p['brand_name'] . ' - ' . $p['name']) ?>
          </label>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="btn btn-success">儲存綁定</button>
    <a href="index.php" class="btn btn-secondary">返回列表</a>
  </form>
</div>

<?php include 'partials/footer.php'; ?>