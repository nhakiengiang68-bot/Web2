<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Sửa thông tin tài khoản';
$pageBreadcrumb = 'Sửa thông tin tài khoản';

$user = current_user();
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int)$user['id'] . ' LIMIT 1');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim((string)($_POST['username'] ?? ''));
    $email      = trim((string)($_POST['email'] ?? ''));
    $fullname   = trim((string)($_POST['fullname'] ?? ''));
    $phone      = trim((string)($_POST['phone'] ?? ''));

    $currentPassword = (string)($_POST['current_password'] ?? '');
    $newPassword     = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    $changePassword = !empty($newPassword);

    // ================= VALIDATION =================
    if ($username === '' || $email === '' || $fullname === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } 
    elseif (!username_valid($username)) {
        $error = 'Tên tài khoản không hợp lệ.';
    } 
    elseif (!gmail_valid($email)) {
        $error = 'Email phải có đuôi @gmail.com.';
    } 
    elseif ($changePassword && empty($currentPassword)) {
        $error = 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu.';
    } 
    elseif ($changePassword && !password_matches($currentPassword, (string)$profile['password'])) {
        $error = 'Mật khẩu hiện tại không đúng.';
    } 
    elseif ($changePassword && strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } 
    elseif ($changePassword && $newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } 
    else {
        // Kiểm tra có thay đổi gì không
        $hasChange = 
            $username !== ($profile['username'] ?? '') ||
            $email    !== ($profile['email'] ?? '') ||
            $fullname !== ($profile['fullname'] ?? '') ||
            $phone    !== ($profile['phone'] ?? '') ||
            $changePassword;

        if (!$hasChange) {
            $error = 'Bạn chưa thay đổi thông tin nào.';
        } else {
            // Kiểm tra trùng username hoặc email
            $exists = fetch_one("SELECT id FROM users 
                                 WHERE (username = '" . esc($username) . "' 
                                    OR email = '" . esc($email) . "') 
                                   AND id <> " . (int)$user['id'] . " LIMIT 1");

            if ($exists) {
                $error = 'Tên tài khoản hoặc email đã tồn tại bởi người dùng khác.';
            } else {
                // Xử lý mật khẩu
                $newHash = $changePassword 
                    ? password_hash($newPassword, PASSWORD_DEFAULT) 
                    : $profile['password'];

                // Cập nhật database (không cập nhật gender và dob nữa)
                $stmt = mysqli_prepare(db(), 
                    'UPDATE users SET username=?, email=?, fullname=?, phone=?, password=? WHERE id=?'
                );
                mysqli_stmt_bind_param($stmt, 'sssssi', $username, $email, $fullname, $phone, $newHash, $user['id']);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                // Cập nhật session
                $_SESSION['user'] = [
                    'id'       => (int)$user['id'],
                    'username' => $username,
                    'fullname' => $fullname,
                    'email'    => $email,
                    'phone'    => $phone,
                    'role'     => $user['role'],
                ];

                flash('success', 'Cập nhật thông tin tài khoản thành công!');
                redirect('profile.php?tab=personal-information');
            }
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<style>
   .edit-profile-page {
      padding: 24px 0 44px;
   }

   .edit-profile-hero {
      background: linear-gradient(135deg, #0f766e 0%, #14b8a6 45%, #22c55e 100%);
      border-radius: 24px;
      padding: 26px 28px;
      color: #fff;
      box-shadow: 0 18px 40px rgba(20, 184, 166, 0.16);
      position: relative;
      overflow: hidden;
      margin-bottom: 22px;
   }

   .edit-profile-hero::after {
      content: "";
      position: absolute;
      inset: auto -50px -60px auto;
      width: 190px;
      height: 190px;
      border-radius: 50%;
      background: rgba(255,255,255,0.10);
   }

   .edit-profile-hero h2 {
      margin: 0 0 8px;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -.02em;
   }

   .edit-profile-hero p {
      margin: 0;
      color: rgba(255,255,255,0.92);
      font-size: 15px;
      line-height: 1.7;
   }

   .edit-shell {
      background: #fff;
      border: 1px solid rgba(15, 23, 42, 0.06);
      border-radius: 24px;
      box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
      overflow: hidden;
   }

   .edit-shell-header {
      padding: 18px 22px;
      border-bottom: 1px solid #edf2f7;
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
   }

   .edit-title {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #111827;
   }

   .edit-subtitle {
      margin-top: 4px;
      color: #64748b;
      font-size: 14px;
   }

   .edit-shell-body {
      padding: 22px;
      background: #f8fafc;
   }

   .edit-card {
      background: #fff;
      border-radius: 20px;
      border: 1px solid rgba(15, 23, 42, 0.06);
      box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
      overflow: hidden;
   }

   .edit-card-header {
      padding: 18px 22px;
      border-bottom: 1px solid #eef2f7;
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
   }

   .edit-card-header .card-title {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #111827;
   }

   .edit-card-body {
      padding: 22px;
   }

   .section-box {
      background: #f8fafc;
      border: 1px solid #edf2f7;
      border-radius: 18px;
      padding: 18px;
      margin-bottom: 22px;
   }

   .section-box h5 {
      margin: 0 0 12px;
      color: #0f172a;
      font-weight: 800;
      font-size: 16px;
   }

   .section-desc {
      color: #64748b;
      font-size: 13px;
      line-height: 1.6;
      margin-bottom: 16px;
   }

   .form-group label {
      font-size: 14px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 8px;
   }

   .form-control {
      min-height: 46px;
      border-radius: 12px;
      border: 1px solid #dbe4ee;
      background: #fbfdff;
      box-shadow: none !important;
      transition: all .2s ease;
   }

   .form-control:focus {
      border-color: #14b8a6;
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12) !important;
      background: #fff;
   }

   .form-control::placeholder {
      color: #94a3b8;
   }

   .help-text {
      margin-top: 6px;
      display: block;
      font-size: 12.5px;
      color: #94a3b8;
      line-height: 1.5;
   }

   .alert-custom {
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 600;
      margin-bottom: 18px;
   }

   .action-bar {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 8px;
   }

   .btn-soft {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 46px;
      padding: 0 18px;
      border-radius: 14px;
      font-weight: 800;
      text-decoration: none !important;
      border: 1px solid transparent;
      transition: all .2s ease;
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

   .btn-soft-secondary {
      background: #fff;
      color: #334155;
      border-color: #dbe4ee;
   }

   .btn-soft-secondary:hover {
      background: #f8fafc;
      color: #0f172a;
      transform: translateY(-1px);
   }

   .password-note {
      background: #ecfeff;
      border: 1px solid #c7f9f3;
      color: #0f766e;
      border-radius: 16px;
      padding: 14px 16px;
      font-size: 14px;
      line-height: 1.65;
      margin-bottom: 18px;
   }

   .password-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 16px;
   }

   .profile-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
   }

   .field-card {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      padding: 16px;
   }

   .field-card label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      font-weight: 800;
      color: #475569;
      text-transform: uppercase;
      letter-spacing: .03em;
   }

   .field-card .form-control {
      margin-bottom: 0;
   }

   @media (max-width: 991px) {
      .edit-shell-body {
         padding: 18px;
      }

      .profile-grid,
      .password-grid {
         grid-template-columns: 1fr;
      }
   }

   @media (max-width: 768px) {
      .edit-profile-hero {
         padding: 20px;
         border-radius: 20px;
      }

      .edit-profile-hero h2 {
         font-size: 22px;
      }

      .edit-card-body,
      .edit-card-header,
      .edit-shell-header {
         padding-left: 16px;
         padding-right: 16px;
      }

      .action-bar .btn-soft {
         width: 100%;
      }
   }
</style>

<div id="content-page" class="content-page">
    <?php render_flash(); ?>

    <div class="container-fluid edit-profile-page">
        <div class="edit-profile-hero">
            <h2>Sửa thông tin tài khoản</h2>
            <p>Cập nhật thông tin cá nhân, số điện thoại và mật khẩu ngay trong một trang duy nhất.</p>
        </div>

        <div class="edit-shell">
            <div class="edit-shell-header">
                <h4 class="edit-title mb-0">Chỉnh sửa hồ sơ</h4>
                <div class="edit-subtitle">Điền thông tin mới rồi lưu thay đổi để cập nhật tài khoản.</div>
            </div>

            <div class="edit-shell-body">
                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-custom"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <div class="edit-card">
                            <div class="edit-card-header d-flex justify-content-between align-items-center flex-wrap">
                                <div class="iq-header-title">
                                    <h4 class="card-title">Thông tin tài khoản</h4>
                                </div>
                            </div>

                            <div class="edit-card-body">
                                <form method="post">
                                    <div class="section-box">
                                        <h5>Thông tin cá nhân</h5>
                                        <div class="section-desc">
                                            Cập nhật tên tài khoản, email, họ tên và số điện thoại để đồng bộ với hồ sơ của bạn.
                                        </div>

                                        <div class="profile-grid">
                                            <div class="field-card">
                                                <label for="username">Tên tài khoản <span class="text-danger">*</span></label>
                                                <input type="text" name="username" class="form-control" id="username" 
                                                       value="<?php echo h($profile['username'] ?? ''); ?>" required>
                                            </div>

                                            <div class="field-card">
                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                <input type="email" name="email" class="form-control" id="email" 
                                                       value="<?php echo h($profile['email'] ?? ''); ?>" required>
                                            </div>

                                            <div class="field-card">
                                                <label for="fullname">Họ và tên <span class="text-danger">*</span></label>
                                                <input type="text" name="fullname" class="form-control" id="fullname" 
                                                       value="<?php echo h($profile['fullname'] ?? ''); ?>" required>
                                            </div>

                                            <div class="field-card">
                                                <label for="phone">Số điện thoại</label>
                                                <input type="text" name="phone" class="form-control" id="phone" 
                                                       value="<?php echo h($profile['phone'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="section-box">
                                        <h5>Đổi mật khẩu</h5>
                                        <div class="password-note">
                                            Để trống nếu bạn không muốn thay đổi mật khẩu. Khi đổi mật khẩu, hãy nhập đúng mật khẩu hiện tại.
                                        </div>

                                        <div class="password-grid">
                                            <div class="field-card">
                                                <label for="current_password">Mật khẩu hiện tại</label>
                                                <input type="password" name="current_password" class="form-control" 
                                                       id="current_password" placeholder="Nhập nếu muốn đổi mật khẩu">
                                            </div>

                                            <div class="field-card">
                                                <label for="new_password">Mật khẩu mới</label>
                                                <input type="password" name="new_password" class="form-control" 
                                                       id="new_password" placeholder="Ít nhất 6 ký tự">
                                            </div>

                                            <div class="field-card">
                                                <label for="confirm_password">Nhập lại mật khẩu mới</label>
                                                <input type="password" name="confirm_password" class="form-control" 
                                                       id="confirm_password" placeholder="Xác nhận mật khẩu mới">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="action-bar">
                                        <button type="submit" class="btn-soft btn-soft-primary">Lưu thay đổi</button>
                                        <a href="profile.php" class="btn-soft btn-soft-secondary">Hủy bỏ</a>
                                    </div>
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