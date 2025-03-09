<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/stocks.php';

// Initialize controller
$stockController = new StockController($db);
$stocks = $stockController->getAll();
$suppliers = $stockController->getSuppliers();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Stock Management</h1>
        <?php if (in_array($_SESSION['user']['role'], ['admin', 'store_manager'])): ?>
            <button onclick="openModal('addStockModal')"
                class="bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
                Add New Stock
            </button>
        <?php endif; ?>
    </div>

    <!-- Stock Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                    <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                    <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Stock Level</th>
                    <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reorder Level</th>
                    <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                    <th class="hidden md:table-cell px-3 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expiry Date</th>
                    <th class="px-3 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php foreach ($stocks as $stock): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-3 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900"><?php echo htmlspecialchars($stock['name']); ?></div>
                        <!-- Mobile-only info -->
                        <div class="md:hidden mt-1 text-xs text-slate-500">
                            <div>Category: <?php echo htmlspecialchars($stock['category']); ?></div>
                            <div>Unit: <?php echo htmlspecialchars($stock['unit']); ?></div>
                            <div>Reorder Level: <?php echo $stock['reorder_level']; ?></div>
                            <div>Supplier: <?php echo htmlspecialchars($stock['supplier_name']); ?></div>
                            <div>Expires: <?php echo $stock['expiry_date']; ?></div>
                        </div>
                    </td>
                    <td class="hidden md:table-cell px-3 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($stock['category']); ?></td>
                    <td class="hidden md:table-cell px-3 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($stock['unit']); ?></td>
                    <td class="px-3 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            <?php echo $stock['stock_level'] <= $stock['reorder_level'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                            <?php echo $stock['stock_level']; ?>
                        </span>
                    </td>
                    <td class="hidden md:table-cell px-3 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $stock['reorder_level']; ?></td>
                    <td class="hidden md:table-cell px-3 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($stock['supplier_name']); ?></td>
                    <td class="hidden md:table-cell px-3 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $stock['expiry_date']; ?></td>
                    <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button onclick="openAdjustStockModal(<?php echo htmlspecialchars(json_encode($stock)); ?>)"
                                    class="text-indigo-600 hover:text-indigo-900">
                                Adjust
                            </button>
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($stock)); ?>)"
                                    class="text-blue-600 hover:text-blue-900">
                                Edit
                            </button>
                            <button onclick="deleteStock(<?php echo $stock['id']; ?>)"
                                    class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                            <button onclick="viewTransactions(<?php echo $stock['id']; ?>)"
                                    class="text-green-600 hover:text-green-900">
                                History
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Stock Modal -->
    <div id="addStockModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 class="text-xl font-bold mb-4">Add New Stock</h2>
                <form id="addStockForm" onsubmit="return handleSubmit(event, 'create')">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Category</label>
                            <input type="text" name="category" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Unit</label>
                            <input type="text" name="unit" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Initial Stock Level</label>
                            <input type="number" name="stock_level" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Reorder Level</label>
                            <input type="number" name="reorder_level" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Expiry Date</label>
                            <input type="date" name="expiry_date" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Supplier</label>
                            <select name="supplier_id" required class="mt-1 block w-full rounded-md border-slate-300">
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('addStockModal')"
                            class="bg-white text-slate-700 px-4 py-2 rounded-md border border-slate-300">Cancel</button>
                        <button type="submit"
                            class="bg-slate-800 text-white px-4 py-2 rounded-md hover:bg-slate-700">Add Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Stock Modal -->
    <div id="editStockModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-lg w-full">
                <h2 class="text-xl font-bold mb-4">Edit Stock</h2>
                <form id="editStockForm" onsubmit="return handleSubmit(event, 'update')" class="space-y-4">
                    <input type="hidden" name="id" id="editStockId">

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Left Column -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Name</label>
                                <input type="text" name="name" id="editStockName" required class="mt-1 block w-full rounded-md border-slate-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Category</label>
                                <input type="text" name="category" id="editStockCategory" required class="mt-1 block w-full rounded-md border-slate-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Unit</label>
                                <input type="text" name="unit" id="editStockUnit" required class="mt-1 block w-full rounded-md border-slate-300">
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Reorder Level</label>
                                <input type="number" name="reorder_level" id="editStockReorderLevel" required class="mt-1 block w-full rounded-md border-slate-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Expiry Date</label>
                                <input type="date" name="expiry_date" id="editStockExpiryDate" required class="mt-1 block w-full rounded-md border-slate-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Supplier</label>
                                <select name="supplier_id" id="editStockSupplierId" required class="mt-1 block w-full rounded-md border-slate-300">
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>"><?php echo htmlspecialchars($supplier['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-2">
                        <button type="button" onclick="closeModal('editStockModal')" class="bg-white text-slate-700 px-4 py-2 rounded-md border border-slate-300">Cancel</button>
                        <button type="submit" class="bg-slate-800 text-white px-4 py-2 rounded-md hover:bg-slate-700">Update Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjustStockModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 class="text-xl font-bold mb-4">Adjust Stock Level</h2>
                <form id="adjustStockForm" onsubmit="return handleAdjustStock(event)">
                    <input type="hidden" name="id" id="adjustStockId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Item Name</label>
                            <p id="adjustStockName" class="mt-1 text-sm text-slate-900"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Current Stock Level</label>
                            <p id="adjustStockCurrentLevel" class="mt-1 text-sm text-slate-900"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Adjustment Type</label>
                            <select name="type" class="mt-1 block w-full rounded-md border-slate-300" required>
                                <option value="received">Add Stock (Received)</option>
                                <option value="issued">Remove Stock (Issued)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Quantity</label>
                            <input type="number" name="quantity" min="1" required class="mt-1 block w-full rounded-md border-slate-300">
                        </div>
                        <div id="issuedToField">
                            <label class="block text-sm font-medium text-slate-700">Issued To</label>
                            <input type="text" name="issued_to" class="mt-1 block w-full rounded-md border-slate-300" placeholder="Enter recipient name">
                        </div>
                        <div id="receivedFromField">
                            <label class="block text-sm font-medium text-slate-700">Received From</label>
                            <input type="text" name="received_from" class="mt-1 block w-full rounded-md border-slate-300" placeholder="Enter supplier name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Notes</label>
                            <textarea name="reason" class="mt-1 block w-full rounded-md border-slate-300" rows="2" placeholder="Enter any additional notes"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeAdjustStockModal()"
                            class="bg-white text-slate-700 px-4 py-2 rounded-md border border-slate-300">Cancel</button>
                        <button type="submit"
                            class="bg-slate-800 text-white px-4 py-2 rounded-md hover:bg-slate-700">Adjust Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transaction History Modal -->
    <div id="historyModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-4xl w-full">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Transaction History</h2>
                    <button onclick="closeModal('historyModal')" class="text-slate-500 hover:text-slate-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Transaction With</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="bg-white divide-y divide-slate-200">
                            <!-- Transaction history rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        // Handle form submissions
        async function handleSubmit(event, action) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            //add post[action]
            data.action = action;

            try {
                const url = action === 'create' ? '/api/stock/index.php' : `/api/stock/index.php?id=${data.id}`;
                const method = action === 'create' ? 'POST' : 'PUT';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    console.log(result);
                    alert(result.message || 'Operation failed');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
            }
        }

        // Open edit modal with stock data
        function openEditModal(stock) {
            document.getElementById('editStockId').value = stock.id;
            document.getElementById('editStockName').value = stock.name;
            document.getElementById('editStockCategory').value = stock.category;
            document.getElementById('editStockUnit').value = stock.unit;
            document.getElementById('editStockReorderLevel').value = stock.reorder_level;
            document.getElementById('editStockExpiryDate').value = stock.expiry_date;
            document.getElementById('editStockSupplierId').value = stock.supplier_id;
            openModal('editStockModal');
        }

        // Delete stock
        async function deleteStock(id) {
            if (!confirm('Are you sure you want to delete this stock item?')) return;

            try {
                const response = await fetch(`/api/stock/index.php?id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to delete stock');
                }
            } catch (error) {
                console.error(error);
                alert('An error occurred. Please try again.');
            }
        }

        // View transaction history
        function viewTransactions(stockId) {
            fetch(`/api/stock/transactions.php?stock_id=${stockId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('historyTableBody');
                    tbody.innerHTML = '';

                    data.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                ${new Date(transaction.transaction_date).toLocaleString()}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                ${transaction.transaction_type}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                ${transaction.quantity} ${transaction.unit}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                ${transaction.transaction_with || '-'}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });

                    openModal('historyModal');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load transaction history');
                });
        }

        // Handle adjust stock form submission
        function handleAdjustStock(event) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.action = 'adjust';

            fetch('/api/stock/adjust.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Stock adjusted successfully');
                    closeModal('adjustStockModal');
                    location.reload();
                } else {
                    alert(result.message || 'Failed to adjust stock');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to adjust stock');
            });

            return false;
        }

        function openAdjustStockModal(stock) {
            document.getElementById('adjustStockId').value = stock.id;
            document.getElementById('adjustStockName').textContent = stock.name;
            document.getElementById('adjustStockCurrentLevel').textContent = stock.stock_level;
            
            // Show the correct fields based on default selection
            toggleAdjustmentFields(document.querySelector('select[name="type"]').value);
            
            document.getElementById('adjustStockModal').classList.remove('hidden');
        }

        function toggleAdjustmentFields(type) {
            const issuedToField = document.getElementById('issuedToField');
            const receivedFromField = document.getElementById('receivedFromField');

            if (type === 'issued') {
                issuedToField.style.display = 'block';
                receivedFromField.style.display = 'none';
                receivedFromField.querySelector('input').value = '';
            } else {
                issuedToField.style.display = 'none';
                receivedFromField.style.display = 'block';
                issuedToField.querySelector('input').value = '';
            }
        }

        // Add event listener for adjustment type change
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            toggleAdjustmentFields(this.value);
        });

        function closeAdjustStockModal() {
            document.getElementById('adjustStockModal').classList.add('hidden');
            document.getElementById('adjustStockForm').reset();
        }

        // Make sure all modal-related functions are defined
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openEditModal(stock) {
            document.getElementById('editStockId').value = stock.id;
            document.getElementById('editStockName').value = stock.name;
            document.getElementById('editStockCategory').value = stock.category;
            document.getElementById('editStockUnit').value = stock.unit;
            document.getElementById('editStockReorderLevel').value = stock.reorder_level;
            document.getElementById('editStockExpiryDate').value = stock.expiry_date;
            document.getElementById('editStockSupplierId').value = stock.supplier_id;
            openModal('editStockModal');
        }

        // Add event listeners for clicking outside modals to close them
        document.addEventListener('DOMContentLoaded', function() {
            const modals = ['addStockModal', 'editStockModal', 'adjustStockModal', 'transactionHistoryModal'];
            
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeModal(modalId);
                        }
                    });
                }
            });
        });
    </script>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>