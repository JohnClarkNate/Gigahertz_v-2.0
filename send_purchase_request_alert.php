<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';

if (!function_exists('sendPurchaseRequestAlert')) {
    /**
     * Sends an approval email for a purchase request with approve/decline links.
     *
     * @param array $payload {
     *     @var string $recipient    Supplier email address.
     *     @var int    $request_id   Purchase request ID for reference.
     *     @var string $request_type Human readable request type.
     *     @var string $project_name Project requesting the materials.
     *     @var string $item_name    Item requiring replenishment.
     *     @var float  $quantity     Quantity requested.
     *     @var string $status       Current status label (e.g., "Pending").
     *     @var string $approve_url  Link for approving the request.
     *     @var string $decline_url  Link for declining the request.
     *     @var string $sku          Optional SKU for reference.
     *     @var float  $current_qty  Optional current inventory quantity.
     * }
     */
    function sendPurchaseRequestAlert(array $payload): bool
    {
        $required = ['recipient', 'request_id', 'request_type', 'project_name', 'item_name', 'quantity', 'status', 'approve_url', 'decline_url'];
        foreach ($required as $key) {
            if (empty($payload[$key])) {
                error_log("sendPurchaseRequestAlert: missing {$key}");
                return false;
            }
        }

        $quantity = (float)$payload['quantity'];
        $sku = $payload['sku'] ?? null;
        $currentQty = isset($payload['current_qty']) ? (float)$payload['current_qty'] : null;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp.qcu.edu@gmail.com';
            $mail->Password   = 'bfksfhvzzuhxfhom';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('erp.qcu.edu@gmail.com', 'ERP');
            $mail->addAddress($payload['recipient']);

            $mail->isHTML(true);
            $mail->Subject = 'Purchase Request Approval Needed';

            $detailsRows = [
                ['label' => 'Request #', 'value' => (int)$payload['request_id']],
                ['label' => 'Request Type', 'value' => htmlspecialchars($payload['request_type'], ENT_QUOTES, 'UTF-8')],
                ['label' => 'Project', 'value' => htmlspecialchars($payload['project_name'], ENT_QUOTES, 'UTF-8')],
                ['label' => 'Item', 'value' => htmlspecialchars($payload['item_name'], ENT_QUOTES, 'UTF-8')],
                ['label' => 'Quantity Needed', 'value' => number_format($quantity, 2)],
            ];

            if ($sku) {
                $detailsRows[] = ['label' => 'SKU', 'value' => htmlspecialchars($sku, ENT_QUOTES, 'UTF-8')];
            }
            if ($currentQty !== null) {
                $detailsRows[] = ['label' => 'Current Qty', 'value' => number_format($currentQty, 2)];
            }

            $rowsHtml = '';
            foreach ($detailsRows as $row) {
                $rowsHtml .= '<tr>'
                    . '<td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">' . $row['label'] . '</td>'
                    . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . $row['value'] . '</td>'
                    . '</tr>';
            }

            $mail->Body = '<html><body style="font-family:Arial,sans-serif;color:#111827;">'
                . '<h2 style="margin-top:0;">Purchase Request Pending Approval</h2>'
                . '<p style="color:#374151;">A project team has requested additional materials. Please review the details below and choose whether to approve or decline the request.</p>'
                . '<table style="border-collapse:collapse;margin:15px 0;width:100%;max-width:520px;font-size:14px;">'
                . $rowsHtml
                . '<tr>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Status</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($payload['status'], ENT_QUOTES, 'UTF-8') . '</td>'
                . '</tr>'
                . '</table>'
                . '<div style="margin-top:20px;display:flex;gap:12px;">'
                . '<a href="' . htmlspecialchars($payload['approve_url'], ENT_QUOTES, 'UTF-8') . '" '
                . 'style="background:#16a34a;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:600;">Approve Request</a>'
                . '<a href="' . htmlspecialchars($payload['decline_url'], ENT_QUOTES, 'UTF-8') . '" '
                . 'style="background:#dc2626;color:#fff;padding:10px 18px;text-decoration:none;border-radius:6px;font-weight:600;">Decline Request</a>'
                . '</div>'
                . '<p style="margin-top:24px;color:#6b7280;font-size:12px;">If the buttons do not work, copy and paste these links into your browser:<br>'
                . 'Approve: ' . htmlspecialchars($payload['approve_url'], ENT_QUOTES, 'UTF-8') . '<br>'
                . 'Decline: ' . htmlspecialchars($payload['decline_url'], ENT_QUOTES, 'UTF-8') . '</p>'
                . '</body></html>';

            $mail->AltBody = sprintf(
                "Purchase request #%d for %s (Qty: %s)\nApprove: %s\nDecline: %s",
                (int)$payload['request_id'],
                $payload['item_name'],
                number_format($quantity, 2),
                $payload['approve_url'],
                $payload['decline_url']
            );

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Purchase request mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

if (!function_exists('sendPurchaseRequestAcknowledgment')) {
    /**
     * Sends a confirmation email letting the supplier know the approval was received.
     *
     * @param array $payload {
     *     @var string $recipient   Supplier email address.
     *     @var int    $request_id  Purchase request ID for reference.
     *     @var string $item_name   Item that was approved.
     *     @var float  $quantity    Quantity confirmed.
     *     @var string $project_name Optional project reference.
     *     @var string $request_type Optional request type label.
     * }
     */
    function sendPurchaseRequestAcknowledgment(array $payload): bool
    {
        $required = ['recipient', 'request_id', 'item_name', 'quantity'];
        foreach ($required as $key) {
            if (empty($payload[$key])) {
                error_log("sendPurchaseRequestAcknowledgment: missing {$key}");
                return false;
            }
        }

        $quantity = (float)$payload['quantity'];
        $projectName = $payload['project_name'] ?? 'Project team';
        $requestType = $payload['request_type'] ?? 'Purchase request';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp.qcu.edu@gmail.com';
            $mail->Password   = 'bfksfhvzzuhxfhom';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('erp.qcu.edu@gmail.com', 'ERP');
            $mail->addAddress($payload['recipient']);

            $mail->isHTML(true);
            $mail->Subject = 'Approval Received - Purchase Request #' . (int)$payload['request_id'];

            $mail->Body = '<html><body style="font-family:Arial,sans-serif;color:#111827;">'
                . '<h2 style="margin-top:0;">Thanks for confirming the approval</h2>'
                . '<p style="color:#374151;">We have recorded your approval for the request below. The procurement team has been notified.</p>'
                . '<table style="border-collapse:collapse;margin:15px 0;width:100%;max-width:520px;font-size:14px;">'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Request #</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . (int)$payload['request_id'] . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Request Type</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($requestType, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Project</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Item</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($payload['item_name'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Approved Qty</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . number_format($quantity, 2) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:24px;color:#6b7280;font-size:12px;">No action is required on your side. This email is only a confirmation that the system received your approval.</p>'
                . '</body></html>';

            $mail->AltBody = sprintf(
                'Purchase request #%d for %s (Qty: %s) has been marked as approved. No further action is required.',
                (int)$payload['request_id'],
                $payload['item_name'],
                number_format($quantity, 2)
            );

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Purchase request acknowledgment error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

if (!function_exists('sendPurchaseRequestDeclineNotice')) {
    /**
     * Sends a confirmation email indicating the request was declined.
     *
     * @param array $payload {
     *     @var string $recipient   Supplier email address.
     *     @var int    $request_id  Purchase request ID for reference.
     *     @var string $item_name   Item involved in the request.
     *     @var float  $quantity    Quantity that was declined.
     *     @var string $project_name Optional project reference.
     *     @var string $request_type Optional request type label.
     * }
     */
    function sendPurchaseRequestDeclineNotice(array $payload): bool
    {
        $required = ['recipient', 'request_id', 'item_name', 'quantity'];
        foreach ($required as $key) {
            if (empty($payload[$key])) {
                error_log("sendPurchaseRequestDeclineNotice: missing {$key}");
                return false;
            }
        }

        $quantity = (float)$payload['quantity'];
        $projectName = $payload['project_name'] ?? 'Project team';
        $requestType = $payload['request_type'] ?? 'Purchase request';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp.qcu.edu@gmail.com';
            $mail->Password   = 'bfksfhvzzuhxfhom';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('erp.qcu.edu@gmail.com', 'ERP');
            $mail->addAddress($payload['recipient']);

            $mail->isHTML(true);
            $mail->Subject = 'Decline Recorded - Purchase Request #' . (int)$payload['request_id'];

            $mail->Body = '<html><body style="font-family:Arial,sans-serif;color:#111827;">'
                . '<h2 style="margin-top:0;">Decline received</h2>'
                . '<p style="color:#374151;">We have recorded your decline for the request below. The procurement team has been informed.</p>'
                . '<table style="border-collapse:collapse;margin:15px 0;width:100%;max-width:520px;font-size:14px;">'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Request #</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . (int)$payload['request_id'] . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Request Type</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($requestType, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Project</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Item</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($payload['item_name'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Requested Qty</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . number_format($quantity, 2) . '</td></tr>'
                . '</table>'
                . '<p style="margin-top:24px;color:#6b7280;font-size:12px;">This message confirms that no further action is required on your end for this request.</p>'
                . '</body></html>';

            $mail->AltBody = sprintf(
                'Purchase request #%d for %s (Qty: %s) has been marked as declined. No further action is required.',
                (int)$payload['request_id'],
                $payload['item_name'],
                number_format($quantity, 2)
            );

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Purchase request decline notice error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

if (!function_exists('sendPurchaseRequestStatusNotification')) {
    /**
     * Notifies the system mailbox whenever a purchase request is approved or declined.
     *
     * @param array $payload {
     *     @var int    $request_id
     *     @var string $status        Either 'approved' or 'declined'.
     *     @var string $item_name
     *     @var float  $quantity
     *     @var string $project_name
     * }
     */
    function sendPurchaseRequestStatusNotification(array $payload): bool
    {
        $required = ['request_id', 'status', 'item_name', 'quantity'];
        foreach ($required as $key) {
            if (!isset($payload[$key])) {
                error_log("sendPurchaseRequestStatusNotification: missing {$key}");
                return false;
            }
        }

        $status = strtolower((string)$payload['status']);
        if ($status !== 'approved' && $status !== 'declined') {
            error_log('sendPurchaseRequestStatusNotification: invalid status');
            return false;
        }

        $quantity = (float)$payload['quantity'];
        $projectName = $payload['project_name'] ?? 'N/A';
        $messageLine = $status === 'approved'
            ? 'Please arrange delivery of the requested materials as soon as possible.'
            : 'The purchase request was not approved and no delivery is required.';

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'erp.qcu.edu@gmail.com';
            $mail->Password   = 'bfksfhvzzuhxfhom';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('erp.qcu.edu@gmail.com', 'ERP');
            $mail->addAddress('erp.qcu.edu@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = sprintf('Purchase Request #%d %s', (int)$payload['request_id'], ucfirst($status));

            $mail->Body = '<html><body style="font-family:Arial,sans-serif;color:#111827;">'
                . '<h2 style="margin-top:0;">Purchase Request ' . ucfirst($status) . '</h2>'
                . '<p style="color:#374151;">' . htmlspecialchars($messageLine, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<table style="border-collapse:collapse;margin:15px 0;width:100%;max-width:520px;font-size:14px;">'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Request #</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . (int)$payload['request_id'] . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Project</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Item</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($payload['item_name'], ENT_QUOTES, 'UTF-8') . '</td></tr>'
                . '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:600;">Quantity</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . number_format($quantity, 2) . '</td></tr>'
                . '</table>'
                . '</body></html>';

            $mail->AltBody = sprintf(
                'Purchase request #%d (%s) was %s. %s',
                (int)$payload['request_id'],
                $payload['item_name'],
                $status,
                $messageLine
            );

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Purchase request status notification error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
