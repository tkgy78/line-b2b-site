<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 取得軟體清單
$softwares = $pdo->query("SELECT id, name FROM softwares ORDER BY name")->fetchAll();

include 'partials/header.php';
?>

<div class="container py-4">
  <h2 class="mb-3">新增版本</h2>
  <form action="store_version.php" method="post" enctype="multipart/form-data" class="vstack gap-3">
    <div>
      <label class="form-label">套用軟體</label>
      <select name="software_id" class="form-select" required>
        <option value="">請選擇軟體</option>
        <?php foreach ($softwares as $s): ?>
          <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="form-label">版本號</label>
      <input type="text" name="version" class="form-control" required placeholder="例：v1.0.2">
    </div>

    <div>
      <label class="form-label">檔案</label>
      <input type="file" name="file" class="form-control" required>
    </div>

    <div>
      <label class="form-label">更新內容 / Changelog</label>
      <textarea name="changelog" class="form-control" rows="5" placeholder="版本說明..."></textarea>
    </div>

    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="is_latest" value="1" id="isLatest">
      <label class="form-check-label" for="isLatest">
        設為最新版
      </label>
    </div>

    <div class="text-end">
      <button class="btn btn-primary">儲存版本</button>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>