document.addEventListener("DOMContentLoaded", function () {
  const modal = new bootstrap.Modal(document.getElementById("editModal"));
  const modalBody = document.getElementById("editModalBody");

  // 點擊編輯按鈕
  document.querySelectorAll(".btn-edit-modal").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      modal.show();
      loadTab("basic", id);
    });
  });

  // 點擊 tabs
  document.querySelectorAll("#editTab .nav-link").forEach(tab => {
    tab.addEventListener("click", () => {
      const type = tab.dataset.tab;
      const id = document.querySelector(".btn-edit-modal[data-id]")?.dataset.id;
      document.querySelectorAll("#editTab .nav-link").forEach(t => t.classList.remove("active"));
      tab.classList.add("active");
      loadTab(type, id);
    });
  });

  function loadTab(type, id) {
    let url = type === "basic" ? "edit_basic_modal.php" : "edit_detail_modal.php";
    fetch(`${url}?id=${id}`)
      .then(res => res.text())
      .then(html => modalBody.innerHTML = html)
      .catch(err => modalBody.innerHTML = `<div class="text-danger">載入失敗：${err}</div>`);
  }
});