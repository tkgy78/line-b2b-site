<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

// 品牌篩選
$brandsStmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
$allBrands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);
$filterBrandId = isset($_GET['brand_id']) && is_numeric($_GET['brand_id']) ? intval($_GET['brand_id']) : null;

// 商品與價格資料
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
            'msrp' => '-', 'vip' => '-', 'vvip' => '-', 'wholesale' => '-', 'cost' => '-', 'emma' => '-',
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
    <div class="col-12 col-md-4 mt-2 mt-md-0 text-md-end">
  <div class="d-flex flex-wrap justify-content-md-end gap-2">
    <a href="create.php" class="btn btn-success">
      <i class="bi bi-plus-lg"></i> 新增商品
    </a>
    <a href="export_csv.php" class="btn btn-secondary">
      <i class="bi bi-download"></i> 匯出 CSV
    </a>
    <button class="btn btn-primary" id="btn-open-import-modal">
      <i class="bi bi-upload"></i> 匯入 CSV
    </button>
  </div>
</div>
    <div class="col-12 col-md-4 mt-2 mt-md-0 text-md-end">
      <form class="d-inline" method="get" action="index.php">
        <select name="brand_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
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
          <th class="d-none d-md-table-cell">EMMA</th>
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
              <img src="/line_b2b/<?= htmlspecialchars($p['cover_img']) ?>" class="img-fluid img-thumb" style="max-width: 60px;">
            </td>
            <td><?= htmlspecialchars($p['brand_name']) ?></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= $p['msrp'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['vip'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['vvip'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['wholesale'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['cost'] ?></td>
            <td class="d-none d-md-table-cell"><?= $p['emma'] ?></td>
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

<!-- 匯入 CSV 的 Modal -->
<div class="modal fade" id="importCsvModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">匯入商品 CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="import-modal-body" class="text-center text-muted">載入中...</div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const contentArea = document.querySelector('#modal-tab-content');

  // 編輯商品 Modal 控制
  document.querySelectorAll('.btn-edit-modal').forEach(btn => {
    btn.addEventListener('click', async () => {
      window.currentEditProductId = btn.dataset.id;
      const modal = new bootstrap.Modal(document.getElementById('editModal'));
      modal.show();
      const res = await fetch('/line_b2b/products_admin/edit_basic_modal.php?id=' + btn.dataset.id);
      const html = await res.text();
      contentArea.innerHTML = html;

      const tabLinks = document.querySelectorAll('#editModal .nav-link');
      tabLinks.forEach(l => l.classList.remove('active'));
      document.querySelector('[data-tab="basic"]').classList.add('active');

      setTimeout(() => {
        const oldBtn = document.querySelector('#btn-save-basic');
        if (oldBtn) {
          const newBtn = oldBtn.cloneNode(true);
          oldBtn.parentNode.replaceChild(newBtn, oldBtn);
          newBtn.addEventListener('click', () => {
            const form = document.querySelector('#form-basic');
            if (!form) return alert('找不到表單');
            const formData = new FormData(form);
            fetch('/line_b2b/products_admin/update_product_basic.php', {
              method: 'POST', body: formData
            })
            .then(res => res.text())
            .then(msg => {
              if (msg.trim() === 'success') {
                alert('更新成功！');
                modal.hide();
                setTimeout(() => location.reload(), 300);
              } else {
                alert('更新失敗：' + msg);
              }
            })
            .catch(err => alert('錯誤：' + err));
          });
        }
      }, 100);
    });
  });

  // Modal 分頁 CKEditor 切換
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('#editModal .nav-link');
    if (!link) return;
    e.preventDefault();
    const tab = link.dataset.tab;
    const pid = window.currentEditProductId;
    const res = await fetch('/line_b2b/products_admin/' + (tab === 'basic' ? 'edit_basic_modal.php' : 'edit_detail_modal.php') + '?id=' + pid);
    document.querySelector('#modal-tab-content').innerHTML = await res.text();
    document.querySelectorAll('#editModal .nav-link').forEach(l => l.classList.remove('active'));
    link.classList.add('active');

    if (tab === 'detail') {
      setTimeout(() => {
        if (document.querySelector('#detailed_desc')) {
          CKEDITOR.replace('detailed_desc', { height: 300 });
        }
      }, 100);
    }
  });

  // 點擊匯入 CSV 按鈕載入 modal
  document.getElementById('btn-open-import-modal').addEventListener('click', () => {
    const modal = new bootstrap.Modal(document.getElementById('importCsvModal'));
    modal.show();

    // 強制重新載入 import_modal.php，避免快取
    fetch('import_modal.php?ts=' + Date.now())
      .then(res => {
        if (!res.ok) throw new Error('載入匯入表單失敗');
        return res.text();
      })
      .then(html => {
        document.getElementById('import-modal-body').innerHTML = html;
      })
      .catch(err => {
        document.getElementById('import-modal-body').innerHTML =
          `<div class="alert alert-danger">載入表單失敗：${err.message}</div>`;
      });
  });
});
</script>