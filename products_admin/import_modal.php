<form enctype="multipart/form-data" id="form-import-csv">
  <div class="mb-3">
    <label for="csv_file" class="form-label">選擇 CSV 檔案</label>
    <input type="file" name="csv_file" id="csv_file" accept=".csv" class="form-control" required>
  </div>
  <div class="text-end">
    <button type="submit" class="btn btn-primary">開始匯入</button>
  </div>
</form>

<script>
setTimeout(() => {
  const form = document.getElementById('form-import-csv');
  if (!form) return;

  form.addEventListener('submit', async function(e) {
    e.preventDefault(); // ✅ 阻止表單預設送出行為

    const formData = new FormData(form);

    try {
      const res = await fetch('import_csv.php', {
        method: 'POST',
        body: formData
      });

      const text = await res.text();
      let result;

      try {
        result = JSON.parse(text);
      } catch (err) {
        alert('⚠️ 回傳格式錯誤，請檢查 import_csv.php 是否正確輸出 JSON');
        return;
      }

      if (result.success) {
        // ✅ 顯示 alert 後才 reload
        alert(result.message || '✅ 匯入成功！');

        // 關閉 Modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('importCsvModal'));
        if (modal) modal.hide();

        // 清除表單重送提示
        if (history.replaceState) {
          history.replaceState(null, '', location.href);
        }

        // 使用者按下 alert 確定後再跳轉
        setTimeout(() => location.reload(), 200);
      } else {
        let msg = result.message || '❌ 匯入失敗';
        if (result.errors?.length) {
          msg += '\n\n錯誤詳情：\n' + result.errors.join('\n');
        }
        alert(msg);
      }

    } catch (err) {
      alert('❌ 匯入時發生錯誤：' + err.message);
    }
  });
}, 100);
</script>