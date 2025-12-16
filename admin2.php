<?php
require 'config.php';

// -----------------------------
// UPDATE STATUS PESANAN
// -----------------------------
if (isset($_GET['update_status']) && isset($_GET['status'])) {
    $order_id = $_GET['update_status'];
    $new_status = $_GET['status'];

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);

    header("Location: admin2.php");
    exit;
}

// -----------------------------
// AMBIL DATA PESANAN
// -----------------------------
$orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Pesanan Masuk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="mb-4 fw-bold">📦 Pesanan Masuk</h2>

    <?php foreach ($orders as $order): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between">
                    <h5 class="fw-bold">Order #<?= $order['id'] ?></h5>
                    <span class="badge bg-primary"><?= ucfirst($order['status']) ?></span>
                </div>

                <p class="mb-1"><strong>Nama:</strong> <?= $order['customer_name'] ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= $order['email'] ?></p>
                <p class="mb-1"><strong>No HP:</strong> <?= $order['phone'] ?></p>
                <p class="mb-1"><strong>Alamat:</strong> <?= $order['address'] ?></p>
                <p><strong>Total Harga:</strong> Rp <?= number_format($order['total_price'], 0, ',', '.') ?></p>

                <hr>

                <h6 class="fw-bold">🛒 Detail Item:</h6>
                <ul class="list-group mb-3">
                    <?php
                    $items_query = $pdo->prepare("
                        SELECT order_items.*, products.name 
                        FROM order_items 
                        JOIN products ON order_items.product_id = products.id
                        WHERE order_items.order_id = ?
                    ");
                    $items_query->execute([$order['id']]);
                    $items = $items_query->fetchAll();
                    
                    foreach ($items as $item):
                    ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= $item['name'] ?> (x<?= $item['quantity'] ?>)</span>
                            <strong>Rp <?= number_format($item['price'], 0, ',', '.') ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="d-flex gap-2">
                    <a href="admin2.php?update_status=<?= $order['id'] ?>&status=pending" 
                       class="btn btn-secondary btn-sm">Pending</a>

                    <a href="admin2.php?update_status=<?= $order['id'] ?>&status=proses" 
                       class="btn btn-warning btn-sm">Proses</a>

                    <a href="admin2.php?update_status=<?= $order['id'] ?>&status=selesai" 
                       class="btn btn-success btn-sm">Selesai</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>
