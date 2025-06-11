<?php
session_start();
if (isset($_SESSION['member_id'])) {
  header("Location: frontend/products/index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>登入 LINE B2B 系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title text-center mb-4">登入系統</h4>
          <form method="post" action="auth.php">
            <div class="mb-3">
              <label for="account" class="form-label">帳號（Email）</label>
              <input type="email" class="form-control" id="account" name="account" required>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">手機號碼</label>
              <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">登入</button>
          </form>
        </div>
      </div>
      <p class="text-center text-muted mt-3" style="font-size: 0.9rem;">請使用邀請註冊後的帳號登入</p>
    </div>
  </div>
</div>

</body>
</html>