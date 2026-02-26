<?php
session_start();
require 'db.php';

// Check authorization
if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['head_sales', 'admin'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Fetch sales records from today
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT 
        s.id,
        s.product_name,
        s.quantity,
        s.price,
        s.total_price,
        s.date_sold,
        f.id as finance_id,
        f.amount as finance_amount,
        f.description,
        f.date as finance_date
    FROM sales s
    LEFT JOIN finance f ON f.company_id = s.company_id AND f.description LIKE CONCAT('%Receipt%') AND f.date = s.date_sold
    WHERE s.company_id = ? AND s.date_sold >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY s.date_sold DESC, s.id DESC
");
$stmt->execute([$company_id]);
$transactions = $stmt->fetchAll();

// Get summary stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(total_price) as total_sales,
        COUNT(DISTINCT date_sold) as days_count
    FROM sales
    WHERE company_id = ? AND date_sold >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
");
$stmt->execute([$company_id]);
$stats = $stmt->fetch();

$total_trans = $stats['total_transactions'] ?? 0;
$total_sales_amount = $stats['total_sales'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>POS Transactions History</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light);
            color: var(--text-primary);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: #64748b;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .stat-card.success {
            border-left-color: var(--success);
        }

        .stat-card.warning {
            border-left-color: var(--warning);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card.success .stat-value {
            color: var(--success);
        }

        .stat-card.warning .stat-value {
            color: var(--warning);
        }

        .transactions-section {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .section-header {
            padding: 20px;
            border-bottom: 2px solid var(--border);
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: var(--light);
            border-bottom: 2px solid var(--border);
        }

        th {
            padding: 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background-color 0.2s ease;
        }

        tbody tr:hover {
            background-color: var(--light);
        }

        td {
            padding: 15px;
            font-size: 14px;
        }

        .product-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .quantity-badge {
            background-color: var(--primary);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .price-amount {
            font-weight: 600;
            color: var(--success);
        }

        .date-badge {
            background-color: #dbeafe;
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filters {
            display: flex;
            gap: 10px;
            padding: 20px;
            background: var(--light);
            border-bottom: 1px solid var(--border);
        }

        .filters input, .filters select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 14px;
        }

        @media print {
            body {
                background: white;
            }

            .header, .action-buttons, .filters {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-receipt"></i> Transaction History</h1>
            <div>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="pos_system.php" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="fas fa-arrow-left"></i> Back to POS
                </a>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value"><?php echo $total_trans; ?></div>
            </div>
            <div class="stat-card success">
                <div class="stat-label">Total Sales (30 Days)</div>
                <div class="stat-value">₱<?php echo number_format($total_sales_amount, 2); ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-label">Average per Transaction</div>
                <div class="stat-value">₱<?php echo $total_trans > 0 ? number_format($total_sales_amount / $total_trans, 2) : '0.00'; ?></div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="transactions-section">
            <div class="section-header">
                <i class="fas fa-list"></i> Recent Transactions (Last 30 Days)
            </div>
            
            <?php if (empty($transactions)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                    <p>No transactions found</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td>
                                        <span class="date-badge"><?php echo date('M d, Y', strtotime($trans['date_sold'])); ?></span>
                                    </td>
                                    <td><span class="product-name"><?php echo htmlspecialchars($trans['product_name']); ?></span></td>
                                    <td><span class="quantity-badge"><?php echo $trans['quantity']; ?> units</span></td>
                                    <td>₱<?php echo number_format($trans['price'], 2); ?></td>
                                    <td><span class="price-amount">₱<?php echo number_format($trans['total_price'], 2); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
