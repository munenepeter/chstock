<?php
require_once __DIR__ . '/../../includes/header.php';
?>


<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Departments</h1>
            <p class="mt-2 text-sm text-gray-700">A list of all Departments in the system.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <?php if (in_array($_SESSION['user']['role'], ['admin'])): ?>
                <button type="button" id="addDepartmentBtn" class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto">
                    Add Department
                </button>
            <?php endif; ?>
        </div>
    </div>


    <!-- Search Box -->
    <div class="my-4">
        <div class="relative rounded-md shadow-sm">
            <input type="text"
                id="searchDepartment" placeholder="Search departments..."
                class="block w-full rounded-md border-gray-300 pl-4 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </div>



    <!-- Departments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="departmentsTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Departments will be loaded here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div id="departmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modalTitle">Add Department</h3>
            <form id="departmentForm">
                <input type="hidden" id="departmentId">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Department Name
                    </label>
                    <input type="text" id="name" name="name" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="code">
                        Department Code
                    </label>
                    <input type="text" id="code" name="code" required maxlength="10"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="active" name="active" class="form-checkbox" checked>
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                </div>
                <div class="flex justify-end">
                    <button type="button" class="mr-2 px-4 py-2 text-gray-500 hover:text-gray-700" onclick="closeModal()">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let departments = [];

    // Load departments on page load
    document.addEventListener('DOMContentLoaded', loadDepartments);

    // Search functionality
    document.getElementById('searchDepartment').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const filteredDepartments = departments.filter(dept =>
            dept.name.toLowerCase().includes(searchTerm) ||
            dept.code.toLowerCase().includes(searchTerm)
        );
        renderDepartments(filteredDepartments);
    });

    // Add department button click
    const addDepartmentBtn = document.getElementById('addDepartmentBtn');
    if (addDepartmentBtn) {
        addDepartmentBtn.addEventListener('click', () => {
            document.getElementById('modalTitle').textContent = 'Add Department';
            document.getElementById('departmentId').value = '';
            document.getElementById('departmentForm').reset();
            document.getElementById('departmentModal').classList.remove('hidden');
        });
    }

    // Form submission
    document.getElementById('departmentForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const departmentId = document.getElementById('departmentId').value;
        const data = {
            name: document.getElementById('name').value,
            code: document.getElementById('code').value,
            active: document.getElementById('active').checked
        };

        try {
            const response = await fetch(`/api/departments/${departmentId ? '?id=' + departmentId : ''}`, {
                method: departmentId ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                closeModal();
                loadDepartments();
                showAlert(result.message, 'success');
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to save department', 'error');
        }
    });

    async function loadDepartments() {
        try {
            const response = await fetch('/api/departments/');
            const result = await response.json();

            if (result.success) {
                departments = result.data;
                renderDepartments(departments);
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to load departments', 'error');
        }
    }

    function renderDepartments(deps) {
        const tbody = document.getElementById('departmentsTableBody');
        tbody.innerHTML = '';

        deps.forEach(dept => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">${dept.code}</td>
            <td class="px-6 py-4 whitespace-nowrap">${dept.name}</td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    ${dept.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                    ${dept.active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <?php if (in_array($_SESSION['user']['role'], ['admin'])): ?>
                <button onclick="editDepartment(${dept.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                <button onclick="deleteDepartment(${dept.id})" class="text-red-600 hover:text-red-900">Delete</button>
                <?php endif; ?>
            </td>
        `;
            tbody.appendChild(tr);
        });
    }

    async function editDepartment(id) {
        try {
            const dept = departments.find(d => d.id === id);
            if (!dept) return;

            document.getElementById('modalTitle').textContent = 'Edit Department';
            document.getElementById('departmentId').value = dept.id;
            document.getElementById('name').value = dept.name;
            document.getElementById('code').value = dept.code;
            document.getElementById('active').checked = dept.active;

            document.getElementById('departmentModal').classList.remove('hidden');
        } catch (error) {
            showAlert('Failed to load department details', 'error');
        }
    }

    async function deleteDepartment(id) {
        if (!confirm('Are you sure you want to delete this department?')) return;

        try {
            const response = await fetch(`/api/departments/?id=${id}`, {
                method: 'DELETE'
            });

            const result = await response.json();

            if (result.success) {
                loadDepartments();
                showAlert(result.message, 'success');
            } else {
                showAlert(result.error, 'error');
            }
        } catch (error) {
            showAlert('Failed to delete department', 'error');
        }
    }

    function closeModal() {
        document.getElementById('departmentModal').classList.add('hidden');
        document.getElementById('departmentForm').reset();
    }

    function showAlert(message, type = 'success') {
        // You can implement your preferred alert/notification system here
        alert(message);
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>