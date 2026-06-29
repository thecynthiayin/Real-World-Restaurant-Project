-- Seed data for menu item options
-- Safe to re-run: it clears existing options

START TRANSACTION;

DELETE FROM menu_item_options;

-- Na An Butter (ID 2) - tea sweetness options and milk
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(2, 'sweetness', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(2, 'sweetness', 2, 0, 1, 0, 'Sweet', 'ချိုစိမ့်', 'หวาน'),
(2, 'sweetness', 3, 0, 1, 0, 'Glass Sweet', 'ဖန်ချို', 'แก้วหวาน'),
(2, 'sweetness', 4, 0, 1, 0, 'Normal Sweet', 'ပုံစိမ့်', 'ปกติหวาน'),
(2, 'sweetness', 5, 0, 1, 0, 'Less Sweet', 'ကျစိမ့်', 'หวานน้อย'),
(2, 'sweetness', 6, 0, 1, 0, 'More Sweet', 'ပေါ့စိမ့်', 'หวานมาก'),
(2, 'sweetness', 7, 0, 1, 0, 'Sweet Less', 'ချိုကျ', 'หวานน้อย'),
(2, 'sweetness', 8, 0, 1, 0, 'More Less', 'ပေါ့ကျ', 'น้อยมาก'),
(2, 'sweetness', 9, 0, 1, 0, 'Most Sweet', 'ဂိတ်ဆုံး', 'หวานที่สุด');

-- Ceylon Tea (ID 3) - tea sweetness options and milk
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(3, 'milk', 1, 0, 0, 0, 'Milk (Cold)', 'နို့အေး', 'นมเย็น');

-- Rice items (IDs 8-17) - spicy level, vegetables, meat, and shrimp options
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(10, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(10, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(10, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(10, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(10, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(10, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(10, 'meat', 3, 0, 1, 0, 'Fish Cake', 'ငါးဖယ်', 'ลูกชิ้นปลา'),
(10, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(11, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(11, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(11, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(11, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(11, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(12, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(12, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(12, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(12, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(12, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(12, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(12, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(13, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(13, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(13, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(13, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(13, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(13, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(13, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(14, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(14, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(14, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(14, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(14, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(14, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(14, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(15, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(15, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(15, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(15, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(15, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(15, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(15, 'shrimp', 1, 15, 0, 0, 'Shrimp', 'ပုစွန်', 'กุ้ง'),

(16, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(16, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(16, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(16, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),

(17, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(17, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(17, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(17, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(20, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(20, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(20, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(20, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(20, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(20, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(20, 'pork_ribs', 1, 20, 0, 0, 'Pork Ribs', 'ဝက်နံရိုး', 'ซี่โครงหมู');


-- Shan Noodle (ရှမ်း) - ID 18
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(18, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(18, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(18, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(18, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(18, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(18, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(18, 'pork_ribs', 1, 20, 0, 0, 'Pork Ribs', 'ဝက်နံရိုး', 'ซี่โครงหมู');

-- Shan Noodle (ဆန်စီး) - ID 19
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(19, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(19, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(19, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(19, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(19, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(19, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(19, 'pork_ribs', 1, 20, 0, 0, 'Pork Ribs', 'ဝက်နံရိုး', 'ซี่โครงหมู');

-- Woman (Tea) - ID 37 - tea sweetness options and milk
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(37, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(37, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(35, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(35, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(36, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(36, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(38, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(38, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(39, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(39, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(40, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(40, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก');

-- Nangyi (နန်းကြီး) - ID 21
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(21, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(21, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(21, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(21, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(21, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(21, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(21, 'meat', 3, 0, 1, 0, 'Fish Cake', 'ငါးဖယ်', 'ลูกชิ้นปลา');

-- Nanthay (နန်းသေး) - ID 22
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(22, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(22, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(22, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(22, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(22, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(22, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(22, 'meat', 3, 0, 1, 0, 'Fish Cake', 'ငါးဖယ်', 'ลูกชิ้นปลา');

-- Garlic-oil Noodle (ဆီချက်) - ID 23
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(23, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(23, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(23, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(23, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(23, 'pork_ribs', 1, 20, 0, 0, 'Pork Ribs', 'ဝက်နံရိုး', 'ซี่โครงหมู');

-- Mandalay Rice Noodle (မန္တလေးမြီးရှည်) - ID 24 (no options)

-- Creamy Tofu (တို့ဟူးနွေး) - ID 25
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(25, 'spicy_level', 1, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(25, 'spicy_level', 2, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(25, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(25, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(25, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(25, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู'),
(25, 'pork_ribs', 1, 20, 0, 0, 'Pork Ribs', 'ဝက်နံရိုး', 'ซี่โครงหมู');

-- Pork Stew (ဝက်စတူးကော်ရည်ခေါက်ဆွဲ) - ID 26
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(26, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(26, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี');

-- Pork Rib Noodle (ဝက်နံရိုးကော်ရည်ခေါက်ဆွဲ) - ID 27
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(27, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(27, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี');

-- Mohinga (မုန့်ဟင်းခါး) - ID 28
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(28, 'spicy_level', 1, 0, 1, 0, 'Less', 'နည်းနည်း', 'น้อย'),
(28, 'spicy_level', 2, 0, 1, 0, 'Normal', 'ပုံမှန်', 'ปกติ'),
(28, 'spicy_level', 3, 0, 1, 0, 'More', 'ပိုပြင်း', 'เผ็ดมาก'),
(28, 'vegetables', 1, 0, 1, 0, 'Yes', 'ပါ', 'มี'),
(28, 'vegetables', 2, 0, 1, 0, 'No', 'မပါ', 'ไม่มี'),
(28, 'add_on', 1, 10, 0, 1, 'Duck Egg', 'ဘဲဥခြမ်း', 'ไข่เป็ด'),
(28, 'add_on', 2, 10, 0, 1, 'Fritter', 'အကြော်', 'ขนมทอด'),
(28, 'add_on', 3, 10, 0, 1, 'Fish Cake', 'ငါးဖယ်', 'ลูกชิ้นปลา');


-- Fried Noodle (ခေါက်ဆွဲကြော်) - ID 29
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(29, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(29, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู');

-- Fried Kyar Zan (ကြာဇံကြော်) - ID 30
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(30, 'meat', 1, 0, 1, 0, 'Chicken', 'ကြက်', 'ไก่'),
(30, 'meat', 2, 0, 1, 0, 'Pork', 'ဝက်', 'หมู');

COMMIT;
