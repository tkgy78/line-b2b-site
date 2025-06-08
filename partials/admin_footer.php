<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('description', {
    height: 300
  });
</script>
<!-- 雙分頁 Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">編輯商品</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <!-- 導覽 Tabs -->
      <ul class="nav nav-tabs px-3 pt-3" id="editTab">
        <li class="nav-item">
          <a class="nav-link active" data-tab="basic" href="javascript:void(0);">基本資料</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="detail" href="javascript:void(0);">商品詳情</a>
        </li>
      </ul>

      <div class="modal-body" id="editModalBody">
        <!-- 這裡會透過 JS 載入內容 -->
      </div>
    </div>
  </div>
</div>
<script src="assets/js/edit_modal.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html>