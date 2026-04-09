<?php
$cartItems = cart_items();
$cartCount = array_sum(array_map(static fn ($item) => (int) $item['quantity'], $cartItems));
$user = current_user();
$pageBreadcrumb = $pageBreadcrumb ?? ($pageTitle ?? 'Trang Chủ');
?>
<!-- TOP Nav Bar -->
<div class="iq-top-navbar">
   <div class="iq-navbar-custom">
      <nav class="navbar navbar-expand-lg navbar-light p-0">
         <div class="iq-menu-bt d-flex align-items-center">
            <div class="wrapper-menu" aria-label="Đóng mở menu">
               <span class="line-menu half start"></span>
               <span class="line-menu"></span>
               <span class="line-menu half end"></span>
            </div>
            <div class="iq-navbar-logo d-flex justify-content-between">
               <a href="index.php" class="header-logo">
                  <img src="images/logo.png" class="img-fluid rounded-normal" alt="">
                  <div class="logo-title"><span class="text-primary text-uppercase">NHASACHTV</span></div>
               </a>
            </div>
         </div>
         <div class="navbar-breadcrumb"><h5 class="mb-0"><?php echo h($pageBreadcrumb); ?></h5></div>
         <div class="iq-search-bar">
            <form action="search.php" method="get" class="searchbox">
               <input type="text" name="q" class="text search-input" 
          placeholder="Tìm kiếm sản phẩm..." 
          value="<?php echo h($_GET['q'] ?? ''); ?>"
          style="padding-left: 45px !important; background-color: white !important; border: 1px solid #eee;">
               <button type="submit" class="search-link" style="
       border: none; 
       background: none; 
       position: absolute; 
       left: 15px; 
       top: 50%; 
       transform: translateY(-50%); 
       color: #1abc9c; 
       padding: 0;
       z-index: 10;
   ">
      <i class="ri-search-line"></i>
   </button> 
            </form>
         </div>
         <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-label="Toggle navigation">
            <i class="ri-menu-3-line"></i>
         </button>
         <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto navbar-list">
               <li class="nav-item nav-icon search-content">
                  <a href="#" class="search-toggle iq-waves-effect text-gray rounded"><i class="ri-search-line"></i></a>
                  <form action="search.php" method="get" class="search-box p-0">
                     <input type="text" name="q" class="text search-input" placeholder="Tìm kiếm...">
                     <button class="search-link" type="submit"><i class="ri-search-line"></i></button>
                  </form>
               </li>
               <li class="nav-item nav-icon dropdown">
                  <a href="#" class="search-toggle iq-waves-effect text-gray rounded">
                     <i class="ri-shopping-cart-2-line"></i>
                     <span class="badge badge-danger count-cart rounded-circle"><?php echo (int) $cartCount; ?></span>
                  </a>
                  <div class="iq-sub-dropdown">
                     <div class="iq-card shadow-none m-0">
                        <div class="iq-card-body p-0 toggle-cart-info">
                           <div class="bg-primary p-3">
                              <h5 class="mb-0 text-white">Giỏ Hàng<small class="badge badge-light float-right pt-1"><?php echo (int) $cartCount; ?></small></h5>
                           </div>
                           <?php if ($cartItems): ?>
                              <?php foreach (array_slice($cartItems, 0, 3) as $item): ?>
                                 <?php $book = $item['book']; ?>
                                 <a href="book-page.php?id=<?php echo (int) $book['id']; ?>" class="iq-sub-card">
                                    <div class="media align-items-center">
                                       <div class=""><img class="rounded" src="<?php echo h(book_image_src($book, (int) $book['id'])); ?>" alt=""></div>
                                       <div class="media-body ml-3">
                                          <h6 class="mb-0"><?php echo h($book['bookname']); ?></h6>
                                          <p class="mb-0"><?php echo vn_money($item['subtotal']); ?>đ</p>
                                       </div>
                                    </div>
                                 </a>
                              <?php endforeach; ?>
                           <?php else: ?>
                              <div class="p-3 text-center text-muted">Giỏ hàng đang trống</div>
                           <?php endif; ?>
                           <div class="d-flex align-items-center text-center p-3">
                              <a class="btn btn-primary mr-2 iq-sign-btn" href="Checkout.php" role="button">Giỏ Hàng</a>
                              <a class="btn btn-primary iq-sign-btn" href="Checkout-preview.php" role="button">Thanh Toán</a>
                           </div>
                        </div>
                     </div>
                  </div>
               </li>
               <?php if ($user): ?>
                  <li class="line-height pt-3">
                     <a href="#" class="search-toggle iq-waves-effect d-flex align-items-center">
                        <img src="images/user/1.jpg" class="img-fluid rounded-circle mr-3" alt="user">
                        <div class="caption">
                           <h6 class="mb-1 line-height"><?php echo h(user_display_name($user)); ?></h6>
                           <p class="mb-0 text-primary">Tài Khoản</p>
                        </div>
                     </a>
                     <div class="iq-sub-dropdown iq-user-dropdown">
                        <div class="iq-card shadow-none m-0">
                           <div class="iq-card-body p-0">
                              <div class="bg-primary p-3"><h5 class="mb-0 text-white line-height">Xin Chào <?php echo h(user_display_name($user)); ?></h5></div>
                              <a href="profile.php" class="iq-sub-card iq-bg-primary-hover"><div class="media align-items-center"><div class="rounded iq-card-icon iq-bg-primary"><i class="ri-file-user-line"></i></div><div class="media-body ml-3"><h6 class="mb-0">Tài khoản của tôi</h6></div></div></a>
                              <a href="account-order.php" class="iq-sub-card iq-bg-primary-hover"><div class="media align-items-center"><div class="rounded iq-card-icon iq-bg-primary"><i class="ri-account-box-line"></i></div><div class="media-body ml-3"><h6 class="mb-0">Lịch sử mua hàng</h6></div></div></a>
                              <a href="favourite.php" class="iq-sub-card iq-bg-primary-hover"><div class="media align-items-center"><div class="rounded iq-card-icon iq-bg-primary"><i class="ri-heart-line"></i></div><div class="media-body ml-3"><h6 class="mb-0">Yêu Thích</h6></div></div></a>
                              <a href="sign-in.php?logout=1" class="iq-sub-card iq-bg-primary-hover"><div class="media align-items-center"><div class="rounded iq-card-icon iq-bg-primary"><i class="ri-logout-box-line"></i></div><div class="media-body ml-3"><h6 class="mb-0">Đăng xuất</h6></div></div></a>
                           </div>
                        </div>
                     </div>
                  </li>
               <?php else: ?>
                  <li class="line-height pt-3 d-flex align-items-center">
                     <a href="sign-in.php" class="btn btn-outline-primary btn-sm mr-2">Đăng nhập</a>
                     <a href="sign-up.php" class="btn btn-primary btn-sm">Đăng ký</a>
                  </li>
               <?php endif; ?>
            </ul>
         </div>
      </nav>
   </div>
</div>
