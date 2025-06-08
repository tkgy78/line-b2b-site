<?php
require_once __DIR__.'/../db.php';

function ensureDir($dir) {
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  if (!is_writable($dir)) chmod($dir, 0775);
}

if (!empty($_POST['name'])) {
    $name = trim($_POST['name']);
    $logo = 'no-logo.png';

    /* ① 檢查目錄 */
    $targetDir = __DIR__ . '/../uploads/brand_logos/';
    ensureDir($targetDir);

    /* ② 若有選檔就上傳 */
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {

        // 檔案大小限制（例如 2MB）
        if ($_FILES['logo']['size'] > 2*1024*1024) {
            die('檔案超過 2MB');
        }

        $ext  = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $safe = preg_replace('/[^a-zA-Z0-9_\\-]/', '_', $name);
        $logo = $safe . '_' . time() . '.' . $ext;
        $dest = $targetDir . $logo;

        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
            // 若失敗就回到 no-logo
            $logo = 'no-logo.png';
        }
        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    error_log('UPLOAD ERROR: ' . $_FILES['logo']['error']);
}
    }

    /* ③ 寫入 DB（只存檔名） */
    $stmt = $pdo->prepare('INSERT INTO brands(name,logo) VALUES (?,?)');
    $stmt->execute([$name, $logo]);
}

header('Location: index.php');