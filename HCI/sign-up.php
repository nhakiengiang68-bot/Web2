<?php
require_once 'includes/app.php';
$pageTitle = 'Đăng ký';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif (!username_valid($username)) {
        $error = 'Tên tài khoản chỉ được chứa chữ cái, số và dấu gạch dưới.';
    } elseif (!gmail_valid($email)) {
        $error = 'Email phải có đuôi @gmail.com.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm) {
        $error = 'Mật khẩu xác nhận không khớp.';
    } else {
        $usernameEsc = esc($username);
        $emailEsc = esc($email);
        $exists = fetch_one("SELECT id FROM users WHERE username = '{$usernameEsc}' OR email = '{$emailEsc}' LIMIT 1");
        if ($exists) {
            $error = 'Tên tài khoản hoặc email đã tồn tại.';
        } else {
            $fullname = $username;
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = 'active';
            $role = 'user';
            $phone = '';
            $stmt = mysqli_prepare(db(), 'INSERT INTO users (username, password, fullname, phone, email, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sssssss', $username, $hash, $fullname, $phone, $email, $role, $status);
            if (mysqli_stmt_execute($stmt)) {
                $id = mysqli_insert_id(db());
                mysqli_stmt_close($stmt);
                $_SESSION['user'] = [
                    'id' => $id,
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => $role,
                ];
                flash('success', 'Đăng ký thành công.');
                redirect('index.php');
            }
            $error = 'Không thể tạo tài khoản mới.';
            mysqli_stmt_close($stmt);
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
                     <h3 class="mb-0 text-center text-white">Đăng ký</h3>
                     <p class="text-center text-white">Nhập thông tin tài khoản để tạo mới.</p>
                     <?php render_flash(); ?>
                     <?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
                     <form class="mt-4 form-text" method="post" id="signup-form" novalidate>
                        <div class="form-group">
                           <label for="username">Tên tài khoản</label>
                           <input type="text" name="username" class="form-control mb-0" id="username" placeholder="Nhập tên tài khoản" required pattern="^[A-Za-z0-9_]+$" value="<?php echo h($_POST['username'] ?? ''); ?>">
                           <small class="text-white-50">Chỉ dùng chữ cái, số và dấu gạch dưới.</small>
                        </div>
                        <div class="form-group">
                           <label for="email">Email</label>
                           <input type="email" name="email" class="form-control mb-0" id="email" placeholder="Nhập email" required value="<?php echo h($_POST['email'] ?? ''); ?>">
                           <small class="text-white-50">Email phải kết thúc bằng @gmail.com.</small>
                        </div>
                        <div class="form-group">
                           <label for="password">Mật khẩu</label>
                           <input type="password" name="password" class="form-control mb-0" id="password" placeholder="Mật khẩu" required minlength="6">
                        </div>
                        <div class="form-group">
                           <label for="confirm_password">Nhập lại mật khẩu</label>
                           <input type="password" name="confirm_password" class="form-control mb-0" id="confirm_password" placeholder="Nhập lại mật khẩu" required minlength="6">
                        </div>
                        <div class="d-inline-block w-100">
                           <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                              <input type="checkbox" class="custom-control-input" id="terms" required>
                              <label class="custom-control-label" for="terms">Tôi đồng ý <a href="#" class="text-light">Điều khoản và Điều kiện</a></label>
                           </div>
                        </div>
                        <div class="sign-info text-center">
                           <button type="submit" class="btn btn-white d-block w-100 mb-2">Đăng ký</button>
                           <span class="text-dark d-inline-block line-height-2">Đã có tài khoản?
                              <a href="sign-in.php" class="text-white">Đăng nhập</a></span>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
<script>
(function () {
  const form = document.getElementById('signup-form');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const usernameOk = /^[A-Za-z0-9_]+$/.test(username);
    const emailOk = /^[A-Za-z0-9._%+-]+@gmail\.com$/i.test(email);
    if (!usernameOk) {
      alert('Tên tài khoản chỉ được chứa chữ cái, số và dấu gạch dưới.');
      e.preventDefault();
      return;
    }
    if (!emailOk) {
      alert('Email phải có đuôi @gmail.com.');
      e.preventDefault();
      return;
    }
    if (password.length < 6) {
      alert('Mật khẩu phải có ít nhất 6 ký tự.');
      e.preventDefault();
      return;
    }
    if (password !== confirm) {
      alert('Mật khẩu xác nhận không khớp.');
      e.preventDefault();
    }
  });
})();
</script>
<?php include 'includes/footer.php'; ?>
