<?php
require_once __DIR__ . '/../../../includes/header.php';
require_once __DIR__ . '/../../../controllers/acquisition.php';

$controller = new AcquisitionController($db);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Requisition Orders</h1>
        <div class="flex items-center space-x-4">
            <select id="statusFilter" class="rounded-md border-slate-300 text-sm">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
            </select>
            <button type="button" id="createROBtn"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i> New Requisition
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200" id="requisitionsTable">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">RO Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Number of Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <!-- Filled via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create RO Modal -->
<div id="createROModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-slate-900">Create Requisition Order</h3>
                <button type="button" class="text-slate-400 hover:text-slate-500" id="closeCreateROModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="px-6 py-4">
            <form id="createROForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Department</label>
                    <select name="department_id" class="w-full rounded-md border-slate-300 text-sm" required>
                        <option value="">Select Department</option>
                        <!-- Filled via AJAX -->
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Items</label>
                    <div id="itemsContainer" class="space-y-3">
                        <div class="item-row grid grid-cols-12 gap-4">
                            <div class="col-span-5">
                                <select name="items[0][item_id]" class="w-full rounded-md border-slate-300 text-sm item-select" required>
                                    <option value="">Select Item</option>
                                    <!-- Filled via AJAX -->
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][quantity]"
                                    class="w-full rounded-md border-slate-300 text-sm item-quantity"
                                    placeholder="Quantity" required min="1">
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][unit_price]"
                                    class="w-full rounded-md border-slate-300 text-sm item-price"
                                    placeholder="Unit Price" required min="0" step="0.01" readonly>
                            </div>
                            <div class="col-span-1">
                                <button type="button" class="text-red-600 hover:text-red-700 remove-item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="endpoint" value="requisitions">
                    <button type="button" id="addItem"
                        class="mt-3 inline-flex items-center px-3 py-2 border border-slate-300 shadow-sm text-sm leading-4 font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50">
                        <i class="fas fa-plus mr-2"></i> Add Item
                    </button>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 bg-slate-50 rounded-b-lg flex justify-end space-x-3">
            <button type="button" id="cancelCreateRO"
                class="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 hover:bg-slate-50">
                Cancel
            </button>
            <button type="button" id="submitRO"
                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700">
                Create Requisition
            </button>
        </div>
    </div>
</div>

