<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Xem lại đơn đặt hàng';
$pageBreadcrumb = 'Xem lại đơn đặt hàng';
$user = current_user();

// =========================
// CHỌN ĐỊA CHỈ NGAY TRONG TRANG NÀY
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_address'])) {
    $_SESSION['selected_address_id'] = (int) ($_POST['address_id'] ?? 0);
    redirect('Checkout-preview.php');
}

// =========================
// XỬ LÝ ĐẶT HÀNG
// =========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $addressId = (int) ($_SESSION['selected_address_id'] ?? 0);
    $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'Thanh toán khi nhận hàng'));
    $note = trim((string) ($_POST['note'] ?? ''));

    $inputBankName = trim((string) ($_POST['bank_name'] ?? ''));
    $inputBankNumber = trim((string) ($_POST['bank_number'] ?? ''));
    $inputBankOwner = trim((string) ($_POST['bank_owner'] ?? ''));

    if ($addressId <= 0) {
        flash('danger', 'Vui lòng chọn địa chỉ giao hàng.');
        redirect('Checkout.php');
    }

    if ($paymentMethod === 'Chuyển khoản') {
        if ($inputBankName === '' || $inputBankNumber === '' || $inputBankOwner === '') {
            flash('danger', 'Vui lòng nhập đầy đủ thông tin chuyển khoản.');
            redirect('Checkout-preview.php');
        }

        if (!preg_match('/^\d{10}$/', $inputBankNumber)) {
            flash('danger', 'Số tài khoản phải đúng 10 chữ số.');
            redirect('Checkout-preview.php');
        }

        $note .= "\n--- Thông tin chuyển khoản ---";
        $note .= "\nNgân hàng: " . $inputBankName;
        $note .= "\nSố tài khoản: " . $inputBankNumber;
        $note .= "\nTên tài khoản: " . $inputBankOwner;
    }

    if ($paymentMethod === 'Thanh toán khi nhận hàng') {
        $note .= "\n--- Thanh toán khi nhận hàng ---";
    }

    $orderId = create_order_from_cart((int) $user['id'], $addressId, $paymentMethod, $note);
    if ($orderId) {
        redirect('Checkout-success.php?id=' . $orderId);
    }
}

// =========================
// DỮ LIỆU GIỎ HÀNG
// =========================
$items = cart_items();
$total = cart_total();

// =========================
// LẤY DANH SÁCH ĐỊA CHỈ
// =========================
$addresses = fetch_all('SELECT * FROM address WHERE user_id = ' . (int) $user['id']);

$selectedAddressId = (int) ($_SESSION['selected_address_id'] ?? 0);
$address = null;

if ($selectedAddressId > 0) {
    $address = fetch_one(
        'SELECT * FROM address WHERE id = ' . $selectedAddressId . ' AND user_id = ' . (int) $user['id'] . ' LIMIT 1'
    );
}

