<?php 
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../controllers/stocks.php';
require_once __DIR__ . '/../controllers/orders.php';

$stockController = new StockController();
$orderController = new OrderController();

// Get statistics
$stats = [
    'total_stock' => $db->query("SELECT COUNT(*) FROM stock_items")->fetchColumn(),
    'low_stock' => $db->query("SELECT COUNT(*) FROM stock_items WHERE stock_level <= reorder_level")->fetchColumn(),
    'expiring_soon' => $db->query("SELECT COUNT(*) FROM stock_items WHERE expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY)")->fetchColumn(),

    // 'expiring_soon' => $db->query("SELECT COUNT(*) FROM stock_items WHERE expiry_date <= DATE('now', '+30 days')")->fetchColumn(),
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'total_suppliers' => $db->query("SELECT COUNT(*) FROM suppliers")->fetchColumn(),
    'recent_transactions' => $db->query("
        SELECT COUNT(*) FROM stock_transactions 
        WHERE transaction_date >= 
        DATE_ADD(NOW(), INTERVAL -7 DAY)
    ")->fetchColumn(),
    'total_value' => $db->query("
        SELECT COALESCE(SUM(stock_level), 0) as total 
        FROM stock_items
    ")->fetchColumn(),
    'alerts' => $db->query("
        SELECT COUNT(*) FROM alerts 
        WHERE resolved = 0
    ")->fetchColumn()
];

// Get recent activities
$recentActivities = $db->query("
    SELECT 
        'transaction' as type,
        st.transaction_type,
        st.quantity,
        si.name as item_name,
        st.transaction_date as date,
        CASE 
            WHEN st.transaction_type = 'received' THEN st.received_from
            ELSE st.issued_to
        END as party
    FROM stock_transactions st
    JOIN stock_items si ON st.stock_id = si.id
    UNION ALL
    SELECT 
        'order' as type,
        o.status as transaction_type,
        NULL as quantity,
        s.name as item_name,
        o.order_date as date,
        o.order_number as party
    FROM orders o
    JOIN suppliers s ON o.supplier_id = s.id
    ORDER BY date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Dashboard</h1>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Stock -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-boxes fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Stock Items</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['total_stock']; ?></p>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Low Stock Items</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['low_stock']; ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Pending Orders</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['pending_orders']; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Suppliers -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-truck fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Suppliers</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['total_suppliers']; ?></p>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-exchange-alt fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Recent Transactions</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['recent_transactions']; ?></p>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <i class="fas fa-calendar-times fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Expiring Soon</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['expiring_soon']; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Stock Value -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <i class="fas fa-cubes fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Total Items in Stock</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo number_format($stats['total_value']); ?></p>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-bell fa-2x"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-slate-500">Active Alerts</p>
                    <p class="text-xl font-semibold text-slate-700"><?php echo $stats['alerts']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-slate-800 mb-4">Recent Activity</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Item/Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    <?php foreach ($recentActivities as $activity): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                            <?php echo date('M j, Y H:i', strtotime($activity['date'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php 
                                $type = $activity['transaction_type'];
                                echo match($type) {
                                    'received' => 'bg-green-100 text-green-800',
                                    'issued' => 'bg-blue-100 text-blue-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-slate-100 text-slate-800'
                                };
                                ?>">
                                <?php echo ucfirst($type); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                            <?php echo htmlspecialchars($activity['item_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            <?php 
                            if ($activity['type'] === 'transaction') {
                                echo $activity['quantity'] . ' ' . 
                                     ($activity['transaction_type'] === 'received' ? 'from ' : 'to ') . 
                                     $activity['party'];
                            } else {
                                echo "Order #" . $activity['party'];
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
