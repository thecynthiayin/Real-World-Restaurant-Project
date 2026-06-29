<?php

require_once __DIR__ . '/../lib/db.php';

$pdo = db();

$rows = [
    [10, 'Plain Coffee', 'ပလိန်းကော်ဖီ', 'กาแฟเพลน', 25],
    [20, 'Tea', 'ရှယ်လက်ဖက်ရည်', 'ชา', 30],
    [ 30, 'Milk', 'နွားနို့', 'นมสด', 30],
    [ 40, 'Hot Lemon Tea', 'လီမွန်တီး (အပူ)', 'ชามะนาวร้อน', 30],
    [50, 'Black Coffee', 'ဘလက်(ခ်)ကော်ဖီ', 'กาแฟดำ', 35],
    [ 60, 'Na An Roti', 'ပဲနံပြား (ကြည်)', 'โรตีนาน', 40],
    [ 70, 'Mandalay Roti', 'ထပ်တရာ (ပဲ၊သကြား)', 'โรตีมันฑะเลย์', 45],
    [ 80, 'Special Mandalay Roti', 'ရှယ်ထပ်တရာ', 'โรตีมันฑะเลย์พิเศษ', 50],
    [ 90, 'Butter Na An Roti', 'နံပြားထောပတ်သုပ်', 'บัตเตอร์ นา อัน โรตี', 50],
    [ 100, 'Ceylon Tea', 'စီလုံတီး', 'ชาซีลอน', 50],
];

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        "UPDATE menu_items
         SET name = ?, name_en = ?, name_bu = ?, name_th = ?, price = ?, status = 'available'
         WHERE category = 'breakfast' AND sort_order = ?"
    );

    foreach ($rows as [$sortOrder, $nameEn, $nameBu, $nameTh, $price]) {
        $stmt->execute([$nameEn, $nameEn, $nameBu, $nameTh, $price, $sortOrder]);
    }

    $pdo->commit();
    echo "ok\n";
    echo "updated=" . count($rows) . "\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "error\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

