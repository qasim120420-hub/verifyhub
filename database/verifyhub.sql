-- ============================================
-- VERIFYHUB - Complete Database Schema
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: verifyhub
CREATE DATABASE IF NOT EXISTS verifyhub CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE verifyhub;

-- ============================================
-- 1. USERS
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    status ENUM('active','blocked','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. WALLETS
-- ============================================
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    total_deposited DECIMAL(15,2) DEFAULT 0.00,
    total_spent DECIMAL(15,2) DEFAULT 0.00,
    total_refunded DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. COUNTRIES
-- ============================================
CREATE TABLE IF NOT EXISTS countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(5) NOT NULL,
    flag_icon VARCHAR(100) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. SERVICES
-- ============================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(100) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. NUMBERS
-- ============================================
CREATE TABLE IF NOT EXISTS numbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(20) NOT NULL,
    type ENUM('virtual','physical') NOT NULL,
    country_id INT DEFAULT NULL,
    service_id INT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('available','assigned','used','disabled','expired') DEFAULT 'available',
    assigned_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. ORDERS
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    number_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','waiting_otp','otp_received','completed','cancelled','expired') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (number_id) REFERENCES numbers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. OTP MESSAGES
-- ============================================
CREATE TABLE IF NOT EXISTS otp_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    number_id INT NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    full_message TEXT,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (number_id) REFERENCES numbers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. WALLET TRANSACTIONS
-- ============================================
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit','purchase','refund','transfer','admin_credit','admin_debit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    previous_balance DECIMAL(15,2) NOT NULL,
    new_balance DECIMAL(15,2) NOT NULL,
    description TEXT,
    status ENUM('pending','completed','failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 9. DEPOSITS
-- ============================================
CREATE TABLE IF NOT EXISTS deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    receipt_image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 10. NOTIFICATIONS
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 11. SUPPORT TICKETS
-- ============================================
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(150) NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    priority ENUM('low','medium','high') DEFAULT 'medium',
    status ENUM('open','in_progress','waiting_user','resolved','closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 12. SUPPORT MESSAGES
-- ============================================
CREATE TABLE IF NOT EXISTS support_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    attachment VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 13. LOGIN LOGS
-- ============================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 14. ADMIN ACTIVITY LOGS
-- ============================================
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_id INT DEFAULT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 15. HELP ARTICLES
-- ============================================
CREATE TABLE IF NOT EXISTS help_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 16. USER SESSIONS
-- ============================================
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 17. THEME SETTINGS
-- ============================================
CREATE TABLE IF NOT EXISTS theme_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 18. PAYMENT RECEIPTS
-- ============================================
CREATE TABLE IF NOT EXISTS payment_receipts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deposit_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (deposit_id) REFERENCES deposits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Default Admin User (Password: admin123)
INSERT INTO users (username, full_name, email, phone, password, role, status) 
VALUES ('admin', 'Super Admin', 'admin@verifyhub.com', '+920000000000', 
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Admin wallet
INSERT INTO wallets (user_id, balance) 
SELECT id, 0.00 FROM users WHERE email='admin@verifyhub.com';

-- ============================================
-- DEFAULT COUNTRIES
-- ============================================
INSERT INTO countries (name, code, flag_icon) VALUES 
('Pakistan', 'PK', '🇵🇰'),
('United States', 'US', '🇺🇸'),
('United Kingdom', 'UK', '🇬🇧'),
('India', 'IN', '🇮🇳'),
('UAE', 'AE', '🇦🇪'),
('Saudi Arabia', 'SA', '🇸🇦'),
('Canada', 'CA', '🇨🇦'),
('Australia', 'AU', '🇦🇺'),
('Germany', 'DE', '🇩🇪'),
('France', 'FR', '🇫🇷');

-- ============================================
-- DEFAULT SERVICES
-- ============================================
INSERT INTO services (name, icon, price) VALUES
('WhatsApp', '💬', 150.00),
('Telegram', '✈️', 120.00),
('Facebook', '📘', 100.00),
('Google', '🔍', 80.00),
('Instagram', '📸', 130.00),
('Twitter', '🐦', 110.00),
('TikTok', '🎵', 140.00),
('Discord', '💬', 90.00);

-- ============================================
-- DEFAULT THEME SETTINGS
-- ============================================
INSERT INTO theme_settings (setting_key, setting_value) VALUES
('primary_color', '#8B5CF6'),
('secondary_color', '#A855F7'),
('accent_color', '#EC4899'),
('bg_color', '#F8F7FC'),
('sidebar_color', '#0F0C29'),
('text_color', '#0F0F23'),
('card_color', '#FFFFFF'),
('border_color', '#E8E5F5'),
('site_name', 'VerifyHub'),
('site_tagline', 'Smart Numbers, Instant Verification.'),
('currency', 'PKR'),
('min_deposit', '100'),
('max_deposit', '100000'),
('min_transfer', '1'),
('max_transfer', '50000'),
('order_expiry', '20'),
('otp_expiry', '300'),
('otp_length', '6'),
('otp_expiry_minutes', '5'),
('otp_max_attempts', '3'),
('otp_resend_seconds', '60'),
('max_login_attempts', '5'),
('registration_enabled', '1'),
('support_email', 'support@verifyhub.com'),
('support_phone', '+920000000000'),
('site_url', 'http://localhost/verifyhub'),
('payment_instructions_bank_transfer', 'Bank: Meezan Bank | Account: 0123-4567890123 | Title: VerifyHub Pvt Ltd'),
('payment_instructions_easypaisa', 'Account: 0300-1234567 | Title: VerifyHub'),
('payment_instructions_jazzcash', 'Account: 0301-7654321 | Title: VerifyHub'),
('payment_instructions_usdt', 'TRC20 Wallet: TXxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

-- ============================================
-- SAMPLE HELP ARTICLES
-- ============================================
INSERT INTO help_articles (category, title, content) VALUES
('Account', 'How to create an account?', 'Click on Create Account button on homepage, fill the form, and submit.'),
('Wallet', 'How to add funds?', 'Go to Wallet → Deposit Funds, upload payment proof, and wait for admin approval.'),
('Numbers', 'How to buy a number?', 'Go to Numbers → Buy Number, follow 5 steps, and complete purchase.'),
('Orders', 'What is order expiry?', 'Orders expire after 20 minutes if no OTP is received. Amount is refunded automatically.'),
('OTP', 'How long do OTPs take?', 'OTPs usually arrive within 5-30 seconds.'),
('Support', 'How to contact support?', 'Go to Support → Create Ticket and submit your query.');

COMMIT;

-- ============================================
-- ✅ DATABASE READY
-- ============================================