<?php
session_start();
require_once '../../config.php';
require_once '../../db.php';

// 僅限管理員登入
if (!isset($_SESSION['member_id']) || $_SESSION['member_type'] !== 'admin') {
    echo "❌ 僅限管理員存取";
    exit;
}

$pdo = connect();

// 正確使用 inviter_id 對應產生人
$sql = "SELECT ic.*, m.name AS inviter_name
        FROM invite_codes ic
        LEFT JOIN members m ON ic.inviter_id = m.id
        ORDER BY ic.created_at DESC";
$stmt = $pdo->query($sql);
$invite_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>邀請碼管理（管理員）</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h3 class="mb-4">📋 邀請碼管理列表（僅限管理員）</h3>
  <table class="table table-bordered table-hover bg-white align-middle">
    <thead class="table-light">
      <tr>
        <th>邀請碼</th>
        <th>邀請人</th>
        <th>身分</th>
        <th>邀請對象</th>
        <th>所屬店家</th>
        <th>產生時間</th>
        <th>有效期限</th>
        <th>使用時間</th>
        <th>狀態</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($invite_codes as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['code']) ?></td>
        <td><?= htmlspecialchars($row['inviter_name'] ?? '未知') ?></td>
        <td><?= htmlspecialchars($row['inviter_role']) ?></td>
        <td>
          <?php
            echo $row['target_type'] === 'store_owner' ? '店家老闆' : '店內員工';
          ?>
        </td>
        <td><?= $row['target_store_id'] ?? '-' ?></td>
        <td><?= $row['created_at'] ?></td>
        <td><?= $row['expires_at'] ?></td>
        <td><?= $row['used_at'] ?? '-' ?></td>
        <td>
          <?php
            if (!empty($row['used_at'])) {
              echo '✅ 已使用';
            } elseif (strtotime($row['expires_at']) < time()) {
              echo '⏰ 已過期';
            } else {
              echo '🟡 未使用';
            }
          ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>