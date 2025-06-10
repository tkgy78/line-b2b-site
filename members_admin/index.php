<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 撈取會員資料，包含所屬店家、店家等級與地區名稱
$stmt = $pdo->query("
  SELECT 
    m.*, 
    s.name AS store_name_ref, 
    s.type AS store_type,
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

  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th style="width: 40px;">ID</th>
        <th style="width: 15%;">帳號</th>
        <th style="width: 10%;">姓名</th>
        <th style="width: 10%;">職稱</th>
        <th style="width: 25%;">所屬店家</th>
        <th style="width: 10%;">店內角色</th>
        <th style="width: 10%;">身份等級</th>
        <th style="width: 12%;">註冊時間</th>
        <th style="width: 10%;">操作</th>
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
            <?php if ($m['region_name']): ?>
              <small class="text-muted">（<?= htmlspecialchars($m['region_name']) ?>）</small>
            <?php endif; ?>
            <?php if ($m['store_type']): ?>
              <span class="badge bg-secondary"><?= strtoupper($m['store_type']) ?></span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($m['store_role'] ?: '-') ?></td>
          <td><?= htmlspecialchars($m['member_level'] ?: '-') ?></td>
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