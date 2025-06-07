<?php $cats = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(); ?>

<div class="col-12 col-md-4">
  <h4>分類</h4>

  <!-- 清單放上面 -->
  <ul class="list-group mb-3">
    <?php foreach ($cats as $c): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <?= htmlspecialchars($c['name']); ?>
        <span>
          <a href="edit_category.php?id=<?= $c['id']; ?>"  class="btn btn-sm btn-outline-primary">編輯</a>
          <a href="delete_category.php?id=<?= $c['id']; ?>" class="btn btn-sm btn-outline-danger">刪除</a>
        </span>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- 表單改為 vstack + 全寬按鈕 -->
  <form method="POST" action="store_category.php" class="vstack gap-2">
    <input type="text" name="name" class="form-control" placeholder="新增分類" required>
    <button class="btn btn-success w-100">新增分類</button>
  </form>
</div>