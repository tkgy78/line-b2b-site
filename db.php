<?php
/* ---------- 基本設定 ---------- */
$host   = 'localhost';
$dbname = 'line_b2b';         // 資料庫名稱 → $dbname
$user   = 'root';
$pass   = '';
$charset = 'utf8mb4';

/* ---------- MySQLi 連線 ---------- */
$dbConn = new mysqli($host, $user, $pass, $dbname);
if ($dbConn->connect_error) {
    die('MySQLi 連線失敗：' . $dbConn->connect_error);
}

/* ---------- PDO 連線 ---------- */
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('PDO 連線失敗：' . $e->getMessage());
}
function connect() {
    global $pdo;
    return $pdo;
}
?>