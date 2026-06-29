<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json');

$itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;
$lang = normalize_menu_lang($_GET['lang'] ?? 'en');

if ($itemId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT * FROM menu_item_options 
        WHERE menu_item_id = ? 
        ORDER BY option_group ASC, sort_order ASC
    ");
    $stmt->execute([$itemId]);
    $options = $stmt->fetchAll();
    
    // Group by option_group
    $grouped = [];
    foreach ($options as $opt) {
        $group = $opt['option_group'] ?? 'default';
        if (!isset($grouped[$group])) {
            $grouped[$group] = [
                'group' => $group,
                'is_required' => (bool) $opt['is_required'],
                'is_multi_select' => (bool) $opt['is_multi_select'],
                'options' => []
            ];
        }
        
        // Add localized name
        $opt['localized_name'] = menu_option_name_by_lang($opt, $lang);
        $grouped[$group]['options'][] = $opt;
    }
    
    // Convert to indexed array for JSON
    echo json_encode(array_values($grouped));
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
