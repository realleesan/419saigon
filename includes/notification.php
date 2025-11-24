<!-- Notification System -->
<div id="notification" class="notification">
    <div class="notification-content">
        <span class="notification-icon"></span>
        <span class="notification-message"></span>
        <button class="notification-close">&times;</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-content">
        <div class="confirm-header">
            <h3>Xác nhận</h3>
            <button class="confirm-close">&times;</button>
        </div>
        <div class="confirm-body">
            <span class="confirm-icon">⚠️</span>
            <p class="confirm-message"></p>
        </div>
        <div class="confirm-actions">
            <button class="btn btn-outline" id="confirmCancel">Hủy</button>
            <button class="btn btn-danger" id="confirmOk">Xác nhận</button>
        </div>
    </div>
</div>

<style>
/* Notification System */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    width: auto;
    max-width: 300px;
    background: var(--color-black);
    border: 1px solid var(--color-gold);
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    padding: 8px 10px;
    gap: 6px;
    min-height: 32px;
}

.notification-icon {
    font-size: 1rem;
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-message {
    color: var(--color-cream);
    font-weight: 500;
    font-size: 0.8rem;
    line-height: 1.2;
    flex: 1;
    white-space: normal;
    word-wrap: break-word;
    max-width: 200px;
}

.notification-close {
    background: none;
    border: none;
    color: var(--color-gold);
    font-size: 1rem;
    cursor: pointer;
    padding: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-close:hover {
    color: var(--color-cream);
}

/* Notification Types */
.notification.success {
    border-color: #00ff00;
}

.notification.success .notification-icon {
    color: #00ff00;
}

.notification.error {
    border-color: #ff4444;
}

.notification.error .notification-icon {
    color: #ff4444;
}

.notification.warning {
    border-color: #ffa500;
}

.notification.warning .notification-icon {
    color: #ffa500;
}

.notification.info {
    border-color: #4488ff;
}

.notification.info .notification-icon {
    color: #4488ff;
}

/* Confirmation Modal */
.confirm-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10001;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.confirm-modal.show {
    opacity: 1;
    visibility: visible;
}

.confirm-content {
    background: var(--color-black);
    border: 2px solid var(--color-gold);
    border-radius: 12px;
    min-width: 400px;
    max-width: 500px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.confirm-modal.show .confirm-content {
    transform: scale(1);
}

.confirm-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
}

.confirm-header h3 {
    color: var(--color-gold);
    margin: 0;
    font-size: 1.3rem;
}

.confirm-close {
    background: none;
    border: none;
    color: var(--color-gold);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.confirm-close:hover {
    color: var(--color-cream);
}

.confirm-body {
    padding: var(--spacing-lg);
    text-align: center;
}

.confirm-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: var(--spacing-md);
}

.confirm-message {
    color: var(--color-cream);
    font-size: 1.1rem;
    margin: 0;
    line-height: 1.5;
}

.confirm-actions {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-lg);
    border-top: 1px solid rgba(212, 175, 55, 0.2);
    justify-content: flex-end;
}

.btn-danger {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
}

/* Responsive */
@media (max-width: 768px) {
    .notification {
        top: 10px;
        right: 10px;
        left: 10px;
        max-width: none;
    }
    
    .notification-content {
        padding: 6px 8px;
        gap: 4px;
        min-height: 28px;
    }
    
    .notification-message {
        font-size: 0.75rem;
        max-width: none;
    }
    
    .notification-icon {
        font-size: 0.9rem;
        width: 14px;
        height: 14px;
    }
    
    .notification-close {
        font-size: 0.9rem;
        width: 14px;
        height: 14px;
    }
    
    .confirm-content {
        min-width: 90%;
        margin: var(--spacing-md);
    }
    
    .confirm-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Notification System
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.getElementById('notification');
    const icon = notification.querySelector('.notification-icon');
    const messageEl = notification.querySelector('.notification-message');
    const closeBtn = notification.querySelector('.notification-close');
    
    // Set message
    messageEl.textContent = message;
    
    // Set type and icon
    notification.className = 'notification';
    notification.classList.add(type);
    
    switch(type) {
        case 'success':
            icon.textContent = '✅';
            break;
        case 'error':
            icon.textContent = '❌';
            break;
        case 'warning':
            icon.textContent = '⚠️';
            break;
        case 'info':
        default:
            icon.textContent = 'ℹ️';
            break;
    }
    
    // Show notification
    notification.classList.add('show');
    
    // Auto hide after duration
    setTimeout(() => {
        hideNotification();
    }, duration);
    
    // Close button functionality
    closeBtn.onclick = hideNotification;
}

function hideNotification() {
    const notification = document.getElementById('notification');
    notification.classList.remove('show');
}

// Replace alert function
function showAlert(message, type = 'info') {
    showNotification(message, type, 4000);
}

// Confirmation Modal System
function showConfirm(message, onConfirm, onCancel = null) {
    const modal = document.getElementById('confirmModal');
    const messageEl = modal.querySelector('.confirm-message');
    const confirmBtn = document.getElementById('confirmOk');
    const cancelBtn = document.getElementById('confirmCancel');
    const closeBtn = modal.querySelector('.confirm-close');
    
    // Set message
    messageEl.textContent = message;
    
    // Show modal
    modal.classList.add('show');
    
    // Remove existing event listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    const newCloseBtn = closeBtn.cloneNode(true);
    
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    
    // Add new event listeners
    newConfirmBtn.addEventListener('click', () => {
        hideConfirm();
        if (onConfirm) onConfirm();
    });
    
    newCancelBtn.addEventListener('click', () => {
        hideConfirm();
        if (onCancel) onCancel();
    });
    
    newCloseBtn.addEventListener('click', () => {
        hideConfirm();
        if (onCancel) onCancel();
    });
    
    // Close on background click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideConfirm();
            if (onCancel) onCancel();
        }
    });
}

function hideConfirm() {
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('show');
}

// Replace confirm function
function showConfirmDialog(message, onConfirm, onCancel = null) {
    showConfirm(message, onConfirm, onCancel);
}
</script>
