<?php
session_start();
require 'db.php';

if (!function_exists('logActivity')) {
    function logActivity(PDO $pdo, int $companyId, ?int $userId, string $userRole, string $module, string $action, ?string $description = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (company_id, user_id, user_role, module, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
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

$page = $_GET['page'] ?? null;
$isReportsPage = ($page === 'reports');

$financeDepartmentColumnExists = false;
try {
    $columnStmt = $pdo->query("SHOW COLUMNS FROM finance LIKE 'department'");
    $financeDepartmentColumnExists = $columnStmt && $columnStmt->fetch() ? true : false;
} catch (PDOException $e) {
    $financeDepartmentColumnExists = false;
}

if (!function_exists('resolveFinanceDepartmentLabel')) {
    function resolveFinanceDepartmentLabel(array $record, bool $hasDepartmentColumn): string
    {
        $fromDb = '';
        if ($hasDepartmentColumn) {
            $fromDb = trim((string)($record['department'] ?? ''));
        }
        if ($fromDb !== '') {
            return $fromDb;
        }

        $description = strtolower($record['description'] ?? '');
        $patterns = [
            'Sales' => ['pos', 'sale', 'receipt', 'order'],
            'Inventory' => ['inventory', 'stock', 'supply'],
            'Project Management' => ['project', 'pm ', 'task', 'material'],
            'HR & Payroll' => ['payroll', 'salary', 'hr', 'employee'],
            'Procurement' => ['procurement', 'vendor', 'purchase request'],
        ];
        foreach ($patterns as $label => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && strpos($description, $keyword) !== false) {
                    return $label;
                }
            }
        }
        return 'General';
    }
}

$reportFilters = [
    'start_date' => '',
    'end_date' => '',
    'department' => 'all',
];
$reportRecords = [];
$reportOverview = [
    'total_transactions' => 0,
    'total_income' => 0,
    'total_expense' => 0,
    'net_balance' => 0,
];
$reportValidationError = null;
$reportDepartmentOptions = [];

$company_id = $_SESSION['company_id'];
$finance_flash = $_SESSION['finance_flash'] ?? null;
unset($_SESSION['finance_flash']);

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_finance'])) {
    $amount = $_POST['amount'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $stmt = $pdo->prepare("INSERT INTO finance (company_id, amount, type, description, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$company_id, $amount, $type, $description, $date]);

    $transactionLabel = trim((string)$description) !== '' ? $description : 'No description provided';
    $logDetails = sprintf('Added %s transaction (%s) amount ₱%s on %s', $type, $transactionLabel, number_format((float)$amount, 2, '.', ''), $date);
    logActivity($pdo, $company_id, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? 'head_finance', 'finance', 'add_transaction', $logDetails);

    $_SESSION['finance_flash'] = 'Transaction recorded successfully.';
    header('Location: dashboard_finance.php');
    exit();
}

if (isset($_GET['delete_finance'])) {
    $delete_id = (int)$_GET['delete_finance'];
    $toDeleteStmt = $pdo->prepare("SELECT type, amount, date, description FROM finance WHERE id = ? AND company_id = ? LIMIT 1");
    $toDeleteStmt->execute([$delete_id, $company_id]);
    $transactionToDelete = $toDeleteStmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("DELETE FROM finance WHERE id = ? AND company_id = ?");
    $stmt->execute([$delete_id, $company_id]);

    if ($stmt->rowCount() > 0) {
        $deletedType = $transactionToDelete['type'] ?? 'transaction';
        $deletedAmount = $transactionToDelete['amount'] ?? 0;
        $deletedDate = $transactionToDelete['date'] ?? 'unknown date';
        $deletedLabel = trim((string)($transactionToDelete['description'] ?? '')) !== '' ? $transactionToDelete['description'] : 'No description provided';
        $logDetails = sprintf('Deleted %s transaction (%s) amount ₱%s dated %s', $deletedType, $deletedLabel, number_format((float)$deletedAmount, 2, '.', ''), $deletedDate);
        logActivity($pdo, $company_id, $_SESSION['user_id'] ?? null, $_SESSION['role'] ?? 'head_finance', 'finance', 'delete_transaction', $logDetails);
    }

    $_SESSION['finance_flash'] = 'Transaction deleted successfully.';
    header('Location: dashboard_finance.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM finance WHERE company_id = ? ORDER BY date DESC, id DESC");
$stmt->execute([$company_id]);
$records = $stmt->fetchAll();
$income_records = array_values(array_filter($records, fn($r) => ($r['type'] ?? '') === 'income'));
$expense_records = array_values(array_filter($records, fn($r) => ($r['type'] ?? '') === 'expense'));

$payrollExpenseRecords = [];
$otherExpenseRecords = [];
$excludedDeductionExpenseRecords = [];
$payrollIndicators = ['payroll:', 'salary', 'wage'];
$deductionIndicators = ['sss', 'philhealth', 'pag-ibig', 'pagibig', 'pag ibig', 'withholding', 'tax'];

foreach ($expense_records as $record) {
    $description = strtolower((string)($record['description'] ?? ''));
    $isDeduction = false;
    foreach ($deductionIndicators as $keyword) {
        if ($keyword !== '' && strpos($description, $keyword) !== false) {
            $isDeduction = true;
            break;
        }
    }

    if ($isDeduction) {
        $excludedDeductionExpenseRecords[] = $record;
        continue;
    }

    $isPayrollSalaryCost = false;
    if (strpos($description, 'payroll:') === 0) {
        $isPayrollSalaryCost = true;
    } else {
        foreach ($payrollIndicators as $keyword) {
            if ($keyword === 'payroll:') {
                continue;
            }
            if ($keyword !== '' && strpos($description, $keyword) !== false) {
                $isPayrollSalaryCost = true;
                break;
            }
        }
    }

    if ($isPayrollSalaryCost) {
        $payrollExpenseRecords[] = $record;
    } else {
        $otherExpenseRecords[] = $record;
    }
}

$payrollExpenseRecords = array_values($payrollExpenseRecords);
$expense_records = array_values($otherExpenseRecords);

$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type ='expense' THEN amount ELSE 0 END) AS total_expense
    FROM finance WHERE company_id = ?");
$stmt->execute([$company_id]);
$totals = $stmt->fetch();
$sumAmounts = static function (array $rows): float {
    return array_sum(array_map(static fn($row) => (float)($row['amount'] ?? 0), $rows));
};
$payrollExpenseTotal = $sumAmounts($payrollExpenseRecords);
$otherExpenseTotal = $sumAmounts($expense_records);
$visibleExpenseTotal = $payrollExpenseTotal + $otherExpenseTotal;
$incomeTotal = (float)($totals['total_income'] ?? 0);
$net = $incomeTotal - $visibleExpenseTotal;
$expenseRecordCount = count($payrollExpenseRecords) + count($expense_records);
$excludedDeductionExpenseTotal = $sumAmounts($excludedDeductionExpenseRecords);

if ($isReportsPage) {
    $reportFilters['start_date'] = trim($_GET['report_start_date'] ?? '');
    $reportFilters['end_date'] = trim($_GET['report_end_date'] ?? '');
    $reportFilters['department'] = trim($_GET['report_department'] ?? 'all') ?: 'all';

    if ($reportFilters['start_date'] !== '' && $reportFilters['end_date'] !== '' && $reportFilters['start_date'] > $reportFilters['end_date']) {
        $reportValidationError = 'Start date must be earlier than or equal to end date.';
    }

    if ($financeDepartmentColumnExists) {
        $deptStmt = $pdo->prepare("SELECT DISTINCT department FROM finance WHERE company_id = ? AND department IS NOT NULL AND department <> '' ORDER BY department ASC");
        $deptStmt->execute([$company_id]);
        $reportDepartmentOptions = $deptStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    } else {
        $reportDepartmentOptions = ['Sales', 'Project Management', 'HR & Payroll', 'Inventory', 'Procurement', 'General'];
    }

    if (!$reportValidationError) {
        $selectCols = "id, date, type, description, amount" . ($financeDepartmentColumnExists ? ", department" : '');
        $reportSql = "SELECT $selectCols FROM finance WHERE company_id = ?";
        $params = [$company_id];

        if ($reportFilters['start_date'] !== '') {
            $reportSql .= " AND date >= ?";
            $params[] = $reportFilters['start_date'];
        }
        if ($reportFilters['end_date'] !== '') {
            $reportSql .= " AND date <= ?";
            $params[] = $reportFilters['end_date'];
        }
        if ($financeDepartmentColumnExists && $reportFilters['department'] !== 'all' && $reportFilters['department'] !== '') {
            $reportSql .= " AND department = ?";
            $params[] = $reportFilters['department'];
        }

        $reportSql .= " ORDER BY date DESC, id DESC";
        $stmt = $pdo->prepare($reportSql);
        $stmt->execute($params);
        $fetchedRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $derivedDepartments = [];

        foreach ($fetchedRecords as $row) {
            $departmentLabel = resolveFinanceDepartmentLabel($row, $financeDepartmentColumnExists);
            if (!$financeDepartmentColumnExists) {
                $derivedDepartments[$departmentLabel] = true;
                if ($reportFilters['department'] !== 'all' && $reportFilters['department'] !== '' && strcasecmp($departmentLabel, $reportFilters['department']) !== 0) {
                    continue;
                }
            }

            $amount = (float)($row['amount'] ?? 0);
            if (($row['type'] ?? '') === 'income') {
                $reportOverview['total_income'] += $amount;
            } else {
                $reportOverview['total_expense'] += $amount;
            }
            $reportOverview['total_transactions']++;

            $reportRecords[] = [
                'date' => $row['date'] ?? '',
                'department' => $departmentLabel,
                'type' => $row['type'] ?? '',
                'description' => $row['description'] ?? '',
                'amount' => $amount,
            ];
        }

        $reportOverview['net_balance'] = $reportOverview['total_income'] - $reportOverview['total_expense'];

        if (!$financeDepartmentColumnExists && !empty($derivedDepartments)) {
            $reportDepartmentOptions = array_values(array_unique(array_merge($reportDepartmentOptions, array_keys($derivedDepartments))));
            sort($reportDepartmentOptions);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Finance Module</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Apply saved theme immediately -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- Reuse your existing CSS variables from dashboard_admin.php --- */
        :root {
            /* Light Theme Colors */
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
            /* Dark Theme Colors */
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
            display: flex; /* Flex for sidebar + main */
            min-height: 100vh;
            transition: var(--transition);
        }
        /* --- Sidebar Styles (Copied from main dashboard) --- */
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
            color: white;
            font-size: 1.125rem;
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
        .nav-section {
            margin-bottom: 1.5rem;
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
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            border-radius: var(--radius-sm);
            margin: 0.125rem 0;
            font-size: 0.9375rem;
            font-weight: 500;
            gap: 0.75rem;
        }
        .nav-item:hover {
            background: var(--border-light);
            color: var(--text-primary);
        }
        .nav-item.active {
            background: var(--primary);
            color: white;
        }
        .nav-item.active:hover {
            background: var(--primary-hover);
        }
        .nav-icon {
            font-size: 1.125rem;
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
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
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
        /* --- Main Content Styles (Copied from main dashboard) --- */
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
            transition: var(--transition);
            position: sticky;
            top: 0;
            z-index: 900;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-greeting {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .header-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .theme-toggle {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-primary);
            font-size: 1.125rem;
            flex-shrink: 0;
        }
        .theme-toggle:hover {
            background: var(--border-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .search-box {
            position: relative;
            width: 100%;
            max-width: 20rem;
        }
        .search-box input {
            padding: 0.5rem 3rem 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            width: 100%;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .search-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        .content {
            padding: 2rem;
            flex: 1;
            background-color: var(--bg-primary);
            overflow-y: auto;
        }
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.5rem;
        }
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
        }
        .card:hover {
             box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .reports-stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .reports-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .filter-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .btn-secondary-link {
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 0.65rem 1.25rem;
            background: transparent;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .btn-secondary-link:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .reports-note {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
        }
        .reports-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .report-stat {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            box-shadow: var(--shadow-card);
        }
        .report-stat-label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .report-stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .type-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .type-pill.income {
            background: var(--success-bg);
            color: var(--success);
        }
        .type-pill.expense {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .finance-tab-panel {
            margin-bottom: 1.5rem;
        }
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-secondary);
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-title i {
            font-size: 1.125rem;
            color: var(--primary);
        }
        .card-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .card-body {
            padding: 1.25rem;
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
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            background: var(--bg-secondary);
        }
        .data-table td {
            padding: 1rem;
            font-size: 0.9375rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .data-table tbody tr:hover {
            background: var(--border-light);
        }
        .action-btn, .edit-btn {
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            justify-content: center;
            min-width: 70px;
        }
        .action-btn {
            background: var(--danger);
            color: white;
        }
        .action-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .edit-btn {
            background: var(--primary);
            color: white;
            margin-right: 0.5rem;
        }
        .edit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        .form-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
        }
        .form-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9375rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.9375rem;
            transition: var(--transition);
            background: var(--bg-secondary);
            color: var(--text-primary);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary {
            width: 100%;
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .stats-summary {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.9375rem;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .stat-item-label {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.8125rem;
        }
        .stat-item-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .stat-item-value.success {
            color: var(--success);
        }
        .stat-item-value.danger {
            color: var(--danger);
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
            z-index: 9999;
            backdrop-filter: blur(2px);
        }
        .modal-box {
            background: var(--bg-secondary);
            padding: 2rem;
            border-radius: var(--radius-lg);
            text-align: center;
            box-shadow: var(--shadow-modal);
            max-width: 90%;
            width: 420px;
            transition: var(--transition);
            border: 1px solid var(--border-color);
        }
        .modal-box h3 {
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-size: 1.25rem;
        }
        .modal-box p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .modal-actions button {
            padding: 0.625rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.9375rem;
        }
        #confirmDeleteBtn {
            background: var(--danger);
            color: white;
        }
        #confirmDeleteBtn:hover {
            background: #dc2626;
        }
        .modal-actions button:last-child {
            background: var(--border-color);
            color: var(--text-primary);
        }
        .modal-actions button:last-child:hover {
            background: var(--text-muted);
        }
        /* --- Responsive Adjustments (Copied from main dashboard) --- */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            .sidebar-logo span,
            .sidebar-user,
            .nav-section-title,
            .nav-item span {
                display: none;
            }
            .sidebar-logo {
                justify-content: center;
            }
            .nav-item {
                justify-content: center;
                padding: 1rem 0;
            }
            .nav-icon {
                margin-right: 0;
            }
            .main-content {
                margin-left: 70px;
            }
            .header-content {
                 flex-direction: column;
                 align-items: flex-start;
                 gap: 1rem;
            }
            .header-actions {
                 width: 100%;
                 justify-content: space-between;
            }
            .search-box {
                 max-width: 100%;
            }
            .content {
                 padding: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .modal-box {
                 padding: 1.5rem;
            }
            .modal-actions {
                 flex-direction: column;
            }
            .modal-actions button {
                 width: 100%;
            }
            .header-greeting {
                 font-size: 1.25rem;
            }
        }
        /* --- Focus Styles (Copied from main dashboard) --- */
        button:focus,
        input:focus,
        select:focus,
        textarea:focus,
        a:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        .nav-item:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: var(--radius-sm);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="fas fa-wallet"></i></div>
            <span><?= htmlspecialchars($_SESSION['company'] ?? '') ?></span>
        </div>
        <div class="sidebar-user">Code: <?= htmlspecialchars($_SESSION['company_code']) ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Finance</div>
            <a href="dashboard_finance.php" class="nav-item <?= $isReportsPage ? '' : 'active' ?>"><i class="fas fa-wallet nav-icon"></i><span>Transactions</span></a>
            <a href="dashboard_finance.php?page=reports" class="nav-item <?= $isReportsPage ? 'active' : '' ?>"><i class="fas fa-chart-line nav-icon"></i><span>Reports</span></a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <form method="POST">
            <button type="submit" name="logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Sign Out</button>
        </form>
    </div>
</div>

<div class="main-content">
    <header>
        <div class="header-content">
            <div>
                <div class="header-greeting">
                    <?php
                    $hour = date('H');
                    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    echo $greeting . ', ' . htmlspecialchars($_SESSION['user'] ?? '');
                    ?>
                </div>
                <div class="header-subtitle"><?= date('l, F j, Y') ?></div>
            </div>
            <div class="header-actions">
                </a>
                <button class="theme-toggle" onclick="toggleTheme()" id="themeToggle"><i class="fas fa-moon"></i></button>
                <div class="search-box"><input type="text" placeholder="Search..."><i class="fas fa-search search-icon"></i></div>
            </div>
        </div>
    </header>

    <div class="content">
        <?php if ($finance_flash): ?>
        <div class="alert" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:var(--radius);background:var(--success-bg);color:var(--success);border-left:4px solid var(--success);">
            <?= htmlspecialchars($finance_flash) ?>
        </div>
        <?php endif; ?>
        <?php if ($isReportsPage): ?>
        <div class="reports-stack">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-filter"></i> Reports Filter</div>
                </div>
                <div class="card-body">
                    <?php if ($reportValidationError): ?>
                    <div class="alert" style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:var(--radius);background:var(--danger-bg);color:var(--danger);border-left:4px solid var(--danger);">
                        <?= htmlspecialchars($reportValidationError) ?>
                    </div>
                    <?php endif; ?>
                    <form method="GET">
                        <input type="hidden" name="page" value="reports">
                        <div class="reports-filter-grid">
                            <div class="form-group">
                                <label for="report_start_date">Start Date</label>
                                <input type="date" id="report_start_date" name="report_start_date" value="<?= htmlspecialchars($reportFilters['start_date']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="report_end_date">End Date</label>
                                <input type="date" id="report_end_date" name="report_end_date" value="<?= htmlspecialchars($reportFilters['end_date']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="report_department">Department</label>
                                <select id="report_department" name="report_department">
                                    <option value="all">All Departments</option>
                                    <?php foreach ($reportDepartmentOptions as $dept): ?>
                                    <?php $isSelected = strcasecmp($dept, $reportFilters['department']) === 0; ?>
                                    <option value="<?= htmlspecialchars($dept) ?>" <?= $isSelected ? 'selected' : '' ?>><?= htmlspecialchars($dept) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (!$financeDepartmentColumnExists): ?>
                        <p class="reports-note">Departments are auto-classified based on transaction descriptions.</p>
                        <?php endif; ?>
                        <div class="filter-actions">
                            <button type="submit" class="btn-primary" style="width:auto;max-width:200px;">Apply Filters</button>
                            <a href="dashboard_finance.php?page=reports" class="btn-secondary-link">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-area"></i> Overview</div>
                </div>
                <div class="card-body">
                    <div class="reports-overview">
                        <div class="report-stat">
                            <span class="report-stat-label">Total Transactions</span>
                            <span class="report-stat-value"><?= number_format($reportOverview['total_transactions']) ?></span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-label">Total Income</span>
                            <span class="report-stat-value" style="color: var(--success);">₱<?= number_format($reportOverview['total_income'], 2) ?></span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-label">Total Expense</span>
                            <span class="report-stat-value" style="color: var(--danger);">₱<?= number_format($reportOverview['total_expense'], 2) ?></span>
                        </div>
                        <div class="report-stat">
                            <span class="report-stat-label">Net Balance</span>
                            <span class="report-stat-value">₱<?= number_format($reportOverview['net_balance'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-table"></i> Transactions Breakdown</div>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportRecords as $record): ?>
                            <tr>
                                <td><?= htmlspecialchars($record['date']) ?></td>
                                <td><?= htmlspecialchars($record['department']) ?></td>
                                <td><span class="type-pill <?= htmlspecialchars($record['type']) ?>"><?= htmlspecialchars($record['type']) ?></span></td>
                                <td><?= htmlspecialchars($record['description']) ?></td>
                                <td style="font-weight:600; color: <?= ($record['type'] === 'income') ? 'var(--success)' : 'var(--danger)'; ?>;">₱<?= number_format($record['amount'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($reportRecords)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:2rem;color:var(--text-secondary);">No transactions found for the selected filters.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="content-grid">
            <div>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="fas fa-chart-pie"></i> Summary
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="stats-summary">
                            <div class="stat-item">
                                <span class="stat-item-label">Total Income</span>
                                <span class="stat-item-value success" id="totalIncome">₱<?= number_format($incomeTotal, 2) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Total Expense</span>
                                <span class="stat-item-value danger" id="totalExpense">₱<?= number_format($visibleExpenseTotal, 2) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-item-label">Net Balance</span>
                                <span class="stat-item-value" id="netBalance">₱<?= number_format($net, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header" style="align-items:center;gap:1rem;">
                        <div class="card-title">
                            <i class="fas fa-receipt"></i> Financial Records
                        </div>
                        <button type="button" class="edit-btn" style="margin-left:auto;padding:0.75rem 1rem;font-size:0.9375rem;display:flex;align-items:center;gap:0.4rem;" onclick="openFinanceAddModal()">
                            <i class="fas fa-plus"></i> Add Transaction
                        </button>
                    </div>
                    <div class="finance-tab-links" style="padding:0.75rem;display:flex;flex-wrap:wrap;gap:0.5rem;">
                        <a href="#incomeTab" class="nav-item active" style="padding:0.5rem 1rem;border-radius:var(--radius-sm);text-decoration:none;white-space:nowrap;">
                            <i class="fas fa-plus-circle nav-icon" style="color:var(--success);"></i> <span>Income (<?= count($income_records) ?>)</span>
                        </a>
                        <a href="#expenseTab" class="nav-item" style="padding:0.5rem 1rem;border-radius:var(--radius-sm);text-decoration:none;white-space:nowrap;">
                            <i class="fas fa-minus-circle nav-icon" style="color:var(--danger);"></i> <span>Expense (<?= $expenseRecordCount ?>)</span>
                        </a>
                    </div>
                </div>

                <div id="incomeTab" class="finance-tab-panel active">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-plus-circle" style="color: var(--success);"></i> Income Records
                            </div>
                            <span class="card-badge">₱<?= number_format($incomeTotal, 2) ?></span>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($income_records as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['date']) ?></td>
                                        <td style="color: var(--success); font-weight: 600;">₱<?= number_format($r['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($r['description']) ?></td>
                                        <td>
                                            <a href="#" class="action-btn" onclick="openDeleteModal('dashboard_finance.php?delete_finance=<?= $r['id'] ?>')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($income_records)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center;padding:2rem;color:var(--text-secondary);">No income records</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="expenseTab" class="finance-tab-panel" hidden>
                    <div class="card" style="margin-top:1.5rem;">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-user-check" style="color: var(--danger);"></i> Payroll Expense (Salary Costs Only)
                            </div>
                            <span class="card-badge">₱<?= number_format($payrollExpenseTotal, 2) ?></span>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payrollExpenseRecords as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['date']) ?></td>
                                        <td style="color: var(--danger); font-weight: 600;">₱<?= number_format($r['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($r['description']) ?></td>
                                        <td>
                                            <a href="#" class="action-btn" onclick="openDeleteModal('dashboard_finance.php?delete_finance=<?= $r['id'] ?>')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($payrollExpenseRecords)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center;padding:2rem;color:var(--text-secondary);">No payroll expense records yet</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card" style="margin-top:1.5rem;">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-minus-circle" style="color: var(--danger);"></i> Other Expense Records
                            </div>
                            <span class="card-badge">₱<?= number_format($otherExpenseTotal, 2) ?></span>
                        </div>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expense_records as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['date']) ?></td>
                                        <td style="color: var(--danger); font-weight: 600;">₱<?= number_format($r['amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($r['description']) ?></td>
                                        <td>
                                            <a href="#" class="action-btn" onclick="openDeleteModal('dashboard_finance.php?delete_finance=<?= $r['id'] ?>')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($expense_records)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center;padding:2rem;color:var(--text-secondary);">No other expense records</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($excludedDeductionExpenseTotal > 0): ?>
                    <div class="card" style="margin-top:1rem;border-left:4px solid var(--warning);background:rgba(245, 158, 11, 0.08);">
                        <div class="card-body" style="color:var(--text-secondary);">
                            <strong>Note:</strong> ₱<?= number_format($excludedDeductionExpenseTotal, 2) ?> in employee deductions (SSS, PhilHealth, Pag-IBIG, tax) are excluded from the expense tab and tracked as liabilities instead.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<div id="financeAddModal" class="modal-overlay" aria-hidden="true" style="display:none;">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="financeAddModalTitle" style="max-width:480px;width:100%;">
        <div class="modal-header" style="display:flex;justify-content:center;align-items:center;margin-bottom:1rem;">
            <h3 id="financeAddModalTitle" style="margin:0;font-size:1.25rem;">Add Transaction</h3>
        </div>
        <form method="POST">
            <div class="form-group">
                <label for="modal_add_amount">Amount</label>
                <input type="number" id="modal_add_amount" step="0.01" name="amount" placeholder="Enter amount" required>
            </div>
            <div class="form-group">
                <label for="modal_add_type">Type</label>
                <select id="modal_add_type" name="type" required>
                    <option value="">Select type...</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>
            <div class="form-group">
                <label for="modal_add_desc">Description</label>
                <input type="text" id="modal_add_desc" name="description" placeholder="Enter description">
            </div>
            <div class="form-group">
                <label for="modal_add_finance_date">Date</label>
                <input type="date" id="modal_add_finance_date" name="date" required>
            </div>
            <div class="modal-actions" style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
                <button type="button" class="btn-secondary" onclick="closeFinanceAddModal()">Cancel</button>
                <button type="submit" name="add_finance" class="btn-primary">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <h3><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i> Confirm Deletion</h3>
        <p>Are you sure you want to delete this item? This action cannot be undone.</p>
        <div class="modal-actions">
            <button id="confirmDeleteBtn">Yes, Delete</button>
            <button onclick="closeDeleteModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
let deleteUrl = "";

function openDeleteModal(url) {
    deleteUrl = url;
    document.getElementById("deleteModal").style.display = "flex";
}

function closeDeleteModal() {
    document.getElementById("deleteModal").style.display = "none";
    deleteUrl = "";
}

(function initFinanceTabs() {
    const tabNav = document.querySelector('.finance-tab-links');
    if (!tabNav) { return; }
    const tabButtons = Array.from(tabNav.querySelectorAll('a[href^="#"]'));
    const tabPanels = Array.from(document.querySelectorAll('.finance-tab-panel'));
    if (!tabButtons.length || !tabPanels.length) { return; }

    const activateTab = (targetId) => {
        tabButtons.forEach((btn) => {
            const isTarget = btn.getAttribute('href') === '#' + targetId;
            btn.classList.toggle('active', isTarget);
        });
        tabPanels.forEach((panel) => {
            const isTarget = panel.id === targetId;
            panel.classList.toggle('active', isTarget);
            panel.hidden = !isTarget;
        });
    };

    tabButtons.forEach((button) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = button.getAttribute('href').substring(1);
            if (targetId) {
                activateTab(targetId);
            }
        });
    });

    const defaultTab = window.location.hash === '#expenseTab' ? 'expenseTab' : 'incomeTab';
    activateTab(defaultTab);
})();

function openFinanceAddModal() {
    const modal = document.getElementById('financeAddModal');
    if (!modal) { return; }
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
}

function closeFinanceAddModal() {
    const modal = document.getElementById('financeAddModal');
    if (!modal) { return; }
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

document.getElementById('financeAddModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeFinanceAddModal();
    }
});

// --- Theme Toggle Logic (Copied from main dashboard) ---
function toggleTheme() {
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const icon = themeToggle.querySelector('i');
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

    // Add subtle animation
    icon.style.transform = 'rotate(180deg)';
    setTimeout(() => {
        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        icon.style.transform = 'rotate(0deg)';
    }, 150);
}

// Load saved theme on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const html = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const icon = themeToggle.querySelector('i');

    html.setAttribute('data-theme', savedTheme);
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
});

function animate(id, value) {
    const el = document.getElementById(id);
    if (!el) return;
    let start = 0;
    const duration = 1000;
    const step = (timestamp) => {
        if (!start) start = timestamp;
        const progress = Math.min((timestamp - start) / duration, 1);
        const val = Math.floor(progress * value);
        if (el.textContent.includes('₱')) {
            el.textContent = '₱' + val.toLocaleString();
        } else {
            el.textContent = val.toLocaleString();
        }
        if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
}

document.addEventListener("DOMContentLoaded", () => {
    const confirmBtn = document.getElementById("confirmDeleteBtn");
    if (confirmBtn) {
        confirmBtn.addEventListener("click", () => {
            if (deleteUrl) window.location.href = deleteUrl;
        });
    }

    animate("totalIncome", <?= $incomeTotal ?>);
    animate("totalExpense", <?= $visibleExpenseTotal ?>);
    animate("netBalance", <?= $net ?>);
});
</script>

<!-- place this before </body> in your dashboards or save as assets/search.js and include -->
<script>
(function(){
  function debounce(fn, wait){
    let t;
    return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); };
  }

  function makeNoResultsRow(table){
    const tbody = table.tBodies[0];
    if (!tbody) return null;
    let nr = tbody.querySelector('.no-results-row');
    if (!nr) {
      nr = document.createElement('tr');
      nr.className = 'no-results-row';
      const colspan = (table.tHead && table.tHead.rows[0]) ? table.tHead.rows[0].cells.length : 1;
      nr.innerHTML = `<td colspan="${colspan}" style="text-align:center;padding:1rem;color:var(--text-secondary)">No matching results</td>`;
      tbody.appendChild(nr);
    }
    return nr;
  }

  function filterTables(input){
    const q = (input.value || '').trim().toLowerCase();
    // prefer tables inside the same main area; fallback to all tables
    const scope = input.closest('.main-content, .content') || document;
    let tables = Array.from(scope.querySelectorAll('.data-table'));
    if (tables.length === 0) tables = Array.from(document.querySelectorAll('.data-table'));
    tables.forEach(table => {
      const tbody = table.tBodies[0];
      if (!tbody) return;
      const rows = Array.from(tbody.rows).filter(r => !r.classList.contains('no-results-row') && !r.classList.contains('template'));
      let visible = 0;
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const ok = q === '' || text.indexOf(q) !== -1;
        row.style.display = ok ? '' : 'none';
        if (ok) visible++;
      });
      const nr = makeNoResultsRow(table);
      if (nr) nr.style.display = visible === 0 ? '' : 'none';
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    const inputs = Array.from(document.querySelectorAll('.search-box input'));
    inputs.forEach(inp => {
      // attach safe handler
      const handler = debounce(()=>filterTables(inp), 150);
      inp.addEventListener('input', handler);
      // optional: allow Enter to focus first visible row
      inp.addEventListener('keydown', function(e){
        if (e.key === 'Enter') {
          const scope = inp.closest('.main-content, .content') || document;
          const first = scope.querySelector('.data-table tbody tr:not([style*="display: none"])');
          if (first) first.scrollIntoView({behavior:'smooth', block:'center'});
        }
      });
    });
  });
})();
</script>

</body>
</html>