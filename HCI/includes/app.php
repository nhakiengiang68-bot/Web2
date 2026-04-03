<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = mysqli_connect('localhost', 'root', '', 'nhasach');
    if (!$conn) {
        die('Kết nối CSDL thất bại.');
    }
    mysqli_set_charset($conn, 'utf8mb4');
}

function db(): mysqli
{
    global $conn;
    return $conn;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function vn_money($value): string
{
    return number_format((float) $value, 0, ',', '.');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function fetch_one(string $sql): ?array
{
    $result = mysqli_query(db(), $sql);
    if (!$result) {
        return null;
    }
    $row = mysqli_fetch_assoc($result);
    return $row ?: null;
}

function fetch_all(string $sql): array
{
    $result = mysqli_query(db(), $sql);
    if (!$result) {
        return [];
    }
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function fetch_count(string $sql): int
{
    $row = fetch_one($sql);
    if (!$row) {
        return 0;
    }
    $values = array_values($row);
    return isset($values[0]) ? (int) $values[0] : 0;
}

function esc($value): string
{
    return mysqli_real_escape_string(db(), (string) $value);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']) && is_array($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Vui lòng đăng nhập trước.');
        redirect('sign-in.php');
    }
}

function user_display_name(?array $user = null): string
{
    $user = $user ?: current_user();
    if (!$user) {
        return 'Khách';
    }
    return trim((string) ($user['fullname'] ?? '')) !== '' ? (string) $user['fullname'] : (string) ($user['username'] ?? 'Khách');
}

function username_valid(string $username): bool
{
    return (bool) preg_match('/^[A-Za-z0-9_]+$/', $username);
}

function gmail_valid(string $email): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email);
}

function password_matches(string $plain, string $stored): bool
{
    return password_verify($plain, $stored) || hash_equals($stored, $plain);
}

function categories_all(): array
{
    return fetch_all('SELECT id, name, info FROM categories ORDER BY name');
}

function authors_all(): array
{
    return fetch_all('SELECT id, fullname, info FROM authors ORDER BY fullname');
}

function book_categories_text(int $bookId): string
{
    $rows = fetch_all('SELECT c.name FROM book_category bc JOIN categories c ON c.id = bc.category_id WHERE bc.book_id = ' . (int) $bookId . ' ORDER BY c.name');
    return implode(', ', array_map(static fn ($row) => $row['name'], $rows));
}

function book_category_ids(int $bookId): array
{
    $rows = fetch_all('SELECT category_id FROM book_category WHERE book_id = ' . (int) $bookId . ' ORDER BY category_id');
    return array_map(static fn ($row) => (int) $row['category_id'], $rows);
}

function books_visible_condition(string $alias = 'b'): string
{
    return 'COALESCE(' . $alias . '.status, "visible") <> "hidden"';
}

function favourite_exists(int $userId, int $bookId): bool
{
    $row = fetch_one('SELECT id_book FROM favourite WHERE id_user = ' . (int) $userId . ' AND id_book = ' . (int) $bookId . ' LIMIT 1');
    return (bool) $row;
}

function favourite_toggle(int $userId, int $bookId): bool
{
    if ($userId <= 0 || $bookId <= 0) {
        return false;
    }

    if (favourite_exists($userId, $bookId)) {
        return (bool) mysqli_query(db(), 'DELETE FROM favourite WHERE id_user = ' . (int) $userId . ' AND id_book = ' . (int) $bookId);
    }

    return (bool) mysqli_query(db(), 'INSERT INTO favourite (id_user, id_book) VALUES (' . (int) $userId . ', ' . (int) $bookId . ')');
}

function favourite_books_for(int $userId): array
{
    return fetch_all('SELECT b.*, a.fullname AS author_name FROM favourite f INNER JOIN books b ON b.id = f.id_book LEFT JOIN authors a ON a.id = b.author_id WHERE f.id_user = ' . (int) $userId . ' AND ' . books_visible_condition('b') . ' ORDER BY b.updated_at DESC, b.id DESC');
}

