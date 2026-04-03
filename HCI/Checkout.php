<?php
require_once 'includes/app.php';
$pageTitle = 'Giỏ hàng';
$pageBreadcrumb = 'Giỏ hàng';

if (isset($_GET['add'])) {
    $bookId = (int) $_GET['add'];
    if ($bookId > 0) {
        cart_add($bookId, 1);
        flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }
    redirect('Checkout.php');
}
if (isset($_GET['remove'])) {
    cart_remove((int) $_GET['remove']);
    flash('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    redirect('Checkout.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
    foreach ((array) $_POST['qty'] as $bookId => $qty) {
        cart_set_qty((int) $bookId, (int) $qty);
    }
    flash('success', 'Đã cập nhật giỏ hàng.');
    redirect('Checkout.php');
}

$items = cart_items();
$total = cart_total();

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
               <div class="iq-card-header d-flex justify-content-between">
                  <h4 class="card-title mb-0">Giỏ hàng của bạn</h4>
               </div>
               <div class="iq-card-body">
                  <?php if ($items): ?>
                     <form method="post">
                        <div class="table-responsive">
                           <table class="table table-bordered table-hover mb-0">
                              <thead class="thead-light">
                                 <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center" style="width:120px;">Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                    <th style="width:60px;"></th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php foreach ($items as $item): $book = $item['book']; ?>
                                    <tr>
                                       <td>
                                          <div class="media align-items-center">
                                             <img src="<?php echo h(book_image_src($book, (int) $book['id'])); ?>" alt="" class="rounded mr-3" style="width:60px; height:60px; object-fit:cover;">
                                             <div class="media-body">
                                                <a href="book-page.php?id=<?php echo (int) $book['id']; ?>"><strong><?php echo h($book['bookname']); ?></strong></a><br>
                                                <small class="text-muted"><?php echo h($book['author_name'] ?? ''); ?></small>
                                             </div>
                                          </div>
                                       </td>
                                       <td class="text-center"><input type="number" min="1" name="qty[<?php echo (int) $book['id']; ?>]" class="form-control text-center" value="<?php echo (int) $item['quantity']; ?>"></td>
                                       <td><?php echo vn_money($item['price']); ?> đ</td>
                                       <td><?php echo vn_money($item['subtotal']); ?> đ</td>
                                       <td class="text-center"><a class="text-danger" href="Checkout.php?remove=<?php echo (int) $book['id']; ?>">×</a></td>
                                    </tr>
                                 <?php endforeach; ?>
                              </tbody>
                           </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                           <button class="btn btn-outline-primary">Cập nhật</button>
                           <strong>Tổng: <?php echo vn_money($total); ?> đ</strong>
                        </div>
                     </form>
                  <?php else: ?>
                     <div class="alert alert-info mb-0">Giỏ hàng đang trống.</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
         <div class="col-lg-4">
            <div class="iq-card">
               <div class="iq-card-body">
                  <h5>Thanh toán</h5>
                  <div class="d-flex justify-content-between mt-2"><span>Tạm tính</span><span><?php echo vn_money($total); ?> đ</span></div>
                  <div class="d-flex justify-content-between mt-2"><span>Phí vận chuyển</span><span>Miễn phí</span></div>
                  <hr>
                  <div class="d-flex justify-content-between"><strong>Tổng</strong><strong class="text-danger"><?php echo vn_money($total); ?> đ</strong></div>
                  <div class="mt-4">
                     <?php if (is_logged_in()): ?>
                        <a href="Checkout-address.php" class="btn btn-primary btn-block mb-2">Chọn địa chỉ</a>
                        <a href="Checkout-preview.php" class="btn btn-outline-primary btn-block">Xem lại đơn hàng</a>
                     <?php else: ?>
                        <a href="sign-in.php" class="btn btn-primary btn-block mb-2">Đăng nhập để thanh toán</a>
                        <a href="sign-up.php" class="btn btn-outline-primary btn-block">Tạo tài khoản</a>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
