<?php
session_start();
require_once '../../config.php';
require_once '../../db.php';

if (!isset($_SESSION['member_id'])) {
    echo "請先登入";
    exit;
}

$member_id = $_SESSION['member_id'];
$store_id = $_SESSION['store_id'] ?? null; // 若為邀請員工需帶入 store_id

$type = $_GET['type'] ?? 'store'; // 預設為邀請店家
$is_employee = ($type === 'employee');

// 設定過期時間（24 小時）
$created_at = date('Y-m-d H:i:s');
$expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));

if (!$is_employee) {
    // 邀請店家：限制每天最多 3 組
    $sql = "SELECT COUNT(*) FROM invite_codes WHERE generated_by = :uid AND role_type = 'store_pending' AND DATE(created_at) = CURDATE()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['uid' => $member_id]);
    $count = $stmt->fetchColumn();
    if ($count >= 3) {
        echo "<h3>⚠️ 今日已達 3 組店家邀請碼上限，請明日再試</h3>";
        exit;
    }
}

// 產生新邀請碼
$invite_code = strtoupper(bin2hex(random_bytes(4))); // 8 碼
$role_type = $is_employee ? 'employee' : 'store_pending';

$sql = "INSERT INTO invite_codes (code, generated_by, role_type, store_id, created_at, expires_at)
        VALUES (:code, :by, :role_type, :store_id, :created_at, :expires_at)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'code' => $invite_code,
    'by' => $member_id,
    'role_type' => $role_type,
    'store_id' => $is_employee ? $store_id : null,
    'created_at' => $created_at,
    'expires_at' => $expires_at
]);

$invite_url = "http://localhost/frontend/members/register_with_code.php?code=" . $invite_code;
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
    <h2>✅ <?= $is_employee ? '員工邀請碼' : '店家邀請碼' ?> 已產生</h2>
    <p><strong>邀請碼：</strong><?= htmlspecialchars($invite_code) ?></p>
    <p><strong>有效期限：</strong><?= htmlspecialchars($expires_at) ?>（24 小時）</p>

    <input type="text" id="inviteUrl" value="<?= htmlspecialchars($invite_url) ?>" readonly>
    <button onclick="copyLink()">📋 複製邀請連結</button>

    <h3>📱 QR Code 掃碼註冊</h3>
    <img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=<?= urlencode($invite_url) ?>" alt="邀請碼 QR Code">
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