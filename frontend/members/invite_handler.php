<?php
require_once __DIR__ . '/../../db.php';
$pdo = connect();

// 接收表單資料
$invite_code = $_POST['invite_code'] ?? '';
$name = $_POST['name'] ?? '';
$account = $_POST['account'] ?? '';
$phone = $_POST['phone'] ?? '';
$store_name = $_POST['store_name'] ?? null;
$store_id = $_POST['store_id'] ?? null;

if (!$invite_code || !$name || !$account || !$phone) {
  die("缺少必要欄位");
}

// 查詢邀請碼
$stmt = $pdo->prepare("SELECT * FROM invite_codes WHERE code = ? AND used_at IS NULL AND expires_at > NOW()");
$stmt->execute([$invite_code]);
$invite = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$invite) {
  die("邀請碼無效或已過期");
}

$now = date('Y-m-d H:i:s');
$member_level = 'vip';  // 所有邀請註冊都預設成 VIP，未來由後台調整

try {
  $pdo->beginTransaction();

  // 若需要建立新店家（自主註冊）
  if ($invite['role_type'] === 'store_pending' && $store_name) {
    $stmt = $pdo->prepare("INSERT INTO stores (name, created_at) VALUES (?, ?)");
    $stmt->execute([$store_name, $now]);
    $store_id = $pdo->lastInsertId();
  }

  // 建立會員
  $stmt = $pdo->prepare("
    INSERT INTO members (name, account, phone, store_id, store_role, member_level, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $name,
    $account,
    $phone,
    $store_id ?: null,
    $invite['store_role'] ?? null,
    $member_level,
    $now
  ]);

  // 標記邀請碼已使用
  $stmt = $pdo->prepare("UPDATE invite_codes SET used_at = ? WHERE id = ?");
  $stmt->execute([$now, $invite['id']]);

  $pdo->commit();
  header("Location: register_success.php");
  exit;
} catch (Exception $e) {
  $pdo->rollBack();
  die("發生錯誤：" . $e->getMessage());
}