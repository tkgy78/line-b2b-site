<form action="import_csv.php" method="post" enctype="multipart/form-data" id="form-import-csv">
  <div class="mb-3">
    <label for="csv_file" class="form-label">選擇 CSV 檔案</label>
    <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
  </div>
  <div class="text-end">
    <button type="submit" class="btn btn-primary">開始匯入</button>
  </div>
</form>

<script>
document.getElementById('form-import-csv').addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const res = await fetch('import_csv.php', {
    method: 'POST',
    body: formData
  });
  const text = await res.text();
  alert(text);
  if (text.trim().startsWith('匯入成功')) {
    const modal = bootstrap.Modal.getInstance(document.getElementById('importCsvModal'));
    if (modal) modal.hide();
    setTimeout(() => location.reload(), 500);
  }
});
</script>