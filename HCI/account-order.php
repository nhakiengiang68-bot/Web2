<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Đơn hàng của tôi';
$pageBreadcrumb = 'Đơn hàng của tôi';
$user = current_user();
$orders = fetch_all('SELECT * FROM orders WHERE user_id = ' . (int) $user['id'] . ' ORDER BY `date` DESC, id DESC');

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card">
               <div class="iq-card-header d-flex justify-content-between"><h4 class="card-title mb-0">Đơn hàng của tôi</h4></div>
               <div class="iq-card-body">
                  <?php if ($orders): ?>
                     <?php foreach ($orders as $order): ?>
                        <?php $items = order_items_for((int) $order['id']); $first = $items[0]['book_code'] ?? ''; ?>
                        <a href="Checkout-success.php?id=<?php echo (int) $order['id']; ?>" class="d-block mb-3">
                           <div class="media align-items-center border rounded p-3">
                              <div class="col-sm-1 p-0"><img width="90" height="90" class="img-fluid rounded" src="<?php echo h(isset($items[0]) ? book_image_src($items[0], (int) ($items[0]['book_id'] ?? 1)) : 'images/checkout/01.jpg'); ?>" alt=""></div>
                              <div class="col-sm-5">
                                 <h6 class="mb-1"><?php echo h($items[0]['bookname'] ?? ''); ?></h6>
                                 <p class="mb-1">x<?php echo count($items); ?></p>
                                 <small class="text-muted">Mã đơn: #DH<?php echo str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT); ?></small>
                              </div>
                              <div class="col-sm-4"><p class="mb-0">Tổng tiền: <strong><?php echo vn_money($order['price']); ?> đ</strong></p></div>
                              <div class="col-sm-2 text-right"><p class="mb-0 <?php echo $order['status'] === 'delivered' ? 'text-primary' : ($order['status'] === 'cancelled' ? 'text-danger' : 'text-warning'); ?>"><?php echo h($order['status']); ?></p></div>
                           </div>
                        </a>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">Bạn chưa có đơn hàng nào.</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
