<?php
require_once 'includes/app.php';
$pageTitle = 'Trang Chủ';
$pageBreadcrumb = 'Trang Chủ';

// =========================
// LẤY CATEGORY + PAGE
// =========================
$categoryId = (int)($_GET['category_id'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

// =========================
// ĐIỀU KIỆN QUERY
// =========================
$where = books_visible_condition('b');

if ($categoryId > 0) {
   $where .= ' AND b.category_id = ' . $categoryId;
}

// =========================
// ĐẾM TỔNG SẢN PHẨM
// =========================
$totalRow = fetch_one('SELECT COUNT(*) AS total FROM books b WHERE ' . $where);
$totalBooks = (int)$totalRow['total'];
$totalPages = ceil($totalBooks / $limit);

// Fix page vượt quá
if ($page > $totalPages && $totalPages > 0) {
   $page = $totalPages;
   $offset = ($page - 1) * $limit;
}

// =========================
// QUERY SẢN PHẨM
// =========================
$featuredBooks = fetch_all('
   SELECT b.*, a.fullname AS author_name 
   FROM books b 
   LEFT JOIN authors a ON a.id = b.author_id 
   WHERE ' . $where . ' 
   ORDER BY b.updated_at DESC, b.id DESC 
   LIMIT ' . $limit . ' OFFSET ' . $offset
);

// =========================
// CATEGORY LIST
// =========================
$categories = categories_all();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">

      <!-- =========================
           GỢI Ý CHO BẠN
      ========================= -->
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
               <div class="iq-card-header d-flex justify-content-between align-items-center position-relative">
                  <div class="iq-header-title">
                     <h4 class="card-title mb-0">Gợi ý cho bạn</h4>
                  </div>
                  <div class="iq-card-header-toolbar d-flex align-items-center">
                     <a href="search.php" class="btn btn-sm btn-primary view-more">Xem Thêm</a>
                  </div>
               </div>

               <div class="iq-card-body">
                  <div class="row">
                     <?php if ($featuredBooks): ?>
                        <?php foreach ($featuredBooks as $i => $book): ?>
                           <?php echo render_book_card($book, $i + 1); ?>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <div class="col-12 text-center text-muted py-5">
                           Không có sản phẩm phù hợp.
                        </div>
                     <?php endif; ?>
                  </div>

                  <!-- =========================
                       PAGINATION
                  ========================= -->
                  <?php if ($totalPages > 1): ?>
                     <div class="row">
                        <div class="col-12 mt-4">
                           <nav>
                              <ul class="pagination justify-content-center">

                                 <!-- Prev -->
                                 <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?category_id=<?php echo $categoryId; ?>&page=<?php echo $page - 1; ?>">«</a>
                                 </li>

                                 <!-- Page numbers -->
                                 <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                       <a class="page-link" href="?category_id=<?php echo $categoryId; ?>&page=<?php echo $i; ?>">
                                          <?php echo $i; ?>
                                       </a>
                                    </li>
                                 <?php endfor; ?>

                                 <!-- Next -->
                                 <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?category_id=<?php echo $categoryId; ?>&page=<?php echo $page + 1; ?>">»</a>
                                 </li>

                              </ul>
                           </nav>
                        </div>
                     </div>
                  <?php endif; ?>

               </div>
            </div>
         </div>
      </div>

      <!-- =========================
           BEST SELLER
      ========================= -->
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height">
               <div class="iq-card-header">
                  <h4 class="card-title mb-0">Best Seller</h4>
               </div>

               <div class="iq-card-body">
                  <div class="row">
                     <?php
                     $bestSellerBooks = fetch_all('
                        SELECT b.*, a.fullname AS author_name 
                        FROM books b 
                        LEFT JOIN authors a ON a.id = b.author_id 
                        WHERE ' . books_visible_condition('b') . ' 
                        ORDER BY (
                           SELECT COALESCE(SUM(oi.quantity), 0) 
                           FROM order_items oi 
                           WHERE oi.book_id = b.id
                        ) DESC 
                        LIMIT 8
                     ');

                     if ($bestSellerBooks):
                        foreach ($bestSellerBooks as $i => $book):
                           echo render_book_card($book, $i + 1, 'Xem sách');
                        endforeach;
                     else: ?>
                        <div class="col-12 text-center text-muted py-5">
                           Chưa có dữ liệu bán chạy.
                        </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- =========================
           CATEGORY + ACCOUNT
      ========================= -->
      <div class="row">
         <div class="col-lg-8">
            <div class="iq-card">
               <div class="iq-card-header">
                  <h4 class="card-title mb-0">Danh mục nổi bật</h4>
               </div>

               <div class="iq-card-body">
                  <div class="row">
                     <?php foreach ($categories as $category): ?>
                        <div class="col-md-6 mb-3">
                           <a href="?category_id=<?php echo (int)$category['id']; ?>" 
                              class="iq-card d-block mb-0 <?php echo ($category['id'] == $categoryId) ? 'border-primary' : ''; ?>">
                              
                              <div class="iq-card-body d-flex justify-content-between align-items-center">
                                 <div>
                                    <h6 class="mb-1"><?php echo h($category['name']); ?></h6>
                                    <small class="text-muted"><?php echo h($category['info']); ?></small>
                                 </div>
                                 <i class="ri-arrow-right-line"></i>
                              </div>
                           </a>
                        </div>
                     <?php endforeach; ?>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-lg-4">
            <div class="iq-card">
               <div class="iq-card-header">
                  <h4 class="card-title mb-0">Tài khoản</h4>
               </div>

               <div class="iq-card-body">
                  <?php if (is_logged_in()): ?>
                     <p class="mb-1">
                        Xin chào, <strong><?php echo h(user_display_name()); ?></strong>
                     </p>
                     <p class="mb-3 text-muted">
                        Email: <?php echo h(current_user()['email'] ?? ''); ?>
                     </p>

                     <a href="profile.php" class="btn btn-primary btn-block mb-2">
                        Xem tài khoản
                     </a>
                     <a href="account-order.php" class="btn btn-outline-primary btn-block">
                        Lịch sử mua hàng
                     </a>
                  <?php else: ?>
                     <p class="mb-3">
                        Đăng nhập để đồng bộ tài khoản và đơn hàng.
                     </p>

                     <a href="sign-in.php" class="btn btn-primary btn-block mb-2">
                        Đăng nhập
                     </a>
                     <a href="sign-up.php" class="btn btn-outline-primary btn-block">
                        Đăng ký
                     </a>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>

   </div>
</div>

<?php include 'includes/footer.php'; ?>