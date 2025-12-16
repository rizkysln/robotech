// script.js - JavaScript Logic
let cart = [];
let products = [];

// Load products saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
    loadProducts();
});

// Load products dari database
function loadProducts() {
    fetch('api/get_products.php')
        .then(response => response.json())
        .then(data => {
            products = data;
            displayProducts(products);
        })
        .catch(error => console.error('Error:', error));
}

// Display products
function displayProducts(productsToShow) {
    const grid = document.getElementById('productsGrid');
    grid.innerHTML = '';
    
    if (productsToShow.length === 0) {
        grid.innerHTML = '<p style="text-align: center; color: #999; padding: 40px;">Produk tidak ditemukan</p>';
        return;
    }
    
    productsToShow.forEach(product => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.innerHTML = `
            <div class="product-image">
                <img src="images/${product.image}" alt="${product.name}">
            </div>
            <div class="product-info">
                <span class="product-category">${product.category}</span>
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-footer">
                    <div>
                        <div class="product-price">${formatPrice(product.price)}</div>
                        <div class="product-stock">Stok: ${product.stock}</div>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart(${product.id})">
                        ➕ Tambah
                    </button>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}


// Search products
function searchProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filtered = products.filter(product =>
        product.name.toLowerCase().includes(searchTerm) ||
        product.category.toLowerCase().includes(searchTerm)
    );
    displayProducts(filtered);
}

// Add to cart
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }

    updateCart();
}

// Update cart display
function updateCart() {
    const cartContent = document.getElementById('cartContent');
    const cartFooter = document.getElementById('cartFooter');
    const cartBadge = document.getElementById('cartBadge');

    // Update badge
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartBadge.textContent = totalItems;

    if (cart.length === 0) {
        cartContent.innerHTML = '<p class="empty-cart">Keranjang masih kosong</p>';
        cartFooter.style.display = 'none';
        return;
    }

    cartFooter.style.display = 'block';

    // Display cart items
    cartContent.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-header">
                <div>
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatPrice(item.price)}</div>
                </div>
                <button class="delete-btn" onclick="removeFromCart(${item.id})">🗑️</button>
            </div>
            <div class="cart-item-controls">
                <button class="qty-btn qty-minus" onclick="updateQuantity(${item.id}, -1)">−</button>
                <span class="cart-item-quantity">${item.quantity}</span>
                <button class="qty-btn qty-plus" onclick="updateQuantity(${item.id}, 1)">+</button>
                <span class="cart-item-subtotal">${formatPrice(item.price * item.quantity)}</span>
            </div>
        </div>
    `).join('');

    // Update total
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('totalPrice').textContent = formatPrice(total);
}

// Update quantity
function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (!item) return;

    item.quantity += change;

    if (item.quantity <= 0) {
        removeFromCart(productId);
        return;
    }

    updateCart();
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

// Toggle cart sidebar
function toggleCart() {
    const overlay = document.getElementById('cartOverlay');
    const sidebar = document.getElementById('cartSidebar');

    overlay.classList.toggle('active');
    sidebar.classList.toggle('active');
}

// Show checkout form
function showCheckoutForm() {
    if (cart.length === 0) return;

    const modal = document.getElementById('checkoutModal');
    const orderSummary = document.getElementById('orderSummary');

    // Build order summary
    let summaryHTML = '';
    cart.forEach(item => {
        summaryHTML += `
            <div class="summary-item">
                <span>${item.name} x${item.quantity}</span>
                <span>${formatPrice(item.price * item.quantity)}</span>
            </div>
        `;
    });

    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    summaryHTML += `
        <div class="summary-item summary-total">
            <span>Total</span>
            <span>${formatPrice(total)}</span>
        </div>
    `;

    orderSummary.innerHTML = summaryHTML;
    modal.classList.add('active');
    toggleCart(); // Close cart sidebar
}

// Close checkout
function closeCheckout() {
    document.getElementById('checkoutModal').classList.remove('active');
}

// Submit order
function submitOrder(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    const orderData = {
        customer_name: formData.get('name'),
        email: formData.get('email'),
        phone: formData.get('phone'),
        address: formData.get('address'),
        items: cart,
        total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    };

    fetch('api/checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Pesanan berhasil! Terima kasih telah berbelanja di RoboTech Market 🎉');
                cart = [];
                updateCart();
                closeCheckout();
                form.reset();
            } else {
                alert('❌ Terjadi kesalahan. Silakan coba lagi.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan. Silakan coba lagi.');
        });
}

// Format price to Rupiah
function formatPrice(price) {
    return 'Rp ' + price.toLocaleString('id-ID');
}