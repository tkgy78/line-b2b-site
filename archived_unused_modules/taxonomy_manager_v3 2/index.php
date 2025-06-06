<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>分類管理 V4</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <div class="row g-4">
      <!-- 品牌 -->
      <div class="col-12 col-md-4">
        <h4>品牌</h4>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>名稱</th>
              <th class="text-end">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>MOSCONI</td>
              <td class="text-end">
                <a href="#" class="btn btn-sm btn-primary">編輯</a>
                <a href="#" class="btn btn-sm btn-danger">刪除</a>
              </td>
            </tr>
            <tr>
              <td>GLADEN</td>
              <td class="text-end">
                <a href="#" class="btn btn-sm btn-primary">編輯</a>
                <a href="#" class="btn btn-sm btn-danger">刪除</a>
              </td>
            </tr>
          </tbody>
        </table>
        <form class="input-group mb-4" method="POST">
          <input type="text" name="brand" class="form-control" placeholder="新增品牌">
          <button type="submit" class="btn btn-success">送出</button>
        </form>
      </div>

      <!-- 系列 -->
      <div class="col-12 col-md-4">
        <h4>系列</h4>
        <form class="mb-2">
          <select class="form-select mb-2" name="brand_for_series">
            <option value="MOSCONI">MOSCONI</option>
            <option value="GLADEN">GLADEN</option>
          </select>
        </form>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>系列名稱</th>
              <th class="text-end">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>A Class</td>
              <td class="text-end">
                <a href="#" class="btn btn-sm btn-primary">編輯</a>
                <a href="#" class="btn btn-sm btn-danger">刪除</a>
              </td>
            </tr>
          </tbody>
        </table>
        <form class="input-group mb-4" method="POST">
          <input type="text" name="series" class="form-control" placeholder="新增系列">
          <button type="submit" class="btn btn-success">送出</button>
        </form>
      </div>

      <!-- 分類 -->
      <div class="col-12 col-md-4">
        <h4>分類</h4>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>分類名稱</th>
              <th class="text-end">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>擴大機</td>
              <td class="text-end">
                <a href="#" class="btn btn-sm btn-primary">編輯</a>
                <a href="#" class="btn btn-sm btn-danger">刪除</a>
              </td>
            </tr>
            <tr>
              <td>喇叭</td>
              <td class="text-end">
                <a href="#" class="btn btn-sm btn-primary">編輯</a>
                <a href="#" class="btn btn-sm btn-danger">刪除</a>
              </td>
            </tr>
          </tbody>
        </table>
        <form class="input-group mb-4" method="POST">
          <input type="text" name="category" class="form-control" placeholder="新增分類">
          <button type="submit" class="btn btn-success">送出</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
