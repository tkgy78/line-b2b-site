<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $page_title ?? '管理後台' ?></title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- 自訂樣式：手機字體縮小 -->
  <style>
    @media (max-width: 576px){
      table { font-size: .80rem; }
    }
  </style>
</head>

<body class="p-3">

<!-- Bootstrap 5 JS（含 Popper，用於 tab、modal 等功能） -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- CKEditor 載入與啟用 -->
<script src="/line_b2b/vendor/ckeditor/ckeditor.js"></script>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('#description')) {
      CKEDITOR.replace('description', { height: 300 });
    }
  });
</script>