<?php
require_once '../db.php';
if (!empty($_POST['name']) && !empty($_POST['brand_id'])) {
    $stmt = $db->prepare("INSERT INTO series (brand_id, name) VALUES (?, ?)");
    $stmt->bind_param("is", $_POST['brand_id'], $_POST['name']);
    $stmt->execute();
}
header("Location: index.php");
