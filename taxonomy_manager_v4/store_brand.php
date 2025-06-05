<?php
require_once '../db.php';
if (!empty($_POST['name'])) {
    $stmt = $db->prepare("INSERT INTO brands (name) VALUES (?)");
    $stmt->bind_param("s", $_POST['name']);
    $stmt->execute();
}
header("Location: index.php");
