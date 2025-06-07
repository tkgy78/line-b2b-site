<?php
// software_admin/edit_software.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM softwares WHERE id = ?");
$stmt->execute([$id]);
$software = $stmt->fetch();

if (!$software) {
    echo "<div class='text-danger'>找不到該筆軟體資料</div>";
    exit;
}

include 'partials/header.php';
?>

<div class="container py-4">
  <h2>編輯軟體</h2>
  <form action="update_software.php" method="post" class="vstack gap-3">
    <input type="hidden" name="id" value="<?= $software['id'] ?>">

    <div>
      <label class="form-label">軟體名稱</label>
      <input type="text" name="name" class="form-control" required
             value="<?= htmlspecialchars($software['name']) ?>">
    </div>

    <div>
      <label class="form-label">描述（可選）</label>
      <textarea name="description" rows="4" class="form-control"><?= htmlspecialchars($software['description']) ?></textarea>
    </div>

    <div class="text-end">
      <button type="submit" class="btn btn-primary">更新</button>
      <a href="index.php" class="btn btn-secondary">取消</a>
    </div>
  </form>
</div>

<?php include 'partials/footer.php'; ?>