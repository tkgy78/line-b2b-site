<?php
require_once '../../db.php';
session_start();
$pdo = connect();

$user_role = $_SESSION['user']['role'] ?? 'guest';

// 撈取商品、品牌與系列資料，並依品牌與系列排序
$stmt = $pdo->query("
  SELECT p.*, b.name AS brand_name, s.name AS series_name, s.display_order
  FROM products p
  JOIN brands b ON b.id = p.brand_id
  LEFT JOIN series s ON s.id = p.series_id
  ORDER BY b.name, s.display_order, p.name
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 分組：品牌 → 系列
$grouped = [];
foreach ($products as $p) {
  $brand = $p['brand_name'] ?? '未分類品牌';
  $series = $p['series_name'] ?? '未分類系列';
  $grouped[$brand][$series][] = $p;
}

include '../partials/header.php';
?>

<div class="container py-4">
  <h2 class="mb-4">產品清單價目表</h2>

  <?php foreach ($grouped as $brand => $seriesGroup): ?>
    <h3 class="mt-5"><?= htmlspecialchars($brand) ?></h3>

    <?php foreach ($seriesGroup as $series => $items): ?>
      <h5 class="mt-3 text-primary"><?= htmlspecialchars($series) ?></h5>
      <table class="table table-bordered align-middle table-sm">
        <thead class="table-light">
          <tr>
            <th style="width: 80px;">圖片</th>
            <th style="width: 20%;">型號</th>
            <th>功能簡述</th>
            <th style="width: 15%;">建議售價</th>
            <?php if (in_array($user_role, ['vip', 'vvip', 'sales', 'wholesaler', 'admin'])): ?>
              <th style="width: 15%;">VIP 價</th>
              <th style="width: 15%;">VVIP 價</th>
            <?php endif; ?>
            <?php if (in_array($user_role, ['sales', 'admin'])): ?>
              <th style="width: 15%;">業績獎金</th>
            <?php endif; ?>
            <?php if (in_array($user_role, ['wholesaler', 'admin'])): ?>
              <th style="width: 15%;">批發價</th>
            <?php endif; ?>
            <th style="width: 10%;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $p): ?>
            <tr>
              <td>
                <?php if (!empty($p['cover_img'])): ?>
                  <img src="/<?= htmlspecialchars($p['cover_img']) ?>" alt="" class="img-thumbnail" style="max-width: 70px;">
                <?php else: ?>
                  <span class="text-muted">無圖片</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= nl2br(htmlspecialchars($p['short_desc'])) ?></td>
              <td><?= number_format($p['price_msrp'] ?? 0) ?></td>

              <?php if (in_array($user_role, ['vip', 'vvip', 'sales', 'wholesaler', 'admin'])): ?>
                <td><?= number_format($p['price_vip'] ?? 0) ?></td>
                <td><?= number_format($p['price_vvip'] ?? 0) ?></td>
              <?php endif; ?>

              <?php if (in_array($user_role, ['sales', 'admin'])): ?>
                <td>
                  <?php
                    // 業績獎金邏輯範例：VIP 價的 10%
                    $commission = ($p['price_vip'] ?? 0) * 0.1;
                    echo number_format($commission, 0);
                  ?>
                </td>
              <?php endif; ?>

              <?php if (in_array($user_role, ['wholesaler', 'admin'])): ?>
                <td><?= number_format($p['price_wholesale'] ?? 0) ?></td>
              <?php endif; ?>

              <td><a href="view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">詳情</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>

<?php include '../partials/footer.php'; ?>