<?php
require 'db.php';
$company_id = $_SESSION['company_id'];

// Attendance Summary
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE company_id = ?");
$stmt->execute([$company_id]);
$attendance_total = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE company_id = ? AND time_out IS NULL");
$stmt->execute([$company_id]);
$currently_clocked_in = $stmt->fetchColumn();

// Finance Summary
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense
    FROM finance WHERE company_id = ?");
$stmt->execute([$company_id]);
$finance = $stmt->fetch();
$net_balance = $finance['total_income'] - $finance['total_expense'];

// Inventory Summary
$stmt = $pdo->prepare("SELECT COUNT(*) AS items, SUM(quantity) AS total_stock FROM inventory WHERE company_id = ?");
$stmt->execute([$company_id]);
$inventory = $stmt->fetch();

// Sales Summary
$stmt = $pdo->prepare("SELECT COUNT(*) AS orders, SUM(total_price) AS revenue FROM sales WHERE company_id = ?");
$stmt->execute([$company_id]);
$sales = $stmt->fetch();
?>

<h2>📊 Business Intelligence Dashboard</h2>

<table border="1" cellpadding="10" cellspacing="0">
    <tr><th>Module</th><th>Summary</th></tr>
    <tr>
        <td>Attendance</td>
        <td><?= $attendance_total ?> records<br><?= $currently_clocked_in ?> currently clocked in</td>
    </tr>
    <tr>
        <td>Finance</td>
        <td>
            Income: ₱<?= number_format($finance['total_income'], 2) ?><br>
            Expense: ₱<?= number_format($finance['total_expense'], 2) ?><br>
            Net Balance: <strong>₱<?= number_format($net_balance, 2) ?></strong>
        </td>
    </tr>
    <tr>
        <td>Inventory</td>
        <td><?= $inventory['items'] ?> items<br>Total stock: <?= $inventory['total_stock'] ?></td>
    </tr>
    <tr>
        <td>Sales</td>
        <td><?= $sales['orders'] ?> orders<br>Total revenue: ₱<?= number_format($sales['revenue'], 2) ?></td>
    </tr>
</table>

<hr>

<h3>📈 Monthly Sales Chart (Last 6 Months)</h3>
<?php
// Prepare monthly sales data
$stmt = $pdo->prepare("SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(total_price) AS revenue 
                       FROM sales WHERE company_id = ? 
                       GROUP BY month ORDER BY month DESC LIMIT 6");
$stmt->execute([$company_id]);
$monthly_sales = $stmt->fetchAll();
?>

<table border="1" cellpadding="8" cellspacing="0">
    <tr><th>Month</th><th>Revenue</th></tr>
    <?php foreach (array_reverse($monthly_sales) as $row): ?>
    <tr>
        <td><?= $row['month'] ?></td>
        <td>₱<?= number_format($row['revenue'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<p style="margin-top:20px;">Let me know if you want to add charts, filters, or export options next.</p>
