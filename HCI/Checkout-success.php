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
<div id="content-page" class="content-page">
   <div class="container-fluid">
      <div class="row">
         <div class="col-12">
            <div class="iq-card">
               <div class="iq-card-body text-center">
                  <div class="mb-4"><i class="ri-checkbox-circle-line" style="font-size:48px; color:green;"></i></div>
                  <h3>Đặt hàng thành công!</h3>
                  <?php if ($order): ?>
                     <p class="mb-2">Mã đơn hàng của bạn: <strong>#DH<?php echo str_pad((string) $orderId, 6, '0', STR_PAD_LEFT); ?></strong></p>
                     <p class="mb-4">Đơn hàng đã được lưu vào cơ sở dữ liệu. Bạn có thể xem trong mục "Đơn hàng của tôi".</p>
                     <div class="row justify-content-center">
                        <div class="col-md-7">
                           <div class="card mb-3"><div class="card-body text-left"><h6>Thông tin đơn hàng</h6><p class="mb-1"><strong>Số sản phẩm:</strong> <?php echo count($items); ?></p><p class="mb-1"><strong>Tổng thanh toán:</strong> <?php echo vn_money($order['price']); ?> đ</p><p class="mb-0"><strong>Phương thức:</strong> <?php echo h($order['payment_method']); ?></p></div></div>
                        </div>
                     </div>
                  <?php else: ?>
                     <p class="mb-4">Không tìm thấy đơn hàng gần nhất.</p>
                  <?php endif; ?>
                  <div class="row justify-content-center">
                     <div class="col-md-6">
                        <a href="account-order.php" class="btn btn-outline-primary btn-block mb-2">Xem đơn hàng của tôi</a>
                        <a href="index.php" class="btn btn-primary btn-block">Tiếp tục mua sắm</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
