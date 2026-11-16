-- ============================================================================
-- SMEIC School Canteen Management System - Database Schema + Seed
-- Run this file in MySQL (phpMyAdmin import is fine) before launching the app.
-- ============================================================================

DROP DATABASE IF EXISTS smeic_canteen;
CREATE DATABASE smeic_canteen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smeic_canteen;

-- ----------------------------------------------------------------------------
-- USERS (common auth table for admin, parent, canteen). Students authenticate
-- via students table by student_id.
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin','parent','canteen') NOT NULL,
    ref_id INT NOT NULL COMMENT 'FK to parents.id or canteen_staff.id',
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    force_password_change TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_ref ON users(ref_id);

-- ----------------------------------------------------------------------------
-- CANTEEN STAFF
-- ----------------------------------------------------------------------------
CREATE TABLE canteen_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    force_password_change TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- PARENTS
-- ----------------------------------------------------------------------------
CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT,
    wallet_balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    password VARCHAR(255) NOT NULL,
    force_password_change TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- STUDENTS
-- ----------------------------------------------------------------------------
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    class_name VARCHAR(50) DEFAULT NULL,
    roll_no VARCHAR(20) DEFAULT NULL,
    address TEXT,
    behavioral_score INT NOT NULL DEFAULT 100,
    parent_id INT DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    force_password_change TINYINT(1) NOT NULL DEFAULT 1,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX idx_student_class ON students(class_name);
CREATE INDEX idx_student_roll ON students(roll_no);

-- ----------------------------------------------------------------------------
-- STUDENT <-> PARENT LINKS (for future multi-child)
-- ----------------------------------------------------------------------------
CREATE TABLE student_parent_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_id INT NOT NULL,
    relationship VARCHAR(50) DEFAULT 'parent',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_link (student_id, parent_id),
    CONSTRAINT fk_link_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_link_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- WALLETS (student-side balance)
-- ----------------------------------------------------------------------------
CREATE TABLE wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wallet_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- MENU ITEMS
-- ----------------------------------------------------------------------------
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('veg','nonveg','snacks','drinks','desserts') NOT NULL DEFAULT 'veg',
    image VARCHAR(255) DEFAULT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_menu_cat ON menu_items(category);
CREATE INDEX idx_menu_avail ON menu_items(is_available);

-- ----------------------------------------------------------------------------
-- DAILY SPECIALS
-- ----------------------------------------------------------------------------
CREATE TABLE daily_specials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    special_date DATE NOT NULL,
    expires_at DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_special_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_special_active ON daily_specials(is_active, expires_at);

-- ----------------------------------------------------------------------------
-- CARTS
-- ----------------------------------------------------------------------------
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_cart (student_id, menu_item_id),
    CONSTRAINT fk_cart_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- ORDERS
-- ----------------------------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','accepted','preparing','ready','picked_up','cancelled') NOT NULL DEFAULT 'pending',
    prepared_time_minutes INT DEFAULT NULL,
    ready_at DATETIME DEFAULT NULL,
    picked_up_at DATETIME DEFAULT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    pickup_token VARCHAR(40) DEFAULT NULL,
    pickup_reminder_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_order_student ON orders(student_id);
CREATE INDEX idx_order_ready ON orders(ready_at);

-- ----------------------------------------------------------------------------
-- ORDER ITEMS
-- ----------------------------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- TRANSACTIONS (parent and student wallets)
-- ----------------------------------------------------------------------------
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('parent','student','admin') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    reference VARCHAR(255) DEFAULT NULL,
    balance_after DECIMAL(10,2) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_tx_user ON transactions(user_id, user_type);
CREATE INDEX idx_tx_created ON transactions(created_at);

-- ----------------------------------------------------------------------------
-- NOTIFICATIONS
-- ----------------------------------------------------------------------------
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('student','parent','canteen','admin') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_notif_user ON notifications(user_id, user_type);
CREATE INDEX idx_notif_read ON notifications(is_read);

-- ----------------------------------------------------------------------------
-- FEEDBACK
-- ----------------------------------------------------------------------------
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    order_id INT DEFAULT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fb_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_fb_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- PASSWORD RESETS
-- ----------------------------------------------------------------------------
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX idx_pw_email ON password_resets(email);
CREATE INDEX idx_pw_token ON password_resets(token);

-- ----------------------------------------------------------------------------
-- AUDIT LOGS
-- ----------------------------------------------------------------------------
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- PARENT PREFERENCES (parent suggests items to children)
-- ----------------------------------------------------------------------------
CREATE TABLE parent_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    preference_date DATE NOT NULL,
    note TEXT,
    is_seen TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pref_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
    CONSTRAINT fk_pref_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------------------------------------------------------
