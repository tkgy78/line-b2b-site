<?php
session_start();
require_once 'config.php';
require_once 'db.php';

$email = $_POST['account'] ?? '';
$phone = $_POST['phone'] ?? '';

if (!$email || !$phone) {
  die("請輸入帳號與手機號碼");
}

$pdo = connect();
$sql = "SELECT * FROM members WHERE email = :email AND phone = :phone LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email, 'phone' => $phone]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

if ($member) {
  $_SESSION['member_id'] = $member['id'];
  $_SESSION['store_id'] = $member['store_id'];
  $_SESSION['member_type'] = $member['member_type'];
  $_SESSION['store_role'] = $member['store_role'];

  header("Location: frontend/products/index.php");
  exit;
} else {
  echo "<script>alert('登入失敗，請確認帳號與手機號碼');history.back();</script>";
  exit;
}