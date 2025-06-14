<?php
require_once __DIR__ . '/../db.php';
$pdo = connect();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  echo "<div class='text-danger p-3'>未提供商品 ID</div>";
  exit;
}

// 撈主商品
$stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<div class='text-danger p-3'>找不到此商品</div>";
  exit;
}

// 撈詳細資料（product_details）
$detailStmt = $pdo->prepare("SELECT * FROM product_details WHERE product_id = ?");
$detailStmt->execute([$id]);
$detail = $detailStmt->fetch(PDO::FETCH_ASSOC) ?: ['detail_html' => '', 'spec_file' => '', 'manual_file' => ''];

// 撈圖片
$stmt = $pdo->prepare("SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY id");
$stmt->execute([$id]);
$productImages = $stmt->fetchAll();
?>

<form action="update_detail.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

  <!-- 商品說明 -->
  <div class="mb-3">
    <label class="form-label">詳細說明</label>
    <textarea name="detail_html" id="detailed_desc" rows="10" class="form-control"><?= htmlspecialchars($detail['detail_html']) ?></textarea>
  </div>

  <!-- 圖片管理 -->
<div class="mb-3">
  <label class="form-label">新增圖片（可多選）</label>
  <input type="file" name="images[]" multiple class="form-control" accept="image/*">
</div>
<div class="mb-2">已上傳圖片如下（可拖曳排序）：</div>
<div id="sortable-images" class="d-flex flex-wrap gap-2">
  <?php foreach ($productImages as $img): ?>
    <div class="sortable-item border p-1 text-center" data-id="<?= $img['id'] ?>">
      <img src="/line_b2b/<?= htmlspecialchars($img['image_url']) ?>" style="width: 80px; height: 80px; object-fit: cover;">
      <div class="mt-1">
        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-img" data-id="<?= $img['id'] ?>" data-pid="<?= $id ?>">刪除</button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

  <!-- PDF 區 -->
  <div class="mb-3 mt-4">
    <label class="form-label">上傳規格書 PDF</label>
    <input type="file" name="spec_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($detail['spec_file'])): ?>
      <div class="pdf-wrapper d-flex align-items-center justify-content-between border p-2 mb-2 bg-light">
        <a href="/<?= htmlspecialchars($detail['spec_file']) ?>" target="_blank">
          <?= basename($detail['spec_file']) ?>
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-pdf" data-type="spec" data-pid="<?= $product['id'] ?>">刪除</button>
      </div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label">上傳使用手冊 PDF</label>
    <input type="file" name="manual_file" accept="application/pdf" class="form-control mb-2">
    <?php if (!empty($detail['manual_file'])): ?>
      <div class="pdf-wrapper d-flex align-items-center justify-content-between border p-2 mb-2 bg-light">
        <a href="/<?= htmlspecialchars($detail['manual_file']) ?>" target="_blank">
          <?= basename($detail['manual_file']) ?>
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-pdf" data-type="manual" data-pid="<?= $product['id'] ?>">刪除</button>
      </div>
    <?php endif; ?>
  </div>

  <div class="text-end mt-3">
    <button type="submit" class="btn btn-primary">儲存商品詳情</button>
  </div>
</form>

<!-- CKEditor -->
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
  setTimeout(() => {
    if (document.querySelector('#detailed_desc')) {
      CKEDITOR.replace('detailed_desc', { height: 300 });
    }
  }, 200);
</script>

<script>
// 👇 圖片 & PDF 刪除功能（AJAX，不跳頁）
document.addEventListener('click', function (e) {
  if (e.target.classList.contains('btn-delete-img')) {
    const id = e.target.dataset.id;
    const pid = e.target.dataset.pid;

    if (!confirm("確定要刪除這張圖片？")) return;

    fetch(`/line_b2b/products_admin/delete_image.php?id=${id}&pid=${pid}`)
      .then(res => {
        if (res.ok) {
          e.target.closest('div.border').remove();
        } else {
          alert("刪除失敗");
        }
      })
      .catch(err => {
        console.error(err);
        alert("發生錯誤，無法刪除圖片");
      });
  }

  // 👇 PDF 刪除功能（spec/manual）
  if (e.target.classList.contains('btn-delete-pdf')) {
    const type = e.target.dataset.type;
    const pid = e.target.dataset.pid;

    if (!confirm("確定要刪除這個 PDF 檔案？")) return;

    fetch(`/line_b2b/products_admin/delete_pdf.php?type=${type}&pid=${pid}`)
      .then(res => res.text())
      .then(txt => {
        if (txt.trim() === 'success') {
          e.target.closest('.d-flex').remove();
        } else {
          alert('刪除失敗：' + txt);
        }
      })
      .catch(err => {
        console.error(err);
        alert('發生錯誤，無法刪除 PDF');
      });
  }
});

// ✅ 初始化圖片拖曳排序（jQuery UI sortable）
setTimeout(() => {
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
}, 200);
</script>