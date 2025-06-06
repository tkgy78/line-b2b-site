<?php
require_once __DIR__.'/../db.php';
$id = (int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT * FROM brands WHERE id=?");
$stmt->execute([$id]);
$brand=$stmt->fetch();
if(!$brand){exit('品牌不存在');}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name']);
  $logo=$brand['logo']?:'no-logo.png';
  if(isset($_FILES['logo']) && $_FILES['logo']['error']==UPLOAD_ERR_OK){
    $ext=pathinfo($_FILES['logo']['name'],PATHINFO_EXTENSION);
    $safe=preg_replace('/[^a-zA-Z0-9_\-]/','_', $name);
    $logo=$safe.'_'.time().'.'.$ext;
    move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__.'/../uploads/brand_logos/'.$logo);
  }
  $pdo->prepare("UPDATE brands SET name=?,logo=? WHERE id=?")->execute([$name,$logo,$id]);
  header('Location: index.php');exit;
}
?>
<!DOCTYPE html><html lang="zh-Hant"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>編輯品牌</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="p-4">
<div class="container" style="max-width:480px">
<h4 class="mb-3">編輯品牌</h4>
<form method="POST" enctype="multipart/form-data" class="vstack gap-3">
  <div><label class="form-label">品牌名稱</label>
    <input type="text" name="name" class="form-control" value="<?=htmlspecialchars($brand['name']);?>" required></div>
  <div><label class="form-label">品牌 Logo (可留空)</label><br>
    <?php if($brand['logo'] && $brand['logo']!=='no-logo.png'):?>
      <img src="/uploads/brand_logos/<?=htmlspecialchars($brand['logo']);?>" style="max-height:60px" class="mb-2"><br>
    <?php endif;?>
    <input type="file" name="logo" class="form-control" accept="image/*"></div>
  <div class="d-flex gap-2">
    <button class="btn btn-success flex-grow-1">儲存</button>
    <a href="index.php" class="btn btn-secondary flex-grow-1">取消</a>
  </div>
</form></div></body></html>