-- BEHAVIORAL SCORES (audit trail of score changes)
-- ----------------------------------------------------------------------------
CREATE TABLE behavioral_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    score INT NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bs_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Canteen staff
INSERT INTO canteen_staff (id, full_name, email, phone, password, force_password_change)
VALUES (1, 'Canteen Manager', 'canteen@canteen.com', '9000000000',
        '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1);

-- Admin user (uses users table only; ref_id points to a placeholder admin row)
-- We treat admin as self-contained in users table.
INSERT INTO users (id, role, ref_id, email, password, force_password_change, is_active)
VALUES (1, 'admin', 1, 'admin@canteen.com',
        '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1, 1);

-- Parents
INSERT INTO parents (id, parent_id, full_name, email, phone, address, wallet_balance, password, force_password_change)
VALUES
(1, 'P10001', 'Ramesh Kumar', 'parent1@school.com', '9111111111', '12 Park Street', 500.00,
    '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1),
(2, 'P10002', 'Sunita Sharma', 'parent2@school.com', '9222222222', '45 Lake View', 750.00,
    '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1);

-- Mirror parents into users table for unified auth
INSERT INTO users (id, role, ref_id, email, password, force_password_change, is_active) VALUES
(10, 'parent', 1, 'parent1@school.com', '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1, 1),
(11, 'parent', 2, 'parent2@school.com', '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1, 1);

-- Mirror canteen staff into users for unified auth
INSERT INTO users (id, role, ref_id, email, password, force_password_change, is_active) VALUES
(20, 'canteen', 1, 'canteen@canteen.com', '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1, 1);

-- Students
INSERT INTO students (id, student_id, full_name, class_name, roll_no, address, behavioral_score, parent_id, password, force_password_change)
VALUES
(1, 'S10001', 'Aarav Kumar', '10-A', '12', '12 Park Street', 95, 1,
    '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1),
(2, 'S10002', 'Diya Sharma', '9-B', '5', '45 Lake View', 98, 2,
    '$2y$10$5nZ1RkjA3L/Id5jKqLq1FOOQ0d2YKlO.OLjTk3CmrL/1qSrWkzS4i', 1);

-- Student parent links
INSERT INTO student_parent_links (student_id, parent_id, relationship) VALUES
(1, 1, 'father'),
(2, 2, 'mother');

-- Wallets
INSERT INTO wallets (student_id, balance) VALUES
(1, 250.00),
(2, 180.00);

-- Menu items (with image references)
INSERT INTO menu_items (id, name, description, price, category, image, is_available) VALUES
(1, 'Veg Pulao', 'Aromatic basmati rice with seasonal vegetables', 80.00, 'veg', 'menu_6a23f3b791d377.60388105.jpg', 1),
(2, 'Chicken Biryani', 'Spicy chicken biryani with raita', 140.00, 'nonveg', 'menu_6a23f3527dbe06.07510675.png', 1),
(3, 'Paneer Butter Masala', 'Creamy paneer curry with 2 rotis', 110.00, 'veg', 'menu_6a23f30b05ad33.81637616.webp', 1),
(4, 'Masala Dosa', 'Crispy dosa with potato filling and chutney', 70.00, 'veg', 'menu_6a23f3ab6c5648.21707180.png', 1),
(5, 'Veg Burger', 'Loaded veg patty burger with fries', 90.00, 'snacks', 'menu_6a23f35b57b3c2.74047410.jpg', 1),
(6, 'Cold Coffee', 'Chilled coffee with ice cream', 60.00, 'drinks', 'menu_6a23f3c0720809.15289220.jpg', 1),
(7, 'Fresh Lime Soda', 'Refreshing lime soda - sweet or salt', 30.00, 'drinks', 'menu_6a23f2e94c72d9.68499659.webp', 1),
(8, 'Samosa (2 pcs)', 'Crispy samosas with mint chutney', 25.00, 'snacks', 'menu_6a23f2fddef890.75732256.jpg', 1),
(9, 'Chocolate Brownie', 'Warm fudgy brownie', 50.00, 'desserts', 'menu_6a23f2f54d9ea7.87635705.jpg', 1),
(10, 'Egg Fried Rice', 'Fried rice with scrambled egg', 100.00, 'nonveg', 'menu_6a23f31d2404c1.54162767.webp', 1);

-- Active daily special
INSERT INTO daily_specials (menu_item_id, special_date, expires_at, is_active, created_by) VALUES
(4, CURDATE(), DATE_ADD(NOW(), INTERVAL 1 DAY), 1, 1);

-- Sample transactions (history)
INSERT INTO transactions (user_id, user_type, amount, type, reference, balance_after) VALUES
(1, 'parent', 500.00, 'credit', 'Wallet top-up by admin', 500.00),
(1, 'parent', 250.00, 'debit', 'Transfer to S10001', 250.00),
(1, 'student', 250.00, 'credit', 'Transfer from P10001', 250.00),
(2, 'parent', 750.00, 'credit', 'Wallet top-up by admin', 750.00),
(2, 'parent', 180.00, 'debit', 'Transfer to S10002', 570.00),
(2, 'student', 180.00, 'credit', 'Transfer from P10002', 180.00);

-- Behavioral score history
INSERT INTO behavioral_scores (student_id, score, reason, updated_by) VALUES
(1, 95, 'Initial score', 1),
(2, 98, 'Initial score', 1);

-- Sample parent preference
INSERT INTO parent_preferences (parent_id, menu_item_id, preference_date, note) VALUES
(1, 5, CURDATE(), 'Try the burger today — it is freshly prepared!');

-- The seeded password hash above is for 'Admin@123' (also reused for
-- Student@123, Parent@123, Canteen@123). The application will detect the
-- force_password_change=1 flag on first login and require a new password.
-- The hash is the bcrypt of 'Admin@123' generated by password_hash().

-- Done.
