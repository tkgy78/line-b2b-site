<?php
require_once __DIR__ . '/../../db.php';
$pdo = connect();

$code = $_GET['code'] ?? '';
if (!$code) {
  die("缺少邀請碼");
}

// 讀取邀請碼資料
$stmt = $pdo->prepare("SELECT * FROM invite_codes WHERE code = ? AND used_at IS NULL AND expires_at > NOW()");
$stmt->execute([$code]);
$invite = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invite) {
  die("邀請碼無效或已過期");
}

// 顯示畫面
$page_title = "邀請註冊";
include __DIR__ . '/../../partials/frontend_header.php';
?>

<div class="container my-5">
  <h3 class="mb-4">完成註冊</h3>

  <form method="post" action="invite_handler.php">
    <input type="hidden" name="invite_code" value="<?= htmlspecialchars($code) ?>">

    <div class="mb-3">
      <label class="form-label">姓名</label>
      <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email 帳號</label>
      <input type="email" name="account" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">手機號碼</label>
      <input type="text" name="phone" class="form-control" required>
    </div>

    <?php if ($invite['role_type'] === 'store_pending'): ?>
      <!-- 自主註冊店家，需填寫店家資料 -->
      <div class="mb-3">
        <label class="form-label">店家名稱</label>
        <input type="text" name="store_name" class="form-control" required>
      </div>
    <?php elseif ($invite['role_type'] === 'employee' && $invite['store_id']): ?>
      <div class="mb-3">
        <label class="form-label">所屬店家</label>
        <input type="text" class="form-control" value="ID: <?= $invite['store_id'] ?>" disabled>
        <input type="hidden" name="store_id" value="<?= $invite['store_id'] ?>">
      </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary">送出註冊</button>
  </form>
</div>

<?php include __DIR__ . '/../../partials/frontend_footer.php'; ?>