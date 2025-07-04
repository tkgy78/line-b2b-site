<?php
require_once __DIR__ . '/../../db.php';
$pdo = connect();

// 先抓系列，依品牌與排序顯示
$sql = "SELECT s.*, b.name AS bname
        FROM series s
        JOIN brands b ON s.brand_id = b.id
        ORDER BY b.name, s.display_order ASC, s.id ASC";
$series = $pdo->query($sql)->fetchAll();
?>

<div class="col-12 col-md-4">
  <h4>系列</h4>

  <ul class="list-group mb-3">
    <?php foreach ($series as $index => $s): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($s['bname'].' - '.$s['name']); ?>
        <span>
          <?php if ($index > 0): ?>
            <a href="views/move_series.php?id=<?= $s['id']; ?>&direction=up" class="btn btn-sm btn-outline-secondary">↑</a>
          <?php endif; ?>
          <?php if ($index < count($series) - 1): ?>
            <a href="views/move_series.php?id=<?= $s['id']; ?>&direction=down" class="btn btn-sm btn-outline-secondary">↓</a>
          <?php endif; ?>
          <a href="delete_series.php?id=<?= $s['id']; ?>" class="btn btn-sm btn-outline-danger">刪除</a>
        </span>
      </li>
    <?php endforeach; ?>
  </ul>

  <form method="POST" action="store_series.php" class="vstack gap-2">
    <select name="brand_id" class="form-select" required>
      <option value="">選擇品牌</option>
      <?php foreach ($pdo->query("SELECT id,name FROM brands") as $b): ?>
        <option value="<?= $b['id']; ?>"><?= htmlspecialchars($b['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <input type="text" name="name" class="form-control" placeholder="系列名稱" required>
    <button class="btn btn-success w-100">新增系列</button>
  </form>
</div>