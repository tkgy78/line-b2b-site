document.addEventListener("DOMContentLoaded", function () {
  console.log("🚀 edit_modal.js 載入成功");

  const modal = new bootstrap.Modal(document.getElementById("editModal"));
  const modalBody = document.getElementById("editModalBody");

  // 綁定每個編輯按鈕
  document.querySelectorAll(".btn-edit-modal").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      console.log("🟡 點擊編輯按鈕，商品ID:", id);

      modal.show();
      document.getElementById("editModal").dataset.id = id;

      loadTab("basic", id); // 預設載入 basic 頁籤
    });
  });

  // 載入 modal 的對應內容（basic or detail）
  function loadTab(type, id) {
    console.log("🔍 正在呼叫 loadTab：", type, id);

    const url = type === "basic" ? "edit_basic_modal.php" : "edit_detail_modal.php";
    modalBody.innerHTML = '<div class="text-muted p-3">載入中...</div>';

    fetch(`${url}?id=${id}`)
      .then(res => res.text())
      .then(html => {
        modalBody.innerHTML = html;

        // 等 innerHTML 寫入完後再綁事件
        setTimeout(() => {
          const saveBtn = modalBody.querySelector('#btn-save-basic');
          if (saveBtn) {
            console.log("✅ 儲存按鈕找到");

            saveBtn.addEventListener('click', () => {
              const form = modalBody.querySelector('#form-basic');
              const formData = new FormData(form);

              console.log("🟢 送出表單內容如下：");
              for (let pair of formData.entries()) {
                console.log(pair[0] + ": " + pair[1]);
              }

              fetch('update_product_basic.php', {
                method: 'POST',
                body: formData
              })
                .then(res => res.text())
                .then(msg => {
                  console.log("🧾 伺服器回應：", msg);
                  if (msg === 'success') {
                    alert('✅ 更新成功！');
                    location.reload();
                  } else {
                    alert('❌ 更新失敗：' + msg);
                  }
                })
                .catch(err => {
                  console.error("❌ 發生錯誤：", err);
                  alert('更新失敗：' + err);
                });
            });
          } else {
            console.warn("⚠️ 沒找到儲存按鈕");
          }
        }, 100); // 延遲一點點再綁定
      })
      .catch(err => {
        console.error("❌ 載入 tab 時出錯：", err);
      });
  }

});