<?php
session_start();
require_once '../../config.php';
require_once '../../db.php';

if (!isset($_SESSION['member_id'])) {
    echo "請先登入";
    exit;
}

$member_id = $_SESSION['member_id'];
$store_id = $_SESSION['store_id'] ?? null;
$member_type = $_SESSION['member_type'] ?? 'vip'; // 預設給 VIP，如果沒有登入角色
$store_role = $_SESSION['store_role'] ?? '';

$type = $_GET['type'] ?? 'store'; // 預設為邀請店家
$is_employee = ($type === 'employee');

// 設定過期時間（24 小時）
$created_at = date('Y-m-d H:i:s');
$expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));

$pdo = connect();

if (!$is_employee) {
    // 店家邀請碼：每天最多產 3 組
    $sql = "SELECT COUNT(*) FROM invite_codes 
            WHERE inviter_id = :uid AND target_type = 'store_owner' AND DATE(created_at) = CURDATE()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $member_id]);
    $count = $stmt->fetchColumn();
    if ($count >= 3) {
        echo "<h3>⚠️ 今日已達 3 組店家邀請碼上限，請明日再試</h3>";
        exit;
    }
}

// 產生新邀請碼
$invite_code = strtoupper(bin2hex(random_bytes(4))); // 8碼
$target_type = $is_employee ? 'store_staff' : 'store_owner';

$sql = "INSERT INTO invite_codes 
        (code, inviter_id, inviter_role, target_type, target_store_id, expires_at, created_at)
        VALUES 
        (:code, :inviter_id, :inviter_role, :target_type, :target_store_id, :expires_at, :created_at)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'code' => $invite_code,
    'inviter_id' => $member_id,
    'inviter_role' => $member_type,
    'target_type' => $target_type,
    'target_store_id' => $is_employee ? $store_id : null,
    'expires_at' => $expires_at,
    'created_at' => $created_at
]);

$invite_url = "http://localhost/line_b2b/frontend/members/register_with_code.php?code=" . $invite_code;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>邀請碼已產生</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .box { border: 1px solid #ccc; padding: 20px; border-radius: 10px; width: 90%; max-width: 500px; margin: auto; text-align: center; }
        input[type="text"] { width: 100%; padding: 10px; font-size: 16px; }
        button { margin-top: 10px; padding: 10px 20px; font-size: 16px; cursor: pointer; }
        img { margin-top: 20px; }
    </style>
</head>
<body>

<div class="box">
    <h2>✅ <?= $is_employee ? '員工邀請碼' : '經銷商邀請碼' ?> 已產生</h2>
    <p><strong>邀請碼：</strong><?= htmlspecialchars($invite_code) ?></p>
    <p><strong>有效期限：</strong><?= htmlspecialchars($expires_at) ?>（24 小時）</p>

    <input type="text" id="inviteUrl" value="<?= htmlspecialchars($invite_url) ?>" readonly>
    <button onclick="copyLink()">📋 複製邀請連結</button>

    <h3>📱 QR Code 掃碼註冊</h3>
    <img src="generate_qr.php?code=<?= urlencode($invite_code) ?>" width="200">
</div>

<script>
function copyLink() {
    const urlField = document.getElementById('inviteUrl');
    navigator.clipboard.writeText(urlField.value).then(function() {
        alert("已複製邀請連結！");
    }, function(err) {
        alert("無法複製：", err);
    });
}
</script>

</body>
</html>