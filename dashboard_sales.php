<?php
session_start();
require 'db.php';

if (!function_exists('logActivity')) {
    function logActivity(PDO $pdo, int $companyId, ?int $userId, string $userRole, string $module, string $action, ?string $description = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $stmt = $pdo->prepare('INSERT INTO activity_logs (company_id, user_id, user_role, module, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $companyId,
            $userId,
            $userRole,
            $module,
            $action,
            $description,
            $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? null)
        ]);
    }
}

if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? null) !== 'head_sales') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$company_id = (int)($_SESSION['company_id'] ?? 0);
$sales_section = $_GET['sales_section'] ?? 'records';
$sales_section = in_array($sales_section, ['records', 'crm'], true) ? $sales_section : 'records';

$sales_message = null;
$customer_message = null;
$sales_flash = $_SESSION['sales_flash'] ?? null;
$customer_flash = $_SESSION['customer_flash'] ?? null;
$customer_error = $_SESSION['customer_error'] ?? null;
unset($_SESSION['customer_error']);
unset($_SESSION['sales_flash'], $_SESSION['customer_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_sale'])) {
        $sales_section = 'records';
        $inventory_id = (int)($_POST['inventory_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $date_sold = $_POST['date_sold'] ?? null;

        if ($inventory_id <= 0 || $quantity <= 0 || $price <= 0 || empty($date_sold)) {
            $sales_message = 'Please select a product with valid quantity, price, and sale date.';
        } else {
            try {
                $pdo->beginTransaction();

                $inventoryStmt = $pdo->prepare('SELECT id, item_name, quantity, reorder_level, sku, supplier_id FROM inventory WHERE id = ? AND company_id = ? FOR UPDATE');
                $inventoryStmt->execute([$inventory_id, $company_id]);
                $item = $inventoryStmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    throw new Exception('Selected inventory item not found.');
                }

                if ((int)$item['quantity'] < $quantity) {
                    throw new Exception('Insufficient stock. Available: ' . (int)$item['quantity']);
                }

                $newQty = (int)$item['quantity'] - $quantity;
                $updateInventory = $pdo->prepare('UPDATE inventory SET quantity = ? WHERE id = ? AND company_id = ?');
                $updateInventory->execute([$newQty, $inventory_id, $company_id]);

                $insertSale = $pdo->prepare('INSERT INTO sales (company_id, product, quantity, price, date_sold) VALUES (?, ?, ?, ?, ?)');
                $insertSale->execute([$company_id, $item['item_name'], $quantity, $price, $date_sold]);

                $saleAmount = $price * $quantity;
                $insertFinance = $pdo->prepare('INSERT INTO finance (company_id, amount, type, description, date) VALUES (?, ?, ?, ?, ?)');
                $insertFinance->execute([
                    $company_id,
                    $saleAmount,
                    'income',
                    'Sale: ' . $item['item_name'] . ' (Qty: ' . $quantity . ')',
                    $date_sold
                ]);

                logActivity(
                    $pdo,
                    $company_id,
                    $_SESSION['user_id'] ?? null,
                    $_SESSION['role'] ?? 'head_sales',
                    'sales',
                    'add_sale',
                    'Sold ' . $quantity . ' of ' . $item['item_name'] . ' for PHP ' . number_format($saleAmount, 2)
                );

                $checkStmt = $pdo->prepare('SELECT id, item_name, quantity, reorder_level, sku, supplier_id FROM inventory WHERE id = ? AND company_id = ?');
                $checkStmt->execute([$inventory_id, $company_id]);
                $updatedItem = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($updatedItem && isset($updatedItem['reorder_level']) && $updatedItem['reorder_level'] !== null && (int)$updatedItem['quantity'] <= (int)$updatedItem['reorder_level']) {
                    $vendorEmail = null;
                    if (!empty($updatedItem['supplier_id'])) {
                        $vendorStmt = $pdo->prepare('SELECT email FROM vendors WHERE id = ? AND company_id = ? LIMIT 1');
                        $vendorStmt->execute([$updatedItem['supplier_id'], $company_id]);
                        $vendor = $vendorStmt->fetch(PDO::FETCH_ASSOC);
                        if ($vendor && !empty($vendor['email'])) {
                            $vendorEmail = $vendor['email'];
                        }
                    }

                    if ($vendorEmail) {
                        require_once __DIR__ . '/send_stock_alert.php';
                        sendStockAlert(
                            $vendorEmail,
                            $updatedItem['item_name'],
                            (int)$updatedItem['quantity'],
                            (int)$updatedItem['reorder_level'],
                            $updatedItem['sku'] ?? ''
                        );
                    } else {
                        error_log('Stock alert skipped for item ' . $updatedItem['item_name'] . ' (no vendor email found).');
                    }
                }

                $pdo->commit();
                $_SESSION['sales_flash'] = 'Sale recorded successfully.';
                header('Location: dashboard_sales.php?sales_section=records');
                exit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $sales_message = 'Sale failed: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['edit_customer'])) {
        $sales_section = 'crm';
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_address = trim($_POST['customer_address'] ?? '');

        if ($customer_id <= 0 || $customer_name === '') {
            $_SESSION['customer_error'] = 'Customer name is required to update.';
        } else {
            $customerCheck = $pdo->prepare('SELECT customer_id FROM customers WHERE customer_id = ? AND company_id = ? LIMIT 1');
            $customerCheck->execute([$customer_id, $company_id]);

            if (!$customerCheck->fetchColumn()) {
                $_SESSION['customer_error'] = 'Customer not found or already removed.';
            } else {
                $updateCustomer = $pdo->prepare('UPDATE customers SET name = ?, email = ?, phone = ?, address = ? WHERE customer_id = ? AND company_id = ?');
                $updateCustomer->execute([$customer_name, $customer_email, $customer_phone, $customer_address, $customer_id, $company_id]);

                logActivity(
                    $pdo,
                    $company_id,
                    $_SESSION['user_id'] ?? null,
                    $_SESSION['role'] ?? 'head_sales',
                    'crm',
                    'edit_customer',
                    'Updated customer ' . $customer_name . ' (ID: ' . $customer_id . ')'
                );
                $_SESSION['customer_flash'] = 'Customer updated successfully.';
            }
        }

        header('Location: dashboard_sales.php?sales_section=crm');
        exit();
    }

    if (isset($_POST['add_customer'])) {
        $sales_section = 'crm';
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_address = trim($_POST['customer_address'] ?? '');

        if ($customer_name === '') {
            $customer_message = 'Customer name is required.';
        } else {
            $insertCustomer = $pdo->prepare('INSERT INTO customers (company_id, name, email, phone, address) VALUES (?, ?, ?, ?, ?)');
            $insertCustomer->execute([$company_id, $customer_name, $customer_email, $customer_phone, $customer_address]);

            logActivity(
                $pdo,
                $company_id,
                $_SESSION['user_id'] ?? null,
                $_SESSION['role'] ?? 'head_sales',
                'crm',
                'add_customer',
                'Added customer ' . $customer_name
            );

            $_SESSION['customer_flash'] = 'Customer added successfully.';
            header('Location: dashboard_sales.php?sales_section=crm');
            exit();
        }
    }
}

if (isset($_GET['delete_sale'])) {
    $sales_section = 'records';
    $delete_id = (int)$_GET['delete_sale'];
    $stmt = $pdo->prepare('DELETE FROM sales WHERE id = ? AND company_id = ?');
    $stmt->execute([$delete_id, $company_id]);
    logActivity($pdo, $company_id, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? 'head_sales', 'sales', 'delete_sale', 'Deleted sale ID: ' . $delete_id);
    $_SESSION['sales_flash'] = 'Sale deleted.';
    header('Location: dashboard_sales.php?sales_section=records');
    exit();
}

if (isset($_GET['delete_customer'])) {
    $sales_section = 'crm';
    $customer_id = (int)$_GET['delete_customer'];
    $stmt = $pdo->prepare('DELETE FROM customers WHERE customer_id = ? AND company_id = ?');
    $stmt->execute([$customer_id, $company_id]);
    logActivity($pdo, $company_id, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? 'head_sales', 'crm', 'delete_customer', 'Deleted customer ID: ' . $customer_id);
    $_SESSION['customer_flash'] = 'Customer deleted.';
    header('Location: dashboard_sales.php?sales_section=crm');
    exit();
}

$salesStmt = $pdo->prepare('SELECT id, product, quantity, price, date_sold FROM sales WHERE company_id = ? ORDER BY date_sold DESC');
$salesStmt->execute([$company_id]);
$sales_data = $salesStmt->fetchAll(PDO::FETCH_ASSOC);
$sales_count = count($sales_data);

$revenueStmt = $pdo->prepare('SELECT COALESCE(SUM(quantity * price), 0) FROM sales WHERE company_id = ?');
$revenueStmt->execute([$company_id]);
$revenue = (float)$revenueStmt->fetchColumn();

$inventoryStmt = $pdo->prepare('SELECT id, item_name, quantity FROM inventory WHERE company_id = ? ORDER BY item_name ASC');
$inventoryStmt->execute([$company_id]);
$inventory_items = $inventoryStmt->fetchAll(PDO::FETCH_ASSOC);
$inventory_lookup = [];
foreach ($inventory_items as $item) {
    $inventory_lookup[(int)$item['id']] = $item['item_name'];
}

$customerStmt = $pdo->prepare('SELECT customer_id, name, email, phone, address, created_at FROM customers WHERE company_id = ? ORDER BY created_at DESC');
$customerStmt->execute([$company_id]);
$customers = $customerStmt->fetchAll(PDO::FETCH_ASSOC);
$customer_count = count($customers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sales Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #f0f4f8;
            --bg-secondary: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-modal: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --success-bg: #d1fae5;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --sidebar-width: 240px;
            --transition: all 0.2s ease;
            --radius: 0.5rem;
            --radius-sm: 0.25rem;
            --radius-lg: 0.75rem;
        }
        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: #334155;
            --border-light: #1e293b;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
            --shadow-modal: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #34d399;
            --success-bg: rgba(16, 185, 129, 0.2);
            --danger: #f87171;
            --danger-bg: rgba(239, 68, 68, 0.2);
            --warning: #fbbf24;
            --info: #7dd3fc;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            transition: var(--transition);
        }
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: var(--shadow-card);
            border-right: 1px solid var(--border-color);
        }
        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .sidebar-logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .sidebar-user {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }
        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 0.75rem 0.25rem;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.125rem 0;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: var(--radius-sm);
            gap: 0.75rem;
            transition: var(--transition);
            font-weight: 500;
        }
        .nav-item:hover {
            background: var(--border-light);
            color: var(--text-primary);
        }
        .nav-item.active {
            background: var(--primary);
            color: #fff;
        }
        .nav-icon {
            width: 1.5rem;
            text-align: center;
        }
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .logout-btn {
            width: 100%;
            background: var(--danger);
            color: #fff;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        header {
            background: var(--bg-secondary);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
            position: sticky;
            top: 0;
            z-index: 900;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .header-greeting {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .header-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .content {
            padding: 2rem;
            flex: 1;
            background: var(--bg-primary);
        }
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
            margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stats-summary {
            display: flex;
            gap: 1.25rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .stat-item-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
        }
        .stat-item-value {
            font-size: 1.25rem;
            font-weight: 700;
        }
        .card-badge {
            background: var(--primary);
            color: #fff;
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.85rem;
            font-weight: 600;
        }
        .table-container {
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
        }
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        .data-table tbody tr:hover {
            background: var(--border-light);
        }
        .action-btn,
        .edit-btn {
            padding: 0.45rem 0.9rem;
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: var(--transition);
        }
        .action-btn {
            background: var(--danger);
            color: #fff;
        }
        .action-btn:hover {
            background: #dc2626;
        }
        .edit-btn {
            background: var(--primary);
            color: #fff;
        }
        .edit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
        }
        .btn-secondary {
            background: var(--border-color);
            color: var(--text-primary);
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
        }
        .btn-secondary:hover {
            background: var(--text-muted);
            color: #fff;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1500;
            backdrop-filter: blur(2px);
            padding: 1rem;
        }
        .modal-box {
            background: var(--bg-secondary);
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-modal);
            border: 1px solid var(--border-color);
        }
        .modal-header {
            margin-bottom: 1rem;
        }
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .form-group {
            margin-bottom: 1.1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.65rem 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        #deleteModal .modal-box {
            text-align: center;
            max-width: 420px;
        }
        #deleteModal p {
            margin: 1rem 0;
            color: var(--text-secondary);
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-logo span,
            .sidebar-user,
            .nav-section-title,
            .nav-item span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
            }
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            .content {
                padding: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .modal-actions {
                flex-direction: column;
            }
            .modal-actions button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span><?= htmlspecialchars($_SESSION['company'] ?? '') ?></span>
            </div>
            <div class="sidebar-user">Code: <?= htmlspecialchars($_SESSION['company_code'] ?? '') ?></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="dashboard_sales.php?sales_section=records" class="nav-item <?= $sales_section === 'records' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <span>Sales Records</span>
                </a>
                <a href="dashboard_sales.php?sales_section=crm" class="nav-item <?= $sales_section === 'crm' ? 'active' : '' ?>">
                    <i class="fas fa-address-book nav-icon"></i>
                    <span>Customer CRM</span>
                </a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <form method="POST">
                <button type="submit" name="logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
    <div class="main-content">
        <header>
            <div class="header-content">
                <div>
                    <div class="header-greeting">
                        <?php
                        $hour = (int)date('H');
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                        echo $greeting . ', ' . htmlspecialchars($_SESSION['user'] ?? '');
                        ?>
                    </div>
                    <div class="header-subtitle"><?= date('l, F j, Y') ?></div>
                </div>
                <div class="header-actions">
                    <a href="pos_system.php" class="edit-btn" style="background:#10b981;">
                        <i class="fas fa-cash-register"></i> POS System
                    </a>
                    <a href="pos_transactions.php" class="edit-btn">
                        <i class="fas fa-receipt"></i> Transactions
                    </a>
                    <button class="edit-btn" style="background:transparent;border:1px solid var(--border-color);color:var(--text-primary);" id="themeToggle" type="button" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="search-box" style="position:relative;">
                        <input type="text" placeholder="Search..." style="padding:0.5rem 2.5rem 0.5rem 0.85rem;border:1px solid var(--border-color);border-radius:var(--radius);background:var(--bg-secondary);color:var(--text-primary);">
                        <i class="fas fa-search" style="position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    </div>
                </div>
            </div>
        </header>
        <div class="content">
            <?php if ($sales_flash): ?>
            <div class="alert" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:var(--radius);background:var(--success-bg);color:var(--success);border-left:4px solid var(--success);">
                <?= htmlspecialchars($sales_flash) ?>
            </div>
            <?php endif; ?>
            <?php if ($customer_flash): ?>
            <div class="alert" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:var(--radius);background:var(--success-bg);color:var(--success);border-left:4px solid var(--success);">
                <?= htmlspecialchars($customer_flash) ?>
            </div>
            <?php endif; ?>
            <?php if ($customer_error): ?>
            <div class="alert" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:var(--radius);background:var(--danger-bg);color:var(--danger);border-left:4px solid var(--danger);">
                <?= htmlspecialchars($customer_error) ?>
            </div>
            <?php endif; ?>

            <?php if ($sales_section === 'records'): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Sales Records</span>
                    </div>
                    <div class="stats-summary">
                        <div class="stat-item">
                            <span class="stat-item-label">Total Sales</span>
                            <span class="stat-item-value" id="salesCount" data-value="<?= (int)$sales_count ?>"><?= number_format($sales_count) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-item-label">Revenue</span>
                            <span class="stat-item-value" id="totalRevenue" data-value="<?= number_format($revenue, 2, '.', '') ?>">&#8369;<?= number_format($revenue, 2) ?></span>
                        </div>
                        <button type="button" class="edit-btn" onclick="openSalesModal()" <?= count($inventory_items) === 0 ? 'disabled' : '' ?>>
                            <i class="fas fa-plus"></i> Record Sale
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date Sold</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($sales_data) === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:1.25rem;color:var(--text-secondary);">No sales recorded yet.</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($sales_data as $sale): ?>
                            <?php
                                $productDisplay = $sale['product'];
                                if ($productDisplay !== null && $productDisplay !== '' && is_numeric($productDisplay)) {
                                    $intId = (int)$productDisplay;
                                    if (isset($inventory_lookup[$intId])) {
                                        $productDisplay = $inventory_lookup[$intId];
                                    }
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($sale['date_sold']) ?></td>
                                <td><?= htmlspecialchars($productDisplay) ?></td>
                                <td><?= (int)$sale['quantity'] ?></td>
                                <td>&#8369;<?= number_format((float)$sale['price'], 2) ?></td>
                                <td>&#8369;<?= number_format((float)$sale['price'] * (int)$sale['quantity'], 2) ?></td>
                                <td>
                                    <a href="#" class="action-btn" onclick="openDeleteModal('dashboard_sales.php?sales_section=records&amp;delete_sale=<?= (int)$sale['id'] ?>')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="recordSaleModal" class="modal-overlay">
                <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="recordSaleModalTitle">
                    <div class="modal-header">
                        <h3 id="recordSaleModalTitle">Record Sale</h3>
                    </div>
                    <?php if (!empty($sales_message)): ?>
                    <div style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:var(--radius);background:var(--danger-bg);color:var(--danger);">
                        <?= htmlspecialchars($sales_message) ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" id="salesForm">
                        <div class="form-group">
                            <label for="add_inventory_select">Product</label>
                            <?php if (count($inventory_items) === 0): ?>
                            <div style="padding:0.75rem;border-radius:var(--radius);background:var(--border-light);color:var(--text-secondary);">
                                No inventory items available. Please add items first.
                            </div>
                            <?php else: ?>
                            <select id="add_inventory_select" name="inventory_id" required>
                                <option value="">Select product...</option>
                                <?php foreach ($inventory_items as $item): ?>
                                <option value="<?= (int)$item['id'] ?>" data-qty="<?= (int)$item['quantity'] ?>">
                                    <?= htmlspecialchars($item['item_name']) ?> (Available: <?= (int)$item['quantity'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="add_qty">Quantity</label>
                            <input type="number" id="add_qty" name="quantity" min="1" required>
                            <small id="availableHint" style="display:block;margin-top:0.4rem;color:var(--text-secondary);"></small>
                        </div>
                        <div class="form-group">
                            <label for="add_price">Price per Unit</label>
                            <input type="number" step="0.01" id="add_price" name="price" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="add_date_sold">Date Sold</label>
                            <input type="date" id="add_date_sold" name="date_sold" required>
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="closeSalesModal()">Cancel</button>
                            <button type="submit" name="add_sale" class="btn-primary" <?= count($inventory_items) === 0 ? 'disabled' : '' ?>>Record Sale</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-address-book"></i>
                        <span>Customers</span>
                    </div>
                    <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                        <span class="card-badge"><?= $customer_count ?> customers</span>
                        <button type="button" class="edit-btn" onclick="openCustomerModal()">
                            <i class="fas fa-user-plus"></i> Add Customer
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($customers) === 0): ?>
                            <tr>
                                <td colspan="6" style="text-align:center;padding:1.25rem;color:var(--text-secondary);">No customers added yet.</td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($customers as $customer): ?>
                            <?php
                                $customerPayload = htmlspecialchars(
                                    json_encode([
                                        'customer_id' => (int)$customer['customer_id'],
                                        'name' => $customer['name'] ?? '',
                                        'email' => $customer['email'] ?? '',
                                        'phone' => $customer['phone'] ?? '',
                                        'address' => $customer['address'] ?? ''
                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['name']) ?></td>
                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                <td><?= htmlspecialchars($customer['phone']) ?></td>
                                <td><?= htmlspecialchars($customer['address']) ?></td>
                                <td><?= htmlspecialchars($customer['created_at']) ?></td>
                                <td>
                                    <button type="button" class="edit-btn" onclick="openEditCustomerModal(this)" data-customer='<?= $customerPayload ?>'>
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <a href="#" class="action-btn" onclick="openDeleteModal('dashboard_sales.php?sales_section=crm&amp;delete_customer=<?= (int)$customer['customer_id'] ?>')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="addCustomerModal" class="modal-overlay">
                <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="addCustomerModalTitle">
                    <div class="modal-header">
                        <h3 id="addCustomerModalTitle">Add Customer</h3>
                    </div>
                    <?php if (!empty($customer_message)): ?>
                    <div style="margin-bottom:1rem;padding:0.75rem 1rem;border-radius:var(--radius);background:var(--danger-bg);color:var(--danger);">
                        <?= htmlspecialchars($customer_message) ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="customer_name">Name</label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_email">Email</label>
                            <input type="email" id="customer_email" name="customer_email">
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone</label>
                            <input type="text" id="customer_phone" name="customer_phone">
                        </div>
                        <div class="form-group">
                            <label for="customer_address">Address</label>
                            <input type="text" id="customer_address" name="customer_address">
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="closeCustomerModal()">Cancel</button>
                            <button type="submit" name="add_customer" class="btn-primary">Add Customer</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="editCustomerModal" class="modal-overlay">
                <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="editCustomerModalTitle">
                    <div class="modal-header">
                        <h3 id="editCustomerModalTitle">Edit Customer</h3>
                    </div>
                    <form method="POST" id="editCustomerForm">
                        <input type="hidden" name="edit_customer" value="1">
                        <input type="hidden" name="customer_id" id="edit_customer_id">
                        <div class="form-group">
                            <label for="edit_customer_name">Name</label>
                            <input type="text" id="edit_customer_name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_customer_email">Email</label>
                            <input type="email" id="edit_customer_email" name="customer_email">
                        </div>
                        <div class="form-group">
                            <label for="edit_customer_phone">Phone</label>
                            <input type="text" id="edit_customer_phone" name="customer_phone">
                        </div>
                        <div class="form-group">
                            <label for="edit_customer_address">Address</label>
                            <input type="text" id="edit_customer_address" name="customer_address">
                        </div>
                        <div class="modal-actions">
                            <button type="button" class="btn-secondary" onclick="closeEditCustomerModal()">Cancel</button>
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h3><i class="fas fa-exclamation-triangle" style="color:var(--warning);"></i> Confirm Deletion</h3>
            <p>Are you sure you want to delete this record? This action cannot be undone.</p>
            <div class="modal-actions" style="justify-content:center;">
                <button id="confirmDeleteBtn" class="btn-primary">Yes, Delete</button>
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let deleteUrl = '';

        function openDeleteModal(url) {
            deleteUrl = url;
            const modal = document.getElementById('deleteModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            if (modal) modal.style.display = 'none';
            deleteUrl = '';
        }

        function openSalesModal() {
            const modal = document.getElementById('recordSaleModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeSalesModal() {
            const modal = document.getElementById('recordSaleModal');
            if (modal) modal.style.display = 'none';
        }

        function openCustomerModal() {
            const modal = document.getElementById('addCustomerModal');
            if (modal) modal.style.display = 'flex';
        }

        function closeCustomerModal() {
            const modal = document.getElementById('addCustomerModal');
            if (modal) modal.style.display = 'none';
        }

        function openEditCustomerModal(trigger) {
            const modal = document.getElementById('editCustomerModal');
            if (!modal || !trigger) return;
            let payload = null;
            try {
                payload = trigger.dataset.customer ? JSON.parse(trigger.dataset.customer) : null;
            } catch (error) {
                payload = null;
            }
            if (!payload) return;
            document.getElementById('edit_customer_id').value = payload.customer_id || '';
            document.getElementById('edit_customer_name').value = payload.name || '';
            document.getElementById('edit_customer_email').value = payload.email || '';
            document.getElementById('edit_customer_phone').value = payload.phone || '';
            document.getElementById('edit_customer_address').value = payload.address || '';
            modal.style.display = 'flex';
        }

        function closeEditCustomerModal() {
            const modal = document.getElementById('editCustomerModal');
            if (modal) modal.style.display = 'none';
        }

        function toggleTheme() {
            const html = document.documentElement;
            const toggle = document.getElementById('themeToggle');
            const icon = toggle ? toggle.querySelector('i') : null;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            if (icon) {
                icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        function updateAvailableHint() {
            const select = document.getElementById('add_inventory_select');
            const hint = document.getElementById('availableHint');
            if (!select || !hint) return;
            const option = select.options[select.selectedIndex];
            const qty = option ? option.getAttribute('data-qty') : '';
            hint.textContent = qty ? 'Available quantity: ' + qty : '';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const theme = localStorage.getItem('theme') || 'light';
            const toggle = document.getElementById('themeToggle');
            if (toggle) {
                const icon = toggle.querySelector('i');
                if (icon) {
                    icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                }
            }

            const confirmBtn = document.getElementById('confirmDeleteBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    if (deleteUrl) {
                        window.location.href = deleteUrl;
                    }
                });
            }

            const salesModal = document.getElementById('recordSaleModal');
            if (salesModal) {
                salesModal.addEventListener('click', (event) => {
                    if (event.target === salesModal) {
                        closeSalesModal();
                    }
                });
            }

            const customerModal = document.getElementById('addCustomerModal');
            if (customerModal) {
                customerModal.addEventListener('click', (event) => {
                    if (event.target === customerModal) {
                        closeCustomerModal();
                    }
                });
            }

            const editCustomerModal = document.getElementById('editCustomerModal');
            if (editCustomerModal) {
                editCustomerModal.addEventListener('click', (event) => {
                    if (event.target === editCustomerModal) {
                        closeEditCustomerModal();
                    }
                });
            }

            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('click', (event) => {
                    if (event.target === deleteModal) {
                        closeDeleteModal();
                    }
                });
            }

            const inventorySelect = document.getElementById('add_inventory_select');
            if (inventorySelect) {
                inventorySelect.addEventListener('change', updateAvailableHint);
            }

            animateNumber('salesCount');
            animateNumber('totalRevenue', true);
        });

        function animateNumber(id, isCurrency) {
            const el = document.getElementById(id);
            if (!el) return;
            const raw = parseFloat(el.getAttribute('data-value') || '0');
            const duration = 800;
            let start;
            function step(timestamp) {
                if (!start) start = timestamp;
                const progress = Math.min((timestamp - start) / duration, 1);
                const value = raw * progress;
                el.textContent = isCurrency ? ('\u20B1' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')) : Math.floor(value).toLocaleString();
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            }
            window.requestAnimationFrame(step);
        }
    </script>
    <script>
    (function(){
        function debounce(fn, wait){
            let timer;
            return function(){
                const args = arguments;
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), wait);
            };
        }
        function escapeRegExp(str){
            return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        function ensureNoResultsRow(table){
            const tbody = table.tBodies[0];
            if (!tbody) return null;
            let marker = tbody.querySelector('.no-results-row');
            if (!marker) {
                marker = document.createElement('tr');
                marker.className = 'no-results-row';
                const colspan = (table.tHead && table.tHead.rows[0]) ? table.tHead.rows[0].cells.length : 1;
                marker.innerHTML = '<td colspan="' + colspan + '" style="text-align:center;padding:1rem;color:var(--text-secondary)">No matching results</td>';
                tbody.appendChild(marker);
            }
            return marker;
        }
        function filterTables(input){
            const raw = (input.value || '').trim();
            if (raw === '') {
                document.querySelectorAll('.data-table tbody tr').forEach(row => {
                    if (!row.classList.contains('no-results-row')) {
                        row.style.display = '';
                    }
                });
                document.querySelectorAll('.no-results-row').forEach(row => row.style.display = 'none');
                return;
            }
            const isNumeric = /^\d+$/.test(raw);
            const pattern = new RegExp(isNumeric ? ('\\b' + escapeRegExp(raw) + '\\b') : escapeRegExp(raw), 'i');
            document.querySelectorAll('.data-table').forEach(table => {
                const tbody = table.tBodies[0];
                if (!tbody) return;
                const rows = Array.from(tbody.rows).filter(row => !row.classList.contains('no-results-row'));
                let visible = 0;
                rows.forEach(row => {
                    const match = pattern.test(row.textContent);
                    row.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                const marker = ensureNoResultsRow(table);
                if (marker) marker.style.display = visible === 0 ? '' : 'none';
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.search-box input').forEach(input => {
                const handler = debounce(() => filterTables(input), 200);
                input.addEventListener('input', handler);
                input.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        const firstVisible = document.querySelector('.data-table tbody tr:not(.no-results-row):not([style*="display: none"])');
                        if (firstVisible) {
                            firstVisible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
            });
        });
    })();
    </script>
</body>
</html>
