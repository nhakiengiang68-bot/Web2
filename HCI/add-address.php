<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Thêm địa chỉ';
$pageBreadcrumb = 'Thêm địa chỉ';

$user = current_user();
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int)$user['id'] . ' LIMIT 1');

$error = '';
$success = '';

$receiverName  = trim((string)($_POST['receiver_name'] ?? ($profile['fullname'] ?? $user['fullname'] ?? $user['username'] ?? '')));
$phone         = trim((string)($_POST['phone'] ?? ($profile['phone'] ?? $user['phone'] ?? '')));
$addressDetail = trim((string)($_POST['address_detail'] ?? ''));
$ward          = trim((string)($_POST['ward'] ?? ''));
$district      = trim((string)($_POST['district'] ?? ''));
$province      = trim((string)($_POST['province'] ?? ''));
$isDefault     = isset($_POST['is_default']) ? 1 : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($receiverName === '' || $phone === '' || $addressDetail === '' || $ward === '' || $district === '' || $province === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin địa chỉ.';
    } elseif (!preg_match('/^[0-9]{9,11}$/', $phone)) {
        $error = 'Số điện thoại không hợp lệ.';
    } else {
        $db = db();

        if ($isDefault) {
            $stmtReset = mysqli_prepare($db, 'UPDATE address SET is_default = 0 WHERE user_id = ?');
            mysqli_stmt_bind_param($stmtReset, 'i', $user['id']);
            mysqli_stmt_execute($stmtReset);
            mysqli_stmt_close($stmtReset);
        }

        $stmt = mysqli_prepare(
            $db,
            'INSERT INTO address 
            (user_id, receiver_name, phone, address_detail, ward, district, province, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

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
        $newId = mysqli_insert_id($db);
        mysqli_stmt_close($stmt);

        if ($ok) {
            flash('success', 'Đã thêm địa chỉ mới.');
            redirect('profile.php?tab=manage-contact');
        } else {
            $error = 'Không thể lưu địa chỉ.';
        }
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<div id="content-page" class="content-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="iq-card">
                    <div class="iq-card-header d-flex justify-content-between">
                        <div class="iq-header-title">
                            <h4 class="card-title">Thêm địa chỉ mới</h4>
                        </div>
                    </div>

                    <div class="iq-card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Người nhận</label>
                                    <input type="text" name="receiver_name" class="form-control"
                                           value="<?php echo h($receiverName); ?>" required>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="<?php echo h($phone); ?>" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Địa chỉ chi tiết</label>
                                    <input type="text" name="address_detail" class="form-control"
                                           value="<?php echo h($addressDetail); ?>" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Phường / Xã</label>
                                    <input type="text" name="ward" class="form-control"
                                           value="<?php echo h($ward); ?>" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Quận / Huyện</label>
                                    <input type="text" name="district" class="form-control"
                                           value="<?php echo h($district); ?>" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Tỉnh / Thành phố</label>
                                    <input type="text" name="province" class="form-control"
                                           value="<?php echo h($province); ?>" required>
                                </div>

                                <div class="form-group col-md-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_default"
                                               name="is_default" value="1" <?php echo $isDefault ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="is_default">
                                            Đặt làm địa chỉ mặc định
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Lưu địa chỉ</button>
                            <a href="profile.php?tab=manage-contact" class="btn btn-secondary">Quay lại</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>