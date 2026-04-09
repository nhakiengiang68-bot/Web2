<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Hồ sơ cá nhân';
$pageBreadcrumb = 'Hồ sơ cá nhân';

$user = current_user();

// Lấy thông tin đầy đủ từ database
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int)$user['id'] . ' LIMIT 1');

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<div id="content-page" class="content-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between align-items-center">
                        <div class="iq-header-title">
                            <h4 class="card-title">Hồ sơ cá nhân</h4>
                        </div>
                        <a href="edit-profile.php" class="btn btn-primary">
                            <i class="ri-edit-line"></i> Sửa thông tin
                        </a>
                    </div>

                    <div class="iq-card-body">
                        <div class="row">

                            <!-- Phần Avatar + Thông tin cơ bản -->
                            <div class="col-md-4 text-center mb-4">
                                <div class="profile-img-edit mb-3">
                                    <img class="profile-pic rounded-circle border" 
                                         src="<?php echo h($profile['avatar'] ?? 'images/user/1.jpg'); ?>" 
                                         alt="Avatar"
                                         style="width: 160px; height: 160px; object-fit: cover;">
                                </div>
                                <h5 class="mb-1"><?php echo h($profile['fullname'] ?? 'Chưa cập nhật'); ?></h5>
                                <p class="text-muted mb-0">@<?php echo h($profile['username'] ?? ''); ?></p>
                            </div>

                            <!-- Phần chi tiết thông tin -->
                            <div class="col-md-8">
                                <div class="row">

                                    <!-- Thông tin tài khoản -->
                                    <div class="col-12">
                                        <h5 class="mb-3 text-primary">
                                            <i class="ri-user-3-line"></i> Thông tin tài khoản
                                        </h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Tên tài khoản</label>
                                        <p class="mb-0 fw-bold"><?php echo h($profile['username'] ?? ''); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Email</label>
                                        <p class="mb-0 fw-bold"><?php echo h($profile['email'] ?? ''); ?></p>
                                    </div>

                                    <!-- Thông tin cá nhân -->
                                    <div class="col-12 mt-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="ri-profile-line"></i> Thông tin cá nhân
                                        </h5>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Họ và tên</label>
                                        <p class="mb-0"><?php echo h($profile['fullname'] ?? 'Chưa cập nhật'); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Số điện thoại</label>
                                        <p class="mb-0"><?php echo h($profile['phone'] ?? 'Chưa cập nhật'); ?></p>
                                    </div>

                                    <?php if (!empty($profile['gender'])): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Giới tính</label>
                                        <p class="mb-0"><?php echo h($profile['gender']) === 'male' ? 'Nam' : 'Nữ'; ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($profile['dob'])): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small d-block">Ngày sinh</label>
                                        <p class="mb-0"><?php echo date('d/m/Y', strtotime($profile['dob'])); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($profile['address'])): ?>
                                    <div class="col-12 mb-3">
                                        <label class="text-muted small d-block">Địa chỉ</label>
                                        <p class="mb-0"><?php echo nl2br(h($profile['address'])); ?></p>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <!-- Các nút hành động -->
                        <div class="border-top pt-4 mt-4">
                            <a href="edit-profile.php" class="btn btn-primary mr-2">
                                <i class="ri-edit-line"></i> Sửa thông tin tài khoản
                            </a>
                            <a href="favourite.php" class="btn btn-outline-info mr-2">
                                <i class="ri-heart-line"></i> Danh sách yêu thích
                            </a>
                            <a href="change-password.php" class="btn btn-outline-warning">
                                <i class="ri-lock-password-line"></i> Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>