function book_detail(int $bookId): ?array
{
    $book = fetch_one('
        SELECT b.*, a.fullname AS author_name
        FROM books b
        LEFT JOIN authors a ON a.id = b.author_id
        WHERE b.id = ' . (int) $bookId . '
        LIMIT 1
    ');
    if ($book) {
        $book['categories_text'] = book_categories_text((int) $book['id']);
    }
    return $book;
}

function book_image_src(array $book, int $fallbackIndex = 1): string
{
    $image = trim((string) ($book['image'] ?? ''));
    if ($image !== '') {
        $image = str_replace('\\', '/', $image);
        $candidates = [];
        $candidates[] = ltrim($image, '/');
        $candidates[] = 'images/books/' . ltrim($image, '/');
        $basename = basename($image);
        if ($basename !== '' && $basename !== $image) {
            $candidates[] = 'images/books/' . $basename;
            $candidates[] = 'images/' . $basename;
        }
        foreach (array_unique($candidates) as $candidate) {
            $local = __DIR__ . '/../' . $candidate;
            if (is_file($local)) {
                return str_replace('\\', '/', $candidate);
            }
        }
        // Nếu CSDL đã lưu đường dẫn hợp lệ nhưng file chưa có ở local, vẫn trả đúng đường dẫn gốc.
        return ltrim($image, '/');
    }

    return 'images/books/book1.jpg';
}

function book_sell_price(array $book): float
{
    if (isset($book['sell_price']) && (float) $book['sell_price'] > 0) {
        return (float) $book['sell_price'];
    }
    $cost = (float) ($book['cost_price'] ?? 0);
    $profit = (float) ($book['profit_percent'] ?? 0);
    return round($cost * (1 + $profit / 100), 2);
}

function render_stars(int $count = 5): string
{
    $html = '';
    for ($i = 0; $i < $count; $i++) {
        $html .= '<i class="fa fa-star"></i>';
    }
    return $html;
}

function render_book_card(array $book, int $index = 1, string $buttonLabel = 'Mua Ngay'): string
{
    $bookId = (int) $book['id'];
    $image = h(book_image_src($book, $index));
    $title = h($book['bookname'] ?? '');
    $author = h($book['author_name'] ?? '');
    $price = vn_money(book_sell_price($book));
    $link = 'book-page.php?id=' . $bookId;
    $cartLink = 'Checkout.php?add=' . $bookId;
    $user = current_user();
    $isFavourite = $user ? favourite_exists((int) $user['id'], $bookId) : false;
    $favouriteLink = $user ? 'book-page.php?id=' . $bookId . '&favorite=1' : 'sign-in.php';
    $favouriteIcon = $isFavourite ? 'ri-heart-fill text-danger' : 'ri-heart-line text-danger';

    return '
      <div class="col-sm-6 col-md-4 col-lg-3">
         <div class="iq-card iq-card-block iq-card-stretch iq-card-height browse-bookcontent">
            <div class="iq-card-body p-0">
               <div class="d-flex align-items-center">
                  <div class="col-6 p-0 position-relative image-overlap-shadow">
                     <a href="' . $link . '"><img class="img-fluid rounded w-100" src="' . $image . '" alt=""></a>
                     <div class="view-book">
                        <a href="' . $link . '" class="btn btn-sm btn-white">' . h($buttonLabel) . '</a>
                     </div>
                  </div>
                  <div class="col-6">
                     <div class="mb-2">
                        <h6 class="mb-1">' . $title . '</h6>
                        <p class="font-size-13 line-height mb-1">' . $author . '</p>
                        <div class="d-block line-height"><span class="font-size-11 text-warning">' . render_stars() . '</span></div>
                     </div>
                     <div class="price d-flex align-items-center"><h6><b>' . $price . ' đ</b></h6></div>
                     <div class="iq-product-action">
                        <a href="' . $cartLink . '"><i class="ri-shopping-cart-2-fill text-primary"></i></a>
                        <a href="' . $favouriteLink . '" class="ml-2"><i class="' . $favouriteIcon . '"></i></a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>';
}

function user_addresses(int $userId): array
{
    return fetch_all('SELECT * FROM address WHERE user_id = ' . (int) $userId . ' ORDER BY is_default DESC, id DESC');
}

function default_address(int $userId): ?array
{
    return fetch_one('SELECT * FROM address WHERE user_id = ' . (int) $userId . ' ORDER BY is_default DESC, id DESC LIMIT 1');
}

function cart_get(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) {
        $cart = [];
    }
    $clean = [];
    foreach ($cart as $bookId => $qty) {
        $bookId = (int) $bookId;
        $qty = max(1, (int) $qty);
        if ($bookId > 0) {
            $clean[$bookId] = ($clean[$bookId] ?? 0) + $qty;
        }
    }
    $_SESSION['cart'] = $clean;
    return $clean;
}

function cart_add(int $bookId, int $qty = 1): void
{
    $cart = cart_get();
    $cart[$bookId] = ($cart[$bookId] ?? 0) + max(1, $qty);
    $_SESSION['cart'] = $cart;
}

