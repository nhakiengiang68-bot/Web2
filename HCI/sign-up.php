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

    // THÊM: thông tin địa chỉ giao hàng mặc định
    $receiver_name   = trim((string) ($_POST['receiver_name'] ?? ''));
    $phone           = trim((string) ($_POST['phone'] ?? ''));
    $address_detail  = trim((string) ($_POST['address_detail'] ?? ''));
    $ward            = trim((string) ($_POST['ward'] ?? ''));
    $district        = trim((string) ($_POST['district'] ?? ''));
    $province        = trim((string) ($_POST['province'] ?? ''));

    if (
        $username === '' || $email === '' || $password === '' || $confirm === '' ||
        $receiver_name === '' || $phone === '' || $address_detail === '' ||
        $ward === '' || $district === '' || $province === ''
    ) {
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
            $fullname = $receiver_name; // dùng tên người nhận làm tên hiển thị
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $status = 'active';
            $role = 'customer'; // THÊM/SỬA: tài khoản đăng ký là khách hàng
            $phoneUser = $phone;

            $stmt = mysqli_prepare(
                db(),
                'INSERT INTO users (username, password, fullname, phone, email, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            mysqli_stmt_bind_param($stmt, 'sssssss', $username, $hash, $fullname, $phoneUser, $email, $role, $status);

            if (mysqli_stmt_execute($stmt)) {
                $id = mysqli_insert_id(db());
                mysqli_stmt_close($stmt);

                // THÊM: lưu địa chỉ giao hàng mặc định
                $stmtAddress = mysqli_prepare(
                    db(),
                    'INSERT INTO address (user_id, receiver_name, phone, address_detail, ward, district, province, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, 1)'
                );
                mysqli_stmt_bind_param(
                    $stmtAddress,
                    'issssss',
                    $id,
                    $receiver_name,
                    $phone,
                    $address_detail,
                    $ward,
                    $district,
                    $province
                );
                mysqli_stmt_execute($stmtAddress);
                mysqli_stmt_close($stmtAddress);

                $_SESSION['user'] = [
                    'id' => $id,
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'phone' => $phoneUser,
                    'role' => $role,
                ];

                flash('success', 'Đăng ký thành công.');
                redirect('sign-in.php');
            }

            $error = 'Không thể tạo tài khoản mới.';
            mysqli_stmt_close($stmt);
        }
    }
}

include 'includes/header.php';
?>

