<?php
session_start();

$user = $_SESSION['user'] ?? ['id' => 0, 'name' => '訪客', 'role' => 'guest'];
$isLoggedIn = $user['role'] !== 'guest';
?>
