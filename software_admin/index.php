<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$softwares = $pdo->query("SELECT * FROM softwares ORDER BY name")->fetchAll();

include 'partials/header.php';
?>
<div class="container py-4">
  <h2>軟體管理</h2>
  <a href="create_software.php" class="btn btn-success mb-3">新增軟體</a>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>名稱</th>
        <th>描述</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($softwares as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['name']) ?></td>
          <td><?= nl2br(htmlspecialchars($s['description'])) ?></td>
          <td>
            <a href="edit_software.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-primary">編輯</a>
            <a href="versions.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-info">版本</a>
            <a href="link_products.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">綁商品</a>
            <a href="delete_software.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確認刪除？')">刪除</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include 'partials/footer.php'; ?>