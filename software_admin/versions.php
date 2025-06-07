<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
if (!$id) {
  die('未提供軟體 ID');
}

// 撈軟體主檔
$stmt = $pdo->prepare("SELECT * FROM softwares WHERE id = ?");
$stmt->execute([$id]);
$software = $stmt->fetch();

if (!$software) {
  die('找不到軟體');
}

// 撈版本清單
$stmt = $pdo->prepare("SELECT * FROM software_versions WHERE software_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$versions = $stmt->fetchAll();

include 'partials/header.php';
?>

<div class="container py-4">
  <h2>版本管理：<?= htmlspecialchars($software['name']) ?></h2>

  <a href="create_version.php?software_id=<?= $software['id'] ?>" class="btn btn-success mb-3">新增版本</a>
  <a href="index.php" class="btn btn-secondary mb-3">回列表</a>

  <?php if (empty($versions)): ?>
    <div class="alert alert-info">尚無任何版本。</div>
  <?php else: ?>
    <table class="table table-bordered">
      <thead><tr>
        <th>版本</th>
        <th>更新說明</th>
        <th>檔案</th>
        <th>是否為最新版</th>
        <th>操作</th>
      </tr></thead>
      <tbody>
        <?php foreach ($versions as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['version']) ?></td>
          <td><?= nl2br(htmlspecialchars($v['changelog'])) ?></td>
          <td><a href="/<?= htmlspecialchars($v['file_path']) ?>" target="_blank">下載</a></td>
          <td>
            <?php if ($v['is_latest']): ?>
              <span class="badge bg-success">最新版</span>
            <?php else: ?>
              <a href="set_latest.php?id=<?= $v['id'] ?>&software_id=<?= $software['id'] ?>" class="btn btn-sm btn-outline-primary">設為最新版</a>
            <?php endif; ?>
          </td>
          <td>
            <a href="edit_version.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-primary">編輯</a>
            <a href="delete_version.php?id=<?= $v['id'] ?>&software_id=<?= $software['id'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('確認刪除這個版本？')">刪除</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include 'partials/footer.php'; ?>