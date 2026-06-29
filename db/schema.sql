-- MySQL schema for QR Table Ordering

CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  -- Legacy single-language name column (keep for backward compatibility).
  name VARCHAR(255) NULL,

  -- Multilingual names.
  name_en VARCHAR(255) NULL,
  name_bu VARCHAR(255) NULL,
  name_th VARCHAR(255) NULL,

  -- Used to render category sections in the QR menu.
  category VARCHAR(50) NOT NULL DEFAULT 'menu',
  sort_order INT NOT NULL DEFAULT 0,

  price DECIMAL(10,2) NOT NULL,
  status ENUM('available','out_of_stock') NOT NULL DEFAULT 'available'
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
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_order_items_item
    FOREIGN KEY (item_id) REFERENCES menu_items(id)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_orders_status_created_at ON orders(status, created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_menu_items_category_sort ON menu_items(category, sort_order);

