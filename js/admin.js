// admin.js - Admin JavaScript

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
});

// Tab Switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + 'Tab').classList.add('active');
    event.target.classList.add('active');
    
    // Load data if needed
    if (tabName === 'orders') {
        loadOrders();
    } else if (tabName === 'products') {
        loadProducts();
    } else if (tabName === 'stats') {
        loadStats();
    }
}

// Load Orders
async function loadOrders() {
    const ordersList = document.getElementById('ordersList');
    ordersList.innerHTML = '<p class="loading">Memuat pesanan...</p>';
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_orders');
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.orders.length > 0) {
            ordersList.innerHTML = data.orders.map(order => {
                // Sesuaikan dengan struktur database admin2.php
                const status = order.status || 'pending';
                const orderDate = order.order_date || order.created_at;
                
                return `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #${order.id}</h3>
                            <p><strong>👤 Nama:</strong> ${order.customer_name}</p>
                            <p><strong>📧 Email:</strong> ${order.email}</p>
                            <p><strong>📱 HP:</strong> ${order.phone}</p>
                            <p><strong>📅 Tanggal:</strong> ${formatDate(orderDate)}</p>
                        </div>
                        <span class="order-status status-${status}">${getStatusText(status)}</span>
                    </div>
                    <div class="order-details">
                        <div class="detail-group">
                            <h4>📍 Alamat Pengiriman</h4>
                            <p>${order.address}</p>
                        </div>
                        <div class="detail-group">
                            <h4>💰 Total Pesanan</h4>
                            <p class="order-total">${formatRupiah(order.total_price)}</p>
                        </div>
                    </div>
                    <div class="order-actions">
                        <button class="btn-small btn-view" onclick="showOrderDetail(${order.id})">👁️ Lihat Detail</button>
                        <button class="btn-small btn-pending" onclick="quickUpdateStatus(${order.id}, 'pending')">⏳ Pending</button>
                        <button class="btn-small btn-process" onclick="quickUpdateStatus(${order.id}, 'proses')">⚙️ Proses</button>
                        <button class="btn-small btn-done" onclick="quickUpdateStatus(${order.id}, 'selesai')">✅ Selesai</button>
                    </div>
                </div>
            `;
            }).join('');
        } else {
            ordersList.innerHTML = '<p class="loading">Belum ada pesanan</p>';
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        ordersList.innerHTML = '<p class="loading">Gagal memuat pesanan</p>';
    }
}

