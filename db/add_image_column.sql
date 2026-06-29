-- Add image column to menu_items table
ALTER TABLE menu_items ADD COLUMN image VARCHAR(255) NULL AFTER status;
