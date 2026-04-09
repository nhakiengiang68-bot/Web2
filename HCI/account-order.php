<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Lịch sử mua hàng';
$pageBreadcrumb = 'Lịch sử mua hàng';
$user = current_user();

$cancelMessage = '';
$cancelMessageType = 'info';

/*
|-----------------------------------------------------------
| XỬ LÝ HỦY ĐƠN
| Chỉ cho hủy khi đơn còn ở trạng thái chưa xử lý
|-----------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($orderId <= 0) {
        $cancelMessage = 'Đơn hàng không hợp lệ.';
        $cancelMessageType = 'danger';
    } else {
        $orderRows = fetch_all(
            'SELECT id, status 
             FROM orders 
             WHERE id = ' . $orderId . ' 
               AND user_id = ' . (int) $user['id'] . ' 
             LIMIT 1'
        );

        $orderToCancel = $orderRows[0] ?? null;

        if (!$orderToCancel) {
            $cancelMessage = 'Không tìm thấy đơn hàng cần hủy.';
            $cancelMessageType = 'danger';
        } else {
            $currentStatus = strtolower(trim((string) ($orderToCancel['status'] ?? '')));
            $canCancel = in_array($currentStatus, ['pending', 'new', 'unprocessed'], true);

            if (!$canCancel) {
                $cancelMessage = 'Đơn hàng đã được xử lý nên không thể hủy.';
                $cancelMessageType = 'warning';
            } else {
                $sqlUpdate = "
                    UPDATE orders
                    SET status = 'cancelled'
                    WHERE id = " . $orderId . "
                      AND user_id = " . (int) $user['id'] . "
                    LIMIT 1
                ";

                $updated = false;

                if (function_exists('db_query')) {
                    $updated = db_query($sqlUpdate) !== false;
                } elseif (function_exists('execute_query')) {
                    $updated = execute_query($sqlUpdate) !== false;
                } elseif (isset($conn) && $conn instanceof mysqli) {
                    $updated = mysqli_query($conn, $sqlUpdate) !== false;
                } elseif (isset($mysqli) && $mysqli instanceof mysqli) {
                    $updated = mysqli_query($mysqli, $sqlUpdate) !== false;
                }

                if ($updated) {
                    $cancelMessage = 'Hủy đơn thành công.';
                    $cancelMessageType = 'success';
                } else {
                    $cancelMessage = 'Hủy đơn thất bại. Vui lòng thử lại.';
                    $cancelMessageType = 'danger';
                }
            }
        }
    }
}

$orders = fetch_all(
    'SELECT * FROM orders WHERE user_id = ' . (int) $user['id'] . ' ORDER BY `date` DESC, id DESC'
);

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
      --surface: #ffffff;
      --surface-soft: #f8fafc;
      --border: #e2e8f0;
      --danger: #dc2626;
      --warning: #d97706;
      --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
   }

   .history-shell {
      padding-top: 8px;
      padding-bottom: 8px;
   }

   .history-card {
      border: 0;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow);
      background: var(--surface);
   }

   .history-card .iq-card-header {
      background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
      border-bottom: 1px solid #eef2f7;
      padding: 18px 22px;
   }

   .history-card .card-title {
      margin: 0;
      font-size: 20px;
      font-weight: 900;
      color: var(--text-main);
   }

   .history-card .iq-card-body {
      padding: 22px;
   }

   .orders-table {
      border-collapse: separate;
      border-spacing: 0 12px;
      margin-bottom: 0;
   }

   .orders-table thead th {
      background: #f8fafc;
      color: #475569;
      font-weight: 800;
      font-size: 13px;
      letter-spacing: 0.2px;
      border: 0 !important;
      padding: 14px 12px;
      white-space: nowrap;
   }

   .orders-table tbody tr {
      background: #fff;
      box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
   }

   .orders-table tbody tr:hover {
      transform: translateY(-1px);
      box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
   }

   .orders-table tbody td {
      vertical-align: middle !important;
      padding: 16px 12px;
      border-top: 1px solid #eef2f7;
      border-bottom: 1px solid #eef2f7;
      background: #fff;
   }

   .orders-table tbody td:first-child {
      border-left: 1px solid #eef2f7;
      border-top-left-radius: 14px;
      border-bottom-left-radius: 14px;
   }

   .orders-table tbody td:last-child {
      border-right: 1px solid #eef2f7;
      border-top-right-radius: 14px;
      border-bottom-right-radius: 14px;
   }

   .order-items-list {
      min-width: 280px;
   }

   .order-item-row {
      padding: 10px 0;
   }

   .order-item-row:not(:last-child) {
      border-bottom: 1px solid #eef2f7;
      margin-bottom: 8px;
   }

   .order-item-name {
      font-weight: 700;
      line-height: 1.45;
      color: #111827;
   }

   .order-item-qty {
      white-space: nowrap;
      font-weight: 800;
      color: #0f172a;
      background: var(--surface-soft);
      border: 1px solid #e2e8f0;
      border-radius: 999px;
      padding: 4px 10px;
      display: inline-block;
   }

   .order-status {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 7px 12px;
      border-radius: 999px;
      font-weight: 800;
      font-size: 13px;
      white-space: nowrap;
      border: 1px solid transparent;
   }

   .status-pending,
   .status-new,
   .status-unprocessed {
      background: #fff7e6;
      color: #b45309;
      border-color: #fde68a;
   }

   .status-processing,
   .status-confirmed,
   .status-shipping,
   .status-delivering {
      background: #e0f2fe;
      color: #0369a1;
      border-color: #bae6fd;
   }

   .status-delivered {
      background: #dcfce7;
      color: #15803d;
      border-color: #bbf7d0;
   }

   .status-cancelled {
      background: #fee2e2;
      color: #b91c1c;
      border-color: #fecaca;
   }

   .alert {
      border-radius: 14px;
      padding: 14px 16px;
   }

   .alert-info {
      background: #eff6ff;
      border-color: #bfdbfe;
      color: #1d4ed8;
   }

   .alert-warning {
      background: #fff7ed;
      border-color: #fed7aa;
      color: #c2410c;
   }

   .alert-success {
      background: #ecfdf5;
      border-color: #a7f3d0;
      color: #047857;
   }

   .alert-danger {
      background: #fef2f2;
      border-color: #fecaca;
      color: #b91c1c;
   }

   .btn-danger {
      border-radius: 10px;
      font-weight: 700;
      padding: 8px 14px;
      background: var(--danger);
      border-color: var(--danger);
   }

   .btn-danger:hover,
   .btn-danger:focus,
   .btn-danger:active {
      background: #b91c1c !important;
      border-color: #b91c1c !important;
   }

   .text-muted {
      color: var(--text-muted) !important;
   }

   .font-weight-bold {
      color: #0f172a;
   }

   .order-code {
      font-weight: 900;
      color: var(--text-main);
      letter-spacing: 0.2px;
   }

   .date-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--surface-soft);
      border: 1px solid #e2e8f0;
      border-radius: 999px;
      padding: 6px 10px;
      font-weight: 700;
      color: #334155;
      white-space: nowrap;
   }

   .price-strong {
      font-weight: 900;
      color: var(--danger);
      white-space: nowrap;
   }

   .no-order-box {
      background: var(--surface-soft);
      border: 1px dashed #cbd5e1;
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      color: var(--text-muted);
   }

   .no-order-box strong {
      display: block;
      color: var(--text-main);
      font-size: 16px;
      margin-bottom: 6px;
   }

   .product-thumb {
      width: 52px;
      height: 52px;
      object-fit: cover;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background: #fff;
      flex: 0 0 auto;
   }

   @media (max-width: 992px) {
      .orders-table {
         min-width: 980px;
      }
   }
</style>

<div id="content-page" class="content-page">
   <div class="container-fluid history-shell">
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card history-card">
               <div class="iq-card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0">Lịch sử mua hàng</h4>
               </div>

               <div class="iq-card-body">
                  <?php if ($cancelMessage): ?>
                     <div class="alert alert-<?php echo h($cancelMessageType); ?>">
                        <?php echo h($cancelMessage); ?>
                     </div>
                  <?php endif; ?>

                  <?php if ($orders): ?>
                     <div class="table-responsive">
                        <table class="table table-bordered table-hover orders-table mb-0">
                           <thead>
                              <tr>
                                 <th style="width: 120px;">Ngày đặt</th>
                                 <th style="width: 140px;">Mã đơn</th>
                                 <th>Đơn hàng đã đặt</th>
                                 <th style="width: 130px;">Số lượng</th>
                                 <th class="text-right" style="width: 160px;">Tổng tiền</th>
                                 <th style="width: 170px;">Trạng thái</th>
                                 <th style="width: 120px;">Hành động</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($orders as $order): ?>
                                 <?php
                                 $items = order_items_for((int) $order['id']);

                                 $orderCode = '#DH' . str_pad((string) $order['id'], 6, '0', STR_PAD_LEFT);
                                 $orderDate = !empty($order['date']) ? date('d/m/Y', strtotime($order['date'])) : '';

                                 $statusRaw = strtolower(trim((string) ($order['status'] ?? '')));
                                 $statusText = $statusRaw;

                                 if (in_array($statusRaw, ['pending', 'new', 'unprocessed'], true)) {
                                     $statusText = 'Chờ xử lý';
                                 } elseif (in_array($statusRaw, ['processing', 'confirmed'], true)) {
                                     $statusText = 'Đã xác nhận';
                                 } elseif (in_array($statusRaw, ['shipping', 'delivering'], true)) {
                                     $statusText = 'Đang giao';
                                 } elseif ($statusRaw === 'delivered') {
                                     $statusText = 'Đã giao';
                                 } elseif ($statusRaw === 'cancelled') {
                                     $statusText = 'Đã hủy đơn hàng này';
                                 }

                                 $canCancel = in_array($statusRaw, ['pending', 'new', 'unprocessed'], true);
                                 $statusClass = 'status-' . ($statusRaw ?: 'pending');
                                 ?>

                                 <tr>
                                    <td>
                                       <span class="date-pill"><?php echo h($orderDate); ?></span>
                                    </td>

                                    <td>
                                       <span class="order-code"><?php echo h($orderCode); ?></span>
                                    </td>

                                    <td>
                                       <?php if (!empty($items)): ?>
                                          <div class="order-items-list">
                                             <?php foreach ($items as $index => $item): ?>
                                                <?php
                                                $bookName = $item['bookname'] ?? 'Không có tên sách';
                                                $bookImage = book_image_src($item, (int) ($item['book_id'] ?? 1));
                                                $qty = (int) ($item['quantity'] ?? 1);
                                                ?>
                                                <div class="order-item-row">
                                                   <div class="d-flex align-items-center">
                                                      <img
                                                         src="<?php echo h($bookImage); ?>"
                                                         alt="<?php echo h($bookName); ?>"
                                                         class="product-thumb mr-3"
                                                      >
                                                      <div class="order-item-name">
                                                         <?php echo h($bookName); ?>
                                                      </div>
                                                   </div>
                                                </div>
                                             <?php endforeach; ?>
                                          </div>
                                       <?php else: ?>
                                          <span class="text-muted">Không có thông tin sản phẩm</span>
                                       <?php endif; ?>
                                    </td>

                                    <td>
                                       <?php if (!empty($items)): ?>
                                          <div class="order-items-list">
                                             <?php foreach ($items as $item): ?>
                                                <?php $qty = (int) ($item['quantity'] ?? 1); ?>
                                                <div class="order-item-row">
                                                   <span class="order-item-qty">x<?php echo (int) $qty; ?></span>
                                                </div>
                                             <?php endforeach; ?>
                                          </div>
                                       <?php else: ?>
                                          <span class="text-muted">-</span>
                                       <?php endif; ?>
                                    </td>

                                    <td class="text-right">
                                       <strong class="price-strong"><?php echo vn_money($order['price']); ?> đ</strong>
                                    </td>

                                    <td>
                                       <span class="order-status <?php echo h($statusClass); ?>">
                                          <?php echo h($statusText); ?>
                                       </span>
                                    </td>

                                    <td>
                                       <?php if ($canCancel): ?>
                                          <form method="post" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');" class="m-0">
                                             <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                             <button type="submit" name="cancel_order" class="btn btn-sm btn-danger">
                                                Hủy đơn
                                             </button>
                                          </form>
                                       <?php else: ?>
                                          <span class="text-muted">Không thể hủy</span>
                                       <?php endif; ?>
                                    </td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="no-order-box mb-0">
                        <strong>Bạn chưa có đơn hàng nào.</strong>
                        <div>Hãy quay lại mua sắm và theo dõi các đơn hàng của bạn tại đây.</div>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<?php include 'includes/footer.php'; ?>