<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_GET['code'])) {
    http_response_code(400);
    echo "缺少邀請碼參數";
    exit;
}

$code = $_GET['code'];
$url = "http://localhost/line_b2b/frontend/members/register_with_code.php?code=" . urlencode($code);

// 建立 QR Code 物件（這是 v3 正確用法）
$qrCode = new QrCode($url);

// 你可以用 setSize() 的替代方法是 setEncoding / setErrorCorrectionLevel 等
// 但這邊我們用預設即可

$writer = new PngWriter();
$result = $writer->write($qrCode);

// 輸出圖片
header('Content-Type: ' . $result->getMimeType());
echo $result->getString();