<style>
   .auth-page {
      min-height: calc(100vh - 90px);
      display: flex;
      align-items: center;
      padding: 40px 0;
      background:
         radial-gradient(circle at top left, rgba(20, 184, 166, 0.14), transparent 32%),
         radial-gradient(circle at bottom right, rgba(34, 197, 94, 0.12), transparent 28%),
         linear-gradient(180deg, #f8fbff 0%, #f5f9ff 100%);
   }

   .auth-shell {
      max-width: 1180px;
      margin: 0 auto;
   }

   .auth-card {
      background: #fff;
      border-radius: 28px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
      border: 1px solid rgba(15, 23, 42, 0.06);
   }

   .auth-left {
      background: linear-gradient(135deg, #0f766e 0%, #14b8a6 45%, #22c55e 100%);
      color: #fff;
      padding: 42px 38px;
      position: relative;
      overflow: hidden;
      min-height: 100%;
   }

   .auth-left::before,
   .auth-left::after {
      content: "";
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.10);
      pointer-events: none;
   }

   .auth-left::before {
      width: 180px;
      height: 180px;
      top: -50px;
      right: -40px;
   }

   .auth-left::after {
      width: 120px;
      height: 120px;
      bottom: -35px;
      left: -20px;
   }

   .auth-brand {
      position: relative;
      z-index: 1;
   }

   .auth-brand .badge-soft {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.16);
      color: #fff;
      font-weight: 700;
      font-size: 13px;
      letter-spacing: .02em;
      margin-bottom: 18px;
   }

   .auth-brand h2 {
      font-size: 34px;
      line-height: 1.15;
      font-weight: 800;
      margin: 0 0 14px;
      letter-spacing: -.03em;
   }

   .auth-brand p {
      margin: 0;
      color: rgba(255, 255, 255, 0.92);
      font-size: 15px;
      line-height: 1.7;
   }

   .auth-points {
      position: relative;
      z-index: 1;
      margin-top: 34px;
      display: grid;
      gap: 14px;
   }

   .auth-point {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      background: rgba(255, 255, 255, 0.10);
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 18px;
      padding: 14px 16px;
      backdrop-filter: blur(8px);
   }

   .auth-point .icon {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.16);
      font-size: 18px;
      flex: 0 0 auto;
   }

   .auth-point strong {
      display: block;
      font-size: 14px;
      margin-bottom: 3px;
   }

   .auth-point span {
      display: block;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.88);
      line-height: 1.55;
   }

   .auth-right {
      padding: 42px 40px;
      background: #fff;
   }

   .auth-header {
      margin-bottom: 22px;
   }

   .auth-header h3 {
      font-size: 28px;
      font-weight: 800;
      color: #111827;
      margin: 0 0 8px;
      letter-spacing: -.02em;
   }

   .auth-header p {
      margin: 0;
      color: #64748b;
      font-size: 14px;
      line-height: 1.6;
   }

   .auth-form .form-group {
      margin-bottom: 18px;
   }

   .auth-form label {
      display: block;
      font-size: 14px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 8px;
   }

   .auth-form .form-control {
      height: 50px;
      border-radius: 14px;
      border: 1px solid #dbe4ee;
      background: #fbfdff;
      padding-left: 16px;
      padding-right: 16px;
      box-shadow: none !important;
      transition: all .2s ease;
   }

   .auth-form .form-control:focus {
      border-color: #14b8a6;
      box-shadow: 0 0 0 4px rgba(20, 184, 166, 0.12) !important;
      background: #fff;
   }

   .auth-form .form-control::placeholder {
      color: #94a3b8;
   }

   .auth-help {
      margin-top: 6px;
      display: block;
      font-size: 12.5px;
      color: #94a3b8;
      line-height: 1.5;
   }

   .auth-form .custom-control-label {
      font-size: 14px;
      font-weight: 600;
      color: #475569;
   }

   .auth-links {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 8px;
   }

   .auth-links a {
      color: #0f766e;
      font-weight: 700;
      text-decoration: none !important;
   }

   .auth-links a:hover {
      text-decoration: underline !important;
   }

   .auth-submit {
      width: 100%;
      height: 50px;
      border: none;
      border-radius: 14px;
      font-weight: 800;
      font-size: 15px;
      color: #fff;
      background: linear-gradient(135deg, #0f766e, #14b8a6);
      box-shadow: 0 14px 30px rgba(20, 184, 166, 0.22);
      transition: all .2s ease;
   }

   .auth-submit:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 36px rgba(20, 184, 166, 0.28);
      color: #fff;
   }

   .auth-footer {
      text-align: center;
      margin-top: 18px;
      color: #64748b;
      font-size: 14px;
   }

   .auth-footer a {
      color: #0f766e;
      font-weight: 800;
      text-decoration: none !important;
   }

   .auth-footer a:hover {
      text-decoration: underline !important;
   }

   .alert-custom {
      border-radius: 14px;
      padding: 14px 16px;
      font-weight: 600;
      margin-bottom: 18px;
   }

   .auth-mini-note {
      margin-top: 16px;
      font-size: 13px;
      color: #94a3b8;
      line-height: 1.6;
      text-align: center;
   }

   @media (max-width: 991px) {
      .auth-left {
         padding: 34px 28px;
      }

      .auth-right {
         padding: 34px 28px;
      }
   }

   @media (max-width: 767px) {
      .auth-page {
         padding: 22px 0;
      }

      .auth-card {
         border-radius: 22px;
      }

      .auth-left,
      .auth-right {
         padding: 24px 20px;
      }

      .auth-brand h2,
      .auth-header h3 {
         font-size: 24px;
      }
   }
</style>

