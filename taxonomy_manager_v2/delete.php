<?php
require 'db.php';
$type = $_GET['type'];
$id = (int)$_GET['id'];
if ($type == 'brand') {
    $count = $conn->query("SELECT COUNT(*) FROM series WHERE brand_id = $id")->fetch_row()[0];
    if ($count == 0) {
        $conn->query("DELETE FROM brands WHERE id = $id");
    }
} elseif ($type == 'category') {
    $conn->query("DELETE FROM categories WHERE id = $id");
} elseif ($type == 'series') {
    $conn->query("DELETE FROM series WHERE id = $id");
}
header('Location: index.php');
?>