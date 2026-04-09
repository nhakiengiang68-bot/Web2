<?php
require_once 'includes/app.php';

$pageTitle = 'Yêu Thích';
$pageBreadcrumb = 'Yêu Thích';

$user = current_user();

// =========================
// THÊM / XÓA YÊU THÍCH
// =========================
if (isset($_GET['favorite'])) {
    $bookId = (int) ($_GET['favorite'] ?? 0);

    if ($bookId > 0) {
        // Chưa đăng nhập thì chỉ báo, không chuyển trang đăng nhập
        if (!$user) {
            flash('error', 'Bạn cần đăng nhập để thêm sách vào danh sách yêu thích.');
        } else {
            $wasFavourite = favourite_exists((int) $user['id'], $bookId);
            if (favourite_toggle((int) $user['id'], $bookId)) {
                flash('success', $wasFavourite ? 'Đã xóa khỏi danh sách yêu thích.' : 'Đã thêm vào danh sách yêu thích.');
            }
        }
    }

    redirect('favourite.php');
}

// =========================
// DỮ LIỆU YÊU THÍCH
// =========================
$favourites = $user ? favourite_books_for((int) $user['id']) : [];

include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/topnav.php';
?>

<style>
   .fav-page {
      padding: 24px 0 44px;
   }

   .fav-hero {
      background: linear-gradient(135deg, #7c3aed 0%, #14b8a6 45%, #22c55e 100%);
      border-radius: 24px;
      padding: 26px 28px;
      color: #fff;
      box-shadow: 0 18px 40px rgba(20, 184, 166, 0.16);
      position: relative;
      overflow: hidden;
      margin-bottom: 22px;
   }

   .fav-hero::after {
      content: "";
      position: absolute;
      inset: auto -40px -50px auto;
      width: 180px;
      height: 180px;
      border-radius: 50%;
      background: rgba(255,255,255,0.10);
   }

   .fav-hero h2 {
      margin: 0 0 8px;
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -.02em;
   }

   .fav-hero p {
      margin: 0;
      color: rgba(255,255,255,0.92);
      font-size: 15px;
      line-height: 1.7;
   }

   .fav-shell {
      background: #fff;
      border: 1px solid rgba(15, 23, 42, 0.06);
      border-radius: 24px;
      box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08);
      overflow: hidden;
   }

   .fav-header {
      padding: 18px 22px;
      border-bottom: 1px solid #edf2f7;
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
   }

   .fav-title {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: #111827;
   }

   .fav-subtitle {
      margin-top: 4px;
      color: #64748b;
      font-size: 14px;
   }

   .fav-body {
      padding: 22px;
      background: #f8fafc;
   }

   .fav-alert {
      border-radius: 16px;
      padding: 14px 16px;
      font-weight: 600;
   }

   .fav-empty {
      background: #fff;
      border: 1px dashed #cbd5e1;
      border-radius: 22px;
      padding: 42px 22px;
      text-align: center;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
   }

   .fav-empty-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      margin: 0 auto 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #fdf2f8, #ecfeff);
      color: #db2777;
      font-size: 30px;
      box-shadow: 0 10px 20px rgba(219, 39, 119, 0.10);
   }

   .fav-empty h5 {
      margin: 0 0 8px;
      font-weight: 800;
      color: #111827;
      font-size: 20px;
   }

   .fav-empty p {
      margin: 0;
      color: #64748b;
      font-size: 14px;
      line-height: 1.65;
   }

   .fav-login-note {
      background: #ecfeff;
      border: 1px solid #c7f9f3;
      color: #0f766e;
      border-radius: 18px;
      padding: 14px 16px;
      margin-bottom: 18px;
      font-size: 14px;
      line-height: 1.6;
   }

   .fav-grid .col-lg-3,
   .fav-grid .col-md-4,
   .fav-grid .col-sm-6 {
      margin-bottom: 18px;
   }

   .fav-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,0.16);
      color: #fff;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 14px;
   }

   .fav-top-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 12px;
   }

   .fav-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      background: #fff;
      border: 1px solid #e5e7eb;
      color: #334155;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
   }

   @media (max-width: 768px) {
      .fav-page {
         padding: 18px 0 34px;
      }

      .fav-hero {
         padding: 20px;
         border-radius: 20px;
      }

      .fav-hero h2 {
         font-size: 22px;
      }

      .fav-body,
      .fav-header {
         padding-left: 16px;
         padding-right: 16px;
      }
   }
</style>

<div id="content-page" class="content-page">
   <?php render_flash(); ?>

   <div class="container-fluid fav-page">
      <div class="fav-hero">
         <div class="fav-badge">❤️ Danh sách yêu thích</div>
         <h2>Yêu Thích</h2>
         <p>Lưu những cuốn sách bạn thích nhất để xem lại nhanh hơn và mua sắm tiện lợi hơn.</p>

         <div class="fav-top-actions">
            <div class="fav-chip">✨ Gợi ý sách yêu thích của bạn</div>
            <div class="fav-chip">📚 Quản lý danh sách dễ dàng</div>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-12">
            <div class="fav-shell">
               <div class="fav-header d-flex justify-content-between align-items-center flex-wrap">
                  <div>
                     <h4 class="fav-title mb-0">Danh sách yêu thích</h4>
                     <div class="fav-subtitle">Những cuốn sách bạn đã lưu lại để xem sau.</div>
                  </div>
               </div>

               <div class="fav-body">
                  <?php if (!$user): ?>
                     <div class="fav-login-note">
                        Bạn cần đăng nhập để xem và thêm sách vào danh sách yêu thích.
                     </div>
                  <?php endif; ?>

                  <div class="row fav-grid">
                     <?php if ($favourites): ?>
                        <?php foreach ($favourites as $i => $book): ?>
                           <?php echo render_book_card($book, $i + 1, 'Xem sách'); ?>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <div class="col-12">
                           <div class="fav-empty">
                              <div class="fav-empty-icon">♡</div>
                              <h5>Chưa có sách yêu thích</h5>
                              <p>Bạn chưa thêm cuốn sách nào vào danh sách yêu thích. Hãy khám phá và lưu lại những cuốn bạn thích nhé.</p>
                           </div>
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