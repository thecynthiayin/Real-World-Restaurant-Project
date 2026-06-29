<?php

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function require_table_number(): int
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $table = null;
    $tableRaw = null;
    if (isset($_GET['table'])) {
        $tableRaw = $_GET['table'];
    } elseif (isset($_POST['table'])) {
        $tableRaw = $_POST['table'];
    } elseif (isset($_POST['table_number'])) {
        $tableRaw = $_POST['table_number'];
    } elseif (isset($_GET['table_number'])) {
        $tableRaw = $_GET['table_number'];
    }

    if ($tableRaw !== null) {
        $table = filter_var($tableRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($table !== false && $table !== null) {
            $_SESSION['table_number'] = (int) $table;
        }
    }

    if (isset($_SESSION['table_number'])) {
        return (int) $_SESSION['table_number'];
    }

    http_response_code(400);
    echo "Missing or invalid table number. Use index.php?table=1";
    exit;
}

function get_order_type(): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return isset($_SESSION['order_type']) ? (string) $_SESSION['order_type'] : null;
}

function get_order_type_for_table(int $table): ?string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $type = isset($_SESSION['order_type']) ? (string) $_SESSION['order_type'] : null;
    if ($type !== 'eat_in' && $type !== 'take_away') {
        return null;
    }

    $typeTable = $_SESSION['order_type_table'] ?? null;
    if (!is_int($typeTable)) {
        $typeTable = filter_var($typeTable, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $typeTable = ($typeTable === false || $typeTable === null) ? null : (int) $typeTable;
    }

    return ($typeTable === $table) ? $type : null;
}

function require_order_type(): string
{
    $table = require_table_number();
    $type = get_order_type_for_table($table);
    if ($type === 'eat_in' || $type === 'take_away') {
        return $type;
    }

    header('Location: index.php?table=' . urlencode((string) $table));
    exit;
}

function order_type_label(string $type): string
{
    return $type === 'take_away' ? 'TAKE AWAY' : 'EAT IN';
}

function order_type_badge_class(string $type): string
{
    return $type === 'take_away' ? 'text-bg-warning' : 'text-bg-success';
}

function currency_symbol(): string
{
    // Thai Baht
    return '฿';
}

function money_decimals(): int
{
    return 2;
}

function format_money(string|int|float $amount): string
{
    return currency_symbol() . ' ' . number_format((float) $amount, money_decimals(), '.', ',');
}

function normalize_menu_lang(?string $lang): string
{
    $v = strtolower(trim((string) $lang));
    return in_array($v, ['en', 'bu', 'th'], true) ? $v : 'en';
}

function menu_category_label(string $category, string $lang): string
{
    $map = [
        'breakfast' => ['en' => 'BREAKFAST', 'bu' => 'မနက်စာ', 'th' => 'อาหารเช้า'],
        'rice' => ['en' => 'RICE', 'bu' => 'ထမင်း', 'th' => 'ข้าว'],
        'noodles' => ['en' => 'NOODLES', 'bu' => 'ခေါက်ဆွဲ', 'th' => 'ก๋วยเตี๋ยว'],
        'dumplings' => ['en' => 'DUMPLINGS', 'bu' => 'ဖက်ထုပ်', 'th' => 'เกี๊ยว'],
        'salad' => ['en' => 'SALAD', 'bu' => 'အသုပ်', 'th' => 'ยำ / สลัด'],
        'drinks' => ['en' => 'DRINKS', 'bu' => 'အချိုရည်', 'th' => 'เครื่องดื่ม'],
        'yako' => ['en' => 'YAKO', 'bu' => 'ယာကို', 'th' => 'ยาโกะ'],
    ];

    $lang = normalize_menu_lang($lang);
    if (!isset($map[$category])) {
        return strtoupper($category);
    }
    return $map[$category][$lang] ?? $map[$category]['en'];
}

function menu_item_name_by_lang(array $row, string $lang): string
{
    $lang = normalize_menu_lang($lang);
    $en = trim((string) ($row['name_en'] ?? $row['name'] ?? ''));
    $bu = trim((string) ($row['name_bu'] ?? ''));
    $th = trim((string) ($row['name_th'] ?? ''));

    if ($lang === 'bu') {
        return $bu !== '' ? $bu : $en;
    }
    if ($lang === 'th') {
        return $th !== '' ? $th : $en;
    }
    return $en;
}

/**
 * Returns a 3-language HTML block for a menu item.
 * Falls back to the legacy `name` column if the multilingual columns aren't present.
 */
function menu_item_name_block(array $row): string
{
    $en = (string) ($row['name_en'] ?? $row['name'] ?? '');
    $bu = (string) ($row['name_bu'] ?? '');
    $th = (string) ($row['name_th'] ?? '');

    $out = '<div class="menu-name">';
    $out .= '<div class="lang-en">' . h($en) . '</div>';
    if (trim($bu) !== '') {
        $out .= '<div class="lang-bu">' . h($bu) . '</div>';
    }
    if (trim($th) !== '') {
        $out .= '<div class="lang-th">' . h($th) . '</div>';
    }
    $out .= '</div>';

    return $out;
}

/**
 * Get option name by language
 */
function menu_option_name_by_lang(array $row, string $lang): string
{
    $lang = normalize_menu_lang($lang);
    $en = trim((string) ($row['name_en'] ?? ''));
    $bu = trim((string) ($row['name_bu'] ?? ''));
    $th = trim((string) ($row['name_th'] ?? ''));

    if ($lang === 'bu') {
        return $bu !== '' ? $bu : $en;
    }
    if ($lang === 'th') {
        return $th !== '' ? $th : $en;
    }
    return $en;
}

/**
 * Get menu item options grouped by option group
 */
function get_menu_item_options(int $menuItemId, string $lang): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    try {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT * FROM menu_item_options 
            WHERE menu_item_id = ? 
            ORDER BY option_group ASC, sort_order ASC
        ");
        $stmt->execute([$menuItemId]);
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
            $grouped[$group]['options'][] = $opt;
        }
        
        return $grouped;
    } catch (Exception $e) {
        return [];
    }
}

