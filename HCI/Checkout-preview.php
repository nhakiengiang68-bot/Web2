<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Xem lại đơn đặt hàng';
$pageBreadcrumb = 'Xem lại đơn đặt hàng';
$user = current_user();

if (isset($_GET['address_id'])) {
    $_SESSION['selected_address_id'] = (int) $_GET['address_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $addressId = (int) ($_SESSION['selected_address_id'] ?? 0);
    $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'Thanh toán khi nhận hàng'));
    $note = trim((string) ($_POST['note'] ?? ''));
    $orderId = create_order_from_cart((int) $user['id'], $addressId, $paymentMethod, $note);
    if ($orderId) {
        redirect('Checkout-success.php?id=' . $orderId);
    }
}

$items = cart_items();
$total = cart_total();
$address = null;
$selectedAddressId = (int) ($_SESSION['selected_address_id'] ?? 0);
if ($selectedAddressId > 0) {
    $address = fetch_one('SELECT * FROM address WHERE id = ' . $selectedAddressId . ' AND user_id = ' . (int) $user['id'] . ' LIMIT 1');
}
if (!$address) {
    $address = default_address((int) $user['id']);
    if ($address) {
        $_SESSION['selected_address_id'] = (int) $address['id'];
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-8">
            <div class="iq-card">
               <div class="iq-card-header d-flex justify-content-between"><h4 class="card-title mb-0">Sản phẩm</h4></div>
               <div class="iq-card-body">
                  <?php if ($items): ?>
                     <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                           <thead class="thead-light"><tr><th>#</th><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                           <tbody>
                              <?php foreach ($items as $i => $item): $book = $item['book']; ?>
                                 <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><?php echo h($book['bookname']); ?></td>
                                    <td><?php echo (int) $item['quantity']; ?></td>
                                    <td><?php echo vn_money($item['price']); ?> đ</td>
                                    <td><?php echo vn_money($item['subtotal']); ?> đ</td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">Giỏ hàng đang trống.</div>
                  <?php endif; ?>
               </div>
            </div>

            <div class="iq-card mt-3">
               <div class="iq-card-body">
                  <div class="row">
                     <div class="col-md-6">
                        <h6>Thông tin nhận hàng</h6>
                        <?php if ($address): ?>
                           <p class="mb-1"><strong>Người nhận:</strong> <?php echo h($address['receiver_name']); ?></p>
                           <p class="mb-1"><strong>SĐT:</strong> <?php echo h($address['phone']); ?></p>
                           <p class="mb-0"><strong>Địa chỉ:</strong> <?php echo h($address['address_detail'] . ', ' . $address['ward'] . ', ' . $address['district'] . ', ' . $address['province']); ?></p>
                        <?php else: ?>
                           <p class="text-danger mb-0">Chưa có địa chỉ giao hàng. Hãy thêm địa chỉ ở bước trước.</p>
                        <?php endif; ?>
                     </div>
                     <div class="col-md-6">
                        <h6>Phương thức thanh toán</h6>
                        <p class="mb-1"><strong>Thanh toán:</strong> Thanh toán khi nhận hàng</p>
                        <p class="mb-0"><strong>Ghi chú:</strong> </p>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-lg-4">
            <div class="iq-card">
               <div class="iq-card-body">
                  <h5>Chi tiết đơn hàng</h5>
                  <div class="d-flex justify-content-between mt-2"><span>Tạm tính</span><span><?php echo vn_money($total); ?> đ</span></div>
                  <div class="d-flex justify-content-between mt-2"><span>Giảm giá</span><span class="text-success">0 đ</span></div>
                  <div class="d-flex justify-content-between mt-2"><span>Thuế VAT</span><span>0 đ</span></div>
                  <div class="d-flex justify-content-between mt-2"><span>Phí vận chuyển</span><span class="text-success">Miễn phí</span></div>
                  <hr>
                  <div class="d-flex justify-content-between"><span class="font-weight-bold">Tổng</span><span class="font-weight-bold text-danger"><?php echo vn_money($total); ?> đ</span></div>
                  <form method="post" class="mt-4">
                     <input type="hidden" name="confirm_order" value="1">
                     <div class="form-group"><label>Phương thức thanh toán</label><select name="payment_method" class="form-control"><option value="Thanh toán khi nhận hàng">Thanh toán khi nhận hàng</option></select></div>
                     <div class="form-group"><label>Ghi chú</label><textarea name="note" rows="3" class="form-control"></textarea></div>
                     <button class="btn btn-primary btn-block" <?php echo (!$items || !$address) ? 'disabled' : ''; ?>>Xác nhận đặt hàng</button>
                  </form>
                  <a href="Checkout.php" class="btn btn-secondary btn-block mt-2">Quay lại giỏ hàng</a>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
