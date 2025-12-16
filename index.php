<?php
// index.php - Halaman Utama
session_start();
require_once 'config.php';

// Inisialisasi cart di session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoboTech Market - E-Marketplace Komponen Robotika</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="admin.php" class="btn-admin">⚙️ Admin</a>
                <div class="logo">
                    <img src="images/logo.png" alt="RoboTech Logo" class="logo-icon">
                </div>
                <button class="cart-btn" onclick="toggleCart()">
                    🛒 Keranjang
                    <span class="cart-badge" id="cartBadge">0</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Search Bar -->
    <section class="search-section">
        <div class="container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Cari mikrokontroler, sensor, atau modul..." onkeyup="searchProducts()">
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="products-grid" id="productsGrid">
                <!-- Products akan dimuat via JavaScript -->
            </div>
        </div>
    </main>

    <!-- Cart Sidebar -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h2>Keranjang Belanja</h2>
            <button class="close-btn" onclick="toggleCart()">✕</button>
        </div>
        <div class="cart-content" id="cartContent">
            <p class="empty-cart">Keranjang masih kosong</p>
        </div>
        <div class="cart-footer" id="cartFooter" style="display: none;">
            <div class="cart-total">
                <span>Total</span>
                <span class="total-price" id="totalPrice">Rp 0</span>
            </div>
            <button class="checkout-btn" onclick="showCheckoutForm()">Checkout</button>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal" id="checkoutModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Checkout</h2>
                <button class="close-btn" onclick="closeCheckout()">✕</button>
            </div>
            <div class="modal-body">
                <div class="order-summary">
                    <h3>Ringkasan Pesanan</h3>
                    <div id="orderSummary"></div>
                </div>
                <form id="checkoutForm" onsubmit="submitOrder(event)">
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor HP *</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Pengiriman *</label>
                        <textarea name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeCheckout()">Kembali</button>
                        <button type="submit" class="btn-primary">Konfirmasi Pesanan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>