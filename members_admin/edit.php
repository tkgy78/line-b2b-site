<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id) {
  die("缺少 ID 參數");
}

// 撈取會員資料
$stmt = $pdo->prepare("
  SELECT m.*, s.name AS store_name, s.type AS store_type
  FROM members m
  LEFT JOIN stores s ON m.store_id = s.id
  WHERE m.id = ?
");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$member) {
  die("找不到該會員");
}

// 撈取所有店家
$stores = $pdo->query("SELECT id, name FROM stores ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// 儲存更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $store_id = $_POST['store_id'] ?? null;
  $store_role = $_POST['store_role'] ?? '';
  $member_level = $_POST['member_level'] ?? '';
  $store_type = $_POST['store_type'] ?? null;

  // 更新會員資料
  $stmt = $pdo->prepare("UPDATE members SET store_id=?, store_role=?, member_level=? WHERE id=?");
  $stmt->execute([$store_id, $store_role, $member_level, $id]);

  // 同步更新店家 VIP/VVIP 狀態
  if ($store_id && $store_type) {
    $stmt = $pdo->prepare("UPDATE stores SET type=? WHERE id=?");
    $stmt->execute([$store_type, $store_id]);
  }

  header("Location: index.php");
  exit;
}

include __DIR__ . '/../products_admin/partials/header.php';
?>

<div class="container my-4">
  <h3 class="mb-4">編輯會員 #<?= $member['id'] ?></h3>
  <form method="post">
    <div class="mb-3">
      <label class="form-label">帳號 (email)</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($member['account']) ?>" disabled>
    </div>

    <div class="mb-3">
      <label class="form-label">姓名</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" disabled>
    </div>

    <div class="mb-3">
      <label class="form-label">所屬店家</label>
      <select name="store_id" class="form-select">
        <option value="">-- 未指定 --</option>
        <?php foreach ($stores as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $member['store_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if ($member['store_id']): ?>
      <div class="mb-3">
        <label class="form-label">店家 VIP/VVIP 狀態</label>
        <select name="store_type" class="form-select">
          <option value="">-- 無設定 --</option>
          <option value="vip" <?= $member['store_type'] === 'vip' ? 'selected' : '' ?>>VIP</option>
          <option value="vvip" <?= $member['store_type'] === 'vvip' ? 'selected' : '' ?>>VVIP</option>
        </select>
        <small class="text-muted">此選項會同步更新該店家在 stores 資料表的 type 欄位</small>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">店內角色 (store_role)</label>
      <select name="store_role" class="form-select">
        <option value="">-- 無設定 --</option>
        <option value="老闆" <?= $member['store_role'] === '老闆' ? 'selected' : '' ?>>老闆</option>
        <option value="會計" <?= $member['store_role'] === '會計' ? 'selected' : '' ?>>會計</option>
        <option value="店長" <?= $member['store_role'] === '店長' ? 'selected' : '' ?>>店長</option>
        <option value="員工" <?= $member['store_role'] === '員工' ? 'selected' : '' ?>>員工</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">身份等級 (member_level)</label>
      <select name="member_level" class="form-select">
        <option value="">-- 一般會員 --</option>
        <option value="vip" <?= $member['member_level'] === 'vip' ? 'selected' : '' ?>>VIP</option>
        <option value="vvip" <?= $member['member_level'] === 'vvip' ? 'selected' : '' ?>>VVIP</option>
        <option value="staff" <?= $member['member_level'] === 'staff' ? 'selected' : '' ?>>內部業務</option>
        <option value="wholesaler" <?= $member['member_level'] === 'wholesaler' ? 'selected' : '' ?>>批發商</option>
        <option value="admin" <?= $member['member_level'] === 'admin' ? 'selected' : '' ?>>管理員</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">儲存</button>
    <a href="index.php" class="btn btn-secondary">取消</a>
  </form>
</div>

<?php include __DIR__ . '/../products_admin/partials/footer.php'; ?>