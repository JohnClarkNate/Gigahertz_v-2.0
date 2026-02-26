
<?php
// For debugging only - disable display_errors and set error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// Log all errors to a file for inspection
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/pos_checkout_error.log');
session_start();
require 'db.php';

if (!function_exists('logActivity')) {
    function logActivity(PDO $pdo, int $companyId, ?int $userId, string $userRole, string $module, string $action, ?string $description = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (company_id, user_id, user_role, module, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
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

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check authorization - Allow POS Head, Sales Head, or Admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['head_pos', 'head_sales', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$company_id = $_SESSION['company_id'];

// Get JSON input from the frontend
$input = json_decode(file_get_contents('php://input'), true);

// Debug: Log the raw input (remove this after confirming it works)
// error_log("POS Checkout Input: " . print_r($input, true));

// Validate input - Adjust field names to match what pos_system.js sends
$items = $input['items'] ?? []; // Items array from pos_system
$payment_amount = $input['payment'] ?? 0; // pos_system sends 'payment'
$total_amount = $input['total'] ?? 0; // pos_system sends 'total'
$subtotal_amount = $input['subtotal'] ?? 0; // pos_system sends 'subtotal'
$tax_amount = $input['tax'] ?? 0; // pos_system sends 'tax'
$discount_amount = $input['discount'] ?? 0; // pos_system sends 'discount'

// Generate Receipt ID (match existing RC format: RC + timestamp + user id)
$userSuffix = str_pad((string)($_SESSION['user_id'] ?? 0), 4, '0', STR_PAD_LEFT);
$receipt_id = 'RC' . date('YmdHis') . $userSuffix;

if (empty($items) || $payment_amount < $total_amount) {
    $error_msg = 'Invalid checkout data or insufficient payment.';
    if (empty($items)) {
        $error_msg = 'No items in the checkout data.';
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit();
}

try {
    $pdo->beginTransaction();

    $processed_items_info = []; // Store info for potential alerts

    foreach ($items as $itemData) {
        $product_id = (int) $itemData['id'];
        $quantity_sold = (int) $itemData['quantity'];

        if ($product_id <= 0 || $quantity_sold <= 0) {
            continue; // Skip invalid items
        }

        // 1. FETCH CURRENT INVENTORY DETAILS (including reorder level and supplier/vendor link)
        $selectInvStmt = $pdo->prepare("
            SELECT id, item_name, quantity, reorder_level, sku, selling_price, supplier_id
            FROM inventory
            WHERE id = ? AND company_id = ?
        ");
        $selectInvStmt->execute([$product_id, $company_id]);
        $item = $selectInvStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            throw new Exception("Inventory item with ID $product_id not found for company $company_id.");
        }

        if ((int)$item['quantity'] < $quantity_sold) {
            throw new Exception("Insufficient stock for item {$item['item_name']}. Available: {$item['quantity']}, Requested: $quantity_sold");
        }

        // Calculate new quantity
        $new_quantity = (int)$item['quantity'] - $quantity_sold;

        // 2. UPDATE INVENTORY QUANTITY
        $updateInvStmt = $pdo->prepare("UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE id = ? AND company_id = ?");
        $updateInvStmt->execute([$new_quantity, $product_id, $company_id]);

        // 3. STORE INFO FOR POTENTIAL STOCK ALERT (Check AFTER update)
        $processed_items_info[] = [
            'id' => $product_id,
            'name' => $item['item_name'],
            'sku' => $item['sku'],
            'new_quantity' => $new_quantity,
            'reorder_level' => $item['reorder_level'],
            'supplier_id' => $item['supplier_id'] ?? null
        ];

        // 4. RECORD THE SALE ITEM
        // Use the selling_price from the inventory item fetched above, not from the cart potentially
        $item_total = $item['selling_price'] * $quantity_sold; // Calculate total for this item line
        $created_date = date('Y-m-d');
        $sales_stmt = $pdo->prepare("INSERT INTO sales (company_id, product, product_name, quantity, price, date_sold, total_price, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $sales_stmt->execute([
            $company_id,
            $product_id,
            $item['item_name'], // Use item_name from inventory for consistency
            $quantity_sold,
            $item['selling_price'], // Use selling_price from inventory
            $created_date,
            $item_total,
            $created_date
        ]);

    }

    // 5. ADD TO FINANCE - single record per checkout
    if ($total_amount > 0) {
        $finance_description = "POS Sale - Receipt: " . $receipt_id;
        $finance_stmt = $pdo->prepare("INSERT INTO finance (company_id, amount, type, description, date) VALUES (?, ?, ?, ?, ?)");
        $finance_stmt->execute([
            $company_id,
            $total_amount,
            'income',
            $finance_description,
            date('Y-m-d H:i:s')
        ]);
    }

    $pdo->commit();

    try {
        logActivity(
            $pdo,
            $company_id,
            $_SESSION['user_id'] ?? null,
            $_SESSION['role'] ?? 'unknown',
            'pos',
            'process_sale',
            sprintf('Receipt %s | Items: %d | Total: %.2f', $receipt_id, count($items), (float)$total_amount)
        );
    } catch (Throwable $activityError) {
        error_log('POS activity log failed: ' . $activityError->getMessage());
    }

    // --- STOCK ALERT CHECK (Performed AFTER successful commit to avoid alerting on failed transactions) ---
    // This part checks for vendors linked via vendors.supplier_id (selected when creating vendors)
    foreach ($processed_items_info as $info) {
        if ($info['new_quantity'] <= $info['reorder_level']) {
            $vendorEmail = null;
            $linkedSupplierId = $info['supplier_id'] ?? null;

            if (!empty($linkedSupplierId)) {
                $vendorStmt = $pdo->prepare("SELECT email FROM vendors WHERE supplier_id = ? AND company_id = ?");
                $vendorStmt->execute([$linkedSupplierId, $company_id]);
                $vendor = $vendorStmt->fetch();
                if ($vendor && !empty($vendor['email'])) {
                    $vendorEmail = $vendor['email'];
                }
            }

            // --- Send Alert to Vendor if available ---
            if ($vendorEmail) {
                require_once 'send_stock_alert.php'; // Include the email function
                sendStockAlert($vendorEmail, $info['name'], $info['new_quantity'], $info['reorder_level'], $info['sku']);
            }

            if (!$vendorEmail) { // Changed condition: only log if no vendor email found
                error_log("Cannot send stock alert for item {$info['name']} (SKU: {$info['sku']}). No vendor email found or empty.");
            }
        }
    }
    // --- END STOCK ALERT CHECK ---

    // Send successful JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Sale processed successfully!',
        'receipt_id' => $receipt_id // Send back the generated receipt ID
    ]);
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error_message = "Sale failed: " . $e->getMessage();
    error_log($error_message); // Log the error

    // Send error JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $error_message // This message will be shown by the JS showError function
    ]);
    exit();
}
?>