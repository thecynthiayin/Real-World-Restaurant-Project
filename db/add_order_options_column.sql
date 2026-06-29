-- Add selected_options column to order_items table
ALTER TABLE order_items ADD COLUMN selected_options JSON NULL COMMENT 'Selected menu item options as JSON';
