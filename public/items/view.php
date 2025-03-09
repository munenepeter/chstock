<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/items.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
    header('Location: /index.php');
    exit;
}

$itemController = new ItemController($db);
$items = $itemController->getAll();

$items = $items['data'] ?? [];
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Items</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all items in the system.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button type="button"
                onclick="openModal('addItemModal')"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Add Item
            </button>
        </div>
    </div>

    <!-- Search Box -->
    <div class="my-4">
        <div class="relative rounded-md shadow-sm">
            <input type="text"
                id="" placeholder="Search items..." disabled
                class="cursor-not-allowed block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>



    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Unit of Issue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Purchase Limit</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            KES <?php echo number_format($item['unit_price'], 2); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <?php echo htmlspecialchars($item['unit_of_issue']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <?php echo number_format($item['purchase_limit']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="editItem(<?php echo $item['id']; ?>)"
                                class="text-slate-600 hover:text-slate-900 mr-3">Edit</button>
                            <button onclick="deleteItem(<?php echo $item['id']; ?>)"
                                class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Item Modal -->
    <div id="addItemModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center mx-auto min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Add New Item</h3>
                </div>
                <form id="addItemForm" class="px-6 py-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="name">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="unit_price">
                            Unit Price <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="unit_price" name="unit_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="unit_of_issue">
                            Unit of Issue <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="unit_of_issue" name="unit_of_issue" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="purchase_limit">
                            Purchase Limit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="purchase_limit" name="purchase_limit" min="1" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('addItemModal')"
                            class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-md hover:bg-slate-600">
                            Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div id="editItemModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden">
        <div class="flex items-center justify-center mx-auto min-h-screen">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-medium text-slate-900">Edit Item</h3>
                </div>
                <form id="editItemForm" class="px-6 py-4">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="edit_name">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_name" name="name" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="edit_unit_price">
                            Unit Price <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_unit_price" name="unit_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="edit_unit_of_issue">
                            Unit of Issue <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="edit_unit_of_issue" name="unit_of_issue" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2" for="edit_purchase_limit">
                            Purchase Limit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_purchase_limit" name="purchase_limit" min="1" required
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-1 focus:ring-slate-500">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="edit_active" name="active" class="rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                            <span class="ml-2 text-sm text-slate-700">Active</span>
                        </label>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('editItemModal')"
                            class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-500">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-md hover:bg-slate-600">
                            Update Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showToast(message, type = 'success') {

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

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById(modalId).classList.add('flex');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('flex');
        document.getElementById(modalId).classList.add('hidden');
    }

    // Handle form submissions
    document.getElementById('addItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('/api/items/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Item added successfully', 'success');
                    location.reload();
                } else {
                    showToast(result.message || 'Error adding item', 'error');
                }
            })
            .catch(error => {
                showToast('Error adding item', 'error');
                console.error('Error:', error);
            });
    });

    document.getElementById('editItemForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const id = data.id;
        delete data.id;

        // Convert active checkbox to boolean
        data.active = formData.has('active');

        fetch(`/api/items/?id=${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Item updated successfully');
                    location.reload();
                } else {
                    showToast(result.message || 'Error updating item', 'error');
                }
            })
            .catch(error => {
                showToast('Error updating item', 'error');
                console.error('Error:', error);
            });
    });

    function editItem(id) {
        fetch(`/api/items/?id=${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const item = result.data;
                    document.getElementById('edit_id').value = item.id;
                    document.getElementById('edit_name').value = item.name;
                    document.getElementById('edit_unit_price').value = item.unit_price;
                    document.getElementById('edit_unit_of_issue').value = item.unit_of_issue;
                    document.getElementById('edit_purchase_limit').value = item.purchase_limit;
                    document.getElementById('edit_active').checked = item.active == 1;
                    openModal('editItemModal');
                } else {
                    showToast(result.message || 'Error fetching item details', 'error');
                }
            })
            .catch(error => {
                showToast('Error fetching item details', 'error');
            });
    }

    function deleteItem(id) {
        if (confirm('Are you sure you want to delete this item?')) {
            fetch(`/api/items/?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast('Item deleted successfully');
                        location.reload();
                    } else {
                        showToast(result.message || 'Error deleting item' , 'error');
                    }
                })
                .catch(error => {
                    showToast('Error deleting item', 'error');
                    console.error('Error:', error);
                });
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>