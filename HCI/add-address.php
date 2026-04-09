<?php
require_once 'includes/app.php';
require_login();

$pageTitle = 'Thêm địa chỉ';
$pageBreadcrumb = 'Thêm địa chỉ';

$user = current_user();
$profile = fetch_one('SELECT * FROM users WHERE id = ' . (int)$user['id'] . ' LIMIT 1');

$addresses = fetch_all('SELECT * FROM address WHERE user_id = ' . (int)$user['id']);
$useExisting = isset($_POST['use_existing']) ? (int)$_POST['use_existing'] : (count($addresses) ? 1 : 0);
$selectedAddressId = (int)($_POST['address_id'] ?? 0);

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

    // =========================
    // CASE 1: DÙNG ĐỊA CHỈ CÓ SẴN
    // =========================
    if ($useExisting === 1) {
        if ($selectedAddressId <= 0) {
            $error = 'Vui lòng chọn địa chỉ.';
        } else {
            $checkAddress = fetch_one(
                'SELECT * FROM address WHERE id = ' . (int)$selectedAddressId . ' AND user_id = ' . (int)$user['id'] . ' LIMIT 1'
            );

            if (!$checkAddress) {
                $error = 'Địa chỉ đã chọn không hợp lệ.';
            } else {
                flash('success', 'Đã chọn địa chỉ giao hàng.');
                redirect('profile.php?tab=manage-contact');
            }
        }
    } else {
        // =========================
        // CASE 2: NHẬP ĐỊA CHỈ MỚI
        // =========================
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
            mysqli_stmt_close($stmt);

            if ($ok) {
                flash('success', 'Đã thêm địa chỉ mới.');
                redirect('profile.php?tab=manage-contact');
            } else {
                $error = 'Không thể lưu địa chỉ.';
            }
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
                                <div class="form-group col-md-12">
                                    <label><strong>Chọn cách sử dụng địa chỉ</strong></label>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="use_existing" name="use_existing" value="1" <?php echo $useExisting ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="use_existing">Dùng địa chỉ đã lưu trong tài khoản</label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" id="use_new" name="use_existing" value="0" <?php echo !$useExisting ? 'checked' : ''; ?>>
                                        <label class="custom-control-label" for="use_new">Nhập địa chỉ giao hàng mới</label>
                                    </div>
                                </div>

                                <div class="form-group col-md-12" id="existing-address-box">
                                    <label>Địa chỉ đã lưu</label>
                                    <select name="address_id" class="form-control">
                                        <option value="">-- Chọn địa chỉ --</option>
                                        <?php foreach ($addresses as $addr): ?>
                                            <option value="<?php echo (int)$addr['id']; ?>" <?php echo ($selectedAddressId === (int)$addr['id']) ? 'selected' : ''; ?>>
                                                <?php
                                                echo h(
                                                    $addr['receiver_name'] . ' | ' .
                                                    $addr['phone'] . ' | ' .
                                                    $addr['address_detail'] . ', ' .
                                                    $addr['ward'] . ', ' .
                                                    $addr['district'] . ', ' .
                                                    $addr['province']
                                                );
                                                echo !empty($addr['is_default']) ? ' (Mặc định)' : '';
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div id="new-address-box" class="row w-100">
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

<script>
(function () {
    function toggleAddressMode() {
        const useExisting = document.getElementById('use_existing').checked;
        const existingBox = document.getElementById('existing-address-box');
        const newBox = document.getElementById('new-address-box');

        existingBox.style.display = useExisting ? 'block' : 'none';
        newBox.style.display = useExisting ? 'none' : 'flex';

        const newInputs = newBox.querySelectorAll('input');
        newInputs.forEach(function (input) {
            input.disabled = useExisting;
        });

        const select = existingBox.querySelector('select');
        if (select) {
            select.disabled = !useExisting;
        }
    }

    const useExistingRadio = document.getElementById('use_existing');
    const useNewRadio = document.getElementById('use_new');

    if (useExistingRadio && useNewRadio) {
        useExistingRadio.addEventListener('change', toggleAddressMode);
        useNewRadio.addEventListener('change', toggleAddressMode);
        toggleAddressMode();
    }
})();
</script>

<?php include 'includes/footer.php'; ?>