<?php
require_once __DIR__ . '/../db.php';
$page_title = '商品列表';
include __DIR__ . '/_partials/header.php';

$pdo = connect();
$stmt = $pdo->query("
  SELECT p.*, 
         (SELECT price FROM prices pr 
          WHERE pr.product_id = p.id AND pr.price_type='msrp' 
          ORDER BY pr.start_at DESC LIMIT 1) AS msrp
    FROM products p ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<h1 class="mb-4">商品列表</h1>

<?php if (!empty($_GET['msg'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<a href="create.php" class="btn btn-success mb-3">➕ 新增商品</a>

<table class="table table-bordered table-hover">
  <thead class="table-light">
    <tr>
      <th>ID</th><th>品名</th><th>SKU</th><th>建議售價</th><th>狀態</th><th>動作</th>
    </tr>
  </thead>
  <tbody>
  <?php if (!$products): ?>
    <tr><td colspan="6" class="text-center">尚無資料</td></tr>
  <?php else: foreach ($products as $p): ?>
    <tr>
      <td><?= $p['id'] ?></td>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td><?= htmlspecialchars($p['sku']) ?></td>
      <td><?= $p['msrp'] !== null ? number_format($p['msrp'],2) : '-' ?></td>
      <td><?= $p['status'] ? '<span class="badge bg-success">上架</span>' : '<span class="badge bg-secondary">下架</span>' ?></td>
      <td>
        <a href="detail/view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">詳情</a>
      </td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
<?php include __DIR__ . '/_partials/footer.php'; ?>