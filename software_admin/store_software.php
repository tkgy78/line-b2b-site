<?php
// software_admin/store_software.php

require_once __DIR__ . '/../db.php';
$pdo = connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        die('軟體名稱不得為空');
    }

    $stmt = $pdo->prepare("INSERT INTO softwares (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);

    header('Location: index.php');
    exit;
} else {
    echo "請透過表單提交";
}