<section class="auth-page">
   <div class="container auth-shell">
      <div class="auth-card">
         <div class="row no-gutters">
            <div class="col-lg-5">
               <div class="auth-left">
                  <div class="auth-brand">
                     <div class="badge-soft">
                        <span>✨</span> Tạo tài khoản mới
                     </div>
                     <h2>Chào mừng bạn đến với hệ thống</h2>
                     <p>
                        Tạo tài khoản để mua sắm nhanh hơn, lưu thông tin cá nhân và quản lý địa chỉ giao hàng dễ dàng.
                     </p>
                  </div>

                  <div class="auth-points">
                     <div class="auth-point">
                        <div class="icon">🛒</div>
                        <div>
                           <strong>Mua sắm thuận tiện</strong>
                           <span>Thêm sản phẩm, theo dõi giỏ hàng và thanh toán nhanh hơn.</span>
                        </div>
                     </div>

                     <div class="auth-point">
                        <div class="icon">📍</div>
                        <div>
                           <strong>Lưu địa chỉ mặc định</strong>
                           <span>Giúp đặt hàng tiện hơn và hạn chế nhập lại thông tin.</span>
                        </div>
                     </div>

                     <div class="auth-point">
                        <div class="icon">🔒</div>
                        <div>
                           <strong>Quản lý tài khoản</strong>
                           <span>Thông tin của bạn được sắp xếp rõ ràng và dễ theo dõi.</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-lg-7">
               <div class="auth-right">
                  <div class="auth-header">
                     <h3>Đăng ký</h3>
                     <p>Nhập thông tin tài khoản và địa chỉ giao hàng mặc định để bắt đầu.</p>
                  </div>

                  <?php render_flash(); ?>
                  <?php if ($error): ?>
                     <div class="alert alert-danger alert-custom"><?php echo h($error); ?></div>
                  <?php endif; ?>

                  <form class="auth-form" method="post" id="signup-form" novalidate>
                     <div class="form-group">
                        <label for="username">Tên tài khoản</label>
                        <input type="text" name="username" class="form-control mb-0" id="username" placeholder="Nhập tên tài khoản" required pattern="^[A-Za-z0-9_]+$" value="<?php echo h($_POST['username'] ?? ''); ?>">
                        <small class="auth-help">Chỉ dùng chữ cái, số và dấu gạch dưới.</small>
                     </div>

                     <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control mb-0" id="email" placeholder="Nhập email" required value="<?php echo h($_POST['email'] ?? ''); ?>">
                        <small class="auth-help">Email phải kết thúc bằng @gmail.com.</small>
                     </div>

                     <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" name="password" class="form-control mb-0" id="password" placeholder="Mật khẩu" required minlength="6">
                     </div>

                     <div class="form-group">
                        <label for="confirm_password">Nhập lại mật khẩu</label>
                        <input type="password" name="confirm_password" class="form-control mb-0" id="confirm_password" placeholder="Nhập lại mật khẩu" required minlength="6">
                     </div>

                     <div class="form-group">
                        <label for="receiver_name">Họ tên người nhận</label>
                        <input type="text" name="receiver_name" class="form-control mb-0" id="receiver_name" placeholder="Nhập họ tên người nhận" required value="<?php echo h($_POST['receiver_name'] ?? ''); ?>">
                     </div>

                     <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control mb-0" id="phone" placeholder="Nhập số điện thoại" required value="<?php echo h($_POST['phone'] ?? ''); ?>">
                     </div>

                     <div class="form-group">
                        <label for="address_detail">Địa chỉ cụ thể</label>
                        <input type="text" name="address_detail" class="form-control mb-0" id="address_detail" placeholder="Số nhà, tên đường..." required value="<?php echo h($_POST['address_detail'] ?? ''); ?>">
                     </div>

                     <div class="form-group">
                        <label for="ward">Phường/Xã</label>
                        <input type="text" name="ward" class="form-control mb-0" id="ward" placeholder="Nhập phường/xã" required value="<?php echo h($_POST['ward'] ?? ''); ?>">
                     </div>

                     <div class="form-group">
                        <label for="district">Quận/Huyện</label>
                        <input type="text" name="district" class="form-control mb-0" id="district" placeholder="Nhập quận/huyện" required value="<?php echo h($_POST['district'] ?? ''); ?>">
                     </div>

                     <div class="form-group">
                        <label for="province">Tỉnh/Thành phố</label>
                        <input type="text" name="province" class="form-control mb-0" id="province" placeholder="Nhập tỉnh/thành phố" required value="<?php echo h($_POST['province'] ?? ''); ?>">
                     </div>

                     <div class="d-inline-block w-100">
                        <div class="custom-control custom-checkbox d-inline-block mt-2 pt-1">
                           <input type="checkbox" class="custom-control-input" id="terms" required>
                           <label class="custom-control-label" for="terms">Tôi đồng ý <a href="#" class="text-light">Điều khoản và Điều kiện</a></label>
                        </div>
                     </div>

                     <div class="mt-4">
                        <button type="submit" class="auth-submit">Đăng ký</button>
                     </div>

                     <div class="auth-footer">
                        Đã có tài khoản?
                        <a href="sign-in.php">Đăng nhập</a>
                     </div>

                     <div class="auth-mini-note">
                        Tài khoản sẽ được tạo với vai trò khách hàng và địa chỉ giao hàng mặc định.
                     </div>
                  </form>
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

    const receiverName = document.getElementById('receiver_name').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const addressDetail = document.getElementById('address_detail').value.trim();
    const ward = document.getElementById('ward').value.trim();
    const district = document.getElementById('district').value.trim();
    const province = document.getElementById('province').value.trim();

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
      return;
    }

    if (!receiverName || !phone || !addressDetail || !ward || !district || !province) {
      alert('Vui lòng nhập đầy đủ địa chỉ giao hàng mặc định.');
      e.preventDefault();
    }
  });
})();
</script>

<?php include 'includes/footer.php'; ?>