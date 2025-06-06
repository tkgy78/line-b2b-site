<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  header("Location: index.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

include __DIR__ . '/partials/header.php';
?>

<ul class="nav nav-tabs mb-4">
  <li class="nav-item">
    <a class="nav-link" href="edit_basic.php?id=<?= $id ?>">基本資料</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="edit_detail.php?id=<?= $id ?>">商品詳情</a>
  </li>
</ul>

<form action="update_detail.php" method="post">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="mb-4">
    <label class="form-label">商品詳細說明</label>
    <textarea name="description" id="description" rows="10" class="form-control"><?= htmlspecialchars($product['description']) ?? '' ?></textarea>
  </div>

  <!-- 預留圖集區塊 -->
  <!-- <div class="mb-4">圖片上傳（future）</div> -->

  <!-- 預留 PDF 區塊 -->
  <!-- <div class="mb-4">PDF 檔案上傳（future）</div> -->

  <button type="submit" class="btn btn-primary">儲存詳情</button>
  <a href="index.php" class="btn btn-secondary ms-2">取消</a>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>