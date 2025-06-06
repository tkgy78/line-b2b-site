document.addEventListener("DOMContentLoaded", function () {
  const modal = new bootstrap.Modal(document.getElementById("editModal"));
  const modalBody = document.getElementById("editModalBody");

  // 點擊「編輯」按鈕 → 開啟 modal，載入基本資料
  document.querySelectorAll(".btn-edit-modal").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      modal.show();
      document.getElementById("editModal").dataset.id = id;
      loadTab("basic", id);
    });
  });

  // 切換 Tabs
  document.querySelectorAll("#editTab .nav-link").forEach(tab => {
    tab.addEventListener("click", () => {
      const type = tab.dataset.tab;
      const id = document.getElementById("editModal").dataset.id;
      document.querySelectorAll("#editTab .nav-link").forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      loadTab(type, id);
    });
  });

  // 動態載入分頁內容
  function loadTab(type, id) {
    let url = type === "basic" ? "edit_basic_modal.php" : "edit_detail_modal.php";
    fetch(`${url}?id=${id}`)
      .then(res => res.text())
      .then(html => {
        modalBody.innerHTML = html;

        // ✅ 綁定儲存按鈕（只綁一次）
        const saveBtn = modalBody.querySelector('#btn-save-basic');
        if (saveBtn) {
          saveBtn.addEventListener('click', () => {
            const form = modalBody.querySelector('#form-basic');
            const formData = new FormData(form);

            fetch('update_product_basic.php', {
              method: 'POST',
              body: formData
            })
              .then(res => res.text())
              .then(msg => {
                if (msg === 'success') {
                  alert('更新成功！');
                  location.reload();
                } else {
                  alert('更新失敗：' + msg);
                }
              })
              .catch(err => alert('更新失敗：' + err));
          });
        }
      })
      .catch(err => {
        modalBody.innerHTML = `<div class="text-danger">載入失敗：${err}</div>`;
      });
  }

  // 品牌變動時，動態更新系列選單
  document.addEventListener('change', function (e) {
    if (e.target.name === 'brand_id') {
      const brandId = e.target.value;
      fetch(`edit_basic_modal.php?brand_id_only=1&brand_id=${brandId}`)
        .then(res => res.json())
        .then(seriesList => {
          const seriesSelect = document.querySelector('[name="series_id"]');
          seriesSelect.innerHTML = '<option value="">--無--</option>';
          seriesList.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            seriesSelect.appendChild(opt);
          });
        });
    }
  });
});