document.addEventListener("DOMContentLoaded", function () {
  const modal = new bootstrap.Modal(document.getElementById("editModal"));
  const modalBody = document.getElementById("editModalBody");

  document.querySelectorAll(".btn-edit-modal").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      modal.show();
      document.getElementById("editModal").dataset.id = id;
      loadTab("basic", id);
    });
  });

  function loadTab(type, id) {
    const url = type === "basic" ? "edit_basic_modal.php" : "edit_detail_modal.php";
    modalBody.innerHTML = '<div class="text-muted p-3">載入中...</div>';
    fetch(`${url}?id=${id}`)
      .then(res => res.text())
      .then(html => {
        modalBody.innerHTML = html;
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
                if (msg === 'success') {
                  alert('更新成功！');
                  location.reload();
                } else {
                  alert('更新失敗：' + msg);
                }
              })
              .catch(err => alert('更新失敗：' + err));
          });
        } else {
          console.warn("❌ 沒找到儲存按鈕");
        }
      });
  }
});