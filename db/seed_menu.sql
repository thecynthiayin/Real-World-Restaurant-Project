-- Seed data for production menu (EN/BU/TH + categories)
-- Safe to re-run: it clears menu and related order history.

START TRANSACTION;

DELETE FROM order_items;
DELETE FROM orders;
DELETE FROM menu_items;

INSERT INTO menu_items (name, name_en, name_bu, name_th, category, sort_order, price, status) VALUES
('Plain Coffee', 'Plain Coffee', 'ကော်ဖီပလိန်း', 'กาแฟธรรมดา', 'breakfast', 10, 30, 'available'),
('Tea', 'Tea', 'ရှယ်လက်ဖက်ရည်', 'ชา', 'breakfast', 20, 30, 'available'),
('Milk (Hot)', 'Milk (Hot)', 'နွားနို့အပူ', 'นมร้อน', 'breakfast', 30, 30, 'available'),
('Lemon Tea (Hot)', 'Lemon Tea (Hot)', 'လီမွန်တီးအပူ', 'ชามะนาวร้อน', 'breakfast', 40, 35, 'available'),
('Black Coffee (Hot)', 'Black Coffee (Hot)', 'ဘလက်(ခ်)ကော်ဖီ (ပူ)', 'กาแฟดำร้อน', 'breakfast', 50, 35, 'available'),
('Na An Butter', 'Na An Butter', 'နံပြားထောပတ်သုပ်', 'ขนมปังเนย', 'breakfast', 60, 40, 'available'),
('Ceylon Tea', 'Ceylon Tea', 'စီလုံတီး', 'ชาซีลอน', 'breakfast', 70, 35, 'available'),

('Egg Fried Rice', 'Egg Fried Rice', 'ကြက်ဥထမင်းကြော်', 'ข้าวผัดไข่', 'rice', 10, 50, 'available'),
('Boiled Peas Fried Rice', 'Boiled Peas Fried Rice', 'ပဲပြုတ်ထမင်းကြော်', 'ข้าวผัดถั่วลันเตา', 'rice', 20, 60, 'available'),
('Mandalay Rice Salad', 'Mandalay Rice Salad', 'မန္တလေးထမင်းသုပ်', 'สลัดข้าวมันฑะเลย์', 'rice', 30, 60, 'available'),
('Laphat Rice', 'Laphat Rice', 'လက်ဖက်ထမင်း', 'ข้าวยำใบชา', 'rice', 40, 80, 'available'),
('Mala Fried Rice', 'Mala Fried Rice', 'မာလာထမင်းကြော်', 'ข้าวผัดหม่าล่า', 'rice', 50, 80, 'available'),
('Malaysia Fried Rice', 'Malaysia Fried Rice', 'ပသျှူးထမင်းကြော်', 'ข้าวผัดมาเลย์', 'rice', 60, 80, 'available'),
('Chinese Fried Rice', 'Chinese Fried Rice', 'တရုတ်ထမင်းကြော်', 'ข้าวผัดสไตล์จีน', 'rice', 70, 80, 'available'),
('Tom Yum Fried Rice', 'Tom Yum Fried Rice', 'တုံယမ်းထမင်းကြော်', 'ข้าวผัดต้มยำ', 'rice', 80, 80, 'available'),
('Mashed Mutton Rice', 'Mashed Mutton Rice', 'ဆိတ်ထောင်းထမင်း', 'ข้าวกับเนื้อแกะสับ', 'rice', 90, 80, 'available'),
('Seafood Fried Rice', 'Seafood Fried Rice', 'ပင်လယ်စာထမင်းကြော်', 'ข้าวผัดทะเล', 'rice', 100, 110, 'available'),

('Shan Noodle', 'Shan Noodle', 'ရှမ်း', 'ก๋วยเตี๋ยวฉาน', 'noodles', 10, 60, 'available'),
('Shan Noodle', 'Shan Noodle', 'ဆန်စီး', 'ก๋วยเตี๋ยวฉาน', 'noodles', 20, 60, 'available'),
('Shan Noodle', 'Shan Noodle', 'ဆန်ပြား', 'ก๋วยเตี๋ยวฉาน', 'noodles', 30, 60, 'available'),
('Nangyi', 'Nangyi', 'နန်းကြီး', 'หนานจี๋', 'noodles', 40, 60, 'available'),
('Nanthay', 'Nanthay', 'နန်းသေး', 'หนานจี๋เล็ก', 'noodles', 50, 60, 'available'),
('Garlic-oil Noodle', 'Garlic-oil Noodle', 'ဆီချက်', 'บะหมี่น้ำมันกระเทียม', 'noodles', 60, 60, 'available'),
('Mandalay Rice Noodle', 'Mandalay Rice Noodle', 'မန္တလေးမြီးရှည်', 'บะหมี่มันฑะเลย์', 'noodles', 70, 70, 'available'),
('Creamy Tofu', 'Creamy Tofu', 'တို့ဟူးနွေး', 'บะหมี่เต้าหู้ครีมมี่', 'noodles', 80, 70, 'available'),
('Pork Stew', 'Pork Stew', 'ဝက်စတူးကော်ရည်ခေါက်ဆွဲ', 'ก๋วยเตี๋ยวหมูตุ๋น', 'noodles', 90, 80, 'available'),
('Pork Rib Noodle', 'Pork Rib Noodle', 'ဝက်နံရိုးကော်ရည်ခေါက်ဆွဲ', 'ก๋วยเตี๋ยวซี่โครงหมู', 'noodles', 100, 80, 'available'),
('Mohinga', 'Mohinga', 'မုန့်ဟင်းခါး', 'โมฮิงกา', 'noodles', 110, 50, 'available'),
('Fried Noodle', 'Fried Noodle', 'ခေါက်ဆွဲကြော်', 'หมี่ผัด', 'noodles', 120, 80, 'available'),
('Fried Kyar Zan', 'Fried Kyar Zan', 'ကြာဇံကြော်', 'ข้าวทอด', 'noodles', 130, 80, 'available'),

