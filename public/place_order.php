<?php
include_once '../includes/header.php';

// Fetch stock items
$items = $db->query("SELECT id, item_name FROM stock")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Place Order</h2>

<form method="POST" action="../controllers/OrderController.php">
    <select name="item_id" required>
        <?php foreach ($items as $item): ?>
            <option value="<?= $item['id'] ?>"><?= $item['item_name'] ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="quantity" required placeholder="Quantity">
    <input type="text" name="customer" required placeholder="Customer Name">
    <button type="submit" name="place_order">Place Order</button>
</form>

<?php include_once '../includes/footer.php'; ?>