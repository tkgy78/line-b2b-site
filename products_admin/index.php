<?php
// products_admin/index.php

require_once __DIR__ . '/../db.php';  // 載入 db.php，請確保 connect() 函式正確
$pdo = connect();

// 取得品牌
$brandsStmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$allBrands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);

// 處理品牌篩選
$filterBrandId = isset($_GET['brand_id']) && is_numeric($_GET['brand_id'])
               ? intval($_GET['brand_id']) : null;

// 撈商品與價格資料
$sql = "
  SELECT 
    p.id, p.sku, p.name AS product_name, p.cover_img, p.brand_id,
    b.name AS brand_name, p.stock_quantity, p.status,
    pr.price_type, pr.price
  FROM products p
  JOIN brands b ON b.id = p.brand_id
  LEFT JOIN prices pr ON pr.product_id = p.id
  " . (
    $filterBrandId
    ? "ORDER BY (p.brand_id = {$filterBrandId}) DESC, p.id DESC"
    : "ORDER BY p.id DESC"
  );

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 整理商品
$products = [];
foreach ($rows as $r) {
    $id = $r['id'];
    if (!isset($products[$id])) {
        $products[$id] = [
            'id' => $id,
            'sku' => $r['sku'],
            'brand_id' => $r['brand_id'],
            'brand_name' => $r['brand_name'],
            'name' => $r['product_name'],
            'cover_img' => $r['cover_img'],
            'stock' => $r['stock_quantity'] ?? 0,
            'status' => $r['status'],
            'msrp' => '-', 'vip' => '-', 'vvip' => '-', 'wholesale' => '-', 'cost' => '-',
        ];
    }
    if (!empty($r['price_type'])) {
        $products[$id][$r['price_type']] = number_format($r['price'], 2);
    }
}

$page_title = '商品列表';
include __DIR__ . '/partials/header.php';
?>

