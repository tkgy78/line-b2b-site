document.addEventListener("DOMContentLoaded", function () {
  const modal = new bootstrap.Modal(document.getElementById("editModal"));

  document.querySelectorAll(".btn-edit-basic").forEach(btn => {
    btn.addEventListener("click", async () => {
      const id = btn.dataset.id;
      const res = await fetch(`edit_basic_modal.php?id=${id}`);
      const html = await res.text();
      document.getElementById("editModalBody").innerHTML = html;
      modal.show();

      // 綁定送出事件
      const form = document.getElementById("editBasicForm");
      form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const res = await fetch("update_basic.php", {
          method: "POST",
          body: formData
        });
        const result = await res.text();

        if (res.ok) {
          alert("更新成功");
          modal.hide();
          location.reload(); // 或只更新該筆資料
        } else {
          alert("更新失敗：" + result);
        }
      });
    });
  });
});