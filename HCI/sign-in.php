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

   .auth-point i,
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

   .auth-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 22px 0;
      color: #94a3b8;
      font-size: 13px;
      font-weight: 600;
   }

   .auth-divider::before,
   .auth-divider::after {
      content: "";
      height: 1px;
      flex: 1;
      background: #e2e8f0;
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
                        <span>🔐</span> Khu vực đăng nhập
                     </div>
                     <h2>Chào mừng bạn quay lại</h2>
                     <p>
                        Đăng nhập để mua sắm nhanh hơn, lưu giỏ hàng, theo dõi đơn hàng và quản lý tài khoản dễ dàng.
                     </p>
                  </div>

                  <div class="auth-points">
                     <div class="auth-point">
                        <div class="icon">🛒</div>
                        <div>
                           <strong>Mua sắm tiện lợi</strong>
                           <span>Thêm sản phẩm vào giỏ và đặt hàng chỉ trong vài bước.</span>
                        </div>
                     </div>

                     <div class="auth-point">
                        <div class="icon">📦</div>
                        <div>
                           <strong>Theo dõi đơn hàng</strong>
                           <span>Xem lại lịch sử mua hàng và trạng thái đơn bất cứ lúc nào.</span>
                        </div>
                     </div>

                     <div class="auth-point">
                        <div class="icon">⭐</div>
                        <div>
                           <strong>Lưu thông tin cá nhân</strong>
                           <span>Giúp thanh toán nhanh hơn và trải nghiệm mượt hơn.</span>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="col-lg-7">
               <div class="auth-right">
                  <div class="auth-header">
                     <h3>Đăng nhập</h3>
                     <p>Nhập email hoặc tên tài khoản và mật khẩu để tiếp tục.</p>
                  </div>

                  <?php render_flash(); ?>
                  <?php if ($error): ?>
                     <div class="alert alert-danger alert-custom"><?php echo h($error); ?></div>
                  <?php endif; ?>

                  <form class="auth-form" method="post" novalidate>
                     <div class="form-group">
                        <label for="login">Email hoặc tên tài khoản</label>
                        <input
                           type="text"
                           name="login"
                           class="form-control"
                           id="login"
                           value="<?php echo h($loginValue); ?>"
                           placeholder="Nhập email hoặc tên tài khoản"
                           required
                        >
                     </div>

                     <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input
                           type="password"
                           name="password"
                           class="form-control"
                           id="password"
                           placeholder="Nhập mật khẩu"
                           required
                        >
                        <div class="auth-links mt-2">
                           <span></span>
                           <a href="#">Quên mật khẩu?</a>
                        </div>
                     </div>

                     <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap: 12px;">
                        <div class="custom-control custom-checkbox">
                           <input type="checkbox" class="custom-control-input" id="remember">
                           <label class="custom-control-label" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                     </div>

                     <button type="submit" class="auth-submit">Đăng nhập</button>

                     <div class="auth-footer">
                        Không có tài khoản?
                        <a href="sign-up.php">Đăng ký ngay</a>
                     </div>

                     <div class="auth-mini-note">
                        Bằng việc đăng nhập, bạn đồng ý sử dụng tài khoản để quản lý trải nghiệm mua sắm của mình.
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>

<?php include 'includes/footer.php'; ?>