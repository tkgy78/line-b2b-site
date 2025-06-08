<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$stmt = $pdo->query("SELECT * FROM stores WHERE level = 'pending' ORDER BY id DESC");
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "申請中店家";
include __DIR__ . '/../partials/admin_header.php';
?>
<div class="container py-4">
  <h1 class="h4 mb-4">申請中店家</h1>
  <div class="list-group">
    <?php foreach ($stores as $s): ?>
      <a href="view.php?id=<?= $s['id'] ?>" class="list-group-item list-group-item-action">
        <?= htmlspecialchars($s['name']) ?>（<?= htmlspecialchars($s['region']) ?>）
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
