<?php
// admin.php - Halaman Admin Dashboard
session_start();
require_once 'config.php';

// Simple authentication (in production, use proper authentication)
$admin_password = "admin123"; // Change this!

// Handle Login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error_message = "Password salah!";
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Admin - RoboTech Market</title>
        <link rel="stylesheet" href="css/admin.css">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                width: 100%;
                max-width: 400px;
                padding: 20px;
            }
            .login-box {
                background: white;
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            .login-box h2 {
                text-align: center;
                color: #333;
                margin-bottom: 30px;
                font-size: 28px;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group input {
                width: 100%;
                padding: 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 16px;
                transition: all 0.3s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            .btn-login {
                width: 100%;
                padding: 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 18px;
                font-weight: bold;
                cursor: pointer;
                transition: transform 0.2s;
            }
            .btn-login:hover {
                transform: translateY(-2px);
            }
            .error-message {
                background: #fee;
                color: #c33;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 15px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="login-box">
                <h2>🔐 Admin Login</h2>
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?= $error_message ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <input type="password" name="password" placeholder="Masukkan password admin" required autofocus>
                    </div>
                    <button type="submit" name="login" class="btn-login">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        // GET ORDERS
        if ($_POST['action'] === 'get_orders') {
            // Try order_date first, fallback to created_at
            try {
                $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC");
            } catch (PDOException $e) {
                $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
            }
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'orders' => $orders]);
            exit;
        }
        
        // GET ORDER ITEMS
        if ($_POST['action'] === 'get_order_items') {
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$_POST['order_id']]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'items' => $items]);
            exit;
        }
        
        // UPDATE ORDER STATUS
        if ($_POST['action'] === 'update_order_status') {
            // Check if status column exists
            $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
            if ($stmt->rowCount() == 0) {
                // Add status column if doesn't exist
                $pdo->exec("ALTER TABLE orders ADD COLUMN status VARCHAR(50) DEFAULT 'pending'");
            }
            
            // Update status
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['status'], $_POST['order_id']]);
            echo json_encode(['success' => true]);
            exit;
        }
        
        // GET PRODUCTS
        if ($_POST['action'] === 'get_products') {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
            exit;
        }
        
        // ADD PRODUCT
        if ($_POST['action'] === 'add_product') {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, category, stock, image) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['category'],
                $_POST['stock'],
                $_POST['image']
            ]);
            echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan']);
            exit;
        }
        
        // UPDATE PRODUCT
        if ($_POST['action'] === 'update_product') {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, category = ?, stock = ?, image = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['name'],
                $_POST['description'],
                $_POST['price'],
                $_POST['category'],
                $_POST['stock'],
                $_POST['image'],
                $_POST['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Produk berhasil diupdate']);
            exit;
        }
        
        // DELETE PRODUCT
        if ($_POST['action'] === 'delete_product') {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus']);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RoboTech Market</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="images/logo.png" alt="RoboTech Logo" class="logo-icon" onerror="this.style.display='none'">
                    <h1>Admin</h1>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn-secondary" target="_blank">🏪 Lihat Toko</a>
                    <a href="?logout" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">🚪 Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <div class="tabs-container">
        <div class="container">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab('orders')">📦 Pesanan</button>
                <button class="tab-btn" onclick="switchTab('products')">🛍️ Produk</button>
                <button class="tab-btn" onclick="switchTab('stats')">📊 Statistik</button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="admin-content">
        <div class="container">
            <!-- Orders Tab -->
            <div id="ordersTab" class="tab-content active">
                <div class="section-header">
                    <h2>Daftar Pesanan</h2>
                    <button class="btn-primary" onclick="loadOrders()">🔄 Refresh</button>
                </div>
                <div class="orders-list" id="ordersList">
                    <p class="loading">Memuat pesanan...</p>
                </div>
            </div>

            <!-- Products Tab -->
            <div id="productsTab" class="tab-content">
                <div class="section-header">
                    <h2>Manajemen Produk</h2>
                    <button class="btn-primary" onclick="showAddProductModal()">➕ Tambah Produk</button>
                </div>
                <div class="products-table" id="productsTable">
                    <p class="loading">Memuat produk...</p>
                </div>
            </div>

            <!-- Stats Tab -->
            <div id="statsTab" class="tab-content">
                <div class="section-header">
                    <h2>Statistik Penjualan</h2>
                    <button class="btn-primary" onclick="loadStats()">🔄 Refresh</button>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📦</div>
                        <div class="stat-info">
                            <h3 id="totalOrders">0</h3>
                            <p>Total Pesanan</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-info">
                            <h3 id="totalRevenue">Rp 0</h3>
                            <p>Total Pendapatan</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🛍️</div>
                        <div class="stat-info">
                            <h3 id="totalProducts">0</h3>
                            <p>Total Produk</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3 id="pendingOrders">0</h3>
                            <p>Pesanan Pending</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Order Detail Modal -->
    <div class="modal" id="orderDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detail Pesanan</h2>
                <button class="close-btn" onclick="closeOrderDetail()">✕</button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <p class="loading">Memuat detail...</p>
            </div>
        </div>
    </div>

    <!-- Product Form Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="productModalTitle">Tambah Produk</h2>
                <button class="close-btn" onclick="closeProductModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="productForm" onsubmit="saveProduct(event)">
                    <input type="hidden" id="productId">
                    
                    <div class="form-group">
                        <label>Nama Produk *</label>
                        <input type="text" id="productName" placeholder="Contoh: Arduino Uno R3" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Deskripsi *</label>
                        <textarea id="productDescription" rows="3" placeholder="Deskripsi produk..." required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Harga (Rp) *</label>
                            <input type="number" id="productPrice" placeholder="150000" required min="0">
                        </div>
                        <div class="form-group">
                            <label>Stok *</label>
                            <input type="number" id="productStock" placeholder="50" required min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select id="productCategory" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Mikrokontroler">Mikrokontroler</option>
                            <option value="Sensor">Sensor</option>
                            <option value="Modul">Modul</option>
                            <option value="Aktuator">Aktuator</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Gambar Produk *</label>
                        <input type="text" id="productImage" placeholder="arduino.png atau 🤖" required>
                        <small style="color: #999; display: block; margin-top: 4px;">
                            💡 Masukkan nama file dari folder images/ (contoh: arduino.png) atau emoji (🤖)
                        </small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeProductModal()">Batal</button>
                        <button type="submit" class="btn-primary">💾 Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>