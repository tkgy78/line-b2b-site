<?php
// partials/all_stores.php
$sorted_city_order = ['基隆市','台北市','新北市','桃園市','新竹市','台中市','台南市','高雄市'];
?>

<?php foreach ($sorted_city_order as $city): ?>
  <?php if (isset($all_by_city[$city])): ?>
    <div class="mt-4">
      <h6 class="text-secondary small mb-1">縣市</h6>
      <h5 class="bg-light p-2 border rounded mb-2"><?= htmlspecialchars($city) ?></h5>
      <table class="table table-sm table-bordered table-striped align-middle">
        <thead class="table-secondary">
          <tr>
            <th style="width: 20%;">店家名稱</th>
            <th style="width: 15%;">負責業務</th>
            <th style="width: 10%;">等級</th>
            <th style="width: 15%;">最後下單</th>
            <th style="width: 10%;">下單次數</th>
            <th style="width: 10%;">瀏覽次數</th>
            <th style="width: 20%;">操作</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($all_by_city[$city] as $store): ?>
            <tr>
              <td><?= htmlspecialchars($store['name']) ?></td>
              <td><?= htmlspecialchars($store['sales_id']) ?></td>
              <td><?= strtoupper($store['type']) ?></td>
              <td><?= $store['last_order_at'] ?? '—' ?></td>
              <td><?= intval($store['order_count']) ?></td>
              <td><?= intval($store['view_price_count']) ?></td>
              <td>
                <a href="detail/view.php?id=<?= $store['id'] ?>" class="btn btn-sm btn-outline-secondary">查看</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
<?php endforeach; ?>