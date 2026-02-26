<?php
// send_stock_alert.php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP; // Optional, for debugging SMTP errors
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (adjust path if vendor is in a different location)
require 'vendor/autoload.php'; 

/**
 * Sends a stock alert email to a specified address (supplier or vendor).
 *
 * @param string $recipientEmail The email address of the supplier or vendor to alert.
 * @param string $itemName The name of the item with low stock.
 * @param int $currentQty The current quantity of the item.
 * @param int $reorderLevel The reorder level for the item.
 * @param string $itemSku The SKU of the item.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendStockAlert($recipientEmail, $itemName, $currentQty, $reorderLevel, $itemSku) {
    static $queuedAlerts = [];
    static $shutdownRegistered = false;

    if (empty($recipientEmail) || empty($itemName) || $currentQty === null || $reorderLevel === null || empty($itemSku)) {
        error_log("sendStockAlert: Missing required parameters. Email: $recipientEmail, Item: $itemName, Qty: $currentQty, Reorder: $reorderLevel, SKU: $itemSku");
        return false;
    }

    if (!$shutdownRegistered) {
        register_shutdown_function(function () use (&$queuedAlerts) {
            if (empty($queuedAlerts)) {
                return;
            }
            foreach ($queuedAlerts as $email => $items) {
                if (sendQueuedStockAlert($email, $items)) {
                    $itemCount = count($items);
                    $names = implode(', ', array_map(static function ($row) {
                        return $row['item_name'];
                    }, $items));
                    error_log("Stock alert email sent successfully to $email for item(s): $names (total: $itemCount)");
                }
            }
            $queuedAlerts = [];
        });
        $shutdownRegistered = true;
    }

    $queuedAlerts[$recipientEmail][] = [
        'item_name'     => $itemName,
        'current_qty'   => $currentQty,
        'reorder_level' => $reorderLevel,
        'item_sku'      => $itemSku
    ];

    return true;
}

function sendQueuedStockAlert(string $recipientEmail, array $items): bool
{
    if (empty($items)) {
        return true;
    }

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
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Stock Alert: Item(s) Below Reorder Level';

        $rowsHtml = '';
        foreach ($items as $item) {
            $rowsHtml .= '<tr>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($item['item_name']) . '</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars($item['item_sku']) . '</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars((string)$item['current_qty']) . '</td>'
                . '<td style="padding:6px 10px;border:1px solid #e5e7eb;">' . htmlspecialchars((string)$item['reorder_level']) . '</td>'
                . '</tr>';
        }

        $mail->Body = '<html><body>'
            . '<h2 style="font-family:Arial,sans-serif;color:#111827;">Stock Alert</h2>'
            . '<p style="font-family:Arial,sans-serif;color:#374151;">The following item' . (count($items) > 1 ? 's have' : ' has') . ' fallen below the reorder level:</p>'
            . '<table style="border-collapse:collapse;margin-top:10px;font-family:Arial,sans-serif;font-size:14px;">'
            . '<thead>'
            . '<tr style="background:#f3f4f6;">
                    <th style="padding:8px 10px;border:1px solid #e5e7eb;text-align:left;">Item Name</th>
                    <th style="padding:8px 10px;border:1px solid #e5e7eb;text-align:left;">SKU</th>
                    <th style="padding:8px 10px;border:1px solid #e5e7eb;text-align:left;">Current Qty</th>
                    <th style="padding:8px 10px;border:1px solid #e5e7eb;text-align:left;">Reorder Level</th>
                </tr>'
            . '</thead>'
            . '<tbody>' . $rowsHtml . '</tbody>'
            . '</table>'
            . '<p style="font-family:Arial,sans-serif;color:#374151;margin-top:12px;">Please take the necessary action to restock these item' . (count($items) > 1 ? 's' : '') . '.</p>'
            . '</body></html>';

        $plainLines = array_map(static function ($item) {
            return sprintf('%s (SKU: %s) | Current: %s | Reorder Level: %s', $item['item_name'], $item['item_sku'], $item['current_qty'], $item['reorder_level']);
        }, $items);
        $mail->AltBody = "Stock Alert\n" . implode("\n", $plainLines);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent to $recipientEmail. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Example usage (optional, can be removed if calling from another file):
// sendStockAlert('supplier@example.com', 'Test Item', 5, 10, 'SKU123');

?>