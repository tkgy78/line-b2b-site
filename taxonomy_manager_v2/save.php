<?php
require 'db.php';
$type = $_POST['type'];
$name = $conn->real_escape_string($_POST['name']);
if ($type == 'brand') {
    $conn->query("INSERT INTO brands (name) VALUES ('$name')");
} elseif ($type == 'category') {
    $conn->query("INSERT INTO categories (name) VALUES ('$name')");
} elseif ($type == 'series') {
    $brand_id = (int)$_POST['brand_id'];
    $conn->query("INSERT INTO series (name, brand_id) VALUES ('$name', $brand_id)");
}
header('Location: index.php');
?>