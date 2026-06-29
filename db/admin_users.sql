-- Admin users table for authentication
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (username: admin, password: morningstarhuamak)
-- Password is hashed with bcrypt
INSERT INTO admin_users (username, password) VALUES 
('admin', '$2y$10$PB.BQIx4cgsWFOaPJo.2uOX3hTK4jOVJ12elBI6m9V95XcoHi.axa')
ON DUPLICATE KEY UPDATE password='$2y$10$PB.BQIx4cgsWFOaPJo.2uOX3hTK4jOVJ12elBI6m9V95XcoHi.axa';
