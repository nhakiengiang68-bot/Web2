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

    $receiverName  = trim((string) ($_POST['receiver_name'] ?? ''));
    $phone         = trim((string) ($_POST['phone'] ?? ''));
    $addressDetail = trim((string) ($_POST['address_detail'] ?? ''));
    $ward          = trim((string) ($_POST['ward'] ?? ''));
    $district      = trim((string) ($_POST['district'] ?? ''));
    $province      = trim((string) ($_POST['province'] ?? ''));
    $isDefault     = isset($_POST['is_default']) ? 1 : 0;

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

        mysqli_stmt_bind_param(
            $stmt,
            'issssssi',
            $user['id'],
            $receiverName,
            $phone,
            $addressDetail,
            $ward,
            $district,
            $province,
            $isDefault
        );

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

<style>
   .profile-page {
      padding: 24px 0 44px;
   }

   .profile-hero {
      background: linear-gradient(135deg, #0f766e 0%, #14b8a6 40%, #22c55e 100%);
      border-radius: 24px;
      padding: 26px 28px;
      color: #fff;
      box-shadow: 0 18px 40px rgba(20, 184, 166, 0.18);
      position: relative;
      overflow: hidden;
      margin-bottom: 22px;
   }

   .profile-hero::after {
      content: "";
      position: absolute;
      inset: auto -40px -50px auto;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: rgba(255,255,255,0.10);
      filter: blur(0.5px);
   }

   .profile-hero h2 {
      margin: 0 0 8px;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -.02em;
   }

   .profile-hero p {
      margin: 0;
      color: rgba(255,255,255,0.9);
      font-size: 15px;
   }

   .profile-shell {
      background: #fff;
      border: 1px solid rgba(15, 23, 42, 0.06);
      border-radius: 24px;
      box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
      overflow: hidden;
   }

   .profile-tabs-wrap {
      padding: 18px 18px 0;
      background: linear-gradient(180deg, #fbfdff 0%, #ffffff 100%);
      border-bottom: 1px solid #edf2f7;
   }

   .profile-tabs {
      display: flex;
      gap: 10px;
      list-style: none;
      padding: 0;
      margin: 0;
      flex-wrap: wrap;
   }

   .profile-tabs .nav-link {
      border-radius: 16px;
      padding: 14px 18px;
      font-weight: 700;
      color: #475569;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      transition: all .2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 52px;
   }

   .profile-tabs .nav-link:hover {
      background: #ecfeff;
      color: #0f766e;
      border-color: #b2f5ea;
      transform: translateY(-1px);
   }

   .profile-tabs .nav-link.active {
      background: linear-gradient(135deg, #0f766e, #14b8a6);
      color: #fff;
      border-color: transparent;
      box-shadow: 0 12px 24px rgba(20, 184, 166, 0.20);
   }

   .profile-content {
      padding: 22px;
      background: #f8fafc;
   }

   .profile-card {
      background: #fff;
      border-radius: 20px;
      border: 1px solid rgba(15, 23, 42, 0.06);
      box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
      overflow: hidden;
      margin-bottom: 18px;
   }

   .profile-card-header {
      padding: 18px 22px;
      border-bottom: 1px solid #eef2f7;
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
   }

   .profile-card-header .card-title {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #111827;
   }

   .profile-card-body {
      padding: 22px;
   }

   .info-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px 18px;
   }

   .info-item {
      background: #f8fafc;
      border: 1px solid #edf2f7;
      border-radius: 16px;
      padding: 16px 18px;
      min-height: 92px;
   }

   .info-item label {
      display: block;
      margin: 0 0 8px;
      font-size: 13px;
      color: #64748b;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .03em;
   }

   .info-item .value {
      font-size: 15px;
      font-weight: 700;
      color: #0f172a;
      word-break: break-word;
   }

   .btn-soft {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0 18px;
      border-radius: 14px;
      font-weight: 700;
      border: 1px solid transparent;
      transition: all .2s ease;
      text-decoration: none !important;
   }

   .btn-soft-primary {
      background: linear-gradient(135deg, #0f766e, #14b8a6);
      color: #fff;
      box-shadow: 0 10px 22px rgba(20, 184, 166, 0.16);
   }

   .btn-soft-primary:hover {
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(20, 184, 166, 0.24);
   }

   .btn-soft-outline {
      background: #fff;
      border-color: #14b8a6;
      color: #0f766e;
   }

   .btn-soft-outline:hover {
      background: #14b8a6;
      color: #fff;
      transform: translateY(-1px);
   }

   .subtle-note {
      background: #ecfeff;
      border: 1px solid #c7f9f3;
      color: #0f766e;
      border-radius: 16px;
      padding: 14px 16px;
      font-size: 14px;
      margin-bottom: 18px;
   }

   .address-list {
      display: grid;
      gap: 14px;
      margin-bottom: 18px;
   }

   .address-item {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 18px;
      padding: 16px 18px;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
      display: flex;
      justify-content: space-between;
      gap: 14px;
      align-items: flex-start;
   }

   .address-main strong {
      display: inline-block;
      color: #111827;
      font-size: 15px;
      margin-bottom: 6px;
   }

   .address-main {
      color: #475569;
      font-size: 14px;
      line-height: 1.6;
   }

   .badge-default {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 12px;
      border-radius: 999px;
      background: #eff6ff;
      color: #1d4ed8;
      font-size: 12px;
      font-weight: 800;
      white-space: nowrap;
      border: 1px solid #dbeafe;
   }

   .alert-custom {
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 600;
   }

   .form-control, .custom-select {
      min-height: 46px;
      border-radius: 12px;
      border-color: #dbe4ee;
      box-shadow: none !important;
   }

   .form-control:focus, .custom-select:focus {
      border-color: #14b8a6;
      box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.12) !important;
   }

   .custom-control-label {
      font-weight: 600;
      color: #334155;
   }

   .tab-pane.fade {
      opacity: 0;
      transform: translateY(6px);
      transition: all .25s ease;
   }

   .tab-pane.fade.show {
      opacity: 1;
      transform: translateY(0);
   }

   .section-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 18px;
   }

   .section-toolbar .helper {
      color: #64748b;
      font-size: 14px;
   }

   .panel-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
   }

   @media (max-width: 991px) {
      .profile-content {
         padding: 18px;
      }

      .info-grid {
         grid-template-columns: 1fr;
      }

      .address-item {
         flex-direction: column;
      }
   }

   @media (max-width: 768px) {
      .profile-hero {
         padding: 20px;
         border-radius: 20px;
      }

      .profile-hero h2 {
         font-size: 22px;
      }

      .profile-tabs .nav-link {
         width: 100%;
         justify-content: flex-start;
      }

      .profile-card-body,
      .profile-card-header {
         padding-left: 16px;
         padding-right: 16px;
      }
   }
