<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  die('缺少參數');
}
$stmt = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
$stmt->execute([$id]);
$store = $stmt->fetch();
if (!$store) {
  die('找不到資料');
}

$page_title = "店家詳細資料";
include __DIR__ . '/../partials/admin_header.php';
?>
<div class="container py-4">
  <h1 class="h4 mb-3"><?= htmlspecialchars($store['name']) ?> 詳細資料</h1>
  <ul class="list-group">
    <li class="list-group-item"><strong>縣市：</strong><?= $store['region'] ?></li>
    <li class="list-group-item"><strong>等級：</strong><?= $store['level'] ?></li>
    <li class="list-group-item"><strong>地址：</strong><?= $store['address'] ?></li>
    <li class="list-group-item"><strong>電話：</strong><?= $store['phone'] ?></li>
    <li class="list-group-item"><strong>業務ID：</strong><?= $store['manager_id'] ?></li>
    <li class="list-group-item"><strong>備註：</strong><?= nl2br(htmlspecialchars($store['note'])) ?></li>
  </ul>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
