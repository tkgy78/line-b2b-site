<?php
require 'db.php';
$type = $_GET['type'];
$id = (int)$_GET['id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    if ($type == 'brand') {
        $conn->query("UPDATE brands SET name = '$name' WHERE id = $id");
    } elseif ($type == 'category') {
        $conn->query("UPDATE categories SET name = '$name' WHERE id = $id");
    } elseif ($type == 'series') {
        $brand_id = (int)$_POST['brand_id'];
        $conn->query("UPDATE series SET name = '$name', brand_id = $brand_id WHERE id = $id");
    }
    header('Location: index.php');
    exit;
}
$data = $conn->query("SELECT * FROM {$type}s WHERE id = $id")->fetch_assoc();
echo "<form method='POST'><input name='name' value='{$data['name']}'>";
if ($type == 'series') {
    $brands = $conn->query("SELECT * FROM brands");
    echo "<select name='brand_id'>";
    while ($b = $brands->fetch_assoc()) {
        $selected = $b['id'] == $data['brand_id'] ? 'selected' : '';
        echo "<option value='{$b['id']}' $selected>{$b['name']}</option>";
    }
    echo "</select>";
}
echo "<button>儲存</button></form>";
?>