</style>

<div id="content-page" class="content-page">
   <div class="container-fluid profile-page">

      <?php render_flash(); ?>

      <div class="profile-hero">
         <h2>Tài khoản của tôi</h2>
         <p>Quản lý thông tin cá nhân, tài khoản và địa chỉ nhận hàng trong một nơi duy nhất.</p>
      </div>

      <div class="profile-shell">
         <div class="profile-tabs-wrap">
            <ul class="profile-tabs d-flex nav nav-pills">
               <li class="col-md-4 p-0">
                  <a class="nav-link <?php echo $activeTab === 'personal-information' ? 'active' : ''; ?>"
                     data-toggle="pill" href="#personal-information">Thông tin cá nhân</a>
               </li>
               <li class="col-md-4 p-0">
                  <a class="nav-link <?php echo $activeTab === 'account-info' ? 'active' : ''; ?>"
                     data-toggle="pill" href="#account-info">Tài khoản</a>
               </li>
               <li class="col-md-4 p-0">
                  <a class="nav-link <?php echo $activeTab === 'manage-contact' ? 'active' : ''; ?>"
                     data-toggle="pill" href="#manage-contact">Quản lý liên hệ</a>
               </li>
            </ul>
         </div>

         <div class="profile-content">
            <div class="tab-content">
               <div class="tab-pane fade <?php echo $activeTab === 'personal-information' ? 'active show' : ''; ?>"
                    id="personal-information" role="tabpanel">
                  <div class="profile-card">
                     <div class="profile-card-header d-flex justify-content-between align-items-center">
                        <div class="iq-header-title">
                           <h4 class="card-title">Thông tin cá nhân</h4>
                        </div>
                     </div>
                     <div class="profile-card-body">
                        <div class="info-grid">
                           <div class="info-item">
                              <label>Họ và tên</label>
                              <div class="value"><?php echo h($profile['fullname'] ?? ''); ?></div>
                           </div>

                           <div class="info-item">
                              <label>Email</label>
                              <div class="value"><?php echo h($profile['email'] ?? ''); ?></div>
                           </div>

                           <div class="info-item">
                              <label>Giới tính</label>
                              <div class="value"><?php echo h($profile['gender'] ?? 'Chưa cập nhật'); ?></div>
                           </div>

                           <div class="info-item">
                              <label>Ngày sinh</label>
                              <div class="value"><?php echo h($profile['birthday'] ?? 'Chưa cập nhật'); ?></div>
                           </div>
                        </div>

                        <div class="mt-4">
                           <a href="account-edit.php?section=personal-information" class="btn-soft btn-soft-primary">
                              Chỉnh sửa thông tin cá nhân
                           </a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="tab-pane fade <?php echo $activeTab === 'account-info' ? 'active show' : ''; ?>"
                    id="account-info" role="tabpanel">
                  <div class="profile-card">
                     <div class="profile-card-header d-flex justify-content-between align-items-center">
                        <div class="iq-header-title">
                           <h4 class="card-title">Tài khoản</h4>
                        </div>
                     </div>
                     <div class="profile-card-body">
                        <div class="info-grid">
                           <div class="info-item">
                              <label>Tên tài khoản</label>
                              <div class="value"><?php echo h($profile['username'] ?? ''); ?></div>
                           </div>

                           <div class="info-item">
                              <label>Email</label>
                              <div class="value"><?php echo h($profile['email'] ?? ''); ?></div>
                           </div>

                           <div class="info-item">
                              <label>Trạng thái</label>
                              <div class="value"><?php echo h($profile['status'] ?? ''); ?></div>
                           </div>
                        </div>

                        <div class="mt-4">
                           <a href="account-edit.php?section=account-info" class="btn-soft btn-soft-primary">
                              Chỉnh sửa tài khoản
                           </a>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="tab-pane fade <?php echo $activeTab === 'manage-contact' ? 'active show' : ''; ?>"
                    id="manage-contact" role="tabpanel">
                  <div class="profile-card">
                     <div class="profile-card-header">
                        <div class="section-toolbar mb-0">
                           <div>
                              <h4 class="card-title mb-1">Quản lý liên hệ</h4>
                              <div class="helper">Xem và quản lý địa chỉ nhận hàng của bạn.</div>
                           </div>

                           <div class="panel-actions">
                              <a href="add-address.php" class="btn-soft btn-soft-primary">
                                 + Thêm địa chỉ mới
                              </a>
                           </div>
                        </div>
                     </div>

                     <div class="profile-card-body">
                        <?php if ($error): ?>
                           <div class="alert alert-danger alert-custom"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <div class="subtle-note">
                           Bạn có thể đặt một địa chỉ làm mặc định để thao tác thanh toán nhanh hơn.
                        </div>

                        <div class="address-list mb-3">
                           <?php if ($addresses): ?>
                              <?php foreach ($addresses as $address): ?>
                                 <div class="address-item">
                                    <div class="address-main">
                                       <strong><?php echo h($address['receiver_name']); ?></strong><br>
                                       <?php echo h($address['phone']); ?><br>
                                       <?php echo h($address['address_detail'] . ', ' . $address['ward'] . ', ' . $address['district'] . ', ' . $address['province']); ?>
                                    </div>

                                    <div>
                                       <?php if ((int) $address['is_default'] === 1): ?>
                                          <span class="badge-default">Mặc định</span>
                                       <?php endif; ?>
                                    </div>
                                 </div>
                              <?php endforeach; ?>
                           <?php else: ?>
                              <div class="alert alert-info alert-custom mb-0">Chưa có địa chỉ nào.</div>
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

                           <a href="add-address.php" class="btn-soft btn-soft-outline">
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

<?php include 'includes/footer.php'; ?>