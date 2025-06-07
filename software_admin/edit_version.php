<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
if (!$id) {
  die('缺少版本 ID');
}

// 撈版本資料
$stmt = $pdo->prepare("SELECT * FROM software_versions WHERE id = ?");
$stmt->execute([$id]);
$version = $stmt->fetch();

if (!$version) {
  die('找不到此版本');
}

// 撈軟體名稱
$stmt = $pdo->prepare("SELECT name FROM softwares WHERE id = ?");
$stmt->execute([$version['software_id']]);
$software_name = $stmt->fetchColumn();

include 'partials/header.php';
?>

<div class="container py-4">
  <h2>編輯版本：<?= htmlspecialchars($software_name) ?> - <?= htmlspecialchars($version['version']) ?></h2>

  <form action="update_version.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $version['id'] ?>">
    <input type="hidden" name="software_id" value="<?= $version['software_id'] ?>">

    <div class="mb-3">
      <label class="form-label">版本號</label>
      <input type="text" name="version" class="form-control" required value="<?= htmlspecialchars($version['version']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">更新說明</label>
      <textarea name="changelog" class="form-control" rows="4"><?= htmlspecialchars($version['changelog']) ?></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">目前檔案</label><br>
      <a href="/<?= htmlspecialchars($version['file_path']) ?>" target="_blank">下載目前版本</a>
    </div>

    <div class="mb-3">
      <label class="form-label">更換檔案（選填）</label>
      <input type="file" name="file" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">儲存變更</button>
    <a href="versions.php?id=<?= $version['software_id'] ?>" class="btn btn-secondary">取消</a>
  </form>
</div>

<?php include 'partials/footer.php'; ?>