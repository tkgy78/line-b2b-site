<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$stmt = $pdo->query("
  SELECT s.*
  FROM stores s
  LEFT JOIN orders o ON s.id = o.store_id
  WHERE s.level IN ('vip', 'vvip') AND o.id IS NULL
  GROUP BY s.id
  ORDER BY s.region, s.name
");
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "VIP 店家但尚未下單";
include __DIR__ . '/../partials/admin_header.php';
?>
<div class="container py-4">
  <h1 class="h4 mb-4">VIP 店家但尚未下單</h1>
  <div class="list-group">
    <?php foreach ($stores as $s): ?>
      <a href="view.php?id=<?= $s['id'] ?>" class="list-group-item list-group-item-action">
        <?= htmlspecialchars($s['name']) ?>（<?= htmlspecialchars($s['region']) ?>） - <?= htmlspecialchars($s['level']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
