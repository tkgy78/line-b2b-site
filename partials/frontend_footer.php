<?php
// 🔰 取得使用者角色與目前頁面名稱
session_start();
$user_role = $_SESSION['user']['role'] ?? 'guest';  // 可能是 guest, vip, vvip, staff, admin, distributor 等
$current_page = basename($_SERVER['PHP_SELF']);     // 例如 index.php
?>

<!-- ✅ Bootstrap 與圖示 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- ✅ 避免圖文選單遮擋內容 -->
<style>
body {
  padding-bottom: 130px;
}
.footer-line-menu {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 120px;
  background-color: #000;
  display: flex;
  flex-wrap: wrap;
  z-index: 1000;
}
.footer-line-menu a {
  width: 33.33%;
  height: 50%;
  display: block;
  background-image: url('/assets/menu/line_menu.jpg');
  background-size: 300%;
  background-repeat: no-repeat;
  text-indent: -9999px;
}
.footer-line-menu a:nth-child(1) { background-position: 0% 0%; }
.footer-line-menu a:nth-child(2) { background-position: 50% 0%; }
.footer-line-menu a:nth-child(3) { background-position: 100% 0%; }
.footer-line-menu a:nth-child(4) { background-position: 0% 100%; }
.footer-line-menu a:nth-child(5) { background-position: 50% 100%; }
.footer-line-menu a:nth-child(6) { background-position: 100% 100%; }
</style>

<!-- ✅ 動態切換圖文選單（根據角色與頁面） -->
<?php
// 💡 如果要收合式選單，可針對某些頁面處理
$collapsed_pages = ['products_detail.php', 'profile.php']; // 需要收合的頁面
$is_collapsible = in_array($current_page, $collapsed_pages);
?>

<?php if ($is_collapsible): ?>
  <!-- 🔽 收合式選單 -->
  <div id="lineMenuCollapse" class="footer-line-menu d-none"></div>
  <button class="btn btn-secondary position-fixed bottom-0 end-0 m-3 z-3" onclick="toggleLineMenu()">選單</button>
  <script>
    function toggleLineMenu() {
      const menu = document.getElementById('lineMenuCollapse');
      menu.classList.toggle('d-none');
    }
  </script>
<?php else: ?>
  <!-- 📌 展開式圖文選單 -->
  <div class="footer-line-menu">
    <?php if ($user_role === 'vip' || $user_role === 'vvip'): ?>
      <a href="/frontend/products/index.php" title="商品">商品</a>
      <a href="/frontend/orders/history.php" title="訂單查詢">訂單</a>
      <a href="/frontend/profile/index.php" title="會員中心">會員</a>
      <a href="/frontend/rewards/index.php" title="點數">點數</a>
      <a href="/frontend/news/index.php" title="最新消息">消息</a>
      <a href="/frontend/contact.php" title="聯絡我們">聯絡</a>
    <?php elseif ($user_role === 'staff' || $user_role === 'distributor'): ?>
      <a href="/frontend/dashboard.php" title="工作台">工作台</a>
      <a href="/frontend/customers/index.php" title="客戶管理">客戶</a>
      <a href="/frontend/reports/index.php" title="報表">報表</a>
      <a href="/frontend/products/index.php" title="商品">商品</a>
      <a href="/frontend/orders/index.php" title="訂單">訂單</a>
      <a href="/frontend/profile/index.php" title="個人">我</a>
    <?php elseif ($user_role === 'admin'): ?>
      <a href="/backend/index.php" title="後台管理">管理</a>
      <a href="/frontend/profile/index.php" title="帳號">帳號</a>
      <a href="/frontend/reports/index.php" title="分析">分析</a>
      <a href="/frontend/logs.php" title="日誌">日誌</a>
      <a href="/frontend/news/index.php" title="公告">公告</a>
      <a href="/frontend/settings.php" title="設定">設定</a>
    <?php else: ?>
      <!-- guest or 一般使用者 -->
      <a href="/frontend/products/index.php" title="商品">商品</a>
      <a href="/frontend/news/index.php" title="最新消息">消息</a>
      <a href="/frontend/login.php" title="登入">登入</a>
      <a href="/frontend/register.php" title="註冊">註冊</a>
      <a href="/frontend/about.php" title="關於我們">關於</a>
      <a href="/frontend/contact.php" title="聯絡">聯絡</a>
    <?php endif; ?>
  </div>
<?php endif; ?>