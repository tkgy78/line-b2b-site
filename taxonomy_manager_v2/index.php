<?php
require 'db.php';

// Fetch data
$brands = $conn->query("SELECT * FROM brands")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$series = $conn->query("SELECT s.*, b.name as brand_name FROM series s JOIN brands b ON s.brand_id = b.id")->fetch_all(MYSQLI_ASSOC);

echo "<h2>品牌</h2><ul>";
foreach ($brands as $b) {
  echo "<li>{$b['name']} <a href='edit.php?type=brand&id={$b['id']}'>編輯</a> ";
  $count = $conn->query("SELECT COUNT(*) FROM series WHERE brand_id = {$b['id']}")->fetch_row()[0];
  if ($count == 0) {
    echo "<a href='delete.php?type=brand&id={$b['id']}'>刪除</a>";
  } else {
    echo "(已被使用)";
  }
  echo "</li>";
}
echo "</ul><form method='POST' action='save.php'><input name='name'><input type='hidden' name='type' value='brand'><button>新增品牌</button></form>";

echo "<h2>系列</h2><ul>";
foreach ($series as $s) {
  echo "<li>{$s['brand_name']} - {$s['name']} <a href='edit.php?type=series&id={$s['id']}'>編輯</a> ";
  echo "<a href='delete.php?type=series&id={$s['id']}'>刪除</a></li>";
}
echo "</ul><form method='POST' action='save.php'><select name='brand_id'>";
foreach ($brands as $b) {
  echo "<option value='{$b['id']}'>{$b['name']}</option>";
}
echo "</select><input name='name'><input type='hidden' name='type' value='series'><button>新增系列</button></form>";

echo "<h2>分類</h2><ul>";
foreach ($categories as $c) {
  echo "<li>{$c['name']} <a href='edit.php?type=category&id={$c['id']}'>編輯</a> ";
  echo "<a href='delete.php?type=category&id={$c['id']}'>刪除</a></li>";
}
echo "</ul><form method='POST' action='save.php'><input name='name'><input type='hidden' name='type' value='category'><button>新增分類</button></form>";
?>