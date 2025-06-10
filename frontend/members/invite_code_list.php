<?php
// invite_code_list.php
session_start();
require_once '../config.php';
require_once '../db.php';

// 假設登入後 session 有 member_id
if (!isset($_SESSION['member_id'])) {
    echo "請先登入";
    exit;
}

$current_user_id = $_SESSION['member_id'];

// 查詢自己產生的邀請碼
$sql = "
    SELECT ic.*, m.name AS used_by_name
    FROM invite_codes ic
    LEFT JOIN members m ON ic.used_by = m.id
    WHERE ic.generated_by = :current_user_id
    ORDER BY ic.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['current_user_id' => $current_user_id]);
$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>我的邀請碼列表</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
    </style>
</head>
<body>
    <h2>我的邀請碼列表</h2>

    <table>
        <thead>
            <tr>
                <th>邀請碼</th>
                <th>建立日期</th>
                <th>是否已使用</th>
                <th>使用者名稱</th>
                <th>備註</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($codes as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['code']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <td><?= $row['used_by'] ? '✅ 是' : '❌ 否' ?></td>
                    <td><?= $row['used_by_name'] ?? '-' ?></td>
                    <td><?= htmlspecialchars($row['note'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>