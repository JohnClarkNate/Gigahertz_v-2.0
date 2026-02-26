<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['admin', 'head_hr'])) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

require 'db.php';
$companyId = $_SESSION['company_id'] ?? null;
if (!$companyId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing company context.'
    ]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.employee_id,
            p.pay_period_start,
            p.pay_period_end,
            p.base_salary,
            p.overtime_pay,
            p.allowances,
            p.deductions,
            p.net_salary,
            p.created_at,
            COALESCE(h.name, u.username, p.employee_id) AS employee_name
        FROM payroll p
        LEFT JOIN hr h ON p.employee_id = h.employee_id AND p.company_id = h.company_id
        LEFT JOIN users u ON p.employee_id = u.employee_id AND p.company_id = u.company_id
        WHERE p.company_id = ?
        ORDER BY p.pay_period_start DESC, p.id DESC
        LIMIT 200
    ");
    $stmt->execute([$companyId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log('payroll_feed.php error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load payroll records.'
    ]);
}