('Dumplings (Garlic-oil)', 'Dumplings (Garlic-oil)', 'ဖက်ထုပ် (ဆီချက်)', 'เกี๊ยว (น้ำมันกระเทียม)', 'dumplings', 10, 65, 'available'),
('Dumplings (Soup)', 'Dumplings (Soup)', 'ဖက်ထုပ် (ပြုတ်)', 'เกี๊ยว (ซุป)', 'dumplings', 20, 65, 'available'),
('Pan Fried Dumplings', 'Pan Fried Dumplings', 'ဖက်ထုပ်အိုးကပ်', 'เกี๊ยวทอดกระทะ', 'dumplings', 30, 65, 'available'),
('Fried Dumplings', 'Fried Dumplings', 'ဖက်ထုပ်ကြော်', 'เกี๊ยวทอด', 'dumplings', 40, 65, 'available'),

('Snow Fungus', 'Snow Fungus', 'ကျောက်ပွင့်သုပ်', 'สลัดเห็ดหิมะ', 'salad', 10, 60, 'available'),
('Enoki', 'Enoki', 'အပ်မှိုသုပ်', 'ยำเห็ดเข็มทอง', 'salad', 20, 60, 'available'),
('Chicken Salad', 'Chicken Salad', 'ကြက်သားသုပ်', 'ยำไก่', 'salad', 30, 60, 'available'),
('Pork Salad', 'Pork Salad', 'ဝက်သားသုပ်', 'ยำหมู', 'salad', 40, 60, 'available'),
('Mala Chicken Feet Salad', 'Mala Chicken Feet Salad', 'မာလာကြက်ခြေထောက်သုပ်', 'สลัดตีนไก่หม่าล่า', 'salad', 50, 80, 'available'),
('Seafood Salad', 'Seafood Salad', 'ပင်လယ်စာသုပ်', 'สลัดทะเล', 'salad', 60, 90, 'available'),

('Lemon Tea', 'Lemon Tea', 'လီမွန်တီး', 'ชามะนาว', 'drinks', 10, 55, 'available'),
('Woman', 'Woman', 'မိန်းမ', 'ผู้หญิง', 'drinks', 20, 55, 'available'),
('Blueberry (Juice/Soda)', 'Blueberry (Juice/Soda)', 'Blueberry (ဖျော်ရည်/ဆိုဒါ)', 'บลูเบอร์รี่ (น้ำผลไม้/โซดา)', 'drinks', 30, 30, 'available'),
('Blue Hawaii', 'Blue Hawaii', 'Blue Hawaii', 'บลูฮาวาย', 'drinks', 40, 30, 'available'),
('Cantaloupe', 'Cantaloupe', 'Cantaloupe', 'แคนตาลูป', 'drinks', 50, 30, 'available'),
('Grape', 'Grape', 'Grape', 'องุ่น', 'drinks', 60, 30, 'available'),
('Kiwi', 'Kiwi', 'Kiwi', 'กีวี่', 'drinks', 70, 30, 'available'),
('Lychee', 'Lychee', 'Lychee', 'ลิ้นจี่', 'drinks', 80, 30, 'available'),
('Mango', 'Mango', 'Mango', 'มะม่วง', 'drinks', 90, 30, 'available'),
('Orange', 'Orange', 'Orange', 'ส้ม', 'drinks', 100, 30, 'available'),
('Passion', 'Passion', 'Passion', 'เสาวรส', 'drinks', 110, 30, 'available'),
('Peach', 'Peach', 'Peach', 'พีช', 'drinks', 120, 30, 'available'),
('Pineapple', 'Pineapple', 'Pineapple', 'สับปะรด', 'drinks', 130, 30, 'available'),
('Strawberry', 'Strawberry', 'Strawberry', 'สตรอเบอรี่', 'drinks', 140, 30, 'available'),
('Watermelon (Juice/Soda)', 'Watermelon (Juice/Soda)', 'ဖရဲ (ဖျော်ရည်/ဆိုဒါ)', 'แตงโม (น้ำผลไม้/โซดา)', 'drinks', 150, 30, 'available'),

('Blueberry Yako', 'Blueberry Yako', 'Blueberry ယာကို', 'บลูเบอร์รี่ ยาโกะ', 'yako', 10, 35, 'available'),
('Mango Yako', 'Mango Yako', 'Mango ယာကို', 'มะม่วง ยาโกะ', 'yako', 20, 35, 'available'),
('Pineapple Yako', 'Pineapple Yako', 'Pineapple ယာကို', 'สับปะรด ยาโกะ', 'yako', 30, 35, 'available'),
('Strawberry Yako', 'Strawberry Yako', 'Strawberry ယာကို', 'สตรอเบอรี่ ยาโกะ', 'yako', 40, 35, 'available'),
('Passion Yako', 'Passion Yako', 'Passion ယာကို', 'เสาวรส ยาโกะ', 'yako', 50, 35, 'available');

COMMIT;
