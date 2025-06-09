<?php
// 注意：這個檔案會被 storage_admin/index.php 使用
// 必須確保上層變數 $pending、$vip_no_order 等都已經先準備好
?>

<div class="tab-pane fade show active" id="dashboard" role="tabpanel">
  <?php if (count($pending) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">⚠️ 待審核申請</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($pending as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?> - 建立於 <?= $store['created_at'] ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <?php if (count($vip_no_order) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-info text-white">🧾 VIP 店家尚未下單</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($vip_no_order as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?>（<?= $store['city'] ?>）
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <?php if (count($vip_inactive) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white">😴 長期未下單 VIP（>60天）</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($vip_inactive as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?>（<?= $store['inactive_days'] ?> 天未下單）
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <?php if (count($vip_upgradable) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">🚀 可升級 VVIP（年業績已達）</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($vip_upgradable as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?>（業績 $<?= number_format($store['annual_order_amount']) ?>）
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <?php if (count($cold_vip) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-dark text-white">❄️ 冷卻中 VIP</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($cold_vip as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?>（停滯 <?= $store['days_inactive'] ?> 天）
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <?php if (count($warning_vvip) > 0): ?>
  <div class="card mb-4">
    <div class="card-header bg-danger text-white">🔥 VVIP 警示：長時間無活動</div>
    <div class="card-body">
      <ul class="list-group">
        <?php foreach ($warning_vvip as $store): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($store['name']) ?>（停滯 <?= $store['days_inactive'] ?> 天）
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</div>