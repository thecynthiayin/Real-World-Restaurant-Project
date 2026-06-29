-- ============================================================
-- QR Table Ordering — Full Database Setup for Railway
-- Run this once in the Railway MySQL console after deployment
-- ============================================================

-- 1. Core tables
CREATE TABLE IF NOT EXISTS menu_items (
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

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_number INT NOT NULL,
  order_type ENUM('eat_in','take_away') NOT NULL,
  total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('pending','done') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  selected_options JSON NULL,
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_item
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Menu item options
CREATE TABLE IF NOT EXISTS menu_item_options (
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
  CONSTRAINT fk_menu_item_options_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Admin users
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin (username: admin, password: morningstarhuamak)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$PB.BQIx4cgsWFOaPJo.2uOX3hTK4jOVJ12elBI6m9V95XcoHi.axa')
ON DUPLICATE KEY UPDATE password='$2y$10$PB.BQIx4cgsWFOaPJo.2uOX3hTK4jOVJ12elBI6m9V95XcoHi.axa';

-- 4. Reports tables
CREATE TABLE IF NOT EXISTS daily_reports (
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

CREATE TABLE IF NOT EXISTS monthly_reports (
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

CREATE TABLE IF NOT EXISTS popular_items_monthly (
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
  INDEX idx_item_id (item_id),
  FOREIGN KEY (item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Indexes
CREATE INDEX idx_orders_status_created_at ON orders(status, created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_menu_items_category_sort ON menu_items(category, sort_order);
CREATE INDEX idx_menu_item_options_menu_item ON menu_item_options(menu_item_id);
CREATE INDEX idx_menu_item_options_group ON menu_item_options(option_group);
