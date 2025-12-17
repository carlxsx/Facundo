-- ============================================
-- CAR MARKETPLACE DATABASE FOR INFINITYFREE
-- ============================================
-- IMPORTANT: This SQL is for InfinityFree hosting
-- 1. Go to phpMyAdmin
-- 2. Select your existing database (e.g., if0_40582828)
-- 3. Make sure database is selected (highlighted on left)
-- 4. Then import this file
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- USERS TABLE
-- ============================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    profile_image VARCHAR(255),
    location VARCHAR(100),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB;

-- ============================================
-- SOCIAL AUTH TABLE (Google, Facebook Login)
-- ============================================
CREATE TABLE social_auth (
    auth_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    provider ENUM('google', 'facebook') NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    access_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_provider_user (provider, provider_user_id)
) ENGINE=InnoDB;

-- ============================================
-- CAR LISTINGS TABLE
-- ============================================
CREATE TABLE cars (
    car_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    mileage INT NOT NULL,
    condition_type ENUM('New', 'Used') NOT NULL,
    engine_details VARCHAR(255),
    transmission ENUM('Manual', 'Automatic', 'CVT', 'DCT') DEFAULT 'Automatic',
    fuel_type ENUM('Gasoline', 'Diesel', 'Electric', 'Hybrid') DEFAULT 'Gasoline',
    body_type VARCHAR(50),
    color VARCHAR(50),
    seats INT,
    location VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected', 'sold', 'reserved') DEFAULT 'pending',
    is_featured BOOLEAN DEFAULT FALSE,
    sell_to_website BOOLEAN DEFAULT FALSE,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by INT NULL,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_brand (brand),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_year (year),
    INDEX idx_featured (is_featured),
    FULLTEXT idx_search (brand, model, description)
) ENGINE=InnoDB;

-- ============================================
-- CAR IMAGES TABLE
-- ============================================
CREATE TABLE car_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    upload_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB;

