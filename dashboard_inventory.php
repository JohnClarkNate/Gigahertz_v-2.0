<?php
session_start();
require 'db.php';

if (!function_exists('logActivity')) {
    function logActivity(PDO $pdo, int $companyId, ?int $userId, string $userRole, string $module, string $action, ?string $description = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $stmt = $pdo->prepare('
            INSERT INTO activity_logs (company_id, user_id, user_role, module, action, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
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

if (!function_exists('inventoryLogEvent')) {
    function inventoryLogEvent(PDO $pdo, string $action, ?string $description = null): void
    {
        if (!function_exists('logActivity')) {
            return;
        }
        $companyId = isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : 0;
        if ($companyId <= 0) {
            return;
        }
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $userRole = $_SESSION['role'] ?? 'head_inventory';
        try {
            logActivity($pdo, $companyId, $userId, $userRole, 'inventory', $action, $description);
        } catch (Throwable $exception) {
            error_log('Inventory log capture failed: ' . $exception->getMessage());
        }
    }
}

if (!function_exists('inventoryNextId')) {
    function inventoryNextId(PDO $pdo): int
    {
        static $nextIdCache = [];
        $hash = spl_object_hash($pdo);

        if (!isset($nextIdCache[$hash])) {
            $stmt = $pdo->query('SELECT COALESCE(MAX(id), 0) FROM inventory');
            $nextIdCache[$hash] = ((int)$stmt->fetchColumn()) + 1;
        } else {
            $nextIdCache[$hash]++;
        }

        return $nextIdCache[$hash];
    }
}

if (!function_exists('inventoryFlashMessage')) {
    function inventoryFlashMessage(string $text, string $type = 'info', array $options = []): array
    {
        $variants = ['success', 'info', 'warning', 'danger'];
        if (!in_array($type, $variants, true)) {
            $type = 'info';
        }

        $payload = [
            'text' => trim($text),
            'type' => $type,
        ];

        if (isset($options['title']) && $options['title'] !== '') {
            $payload['title'] = trim((string)$options['title']);
        }
        if (isset($options['duration'])) {
            $payload['duration'] = max(800, (int)$options['duration']);
        }

        return $payload;
    }
}

if (!function_exists('inventoryRedirectWithMessage')) {
    function inventoryRedirectWithMessage(array $payload, string $section = 'inventory'): void
    {
        $_SESSION['message'] = $payload;
        $section = trim($section);
        $target = 'dashboard_inventory.php';
        if ($section !== '' && $section !== 'inventory') {
            $target .= '?section=' . rawurlencode($section);
        }
        header('Location: ' . $target);
        exit();
    }
}

if (!function_exists('posEnsureHiddenItemsTable')) {
    function posEnsureHiddenItemsTable(PDO $pdo): void
    {
        static $posHiddenTableReady = false;
        if ($posHiddenTableReady) {
            return;
        }

        $hasHiddenByColumn = false;

        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS pos_hidden_items (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                company_id INT NOT NULL,
                inventory_id INT NOT NULL,
                hidden_by INT DEFAULT NULL,
                hidden_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_company_inventory (company_id, inventory_id),
                KEY idx_company_hidden (company_id, hidden_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        } catch (Throwable $error) {
            error_log('posEnsureHiddenItemsTable create failed: ' . $error->getMessage());
        }

        try {
            $columnStmt = $pdo->query("SHOW COLUMNS FROM pos_hidden_items LIKE 'hidden_by'");
            $hasHiddenByColumn = (bool)($columnStmt && $columnStmt->fetch(PDO::FETCH_ASSOC));
            if (!$hasHiddenByColumn) {
                $pdo->exec('ALTER TABLE pos_hidden_items ADD COLUMN hidden_by INT DEFAULT NULL AFTER inventory_id');
                $hasHiddenByColumn = true;
            }
        } catch (Throwable $error) {
            error_log('posEnsureHiddenItemsTable column sync failed: ' . $error->getMessage());
            $hasHiddenByColumn = false;
        }

        if ($hasHiddenByColumn) {
            try {
                $indexStmt = $pdo->query('SHOW INDEX FROM pos_hidden_items');
                $indexes = [];
                while ($indexStmt && ($row = $indexStmt->fetch(PDO::FETCH_ASSOC))) {
                    $keyName = $row['Key_name'];
                    if (!isset($indexes[$keyName])) {
                        $indexes[$keyName] = [
                            'non_unique' => (int)$row['Non_unique'],
                            'columns' => []
                        ];
                    }
                    $indexes[$keyName]['columns'][(int)$row['Seq_in_index']] = $row['Column_name'];
                }

                $hasUniquePair = false;
                foreach ($indexes as $meta) {
                    if ($meta['non_unique'] !== 0) {
                        continue;
                    }
                    ksort($meta['columns']);
                    $ordered = array_values($meta['columns']);
                    if ($ordered === ['company_id', 'inventory_id'] || $ordered === ['inventory_id', 'company_id']) {
                        $hasUniquePair = true;
                        break;
                    }
                }

                if (!$hasUniquePair) {
                    $pdo->exec('CREATE UNIQUE INDEX uniq_company_inventory ON pos_hidden_items (company_id, inventory_id)');
                }
            } catch (Throwable $error) {
                error_log('posEnsureHiddenItemsTable index sync failed: ' . $error->getMessage());
            }

            $posHiddenTableReady = true;
        }
    }
}

if (!function_exists('posSetItemVisibility')) {
    function posSetItemVisibility(PDO $pdo, int $companyId, int $inventoryId, bool $hidden, ?int $userId = null): bool
    {
        posEnsureHiddenItemsTable($pdo);
        try {
            if ($hidden) {
                $stmt = $pdo->prepare('
                    INSERT INTO pos_hidden_items (company_id, inventory_id, hidden_by, hidden_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE hidden_by = VALUES(hidden_by), hidden_at = NOW()
                ');
                return $stmt->execute([$companyId, $inventoryId, $userId]);
            }

            $stmt = $pdo->prepare('DELETE FROM pos_hidden_items WHERE company_id = ? AND inventory_id = ?');
            $stmt->execute([$companyId, $inventoryId]);
            return true;
        } catch (Throwable $error) {
            error_log('posSetItemVisibility failed: ' . $error->getMessage());
            return false;
        }
    }
}

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'head_inventory') {
    header('Location: login.php');
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

$company_id = $_SESSION['company_id'];
posEnsureHiddenItemsTable($pdo);
$message = null;
$section = $_GET['section'] ?? 'inventory';

$inventory_add_error = null;
$inventory_edit_error = null;
$inventory_adjust_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pos_visibility_action'], $_POST['inventory_id'])) {
    $inventoryId = (int)($_POST['inventory_id'] ?? 0);
    $action = $_POST['pos_visibility_action'];

    if ($inventoryId > 0 && in_array($action, ['hide', 'show'], true)) {
        $itemStmt = $pdo->prepare('SELECT item_name FROM inventory WHERE id = ? AND company_id = ? LIMIT 1');
        $itemStmt->execute([$inventoryId, $company_id]);
        $itemName = $itemStmt->fetchColumn();

        if ($itemName !== false) {
            $shouldHide = $action === 'hide';
            $updated = posSetItemVisibility($pdo, $company_id, $inventoryId, $shouldHide, $_SESSION['user_id'] ?? null);

            if ($updated) {
                inventoryLogEvent(
                    $pdo,
                    $shouldHide ? 'pos_hide_item' : 'pos_show_item',
                    sprintf(
                        '%s inventory item "%s" (ID: %d) for POS.',
                        $shouldHide ? 'Hidden' : 'Restored',
                        $itemName,
                        $inventoryId
                    )
                );

                inventoryRedirectWithMessage(
                    inventoryFlashMessage(
                        $shouldHide
                            ? sprintf('"%s" is now hidden from the POS.', $itemName)
                            : sprintf('"%s" is now visible in the POS.', $itemName),
                        $shouldHide ? 'warning' : 'success'
                    ),
                    'inventory'
                );
            }

            inventoryRedirectWithMessage(
                inventoryFlashMessage('Unable to update POS visibility. Please try again.', 'danger'),
                'inventory'
            );
        }

        inventoryRedirectWithMessage(
            inventoryFlashMessage('Inventory item not found.', 'danger'),
            'inventory'
        );
    }

    inventoryRedirectWithMessage(
        inventoryFlashMessage('Invalid POS visibility request.', 'danger'),
        'inventory'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_inventory'])) {
    $item_name = trim($_POST['item_name']);
    $quantity = (int)($_POST['quantity'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $date_added = $_POST['date_added'] ?? date('Y-m-d');

    $newInventoryId = inventoryNextId($pdo);
    $stmt = $pdo->prepare('INSERT INTO inventory (id, company_id, item_name, quantity, category, date_added, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$newInventoryId, $company_id, $item_name, $quantity, $category, $date_added]);
    inventoryLogEvent(
        $pdo,
        'add_inventory_item',
        sprintf('Added "%s" (%d units) to %s', $item_name, $quantity, $category !== '' ? $category : 'Uncategorized')
    );
    inventoryRedirectWithMessage(
        inventoryFlashMessage('Item added to inventory.', 'success'),
        'inventory'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item'])) {
    $item_id = (int)$_POST['item_id'];
    $new_quantity = (int)$_POST['new_quantity'];
    $stmt = $pdo->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE id = ? AND company_id = ?');
    $stmt->execute([$new_quantity, $item_id, $company_id]);
    inventoryLogEvent(
        $pdo,
        'update_inventory_quantity',
        sprintf('Adjusted item #%d quantity to %d', $item_id, $new_quantity)
    );
    inventoryRedirectWithMessage(
        inventoryFlashMessage('Quantity updated.', 'info'),
        'inventory'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item_full'])) {
    $item_id = (int)$_POST['item_id'];
    $item_name = trim($_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $category = trim($_POST['category']);
    $updated_at = $_POST['updated_at'] ?? date('Y-m-d H:i');
    $stmt = $pdo->prepare('UPDATE inventory SET item_name = ?, quantity = ?, category = ?, updated_at = ? WHERE id = ? AND company_id = ?');
    $stmt->execute([$item_name, $quantity, $category, $updated_at, $item_id, $company_id]);
    inventoryLogEvent(
        $pdo,
        'update_inventory_item',
        sprintf('Updated item #%d (%s)', $item_id, $item_name)
    );
    inventoryRedirectWithMessage(
        inventoryFlashMessage('Inventory item details updated.', 'info'),
        'inventory'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_inventory'])) {
    $rawIds = $_POST['inventory_ids'] ?? [];
    $selectedIds = array_values(array_unique(array_filter(array_map('intval', (array)$rawIds), static fn($id) => $id > 0)));

    if (empty($selectedIds)) {
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Select at least one inventory item to delete.', 'warning'),
            'inventory'
        );
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $params = array_merge([$company_id], $selectedIds);

    $fetchStmt = $pdo->prepare("SELECT id, item_name, quantity FROM inventory WHERE company_id = ? AND id IN ($placeholders)");
    $fetchStmt->execute($params);
    $itemsToDelete = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($itemsToDelete)) {
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Selected inventory items were not found.', 'warning'),
            'inventory'
        );
    }

    $deleteStmt = $pdo->prepare("DELETE FROM inventory WHERE company_id = ? AND id IN ($placeholders)");
    $deleteStmt->execute($params);

    $names = array_slice(array_column($itemsToDelete, 'item_name'), 0, 5);
    $summaryNames = implode(', ', array_map(static fn($name) => $name ?: 'Unnamed Item', $names));
    if (count($itemsToDelete) > 5) {
        $summaryNames .= sprintf(' +%d more', count($itemsToDelete) - 5);
    }

    inventoryLogEvent(
        $pdo,
        'bulk_delete_inventory',
        sprintf('Bulk deleted %d inventory item(s): %s', count($itemsToDelete), $summaryNames)
    );

    inventoryRedirectWithMessage(
        inventoryFlashMessage(sprintf('Deleted %d inventory item(s).', count($itemsToDelete)), 'success'),
        'inventory'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete_defective'])) {
    $rawIds = $_POST['defective_ids'] ?? [];
    $selectedIds = array_values(array_unique(array_filter(array_map('intval', (array)$rawIds), static fn($id) => $id > 0)));

    if (empty($selectedIds)) {
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Select at least one defective item to delete.', 'warning'),
            'defective'
        );
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $params = array_merge([$company_id], $selectedIds);

    $fetchStmt = $pdo->prepare("SELECT id, item_name FROM inventory WHERE company_id = ? AND is_defective = 1 AND id IN ($placeholders)");
    $fetchStmt->execute($params);
    $itemsToDelete = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($itemsToDelete)) {
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Selected defective items were not found.', 'warning'),
            'defective'
        );
    }

    $deleteStmt = $pdo->prepare("DELETE FROM inventory WHERE company_id = ? AND is_defective = 1 AND id IN ($placeholders)");
    $deleteStmt->execute($params);

    $names = array_slice(array_column($itemsToDelete, 'item_name'), 0, 5);
    $summaryNames = implode(', ', array_map(static fn($name) => $name ?: 'Unnamed Item', $names));
    if (count($itemsToDelete) > 5) {
        $summaryNames .= sprintf(' +%d more', count($itemsToDelete) - 5);
    }

    inventoryLogEvent(
        $pdo,
        'bulk_delete_defective_inventory',
        sprintf('Bulk deleted %d defective item(s): %s', count($itemsToDelete), $summaryNames)
    );

    inventoryRedirectWithMessage(
        inventoryFlashMessage(sprintf('Deleted %d defective item(s).', count($itemsToDelete)), 'success'),
        'defective'
    );
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_defective'])) {
    $item_id = (int)($_POST['inventory_id'] ?? 0);
    $defective_qty = max(0, (int)($_POST['defective_quantity'] ?? 0));
    $defective_reason = trim($_POST['defective_reason'] ?? '');
    $defective_reason = $defective_reason !== '' ? $defective_reason : 'Marked as defective';

    if ($item_id <= 0 || $defective_qty <= 0) {
        $inventory_adjust_error = 'Select a valid item and defective quantity.';
    } else {
        $stmt = $pdo->prepare('SELECT id, sku, item_name, quantity, unit, reorder_level, category, cost_price, selling_price, supplier_id, remarks, date_added FROM inventory WHERE id = ? AND company_id = ? AND (is_defective = 0 OR is_defective IS NULL)');
        $stmt->execute([$item_id, $company_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            $inventory_adjust_error = 'Inventory item not found or already defective.';
        } else {
            $current_qty = (int)($item['quantity'] ?? 0);
            if ($current_qty <= 0) {
                $inventory_adjust_error = 'Item has no available quantity to mark as defective.';
            } else {
                if ($defective_qty > $current_qty) {
                    $defective_qty = $current_qty;
                }

                if ($defective_qty >= $current_qty) {
                    $update = $pdo->prepare("UPDATE inventory SET is_defective = 1, defective_reason = ?, defective_at = NOW(), status = 'Defective' WHERE id = ? AND company_id = ?");
                    $update->execute([$defective_reason, $item_id, $company_id]);
                } else {
                    $remaining_qty = $current_qty - $defective_qty;
                    $update_qty = $pdo->prepare('UPDATE inventory SET quantity = ?, updated_at = NOW() WHERE id = ? AND company_id = ?');
                    $update_qty->execute([$remaining_qty, $item_id, $company_id]);

                    $defectiveInventoryId = inventoryNextId($pdo);
                    $insert = $pdo->prepare("INSERT INTO inventory (id, company_id, sku, item_name, quantity, unit, reorder_level, category, cost_price, selling_price, supplier_id, remarks, date_added, updated_at, is_defective, defective_reason, defective_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, ?, NOW(), 'Defective')");
                    $insert->execute([
                        $defectiveInventoryId,
                        $company_id,
                        $item['sku'] ?? null,
                        $item['item_name'] ?? null,
                        $defective_qty,
                        $item['unit'] ?? null,
                        $item['reorder_level'] ?? null,
                        $item['category'] ?? null,
                        $item['cost_price'] ?? null,
                        $item['selling_price'] ?? null,
                        $item['supplier_id'] ?? null,
                        $item['remarks'] ?? null,
                        $item['date_added'] ?? date('Y-m-d'),
                        $defective_reason,
                    ]);
                }

                if (!$inventory_adjust_error) {
                    $itemName = trim($item['item_name'] ?? 'Inventory Item');
                    inventoryLogEvent(
                        $pdo,
                        'mark_defective',
                        sprintf('Marked "%s" (ID %d) defective for %d units. Reason: %s', $itemName, $item_id, $defective_qty, $defective_reason)
                    );
                    inventoryRedirectWithMessage(
                        inventoryFlashMessage('Item successfully marked as defective.', 'warning'),
                        'inventory'
                    );
                }
            }
        }
    }
}

if (isset($_GET['mark_defective'])) {
    $item_id = (int)$_GET['mark_defective'];
    $defective_reason = isset($_GET['reason']) ? trim($_GET['reason']) : 'Marked as defective';
    $stmt = $pdo->prepare("UPDATE inventory SET is_defective = 1, defective_reason = ?, defective_at = NOW(), status = 'Defective' WHERE id = ? AND company_id = ?");
    $stmt->execute([$defective_reason, $item_id, $company_id]);
    inventoryLogEvent(
        $pdo,
        'mark_defective',
        sprintf('Marked item ID %d as defective via quick action. Reason: %s', $item_id, $defective_reason)
    );
    $_SESSION['message'] = inventoryFlashMessage('Item marked as defective.', 'warning');
    header('Location: dashboard_inventory.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM inventory WHERE id = ? AND company_id = ?');
    $stmt->execute([$delete_id, $company_id]);
    if ($stmt->rowCount() > 0) {
        inventoryLogEvent(
            $pdo,
            'delete_inventory_item',
            sprintf('Deleted inventory item ID %d', $delete_id)
        );
    }
    $_SESSION['message'] = inventoryFlashMessage('Item deleted from inventory.', 'danger');
    header('Location: dashboard_inventory.php');
    exit();
}

if (isset($_GET['restore_defective'])) {
    $restore_id = (int)$_GET['restore_defective'];
    $stmt = $pdo->prepare('SELECT id, item_name FROM inventory WHERE id = ? AND company_id = ? AND is_defective = 1');
    $stmt->execute([$restore_id, $company_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $update = $pdo->prepare("UPDATE inventory SET is_defective = 0, status = 'In Stock', defective_reason = NULL, defective_at = NULL, updated_at = NOW() WHERE id = ? AND company_id = ?");
        $update->execute([$restore_id, $company_id]);
        inventoryLogEvent(
            $pdo,
            'restore_defective_item',
            sprintf('Restored defective item ID %d back to stock', $restore_id)
        );
        $_SESSION['message'] = inventoryFlashMessage('Item restored to inventory.', 'success');
        $_SESSION['toast'] = 'Defective item successfully restored.';
    } else {
        $_SESSION['message'] = inventoryFlashMessage('Defective item not found.', 'danger');
    }
    header('Location: dashboard_inventory.php?section=defective');
    exit();
}

if (isset($_GET['delete_defective'])) {
    $delete_id = (int)$_GET['delete_defective'];
    $stmt = $pdo->prepare('DELETE FROM inventory WHERE id = ? AND company_id = ? AND is_defective = 1');
    $stmt->execute([$delete_id, $company_id]);
    if ($stmt->rowCount() > 0) {
        inventoryLogEvent(
            $pdo,
            'delete_defective_item',
            sprintf('Removed defective item record ID %d', $delete_id)
        );
    }
    $_SESSION['message'] = $stmt->rowCount() > 0
        ? inventoryFlashMessage('Defective item deleted.', 'danger')
        : inventoryFlashMessage('Defective item not found.', 'danger');
    header('Location: dashboard_inventory.php?section=defective');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_bom'])) {
    $deleteBomId = (int)($_POST['delete_bom_id'] ?? 0);

    if ($deleteBomId <= 0) {
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Select a valid BOM to delete.', 'warning'),
            'bom'
        );
    }

    try {
        $pdo->beginTransaction();

        $checkStmt = $pdo->prepare('SELECT id, name FROM inventory_bom WHERE id = ? AND company_id = ? LIMIT 1');
        $checkStmt->execute([$deleteBomId, $company_id]);
        $existingBom = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingBom) {
            $pdo->rollBack();
            inventoryRedirectWithMessage(
                inventoryFlashMessage('BOM record not found.', 'danger'),
                'bom'
            );
        }

        $pdo->prepare('DELETE FROM inventory_bom_items WHERE bom_id = ?')->execute([$deleteBomId]);
        $pdo->prepare('DELETE FROM inventory_bom WHERE id = ? AND company_id = ?')->execute([$deleteBomId, $company_id]);

        $pdo->commit();

        $bomName = trim($existingBom['name'] ?? 'Bill of Materials');
        inventoryLogEvent(
            $pdo,
            'delete_bom',
            sprintf('Deleted BOM "%s" (ID %d)', $bomName, $deleteBomId)
        );

        inventoryRedirectWithMessage(
            inventoryFlashMessage('Bill of Materials deleted.', 'success'),
            'bom'
        );
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('BOM deletion failed: ' . $error->getMessage());
        inventoryRedirectWithMessage(
            inventoryFlashMessage('Unable to delete BOM right now. Please try again.', 'danger'),
            'bom'
        );
    }
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$toast_message = null;
if (isset($_SESSION['toast'])) {
    $toast_message = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

$stmt = $pdo->prepare('
    SELECT 
        i.id,
        i.sku,
        i.item_name,
        i.quantity,
        i.unit,
        i.reorder_level,
        i.category,
        i.cost_price,
        i.selling_price,
        i.supplier_id,
        i.remarks,
        i.date_added,
        i.updated_at,
        CASE WHEN phi.inventory_id IS NULL THEN 0 ELSE 1 END AS pos_hidden,
        phi.hidden_at AS pos_hidden_at
    FROM inventory i
    LEFT JOIN pos_hidden_items phi ON phi.inventory_id = i.id AND phi.company_id = i.company_id
    WHERE i.company_id = ? AND (i.is_defective = 0 OR i.is_defective IS NULL)
    ORDER BY i.date_added DESC
');
$stmt->execute([$company_id]);
$inventory_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$inventory_count = count($inventory_data);

$stmt = $pdo->prepare('
    SELECT id, item_name, quantity, category, date_added, defective_reason, defective_at
    FROM inventory
    WHERE company_id = ? AND is_defective = 1
    ORDER BY defective_at DESC
');
$stmt->execute([$company_id]);
$defective_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$defective_count = count($defective_items);

$stmt = $pdo->prepare('SELECT id, vendor_name, email, contact_number, address, supplier_id, created_at FROM vendors WHERE company_id = ? ORDER BY vendor_name ASC');
$stmt->execute([$company_id]);
$vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vendor_supplier_options = [];
foreach ($vendors as $vendorRow) {
    $supplierId = isset($vendorRow['supplier_id']) ? (int)$vendorRow['supplier_id'] : 0;
    if ($supplierId <= 0) {
        continue;
    }
    if (!isset($vendor_supplier_options[$supplierId])) {
        $label = trim($vendorRow['vendor_name'] ?? '');
        $vendor_supplier_options[$supplierId] = $label !== ''
            ? sprintf('%s (ID %d)', $label, $supplierId)
            : sprintf('Supplier ID %d', $supplierId);
    }
}

try {
    $bom_stmt = $pdo->prepare('SELECT id, name, output_qty, created_at FROM inventory_bom WHERE company_id = ? ORDER BY created_at DESC');
    $bom_stmt->execute([$company_id]);
    $bom_results = $bom_stmt->fetchAll(PDO::FETCH_ASSOC);
    $bom_list = [];

    if (!empty($bom_results)) {
        $bom_ids = array_column($bom_results, 'id');
        $placeholders = implode(',', array_fill(0, count($bom_ids), '?'));
        $component_stmt = $pdo->prepare("
            SELECT bi.bom_id, i.item_name, bi.quantity_required
            FROM inventory_bom_items bi
            JOIN inventory i ON i.id = bi.inventory_id
            WHERE bi.bom_id IN ($placeholders)
            ORDER BY bi.bom_id, i.item_name
        ");
        $component_stmt->execute($bom_ids);
        $component_rows = $component_stmt->fetchAll(PDO::FETCH_ASSOC);

        $components_by_bom = [];
        foreach ($component_rows as $component) {
            $bomId = (int)$component['bom_id'];
            if (!isset($components_by_bom[$bomId])) {
                $components_by_bom[$bomId] = [];
            }
            $components_by_bom[$bomId][] = [
                'item_name' => $component['item_name'],
                'qty' => $component['quantity_required'],
            ];
        }

        foreach ($bom_results as $bom_row) {
            $bom_row['components'] = json_encode($components_by_bom[$bom_row['id']] ?? []);
            $bom_list[] = $bom_row;
        }
    }
} catch (PDOException $e) {
    error_log('Failed to fetch BOM list: ' . $e->getMessage());
    $bom_list = [];
}

$inventory_option_payload = array_map(
    static function ($item) {
        return [
            'id' => (int)$item['id'],
            'label' => trim(($item['item_name'] ?? '') . ' (' . ($item['quantity'] ?? 0) . ')'),
        ];
    },
    $inventory_data
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head Inventory Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        .inline-form {
            display: flex;
            gap: 0.35rem;
            align-items: center;
        }
        .inline-form input[type="number"] {
            width: 90px;
            padding: 6px 8px;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 12px;
            background: transparent;
            color: var(--text-primary);
        }
        .btn-link {
            background: var(--badge-bg, var(--primary));
            border: none;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 6px;
            transition: opacity 0.2s ease;
        }
        .btn-link:hover {
            text-decoration: none;
            opacity: 0.85;
        }
        .action-stack {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .edit-btn {
            border: none;
            background: var(--badge-bg, var(--primary));
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: opacity 0.2s ease;
        }
        .edit-btn:hover {
            opacity: 0.85;
        }
        .inventory-bom-name {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .inventory-bom-delete-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid var(--danger, #c9302c);
            background: transparent;
            color: var(--danger, #c9302c);
            font-size: 18px;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .inventory-bom-delete-btn:hover {
            background: var(--danger, #c9302c);
            color: #fff;
            border-color: var(--danger, #c9302c);
        }
        .alert-banner {
            margin: 12px 20px 0 20px;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
        }
        .alert-banner.danger {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .text-muted {
            color: var(--text-secondary);
            font-style: italic;
        }
        .bom-component-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .bom-component-list li {
            font-size: 13px;
            margin-bottom: 4px;
        }
        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--primary);
            color: #fff;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            font-weight: 600;
            z-index: 9999;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
        .pos-visibility-control-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-color);
        }
        .pos-filter-group {
            display: inline-flex;
            gap: 0.35rem;
            padding: 0.2rem;
            border-radius: 999px;
            background: var(--bg-secondary);
        }
        .pos-filter-btn {
            border: none;
            background: transparent;
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.3rem 0.85rem;
            border-radius: 999px;
            cursor: pointer;
            transition: var(--transition);
        }
        .pos-filter-btn.active {
            background: var(--primary);
            color: #fff;
        }
        .pos-visibility-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.15rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(16,185,129,0.15);
            color: var(--success);
        }
        .pos-visibility-badge.hidden {
            background: rgba(239,68,68,0.15);
            color: var(--danger);
        }
        .pos-hidden-row td {
            background: rgba(239,68,68,0.05);
        }
        .pos-visibility-form button {
            border: none;
            background: var(--bg-secondary);
            color: var(--text-primary);
            padding: 0.3rem 0.55rem;
            border-radius: var(--radius-sm, 6px);
            cursor: pointer;
        }
        .pos-visibility-form button:hover {
            background: var(--primary);
            color: #fff;
        }
    </style>
    <script>
        window.__actionFeedback = (function () {
            const STORAGE_KEY = 'erpActionFeedback';
            const ICONS = {
                success: '✔',
                danger: '✖',
                warning: '⚠',
                info: 'ℹ'
            };
            const TITLES = {
                success: 'Success',
                danger: 'Action Completed',
                warning: 'Heads Up',
                info: 'Notice'
            };
            const DEFAULT_DURATION = 4200;
            const state = { overlay: null, timeout: null };

            const normalizeConfig = (config = {}) => {
                if (!config || typeof config !== 'object') {
                    return { duration: DEFAULT_DURATION };
                }
                const clone = { ...config };
                clone.duration = typeof clone.duration === 'number' ? clone.duration : DEFAULT_DURATION;
                if (clone.duration < 800) {
                    clone.duration = DEFAULT_DURATION;
                }
                clone.title = typeof clone.title === 'string' && clone.title.trim() !== '' ? clone.title.trim() : null;
                clone.defer = clone.defer !== false;
                return clone;
            };

            const hide = () => {
                const overlay = state.overlay;
                if (!overlay) {
                    return;
                }
                overlay.classList.remove('visible');
                overlay.setAttribute('aria-hidden', 'true');
                if (state.timeout) {
                    clearTimeout(state.timeout);
                    state.timeout = null;
                }
                setTimeout(() => {
                    if (overlay === state.overlay) {
                        overlay.innerHTML = '';
                    }
                }, 220);
            };

            const ensureOverlay = () => {
                if (state.overlay && document.body.contains(state.overlay)) {
                    return state.overlay;
                }
                const overlay = document.createElement('div');
                overlay.className = 'action-overlay';
                overlay.setAttribute('role', 'alertdialog');
                overlay.setAttribute('aria-live', 'assertive');
                overlay.setAttribute('aria-hidden', 'true');
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        hide();
                    }
                });
                document.body.appendChild(overlay);
                state.overlay = overlay;
                return overlay;
            };

            const buildCard = (message, type, titleText, opts = {}) => {
                const card = document.createElement('div');
                card.className = `action-overlay-card action-overlay-${type}`;

                const iconWrap = document.createElement('span');
                iconWrap.className = 'action-overlay-icon';
                iconWrap.textContent = ICONS[type] || ICONS.info;

                const contentWrap = document.createElement('div');
                contentWrap.className = 'action-overlay-content';
                if (titleText) {
                    const titleEl = document.createElement('div');
                    titleEl.className = 'action-overlay-title';
                    titleEl.textContent = titleText;
                    contentWrap.appendChild(titleEl);
                }
                const bodyEl = document.createElement('div');
                bodyEl.className = 'action-overlay-message';
                bodyEl.textContent = message;
                contentWrap.appendChild(bodyEl);

                const actionsEl = document.createElement('div');
                actionsEl.className = 'action-overlay-actions';
                const okBtn = document.createElement('button');
                okBtn.type = 'button';
                okBtn.className = 'action-overlay-btn action-overlay-btn-primary';
                okBtn.textContent = opts.okLabel || 'Okay';
                okBtn.addEventListener('click', hide);
                actionsEl.appendChild(okBtn);
                contentWrap.appendChild(actionsEl);

                const dismissBtn = document.createElement('button');
                dismissBtn.type = 'button';
                dismissBtn.className = 'action-overlay-dismiss';
                dismissBtn.setAttribute('aria-label', 'Dismiss notification');
                dismissBtn.innerHTML = '&times;';
                dismissBtn.addEventListener('click', hide);

                card.appendChild(iconWrap);
                card.appendChild(contentWrap);
                card.appendChild(dismissBtn);
                return card;
            };

            const show = (message, type = 'success', config) => {
                if (!message) {
                    return;
                }
                const opts = normalizeConfig(config);
                const duration = Math.max(800, opts.duration || DEFAULT_DURATION);
                const titleText = opts.title || TITLES[type] || TITLES.info;
                const overlay = ensureOverlay();
                if (!overlay) {
                    document.addEventListener('DOMContentLoaded', () => show(message, type, opts), { once: true });
                    return;
                }
                overlay.innerHTML = '';
                overlay.appendChild(buildCard(message, type, titleText, opts));
                overlay.setAttribute('aria-hidden', 'false');
                requestAnimationFrame(() => overlay.classList.add('visible'));
                if (state.timeout) {
                    clearTimeout(state.timeout);
                }
                state.timeout = setTimeout(() => hide(), duration);
            };

            const queue = (message, type = 'success', options = {}) => {
                if (!message) {
                    return;
                }
                const payload = {
                    message,
                    type,
                    title: options.title || null,
                    duration: options.duration || DEFAULT_DURATION,
                    timestamp: Date.now()
                };
                if (options.defer === false) {
                    show(message, type, payload);
                    return;
                }
                try {
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                } catch (error) {
                    console.warn('Action feedback storage failed.', error);
                }
            };

            const consume = () => {
                try {
                    const raw = sessionStorage.getItem(STORAGE_KEY);
                    if (!raw) {
                        return null;
                    }
                    sessionStorage.removeItem(STORAGE_KEY);
                    return JSON.parse(raw);
                } catch (error) {
                    console.warn('Action feedback retrieval failed.', error);
                    return null;
                }
            };

            const api = { queue, consume, show, hide, key: STORAGE_KEY };
            window.queueActionMessage = queue;
            window.flashActionMessage = (message, type = 'success', options) => show(message, type, options);
            return api;
        })();
    </script>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon"><i class="fas fa-boxes"></i></div>
            <span><?= htmlspecialchars($_SESSION['company'] ?? '') ?></span>
        </div>
        <div class="sidebar-user">Code: <?= htmlspecialchars($_SESSION['company_code'] ?? '') ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Inventory</div>
            <a href="dashboard_inventory.php?section=inventory" class="nav-item <?= $section === 'inventory' ? 'active' : '' ?>">
                <i class="fas fa-boxes nav-icon"></i>
                <span>Main Inventory</span>
            </a>
            <a href="dashboard_inventory.php?section=defective" class="nav-item <?= $section === 'defective' ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle nav-icon"></i>
                <span>Defective Items</span>
            </a>
            <a href="dashboard_inventory.php?section=bom" class="nav-item <?= $section === 'bom' ? 'active' : '' ?>">
                <i class="fas fa-layer-group nav-icon"></i>
                <span>Bill of Materials</span>
            </a>
            <a href="dashboard_inventory.php?section=vendors" class="nav-item <?= $section === 'vendors' ? 'active' : '' ?>">
                <i class="fas fa-truck nav-icon"></i>
                <span>Vendors</span>
            </a>
            <a href="dashboard_inventory.php?section=reports" class="nav-item <?= $section === 'reports' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar nav-icon"></i>
                <span>Reports</span>
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
                <button class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="search-box">
                    <input type="text" id="tableSearch" placeholder="Search...">
                    <i class="fas fa-search search-icon"></i>
                </div>
            </div>
        </div>
    </header>

    <div class="content">
        <?php if ($section === 'inventory'): ?>
            <div class="content-grid inventory-layout">
                <div>
                    <div class="card inventory-main-card">
                        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                            <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                                <div class="card-title"><i class="fas fa-boxes"></i> Inventory</div>
                                <span class="card-badge"><span id="inventoryCount"><?= $inventory_count ?></span> Items</span>
                                <span class="card-badge"><span id="defectiveCount"><?= $defective_count ?></span> Defective</span>
                            </div>
                            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <button type="button" class="action-btn" style="background:var(--primary);" onclick="openAddItemModal()"><i class="fas fa-plus"></i> Add Item</button>
                                <button type="button" class="action-btn" style="background:var(--primary);" onclick="openImportModal()"><i class="fas fa-file-import"></i> Import CSV</button>
                            </div>
                        </div>

                        <form id="inventoryBulkDeleteForm" method="POST" style="display:none;">
                            <input type="hidden" name="bulk_delete_inventory" value="1">
                        </form>

                        <?php if (!empty($inventory_add_error)): ?>
                            <div class="alert-banner danger"><?= htmlspecialchars($inventory_add_error) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($inventory_edit_error)): ?>
                            <div class="alert-banner danger"><?= htmlspecialchars($inventory_edit_error) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($inventory_adjust_error)): ?>
                            <div class="alert-banner danger"><?= htmlspecialchars($inventory_adjust_error) ?></div>
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="table-container">
                                <div class="pos-visibility-control-bar">
                                    <div style="display:flex;flex-direction:column;gap:0.25rem;">
                                        <span style="font-size:0.85rem;font-weight:600;color:var(--text-secondary);">POS Visibility Filter</span>
                                        <div class="pos-filter-group">
                                            <button type="button" class="pos-filter-btn active" data-pos-filter="all">All</button>
                                        </div>
                                    </div>
                                    <button type="submit" form="inventoryBulkDeleteForm" id="inventoryBulkDeleteBtn" class="action-btn" style="background:var(--danger);opacity:0.6;cursor:not-allowed;" disabled>
                                        <i class="fas fa-trash"></i> Delete Selected
                                    </button>
                                </div>
                                <table class="data-table" id="inventoryTable">
                                    <thead>
                                        <tr>
                                            <th style="width:36px;text-align:center;">
                                                <input type="checkbox" id="inventorySelectAll" aria-label="Select all inventory items">
                                            </th>
                                            <th>SKU</th>
                                            <th>Date</th>
                                            <th>Item Name</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Cost</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Reorder Level</th>
                                            <th>Supplier ID</th>
                                            <th>POS Visibility</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inventory_data as $item): ?>
                                            <?php
                                            $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                                            $reorder = isset($item['reorder_level']) && $item['reorder_level'] !== '' ? (int)$item['reorder_level'] : null;
                                            $isHiddenFromPos = isset($item['pos_hidden']) && (int)$item['pos_hidden'] === 1;
                                            if ($qty <= 0) {
                                                $status_text = 'Out of Stock';
                                                $status_class = 'out';
                                            } elseif ($reorder !== null && $qty <= $reorder) {
                                                $status_text = 'Low Stock';
                                                $status_class = 'low';
                                            } elseif ($reorder !== null && $qty <= ($reorder * 2)) {
                                                $status_text = 'Reorder Soon';
                                                $status_class = 'low';
                                            } else {
                                                $status_text = 'In Stock';
                                                $status_class = 'plenty';
                                            }
                                            ?>
                                            <tr class="pos-visibility-row <?= $isHiddenFromPos ? 'pos-hidden-row' : '' ?>" data-visibility="<?= $isHiddenFromPos ? 'hidden' : 'visible' ?>">
                                                <td style="text-align:center;">
                                                    <input type="checkbox" class="inventory-row-checkbox" form="inventoryBulkDeleteForm" name="inventory_ids[]" value="<?= (int)$item['id'] ?>" aria-label="Select <?= htmlspecialchars($item['item_name'] ?? 'inventory item') ?>">
                                                </td>
                                                <td><?= htmlspecialchars($item['sku'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($item['date_added'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($item['item_name'] ?? '') ?></td>
                                                <td>
                                                    <div style="display:flex;flex-direction:column;gap:0.35rem;">
                                                        <span style="font-weight:600;"><?= $qty ?></span>
                                                        <form method="POST" class="inline-form">
                                                            <input type="hidden" name="inventory_id" value="<?= (int)$item['id'] ?>">
                                                            <input type="number" name="add_quantity" placeholder="+/− Qty" inputmode="numeric">
                                                            <button type="submit" name="increase_inventory_qty" class="btn-link">Add</button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($item['unit'] ?? '') ?></td>
                                                <td><?= isset($item['cost_price']) ? '₱' . number_format((float)$item['cost_price'], 2) : '' ?></td>
                                                <td><?= isset($item['selling_price']) ? '₱' . number_format((float)$item['selling_price'], 2) : '' ?></td>
                                                <td><span class="badge-status <?= $status_class ?>"><?= $status_text ?></span></td>
                                                <td><?= htmlspecialchars($item['reorder_level'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($item['supplier_id'] ?? '') ?></td>
                                                <td>
                                                    <span class="pos-visibility-badge <?= $isHiddenFromPos ? 'hidden' : 'visible' ?>">
                                                        <i class="fas <?= $isHiddenFromPos ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                                        <?= $isHiddenFromPos ? 'Hidden from POS' : 'Visible in POS' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-stack">
                                                        <form method="POST" class="pos-visibility-form">
                                                            <input type="hidden" name="pos_visibility_action" value="<?= $isHiddenFromPos ? 'show' : 'hide' ?>">
                                                            <input type="hidden" name="inventory_id" value="<?= (int)$item['id'] ?>">
                                                            <button type="submit" title="<?= $isHiddenFromPos ? 'Show in POS' : 'Hide from POS' ?>">
                                                                <i class="fas <?= $isHiddenFromPos ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" class="edit-btn" onclick='openInventoryEditModal(<?= json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                                                        <button type="button" class="edit-btn" onclick='openMarkDefectiveModal(<?= json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-wrench"></i></button>
                                                        <button type="button" class="action-btn" onclick="openDeleteModal(<?= (int)$item['id'] ?>, '<?= htmlspecialchars($item['item_name'] ?? '', ENT_QUOTES) ?>')"><i class="fas fa-trash-alt"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($inventory_data)): ?>
                                            <tr>
                                                <td colspan="13" style="text-align:center;padding:2rem;">No inventory items found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ($section === 'defective'): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-exclamation-circle"></i> Defective Items</div>
                    <span class="card-badge"> <?= $defective_count ?> Defective</span>
                </div>
                <div class="card-body">
                    <div style="display:flex;justify-content:flex-end;margin-bottom:0.75rem;">
                        <button type="submit" form="defectiveBulkDeleteForm" id="defectiveBulkDeleteBtn" class="action-btn" style="background:var(--danger);opacity:0.6;cursor:not-allowed;" disabled>
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>

                    <form id="defectiveBulkDeleteForm" method="POST" style="display:none;">
                        <input type="hidden" name="bulk_delete_defective" value="1">
                    </form>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width:36px;text-align:center;">
                                        <input type="checkbox" id="defectiveSelectAll" aria-label="Select all defective items">
                                    </th>
                                    <th>Marked At</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Category</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($defective_items as $defective): ?>
                                    <tr>
                                        <td style="text-align:center;">
                                            <input type="checkbox" class="defective-row-checkbox" form="defectiveBulkDeleteForm" name="defective_ids[]" value="<?= (int)$defective['id'] ?>" aria-label="Select <?= htmlspecialchars($defective['item_name'] ?? 'defective item') ?>">
                                        </td>
                                        <td><?= htmlspecialchars($defective['defective_at'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($defective['item_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($defective['quantity'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($defective['category'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($defective['defective_reason'] ?? 'N/A') ?></td>
                                        <td>
                                            <div class="action-stack">
                                                <button type="button" class="edit-btn" onclick="restoreDefective(<?= (int)$defective['id'] ?>)"><i class="fas fa-undo"></i> Restore</button>
                                                <button type="button" class="action-btn" onclick="deleteDefective(<?= (int)$defective['id'] ?>, '<?= htmlspecialchars($defective['item_name'] ?? '', ENT_QUOTES) ?>')"><i class="fas fa-trash-alt"></i> Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($defective_items)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;padding:2rem;">No defective items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($section === 'bom'): ?>
            <div class="content-grid" style="grid-template-columns:2fr 1fr;">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-layer-group"></i> Bill of Materials</div>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>BOM Name</th>
                                        <th>Output Qty</th>
                                        <th>Components</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bom_list as $bom): ?>
                                        <?php $components = $bom['components'] ? json_decode($bom['components'], true) : []; ?>
                                        <tr>
                                            <td>
                                                <div class="inventory-bom-name">
                                                    <button type="button"
                                                        class="inventory-bom-delete-btn"
                                                        data-bom-id="<?= (int)($bom['id'] ?? 0) ?>"
                                                        data-bom-name="<?= htmlspecialchars($bom['name'] ?? '', ENT_QUOTES) ?>"
                                                        title="Delete BOM"
                                                        aria-label="Delete BOM <?= htmlspecialchars($bom['name'] ?? '') ?>"
                                                    >&minus;</button>
                                                    <span><?= htmlspecialchars($bom['name'] ?? '') ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($bom['output_qty'] ?? '') ?></td>
                                            <td>
                                                <?php if (!empty($components)): ?>
                                                    <ul class="bom-component-list">
                                                        <?php foreach ($components as $component): ?>
                                                            <li><?= htmlspecialchars(($component['item_name'] ?? 'Unknown') . ' — ' . ($component['qty'] ?? 0)) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <span class="text-muted">No components</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($bom['created_at'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($bom_list)): ?>
                                        <tr>
                                            <td colspan="4" style="text-align:center;padding:2rem;">No BOM records yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="form-card">
                    <div class="form-title">Create BOM</div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="bom_name">BOM Name</label>
                            <input type="text" name="bom_name" id="bom_name" required>
                        </div>
                        <div class="form-group">
                            <label for="bom_output_qty">Output Quantity</label>
                            <input type="number" name="bom_output_qty" id="bom_output_qty" min="1" value="1" required>
                        </div>
                        <div id="bomComponents">
                            <div class="form-group bom-component">
                                <label>Component</label>
                                <select name="components[0][inventory_id]" required>
                                    <option value="">Select item…</option>
                                    <?php foreach ($inventory_data as $item): ?>
                                        <option value="<?= (int)$item['id'] ?>"><?= htmlspecialchars(($item['item_name'] ?? 'Unknown') . ' (' . ($item['quantity'] ?? 0) . ')') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" step="0.01" min="0.01" name="components[0][qty]" placeholder="Qty needed" required>
                                <button type="button" class="action-btn" style="margin-top:0.5rem;width:max-content;" onclick="removeBomComponent(this)">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn-primary-compact" style="margin-bottom:0.75rem;" onclick="addBomComponent()">Add Component</button>
                        <button type="submit" name="add_bom" class="btn-primary">Save BOM</button>
                    </form>
                </div>
            </div>
        <?php elseif ($section === 'vendors'): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-truck"></i> Vendors</div>
                    <button type="button" class="action-btn" style="background:var(--primary);" onclick="openAddVendorModal()"><i class="fas fa-plus"></i> Add Vendor</button>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Supplier ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vendors as $vendor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($vendor['vendor_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($vendor['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($vendor['contact_number'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($vendor['address'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($vendor['supplier_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <button type="button" class="edit-btn" onclick='openEditVendorModal(<?= json_encode($vendor, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                                            <button type="button" class="action-btn" onclick="openVendorDelete(<?= (int)$vendor['id'] ?>)"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($vendors)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center;padding:2rem;">No vendors linked yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-chart-bar"></i> Reports</div>
                </div>
                <div class="card-body">
                    <p>Report generation will be available soon.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;">
        <h3 class="modal-title" style="text-align:left;"><i class="fas fa-file-import"></i> Import Inventory (CSV)</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="import_file">Choose CSV file</label>
                <input type="file" id="import_file" name="import_file" accept=".csv" required>
            </div>
            <div class="modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-secondary" onclick="closeImportModal()">Cancel</button>
                <button type="submit" name="import_inventory" class="btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;max-height:80vh;overflow-y:auto;">
        <h3 class="modal-title" style="text-align:left;"><i class="fas fa-plus"></i> Add Inventory Item</h3>
        <form method="POST">
            <div class="form-group">
                <label for="modal_sku">SKU *</label>
                <input type="text" id="modal_sku" name="sku" required>
            </div>
            <div class="form-group">
                <label for="modal_item_name">Item Name *</label>
                <input type="text" id="modal_item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="modal_quantity">Quantity *</label>
                <input type="number" id="modal_quantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="modal_unit">Unit *</label>
                <input type="text" id="modal_unit" name="unit" required>
            </div>
            <div class="form-group">
                <label for="modal_reorder">Reorder Level *</label>
                <input type="number" id="modal_reorder" name="reorder_level" min="0" required>
            </div>
            <div class="form-group">
                <label for="modal_category">Category *</label>
                <input type="text" id="modal_category" name="category" required>
            </div>
            <div class="form-group">
                <label for="modal_cost_price">Cost Price *</label>
                <input type="number" step="0.01" min="0" id="modal_cost_price" name="cost_price" required>
            </div>
            <div class="form-group">
                <label for="modal_selling_price">Selling Price *</label>
                <input type="number" step="0.01" min="0" id="modal_selling_price" name="selling_price" required>
            </div>
            <div class="form-group">
                <label for="modal_supplier_id">Supplier ID *</label>
                <?php if (!empty($vendor_supplier_options)): ?>
                    <select id="modal_supplier_id" name="supplier_id" required>
                        <option value="">Select vendor…</option>
                        <?php foreach ($vendor_supplier_options as $supplierId => $label): ?>
                            <option value="<?= (int)$supplierId ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="number" id="modal_supplier_id" name="supplier_id" min="1" required>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="modal_date_added">Date Added *</label>
                <input type="date" id="modal_date_added" name="date_added" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-secondary" onclick="closeAddItemModal()">Cancel</button>
                <button type="submit" name="add_inventory" class="btn-primary">Add Item</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="editItemModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;max-height:80vh;overflow-y:auto;">
        <h3 class="modal-title" style="text-align:left;"><i class="fas fa-edit"></i> Edit Inventory Item</h3>
        <form method="POST">
            <input type="hidden" name="update_inventory_item" value="1">
            <input type="hidden" id="edit_inventory_id" name="inventory_id" value="">
            <div class="form-group">
                <label for="edit_modal_sku">SKU *</label>
                <input type="text" id="edit_modal_sku" name="sku" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_item_name">Item Name *</label>
                <input type="text" id="edit_modal_item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_quantity">Quantity *</label>
                <input type="number" id="edit_modal_quantity" name="quantity" min="0" required readonly>
            </div>
            <div class="form-group">
                <label for="edit_modal_unit">Unit *</label>
                <input type="text" id="edit_modal_unit" name="unit" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_reorder">Reorder Level *</label>
                <input type="number" id="edit_modal_reorder" name="reorder_level" min="0" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_cost_price">Cost Price *</label>
                <input type="number" step="0.01" min="0" id="edit_modal_cost_price" name="cost_price" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_selling_price">Selling Price *</label>
                <input type="number" step="0.01" min="0" id="edit_modal_selling_price" name="selling_price" required>
            </div>
            <div class="form-group">
                <label for="edit_modal_date_added">Date *</label>
                <input type="date" id="edit_modal_date_added" name="date_added" required>
            </div>
            <div class="modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-secondary" onclick="closeInventoryEditModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

    <!-- Mark Defective Modal -->
    <div id="markDefectiveModal" class="modal-overlay" style="display:none;">
        <div class="modal-box" style="max-width:420px;max-height:80vh;overflow-y:auto;">
            <h3 class="modal-title" style="text-align:left;"><i class="fas fa-wrench"></i> Mark as Defective</h3>
            <form method="POST">
                <input type="hidden" name="mark_defective" value="1">
                <input type="hidden" id="defective_inventory_id" name="inventory_id">
                <div class="form-group">
                    <label for="defective_item_name">Item</label>
                    <input type="text" id="defective_item_name" disabled>
                </div>
                <div class="form-group">
                    <label for="defective_available_qty">Available Quantity</label>
                    <input type="text" id="defective_available_qty" disabled>
                </div>
                <div class="form-group">
                    <label for="defective_quantity">Defective Quantity *</label>
                    <input type="number" id="defective_quantity" name="defective_quantity" min="1" required>
                    <small style="display:block;margin-top:4px;color:var(--text-secondary);">Cannot exceed available stock.</small>
                </div>
                <div class="form-group">
                    <label for="defective_reason">Reason</label>
                    <textarea id="defective_reason" name="defective_reason" rows="2" placeholder="Describe the issue..."></textarea>
                </div>
                <div class="modal-actions" style="justify-content:flex-end;">
                    <button type="button" class="btn-secondary" onclick="closeMarkDefectiveModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

<!-- Add Vendor Modal -->
<div id="addVendorModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;max-height:80vh;overflow-y:auto;">
        <h3 class="modal-title" style="text-align:left;"><i class="fas fa-plus"></i> Add Vendor</h3>
        <form method="POST">
            <input type="hidden" name="add_vendor" value="1">
            <div class="form-group">
                <label for="vendor_name">Vendor Name *</label>
                <input type="text" id="vendor_name" name="vendor_name" required>
            </div>
            <div class="form-group">
                <label for="vendor_email">Email</label>
                <input type="email" id="vendor_email" name="vendor_email">
            </div>
            <div class="form-group">
                <label for="vendor_contact">Contact Number</label>
                <input type="text" id="vendor_contact" name="vendor_contact">
            </div>
            <div class="form-group">
                <label for="vendor_address">Address</label>
                <textarea id="vendor_address" name="vendor_address" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="vendor_supplier_id">Supplier ID</label>
                <input type="number" id="vendor_supplier_id" name="supplier_id" min="0">
            </div>
            <div class="modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-secondary" onclick="closeAddVendorModal()">Cancel</button>
                <button type="submit" class="btn-primary">Add Vendor</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Vendor Modal -->
<div id="editVendorModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" style="max-width:480px;max-height:80vh;overflow-y:auto;">
        <h3 class="modal-title" style="text-align:left;"><i class="fas fa-edit"></i> Edit Vendor</h3>
        <form method="POST">
            <input type="hidden" name="update_vendor" value="1">
            <input type="hidden" id="edit_vendor_id" name="vendor_id" value="">
            <div class="form-group">
                <label for="edit_vendor_name">Vendor Name *</label>
                <input type="text" id="edit_vendor_name" name="vendor_name" required>
            </div>
            <div class="form-group">
                <label for="edit_vendor_email">Email</label>
                <input type="email" id="edit_vendor_email" name="vendor_email">
            </div>
            <div class="form-group">
                <label for="edit_vendor_contact">Contact Number</label>
                <input type="text" id="edit_vendor_contact" name="vendor_contact">
            </div>
            <div class="form-group">
                <label for="edit_vendor_address">Address</label>
                <textarea id="edit_vendor_address" name="vendor_address" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="edit_vendor_supplier_id">Supplier ID</label>
                <input type="number" id="edit_vendor_supplier_id" name="supplier_id" min="0">
            </div>
            <div class="modal-actions" style="justify-content:flex-end;">
                <button type="button" class="btn-secondary" onclick="closeEditVendorModal()">Cancel</button>
                <button type="submit" class="btn-primary">Update Vendor</button>
            </div>
        </form>
    </div>
</div>

<form id="inventoryDeleteBomForm" method="POST" style="display:none;">
    <input type="hidden" name="delete_bom" value="1">
    <input type="hidden" name="delete_bom_id" id="inventoryDeleteBomId" value="">
</form>

<div id="inventoryDeleteBomModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="inventoryDeleteBomTitle">
        <div class="modal-header" id="inventoryDeleteBomTitle">Delete BOM</div>
        <div class="modal-body">
            <p id="inventoryDeleteBomMessage">Do you want to delete this BOM?</p>
        </div>
        <div class="modal-actions" style="justify-content:center; gap:0.5rem;">
            <button type="button" id="inventoryDeleteBomCancel" class="btn-secondary">Cancel</button>
            <button type="button" id="inventoryDeleteBomConfirm" class="btn-danger">Confirm</button>
        </div>
    </div>
</div>

<div id="deleteDefectiveModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteDefectiveTitle">
        <div class="modal-header" id="deleteDefectiveTitle">Delete Defective Item</div>
        <div class="modal-body">
            <p id="deleteDefectiveMessage">Delete defective record?</p>
        </div>
        <div class="modal-actions" style="justify-content:center; gap:0.5rem;">
            <button type="button" id="deleteDefectiveCancel" class="btn-secondary">Cancel</button>
            <button type="button" id="deleteDefectiveConfirm" class="btn-danger">Delete</button>
        </div>
    </div>
</div>

<div id="deleteInventoryModal" class="modal-overlay" style="display:none;">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteInventoryTitle">
        <div class="modal-header" id="deleteInventoryTitle">Delete Inventory Item</div>
        <div class="modal-body">
            <p id="deleteInventoryMessage">Delete this inventory item?</p>
        </div>
        <div class="modal-actions" style="justify-content:center; gap:0.5rem;">
            <button type="button" id="deleteInventoryCancel" class="btn-secondary">Cancel</button>
            <button type="button" id="deleteInventoryConfirm" class="btn-danger">Delete</button>
        </div>
    </div>
</div>

<script>
const inventoryOptions = <?= json_encode($inventory_option_payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const toastMessage = <?= json_encode($toast_message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const actionFeedbackPayload = <?= json_encode($message, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
let bomComponentIndex = 1;
let pendingDefectiveDeleteId = null;
let pendingInventoryDeleteId = null;

function openAddItemModal() {
    document.getElementById('addItemModal').style.display = 'flex';
}
function closeAddItemModal() {
    document.getElementById('addItemModal').style.display = 'none';
}
function openInventoryEditModal(item) {
    const modal = document.getElementById('editItemModal');
    document.getElementById('edit_inventory_id').value = item.id || '';
    document.getElementById('edit_modal_sku').value = item.sku || '';
    document.getElementById('edit_modal_item_name').value = item.item_name || '';
    document.getElementById('edit_modal_quantity').value = item.quantity ?? '';
    document.getElementById('edit_modal_unit').value = item.unit || '';
    document.getElementById('edit_modal_reorder').value = item.reorder_level ?? '';
    document.getElementById('edit_modal_cost_price').value = item.cost_price ?? '';
    document.getElementById('edit_modal_selling_price').value = item.selling_price ?? '';
    document.getElementById('edit_modal_date_added').value = (item.date_added || '').split(' ')[0] || '';
    modal.style.display = 'flex';
}
function closeInventoryEditModal() {
    document.getElementById('editItemModal').style.display = 'none';
}
function openImportModal() {
    document.getElementById('importModal').style.display = 'flex';
}
function closeImportModal() {
    document.getElementById('importModal').style.display = 'none';
}
function addBomComponent() {
    const container = document.getElementById('bomComponents');
    if (!container) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'form-group bom-component';
    const selectOptions = ['<option value="">Select item…</option>']
        .concat(inventoryOptions.map(option => `<option value="${option.id}">${option.label}</option>`))
        .join('');
    wrapper.innerHTML = `
        <label>Component</label>
        <select name="components[${bomComponentIndex}][inventory_id]" required>${selectOptions}</select>
        <input type="number" step="0.01" min="0.01" name="components[${bomComponentIndex}][qty]" placeholder="Qty needed" required>
        <button type="button" class="action-btn" style="margin-top:0.5rem;width:max-content;" onclick="removeBomComponent(this)">Remove</button>
    `;
    container.appendChild(wrapper);
    bomComponentIndex += 1;
}
function removeBomComponent(trigger) {
    const container = document.getElementById('bomComponents');
    if (!container) {
        return;
    }
    const target = trigger?.closest('.bom-component');
    if (!target) {
        return;
    }
    const components = container.querySelectorAll('.bom-component');
    if (components.length <= 1) {
        const select = target.querySelector('select');
        const qtyInput = target.querySelector('input[type="number"]');
        if (select) {
            select.value = '';
        }
        if (qtyInput) {
            qtyInput.value = '';
        }
        return;
    }
    target.remove();
}
function openAddVendorModal() {
    document.getElementById('addVendorModal').style.display = 'flex';
}
function closeAddVendorModal() {
    document.getElementById('addVendorModal').style.display = 'none';
}
function openEditVendorModal(vendor) {
    document.getElementById('edit_vendor_id').value = vendor.id || '';
    document.getElementById('edit_vendor_name').value = vendor.vendor_name || '';
    document.getElementById('edit_vendor_email').value = vendor.email || '';
    document.getElementById('edit_vendor_contact').value = vendor.contact_number || '';
    document.getElementById('edit_vendor_address').value = vendor.address || '';
    document.getElementById('edit_vendor_supplier_id').value = vendor.supplier_id || '';
    document.getElementById('editVendorModal').style.display = 'flex';
}
function closeEditVendorModal() {
    document.getElementById('editVendorModal').style.display = 'none';
}
function openVendorDelete(vendorId) {
    if (confirm('Delete this vendor?')) {
        window.location.href = `dashboard_inventory.php?delete_vendor=${vendorId}`;
    }
}
function openDeleteModal(itemId, itemName) {
    const modal = document.getElementById('deleteInventoryModal');
    const messageEl = document.getElementById('deleteInventoryMessage');
    pendingInventoryDeleteId = itemId || null;
    if (messageEl) {
        const label = itemName && itemName.trim() !== '' ? `"${itemName}"` : 'this item';
        messageEl.textContent = `Delete ${label}? This action cannot be undone.`;
    }
    if (modal) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }
}
function openMarkDefectiveModal(item) {
    const modal = document.getElementById('markDefectiveModal');
    if (!modal) return;

    const qty = Number(item.quantity ?? 0);
    document.getElementById('defective_inventory_id').value = item.id || '';
    document.getElementById('defective_item_name').value = item.item_name || '';
    document.getElementById('defective_available_qty').value = qty;

    const qtyInput = document.getElementById('defective_quantity');
    if (qtyInput) {
        qtyInput.value = qty > 0 ? 1 : 0;
        qtyInput.max = qty;
        qtyInput.dataset.maxQty = qty;
        qtyInput.disabled = qty <= 0;
    }

    const reasonField = document.getElementById('defective_reason');
    if (reasonField) {
        reasonField.value = '';
    }

    const confirmBtn = modal.querySelector('button.btn-primary');
    if (confirmBtn) {
        confirmBtn.disabled = qty <= 0;
    }

    modal.style.display = 'flex';
}
function closeMarkDefectiveModal() {
    const modal = document.getElementById('markDefectiveModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
function enforceDefectiveQuantityLimit() {
    const input = document.getElementById('defective_quantity');
    if (!input) return;
    const rawValue = input.value;
    if (rawValue === '' || input.disabled) {
        return;
    }
    const maxQty = Number(input.dataset.maxQty || 0);
    let current = Number(rawValue) || 0;
    if (maxQty > 0 && current > maxQty) {
        input.value = maxQty;
        current = maxQty;
    }
    if (current < 1) {
        input.value = 1;
    }
}
function restoreDefective(itemId) {
    if (confirm('Restore this defective item back to inventory?')) {
        window.location.href = `dashboard_inventory.php?section=defective&restore_defective=${itemId}`;
    }
}
function deleteDefective(itemId, itemName) {
    const modal = document.getElementById('deleteDefectiveModal');
    const messageEl = document.getElementById('deleteDefectiveMessage');
    pendingDefectiveDeleteId = itemId || null;
    if (messageEl) {
        const label = itemName && itemName.trim() !== '' ? `"${itemName}"` : 'this item';
        messageEl.textContent = `Delete defective record for ${label}? This cannot be undone.`;
    }
    if (modal) {
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }
}
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('dashboardTheme', newTheme);
    const icon = document.getElementById('themeToggle').querySelector('i');
    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}
function showToast(message, timeout = 3000) {
    if (!message) return;
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 200);
    }, timeout);
}

function attachOverlayClose(id, closeFn) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeFn();
        }
    });
}

// --- Global search box filtering across inventory tables ---
(function initInventorySearchBox() {
    const debounce = (fn, wait) => {
        let t;
        return (...args) => {

    (function initInventoryBulkDeleteControls() {
        const setupBulkDelete = ({ formId, buttonId, selectAllId, checkboxSelector, singularLabel, pluralLabel }) => {
            const bulkForm = document.getElementById(formId);
            const bulkButton = document.getElementById(buttonId);
            if (!bulkForm || !bulkButton) { return; }

            const selectAll = selectAllId ? document.getElementById(selectAllId) : null;
            const getRowCheckboxes = () => Array.from(document.querySelectorAll(checkboxSelector));

            const updateState = () => {
                const rowCheckboxes = getRowCheckboxes();
                const checkedCount = rowCheckboxes.filter(cb => cb.checked).length;
                bulkButton.disabled = checkedCount === 0;
                bulkButton.style.opacity = checkedCount === 0 ? '0.6' : '1';
                bulkButton.style.cursor = checkedCount === 0 ? 'not-allowed' : 'pointer';

                if (selectAll) {
                    const total = rowCheckboxes.length;
                    selectAll.checked = checkedCount > 0 && checkedCount === total && total > 0;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < total;
                    if (checkedCount === 0) {
                        selectAll.indeterminate = false;
                    }
                }
            };

            document.addEventListener('change', (event) => {
                if (event.target && event.target.matches(checkboxSelector)) {
                    updateState();
                }
            });

            selectAll?.addEventListener('change', () => {
                const rowCheckboxes = getRowCheckboxes();
                rowCheckboxes.forEach(cb => { cb.checked = selectAll.checked; });
                updateState();
            });

            bulkForm.addEventListener('submit', (event) => {
                const rowCheckboxes = getRowCheckboxes();
                const checkedCount = rowCheckboxes.filter(cb => cb.checked).length;
                if (checkedCount === 0) {
                    event.preventDefault();
                    return;
                }
                const label = checkedCount === 1 ? singularLabel : pluralLabel;
                const confirmation = checkedCount === 1
                    ? `Delete the selected ${label}? This action cannot be undone.`
                    : `Delete ${checkedCount} selected ${label}? This action cannot be undone.`;
                if (!window.confirm(confirmation)) {
                    event.preventDefault();
                }
            });

            updateState();
        };

        setupBulkDelete({
            formId: 'inventoryBulkDeleteForm',
            buttonId: 'inventoryBulkDeleteBtn',
            selectAllId: 'inventorySelectAll',
            checkboxSelector: '.inventory-row-checkbox',
            singularLabel: 'inventory item',
            pluralLabel: 'inventory items'
        });

        setupBulkDelete({
            formId: 'defectiveBulkDeleteForm',
            buttonId: 'defectiveBulkDeleteBtn',
            selectAllId: 'defectiveSelectAll',
            checkboxSelector: '.defective-row-checkbox',
            singularLabel: 'defective item',
            pluralLabel: 'defective items'
        });
    })();
            clearTimeout(t);
            t = setTimeout(() => fn.apply(null, args), wait);
        };
    };

    const ensureNoResultsRow = (table) => {
        const tbody = table.tBodies[0];
        if (!tbody) {
            return null;
        }
        let marker = tbody.querySelector('.no-results-row');
        if (!marker) {
            marker = document.createElement('tr');
            marker.className = 'no-results-row';
            const columnCount = table.tHead ? table.tHead.rows[0].cells.length : tbody.rows[0]?.cells.length || 1;
            marker.innerHTML = `<td colspan="${columnCount}" style="text-align:center;padding:1rem;color:var(--text-secondary)">No matching results</td>`;
            tbody.appendChild(marker);
        }
        return marker;
    };

    const filterTables = (input) => {
        const query = (input.value || '').trim().toLowerCase();
        const scope = input.closest('.main-content, .content') || document;
        let tables = Array.from(scope.querySelectorAll('.data-table'));
        if (!tables.length) {
            tables = Array.from(document.querySelectorAll('.data-table'));
        }
        tables.forEach((table) => {
            const tbody = table.tBodies[0];
            if (!tbody) {
                return;
            }
            const rows = Array.from(tbody.rows).filter((row) => !row.classList.contains('no-results-row'));
            let visible = 0;
            if (query === '') {
                rows.forEach((row) => {
                    row.style.display = '';
                    visible++;
                });
            } else {
                rows.forEach((row) => {
                    const match = row.textContent.toLowerCase().includes(query);
                    row.style.display = match ? '' : 'none';
                    if (match) {
                        visible++;
                    }
                });
            }
            const marker = ensureNoResultsRow(table);
            if (marker) {
                marker.style.display = visible === 0 ? '' : 'none';
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        const inputs = Array.from(document.querySelectorAll('.search-box input'));
        if (!inputs.length) {
            return;
        }
        inputs.forEach((input) => {
            const handler = debounce(() => filterTables(input), 150);
            input.addEventListener('input', handler);
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    const scope = input.closest('.main-content, .content') || document;
                    const firstVisible = scope.querySelector('.data-table tbody tr:not(.no-results-row):not([style*="display: none"])');
                    if (firstVisible) {
                        firstVisible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
        });
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('dashboardTheme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const icon = document.getElementById('themeToggle').querySelector('i');
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

    attachOverlayClose('addItemModal', closeAddItemModal);
    attachOverlayClose('editItemModal', closeInventoryEditModal);
    attachOverlayClose('importModal', closeImportModal);
    attachOverlayClose('addVendorModal', closeAddVendorModal);
    attachOverlayClose('editVendorModal', closeEditVendorModal);
    attachOverlayClose('markDefectiveModal', closeMarkDefectiveModal);

    const bomDeleteModal = document.getElementById('inventoryDeleteBomModal');
    const bomDeleteMessage = document.getElementById('inventoryDeleteBomMessage');
    const bomDeleteConfirm = document.getElementById('inventoryDeleteBomConfirm');
    const bomDeleteCancel = document.getElementById('inventoryDeleteBomCancel');
    const bomDeleteForm = document.getElementById('inventoryDeleteBomForm');
    const bomDeleteInput = document.getElementById('inventoryDeleteBomId');
    let pendingBomDeleteId = null;

    const closeBomDeleteModal = () => {
        if (bomDeleteModal) {
            bomDeleteModal.style.display = 'none';
            bomDeleteModal.setAttribute('aria-hidden', 'true');
        }
        pendingBomDeleteId = null;
    };

    attachOverlayClose('inventoryDeleteBomModal', closeBomDeleteModal);

    document.querySelectorAll('.inventory-bom-delete-btn').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            pendingBomDeleteId = button.dataset.bomId || null;
            const bomName = button.dataset.bomName || 'this BOM';
            if (bomDeleteMessage) {
                bomDeleteMessage.textContent = `Do you want to delete ${bomName}?`;
            }
            if (bomDeleteModal) {
                bomDeleteModal.style.display = 'flex';
                bomDeleteModal.setAttribute('aria-hidden', 'false');
            }
        });
    });

    bomDeleteConfirm?.addEventListener('click', () => {
        if (!pendingBomDeleteId || !bomDeleteForm || !bomDeleteInput) {
            return;
        }
        bomDeleteInput.value = pendingBomDeleteId;
        window.__actionFeedback?.queue('Bill of Materials deleted successfully.', 'success', {
            defer: true,
            title: 'Inventory Update'
        });
        bomDeleteForm.submit();
    });

    bomDeleteCancel?.addEventListener('click', () => {
        closeBomDeleteModal();
    });

    const defectiveDeleteModal = document.getElementById('deleteDefectiveModal');
    const defectiveDeleteConfirm = document.getElementById('deleteDefectiveConfirm');
    const defectiveDeleteCancel = document.getElementById('deleteDefectiveCancel');

    const closeDefectiveDeleteModal = () => {
        if (defectiveDeleteModal) {
            defectiveDeleteModal.style.display = 'none';
            defectiveDeleteModal.setAttribute('aria-hidden', 'true');
        }
        pendingDefectiveDeleteId = null;
    };

    attachOverlayClose('deleteDefectiveModal', closeDefectiveDeleteModal);

    defectiveDeleteConfirm?.addEventListener('click', () => {
        if (!pendingDefectiveDeleteId) {
            return;
        }
        window.location.href = `dashboard_inventory.php?section=defective&delete_defective=${pendingDefectiveDeleteId}`;
    });

    defectiveDeleteCancel?.addEventListener('click', () => {
        closeDefectiveDeleteModal();
    });

    const inventoryDeleteModal = document.getElementById('deleteInventoryModal');
    const inventoryDeleteConfirm = document.getElementById('deleteInventoryConfirm');
    const inventoryDeleteCancel = document.getElementById('deleteInventoryCancel');

    const closeInventoryDeleteModal = () => {
        if (inventoryDeleteModal) {
            inventoryDeleteModal.style.display = 'none';
            inventoryDeleteModal.setAttribute('aria-hidden', 'true');
        }
        pendingInventoryDeleteId = null;
    };

    attachOverlayClose('deleteInventoryModal', closeInventoryDeleteModal);

    inventoryDeleteConfirm?.addEventListener('click', () => {
        if (!pendingInventoryDeleteId) {
            return;
        }
        window.location.href = `dashboard_inventory.php?delete=${pendingInventoryDeleteId}`;
    });

    inventoryDeleteCancel?.addEventListener('click', () => {
        closeInventoryDeleteModal();
    });

    const posFilterButtons = document.querySelectorAll('.pos-filter-btn');
    if (posFilterButtons.length) {
        const applyPosFilter = (target) => {
            const rows = document.querySelectorAll('#inventoryTable .pos-visibility-row');
            rows.forEach((row) => {
                const visibility = row.getAttribute('data-visibility') || 'visible';
                row.style.display = target === 'all' || visibility === target ? '' : 'none';
            });
        };
        posFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const value = button.getAttribute('data-pos-filter') || 'all';
                posFilterButtons.forEach((btn) => btn.classList.toggle('active', btn === button));
                applyPosFilter(value);
            });
        });
        applyPosFilter('all');
    }

    const defectiveQtyInput = document.getElementById('defective_quantity');
    if (defectiveQtyInput) {
        defectiveQtyInput.addEventListener('input', enforceDefectiveQuantityLimit);
    }

    if (toastMessage) {
        showToast(toastMessage);
    }

    const feedbackModule = window.__actionFeedback;
    if (feedbackModule) {
        const immediate = actionFeedbackPayload;
        if (immediate && immediate.text) {
            feedbackModule.show(immediate.text, immediate.type || 'info', {
                duration: immediate.duration || undefined,
                title: immediate.title || undefined,
                defer: false
            });
        } else {
            const pending = feedbackModule.consume();
            if (pending && pending.message) {
                feedbackModule.show(pending.message, pending.type || 'success', {
                    duration: pending.duration || undefined,
                    title: pending.title || undefined,
                    defer: false
                });
            }
        }
    }
});
</script>
</body>
</html>
