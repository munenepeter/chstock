<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../controllers/reports.php';

$reportsController = new ReportsController($db);

// Get report type and date range if provided
$type = $_GET['type'] ?? 'stock_levels';
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

// Get report data based on type
$reportData = [];
$reportTitle = '';

switch ($type) {
    case 'stock_levels':
        $reportData = $reportsController->getStockLevelsReport();
        $reportTitle = 'Stock Levels Report';
        break;
    case 'transactions':
        $reportData = $reportsController->getTransactionsReport($startDate, $endDate);
        $reportTitle = 'Stock Transactions Report';
        if ($startDate && $endDate) {
            $reportTitle .= " ($startDate to $endDate)";
        }
        break;
    case 'low_stock':
        $reportData = $reportsController->getLowStockReport();
        $reportTitle = 'Low Stock Items Report';
        break;
    case 'expiring':
        $reportData = $reportsController->getExpiringItemsReport();
        $reportTitle = 'Expiring Items Report';
        break;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Reports</h1>
    </div>

    <!-- Report Types -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Stock Levels Report -->
        <a href="?type=stock_levels" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow <?php echo $type === 'stock_levels' ? 'ring-2 ring-blue-500' : ''; ?>">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-slate-800">Stock Levels</h3>
                </div>
            </div>
            <p class="text-slate-600">Current inventory levels for all items</p>
        </a>

        <!-- Transactions Report -->
        <div class="bg-white rounded-lg shadow p-6 <?php echo $type === 'transactions' ? 'ring-2 ring-blue-500' : ''; ?>">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-exchange-alt fa-2x"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-slate-800">Transactions</h3>
                </div>
            </div>
            <p class="text-slate-600 mb-4">Stock movement history report</p>
            <button onclick="openDateRangeModal()" 
                    class="text-blue-600 hover:text-blue-800">
                Select Date Range
            </button>
        </div>

        <!-- Low Stock Report -->
        <a href="?type=low_stock" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow <?php echo $type === 'low_stock' ? 'ring-2 ring-blue-500' : ''; ?>">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-slate-800">Low Stock</h3>
                </div>
            </div>
            <p class="text-slate-600">Items below reorder level</p>
        </a>

        <!-- Expiring Items Report -->
        <a href="?type=expiring" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow <?php echo $type === 'expiring' ? 'ring-2 ring-blue-500' : ''; ?>">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                        <i class="fas fa-calendar-times fa-2x"></i>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-slate-800">Expiring Items</h3>
                </div>
            </div>
            <p class="text-slate-600">Items expiring within 30 days</p>
        </a>
    </div>

    <!-- Report Content -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-slate-800"><?php echo $reportTitle; ?></h2>
            <a href="/api/reports/generate.php?type=<?php echo $type; ?><?php echo ($startDate && $endDate) ? "&start_date=$startDate&end_date=$endDate" : ''; ?>" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="fas fa-download mr-2"></i>
                Download PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <?php if ($type === 'stock_levels'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Stock Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reorder Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expiry Date</th>
                        <?php elseif ($type === 'transactions'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">From/To</th>
                        <?php elseif ($type === 'low_stock'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Current Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Reorder Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                        <?php elseif ($type === 'expiring'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Unit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Stock Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Supplier</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php foreach ($reportData as $row): ?>
                        <tr class="hover:bg-slate-50">
                            <?php if ($type === 'stock_levels'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['stock_level']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['reorder_level']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['expiry_date']; ?></td>
                            <?php elseif ($type === 'transactions'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo date('Y-m-d H:i', strtotime($row['transaction_date'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $row['transaction_type'] === 'received' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($row['transaction_type']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['quantity']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    <?php echo htmlspecialchars($row['transaction_type'] === 'received' ? $row['received_from'] : $row['issued_to']); ?>
                                </td>
                            <?php elseif ($type === 'low_stock'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['stock_level']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['reorder_level']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <?php elseif ($type === 'expiring'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['unit']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['stock_level']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo $row['expiry_date']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Date Range Modal -->
    <div id="dateRangeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Select Date Range</h3>
            <form id="dateRangeForm" action="" method="GET">
                <input type="hidden" name="type" value="transactions">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" required
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Date</label>
                    <input type="date" name="end_date" required
                           class="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDateRangeModal()"
                            class="px-4 py-2 bg-slate-100 text-slate-700 rounded hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        View Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDateRangeModal() {
    document.getElementById('dateRangeModal').classList.remove('hidden');
    document.getElementById('dateRangeModal').classList.add('flex');
}

function closeDateRangeModal() {
    document.getElementById('dateRangeModal').classList.add('hidden');
    document.getElementById('dateRangeModal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('dateRangeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDateRangeModal();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
