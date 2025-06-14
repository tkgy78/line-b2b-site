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

<!-- 編輯商品 Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">編輯商品</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- ✅ 加上分頁控制 -->
        <ul class="nav nav-tabs mb-3" id="editModalTab">
          <li class="nav-item">
            <a class="nav-link active" href="#" data-tab="basic">基本資訊</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#" data-tab="detail">商品詳情</a>
          </li>
        </ul>
        <!-- 分頁內容載入 -->
        <div id="modal-tab-content" class="pt-2">載入中...</div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
<!-- jQuery 本體 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- jQuery UI（sortable 功能） -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const contentArea = document.querySelector('#modal-tab-content');

  // 點擊匯入 CSV 按鈕載入 modal 並綁定提交事件
  const importBtn = document.getElementById('btn-open-import-modal');
  importBtn.addEventListener('click', async () => {
    const modalEl = document.getElementById('importCsvModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const res = await fetch('import_modal.php?ts=' + Date.now());
    const html = await res.text();
    document.getElementById('import-modal-body').innerHTML = html;

    setTimeout(() => {
      const form = document.getElementById('form-import-csv');
      if (!form) return;

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        try {
          const res = await fetch('import_csv.php', {
            method: 'POST',
            body: formData
          });
          const text = await res.text();
          console.log('📦 Raw response:', text);
          let result;

          try {
            result = JSON.parse(text);
          } catch (err) {
            alert('⚠️ 回傳格式錯誤，請檢查 import_csv.php 是否正確輸出 JSON');
            return;
          }

          if (result.success) {
            let msg = `✅ ${result.message}\n`;
            msg += `\n📊 匯入總結：\n`;
            msg += `- 匯入筆數：${result.summary.total_rows}\n`;
            msg += `- 新增商品：${result.summary.inserted}\n`;
            msg += `- 更新商品：${result.summary.updated}\n`;
            msg += `- 價格異動：${result.summary.price_changes}\n`;
            msg += `- 略過筆數：${result.summary.skipped}\n`;

            if (result.inserted_items?.length > 0) {
              msg += `\n🆕 新增商品：\n` + result.inserted_items.join('\n') + '\n';
            }
            if (result.updated_items?.length > 0) {
              msg += `\n✏️ 更新商品：\n` + result.updated_items.join('\n') + '\n';
            }
            if (result.price_changes_detail?.length > 0) {
              msg += `\n💲 價格異動：\n` + result.price_changes_detail.join('\n') + '\n';
            }
            if (result.skipped_rows?.length > 0) {
              msg += `\n⏭️ 略過項目：\n` + result.skipped_rows.join('\n') + '\n';
            }
            if (result.errors?.length > 0) {
              msg += `\n❗ 錯誤訊息：\n` + result.errors.join('\n') + '\n';
            }

            alert(msg);
            modal.hide();
            if (history.replaceState) history.replaceState(null, '', location.href);
            setTimeout(() => location.reload(), 300);
            }

        } catch (err) {
          alert('❌ 匯入時發生錯誤：' + err.message);
        }
      });
    }, 100);
  });

  // 編輯商品 Modal 控制
  document.querySelectorAll('.btn-edit-modal').forEach(btn => {
    btn.addEventListener('click', async () => {
      window.currentEditProductId = btn.dataset.id;
      const modalEl = document.getElementById('editModal');
      const modal = new bootstrap.Modal(modalEl);
      modal.show();

      const res = await fetch('edit_basic_modal.php?id=' + btn.dataset.id);
      const html = await res.text();
      contentArea.innerHTML = html;

      // 標記 basic 為 active
      const tabLinks = document.querySelectorAll('#editModal .nav-link');
      tabLinks.forEach(l => l.classList.remove('active'));
      const basicLink = document.querySelector('[data-tab="basic"]');
      if (basicLink) basicLink.classList.add('active');

      // 重綁儲存按鈕
      setTimeout(() => {
        const oldBtn = document.querySelector('#btn-save-basic');
        if (oldBtn) {
          const newBtn = oldBtn.cloneNode(true);
          oldBtn.parentNode.replaceChild(newBtn, oldBtn);
          newBtn.addEventListener('click', () => {
            const form = document.querySelector('#form-basic');
            if (!form) return alert('找不到表單');
            const formData = new FormData(form);
            fetch('update_product_basic.php', {
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

  // Modal 分頁控制與 CKEditor 切換初始化
  document.addEventListener('click', async (e) => {
    const link = e.target.closest('#editModal .nav-link');
    if (!link) return;
    e.preventDefault();
    const tab = link.dataset.tab;
    const pid = window.currentEditProductId;
    const res = await fetch((tab === 'basic' ? 'edit_basic_modal.php' : 'edit_detail_modal.php') + '?id=' + pid);
    const html = await res.text();
    contentArea.innerHTML = html;

    document.querySelectorAll('#editModal .nav-link').forEach(l => l.classList.remove('active'));
    link.classList.add('active');

    if (tab === 'detail') {
      setTimeout(() => {
        if (document.querySelector('#detailed_desc')) {
          CKEDITOR.replace('detailed_desc', { height: 300 });
        }

        // ✅ 拖曳排序初始化（圖片）
        if (window.jQuery && $('#sortable-images').length > 0) {
          $('#sortable-images').sortable({
            update: function () {
              const order = $(this).children('.sortable-item').map(function () {
                return $(this).data('id');
              }).get();

              fetch('/line_b2b/products_admin/update_image_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order })
              })
              .then(res => res.text())
              .then(text => {
                if (text.trim() !== 'success') {
                  alert('儲存圖片順序失敗：' + text);
                }
              })
              .catch(err => {
                console.error(err);
                alert('發生錯誤，無法儲存排序');
              });
            }
          });
        }

      }, 100); // end of setTimeout
    }
  });
  // 修正 Modal 關閉後 backdrop 灰幕未消失 bug
  const allModals = document.querySelectorAll('.modal');
  allModals.forEach(modalEl => {
    modalEl.addEventListener('hidden.bs.modal', () => {
      document.body.classList.remove('modal-open');
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) backdrop.remove();
    });
  });
});
// 修正 Modal 關閉後 backdrop 灰幕未消失 bug
const allModals = document.querySelectorAll('.modal');
allModals.forEach(modalEl => {
  modalEl.addEventListener('hidden.bs.modal', () => {
    document.body.classList.remove('modal-open');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) backdrop.remove();
  });
});

// ✅ 綁定 modal 內圖片刪除按鈕的事件（因為 AJAX 載入不會執行 <script>）
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-delete-img');
  if (!btn) return;

  const id = btn.dataset.id;
  const pid = btn.dataset.pid;

  if (!confirm("確定要刪除這張圖片？")) return;

  fetch(`/line_b2b/products_admin/delete_image.php?id=${id}&pid=${pid}`)
    .then(res => {
      if (res.ok) {
        btn.closest('div.border').remove();
      } else {
        alert("刪除失敗");
      }
    })
    .catch(err => {
      console.error(err);
      alert("發生錯誤，無法刪除圖片");
    });
});
// 👇 針對 PDF 刪除（規格書 / 使用手冊）
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.btn-delete-pdf');
  if (!btn) return;

  const type = btn.dataset.type;  // spec 或 manual
  const pid = btn.dataset.pid;

  if (!confirm("確定要刪除這個 PDF 檔案？")) return;

  fetch(`/line_b2b/products_admin/delete_pdf.php?type=${type}&pid=${pid}`)
    .then(res => res.text())
    .then(text => {
      if (text.trim() === 'success') {
        // 從畫面移除整個區塊（PDF 連結 + 按鈕）
        const wrapper = btn.closest('.pdf-wrapper') || btn.closest('.d-flex');
        if (wrapper) wrapper.remove();
      } else {
        alert("刪除失敗：" + text);
      }
    })
    .catch(err => {
      console.error(err);
      alert("發生錯誤，無法刪除 PDF");
    });
});
</script>