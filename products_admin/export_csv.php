<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 查詢所有商品與價格
$sql = "
  SELECT 
    p.id, p.name, p.sku, p.short_desc, p.cover_img,
    b.name AS brand_name,
    c.name AS category_name,
    s.name AS series_name,
    p.stock_quantity, p.unit, p.barcode,
    pr.price_type, pr.price
  FROM products p
  LEFT JOIN brands b ON b.id = p.brand_id
  LEFT JOIN categories c ON c.id = p.category_id
  LEFT JOIN series s ON s.id = p.series_id
  LEFT JOIN prices pr ON pr.product_id = p.id AND pr.is_latest = 1
  ORDER BY p.id DESC
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 將價格合併進同一列
$products = [];
foreach ($rows as $r) {
  $id = $r['id'];
  if (!isset($products[$id])) {
    $products[$id] = [
      '品牌' => $r['brand_name'] ?? '',
      '分類' => $r['category_name'] ?? '',
      '系列' => $r['series_name'] ?? '',
      '名稱' => $r['name'],
      'SKU' => $r['sku'],
      '簡述' => $r['short_desc'],
      '單位' => $r['unit'],
      '條碼' => $r['barcode'],
      '庫存數量' => $r['stock_quantity'],
      '建議售價' => '',
      'VIP價' => '',
      'VVIP價' => '',
      '批發價' => '',
      '成本價' => '',
      'EMMA價' => '',
    ];
  }

  switch ($r['price_type']) {
    case 'msrp':      $products[$id]['建議售價'] = $r['price']; break;
    case 'vip':       $products[$id]['VIP價'] = $r['price']; break;
    case 'vvip':      $products[$id]['VVIP價'] = $r['price']; break;
    case 'wholesale': $products[$id]['批發價'] = $r['price']; break;
    case 'cost':      $products[$id]['成本價'] = $r['price']; break;
    case 'emma':      $products[$id]['EMMA價'] = $r['price']; break;
  }
}

// 輸出 CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="products_export.csv"');

$output = fopen('php://output', 'w');
fwrite($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 加 BOM 防止中文亂碼

fputcsv($output, array_keys(reset($products))); // 標題列
foreach ($products as $row) {
  fputcsv($output, $row);
}
fclose($output);
exit;