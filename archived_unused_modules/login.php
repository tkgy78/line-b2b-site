
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['user'] = [
        'id' => 1,
        'name' => $_POST['name'],
        'role_id' => $_POST['role_id']
    ];
    header("Location: products.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>模擬 LINE 登入</h2>
    <form method="POST">
        <div class="mb-3">
            <label>顯示名稱：</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>角色：</label>
            <select name="role_id" class="form-control">
                <option value="1">訪客</option>
                <option value="2">VIP</option>
                <option value="3">VVIP</option>
                <option value="4">業務</option>
                <option value="5">批發商</option>
                <option value="6">管理員</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">登入</button>
    </form>
</body>
</html>
