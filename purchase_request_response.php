<?php
session_start();
require 'db.php';
require_once 'send_purchase_request_alert.php';

$token = $_GET['token'] ?? '';
$decision = strtolower($_GET['decision'] ?? '');
$validDecisions = ['approve', 'decline'];
$statusClass = 'error';
$message = 'Invalid or expired request link.';
$requestDetails = null;

if ($token && in_array($decision, $validDecisions, true)) {
    $stmt = $pdo->prepare("SELECT pr.*, i.item_name, i.supplier_id AS inventory_supplier_id, p.name AS project_name, v.email AS vendor_email FROM inventory_purchase_requests pr LEFT JOIN inventory i ON i.id = pr.inventory_id LEFT JOIN projects p ON p.id = pr.project_id LEFT JOIN vendors v ON v.id = i.supplier_id AND v.company_id = pr.company_id WHERE pr.approval_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $requestDetails = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($requestDetails) {
        $currentStatus = strtolower($requestDetails['status'] ?? 'pending');
        if ($currentStatus !== 'pending') {
            $message = 'This purchase request has already been processed and the link has been disabled.';
            $statusClass = $currentStatus === 'approved' ? 'success' : 'warning';
        } else {
            $newStatus = $decision === 'approve' ? 'approved' : 'declined';
            $processedAt = date('Y-m-d H:i:s');
            $updateStmt = $pdo->prepare("UPDATE inventory_purchase_requests SET status = ?, approved_at = ?, approval_token = NULL WHERE id = ?");
            $updateStmt->execute([$newStatus, $processedAt, $requestDetails['id']]);
            $requestDetails['status'] = $newStatus;
            $requestDetails['approved_at'] = $processedAt;
            $requestDetails['approval_token'] = null;
            $message = $newStatus === 'approved'
                ? 'Purchase request has been approved. Thank you for confirming!'
                : 'Purchase request has been declined.';
            $statusClass = $newStatus === 'approved' ? 'success' : 'warning';

            $vendorEmail = $requestDetails['vendor_email'] ?? null;
            if (!$vendorEmail && !empty($requestDetails['inventory_supplier_id'])) {
                $lookup = $pdo->prepare('SELECT email FROM vendors WHERE (id = ? OR supplier_id = ?) AND company_id = ? LIMIT 1');
                $lookup->execute([
                    $requestDetails['inventory_supplier_id'],
                    $requestDetails['inventory_supplier_id'],
                    $requestDetails['company_id']
                ]);
                $vendorEmail = $lookup->fetchColumn() ?: null;
            }

            if ($vendorEmail) {
                if ($decision === 'approve' && function_exists('sendPurchaseRequestAcknowledgment')) {
                    $ackPayload = [
                        'recipient'    => $vendorEmail,
                        'request_id'   => $requestDetails['id'],
                        'request_type' => $requestDetails['request_type'] ?? 'Purchase request',
                        'project_name' => $requestDetails['project_name'] ?? 'N/A',
                        'item_name'    => $requestDetails['item_name'] ?? 'Unknown Item',
                        'quantity'     => $requestDetails['required_qty'] ?? 0,
                    ];
                    sendPurchaseRequestAcknowledgment($ackPayload);
                } elseif ($decision === 'decline' && function_exists('sendPurchaseRequestDeclineNotice')) {
                    $declinePayload = [
                        'recipient'    => $vendorEmail,
                        'request_id'   => $requestDetails['id'],
                        'request_type' => $requestDetails['request_type'] ?? 'Purchase request',
                        'project_name' => $requestDetails['project_name'] ?? 'N/A',
                        'item_name'    => $requestDetails['item_name'] ?? 'Unknown Item',
                        'quantity'     => $requestDetails['required_qty'] ?? 0,
                    ];
                    sendPurchaseRequestDeclineNotice($declinePayload);
                }
            }

            if (function_exists('sendPurchaseRequestStatusNotification')) {
                sendPurchaseRequestStatusNotification([
                    'request_id'   => $requestDetails['id'],
                    'status'       => $newStatus,
                    'project_name' => $requestDetails['project_name'] ?? 'N/A',
                    'item_name'    => $requestDetails['item_name'] ?? 'Unknown Item',
                    'quantity'     => $requestDetails['required_qty'] ?? 0,
                ]);
            }
        }
    } else {
        $requestDetails = null;
        $message = 'This purchase request could not be found. It may have already been processed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Request Response</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .response-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
            padding: 32px;
            width: min(480px, 90vw);
        }
        .response-card h1 {
            margin-top: 0;
            font-size: 1.4rem;
        }
        .response-card p {
            color: #4b5563;
            line-height: 1.5;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 18px;
        }
        .status-chip.success {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }
        .status-chip.warning {
            background: rgba(245, 158, 11, 0.15);
            color: #c2410c;
        }
        .status-chip.error {
            background: rgba(239, 68, 68, 0.15);
            color: #b91c1c;
        }
        .details {
            margin-top: 20px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
        }
        .details dl {
            margin: 0;
        }
        .details dt {
            font-weight: 600;
            color: #374151;
        }
        .details dd {
            margin: 0 0 12px;
            color: #4b5563;
        }
        .hint {
            margin-top: 24px;
            font-size: 0.85rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="response-card">
    <div class="status-chip <?= htmlspecialchars($statusClass) ?>">Status: <?= htmlspecialchars(ucfirst($statusClass === 'error' ? 'error' : ($requestDetails['status'] ?? 'pending'))) ?></div>
    <h1><?= htmlspecialchars($message) ?></h1>
    <?php if ($requestDetails): ?>
    <div class="details">
        <dl>
            <dt>Request #</dt>
            <dd><?= (int)($requestDetails['id'] ?? 0) ?></dd>
            <dt>Project</dt>
            <dd><?= htmlspecialchars($requestDetails['project_name'] ?? 'N/A') ?></dd>
            <dt>Item</dt>
            <dd><?= htmlspecialchars($requestDetails['item_name'] ?? 'Unknown Item') ?></dd>
            <dt>Quantity Needed</dt>
            <dd><?= number_format((float)($requestDetails['required_qty'] ?? 0), 2) ?></dd>
        </dl>
    </div>
    <?php endif; ?>
    <p class="hint">You may close this tab. The procurement team has been notified automatically.</p>
</div>
</body>
</html>
