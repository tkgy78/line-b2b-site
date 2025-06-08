<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 撈取會員資料，包含所屬店家與地區名稱
$stmt = $pdo->query("
  SELECT 
    m.*, 
    s.name AS store_name_ref, 
    r.name AS region_name
  FROM members m
  LEFT JOIN stores s ON m.store_id = s.id
  LEFT JOIN regions r ON s.region_id = r.id
  ORDER BY m.id DESC
");
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 頁面標題
$page_title = "會員列表";
include __DIR__ . '/../products_admin/partials/header.php';
?>

<div class="container">
  <h1 class="h4 my-4">會員列表</h1>

  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>帳號</th>
        <th>姓名</th>
        <th>職稱</th>
        <th>所屬店家</th>
        <th>店內角色</th>
        <th>身份等級</th>
        <th>註冊時間</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($members as $m): ?>
        <tr>
          <td><?= $m['id'] ?></td>
          <td><?= htmlspecialchars($m['account']) ?></td>
          <td><?= htmlspecialchars($m['name']) ?></td>
          <td><?= htmlspecialchars($m['title']) ?></td>
          <td>
            <?= htmlspecialchars($m['store_name_ref'] ?? '-') ?>
            <?= $m['region_name'] ? "<small class='text-muted'>（{$m['region_name']}）</small>" : '' ?>
          </td>
          <td><?= htmlspecialchars($m['store_role']) ?></td>
          <td><?= htmlspecialchars($m['member_level']) ?></td>
          <td><?= htmlspecialchars($m['created_at']) ?></td>
          <td>
            <a href="edit.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">編輯</a>
            <a href="delete.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確認刪除此會員？')">刪除</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php include __DIR__ . '/../products_admin/partials/footer.php'; ?>