<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $page_title ?? '管理後台' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* 手機字再小一些 */
    @media (max-width: 576px){
      table{font-size:.80rem;}
    }
  </style>
</head>
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
  if (document.querySelector('#description')) {
    CKEDITOR.replace('description', { height: 300 });
  }
</script>
<body class="p-3">