-- ============================================
-- CAR VIDEOS TABLE
-- ============================================
CREATE TABLE car_videos (
    video_id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    video_path VARCHAR(255) NOT NULL,
    video_type ENUM('tour', 'engine', 'interior', 'exterior') DEFAULT 'tour',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB;

-- ============================================
-- FAVORITES / WISHLIST TABLE
-- ============================================
CREATE TABLE favorites (
    favorite_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, car_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- ============================================
-- CHAT CONVERSATIONS TABLE
-- ============================================
CREATE TABLE conversations (
    conversation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    admin_id INT NULL,
    car_id INT NULL,
    subject VARCHAR(255),
    status ENUM('open', 'closed', 'assigned') DEFAULT 'open',
    assigned_to INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- CHAT MESSAGES TABLE
-- ============================================
CREATE TABLE messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    file_path VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================
-- NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('car_approved', 'car_rejected', 'new_message', 'price_drop', 'appointment_confirmed', 'reservation', 'general') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INT NULL,
    related_type VARCHAR(50) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================
-- APPOINTMENTS TABLE
-- ============================================
CREATE TABLE appointments (
    appointment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    appointment_type ENUM('test_drive', 'viewing', 'inspection') NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    location VARCHAR(255),
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_car_id (car_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- RESERVATIONS TABLE
-- ============================================
CREATE TABLE reservations (
    reservation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    reservation_amount DECIMAL(12, 2),
    payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(100),
    status ENUM('active', 'expired', 'completed', 'cancelled') DEFAULT 'active',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_car_id (car_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================
-- TRANSACTIONS TABLE
-- ============================================
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    reservation_id INT NULL,
    transaction_type ENUM('purchase', 'reservation', 'refund') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(100),
    invoice_number VARCHAR(50) UNIQUE,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_car_id (car_id),
    INDEX idx_transaction_date (transaction_date),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB;

-- ============================================
-- CAR INSPECTION CHECKLIST TABLE
-- ============================================
CREATE TABLE inspection_checklist (
    checklist_id INT PRIMARY KEY AUTO_INCREMENT,
    car_id INT NOT NULL,
    exterior_condition VARCHAR(50),
    interior_condition VARCHAR(50),
    engine_condition VARCHAR(50),
    transmission_condition VARCHAR(50),
    tire_condition VARCHAR(50),
    brake_condition VARCHAR(50),
    electrical_systems VARCHAR(50),
    accident_history BOOLEAN DEFAULT FALSE,
    service_history TEXT,
    checklist_file VARCHAR(255),
    inspected_by INT NULL,
    inspected_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE,
    FOREIGN KEY (inspected_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_car_id (car_id)
) ENGINE=InnoDB;

-- ============================================
-- ADMIN ACTIVITY LOG TABLE
-- ============================================
CREATE TABLE admin_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    related_id INT NULL,
    related_type VARCHAR(50) NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- ============================================
-- SETTINGS TABLE
-- ============================================
CREATE TABLE settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50),
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- INSERT SAMPLE DATA
-- ============================================

-- Insert Admin User
INSERT INTO users (username, email, password_hash, full_name, phone, user_type, location, is_verified, is_active) VALUES
('admin', 'admin@carmarket.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', '+639123456789', 'admin', 'Manila', TRUE, TRUE),
('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '+639987654321', 'buyer', 'Quezon City', TRUE, TRUE),
('seller1', 'seller@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Santos', '+639111222333', 'seller', 'Makati', TRUE, TRUE);

-- Insert Sample Cars
INSERT INTO cars (seller_id, brand, model, year, price, mileage, condition_type, engine_details, transmission, fuel_type, body_type, color, seats, location, description, status, is_featured) VALUES
(1, 'Toyota', 'Camry', 2022, 1450000.00, 15000, 'Used', '2.5L 4-Cylinder', 'Automatic', 'Gasoline', 'Sedan', 'Silver', 5, 'Manila', 'Well-maintained Toyota Camry with full service history. Perfect condition, single owner.', 'approved', TRUE),
(1, 'Honda', 'Civic', 2023, 1250000.00, 8000, 'Used', '2.0L 4-Cylinder', 'CVT', 'Gasoline', 'Sedan', 'White', 5, 'Quezon City', 'Almost new Honda Civic with low mileage. Garage kept and regularly serviced.', 'approved', TRUE),
(3, 'Ford', 'Ranger', 2021, 1750000.00, 25000, 'Used', '2.2L Turbo Diesel', 'Automatic', 'Diesel', 'Pickup', 'Black', 5, 'Makati', 'Powerful Ford Ranger, great for business and adventure. Well-maintained with complete papers.', 'approved', FALSE),
(3, 'Mitsubishi', 'Montero Sport', 2020, 1680000.00, 35000, 'Used', '2.4L Diesel', 'Automatic', 'Diesel', 'SUV', 'Gray', 7, 'Pasig', 'Spacious family SUV with 7 seats. Excellent condition, accident-free.', 'approved', TRUE),
(1, 'Mazda', 'CX-5', 2023, 1850000.00, 5000, 'Used', '2.5L Skyactiv', 'Automatic', 'Gasoline', 'SUV', 'Red', 5, 'Taguig', 'Premium SUV with advanced safety features. Almost brand new!', 'pending', FALSE);

-- Insert Sample Car Images
INSERT INTO car_images (car_id, image_path, is_primary, upload_order) VALUES
(1, '/uploads/cars/camry_1.jpg', TRUE, 1),
(1, '/uploads/cars/camry_2.jpg', FALSE, 2),
(2, '/uploads/cars/civic_1.jpg', TRUE, 1),
(3, '/uploads/cars/ranger_1.jpg', TRUE, 1),
(4, '/uploads/cars/montero_1.jpg', TRUE, 1);

-- Insert Sample Conversations
INSERT INTO conversations (user_id, admin_id, car_id, subject, status) VALUES
(2, 1, 1, 'Inquiry about Toyota Camry', 'open'),
(2, 1, 2, 'Schedule test drive for Honda Civic', 'assigned');

-- Insert Sample Messages
INSERT INTO messages (conversation_id, sender_id, message_text, is_read) VALUES
(1, 2, 'Hi, is this car still available?', TRUE),
(1, 1, 'Yes! The Toyota Camry is still available. Would you like to schedule a viewing?', TRUE),
(1, 2, 'Yes please! Can I see it this weekend?', FALSE);

-- Insert Sample Notifications
INSERT INTO notifications (user_id, type, title, message, related_id, related_type) VALUES
(2, 'new_message', 'New Message', 'Admin replied to your inquiry about Toyota Camry', 1, 'conversation'),
(3, 'car_approved', 'Car Approved', 'Your Ford Ranger listing has been approved and is now live!', 3, 'car');

-- Insert Sample Settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'AutoHub Car Marketplace', 'text', 'Website name'),
('site_email', 'info@carmarket.com', 'email', 'Contact email'),
('reservation_fee_percentage', '10', 'number', 'Reservation fee percentage'),
('featured_cars_limit', '4', 'number', 'Number of featured cars on homepage'),
('currency', 'PHP', 'text', 'Default currency'),
('enable_notifications', '1', 'boolean', 'Enable push notifications');

-- ============================================
-- USEFUL QUERIES FOR YOUR PHP CODE
-- ============================================
-- Since InfinityFree doesn't support Views, use these queries directly in PHP:

-- Get Active Car Listings with Images:
-- SELECT c.*, u.full_name AS seller_name, u.phone AS seller_phone, 
--        ci.image_path AS primary_image, COUNT(DISTINCT f.favorite_id) AS favorite_count
-- FROM cars c
-- LEFT JOIN users u ON c.seller_id = u.user_id
-- LEFT JOIN car_images ci ON c.car_id = ci.car_id AND ci.is_primary = TRUE
-- LEFT JOIN favorites f ON c.car_id = f.car_id
-- WHERE c.status = 'approved'
-- GROUP BY c.car_id;

-- Get User Dashboard Stats:
-- SELECT u.user_id, u.full_name, u.email,
--        COUNT(DISTINCT c.car_id) AS total_listings,
--        COUNT(DISTINCT f.favorite_id) AS total_favorites,
--        COUNT(DISTINCT conv.conversation_id) AS total_conversations
-- FROM users u
-- LEFT JOIN cars c ON u.user_id = c.seller_id
-- LEFT JOIN favorites f ON u.user_id = f.user_id
-- LEFT JOIN conversations conv ON u.user_id = conv.user_id
-- GROUP BY u.user_id;

-- ============================================
-- END OF DATABASE SETUP
-- ============================================