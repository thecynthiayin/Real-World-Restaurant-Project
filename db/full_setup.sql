-- ============================================================
-- QR Table Ordering — ONE FILE FULL SETUP
-- Paste this entire file into TablePlus SQL editor and run
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS popular_items_monthly;
DROP TABLE IF EXISTS daily_reports;
DROP TABLE IF EXISTS monthly_reports;
DROP TABLE IF EXISTS menu_item_options;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS menu_items;
SET FOREIGN_KEY_CHECKS = 1;

-- TABLES
CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NULL,
  name_en VARCHAR(255) NULL,
  name_bu VARCHAR(255) NULL,
  name_th VARCHAR(255) NULL,
  category VARCHAR(50) NOT NULL DEFAULT 'menu',
  sort_order INT NOT NULL DEFAULT 0,
  price DECIMAL(10,2) NOT NULL,
  status ENUM('available','out_of_stock') NOT NULL DEFAULT 'available',
  image VARCHAR(255) NULL,
  photo VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_number INT NOT NULL,
  order_type ENUM('eat_in','take_away') NOT NULL,
  total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','done') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  selected_options JSON NULL,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_item FOREIGN KEY (item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_item_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_item_id INT NOT NULL,
  option_group VARCHAR(50) NOT NULL DEFAULT 'default',
  sort_order INT NOT NULL DEFAULT 0,
  additional_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_required BOOLEAN NOT NULL DEFAULT FALSE,
  is_multi_select BOOLEAN NOT NULL DEFAULT FALSE,
  name_en VARCHAR(255) NOT NULL,
  name_bu VARCHAR(255) NULL,
  name_th VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu_item_options_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE daily_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_date DATE NOT NULL UNIQUE,
  total_orders INT NOT NULL DEFAULT 0,
  completed_orders INT NOT NULL DEFAULT 0,
  total_revenue DECIMAL(10,2) NOT NULL DEFAULT 0,
  popular_items JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_report_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE monthly_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_month VARCHAR(7) NOT NULL UNIQUE,
  total_orders INT NOT NULL DEFAULT 0,
  completed_orders INT NOT NULL DEFAULT 0,
  total_revenue DECIMAL(10,2) NOT NULL DEFAULT 0,
  popular_items JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_report_month (report_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE popular_items_monthly (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_month VARCHAR(7) NOT NULL,
  item_id INT NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  category VARCHAR(50) NOT NULL,
  total_quantity INT NOT NULL DEFAULT 0,
  total_revenue DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_month_item (report_month, item_id),
  INDEX idx_report_month (report_month),
  FOREIGN KEY (item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INDEXES
CREATE INDEX idx_orders_status_created_at ON orders(status, created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_menu_items_category_sort ON menu_items(category, sort_order);
CREATE INDEX idx_menu_item_options_item ON menu_item_options(menu_item_id);

-- ADMIN USER (password: morningstarhuamak)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$PB.BQIx4cgsWFOaPJo.2uOX3hTK4jOVJ12elBI6m9V95XcoHi.axa')
ON DUPLICATE KEY UPDATE password=VALUES(password);

-- MENU ITEMS
INSERT INTO menu_items (name, name_en, name_bu, name_th, category, sort_order, price, status) VALUES
('Plain Coffee','Plain Coffee','ကော်ဖီပလိန်း','กาแฟธรรมดา','breakfast',10,30,'available'),
('Tea','Tea','ရှယ်လက်ဖက်ရည်','ชา','breakfast',20,30,'available'),
('Milk (Hot)','Milk (Hot)','နွားနို့အပူ','นมร้อน','breakfast',30,30,'available'),
('Lemon Tea (Hot)','Lemon Tea (Hot)','လီမွန်တီးအပူ','ชามะนาวร้อน','breakfast',40,35,'available'),
('Black Coffee (Hot)','Black Coffee (Hot)','ဘလက်(ခ်)ကော်ဖီ (ပူ)','กาแฟดำร้อน','breakfast',50,35,'available'),
('Na An Butter','Na An Butter','နံပြားထောပတ်သုပ်','ขนมปังเนย','breakfast',60,40,'available'),
('Ceylon Tea','Ceylon Tea','စီလုံတီး','ชาซีลอน','breakfast',70,35,'available'),
('Egg Fried Rice','Egg Fried Rice','ကြက်ဥထမင်းကြော်','ข้าวผัดไข่','rice',10,50,'available'),
('Boiled Peas Fried Rice','Boiled Peas Fried Rice','ပဲပြုတ်ထမင်းကြော်','ข้าวผัดถั่วลันเตา','rice',20,60,'available'),
('Mandalay Rice Salad','Mandalay Rice Salad','မန္တလေးထမင်းသုပ်','สลัดข้าวมันฑะเลย์','rice',30,60,'available'),
('Laphat Rice','Laphat Rice','လက်ဖက်ထမင်း','ข้าวยำใบชา','rice',40,80,'available'),
('Mala Fried Rice','Mala Fried Rice','မာလာထမင်းကြော်','ข้าวผัดหม่าล่า','rice',50,80,'available'),
('Malaysia Fried Rice','Malaysia Fried Rice','ပသျှူးထမင်းကြော်','ข้าวผัดมาเลย์','rice',60,80,'available'),
('Chinese Fried Rice','Chinese Fried Rice','တရုတ်ထမင်းကြော်','ข้าวผัดสไตล์จีน','rice',70,80,'available'),
('Tom Yum Fried Rice','Tom Yum Fried Rice','တုံယမ်းထမင်းကြော်','ข้าวผัดต้มยำ','rice',80,80,'available'),
('Mashed Mutton Rice','Mashed Mutton Rice','ဆိတ်ထောင်းထမင်း','ข้าวกับเนื้อแกะสับ','rice',90,80,'available'),
('Seafood Fried Rice','Seafood Fried Rice','ပင်လယ်စာထမင်းကြော်','ข้าวผัดทะเล','rice',100,110,'available'),
('Shan Noodle','Shan Noodle','ရှမ်း','ก๋วยเตี๋ยวฉาน','noodles',10,60,'available'),
('Shan Noodle','Shan Noodle','ဆန်စီး','ก๋วยเตี๋ยวฉาน','noodles',20,60,'available'),
('Shan Noodle','Shan Noodle','ဆန်ပြား','ก๋วยเตี๋ยวฉาน','noodles',30,60,'available'),
('Nangyi','Nangyi','နန်းကြီး','หนานจี๋','noodles',40,60,'available'),
('Nanthay','Nanthay','နန်းသေး','หนานจี๋เล็ก','noodles',50,60,'available'),
('Garlic-oil Noodle','Garlic-oil Noodle','ဆီချက်','บะหมี่น้ำมันกระเทียม','noodles',60,60,'available'),
('Mandalay Rice Noodle','Mandalay Rice Noodle','မန္တလေးမြီးရှည်','บะหมี่มันฑะเลย์','noodles',70,70,'available'),
('Creamy Tofu','Creamy Tofu','တို့ဟူးနွေး','บะหมี่เต้าหู้ครีมมี่','noodles',80,70,'available'),
('Pork Stew','Pork Stew','ဝက်စတူးကော်ရည်ခေါက်ဆွဲ','ก๋วยเตี๋ยวหมูตุ๋น','noodles',90,80,'available'),
('Pork Rib Noodle','Pork Rib Noodle','ဝက်နံရိုးကော်ရည်ခေါက်ဆွဲ','ก๋วยเตี๋ยวซี่โครงหมู','noodles',100,80,'available'),
('Mohinga','Mohinga','မုန့်ဟင်းခါး','โมฮิงกา','noodles',110,50,'available'),
('Fried Noodle','Fried Noodle','ခေါက်ဆွဲကြော်','หมี่ผัด','noodles',120,80,'available'),
('Fried Kyar Zan','Fried Kyar Zan','ကြာဇံကြော်','ข้าวทอด','noodles',130,80,'available'),
('Dumplings (Garlic-oil)','Dumplings (Garlic-oil)','ဖက်ထုပ် (ဆီချက်)','เกี๊ยว (น้ำมันกระเทียม)','dumplings',10,65,'available'),
('Dumplings (Soup)','Dumplings (Soup)','ဖက်ထုပ် (ပြုတ်)','เกี๊ยว (ซุป)','dumplings',20,65,'available'),
('Pan Fried Dumplings','Pan Fried Dumplings','ဖက်ထုပ်အိုးကပ်','เกี๊ยวทอดกระทะ','dumplings',30,65,'available'),
('Fried Dumplings','Fried Dumplings','ဖက်ထုပ်ကြော်','เกี๊ยวทอด','dumplings',40,65,'available'),
('Snow Fungus','Snow Fungus','ကျောက်ပွင့်သုပ်','สลัดเห็ดหิมะ','salad',10,60,'available'),
('Enoki','Enoki','အပ်မှိုသုပ်','ยำเห็ดเข็มทอง','salad',20,60,'available'),
('Chicken Salad','Chicken Salad','ကြက်သားသုပ်','ยำไก่','salad',30,60,'available'),
('Pork Salad','Pork Salad','ဝက်သားသုပ်','ยำหมู','salad',40,60,'available'),
('Mala Chicken Feet Salad','Mala Chicken Feet Salad','မာလာကြက်ခြေထောက်သုပ်','สลัดตีนไก่หม่าล่า','salad',50,80,'available'),
('Seafood Salad','Seafood Salad','ပင်လယ်စာသုပ်','สลัดทะเล','salad',60,90,'available'),
('Lemon Tea','Lemon Tea','လီမွန်တီး','ชามะนาว','drinks',10,55,'available'),
('Woman','Woman','မိန်းမ','ผู้หญิง','drinks',20,55,'available'),
('Blueberry (Juice/Soda)','Blueberry (Juice/Soda)','Blueberry (ဖျော်ရည်/ဆိုဒါ)','บลูเบอร์รี่ (น้ำผลไม้/โซดา)','drinks',30,30,'available'),
('Blue Hawaii','Blue Hawaii','Blue Hawaii','บลูฮาวาย','drinks',40,30,'available'),
('Cantaloupe','Cantaloupe','Cantaloupe','แคนตาลูป','drinks',50,30,'available'),
('Grape','Grape','Grape','องุ่น','drinks',60,30,'available'),
('Kiwi','Kiwi','Kiwi','กีวี่','drinks',70,30,'available'),
('Lychee','Lychee','Lychee','ลิ้นจี่','drinks',80,30,'available'),
('Mango','Mango','Mango','มะม่วง','drinks',90,30,'available'),
('Orange','Orange','Orange','ส้ม','drinks',100,30,'available');

INSERT INTO menu_items (name, name_en, name_bu, name_th, category, sort_order, price, status) VALUES
('Passion','Passion','Passion','เสาวรส','drinks',110,30,'available'),
('Peach','Peach','Peach','พีช','drinks',120,30,'available'),
('Pineapple','Pineapple','Pineapple','สับปะรด','drinks',130,30,'available'),
('Strawberry','Strawberry','Strawberry','สตรอเบอรี่','drinks',140,30,'available'),
('Watermelon (Juice/Soda)','Watermelon (Juice/Soda)','ဖရဲ (ဖျော်ရည်/ဆိုဒါ)','แตงโม (น้ำผลไม้/โซดา)','drinks',150,30,'available'),
('Blueberry Yako','Blueberry Yako','Blueberry ယာကို','บลูเบอร์รี่ ยาโกะ','yako',10,35,'available'),
('Mango Yako','Mango Yako','Mango ယာကို','มะม่วง ยาโกะ','yako',20,35,'available'),
('Pineapple Yako','Pineapple Yako','Pineapple ယာကို','สับปะรด ยาโกะ','yako',30,35,'available'),
('Strawberry Yako','Strawberry Yako','Strawberry ယာကို','สตรอเบอรี่ ยาโกะ','yako',40,35,'available'),
('Passion Yako','Passion Yako','Passion ယာကို','เสาวรส ยาโกะ','yako',50,35,'available');

-- MENU ITEM OPTIONS
INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(6,'sweetness',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(6,'sweetness',2,0,1,0,'Sweet','ချိုစိမ့်','หวาน'),
(6,'sweetness',3,0,1,0,'Glass Sweet','ဖန်ချို','แก้วหวาน'),
(6,'sweetness',4,0,1,0,'Normal Sweet','ပုံစိမ့်','ปกติหวาน'),
(6,'sweetness',5,0,1,0,'Less Sweet','ကျစိမ့်','หวานน้อย'),
(6,'sweetness',6,0,1,0,'More Sweet','ပေါ့စိမ့်','หวานมาก'),
(7,'milk',1,0,0,0,'Milk (Cold)','နို့အေး','นมเย็น'),
(10,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(10,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(10,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(10,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(10,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(10,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(10,'meat',3,0,1,0,'Fish Cake','ငါးဖယ်','ลูกชิ้นปลา'),
(10,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(11,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(11,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(11,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(11,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(11,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(12,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(12,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(12,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(12,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(12,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(12,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(12,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(13,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(13,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(13,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(13,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(13,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(13,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(13,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(14,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(14,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(14,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(14,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(14,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(14,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(14,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(15,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(15,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(15,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(15,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(15,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(15,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(15,'shrimp',1,15,0,0,'Shrimp','ပုစွန်','กุ้ง'),
(16,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(16,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(16,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(16,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(17,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(17,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(17,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(17,'vegetables',2,0,1,0,'No','မပါ','ไม่มี');

INSERT INTO menu_item_options (menu_item_id, option_group, sort_order, additional_price, is_required, is_multi_select, name_en, name_bu, name_th) VALUES
(18,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(18,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(18,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(18,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(18,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(18,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(18,'pork_ribs',1,20,0,0,'Pork Ribs','ဝက်နံရိုး','ซี่โครงหมู'),
(19,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(19,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(19,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(19,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(19,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(19,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(19,'pork_ribs',1,20,0,0,'Pork Ribs','ဝက်နံရိုး','ซี่โครงหมู'),
(20,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(20,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(20,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(20,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(20,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(20,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(20,'pork_ribs',1,20,0,0,'Pork Ribs','ဝက်နံရိုး','ซี่โครงหมู'),
(21,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(21,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(21,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(21,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(21,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(21,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(21,'meat',3,0,1,0,'Fish Cake','ငါးဖယ်','ลูกชิ้นปลา'),
(22,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(22,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(22,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(22,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(22,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(22,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(22,'meat',3,0,1,0,'Fish Cake','ငါးဖယ်','ลูกชิ้นปลา'),
(23,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(23,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(23,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(23,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(23,'pork_ribs',1,20,0,0,'Pork Ribs','ဝက်နံရိုး','ซี่โครงหมู'),
(25,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(25,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(25,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(25,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(25,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(25,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(25,'pork_ribs',1,20,0,0,'Pork Ribs','ဝက်နံရိုး','ซี่โครงหมู'),
(26,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(26,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(27,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(27,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(28,'spicy_level',1,0,1,0,'Less','နည်းနည်း','น้อย'),
(28,'spicy_level',2,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(28,'spicy_level',3,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(28,'vegetables',1,0,1,0,'Yes','ပါ','มี'),
(28,'vegetables',2,0,1,0,'No','မပါ','ไม่มี'),
(28,'add_on',1,10,0,1,'Duck Egg','ဘဲဥခြမ်း','ไข่เป็ด'),
(28,'add_on',2,10,0,1,'Fritter','အကြော်','ขนมทอด'),
(28,'add_on',3,10,0,1,'Fish Cake','ငါးဖယ်','ลูกชิ้นปลา'),
(29,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(29,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(30,'meat',1,0,1,0,'Chicken','ကြက်','ไก่'),
(30,'meat',2,0,1,0,'Pork','ဝက်','หมู'),
(35,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(35,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(36,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(36,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(37,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(37,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(38,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(38,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(39,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(39,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก'),
(40,'spicy_level',1,0,1,0,'Normal','ပုံမှန်','ปกติ'),
(40,'spicy_level',2,0,1,0,'More','ပိုပြင်း','เผ็ดมาก');

-- DONE
SELECT 'Setup complete!' AS status;
SELECT COUNT(*) AS menu_items_count FROM menu_items;
SELECT COUNT(*) AS options_count FROM menu_item_options;
