<?php
require_once __DIR__ . '/../../includes/header.php';

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Purchase Orders</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all purchase orders in the system.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <?php if (in_array($_SESSION['user']['role'], ['admin', 'procurement_officer'])): ?>
                <button type="button"
                    id="addPurchaseOrderBtn"
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                    Add Purchase Order
                </button>
            <?php endif; ?>
        </div>
    </div>


    <!-- Search Box -->
    <div class="my-4">
        <div class="relative rounded-md shadow-sm">
            <input type="text"
                id="searchPurchaseOrder" placeholder="Search purchase orders..."
                class="block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LPO Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="purchaseOrdersTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Purchase orders will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Purchase Order Modal -->
<div id="purchaseOrderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Create New Purchase Order</h3>
            <form id="purchaseOrderForm" class="space-y-2 px-2 py-1 bg-slate-50">
                <input type="hidden" id="lpo_number">
                <div>
                    <label for="ro_number" class="block text-slate-700 text-sm font-medium">Acquisition Order Number</label>
                    <select id="ro_number" name="ro_number" required class="input">
                        <option value="">Select Acquisition Order</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="supplier_id" class="block text-slate-700 text-sm font-medium">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required class="input">
                            <!-- Suppliers will be loaded here -->
                        </select>
                    </div>
                    <div>
                        <label for="procurement_method" class="block text-slate-700 text-sm font-medium">Procurement Method</label>
                        <select id="procurement_method" name="procurement_method" required class="input">
                            <option value="tender">Tender</option>
                            <option value="quotation">Quotation</option>
                            <option value="direct">Direct</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="procurement_reference" class="block text-slate-700 text-sm font-medium">Procurement Reference</label>
                        <input type="text" id="procurement_reference" name="procurement_reference" class="input">
                    </div>
                    <div>
                        <label for="date_of_commitment" class="block text-slate-700 text-sm font-medium">Date of Commitment</label>
                        <input type="date" id="date_of_commitment" name="date_of_commitment" required class="input">
                    </div>
                </div>

                <div>
                    <label class="block text-slate-700 text-sm font-medium">Items <i class="font-ligh text-slate-300 text-xs">(form selected acquisition order)</i></label>
                    <div id="itemsContainer" class="space-y-2">
                        <!-- Items will be loaded here -->
                        <p class="text-sm text-slate-400">Please select Acquisition Order</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let purchaseOrders = [];

    // Load purchase orders on page load
    document.addEventListener('DOMContentLoaded', loadPurchaseOrders);

    // Search functionality
    document.getElementById('searchPurchaseOrder').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const filteredPurchaseOrders = purchaseOrders.filter(po =>
            po.lpo_number.toLowerCase().includes(searchTerm) ||
            po.supplier_name.toLowerCase().includes(searchTerm)
        );
        renderPurchaseOrders(filteredPurchaseOrders);
    });

    // Add purchase order button click
    const addPurchaseOrderBtn = document.getElementById('addPurchaseOrderBtn');
    if (addPurchaseOrderBtn) {
        addPurchaseOrderBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add Purchase Order';
            document.getElementById('lpo_number').value = '';
            document.getElementById('purchaseOrderForm').reset();
            document.getElementById('purchaseOrderModal').classList.remove('hidden');
        });
    }

    // Form submission
    document.getElementById('purchaseOrderForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const lpoNumber = document.getElementById('lpo_number').value;
        const data = {
            ro_number: document.getElementById('ro_number').value,
            supplier_id: document.getElementById('supplier_id').value,
            procurement_method: document.getElementById('procurement_method').value,
            procurement_reference: document.getElementById('procurement_reference').value,
            date_of_commitment: document.getElementById('date_of_commitment').value,
            items: getItemsData()
        };

        try {
            const response = await fetch(`/api/purchase_orders/${lpoNumber ? '?lpo_number=' + lpoNumber : ''}`, {
                method: lpoNumber ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                closeModal();
                loadPurchaseOrders();
                showAlert(result.message, 'success');
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to save purchase order', 'error');
        }
    });

    async function loadPurchaseOrders() {
        try {
            const response = await fetch('/api/purchase_orders/');
            const result = await response.json();

            if (result.success) {
                purchaseOrders = result.data;
                renderPurchaseOrders(purchaseOrders);
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to load purchase orders', 'error');
        }
    }

    function renderPurchaseOrders(pos) {
        const tbody = document.getElementById('purchaseOrdersTableBody');
        tbody.innerHTML = '';

        pos.forEach(po => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${po.lpo_number}</td>
            <td class="px-6 py-4 whitespace-nowrap">${po.supplier_name}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    ${po.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    ${po.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">${po.total_amount}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <?php if (in_array($_SESSION['user']['role'], ['admin', 'procurement_officer'])): ?>
                <button onclick="editPurchaseOrder(${po.lpo_number})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                <button onclick="deletePurchaseOrder(${po.lpo_number})" class="text-red-600 hover:text-red-900">Delete</button>
                <?php endif; ?>
            </td>
        `;
            tbody.appendChild(tr);
        });
    }

    async function editPurchaseOrder(lpoNumber) {
        try {
            const po = purchaseOrders.find(p => p.lpo_number === lpoNumber);
            if (!po) return;

            document.getElementById('modalTitle').textContent = 'Edit Purchase Order';
            document.getElementById('lpo_number').value = po.lpo_number;
            document.getElementById('ro_number').value = po.ro_number;
            document.getElementById('supplier_id').value = po.supplier_id;
            document.getElementById('procurement_method').value = po.procurement_method;
            document.getElementById('procurement_reference').value = po.procurement_reference;
            document.getElementById('date_of_commitment').value = po.date_of_commitment;
            populateItems(po.items);

            document.getElementById('purchaseOrderModal').classList.remove('hidden');
        } catch (error) {
            showAlert('Failed to load purchase order details', 'error');
        }
    }

    async function deletePurchaseOrder(lpoNumber) {
        if (!confirm('Are you sure you want to delete this purchase order?')) return;

        try {
            const response = await fetch(`/api/purchase_orders/?lpo_number=${lpoNumber}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                loadPurchaseOrders();
                showAlert(result.message, 'success');
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to delete purchase order', 'error');
        }
    }

    function closeModal() {
        document.getElementById('purchaseOrderModal').classList.add('hidden');
        document.getElementById('purchaseOrderForm').reset();
        document.getElementById('itemsContainer').innerHTML = '';
    }

    function showAlert(message, type = 'success') {
        let classes = 'fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4';
        if (type === 'success') {
            classes = 'fixed top-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4';
        }

        let actual_msg = type === 'success' ? 'Success ' : 'Error ';

        if (message) {
            actual_msg += ': ' + message;
        }
        // Show success message
        const toast = document.createElement('div');
        toast.className = classes;
        toast.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">${actual_msg}</p>
                        </div>
                    </div>
                `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    function getItemsData() {
        const items = [];
        const itemInputs = document.querySelectorAll('#itemsContainer .item-input');

        itemInputs.forEach((input, index) => {
            const item = {
                name: input.querySelector('.item-name').value,
                quantity: input.querySelector('.item-quantity').value,
                unit_price: input.querySelector('.item-unit-price').value
            };

            items.push(item);
        });

        return items;
    }

    function populateItems(items) {
        const itemsContainer = document.getElementById('itemsContainer');
        itemsContainer.innerHTML = '';

        if (!itemsContainer) {
            console.error("Error: itemsContainer not found.");
            return;
        }

        // Add the header first
        const headerRow = document.createElement('div');
        headerRow.classList.add('item-header', 'grid', 'grid-cols-12', 'gap-4', 'mb-2', 'font-medium', 'text-gray-700', 'items-center');

        headerRow.innerHTML = `
        <div class="col-span-3 pl-2">Name</div>
        <div class="col-span-3 pl-2">Quantity</div>
        <div class="col-span-2 pl-2">Unit Price</div>
        <div class="col-span-3 pl-2">New Quantity</div>
        <div class="col-span-1"></div>
    `;

        itemsContainer.appendChild(headerRow);

        // Add a separator line
        const separatorLine = document.createElement('div');
        separatorLine.classList.add('col-span-12', 'border-b', 'border-gray-200', 'mb-3');
        itemsContainer.appendChild(separatorLine);

        items.forEach((item, index) => {
            const rowId = `item-${Date.now()}-${index}`;
            const itemInput = document.createElement('div');
            itemInput.classList.add('item-input', 'grid', 'grid-cols-12', 'gap-4', 'mt-2', 'items-center');
            itemInput.dataset.rowId = rowId; // Set a unique row ID

            itemInput.innerHTML = `
        <div class="col-span-3" data-item-id="${item.item_id}" data-row-id="${rowId}">
            <input type="text" name="items[${item.item_id}][name]" 
                class="w-full rounded-md border-slate-300 text-sm cursor-not-allowed"
                value="${item.item_name ?? ''}" placeholder="Name" required readonly>
        </div>
        <div class="col-span-3">
            <input type="number" name="items[${item.item_id}][quantity]"
                class="w-full rounded-md border-slate-300 text-sm cursor-not-allowed" placeholder="Quantity" required
                value="${item.quantity ?? ''}" readonly>
        </div>
        <div class="col-span-2">
            <input type="number" name="items[${item.item_id}][unit_price]"
                class="w-full rounded-md border-slate-300 text-sm cursor-not-allowed" placeholder="Unit Price" required
                value="${item.unit_price ?? ''}" readonly>
        </div>
        <div class="col-span-3">
            <input type="number" name="items[${item.item_id}][new_quantity]"
                class="w-full rounded-md border-slate-300 text-sm" placeholder="New Quantity" required
                min="0" max="${item.quantity ?? 10000}" value="${item.quantity ?? ''}">
        </div>
        <div class="col-span-1">
            <button type="button" class="remove-item-btn text-red-600 hover:text-red-700 remove-item">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

            itemsContainer.appendChild(itemInput);

            //rm item
            itemInput.querySelector('.remove-item-btn').addEventListener('click', () => {
                itemInput.remove();
            });
        });
    }


    document.getElementById('ro_number').addEventListener('change', async () => {
        const roNumber = document.getElementById('ro_number').value;

        try {
            const response = await fetch(`/api/acquisition/?endpoint=view&roNumber=${roNumber}`);
            const result = await response.json();

            if (result.success) {
                const items = result.data.items;
                populateItems(items);
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            console.error(error);
            showAlert('Failed to load requisition order items', 'error');
        }
    });

    document.getElementById('addPurchaseOrderBtn').addEventListener('click', () => {
        fill_acquisition_orders();
        fill_suppliers();
    });

    function fill_suppliers() {
        const supplierSelect = document.getElementById('supplier_id');

        supplierSelect.innerHTML = '<option value="">Select Supplier</option>';

        fetch('/api/suppliers')
            .then(response => response.json())
            .then(result => {
                if (result.success) {

                    const suppliers = result.data;

                    suppliers.forEach(supplier => {
                        const option = document.createElement('option');
                        option.value = supplier.id;
                        option.textContent = supplier.name;

                        supplierSelect.appendChild(option);
                    });
                } else {
                    showAlert(result.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Failed to load suppliers', 'error');
            });
    }

    function fill_acquisition_orders() {
        const roNumberSelect = document.getElementById('ro_number');

        roNumberSelect.innerHTML = '<option value="">Select Acquisition Order</option>';

        fetch('/api/acquisition/?endpoint=requisitions')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const acquisition_orders = result.data;

                    acquisition_orders.forEach(order => {
                        const option = document.createElement('option');
                        option.value = order.ro_number;
                        option.textContent = order.ro_number;

                        roNumberSelect.appendChild(option);
                    });
                } else {
                    showAlert(result.error, 'error');
                }
            })
            .catch(error => {
                showAlert('Failed to load requisition orders', 'error');
            });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>