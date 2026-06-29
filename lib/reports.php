<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Generate and save daily report data to database
 */
function generate_daily_report(string $date): array {
    $pdo = db();
    
    // Calculate date range
    $dateStart = $date . ' 00:00:00';
    $dateEnd = $date . ' 23:59:59';
    
    // Get orders data
    $ordersStmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders,
               SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
               SUM(CASE WHEN status = 'done' THEN total_price ELSE 0 END) as total_revenue
        FROM orders
        WHERE created_at BETWEEN ? AND ?
    ");
    $ordersStmt->execute([$dateStart, $dateEnd]);
    $stats = $ordersStmt->fetch();
    
    // Get popular items with options
    $popularItemsStmt = $pdo->prepare("
        SELECT oi.id as order_item_id, mi.id, mi.name_en, mi.category, mi.price as base_price,
               oi.quantity, oi.selected_options
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ? AND o.status = 'done'
    ");
    $popularItemsStmt->execute([$dateStart, $dateEnd]);
    $allItems = $popularItemsStmt->fetchAll();
    
    // Aggregate by item and calculate revenue including options
    $itemStats = [];
    foreach ($allItems as $item) {
        $itemId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        $basePrice = (float)$item['base_price'];
        
        // Calculate option prices
        $optionPrice = 0;
        if (!empty($item['selected_options'])) {
            $options = json_decode($item['selected_options'], true);
            if (is_array($options)) {
                foreach ($options as $opt) {
                    if (isset($opt['additional_price'])) {
                        $optionPrice += (float)$opt['additional_price'];
                    }
                }
            }
        }
        
        $totalPrice = $basePrice + $optionPrice;
        $lineTotal = $totalPrice * $quantity;
        
        if (!isset($itemStats[$itemId])) {
            $itemStats[$itemId] = [
                'id' => $itemId,
                'name_en' => $item['name_en'],
                'category' => $item['category'],
                'total_quantity' => 0,
                'total_revenue' => 0,
                'options' => []
            ];
        }
        
        $itemStats[$itemId]['total_quantity'] += $quantity;
        $itemStats[$itemId]['total_revenue'] += $lineTotal;
        
        // Track unique options for display
        if (!empty($item['selected_options'])) {
            $options = json_decode($item['selected_options'], true);
            if (is_array($options)) {
                foreach ($options as $opt) {
                    $optKey = ($opt['name_en'] ?? 'Option') . ':' . ($opt['additional_price'] ?? 0);
                    if (!isset($itemStats[$itemId]['options'][$optKey])) {
                        $itemStats[$itemId]['options'][$optKey] = $opt;
                    }
                }
            }
        }
    }
    
    // Sort by quantity and take top 5
    usort($itemStats, function($a, $b) {
        return $b['total_quantity'] <=> $a['total_quantity'];
    });
    $popularItems = array_slice($itemStats, 0, 5);
    
    // Save to daily_reports table
    $saveStmt = $pdo->prepare("
        INSERT INTO daily_reports 
        (report_date, total_orders, completed_orders, total_revenue, popular_items)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        total_orders = VALUES(total_orders),
        completed_orders = VALUES(completed_orders),
        total_revenue = VALUES(total_revenue),
        popular_items = VALUES(popular_items),
        updated_at = CURRENT_TIMESTAMP
    ");
    $saveStmt->execute([
        $date,
        (int)$stats['total_orders'],
        (int)$stats['completed_orders'],
        (float)$stats['total_revenue'],
        json_encode($popularItems)
    ]);
    
        
    return [
        'date' => $date,
        'total_orders' => (int)$stats['total_orders'],
        'completed_orders' => (int)$stats['completed_orders'],
        'total_revenue' => (float)$stats['total_revenue'],
        'popular_items' => $popularItems
    ];
}

/**
 * Generate and save monthly report data to database
 */
function generate_monthly_report(string $month): array {
    $pdo = db();
    
    // Calculate date range
    $dateStart = $month . '-01 00:00:00';
    $dateEnd = date('Y-m-t', strtotime($month . '-01')) . ' 23:59:59';
    
    // Get orders data
    $ordersStmt = $pdo->prepare("
        SELECT COUNT(*) as total_orders,
               SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as completed_orders,
               SUM(CASE WHEN status = 'done' THEN total_price ELSE 0 END) as total_revenue
        FROM orders
        WHERE created_at BETWEEN ? AND ?
    ");
    $ordersStmt->execute([$dateStart, $dateEnd]);
    $stats = $ordersStmt->fetch();
    
    // Get popular items with options
    $popularItemsStmt = $pdo->prepare("
        SELECT oi.id as order_item_id, mi.id, mi.name_en, mi.category, mi.price as base_price,
               oi.quantity, oi.selected_options
        FROM order_items oi
        JOIN menu_items mi ON oi.item_id = mi.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ? AND o.status = 'done'
    ");
    $popularItemsStmt->execute([$dateStart, $dateEnd]);
    $allItems = $popularItemsStmt->fetchAll();
    
    // Aggregate by item and calculate revenue including options
    $itemStats = [];
    foreach ($allItems as $item) {
        $itemId = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        $basePrice = (float)$item['base_price'];
        
        // Calculate option prices
        $optionPrice = 0;
        if (!empty($item['selected_options'])) {
            $options = json_decode($item['selected_options'], true);
            if (is_array($options)) {
                foreach ($options as $opt) {
                    if (isset($opt['additional_price'])) {
                        $optionPrice += (float)$opt['additional_price'];
                    }
                }
            }
        }
        
        $totalPrice = $basePrice + $optionPrice;
        $lineTotal = $totalPrice * $quantity;
        
        if (!isset($itemStats[$itemId])) {
            $itemStats[$itemId] = [
                'id' => $itemId,
                'name_en' => $item['name_en'],
                'category' => $item['category'],
                'total_quantity' => 0,
                'total_revenue' => 0,
                'options' => []
            ];
        }
        
        $itemStats[$itemId]['total_quantity'] += $quantity;
        $itemStats[$itemId]['total_revenue'] += $lineTotal;
        
        // Track unique options for display
        if (!empty($item['selected_options'])) {
            $options = json_decode($item['selected_options'], true);
            if (is_array($options)) {
                foreach ($options as $opt) {
                    $optKey = ($opt['name_en'] ?? 'Option') . ':' . ($opt['additional_price'] ?? 0);
                    if (!isset($itemStats[$itemId]['options'][$optKey])) {
                        $itemStats[$itemId]['options'][$optKey] = $opt;
                    }
                }
            }
        }
    }
    
    // Sort by quantity and take top 5
    usort($itemStats, function($a, $b) {
        return $b['total_quantity'] <=> $a['total_quantity'];
    });
    $popularItems = array_slice($itemStats, 0, 5);
    
    // Save to monthly_reports table
    $saveStmt = $pdo->prepare("
        INSERT INTO monthly_reports 
        (report_month, total_orders, completed_orders, total_revenue, popular_items)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        total_orders = VALUES(total_orders),
        completed_orders = VALUES(completed_orders),
        total_revenue = VALUES(total_revenue),
        popular_items = VALUES(popular_items),
        updated_at = CURRENT_TIMESTAMP
    ");
    $saveStmt->execute([
        $month,
        (int)$stats['total_orders'],
        (int)$stats['completed_orders'],
        (float)$stats['total_revenue'],
        json_encode($popularItems)
    ]);
    
    // Save detailed popular items
    $pdo->prepare("DELETE FROM popular_items_monthly WHERE report_month = ?")->execute([$month]);
    foreach ($popularItems as $item) {
        $detailStmt = $pdo->prepare("
            INSERT INTO popular_items_monthly 
            (report_month, item_id, item_name, category, total_quantity, total_revenue)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $detailStmt->execute([
            $month,
            (int)$item['id'],
            $item['name_en'],
            $item['category'],
            (int)$item['total_quantity'],
            (float)$item['total_revenue']
        ]);
    }
    
    return [
        'month' => $month,
        'total_orders' => (int)$stats['total_orders'],
        'completed_orders' => (int)$stats['completed_orders'],
        'total_revenue' => (float)$stats['total_revenue'],
        'popular_items' => $popularItems
    ];
}

/**
 * Get daily report from database
 */
function get_daily_report(string $date): ?array {
    $pdo = db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM daily_reports WHERE report_date = ?
    ");
    $stmt->execute([$date]);
    $report = $stmt->fetch();
    
    if (!$report) {
        return null;
    }
    
    return [
        'date' => $report['report_date'],
        'total_orders' => (int)$report['total_orders'],
        'completed_orders' => (int)$report['completed_orders'],
        'total_revenue' => (float)$report['total_revenue'],
        'popular_items' => json_decode($report['popular_items'] ?: '[]', true)
    ];
}

/**
 * Get monthly report from database
 */
function get_monthly_report(string $month): ?array {
    $pdo = db();
    
    $stmt = $pdo->prepare("
        SELECT * FROM monthly_reports WHERE report_month = ?
    ");
    $stmt->execute([$month]);
    $report = $stmt->fetch();
    
    if (!$report) {
        return null;
    }
    
    return [
        'month' => $report['report_month'],
        'total_orders' => (int)$report['total_orders'],
        'completed_orders' => (int)$report['completed_orders'],
        'total_revenue' => (float)$report['total_revenue'],
        'popular_items' => json_decode($report['popular_items'] ?: '[]', true)
    ];
}

/**
 * Get order details for a specific period (for detailed view)
 */
function get_order_details(string $dateStart, string $dateEnd): array {
    $pdo = db();
    
    $stmt = $pdo->prepare("
        SELECT o.id, o.table_number, o.order_type, o.total_price, o.status, o.created_at,
               GROUP_CONCAT(
                   CONCAT(mi.name_en, ' (', oi.quantity, 'x)') 
                   ORDER BY mi.name_en 
                   SEPARATOR ', '
               ) as items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN menu_items mi ON oi.item_id = mi.id
        WHERE o.created_at BETWEEN ? AND ? AND o.status = 'done'
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$dateStart, $dateEnd]);
    return $stmt->fetchAll();
}

/**
 * Auto-generate reports when orders are marked as done
 */
function update_reports_on_order_completion(int $orderId): void {
    $pdo = db();
    
    // Get the order date
    $stmt = $pdo->prepare("SELECT DATE(created_at) as order_date, DATE_FORMAT(created_at, '%Y-%m') as order_month FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if ($order) {
        // Update daily report
        generate_daily_report($order['order_date']);
        
        // Update monthly report
        generate_monthly_report($order['order_month']);
    }
}
