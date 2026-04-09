<?php
require_once 'includes/app.php';
$pageTitle = 'Đặt hàng thành công';
$pageBreadcrumb = 'Đặt hàng thành công';
$orderId = (int) ($_GET['id'] ?? last_order_id() ?? 0);
$order = $orderId > 0 ? order_summary($orderId) : null;
$items = $orderId > 0 ? order_items_for($orderId) : [];

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<style>
   :root {
      --accent: #14b8a6;
      --accent-dark: #0f9b8e;
      --accent-soft: #e6fffb;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --line: #e5e7eb;
      --surface: #ffffff;
      --surface-soft: #f8fafc;
      --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
   }

   .success-shell {
      padding: 18px 0 8px;
   }

   .success-card {
      border: 0;
      border-radius: 22px;
      overflow: hidden;
      background: var(--surface);
      box-shadow: var(--shadow);
   }

   .success-card .iq-card-body {
      padding: 34px 24px;
   }

   .success-badge {
      width: 92px;
      height: 92px;
      margin: 0 auto 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #e6fffb 0%, #d9fdfa 100%);
      border: 1px solid rgba(20, 184, 166, 0.15);
      box-shadow: 0 10px 25px rgba(20, 184, 166, 0.12);
   }

   .success-badge i {
      font-size: 52px;
      color: var(--accent);
      line-height: 1;
   }

   .success-title {
      font-size: 30px;
      font-weight: 900;
      color: var(--text-main);
      margin-bottom: 10px;
   }

   .success-subtitle {
      color: var(--text-muted);
      font-size: 15px;
      line-height: 1.7;
      margin-bottom: 0;
   }

   .order-code-box {
      margin: 22px auto 0;
      max-width: 760px;
      border-radius: 18px;
      background: linear-gradient(135deg, #f8fffe 0%, #f8fafc 100%);
      border: 1px solid #e2e8f0;
      padding: 18px 20px;
      text-align: left;
   }

   .order-code-box .label {
      font-size: 13px;
      font-weight: 700;
      color: var(--text-muted);
      margin-bottom: 6px;
      display: block;
   }

   .order-code-box .code {
      font-size: 20px;
      font-weight: 900;
      color: var(--text-main);
      letter-spacing: 0.3px;
   }

   .order-code-box .code strong {
      color: var(--accent-dark);
   }

   .info-card {
      border: 0;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
      background: #fff;
   }

   .info-card .card-body {
      padding: 20px;
   }

   .info-card h6 {
      font-size: 16px;
      font-weight: 800;
      color: var(--text-main);
      margin-bottom: 16px;
   }

   .info-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 16px;
      padding: 10px 0;
      border-bottom: 1px solid #eef2f7;
      color: #334155;
      font-size: 14px;
   }

   .info-row:last-child {
      border-bottom: 0;
      padding-bottom: 0;
   }

   .info-row strong {
      color: var(--text-main);
      min-width: 140px;
   }

   .summary-highlight {
      border-radius: 16px;
      padding: 14px 16px;
      background: var(--surface-soft);
      border: 1px solid #e2e8f0;
      margin-top: 12px;
   }

   .summary-highlight .value {
      font-size: 18px;
      font-weight: 900;
      color: #0f172a;
   }

   .summary-highlight .value.accent {
      color: var(--accent-dark);
   }

   .btn-soft {
      border-radius: 12px;
      font-weight: 700;
      padding: 11px 18px;
      transition: 0.2s ease;
   }

   .btn-soft:hover {
      transform: translateY(-1px);
   }

   .btn-primary {
      background: var(--accent);
      border-color: var(--accent);
   }

   .btn-primary:hover,
   .btn-primary:focus,
   .btn-primary:active {
      background: var(--accent-dark) !important;
      border-color: var(--accent-dark) !important;
   }

   .btn-outline-primary {
      color: var(--accent-dark);
      border-color: rgba(20, 184, 166, 0.35);
      background: #fff;
   }

   .btn-outline-primary:hover,
   .btn-outline-primary:focus,
   .btn-outline-primary:active {
      background: var(--accent-soft) !important;
      color: var(--accent-dark) !important;
      border-color: rgba(20, 184, 166, 0.45) !important;
   }

   .action-box {
      max-width: 520px;
      margin: 26px auto 0;
   }

   .muted-note {
      color: var(--text-muted);
      font-size: 13px;
      margin-top: 14px;
   }

   .decor-line {
      width: 120px;
      height: 4px;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--accent) 0%, #7ee7dd 100%);
      margin: 0 auto 18px;
   }

   .empty-panel {
      border-radius: 18px;
      padding: 18px;
      background: var(--surface-soft);
      border: 1px dashed #cbd5e1;
      color: var(--text-muted);
   }

   .empty-panel strong {
      color: var(--text-main);
   }
</style>

<div id="content-page" class="content-page">
   <div class="container-fluid success-shell">
      <div class="row">
         <div class="col-12">
            <div class="iq-card success-card">
               <div class="iq-card-body text-center">
                  <div class="decor-line"></div>

                  <div class="success-badge">
                     <i class="ri-checkbox-circle-line"></i>
                  </div>

                  <h3 class="success-title">Đặt hàng thành công!</h3>
                  <p class="success-subtitle">
                     Đơn hàng của bạn đã được ghi nhận và đang chờ xử lý.
                  </p>

                  <?php if ($order): ?>
                     <div class="order-code-box text-left">
                        <span class="label">Mã đơn hàng</span>
                        <div class="code">#DH<?php echo str_pad((string) $orderId, 6, '0', STR_PAD_LEFT); ?></div>
                        <div class="muted-note mb-0 mt-2">
                           Bạn có thể theo dõi trong mục <strong>Lịch sử mua hàng</strong>.
                        </div>
                     </div>

                     <div class="row justify-content-center mt-4">
                        <div class="col-lg-8 col-md-10">
                           <div class="info-card">
                              <div class="card-body text-left">
                                 <h6>Thông tin đơn hàng</h6>

                                 <div class="info-row">
                                    <strong>Số sản phẩm</strong>
                                    <span><?php echo count($items); ?></span>
                                 </div>

                                 <div class="info-row">
                                    <strong>Tổng thanh toán</strong>
                                    <span class="summary-highlight" style="margin:0; padding:8px 12px;">
                                       <span class="value accent"><?php echo vn_money($order['price']); ?> đ</span>
                                    </span>
                                 </div>

                                 <div class="info-row">
                                    <strong>Phương thức</strong>
                                    <span><?php echo h($order['payment_method']); ?></span>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  <?php else: ?>
                     <div class="row justify-content-center mt-4">
                        <div class="col-lg-7 col-md-9">
                           <div class="empty-panel">
                              <strong>Không tìm thấy đơn hàng gần nhất.</strong>
                              <div class="mt-1">Vui lòng kiểm tra lại lịch sử mua hàng nếu cần.</div>
                           </div>
                        </div>
                     </div>
                  <?php endif; ?>

                  <div class="action-box">
                     <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                           <a href="account-order.php" class="btn btn-outline-primary btn-soft btn-block">
                              Xem đơn hàng của tôi
                           </a>
                        </div>
                        <div class="col-md-6">
                           <a href="index.php" class="btn btn-primary btn-soft btn-block">
                              Tiếp tục mua sắm
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php include 'includes/footer.php'; ?>