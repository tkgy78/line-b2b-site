<?php
require_once __DIR__ . '/../db.php';
include 'partials/header.php';
?>
<div class="container py-4">
  <h2 class="mb-4">新增軟體</h2>
  <form action="store_software.php" method="post">
    <div class="mb-3">
      <label class="form-label">品牌名稱</label>
      <input type="text" name="brand" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">軟體名稱</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">描述</label>
      <textarea name="description" class="form-control" rows="4"></textarea>
    </div>
    <button type="submit" class="btn btn-success">儲存軟體</button>
    <a href="index.php" class="btn btn-secondary">取消</a>
  </form>
</div>
<?php include 'partials/footer.php'; ?>