function cart_set_qty(int $bookId, int $qty): void
{
    $cart = cart_get();
    if ($qty <= 0) {
        unset($cart[$bookId]);
    } else {
        $cart[$bookId] = $qty;
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(int $bookId): void
{
    $cart = cart_get();
    unset($cart[$bookId]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_items(): array
{
    $cart = cart_get();
    if (!$cart) {
        return [];
    }
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $books = fetch_all('SELECT b.*, a.fullname AS author_name FROM books b LEFT JOIN authors a ON a.id = b.author_id WHERE b.id IN (' . $ids . ')');
    $map = [];
    foreach ($books as $book) {
        $map[(int) $book['id']] = $book;
    }
    $items = [];
    foreach ($cart as $bookId => $qty) {
        if (!isset($map[$bookId])) {
            continue;
        }
        $book = $map[$bookId];
        $price = book_sell_price($book);
        $items[] = [
            'book' => $book,
            'quantity' => $qty,
            'price' => $price,
            'subtotal' => $price * $qty,
        ];
    }
    return $items;
}

function cart_total(): float
{
    $total = 0.0;
    foreach (cart_items() as $item) {
        $total += (float) $item['subtotal'];
    }
    return $total;
}

function ensure_order_code(): string
{
    return 'DH' . date('YmdHis');
}

function create_order_from_cart(int $userId, int $addressId, string $paymentMethod, string $note = ''): ?int
{
    $items = cart_items();
    if (!$items) {
        flash('warning', 'Giỏ hàng đang trống.');
        return null;
    }

    $address = fetch_one('SELECT * FROM address WHERE id = ' . (int) $addressId . ' AND user_id = ' . (int) $userId . ' LIMIT 1');
    if (!$address) {
        $address = default_address($userId);
    }
    if (!$address) {
        flash('warning', 'Bạn cần thêm địa chỉ trước khi đặt hàng.');
        return null;
    }

    $total = cart_total();
    $shippingAddress = $address['address_detail'] ?? '';
    $ward = $address['ward'] ?? '';
    $district = $address['district'] ?? '';
    $province = $address['province'] ?? '';
    $receiverName = $address['receiver_name'] ?? user_display_name();
    $receiverPhone = $address['phone'] ?? '';
    $paymentMethod = trim($paymentMethod) !== '' ? $paymentMethod : 'Thanh toán khi nhận hàng';

    mysqli_begin_transaction(db());
    try {
        $stmt = mysqli_prepare(db(), 'INSERT INTO orders (user_id, `date`, price, status, receiver_name, receiver_phone, shipping_address, ward, district, province, payment_method) VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $status = 'pending';
        mysqli_stmt_bind_param($stmt, 'idssssssss', $userId, $total, $status, $receiverName, $receiverPhone, $shippingAddress, $ward, $district, $province, $paymentMethod);
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id(db());
        mysqli_stmt_close($stmt);

        $stmtItem = mysqli_prepare(db(), 'INSERT INTO order_items (order_id, book_id, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)');
        foreach ($items as $item) {
            $bookId = (int) $item['book']['id'];
            $price = (float) $item['price'];
            $qty = (int) $item['quantity'];
            $subtotal = (float) $item['subtotal'];
            mysqli_stmt_bind_param($stmtItem, 'iidid', $orderId, $bookId, $price, $qty, $subtotal);
            mysqli_stmt_execute($stmtItem);
        }
        mysqli_stmt_close($stmtItem);

        mysqli_commit(db());
        cart_clear();
        $_SESSION['last_order_id'] = $orderId;
        return (int) $orderId;
    } catch (Throwable $e) {
        mysqli_rollback(db());
        flash('danger', 'Không thể tạo đơn hàng: ' . $e->getMessage());
        return null;
    }
}

function last_order_id(): ?int
{
    return isset($_SESSION['last_order_id']) ? (int) $_SESSION['last_order_id'] : null;
}

function order_items_for(int $orderId): array
{
    return fetch_all('SELECT oi.*, b.bookname, b.image, b.book_code FROM order_items oi LEFT JOIN books b ON b.id = oi.book_id WHERE oi.order_id = ' . (int) $orderId . ' ORDER BY oi.id');
}

function order_summary(int $orderId): ?array
{
    return fetch_one('SELECT o.*, u.fullname, u.username, u.email, u.phone AS user_phone FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = ' . (int) $orderId . ' LIMIT 1');
}

function render_flash(): void
{
    $flash = consume_flash();
    if (!$flash) {
        return;
    }
    echo '<div class="container-fluid mt-3"><div class="alert alert-' . h($flash['type']) . ' mb-0">' . h($flash['message']) . '</div></div>';
}
?>