<!-- View RO Modal -->
<div id="viewROModal" class="fixed inset-0 bg-slate-500 bg-opacity-75 hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-slate-900">View Requisition Order</h3>
                <button type="button" class="text-slate-400 hover:text-slate-500" id="closeViewROModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-sm font-medium text-slate-500">RO Number:</span>
                    <span class="ml-2 text-sm text-slate-900" id="viewRONumber"></span>
                </div>
                <div>
                    <span class="text-sm font-medium text-slate-500">Department:</span>
                    <span class="ml-2 text-sm text-slate-900" id="viewDepartment"></span>
                </div>
                <div>
                    <span class="text-sm font-medium text-slate-500">Status:</span>
                    <span class="ml-2 text-sm text-slate-900" id="viewStatus"></span>
                </div>
                <div>
                    <span class="text-sm font-medium text-slate-500">Created At:</span>
                    <span class="ml-2 text-sm text-slate-900" id="viewCreatedAt"></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit of Issue</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Total Price</th>
                        </tr>
                    </thead>
                    <tbody id="viewItems" class="bg-white divide-y divide-slate-200">
                        <!-- Filled via AJAX -->
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-slate-500">Total Amount:</td>
                            <td class="px-6 py-3 text-left text-sm font-medium text-slate-900" id="viewTotalAmount"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        // Modal controls
        $('#createROBtn').click(() => $('#createROModal').removeClass('hidden').addClass('flex'));
        $('#closeCreateROModal, #cancelCreateRO').click(() => $('#createROModal').removeClass('flex').addClass('hidden'));
        $('#closeViewROModal').click(() => $('#viewROModal').removeClass('flex').addClass('hidden'));

        // Load departments
        $.get('/api/departments/', function(response) {
            if (response.success) {
                const options = response.data.map(dept =>
                    `<option value="${dept.id}">${dept.name}</option>`
                ).join('');
                $('select[name="department_id"]').append(options);
            }
        });

        // Load items
        function loadItems() {
            $.get('/api/items/', function(response) {
                if (response.success) {
                    const options = response.data.map(item =>
                        `<option value="${item.id}" data-price="${item.unit_price}">${item.name} (${item.unit_of_issue})</option>`
                    ).join('');
                    $('.item-select').each(function() {
                        const currentVal = $(this).val();
                        $(this).html('<option value="">Select Item</option>' + options);
                        $(this).val(currentVal);
                    });
                }
            });
        }
        loadItems();

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

        // Load requisitions
        // function loadRequisitions() {
        //     const status = $('#statusFilter').val();
        //     $.get('/api/acquisition/', {
        //         status,
        //         endpoint: 'requisitions'
        //     }, function(response) {
        //         if (response.success) {
        //             const rows = response.data.map(ro => {
        //                     const getStatusStyle = (status) => {
        //                         switch (status.toLowerCase()) {
        //                             case 'pending':
        //                                 return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        //                             case 'completed':
        //                                 return 'bg-green-100 text-green-800 border-green-200';
        //                             default:
        //                                 return 'bg-gray-100 text-gray-800 border-gray-200';
        //                         }
        //                     };

        //                     return `
        //             <tr class="hover:bg-slate-50">
        //                 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.ro_number}</td>
        //                 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.department_name}</td>
        //                 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${parseFloat(ro.total_amount).toFixed(2)}</td>
        //                    <td class="px-6 py-4 whitespace-nowrap">
        //             <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium border ${getStatusStyle(ro.status)}">
        //                 <span class="h-1.5 w-1.5 rounded-full ${ro.status === 'pending' ? 'bg-yellow-400' : 'bg-green-400'}"></span>
        //                 ${ro.status.charAt(0).toUpperCase() + ro.status.slice(1).toLowerCase()}
        //             </span>
        //         </td>
        //                 <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${new Date(ro.created_at).toLocaleString()}</td>
        //                 <td class="px-6 py-4 whitespace-nowrap text-sm">
        //                     <button type="button" class="text-blue-600 hover:text-blue-700 view-ro" data-ro="${ro.ro_number}">
        //                         <i class="fas fa-eye"></i>
        //                     </button>
        //                 </td>
        //             </tr>
        //         `).join('');

        //                 $('#requisitionsTable tbody').html(rows);
        //             }

        //         }

        //     });
        // }

        function loadRequisitions() {
            const status = $('#statusFilter').val();

            // Function moved outside .map() for better efficiency
            const getStatusStyle = (status) => {
                switch (status?.toLowerCase()) {
                    case 'pending':
                        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    case 'completed':
                        return 'bg-green-100 text-green-800 border-green-200';
                    default:
                        return 'bg-gray-100 text-gray-800 border-gray-200';
                }
            };

            $.get('/api/acquisition/', {
                status,
                endpoint: 'requisitions'
            }, function(response) {
                if (response.success) {
                    const rows = response.data.map(ro => `
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.ro_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.department_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.no_of_items}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">KES ${parseFloat(ro.total_amount).toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-xs font-medium border ${getStatusStyle(ro.status)}">
                            <span class="h-1.5 w-1.5 rounded-full ${ro.status?.toLowerCase() === 'pending' ? 'bg-yellow-400' : 'bg-green-400'}"></span>
                            ${ro.status ? ro.status.charAt(0).toUpperCase() + ro.status.slice(1) : 'Unknown'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${ro.created_at ? new Date(ro.created_at).toLocaleString() : 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button type="button" class="text-blue-600 hover:text-blue-700 view-ro" data-ro="${ro.ro_number}">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

                    $('#requisitionsTable tbody').html(rows);
                } else {
                    showToast('error', 'Failed to load requisitions');
                    $('#requisitionsTable tbody').html(`<tr><td colspan="6" class="text-center text-red-500 py-4">Failed to load requisitions</td></tr>`);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                $('#requisitionsTable tbody').html(`<tr><td colspan="6" class="text-center text-red-500 py-4">Error loading requisitions</td></tr>`);
            });
        }

        loadRequisitions();

        // Status filter change
        $('#statusFilter').change(loadRequisitions);

        // Add item row
        $('#addItem').click(function() {
            const index = $('.item-row').length;
            const newRow = `
            <div class="item-row grid grid-cols-12 gap-4">
                <div class="col-span-5">
                    <select name="items[${index}][item_id]" class="w-full rounded-md border-slate-300 text-sm item-select" required>
                        <option value="">Select Item</option>
                    </select>
                </div>
                <div class="col-span-3">
                    <input type="number" name="items[${index}][quantity]" 
                           class="w-full rounded-md border-slate-300 text-sm item-quantity" 
                           placeholder="Quantity" required min="1">
                </div>
                <div class="col-span-3">
                    <input type="number" name="items[${index}][unit_price]" 
                           class="w-full rounded-md border-slate-300 text-sm item-price" 
                           placeholder="Unit Price" required min="0" step="0.01" readonly>
                </div>
                <div class="col-span-1">
                    <button type="button" class="text-red-600 hover:text-red-700 remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
            $('#itemsContainer').append(newRow);
            loadItems();
        });

        // Remove item row
        $(document).on('click', '.remove-item', function() {
            if ($('.item-row').length > 1) {
                $(this).closest('.item-row').remove();
            }
        });

        // Item selection change
        $(document).on('change', '.item-select', function() {
            const price = $(this).find(':selected').data('price') || '';
            $(this).closest('.item-row').find('.item-price').val(price);
        });

        // Create RO
        $('#submitRO').click(function() {
            const form = $('#createROForm');
            if (!form[0].checkValidity()) {
                form[0].reportValidity();
                return;
            }

            const formData = {
                endpoint: 'requisitions',
                department_id: form.find('[name="department_id"]').val(),
                items: []
            };

            $('.item-row').each(function() {
                formData.items.push({
                    item_id: $(this).find('.item-select').val(),
                    quantity: parseInt($(this).find('.item-quantity').val()),
                    unit_price: parseFloat($(this).find('.item-price').val())
                });
            });


            $.post('/api/acquisition/', formData, function(response) {
                if (response.success) {
                    $('#createROModal').removeClass('flex').addClass('hidden');
                    form[0].reset();
                    $('.item-row:not(:first)').remove();
                    loadRequisitions();

                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            });
        });

        // View RO
        $(document).on('click', '.view-ro', function() {
            const roNumber = $(this).data('ro');
            $.get('/api/acquisition/', {
                endpoint: 'view',
                roNumber: roNumber
            }, function(response) {
                if (response.success) {
                    const ro = response.data.requisition;
                    const items = response.data.items;

                    $('#viewRONumber').text(ro.ro_number);
                    $('#viewDepartment').text(ro.department_name);
                    $('#viewStatus').text(ro.status.toUpperCase());
                    $('#viewCreatedAt').text(new Date(ro.created_at).toLocaleString());

                    const rows = items.map(item => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${item.item_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${item.unit_of_issue}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${item.quantity}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${parseFloat(item.unit_price).toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">${parseFloat(item.total_price).toFixed(2)}</td>
                    </tr>
                `).join('');
                    $('#viewItems').html(rows);
                    $('#viewTotalAmount').text(parseFloat(ro.total_amount).toFixed(2));

                    $('#viewROModal').removeClass('hidden').addClass('flex');
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>