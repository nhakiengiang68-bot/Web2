<?php
require_once 'includes/app.php';
require_login();
$pageTitle = 'Yêu Thích';
$pageBreadcrumb = 'Yêu Thích';
$user = current_user();

if (isset($_GET['favorite'])) {
    $bookId = (int) ($_GET['favorite'] ?? 0);
    if ($bookId > 0) {
        $wasFavourite = favourite_exists((int) $user['id'], $bookId);
        if (favourite_toggle((int) $user['id'], $bookId)) {
            flash('success', $wasFavourite ? 'Đã xóa khỏi danh sách yêu thích.' : 'Đã thêm vào danh sách yêu thích.');
        }
    }
    redirect('favourite.php');
}

$favourites = favourite_books_for((int) $user['id']);

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
               <div class="iq-card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0">Yêu Thích</h4>
               </div>
               <div class="iq-card-body">
                  <div class="row">
                     <?php if ($favourites): ?>
                        <?php foreach ($favourites as $i => $book): ?>
                           <?php echo render_book_card($book, $i + 1, 'Xem sách'); ?>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <div class="col-12">
                           <div class="alert alert-info mb-0">Bạn chưa thêm sách nào vào danh sách yêu thích.</div>
                        </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
