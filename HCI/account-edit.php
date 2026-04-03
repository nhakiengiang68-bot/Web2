<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Sửa tài khoản';
$pageBreadcrumb = 'Sửa tài khoản';
$user = current_user();
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int) $user['id'] . ' LIMIT 1');
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $fullname = trim((string) ($_POST['fullname'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $fullname === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!username_valid($username)) {
        $error = 'Tên tài khoản không hợp lệ.';
    } elseif (!gmail_valid($email)) {
        $error = 'Email phải có đuôi @gmail.com.';
    } elseif (!password_matches($currentPassword, (string) $profile['password'])) {
        $error = 'Mật khẩu hiện tại không đúng.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $exists = fetch_one('SELECT id FROM users WHERE (username = "' . esc($username) . '" OR email = "' . esc($email) . '") AND id <> ' . (int) $user['id'] . ' LIMIT 1');
        if ($exists) {
            $error = 'Tên tài khoản hoặc email đã tồn tại.';
        } else {
            $newHash = $newPassword !== '' ? password_hash($newPassword, PASSWORD_DEFAULT) : $profile['password'];
            $stmt = mysqli_prepare(db(), 'UPDATE users SET username = ?, email = ?, fullname = ?, phone = ?, password = ? WHERE id = ?');
            mysqli_stmt_bind_param($stmt, 'sssssi', $username, $email, $fullname, $phone, $newHash, $user['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => $username,
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'role' => $user['role'],
            ];
            flash('success', 'Đã cập nhật tài khoản.');
            redirect('profile.php');
        }
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
         <div class="col-lg-12">
            <div class="iq-card">
               <div class="iq-card-header d-flex justify-content-between"><div class="iq-header-title"><h4 class="card-title">Sửa tài khoản</h4></div></div>
               <div class="iq-card-body">
                  <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
                  <form method="post">
                     <div class="row align-items-center">
                        <div class="form-group col-sm-6"><label for="username">Tên tài khoản:</label><input type="text" name="username" class="form-control" id="username" value="<?php echo h($profile['username'] ?? ''); ?>" required></div>
                        <div class="form-group col-sm-6"><label for="email">Email:</label><input type="email" name="email" class="form-control" id="email" value="<?php echo h($profile['email'] ?? ''); ?>" required></div>
                        <div class="form-group col-sm-6"><label for="fullname">Họ và tên:</label><input type="text" name="fullname" class="form-control" id="fullname" value="<?php echo h($profile['fullname'] ?? ''); ?>" required></div>
                        <div class="form-group col-sm-6"><label for="phone">Số điện thoại:</label><input type="text" name="phone" class="form-control" id="phone" value="<?php echo h($profile['phone'] ?? ''); ?>"></div>
                        <div class="form-group col-sm-6"><label for="current_password">Mật khẩu hiện tại:</label><input type="password" name="current_password" class="form-control" id="current_password" required></div>
                        <div class="form-group col-sm-6"><label for="new_password">Mật khẩu mới:</label><input type="password" name="new_password" class="form-control" id="new_password" placeholder="Để trống nếu không đổi"></div>
                        <div class="form-group col-sm-6"><label for="confirm_password">Nhập lại mật khẩu mới:</label><input type="password" name="confirm_password" class="form-control" id="confirm_password"></div>
                     </div>
                     <button type="submit" class="btn btn-primary mr-2">Lưu</button>
                     <a href="profile.php" class="btn btn-danger mr-2">Hủy bỏ</a>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
