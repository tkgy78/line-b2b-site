<?php
require_once '../config.php';
require_once '../partials/admin_header.php';

function calculateScore($store) {
    $recent_views = $store['recent_view_count'] ?? 0;
    $total_views = $store['view_price_count'] ?? 0;
    $order_amount = $store['order_amount_90d'] ?? 0;

    $score = 0;
    $score += $recent_views * 0.6;
    $score += $total_views * 0.2;
    $score += ($order_amount / 10000) * 0.2;
    return round($score, 1);
}

$pending = [];
$vip_no_order = [];
$vip_inactive = [];
$potential = [];
$vip_upgradable = [];
$cold_vip = [];
$warning_vvip = [];
$all_by_city = [];
$by_sales = [];

$stmt = $pdo->query("SELECT * FROM stores");
$stores = $stmt->fetchAll();
$today = new DateTime();

foreach ($stores as $store) {
    $type = strtolower($store['type'] ?? '');
    $order_count = intval($store['order_count']);
    $last_order = $store['last_order_at'] ? new DateTime($store['last_order_at']) : null;
    $last_view = $store['last_view_price_at'] ? new DateTime($store['last_view_price_at']) : null;

    if (($store['status'] ?? '') == 'pending') {
        $pending[] = $store;
    }
    if ($type == 'vip' && $order_count == 0) {
        $vip_no_order[] = $store;
    }
    if ($type == 'vip' && $last_order) {
        $days = $last_order->diff($today)->days;
        if ($days > 60) {
            $store['inactive_days'] = $days;
            $vip_inactive[] = $store;
        }
    }

    $score = calculateScore($store);
    $store['score'] = $score;

    $inactive_days = 999;
    if ($last_order && $last_view) {
        $inactive_days = max(
            $last_order->diff($today)->days,
            $last_view->diff($today)->days
        );
    } elseif ($last_order) {
        $inactive_days = $last_order->diff($today)->days;
    } elseif ($last_view) {
        $inactive_days = $last_view->diff($today)->days;
    }

    if (!in_array($type, ['vip', 'vvip']) && $score > 10) {
        $store['days_inactive'] = $inactive_days;
        $potential[] = $store;
    }
    if ($type == 'vip' && intval($store['annual_order_amount']) >= 360000) {
        $vip_upgradable[] = $store;
    }
    if ($type == 'vip' && $inactive_days > 60) {
        $store['days_inactive'] = $inactive_days;
        $cold_vip[] = $store;
    }
    if ($type == 'vvip' && $inactive_days > 60) {
        $store['days_inactive'] = $inactive_days;
        $warning_vvip[] = $store;
    }

    $city = $store['city'] ?? '未知地區';
    if (!isset($all_by_city[$city])) {
        $all_by_city[$city] = [];
    }
    $all_by_city[$city][] = $store;

    $sales = $store['sales_id'] ?? '未指定';
    if (!isset($by_sales[$sales])) {
        $by_sales[$sales] = [];
    }
    $by_sales[$sales][] = $store;
}

uksort($all_by_city, function($a, $b) {
    $order = ['基隆市','台北市','新北市','桃園市','新竹市','台中市','台南市','高雄市'];
    $indexA = array_search($a, $order);
    $indexB = array_search($b, $order);
    if ($indexA === false) $indexA = 999;
    if ($indexB === false) $indexB = 999;
    return $indexA - $indexB;
});
?>

<div class="container mt-4">
  <ul class="nav nav-tabs" id="storeTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">主控台</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-stores" type="button" role="tab">全部店家</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#by-staff" type="button" role="tab">依業務檢視</button>
    </li>
  </ul>
  <div class="tab-content" id="storeTabsContent">
    <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
      <?php include 'partials/dashboard_cards.php'; ?>
    </div>
    <div class="tab-pane fade" id="all-stores" role="tabpanel">
      <?php include 'partials/all_stores.php'; ?>
    </div>
    <div class="tab-pane fade" id="by-staff" role="tabpanel">
      <?php include 'partials/by_staff.php'; ?>
    </div>
  </div>
</div>

<?php require_once '../partials/admin_footer.php'; ?>