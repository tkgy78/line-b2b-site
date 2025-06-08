<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$stmt = $pdo->query("
  SELECT region, COUNT(*) AS total,
         SUM(CASE WHEN level = 'vip' THEN 1 ELSE 0 END) AS vip_count,
         SUM(CASE WHEN level = 'vvip' THEN 1 ELSE 0 END) AS vvip_count
  FROM stores
  GROUP BY region
  ORDER BY region
");
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "縣市會員統計";
include __DIR__ . '/../partials/admin_header.php';
?>
<div class="container py-4">
  <h1 class="h4 mb-4">縣市會員統計</h1>
  <table class="table table-bordered">
    <thead>
      <tr><th>縣市</th><th>總店數</th><th>VIP</th><th>VVIP</th></tr>
    </thead>
    <tbody>
      <?php foreach ($summary as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['region']) ?></td>
        <td><?= $r['total'] ?></td>
        <td><?= $r['vip_count'] ?></td>
        <td><?= $r['vvip_count'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../partials/admin_footer.php'; ?>
