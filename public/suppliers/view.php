<?php
require_once __DIR__ . '/../../includes/header.php';

// Check if user has permission
if (!in_array($_SESSION['user']['role'], ['admin', 'store_manager'])) {
    $_SESSION['error'] = "You don't have permission to access this page";
    header('Location: /dashboard.php');
    exit;
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Suppliers</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all suppliers in the system.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button type="button" 
                    onclick="openAddModal()"
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                Add Supplier
            </button>
        </div>
    </div>

    <!-- Search Box -->
    <div class="mt-4">
        <div class="relative rounded-md shadow-sm">
            <input type="text" 
                   id="searchInput"
                   class="block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                   placeholder="Search suppliers...">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Phone</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Rating</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="suppliersTableBody" class="divide-y divide-gray-200 bg-white">
                            <!-- Table rows will be dynamically populated -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Supplier Modal -->
<div id="supplierModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <form id="supplierForm" onsubmit="handleSubmit(event)">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4" id="modalTitle">Add Supplier</h3>
                    <input type="hidden" id="supplierId">
                    
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" id="name" name="name" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" name="email" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="tel" id="phone" name="phone" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea id="address" name="address" rows="3" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                        
                        <div>
                            <label for="rating" class="block text-sm font-medium text-gray-700">Rating (0-100)</label>
                            <input type="number" id="rating" name="rating" min="0" max="100"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="active" name="active"
                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="active" class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button"
                            onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let suppliers = [];

// Load suppliers on page load
document.addEventListener('DOMContentLoaded', loadSuppliers);

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const filteredSuppliers = suppliers.filter(supplier => 
        supplier.name.toLowerCase().includes(searchTerm) ||
        supplier.email.toLowerCase().includes(searchTerm) ||
        supplier.phone.toLowerCase().includes(searchTerm)
    );
    renderSuppliers(filteredSuppliers);
});

async function loadSuppliers() {
    try {
        const response = await fetch('/api/suppliers/');
        const result = await response.json();
        if (result.success) {
            suppliers = result.data;
            renderSuppliers(suppliers);
        } else {
            showError(result.error);
        }
    } catch (error) {
        showError('Failed to load suppliers');
    }
}

function renderSuppliers(suppliersToRender) {
    const tbody = document.getElementById('suppliersTableBody');
    tbody.innerHTML = '';
    
    suppliersToRender.forEach(supplier => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">${supplier.name}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${supplier.email}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${supplier.phone}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                <div class="flex items-center">
                    <span class="mr-2">${supplier.rating}%</span>
                    <div class="w-24 h-2 bg-gray-200 rounded-full">
                        <div class="h-full bg-indigo-600 rounded-full" style="width: ${supplier.rating}%"></div>
                    </div>
                </div>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 ${supplier.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${supplier.active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                <button onclick="editSupplier(${supplier.id})" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</button>
                <button onclick="deleteSupplier(${supplier.id})" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Supplier';
    document.getElementById('supplierForm').reset();
    document.getElementById('supplierId').value = '';
    document.getElementById('active').checked = true;
    document.getElementById('supplierModal').classList.remove('hidden');
}

async function editSupplier(id) {
    try {
        const response = await fetch(`/api/suppliers/?id=${id}`);
        const result = await response.json();
        if (result.success) {
            const supplier = result.data;
            document.getElementById('modalTitle').textContent = 'Edit Supplier';
            document.getElementById('supplierId').value = supplier.id;
            document.getElementById('name').value = supplier.name;
            document.getElementById('email').value = supplier.email;
            document.getElementById('phone').value = supplier.phone;
            document.getElementById('address').value = supplier.address;
            document.getElementById('rating').value = supplier.rating;
            document.getElementById('active').checked = supplier.active;
            document.getElementById('supplierModal').classList.remove('hidden');
        } else {
            showError(result.error);
        }
    } catch (error) {
        showError('Failed to load supplier details');
    }
}

async function deleteSupplier(id) {
    if (!confirm('Are you sure you want to delete this supplier?')) return;
    
    try {
        const response = await fetch(`/api/suppliers/?id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();
        if (result.success) {
            loadSuppliers();
        } else {
            showError(result.error);
        }
    } catch (error) {
        showError('Failed to delete supplier');
    }
}

async function handleSubmit(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        rating: document.getElementById('rating').value,
        active: document.getElementById('active').checked
    };
    
    const id = document.getElementById('supplierId').value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `/api/suppliers/?id=${id}` : '/api/suppliers/';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        if (result.success) {
            closeModal();
            loadSuppliers();
        } else {
            showError(result.error);
        }
    } catch (error) {
        showError('Failed to save supplier');
    }
}

function closeModal() {
    document.getElementById('supplierModal').classList.add('hidden');
    document.getElementById('supplierForm').reset();
}

function showError(message) {
    alert(message);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
