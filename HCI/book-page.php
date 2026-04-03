<?php
require_once 'includes/app.php';
$pageTitle = 'Chi tiết sản phẩm';
$pageBreadcrumb = 'Chi tiết sản phẩm';

if (isset($_GET['favorite'])) {
    $user = current_user();
    if (!$user) {
        flash('warning', 'Vui lòng đăng nhập để thêm vào yêu thích.');
        redirect('sign-in.php');
    }
    $toggleId = (int) ($_GET['id'] ?? 0);
    if ($toggleId > 0) {
        $wasFavourite = favourite_exists((int) $user['id'], $toggleId);
        if (favourite_toggle((int) $user['id'], $toggleId)) {
            flash('success', $wasFavourite ? 'Đã xóa khỏi yêu thích.' : 'Đã thêm vào yêu thích.');
        }
    }
    redirect('book-page.php?id=' . $toggleId);
}

if (isset($_GET['add'])) {
    $addId = (int) $_GET['add'];
    if ($addId > 0) {
        cart_add($addId, 1);
        flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }
    redirect('Checkout.php');
}

$bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
$currentBook = $bookId > 0 ? book_detail($bookId) : null;
$related = [];
$isFavourite = false;
if ($currentBook) {
    $user = current_user();
    $isFavourite = $user ? favourite_exists((int) $user['id'], (int) $currentBook['id']) : false;

    $categoryIds = book_category_ids((int) $currentBook['id']);
    if ($categoryIds) {
        $categoryList = implode(',', array_map('intval', $categoryIds));
        $related = fetch_all('
            SELECT DISTINCT b.*, a.fullname AS author_name
            FROM books b
            LEFT JOIN authors a ON a.id = b.author_id
            JOIN book_category bc ON bc.book_id = b.id
            WHERE ' . books_visible_condition('b') . ' AND b.id <> ' . (int) $currentBook['id'] . ' AND bc.category_id IN (' . $categoryList . ')
            ORDER BY b.updated_at DESC, b.id DESC
            LIMIT 6
        ');
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
         <div class="col-sm-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
               <div class="iq-card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0">Thông tin</h4>
               </div>
               <div class="iq-card-body pb-0">
                  <?php if ($currentBook): ?>
                     <div class="description-contens align-items-top row">
                        <div class="col-md-5 mb-4">
                           <div class="col-12 p-0">
                              <img src="<?php echo h(book_image_src($currentBook, (int) $currentBook['id'])); ?>" class="img-fluid w-100 rounded" alt="">
                           </div>
                        </div>
                        <div class="col-md-7">
                           <h3 class="mb-3"><?php echo h($currentBook['bookname']); ?></h3>
                           <p class="mb-1">Tác giả: <strong><?php echo h($currentBook['author_name'] ?? ''); ?></strong></p>
                           <p class="mb-1">Mã sách: <strong><?php echo h($currentBook['book_code'] ?? ''); ?></strong></p>
                           <p class="mb-1">Phân loại: <strong><?php echo h($currentBook['categories_text'] ?? ''); ?></strong></p>
                           <div class="price d-flex align-items-center my-3"><h4 class="text-primary mb-0"><?php echo vn_money(book_sell_price($currentBook)); ?> đ</h4></div>
                           <div class="d-flex align-items-center mb-3">
                              <span class="font-size-11 text-warning mr-2"><?php echo render_stars(); ?></span>
                              <span class="text-muted">Kho: <?php echo (int) $currentBook['stock_quantity']; ?></span>
                           </div>
                           <p class="mb-4"><?php echo nl2br(h($currentBook['info'] ?? 'Chưa có mô tả.')); ?></p>
                           <div class="d-flex flex-wrap">
                              <a href="Checkout.php?add=<?php echo (int) $currentBook['id']; ?>" class="btn btn-primary mr-2 mb-2">Thêm vào giỏ</a>
                              <a href="book-page.php?id=<?php echo (int) $currentBook['id']; ?>&favorite=1" class="btn btn-outline-danger mr-2 mb-2"><?php echo $isFavourite ? 'Bỏ yêu thích' : 'Yêu thích'; ?></a>
                              <a href="Checkout-preview.php" class="btn btn-outline-primary mb-2">Xem thanh toán</a>
                           </div>
                        </div>
                     </div>
                  <?php else: ?>
                     <div class="alert alert-info">Không tìm thấy sản phẩm.</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

      <?php if ($related): ?>
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
               <div class="iq-card-header d-flex justify-content-between align-items-center position-relative">
                  <div class="iq-header-title"><h4 class="card-title mb-0">Sách tương tự</h4></div>
               </div>
               <div class="iq-card-body">
                  <div class="row">
                     <?php foreach ($related as $i => $row): ?>
                        <?php echo render_book_card($row, $i + 1, 'Xem sách'); ?>
                     <?php endforeach; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; ?>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
