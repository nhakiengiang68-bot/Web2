<?php
require_once 'includes/app.php';
$pageTitle = 'Tìm kiếm sản phẩm';
$pageBreadcrumb = 'Tìm kiếm sản phẩm';

$q = trim((string) ($_GET['q'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$minPrice = trim((string) ($_GET['min_price'] ?? ''));
$maxPrice = trim((string) ($_GET['max_price'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'latest'));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = [books_visible_condition('b')];
if ($q !== '') {
    $qEsc = esc($q);
    $where[] = "(b.bookname LIKE '%{$qEsc}%' OR b.book_code LIKE '%{$qEsc}%' OR a.fullname LIKE '%{$qEsc}%')";
}
if ($categoryId > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM book_category bc WHERE bc.book_id = b.id AND bc.category_id = ' . $categoryId . ')';
}
if ($minPrice !== '' && is_numeric($minPrice)) {
    $where[] = 'COALESCE(b.sell_price, (b.cost_price * (1 + b.profit_percent / 100))) >= ' . (float) $minPrice;
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $where[] = 'COALESCE(b.sell_price, (b.cost_price * (1 + b.profit_percent / 100))) <= ' . (float) $maxPrice;
}
$whereSql = implode(' AND ', $where);

$total = fetch_count('SELECT COUNT(*) AS total FROM books b LEFT JOIN authors a ON a.id = b.author_id WHERE ' . $whereSql);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

$orderSql = 'b.updated_at DESC, b.id DESC';
if ($sort === 'title_asc') {
    $orderSql = 'b.bookname ASC, b.id ASC';
} elseif ($sort === 'title_desc') {
    $orderSql = 'b.bookname DESC, b.id DESC';
} elseif ($sort === 'price_asc') {
    $orderSql = 'COALESCE(b.sell_price, (b.cost_price * (1 + b.profit_percent / 100))) ASC, b.id ASC';
} elseif ($sort === 'price_desc') {
    $orderSql = 'COALESCE(b.sell_price, (b.cost_price * (1 + b.profit_percent / 100))) DESC, b.id DESC';
}

$rows = fetch_all('
    SELECT b.*, a.fullname AS author_name
    FROM books b
    LEFT JOIN authors a ON a.id = b.author_id
    WHERE ' . $whereSql . '
    ORDER BY ' . $orderSql . '
    LIMIT ' . $offset . ', ' . $perPage . '
');
$categories = categories_all();

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>
<div id="content-page" class="content-page">
   <?php render_flash(); ?>
   <div class="container-fluid">
      <div class="row">
         <div class="col-lg-12">
            <div class="iq-card iq-card-block iq-card-stretch iq-card-height mb-4">
               <div class="iq-card-header d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-0">Tìm kiếm sản phẩm</h4>
               </div>
               <div class="iq-card-body">
                  <form method="get">
                     <div class="form-group mb-3">
                        <label for="search-name" class="font-weight-bold">Tìm theo tên:</label>
                        <input type="text" id="search-name" name="q" class="form-control" placeholder="Nhập tên sản phẩm..." value="<?php echo h($q); ?>">
                     </div>
                     <div class="border-top pt-3">
                        <h6 class="font-weight-bold mb-3">Tìm nâng cao</h6>
                        <div class="form-row">
                           <div class="form-group col-md-3">
                              <label for="search-category">Phân loại:</label>
                              <select id="search-category" name="category_id" class="form-control">
                                 <option value="0">Tất cả</option>
                                 <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int) $category['id']; ?>" <?php echo $categoryId === (int) $category['id'] ? 'selected' : ''; ?>><?php echo h($category['name']); ?></option>
                                 <?php endforeach; ?>
                              </select>
                           </div>
                           <div class="form-group col-md-3">
                              <label for="search-sort">Sắp xếp:</label>
                              <select id="search-sort" name="sort" class="form-control">
                                 <option value="latest" <?php echo $sort === 'latest' ? 'selected' : ''; ?>>Mới nhất</option>
                                 <option value="title_asc" <?php echo $sort === 'title_asc' ? 'selected' : ''; ?>>Tên A → Z</option>
                                 <option value="title_desc" <?php echo $sort === 'title_desc' ? 'selected' : ''; ?>>Tên Z → A</option>
                                 <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                 <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
                              </select>
                           </div>
                           <div class="form-group col-md-3">
                              <label for="price-min">Giá từ:</label>
                              <input type="number" id="price-min" name="min_price" class="form-control" placeholder="VD: 50000" value="<?php echo h($minPrice); ?>">
                           </div>
                           <div class="form-group col-md-3">
                              <label for="price-max">Đến:</label>
                              <input type="number" id="price-max" name="max_price" class="form-control" placeholder="VD: 200000" value="<?php echo h($maxPrice); ?>">
                           </div>
                        </div>
                     </div>
                     <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                        <a href="search.php" class="btn btn-outline-secondary">Xóa</a>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>

      <div class="row">
         <?php if ($rows): ?>
            <?php foreach ($rows as $i => $book): ?>
               <?php echo render_book_card($book, $i + 1, 'Xem sách'); ?>
            <?php endforeach; ?>
         <?php else: ?>
            <div class="col-12"><div class="alert alert-info">Không tìm thấy sản phẩm phù hợp.</div></div>
         <?php endif; ?>
      </div>

      <?php if ($totalPages > 1): ?>
         <div class="row mt-3">
            <div class="col-12">
               <nav>
                  <ul class="pagination justify-content-center">
                     <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php $query = $_GET; $query['page'] = $i; ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                           <a class="page-link" href="?<?php echo h(http_build_query($query)); ?>"><?php echo $i; ?></a>
                        </li>
                     <?php endfor; ?>
                  </ul>
               </nav>
            </div>
         </div>
      <?php endif; ?>
   </div>
</div>
<?php include 'includes/footer.php'; ?>
