-- Menu Item Options Schema
-- Supports multilingual options for menu items (ENG/BU/TH)

-- Add photo column to menu_items
ALTER TABLE menu_items ADD COLUMN photo VARCHAR(255) NULL AFTER status;

-- Create menu_item_options table
CREATE TABLE IF NOT EXISTS menu_item_options (
  id INT AUTO_INCREMENT PRIMARY KEY,
  menu_item_id INT NOT NULL,
  option_group VARCHAR(50) NOT NULL DEFAULT 'default', -- e.g., 'temperature', 'size', 'add_on'
  sort_order INT NOT NULL DEFAULT 0,
  additional_price DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_required BOOLEAN NOT NULL DEFAULT FALSE,
  is_multi_select BOOLEAN NOT NULL DEFAULT FALSE,
  
  -- Multilingual option names
  name_en VARCHAR(255) NOT NULL,
  name_bu VARCHAR(255) NULL,
  name_th VARCHAR(255) NULL,
  
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  CONSTRAINT fk_menu_item_options_menu_item
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for better performance
CREATE INDEX idx_menu_item_options_menu_item ON menu_item_options(menu_item_id);
CREATE INDEX idx_menu_item_options_group ON menu_item_options(option_group);