// Show Order Detail
async function showOrderDetail(orderId) {
    const modal = document.getElementById('orderDetailModal');
    const content = document.getElementById('orderDetailContent');
    
    try {
        // Get order items
        const formData = new FormData();
        formData.append('action', 'get_order_items');
        formData.append('order_id', orderId);
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            content.innerHTML = `
                <div class="order-detail-section">
                    <h3>🛒 Detail Item Pesanan #${orderId}</h3>
                    <ul class="items-list">
                        ${data.items.map(item => `
                            <li class="item-row">
                                <div>
                                    <strong>${item.name}</strong>
                                    <small style="color: #999; display: block;">Jumlah: ${item.quantity} pcs</small>
                                </div>
                                <span class="item-price">${formatRupiah(item.price * item.quantity)}</span>
                            </li>
                        `).join('')}
                    </ul>
                    <div class="items-total">
                        <strong>Total Harga:</strong>
                        <strong>${formatRupiah(data.items.reduce((sum, item) => sum + (item.price * item.quantity), 0))}</strong>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
        }
    } catch (error) {
        console.error('Error loading order detail:', error);
        alert('Gagal memuat detail pesanan');
    }
}

function closeOrderDetail() {
    document.getElementById('orderDetailModal').classList.remove('active');
}

// Quick Update Status (seperti di admin2.php)
async function quickUpdateStatus(orderId, newStatus) {
    if (!confirm(`Ubah status pesanan menjadi "${newStatus}"?`)) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_order_status');
        formData.append('order_id', orderId);
        formData.append('status', newStatus);
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadOrders();
            alert('✅ Status pesanan berhasil diupdate!');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('❌ Gagal mengupdate status');
    }
}

// Update Order Status (versi lengkap dengan dropdown)
async function updateOrderStatus(orderId, currentStatus) {
    const statuses = ['pending', 'proses', 'selesai'];
    const statusTexts = ['⏳ Pending', '⚙️ Proses', '✅ Selesai'];
    
    let options = statuses.map((status, index) => 
        `<option value="${status}" ${status === currentStatus ? 'selected' : ''}>${statusTexts[index]}</option>`
    ).join('');
    
    const modal = document.getElementById('orderDetailModal');
    const content = document.getElementById('orderDetailContent');
    
    content.innerHTML = `
        <div class="order-detail-section">
            <h3>📝 Update Status Pesanan #${orderId}</h3>
            <div class="form-group">
                <label>Pilih Status Baru:</label>
                <select id="newStatus" class="status-select">
                    ${options}
                </select>
            </div>
            <div class="form-actions">
                <button class="btn-secondary" onclick="closeOrderDetail()">Batal</button>
                <button class="btn-primary" onclick="confirmUpdateStatus(${orderId})">Simpan Status</button>
            </div>
        </div>
    `;
    
    modal.classList.add('active');
}

async function confirmUpdateStatus(orderId) {
    const newStatus = document.getElementById('newStatus').value;
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_order_status');
        formData.append('order_id', orderId);
        formData.append('status', newStatus);
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeOrderDetail();
            loadOrders();
            alert('✅ Status pesanan berhasil diupdate!');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        alert('❌ Gagal mengupdate status');
    }
}

// Load Products
async function loadProducts() {
    const productsTable = document.getElementById('productsTable');
    productsTable.innerHTML = '<p class="loading">Memuat produk...</p>';
    
    try {
        const formData = new FormData();
        formData.append('action', 'get_products');
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.products.length > 0) {
            productsTable.innerHTML = `
                <table>
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.products.map(product => `
                            <tr>
                                <td class="product-icon">${product.image}</td>
                                <td>
                                    <strong>${product.name}</strong><br>
                                    <small style="color: #999">${product.description}</small>
                                </td>
                                <td>${product.category}</td>
                                <td class="product-price">${formatRupiah(product.price)}</td>
                                <td>
                                    <span class="stock-badge ${getStockClass(product.stock)}">
                                        ${product.stock} unit
                                    </span>
                                </td>
                                <td>
                                    <div class="product-actions">
                                        <button class="btn-small btn-edit" onclick='editProduct(${JSON.stringify(product)})'>✏️</button>
                                        <button class="btn-small btn-delete" onclick="deleteProduct(${product.id})">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            productsTable.innerHTML = '<p class="loading">Belum ada produk</p>';
        }
    } catch (error) {
        console.error('Error loading products:', error);
        productsTable.innerHTML = '<p class="loading">Gagal memuat produk</p>';
    }
}

// Show Add Product Modal
function showAddProductModal() {
    document.getElementById('productModalTitle').textContent = 'Tambah Produk Baru';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('productModal').classList.add('active');
}

// Edit Product
function editProduct(product) {
    document.getElementById('productModalTitle').textContent = 'Edit Produk';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productImage').value = product.image;
    document.getElementById('productModal').classList.add('active');
}

// Save Product
async function saveProduct(event) {
    event.preventDefault();
    
    const productId = document.getElementById('productId').value;
    const action = productId ? 'update_product' : 'add_product';
    
    const formData = new FormData();
    formData.append('action', action);
    if (productId) formData.append('id', productId);
    formData.append('name', document.getElementById('productName').value);
    formData.append('description', document.getElementById('productDescription').value);
    formData.append('price', document.getElementById('productPrice').value);
    formData.append('stock', document.getElementById('productStock').value);
    formData.append('category', document.getElementById('productCategory').value);
    formData.append('image', document.getElementById('productImage').value);
    
    try {
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeProductModal();
            loadProducts();
            alert(productId ? '✅ Produk berhasil diupdate!' : '✅ Produk berhasil ditambahkan!');
        }
    } catch (error) {
        console.error('Error saving product:', error);
        alert('❌ Gagal menyimpan produk');
    }
}

// Delete Product
async function deleteProduct(productId) {
    if (!confirm('Yakin ingin menghapus produk ini?')) return;
    
    try {
        const formData = new FormData();
        formData.append('action', 'delete_product');
        formData.append('id', productId);
        
        const response = await fetch('admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadProducts();
            alert('✅ Produk berhasil dihapus!');
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        alert('❌ Gagal menghapus produk');
    }
}

function closeProductModal() {
    document.getElementById('productModal').classList.remove('active');
}

// Load Statistics
async function loadStats() {
    try {
        // Get orders
        const ordersFormData = new FormData();
        ordersFormData.append('action', 'get_orders');
        const ordersResponse = await fetch('admin.php', {
            method: 'POST',
            body: ordersFormData
        });
        const ordersData = await ordersResponse.json();
        
        // Get products
        const productsFormData = new FormData();
        productsFormData.append('action', 'get_products');
        const productsResponse = await fetch('admin.php', {
            method: 'POST',
            body: productsFormData
        });
        const productsData = await productsResponse.json();
        
        if (ordersData.success && productsData.success) {
            const orders = ordersData.orders;
            const products = productsData.products;
            
            // Calculate stats
            const totalOrders = orders.length;
            const totalRevenue = orders.reduce((sum, order) => {
                const amount = order.total_price || 0;
                return sum + parseFloat(amount);
            }, 0);
            const totalProducts = products.length;
            const pendingOrders = orders.filter(order => {
                const status = order.status || 'pending';
                return status === 'pending';
            }).length;
            
            // Update UI
            document.getElementById('totalOrders').textContent = totalOrders;
            document.getElementById('totalRevenue').textContent = formatRupiah(totalRevenue);
            document.getElementById('totalProducts').textContent = totalProducts;
            document.getElementById('pendingOrders').textContent = pendingOrders;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Helper Functions
function formatRupiah(number) {
    return 'Rp ' + parseInt(number).toLocaleString('id-ID');
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusText(status) {
    const statusMap = {
        'pending': '⏳ Pending',
        'proses': '⚙️ Diproses',
        'selesai': '✅ Selesai',
        'processing': '⚙️ Diproses',
        'shipped': '🚚 Dikirim',
        'completed': '✅ Selesai',
        'cancelled': '❌ Dibatalkan'
    };
    return statusMap[status] || status;
}

function getStockClass(stock) {
    if (stock > 20) return 'stock-high';
    if (stock > 10) return 'stock-medium';
    return 'stock-low';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const orderModal = document.getElementById('orderDetailModal');
    const productModal = document.getElementById('productModal');
    
    if (event.target === orderModal) {
        closeOrderDetail();
    }
    if (event.target === productModal) {
        closeProductModal();
    }
}