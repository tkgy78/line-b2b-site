<?php
require_once '../config.php';
require_once '../partials/admin_header.php';

// PDO 資料庫連線
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass, $options);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

// 潛力評分公式（可調整）
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

// 初始化陣列
$pending = [];
$vip_no_order = [];
$vip_inactive = [];
$potential = [];
$vip_upgradable = [];
$cold_vip = [];
$warning_vvip = [];

// 撈出所有店家資料
$stmt = $pdo->query("SELECT * FROM stores");
$stores = $stmt->fetchAll();

$today = new DateTime();

foreach ($stores as $store) {
    $type = strtolower($store['type'] ?? '');
    $order_count = intval($store['order_count']);
    $last_order = $store['last_order_at'] ? new DateTime($store['last_order_at']) : null;
    $last_view = $store['last_view_price_at'] ? new DateTime($store['last_view_price_at']) : null;

    // 未啟用
    if (($store['status'] ?? '') == 'pending') {
        $pending[] = $store;
    }

    // VIP 無下單
    if ($type == 'vip' && $order_count == 0) {
        $vip_no_order[] = $store;
    }

    // VIP 長期未下單
    if ($type == 'vip' && $last_order) {
        $days = $last_order->diff($today)->days;
        if ($days > 60) {
            $store['inactive_days'] = $days;
            $vip_inactive[] = $store;
        }
    }

    // 潛力分數與停滯計算
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

    // 潛力開發
    if (!in_array($type, ['vip', 'vvip']) && $score > 10) {
        $store['days_inactive'] = $inactive_days;
        $potential[] = $store;
    }

    // 已達升級條件
    if ($type == 'vip' && intval($store['annual_order_amount']) >= 360000) {
        $vip_upgradable[] = $store;
    }

    // 冷卻 VIP
    if ($type == 'vip' && $inactive_days > 60) {
        $store['days_inactive'] = $inactive_days;
        $cold_vip[] = $store;
    }

    // 停滯 VVIP
    if ($type == 'vvip' && $inactive_days > 60) {
        $store['days_inactive'] = $inactive_days;
        $warning_vvip[] = $store;
    }
}
?>

<div class="container mt-4">
  <ul class="nav nav-tabs mb-4" id="storeTabs" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" href="#dashboard" role="tab">主控台</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="by-staff-tab" data-bs-toggle="tab" href="#by-staff" role="tab">依業務檢視</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="all-stores-tab" data-bs-toggle="tab" href="#all-stores" role="tab">全部店家</a>
    </li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="dashboard" role="tabpanel">

<?php if (count($pending) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-warning text-dark">待審核申請</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家名稱</th><th>申請時間</th></tr></thead>
      <tbody>
        <?php foreach ($pending as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= $store['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($vip_no_order) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-info text-white">VIP 店家尚未下單</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>負責業務</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($vip_no_order as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= htmlspecialchars($store['sales_id']) ?></td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-primary">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($vip_inactive) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-secondary text-white">長期未下單 VIP 店家（超過 60 天）</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>最後下單</th><th>間隔天數</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($vip_inactive as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= $store['last_order_at'] ?></td>
          <td><?= $store['inactive_days'] ?> 天</td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-primary">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($potential) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-success text-white">潛力開發店家（尚未成為 VIP）</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>查價次數</th><th>最後查價</th><th>潛力分數</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($potential as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= $store['view_price_count'] ?></td>
          <td><?= $store['last_view_price_at'] ?></td>
          <td><?= $store['score'] ?></td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-primary">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($vip_upgradable) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-primary text-white">潛力升級 VIP（已達 36 萬）</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>年訂單總額</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($vip_upgradable as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td>$<?= number_format($store['annual_order_amount']) ?></td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-primary">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($cold_vip) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-dark text-white">冷卻中的 VIP（已超過 60 天）</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>最後活動</th><th>停滯天數</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($cold_vip as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= $store['last_view_price_at'] ?? '—' ?></td>
          <td><?= $store['days_inactive'] ?> 天</td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-warning">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php if (count($warning_vvip) > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-danger text-white">警示：VVIP 店家長時間未活動</div>
  <div class="card-body">
    <table class="table table-sm table-bordered">
      <thead><tr><th>店家</th><th>最後查價</th><th>最後下單</th><th>停滯天數</th><th>查看</th></tr></thead>
      <tbody>
        <?php foreach ($warning_vvip as $store): ?>
        <tr>
          <td><?= htmlspecialchars($store['name']) ?></td>
          <td><?= $store['last_view_price_at'] ?? '—' ?></td>
          <td><?= $store['last_order_at'] ?? '—' ?></td>
          <td><?= $store['days_inactive'] ?> 天</td>
          <td><a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-danger">查看</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

    </div> <!-- dashboard tab -->

    <div class="tab-pane fade" id="by-staff" role="tabpanel">
      <div class="alert alert-info mt-3">依業務檢視功能開發中，敬請期待。</div>
    </div>

    <div class="tab-pane fade" id="all-stores" role="tabpanel">
      <div class="alert alert-info mt-3">全部店家總表功能開發中，敬請期待。</div>
    </div>

  </div>
</div>

<?php require_once '../partials/admin_footer.php'; ?>