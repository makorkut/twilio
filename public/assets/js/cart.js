/**
 * Shopping Cart Module
 * Handles cart operations: add, remove, update quantity
 */

class ShoppingCart {
    constructor() {
        this.items = this.loadCart();
        this.currency = 'TRY';
        this.init();
    }

    init() {
        this.attachEventListeners();
        this.updateCartCount();
    }

    attachEventListeners() {
        // Add to cart buttons
        document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                const quantity = parseInt(btn.dataset.quantity || 1);
                this.addItem(productId, quantity);
            });
        });

        // Remove from cart buttons
        document.querySelectorAll('[data-remove-from-cart]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const productId = btn.dataset.productId;
                this.removeItem(productId);
            });
        });

        // Update quantity inputs
        document.querySelectorAll('[data-cart-quantity]').forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = input.dataset.productId;
                const quantity = parseInt(input.value);
                this.updateQuantity(productId, quantity);
            });
        });
    }

    loadCart() {
        const stored = localStorage.getItem('shopping_cart');
        return stored ? JSON.parse(stored) : [];
    }

    saveCart() {
        localStorage.setItem('shopping_cart', JSON.stringify(this.items));
        this.updateCartCount();
        this.dispatchCartUpdate();
    }

    addItem(productId, quantity = 1) {
        const existingItem = this.items.find(item => item.product_id === productId);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.items.push({
                product_id: productId,
                quantity: quantity,
                added_at: new Date().toISOString()
            });
        }

        this.saveCart();
        this.showNotification('Product added to cart', 'success');
    }

    removeItem(productId) {
        this.items = this.items.filter(item => item.product_id !== productId);
        this.saveCart();
        this.showNotification('Product removed from cart', 'info');
    }

    updateQuantity(productId, quantity) {
        const item = this.items.find(item => item.product_id === productId);

        if (item) {
            if (quantity <= 0) {
                this.removeItem(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }

    getItems() {
        return this.items;
    }

    getItemCount() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    }

    clearCart() {
        this.items = [];
        this.saveCart();
    }

    updateCartCount() {
        const count = this.getItemCount();
        document.querySelectorAll('[data-cart-count]').forEach(el => {
            el.textContent = count;
            el.style.display = count > 0 ? 'inline' : 'none';
        });
    }

    dispatchCartUpdate() {
        window.dispatchEvent(new CustomEvent('cartUpdated', {
            detail: {
                items: this.items,
                count: this.getItemCount()
            }
        }));
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#27ae60' : '#3498db'};
            color: white;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    async syncWithServer() {
        try {
            const response = await fetch('/api/cart/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    items: this.items
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('Cart synced with server');
            }
        } catch (error) {
            console.error('Failed to sync cart:', error);
        }
    }
}

// Initialize cart
const cart = new ShoppingCart();

// Export for use in other modules
window.ShoppingCart = cart;

// CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