if (!$address && $addresses) {
    $address = default_address((int) $user['id']);
    if (!$address) {
        $address = $addresses[0];
    }

    if ($address) {
        $_SESSION['selected_address_id'] = (int) $address['id'];
        $selectedAddressId = (int) $address['id'];
    }
}

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
        --danger: #dc2626;
        --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
    }

    .checkout-card {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: var(--shadow);
        background: var(--surface);
    }

    .checkout-card .iq-card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
        border-bottom: 1px solid #eef2f7;
        padding: 18px 22px;
    }

    .checkout-card .card-title {
        font-weight: 800;
        color: var(--text-main);
        margin: 0;
        font-size: 20px;
    }

    .checkout-card .iq-card-body {
        padding: 22px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-title::before {
        content: "";
        width: 4px;
        height: 18px;
        border-radius: 999px;
        background: var(--accent);
        display: inline-block;
    }

    .product-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .product-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        border: 0;
        padding: 14px 14px;
        white-space: nowrap;
    }

    .product-table tbody tr {
        background: #fff;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .product-table tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .product-table tbody td {
        vertical-align: middle;
        padding: 14px;
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
    }

    .product-table tbody td:first-child {
        border-left: 1px solid #eef2f7;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    .product-table tbody td:last-child {
        border-right: 1px solid #eef2f7;
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .product-name {
        font-weight: 700;
        color: #111827;
        line-height: 1.45;
    }

    .muted-small {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .price-text {
        font-weight: 700;
        color: var(--text-main);
        white-space: nowrap;
    }

    .subtotal-text {
        font-weight: 800;
        color: var(--danger);
        white-space: nowrap;
    }

    .address-box {
        background: var(--surface-soft);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
    }

    .address-box p {
        margin-bottom: 8px;
        color: #334155;
    }

    .address-box p:last-child {
        margin-bottom: 0;
    }

    .order-summary {
        background: var(--surface-soft);
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        color: #334155;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
    }

    .btn-soft {
        border-radius: 10px;
        font-weight: 700;
        padding: 10px 16px;
        transition: 0.2s ease;
    }

    .btn-soft:hover {
        transform: translateY(-1px);
    }

    .btn-block-full {
        display: block;
        width: 100%;
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

    .btn-secondary {
        background: #64748b;
        border-color: #64748b;
    }

    .btn-secondary:hover,
    .btn-secondary:focus,
    .btn-secondary:active {
        background: #475569 !important;
        border-color: #475569 !important;
    }

    .form-control,
    .custom-select,
    select.form-control,
    textarea.form-control {
        border-radius: 10px;
        border: 1px solid #dbe3ee;
        padding: 10px 12px;
        box-shadow: none;
    }

    .form-control:focus,
    .custom-select:focus,
    select.form-control:focus,
    textarea.form-control:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12);
    }

    .bank-section {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 16px;
        margin-top: 10px;
    }

    .empty-state {
        background: var(--surface-soft);
        border: 1px dashed #cbd5e1;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state h6 {
        color: var(--text-main);
        font-weight: 800;
        margin-bottom: 8px;
    }

    .alert-danger {
        border-radius: 14px;
    }

    .text-danger {
        color: var(--danger) !important;
    }
</style>

<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">
      <div class="row">

         <!-- LEFT -->
         <div class="col-lg-8">

            <!-- SẢN PHẨM -->
            <div class="iq-card checkout-card">
               <div class="iq-card-header">
                  <h4 class="card-title mb-0">Sản phẩm</h4>
               </div>
               <div class="iq-card-body">
                  <?php if ($items): ?>
                     <div class="table-responsive">
                        <table class="table product-table mb-0">
                           <thead class="thead-light">
                              <tr>
                                 <th style="width: 60px;">#</th>
                                 <th>Sản phẩm</th>
                                 <th style="width: 90px;">SL</th>
                                 <th style="width: 130px;">Giá</th>
                                 <th style="width: 150px;">Thành tiền</th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($items as $i => $item): $book = $item['book']; ?>
                                 <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td>
                                       <div class="product-name"><?php echo h($book['bookname']); ?></div>
                                       <div class="muted-small">Đã thêm vào giỏ hàng</div>
                                    </td>
                                    <td><?php echo (int) $item['quantity']; ?></td>
                                    <td class="price-text"><?php echo vn_money($item['price']); ?> đ</td>
                                    <td class="subtotal-text"><?php echo vn_money($item['subtotal']); ?> đ</td>
                                 </tr>
                              <?php endforeach; ?>
                           </tbody>
                        </table>
                     </div>
                  <?php else: ?>
                     <div class="empty-state mb-0">
                        <h6>Giỏ hàng trống</h6>
                        <p class="mb-0">Bạn chưa có sản phẩm nào trong giỏ.</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>

            <!-- ĐỊA CHỈ -->
            <div class="iq-card checkout-card mt-3">
               <div class="iq-card-body">
                  <h6 class="section-title">Thông tin nhận hàng</h6>

                  <?php if ($addresses): ?>
                     <form method="post" class="mb-3">
                        <input type="hidden" name="change_address" value="1">
                        <div class="form-group mb-0">
                           <label class="mb-2 font-weight-bold">Chọn địa chỉ giao hàng</label>
                           <select name="address_id" class="form-control" onchange="this.form.submit()">
                              <?php foreach ($addresses as $addr): ?>
                                 <option value="<?php echo (int) $addr['id']; ?>" <?php echo ((int) $addr['id'] === $selectedAddressId) ? 'selected' : ''; ?>>
                                    <?php echo h(
                                       $addr['receiver_name'] . ' - ' .
                                       $addr['phone'] . ' - ' .
                                       $addr['address_detail'] . ', ' .
                                       $addr['ward'] . ', ' .
                                       $addr['district'] . ', ' .
                                       $addr['province']
                                    ); ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                     </form>
                  <?php endif; ?>

                  <?php if ($address): ?>
                     <div class="address-box">
                        <p><strong>Người nhận:</strong> <?php echo h($address['receiver_name']); ?></p>
                        <p><strong>SĐT:</strong> <?php echo h($address['phone']); ?></p>
                        <p>
                           <strong>Địa chỉ:</strong>
                           <?php echo h($address['address_detail'] . ', ' . $address['ward'] . ', ' . $address['district'] . ', ' . $address['province']); ?>
                        </p>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-danger mb-0">
                        Chưa có địa chỉ giao hàng phù hợp. Hãy chọn lại ở giỏ hàng.
                     </div>
                  <?php endif; ?>
               </div>
            </div>

         </div>

         <!-- RIGHT -->
         <div class="col-lg-4">
            <div class="iq-card checkout-card">
               <div class="iq-card-body">

                  <h5 class="section-title mb-3">Tổng đơn</h5>

                  <div class="order-summary mb-3">
                     <div class="summary-total">
                        <span>Tổng</span>
                        <span class="text-danger"><?php echo vn_money($total); ?> đ</span>
                     </div>
                  </div>

                  <form method="post" id="order-form">
                     <input type="hidden" name="confirm_order" value="1">

                     <div class="form-group">
                        <label class="font-weight-bold">Phương thức thanh toán</label>
                        <select name="payment_method" id="payment_method" class="form-control">
                           <option value="Thanh toán khi nhận hàng">🚚 Thanh toán khi nhận hàng</option>
                           <option value="Chuyển khoản">🏦 Chuyển khoản</option>
                        </select>
                     </div>

                     <div id="bank-form" class="bank-section" style="display:none;">
                        <div class="form-group">
                           <label class="font-weight-bold">Ngân hàng</label>
                           <select name="bank_name" class="form-control">
                              <option value="">-- Chọn ngân hàng --</option>
                              <option value="MBBank">MBBank</option>
                              <option value="Vietcombank">Vietcombank</option>
                              <option value="Techcombank">Techcombank</option>
                              <option value="VPBank">VPBank</option>
                              <option value="ACB">ACB</option>
                              <option value="BIDV">BIDV</option>
                           </select>
                        </div>

                        <div class="form-group">
                           <label class="font-weight-bold">Số tài khoản</label>
                           <input
                              type="text"
                              name="bank_number"
                              id="bank_number"
                              class="form-control"
                              placeholder="Nhập đúng 10 số"
                              autocomplete="off"
                           >
                           <small id="bank_number_error" class="text-danger" style="display:none;">
                              Số tài khoản phải đủ 10 chữ số.
                           </small>
                        </div>

                        <div class="form-group mb-0">
                           <label class="font-weight-bold">Tên tài khoản</label>
                           <input type="text" name="bank_owner" class="form-control" placeholder="Nhập tên chủ tài khoản">
                        </div>
                     </div>

                     <div class="form-group mt-3">
                        <label class="font-weight-bold">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="4" placeholder="Ghi chú thêm cho đơn hàng..."></textarea>
                     </div>

                     <a href="Checkout.php" class="btn btn-secondary btn-soft btn-block-full mb-2">
                        Xem lại giỏ hàng
                     </a>

                     <button class="btn btn-primary btn-soft btn-block-full" <?php echo (!$items || !$address) ? 'disabled' : ''; ?>>
                        Đặt hàng
                     </button>
                  </form>

               </div>
            </div>
         </div>

      </div>
   </div>
</div>

<script>
(function () {
    const payment = document.getElementById('payment_method');
    const bankForm = document.getElementById('bank-form');
    const bankNumberInput = document.getElementById('bank_number');
    const bankError = document.getElementById('bank_number_error');
    const orderForm = document.getElementById('order-form');

    function toggleBankForm() {
        if (payment && bankForm) {
            bankForm.style.display = (payment.value === 'Chuyển khoản') ? 'block' : 'none';
        }
    }

    function validateBankNumber() {
        if (!bankNumberInput) return true;

        let value = bankNumberInput.value;

        if (!/^\d*$/.test(value)) {
            value = value.replace(/\D/g, '');
            bankNumberInput.value = value;
        }

        const valid = /^\d{10}$/.test(value);

        if (valid) {
            bankNumberInput.classList.remove('is-invalid');
            if (bankError) bankError.style.display = 'none';
        } else {
            bankNumberInput.classList.add('is-invalid');
            if (bankError) bankError.style.display = 'block';
        }

        return valid;
    }

    if (payment) {
        payment.addEventListener('change', toggleBankForm);
        toggleBankForm();
    }

    if (bankNumberInput) {
        bankNumberInput.addEventListener('input', validateBankNumber);
        bankNumberInput.addEventListener('blur', validateBankNumber);
    }

    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            if (payment && payment.value === 'Chuyển khoản') {
                const ok = validateBankNumber();
                if (!ok) {
                    e.preventDefault();
                }
            }
        });
    }
})();
</script>

<?php include 'includes/footer.php'; ?>