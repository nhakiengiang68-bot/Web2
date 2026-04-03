<?php
require_once 'includes/app.php';
$pageTitle = 'Đăng nhập';

if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    unset($_SESSION['cart']);
    flash('success', 'Bạn đã đăng xuất thành công.');
    redirect('sign-in.php');
}

$error = '';
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($loginValue === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        $loginEsc = esc($loginValue);
        $user = fetch_one("SELECT * FROM users WHERE (username = '{$loginEsc}' OR email = '{$loginEsc}') AND role <> 'admin' LIMIT 1");
        if (!$user) {
            $error = 'Tài khoản hoặc mật khẩu không đúng.';
        } elseif (($user['status'] ?? '') === 'locked') {
            $error = 'Tài khoản của bạn đang bị khóa.';
        } elseif (!password_matches($password, (string) $user['password'])) {
            $error = 'Tài khoản hoặc mật khẩu không đúng.';
        } else {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
            ];
            flash('success', 'Đăng nhập thành công.');
            redirect('index.php');
        }
    }
}

include 'includes/header.php';
?>
<section class="sign-in-page">
   <div class="container p-0">
      <div class="row no-gutters height-self-center">
         <div class="col-sm-12 align-self-center page-content rounded">
            <div class="row m-0">
               <div class="col-sm-12 sign-in-page-data">
                  <div class="sign-in-from bg-primary rounded">
                     <h3 class="mb-0 text-center text-white">Đăng nhập</h3>
                     <p class="text-center text-white">Nhập email hoặc tên tài khoản và mật khẩu.</p>
                     <?php render_flash(); ?>
                     <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
                     <form class="mt-4 form-text" method="post" novalidate>
                        <div class="form-group">
                           <label for="login">Email hoặc tên tài khoản:</label>
                           <input type="text" name="login" class="form-control mb-0" id="login" value="<?php echo h($loginValue); ?>" placeholder="Nhập email hoặc tên tài khoản" required>
                        </div>
                        <div class="form-group">
                           <label for="password">Mật khẩu</label>
                           <input type="password" name="password" class="form-control mb-0" id="password" placeholder="Nhập mật khẩu" required>
                           <a href="#" class="float-right text-dark">Quên mật khẩu?</a>
                        </div>
                        <div class="d-inline-block w-100">
                           <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                              <input type="checkbox" class="custom-control-input" id="remember">
                              <label class="custom-control-label" for="remember">Ghi nhớ</label>
                           </div>
                        </div>
                        <div class="sign-info text-center">
                           <button type="submit" class="btn btn-white d-block w-100 mb-2">Đăng nhập</button>
                           <span class="text-dark dark-color d-inline-block line-height-2">Không có tài khoản?<a href="sign-up.php" class="text-white"> Đăng ký</a></span>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<?php include 'includes/footer.php'; ?>