<div class="container py-3">
  <div class="row mb-3 align-items-center">
    <div class="col-6 col-md-4">
      <h1 class="h3 mb-0">商品列表</h1>
    </div>
    <div class="col-6 col-md-4 text-end d-none d-md-block">
      <a href="create.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> 新增商品</a>
    </div>
    <div class="col-12 col-md-4 mt-2 mt-md-0 text-md-end">
      <form class="d-inline" method="get" action="index.php">
        <select name="brand_id" class="form-select form-select-sm d-inline w-auto"
                onchange="this.form.submit()">
          <option value="">— 全部品牌 —</option>
          <?php foreach ($allBrands as $b): ?>
            <option value="<?= $b['id'] ?>" <?= ($filterBrandId == $b['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th class="d-none d-md-table-cell">SKU</th>
          <th class="d-none d-md-table-cell" style="width: 80px;">產品圖</th>
          <th>品牌</th>
          <th class="tbl-name">名稱／型號</th>
          <th class="tbl-price">建議售價</th>
          <th class="d-none d-md-table-cell">VIP 價</th>
          <th class="d-none d-md-table-cell">VVIP 價</th>
          <th class="d-none d-md-table-cell">批發價</th>
          <th class="d-none d-md-table-cell">成本</th>
          <th class="d-none d-md-table-cell">狀態</th>
          <th>庫存</th>
          <th style="width: 150px;">動作</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td class="d-none d-md-table-cell"><?= htmlspecialchars($p['sku']) ?></td>
            <td class="d-none d-md-table-cell">
              <img src="/<?= htmlspecialchars($p['cover_img']) ?>" class="img-fluid img-thumb" style="max-width: 60px;">
            </td>
            <td><?= htmlspecialchars($p['brand_name']) ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= $p['msrp'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['vip'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['vvip'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['wholesale'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['cost'] ?></td>
            <td class="d-none d-md-table-cell">
              <?= $p['status'] === 'active' ? '<span class="badge bg-success">上架</span>' : '<span class="badge bg-secondary">下架</span>' ?>
            </td>
            <td><?= $p['stock'] ?></td>
            <td class="text-nowrap">
              <div class="d-none d-md-inline">
                <a href="javascript:void(0);" class="btn btn-sm btn-warning btn-edit-modal" data-id="<?= $p['id'] ?>">編輯</a>
                <a href="detail/view.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">詳情</a>
                <a href="delete.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('確認刪除？')">刪除</a>
              </div>
              <button class="btn btn-sm btn-secondary d-md-none" data-bs-toggle="modal"
                      data-bs-target="#moreModal"
                      data-product='<?= json_encode($p, JSON_HEX_TAG|JSON_HEX_APOS|JSON_UNESCAPED_UNICODE) ?>'>更多</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 詳細資料 Modal（手機用） -->
<div class="modal fade" id="moreModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">商品詳細</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<!-- 編輯用 Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">商品編輯</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs mb-3">
          <li class="nav-item"><a class="nav-link active" data-tab="basic" href="#">基本資料</a></li>
          <li class="nav-item"><a class="nav-link" data-tab="detail" href="#">商品詳情</a></li>
        </ul>
        <div id="modal-tab-content">
          <div class="text-center text-muted py-5">請選擇要編輯的分頁</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CKEditor -->
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const tabLinks = document.querySelectorAll('#editModal .nav-link');
  const contentArea = document.querySelector('#modal-tab-content');

  tabLinks.forEach(link => {
    link.addEventListener('click', async e => {
      e.preventDefault();
      tabLinks.forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      const tab = link.dataset.tab;
      const pid = window.currentEditProductId;
      const url = tab === 'basic' 
        ? `edit_basic_modal.php?id=${pid}` 
        : `edit_detail_modal.php?id=${pid}`;
      const res = await fetch(url);
      const html = await res.text();
      contentArea.innerHTML = html;

      // 初始化 CKEditor
      setTimeout(() => {
        if (document.querySelector('#detailed_desc')) {
          CKEDITOR.replace('detailed_desc', { height: 300 });
        }
      }, 100);
    });
  });

  document.querySelectorAll('.btn-edit-modal').forEach(btn => {
    btn.addEventListener('click', async () => {
      window.currentEditProductId = btn.dataset.id;
      new bootstrap.Modal(document.getElementById('editModal')).show();
      const res = await fetch(`edit_basic_modal.php?id=${btn.dataset.id}`);
      document.querySelector('#modal-tab-content').innerHTML = await res.text();
      tabLinks.forEach(l => l.classList.remove('active'));
      document.querySelector('[data-tab="basic"]').classList.add('active');
    });
  });

  const moreModal = document.getElementById('moreModal');
  moreModal.addEventListener('show.bs.modal', e => {
    const data = JSON.parse(e.relatedTarget.dataset.product);
    const body = moreModal.querySelector('.modal-body');
    const foot = moreModal.querySelector('.modal-footer');
    body.innerHTML = `
      <ul class="list-group">
        <li class="list-group-item"><strong>SKU：</strong>${data.sku}</li>
        <li class="list-group-item"><strong>品牌：</strong>${data.brand_name}</li>
        <li class="list-group-item"><strong>建議售價：</strong>${data.msrp}</li>
        <li class="list-group-item"><strong>VIP：</strong>${data.vip}</li>
        <li class="list-group-item"><strong>VVIP：</strong>${data.vvip}</li>
        <li class="list-group-item"><strong>批發價：</strong>${data.wholesale}</li>
        <li class="list-group-item"><strong>成本：</strong>${data.cost}</li>
        <li class="list-group-item"><strong>狀態：</strong>${data.status}</li>
        <li class="list-group-item"><strong>庫存：</strong>${data.stock}</li>
      </ul>`;
    foot.innerHTML = `
      <a href="detail/view.php?id=${data.id}" class="btn btn-info">詳情</a>
      <button class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>`;
  });
});
</script>
<script>
// 修復 Bootstrap Modal 關閉後畫面卡住（backdrop 沒移除）
document.addEventListener('hidden.bs.modal', function (event) {
  // 移除 backdrop
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(el => el.remove());

  // 移除 body 的 overflow hidden 和 padding
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
});
</script>
<?php include __DIR__ . '/partials/footer.php'; ?>