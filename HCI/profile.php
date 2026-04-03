<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Tài khoản của tôi';
$pageBreadcrumb = 'Tài khoản của tôi';
$user = current_user();
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int) $user['id'] . ' LIMIT 1');
$activeTab = $_GET['tab'] ?? 'personal-information';
if (!in_array($activeTab, ['personal-information', 'account-info', 'manage-contact'], true)) {
    $activeTab = 'personal-information';
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $activeTab = 'manage-contact';
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

        $stmt = mysqli_prepare(db(), '
    INSERT INTO address 
    (user_id, receiver_name, phone, address_detail, ward, district, province, is_default) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
');
        mysqli_stmt_bind_param($stmt, 'issssssi', $user['id'], $receiverName, $phone, $addressDetail, $ward, $district, $province, $isDefault);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($ok) {
            flash('success', 'Đã lưu địa chỉ.');
            redirect('profile.php?tab=manage-contact');
        }

        $error = 'Không thể lưu địa chỉ.';
        
    }
}

$addresses = user_addresses((int) $user['id']);
$addressForm = $addresses[0] ?? [
    'receiver_name' => $profile['fullname'] ?? ($user['fullname'] ?? $user['username'] ?? ''),
    'phone' => $profile['phone'] ?? ($user['phone'] ?? ''),
    'address_detail' => '',
    'ward' => '',
    'district' => '',
    'province' => '',
    'is_default' => 1,
];

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card">
               <div class="iq-card-body p-0">
                  <div class="iq-edit-list">
                     <ul class="iq-edit-profile d-flex nav nav-pills">
                        <li class="col-md-4 p-0"><a
                              class="nav-link <?php echo $activeTab === 'personal-information' ? 'active' : ''; ?>"
                              data-toggle="pill" href="#personal-information">Thông tin cá nhân</a></li>
                        <li class="col-md-4 p-0"><a
                              class="nav-link <?php echo $activeTab === 'account-info' ? 'active' : ''; ?>"
                              data-toggle="pill" href="#account-info">Tài khoản</a></li>
                        <li class="col-md-4 p-0"><a
                              class="nav-link <?php echo $activeTab === 'manage-contact' ? 'active' : ''; ?>"
                              data-toggle="pill" href="#manage-contact">Quản lý liên hệ</a></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-12">
            <div class="iq-edit-list-data">
               <div class="tab-content">
                  <div class="tab-pane fade <?php echo $activeTab === 'personal-information' ? 'active show' : ''; ?>"
                     id="personal-information" role="tabpanel">
                     <div class="iq-card">
                        <div class="iq-card-header d-flex justify-content-between">
                           <div class="iq-header-title">
                              <h4 class="card-title">Thông tin cá nhân</h4>
                           </div>
                        </div>
                        <div class="iq-card-body">
                           <div class="row align-items-center">
                              <div class="form-group col-sm-6"><label>Họ và tên:</label>
                                 <?php echo h($profile['fullname'] ?? ''); ?></div>
                              <div class="form-group col-sm-6"><label>Email:</label>
                                 <?php echo h($profile['email'] ?? ''); ?></div>
                              <div class="form-group col-sm-6"><label>Giới tính:</label>
                                 <?php echo h($profile['gender'] ?? 'Chưa cập nhật'); ?></div>
                              <div class="form-group col-sm-6"><label>Ngày sinh:</label>
                                 <?php echo h($profile['birthday'] ?? 'Chưa cập nhật'); ?></div>
                           </div><a href="account-edit.php" class="btn btn-primary mr-2">Chỉnh sửa</a>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane fade <?php echo $activeTab === 'account-info' ? 'active show' : ''; ?>"
                     id="account-info" role="tabpanel">
                     <div class="iq-card">
                        <div class="iq-card-header d-flex justify-content-between">
                           <div class="iq-header-title">
                              <h4 class="card-title">Tài khoản</h4>
                           </div>
                        </div>
                        <div class="iq-card-body">
                           <div class="row">
                              <div class="form-group col-sm-6"><label>Tên tài khoản</label><input type="text"
                                    class="form-control" value="<?php echo h($profile['username'] ?? ''); ?>" readonly>
                              </div>
                              <div class="form-group col-sm-6"><label>Email</label><input type="email"
                                    class="form-control" value="<?php echo h($profile['email'] ?? ''); ?>" readonly>
                              </div>
                              <div class="form-group col-sm-6"><label>Trạng thái</label><input type="text"
                                    class="form-control" value="<?php echo h($profile['status'] ?? ''); ?>" readonly>
                              </div>
                           </div><a href="account-edit.php" class="btn btn-primary mr-2">Sửa</a>
                        </div>
                     </div>
                  </div>
                  <div class="tab-pane fade <?php echo $activeTab === 'manage-contact' ? 'active show' : ''; ?>"
                     id="manage-contact" role="tabpanel">
                     <div class="iq-card">
                        <div class="iq-card-header">
                           <h4 class="card-title">Quản lý liên hệ</h4>
                        </div>
                        <div class="iq-card-body">
                           <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div>
                           <?php endif; ?>
                           <div class="list-group mb-3">
                              <?php if ($addresses): ?>
                              <?php foreach ($addresses as $address): ?>
                              <div class="list-group-item d-flex justify-content-between align-items-start">
                                 <div>
                                    <strong><?php echo h($address['receiver_name']); ?></strong><br>
                                    <?php echo h($address['phone']); ?><br>
                                    <?php echo h($address['address_detail'] . ', ' . $address['ward'] . ', ' . $address['district'] . ', ' . $address['province']); ?>
                                 </div>
                                 <div>
                                    <?php if ((int) $address['is_default'] === 1): ?><span
                                       class="badge badge-primary">Mặc định</span><?php endif; ?>
                                 </div>
                              </div>
                              <?php endforeach; ?>
                              <?php else: ?>
                              <div class="alert alert-info mb-0">Chưa có địa chỉ nào.</div>
                              <?php endif; ?>
                           </div>
                           <form method="post" class="mt-4">
                              <input type="hidden" name="save_address" value="1">

                              <div class="custom-control custom-checkbox mb-3">
                                 <input type="checkbox" class="custom-control-input" id="default-address"
                                    name="is_default" value="1"
                                    <?php echo (int) ($addressForm['is_default'] ?? 0) === 1 ? 'checked' : ''; ?>>
                                 <label class="custom-control-label" for="default-address">Đặt làm mặc định</label>
                              </div>
                              <a href="add-address.php" class="btn btn-primary mb-3">
                                 + Thêm địa chỉ mới
                              </a>
                           </form>
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