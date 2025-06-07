<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? 'guest';

    // 模擬使用者資料
    $users = [
        'guest' => ['id' => 0, 'name' => '訪客', 'role' => 'guest'],
        'vip' => ['id' => 1, 'name' => 'VIP 店家', 'role' => 'vip'],
        'vvip' => ['id' => 2, 'name' => 'VVIP 簽約店', 'role' => 'vvip'],
        'sales' => ['id' => 3, 'name' => '業務', 'role' => 'sales'],
        'wholesaler' => ['id' => 4, 'name' => '批發商', 'role' => 'wholesaler'],
        'admin' => ['id' => 5, 'name' => '管理員', 'role' => 'admin'],
    ];

    $_SESSION['user'] = $users[$role] ?? $users['guest'];
    header('Location: frontend/products/index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>登入測試</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h3 class="mb-3">登入（模擬角色選擇）</h3>
    <form method="post">
      <select name="role" class="form-select mb-3">
        <option value="guest">一般訪客</option>
        <option value="vip">VIP（經銷商）</option>
        <option value="vvip">VVIP（簽約店）</option>
        <option value="sales">業務</option>
        <option value="wholesaler">批發商</option>
        <option value="admin">管理員</option>
      </select>
      <button type="submit" class="btn btn-primary">登入</button>
    </form>
  </div>
</body>
</html>
