<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/orders.php';
require_once __DIR__ . '/../../controllers/suppliers.php';
require_once __DIR__ . '/../../controllers/stocks.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
    header('Location: /index.php');
    exit;
}

$orderController = new OrderController();
$supplierController = new SupplierController();
$stockController = new StockController();

$orders = $orderController->getAll();
$suppliers = $supplierController->getAll();
$stocks = $stockController->getAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Purchase Orders</h1>
        <button onclick="openModal('addOrderModal')" 
                class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md">
            Create Order
        </button>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Order Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                        <?php echo htmlspecialchars($order['order_number']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        <?php echo htmlspecialchars($order['supplier_name']); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo match($order['status']) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                default => 'bg-slate-100 text-slate-800'
                            }; ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        <?php echo $order['total_items']; ?> items
                        (<?php echo $order['total_received']; ?>/<?php echo $order['total_quantity']; ?>)
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                        <?php echo date('Y-m-d', strtotime($order['order_date'])); ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="viewOrder(<?php echo $order['id']; ?>)"
                                class="text-slate-600 hover:text-slate-900 mr-3">View</button>
                        <?php if ($order['status'] === 'pending'): ?>
                        <button onclick="markDelivered(<?php echo $order['id']; ?>)"
                                class="text-green-600 hover:text-green-900 mr-3">Mark Delivered</button>
                        <button onclick="cancelOrder(<?php echo $order['id']; ?>)"
                                class="text-red-600 hover:text-red-900">Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Create Order Modal -->
    <div id="addOrderModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Create Purchase Order</h3>
                </div>
                <form id="addOrderForm" class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="supplier_id">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select id="supplier_id" name="supplier_id" required
                                class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>">
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">
                            Order Items <span class="text-red-500">*</span>
                        </label>
                        <div id="orderItems">
                            <div class="flex items-center space-x-4 mb-2">
                                <select name="items[0][stock_id]" required
                                        class="flex-1 px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                                    <option value="">Select Item</option>
                                    <?php foreach ($stocks as $stock): ?>
                                    <option value="<?php echo $stock['id']; ?>">
                                        <?php echo htmlspecialchars($stock['name']); ?> (<?php echo $stock['unit']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="items[0][quantity]" required min="1"
                                       placeholder="Quantity" class="w-32 px-3 py-2 border border-slate-300 rounded-md">
                                <button type="button" onclick="removeOrderItem(this)"
                                        class="text-red-600 hover:text-red-900">Remove</button>
                            </div>
                        </div>
                        <button type="button" onclick="addOrderItem()"
                                class="mt-2 text-slate-600 hover:text-slate-900">+ Add Item</button>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="closeModal('addOrderModal')"
                                class="bg-white text-slate-700 px-4 py-2 rounded-md mr-2">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md">
                            Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Order Modal -->
    <div id="viewOrderModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Order Details</h3>
                </div>
                <div id="orderDetails" class="px-6 py-4">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="px-6 py-4 border-t border-slate-200">
                    <button type="button" onclick="closeModal('viewOrderModal')"
                            class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receive Order Modal -->
    <div id="receiveOrderModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Receive Order</h3>
                </div>
                <form id="receiveOrderForm" class="px-6 py-4">
                    <input type="hidden" id="receive_order_id" name="id">
                    <div id="receiveOrderItems">
                        <!-- Receive items form will be loaded here -->
                    </div>
                    <div class="flex justify-end mt-4">
                        <button type="button" onclick="closeModal('receiveOrderModal')"
                                class="bg-white text-slate-700 px-4 py-2 rounded-md mr-2">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md">
                            Confirm Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let itemCounter = 1;

function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function addOrderItem() {
    const container = document.getElementById('orderItems');
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-4 mb-2';
    div.innerHTML = `
        <select name="items[${itemCounter}][stock_id]" required
                class="flex-1 px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
            <option value="">Select Item</option>
            <?php foreach ($stocks as $stock): ?>
            <option value="<?php echo $stock['id']; ?>">
                <?php echo htmlspecialchars($stock['name']); ?> (<?php echo $stock['unit']; ?>)
            </option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="items[${itemCounter}][quantity]" required min="1"
               placeholder="Quantity" class="w-32 px-3 py-2 border border-slate-300 rounded-md">
        <button type="button" onclick="removeOrderItem(this)"
                class="text-red-600 hover:text-red-900">Remove</button>
    `;
    container.appendChild(div);
    itemCounter++;
}

function removeOrderItem(button) {
    button.parentElement.remove();
}

// Handle form submissions
document.getElementById('addOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await fetch('/api/orders.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.error || 'Failed to create order');
        }
    } catch (error) {
        alert('An error occurred');
    }
});

document.getElementById('receiveOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    try {
        const response = await fetch(`/api/orders.php?id=${id}&action=deliver`, {
            method: 'PUT',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert(result.error || 'Failed to receive order');
        }
    } catch (error) {
        alert('An error occurred');
    }
});

async function viewOrder(id) {
    try {
        const response = await fetch(`/api/orders.php?id=${id}`);
        const order = await response.json();
        if (order) {
            const details = document.getElementById('orderDetails');
            details.innerHTML = `
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Order Number</p>
                        <p class="mt-1">${order.order_number}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Supplier</p>
                        <p class="mt-1">${order.supplier_name}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Status</p>
                        <p class="mt-1">${order.status}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Order Date</p>
                        <p class="mt-1">${new Date(order.order_date).toLocaleDateString()}</p>
                    </div>
                </div>
                <div class="mt-6">
                    <h4 class="text-lg font-medium text-slate-900 mb-4">Order Items</h4>
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Received</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            ${order.items.map(item => `
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        ${item.item_name}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        ${item.quantity} ${item.unit}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        ${item.received_quantity || 0} ${item.unit}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            openModal('viewOrderModal');
        }
    } catch (error) {
        alert('Failed to load order details');
    }
}

async function markDelivered(id) {
    try {
        const response = await fetch(`/api/orders.php?id=${id}`);
        const order = await response.json();
        if (order) {
            const form = document.getElementById('receiveOrderItems');
            form.innerHTML = order.items.map((item, index) => `
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        ${item.item_name} (Ordered: ${item.quantity} ${item.unit})
                    </label>
                    <input type="hidden" name="items[${index}][stock_id]" value="${item.stock_id}">
                    <input type="number" name="items[${index}][received_quantity]" required
                           min="0" max="${item.quantity}" value="${item.quantity}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-md">
                </div>
            `).join('');
            document.getElementById('receive_order_id').value = id;
            openModal('receiveOrderModal');
        }
    } catch (error) {
        alert('Failed to load order details');
    }
}

async function cancelOrder(id) {
    if (confirm('Are you sure you want to cancel this order?')) {
        try {
            const response = await fetch(`/api/orders.php?id=${id}&action=cancel`, {
                method: 'PUT'
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.error || 'Failed to cancel order');
            }
        } catch (error) {
            alert('An error occurred');
        }
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
