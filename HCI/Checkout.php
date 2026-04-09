<?php
require_once 'includes/app.php';
$pageTitle = 'Giỏ hàng';
$pageBreadcrumb = 'Giỏ hàng';

// =========================
// THÊM SẢN PHẨM VÀO GIỎ
// =========================
if (isset($_GET['add'])) {
    $bookId = (int) $_GET['add'];

    if ($bookId > 0) {
        $user = current_user();

        if (!$user) {
            // Chưa đăng nhập -> chỉ thông báo
            flash('error', 'Bạn phải đăng nhập mới có thể thêm sản phẩm vào giỏ hàng.');
        } else {
            // Đã đăng nhập -> thêm vào giỏ
            cart_add($bookId, 1);
            flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
        }
    }

    redirect('Checkout.php'); // vẫn reload trang giỏ hàng
}

// =========================
// TĂNG / GIẢM / XÓA
// =========================
$action = $_GET['action'] ?? '';
$bookIdAction = (int) ($_GET['id'] ?? 0);

if ($action === 'inc' && $bookIdAction > 0) {
    cart_add($bookIdAction, 1);
    redirect('Checkout.php');
}

if ($action === 'dec' && $bookIdAction > 0) {
    $items = cart_items();
    foreach ($items as $item) {
        if ((int) $item['book']['id'] === $bookIdAction) {
            $currentQty = (int) $item['quantity'];
            if ($currentQty <= 1) {
                cart_remove($bookIdAction);
            } else {
                cart_set_qty($bookIdAction, $currentQty - 1);
            }
            break;
        }
    }
    redirect('Checkout.php');
}

if ($action === 'remove' && $bookIdAction > 0) {
    cart_remove($bookIdAction);
    redirect('Checkout.php');
}

// =========================
// CẬP NHẬT SỐ LƯỢNG
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
    foreach ((array) $_POST['qty'] as $bookId => $qty) {
        $bookId = (int) $bookId;
        $qty = (int) $qty;

        if ($qty <= 0) {
            cart_remove($bookId);
        } else {
            cart_set_qty($bookId, $qty);
        }
    }
    flash('success', 'Đã cập nhật giỏ hàng.');
    redirect('Checkout.php');
}

