<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Địa chỉ giao hàng';
$pageBreadcrumb = 'Địa chỉ giao hàng';
$user = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverName = trim((string) ($_POST['receiver_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $addressDetail = trim((string) ($_POST['address_detail'] ?? ''));
    $ward = trim((string) ($_POST['ward'] ?? ''));
    $district = trim((string) ($_POST['district'] ?? ''));
    $province = trim((string) ($_POST['province'] ?? ''));
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    if ($receiverName === '' || $phone === '' || $addressDetail === '' || $ward === '' || $district === '' || $province === '') {
        $error = 'Vui lòng nhập đầy đủ địa chỉ.';
    } else {
        if ($isDefault) {
            mysqli_query(db(), 'UPDATE address SET is_default = 0 WHERE user_id = ' . (int) $user['id']);
        }

        $stmt = mysqli_prepare(db(), 'INSERT INTO address (user_id, receiver_name, phone, address_detail, ward, district, province, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE receiver_name = VALUES(receiver_name), phone = VALUES(phone), address_detail = VALUES(address_detail), ward = VALUES(ward), district = VALUES(district), province = VALUES(province), is_default = VALUES(is_default)');
        mysqli_stmt_bind_param($stmt, 'issssssi', $user['id'], $receiverName, $phone, $addressDetail, $ward, $district, $province, $isDefault);
        $ok = mysqli_stmt_execute($stmt);
        $savedId = (int) mysqli_insert_id(db());
        if ($savedId <= 0) {
            $row = fetch_one('SELECT id FROM address WHERE user_id = ' . (int) $user['id'] . ' LIMIT 1');
            $savedId = (int) ($row['id'] ?? 0);
        }

        if ($ok) {
            mysqli_stmt_close($stmt);
            $_SESSION['selected_address_id'] = $savedId;
            flash('success', 'Đã lưu địa chỉ.');
            redirect('Checkout-preview.php');
        }
        $error = 'Không thể lưu địa chỉ.';
        mysqli_stmt_close($stmt);
    }
}

$addresses = user_addresses((int) $user['id']);
if (!isset($_SESSION['selected_address_id']) && $addresses) {
    $_SESSION['selected_address_id'] = (int) $addresses[0]['id'];
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-7">
            <div class="iq-card">
               <div class="iq-card-header"><h4 class="card-title mb-0">Chọn địa chỉ đã lưu</h4></div>
               <div class="iq-card-body">
                  <?php if ($addresses): ?>
                     <?php foreach ($addresses as $address): ?>
                        <label class="d-block border rounded p-3 mb-3 <?php echo ((int) $address['id'] === (int) ($_SESSION['selected_address_id'] ?? 0)) ? 'border-primary' : ''; ?>">
                           <input type="radio" name="selected_address" value="<?php echo (int) $address['id']; ?>" <?php echo ((int) $address['id'] === (int) ($_SESSION['selected_address_id'] ?? 0)) ? 'checked' : ''; ?> onclick="window.location='Checkout-preview.php?address_id=<?php echo (int) $address['id']; ?>'">
                           <strong><?php echo h($address['receiver_name']); ?></strong> - <?php echo h($address['phone']); ?><br>
                           <?php echo h($address['address_detail'] . ', ' . $address['ward'] . ', ' . $address['district'] . ', ' . $address['province']); ?>
                           <?php if ((int) $address['is_default'] === 1): ?><span class="badge badge-primary ml-2">Mặc định</span><?php endif; ?>
                        </label>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <div class="alert alert-info">Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới bên dưới.</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
         <div class="col-lg-5">
            <div class="iq-card">
               <div class="iq-card-header"><h4 class="card-title mb-0">Thêm địa chỉ mới</h4></div>
               <div class="iq-card-body">
                  <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
                  <form method="post">
                     <div class="form-group"><label>Người nhận</label><input name="receiver_name" class="form-control" value="<?php echo h($user['fullname'] ?? $user['username']); ?>"></div>
                     <div class="form-group"><label>Số điện thoại</label><input name="phone" class="form-control" value="<?php echo h($user['phone'] ?? ''); ?>"></div>
                     <div class="form-group"><label>Địa chỉ chi tiết</label><input name="address_detail" class="form-control"></div>
                     <div class="form-group"><label>Phường/Xã</label><input name="ward" class="form-control"></div>
                     <div class="form-group"><label>Quận/Huyện</label><input name="district" class="form-control"></div>
                     <div class="form-group"><label>Tỉnh/Thành phố</label><input name="province" class="form-control"></div>
                     <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="default-address" name="is_default" value="1">
                        <label class="custom-control-label" for="default-address">Đặt làm mặc định</label>
                     </div>
                     <button class="btn btn-primary">Lưu địa chỉ</button>
                     <a href="Checkout-preview.php" class="btn btn-outline-secondary">Quay lại</a>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