// =========================
// DỮ LIỆU GIỎ HÀNG
// =========================
$items = cart_items();
$total = cart_total();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<style>
   .cart-page {
      padding: 20px 0 40px;
   }

   .cart-card,
   .summary-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(16, 24, 40, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.04);
      overflow: hidden;
   }

   .cart-card-header,
   .summary-card-header {
      padding: 22px 24px 18px;
      border-bottom: 1px solid #eef2f7;
   }

   .cart-card-body,
   .summary-card-body {
      padding: 24px;
   }

   .cart-title {
      font-size: 24px;
      font-weight: 700;
      color: #111827;
      margin: 0;
   }

   .cart-subtitle {
      color: #6b7280;
      font-size: 14px;
      margin-top: 4px;
   }

   .cart-table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
      overflow: hidden;
   }

   .cart-table thead th {
      background: #f8fafc;
      color: #111827;
      font-weight: 700;
      font-size: 14px;
      padding: 16px 14px;
      border-bottom: 1px solid #e5e7eb !important;
      white-space: nowrap;
   }

   .cart-table tbody tr {
      transition: all 0.2s ease;
   }

   .cart-table tbody tr:hover {
      background: #f9fffd;
   }

   .cart-table td {
      padding: 18px 14px;
      vertical-align: middle !important;
      border-top: 1px solid #edf2f7 !important;
      color: #374151;
      font-size: 15px;
   }

   .book-name {
      font-weight: 600;
      color: #111827;
      line-height: 1.4;
   }

   .qty-wrap {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #f8fafc;
      border: 1px solid #dbe4ee;
      border-radius: 999px;
      padding: 6px 8px;
   }

   .qty-btn {
      width: 30px;
      height: 30px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      border: none;
      font-size: 18px;
      font-weight: 700;
      text-decoration: none !important;
      transition: all 0.2s ease;
      cursor: pointer;
      user-select: none;
   }

   .qty-btn.minus {
      background: #e6f9f5;
      color: #14b8a6;
   }

   .qty-btn.minus:hover {
      background: #ccf3ee;
      transform: translateY(-1px);
   }

   .qty-btn.plus {
      background: #e6f9f5;
      color: #14b8a6;
   }

   .qty-btn.plus:hover {
      background: #ccf3ee;
      transform: translateY(-1px);
   }

   .qty-input {
      width: 54px;
      height: 34px;
      border: 1px solid #d1d5db;
      border-radius: 10px;
      text-align: center;
      font-weight: 700;
      color: #111827;
      background: #fff;
      outline: none;
      padding: 0 6px;
   }

   .qty-input:focus {
      border-color: #14b8a6;
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.14);
   }

   .price-text {
      font-weight: 600;
      color: #374151;
      white-space: nowrap;
   }

   .subtotal-text {
      font-weight: 700;
      color: #111827;
      white-space: nowrap;
   }

   .remove-btn {
      width: 38px;
      height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      border: 1px solid #ffd6d6;
      background: #fff5f5;
      color: #ef4444;
      text-decoration: none !important;
      font-size: 20px;
      font-weight: 700;
      transition: all 0.2s ease;
   }

   .remove-btn:hover {
      background: #fee2e2;
      border-color: #fca5a5;
      transform: translateY(-1px);
      color: #dc2626;
   }

   .cart-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 22px;
   }

   .btn-cart {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0 18px;
      border-radius: 12px;
      font-weight: 700;
      text-decoration: none !important;
      border: none;
      transition: all 0.2s ease;
      box-shadow: 0 8px 18px rgba(20, 184, 166, 0.14);
   }

   .btn-cart-primary {
      background: linear-gradient(135deg, #14b8a6, #22c55e);
      color: #fff;
   }

   .btn-cart-primary:hover {
      transform: translateY(-1px);
      color: #fff;
      box-shadow: 0 10px 22px rgba(20, 184, 166, 0.22);
   }

   .btn-cart-secondary {
      background: #ecfdf5;
      color: #0f766e;
      border: 1px solid #bbf7d0;
   }

   .btn-cart-secondary:hover {
      background: #d1fae5;
      color: #0f766e;
      transform: translateY(-1px);
   }

   .btn-cart-outline {
      background: #fff;
      color: #14b8a6;
      border: 1px solid #14b8a6;
      box-shadow: none;
   }

   .btn-cart-outline:hover {
      background: #14b8a6;
      color: #fff;
      transform: translateY(-1px);
   }

   .total-line {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      font-size: 15px;
      padding: 10px 0;
      color: #374151;
   }

   .total-line strong {
      color: #111827;
      font-size: 16px;
   }

   .grand-total {
      color: #fb7185 !important;
      font-size: 20px;
      font-weight: 800;
   }

   .summary-card {
      position: sticky;
      top: 90px;
   }

   .summary-title {
      font-size: 22px;
      font-weight: 800;
      color: #111827;
      margin: 0;
   }

   .empty-cart {
      text-align: center;
      padding: 46px 20px;
      border: 1px dashed #cbd5e1;
      border-radius: 16px;
      background: #f8fafc;
   }

   .empty-cart h5 {
      font-weight: 800;
      color: #111827;
      margin-bottom: 8px;
   }

   .empty-cart p {
      color: #6b7280;
      margin-bottom: 18px;
   }

   @media (max-width: 991px) {
      .summary-card {
         position: static;
         top: auto;
         margin-top: 20px;
      }

      .cart-card-body,
      .summary-card-body,
      .cart-card-header,
      .summary-card-header {
         padding-left: 18px;
         padding-right: 18px;
      }
   }

   @media (max-width: 768px) {
      .cart-title {
         font-size: 20px;
      }

      .cart-table td,
      .cart-table th {
         padding: 12px 10px;
         font-size: 14px;
      }

      .qty-wrap {
         gap: 6px;
         padding: 5px 7px;
      }

      .qty-input {
         width: 46px;
      }

      .btn-cart {
         width: 100%;
      }

      .cart-actions {
         flex-direction: column;
         align-items: stretch;
      }
   }
</style>

<div id="content-page" class="content-page">
   <?php render_flash(); ?>

   <div class="container-fluid cart-page">
      <div class="row">
         <!-- GIỎ HÀNG -->
         <div class="col-lg-8">
            <div class="cart-card">
               <div class="cart-card-header">
                  <h4 class="cart-title">Giỏ hàng của bạn</h4>
                  <div class="cart-subtitle">
                     Quản lý số lượng, cập nhật nhanh và xóa sản phẩm dễ dàng.
                  </div>
               </div>

               <div class="cart-card-body">
                  <?php if ($items): ?>
                     <form method="post" action="Checkout.php">
                        <div class="table-responsive">
                           <table class="table cart-table mb-0">
                              <thead>
                                 <tr>
                                    <th style="min-width: 240px;">Sản phẩm</th>
                                    <th class="text-center" style="min-width: 180px;">Số lượng</th>
                                    <th style="min-width: 130px;">Đơn giá</th>
                                    <th style="min-width: 140px;">Thành tiền</th>
                                    <th class="text-center" style="width: 70px;">Xóa</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <?php foreach ($items as $item): ?>
                                    <?php $book = $item['book']; ?>
                                    <tr>
                                       <td>
                                          <div class="book-name">
                                             <?php echo h($book['bookname']); ?>
                                          </div>
                                       </td>

                                       <td class="text-center">
                                          <div class="qty-wrap">
                                             <a class="qty-btn minus" href="Checkout.php?action=dec&id=<?php echo (int) $book['id']; ?>" aria-label="Giảm số lượng">−</a>

                                             <input
                                                class="qty-input"
                                                type="number"
                                                min="0"
                                                name="qty[<?php echo (int) $book['id']; ?>]"
                                                value="<?php echo (int) $item['quantity']; ?>"
                                             >

                                             <a class="qty-btn plus" href="Checkout.php?action=inc&id=<?php echo (int) $book['id']; ?>" aria-label="Tăng số lượng">+</a>
                                          </div>
                                       </td>

                                       <td class="price-text">
                                          <?php echo vn_money($item['price']); ?> đ
                                       </td>

                                       <td class="subtotal-text">
                                          <?php echo vn_money($item['subtotal']); ?> đ
                                       </td>

                                       <td class="text-center">
                                          <a class="remove-btn" href="Checkout.php?action=remove&id=<?php echo (int) $book['id']; ?>" title="Xóa sản phẩm">
                                             ×
                                          </a>
                                       </td>
                                    </tr>
                                 <?php endforeach; ?>
                              </tbody>
                           </table>
                        </div>

                        <div class="cart-actions">
                           <div class="d-flex flex-wrap gap-2">
                              <button type="submit" class="btn-cart btn-cart-primary">
                                 Cập nhật giỏ hàng
                              </button>

                              <a href="index.php" class="btn-cart btn-cart-secondary">
                                 Tiếp tục mua sắm
                              </a>
                           </div>

                           <div style="font-size: 16px; font-weight: 700; color: #374151;">
                              Tổng:
                              <span class="grand-total"><?php echo vn_money($total); ?> đ</span>
                           </div>
                        </div>
                     </form>
                  <?php else: ?>
                     <div class="empty-cart">
                        <h5>Giỏ hàng đang trống</h5>
                        <p>Hãy thêm một vài sản phẩm để bắt đầu mua sắm nhé.</p>
                        <a href="index.php" class="btn-cart btn-cart-primary">
                           Quay lại trang chủ
                        </a>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- THANH TOÁN -->
         <div class="col-lg-4">
            <div class="summary-card">
               <div class="summary-card-header">
                  <h5 class="summary-title">Thanh toán</h5>
               </div>

               <div class="summary-card-body">
                  <div class="total-line">
                     <span>Tạm tính</span>
                     <span><?php echo vn_money($total); ?> đ</span>
                  </div>

                  <hr style="border-color: #eef2f7; margin: 10px 0 14px;">

                  <div class="total-line">
                     <strong>Tổng</strong>
                     <strong class="grand-total"><?php echo vn_money($total); ?> đ</strong>
                  </div>

                  <div class="mt-4">
                     <a href="Checkout-preview.php" class="btn-cart btn-cart-outline w-100">
                        Xem đơn hàng
                     </a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php include 'includes/footer.php'; ?>