-- =============================================
-- SCHOOL MANAGEMENT SYSTEM - COMPLETE DATABASE
-- Run this file in phpMyAdmin or MySQL CLI
-- =============================================

-- Create database
CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

-- =============================================
-- TABLE: users (all user types)
-- =============================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'parent', 'student') NOT NULL DEFAULT 'parent',
    student_roll_no VARCHAR(50) NULL,
    parent_child_id INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    reset_token VARCHAR(64) NULL,
    reset_expires DATETIME NULL,
    login_attempts INT DEFAULT 0,
    last_login DATETIME NULL,
    last_ip VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_reset_token (reset_token),
    INDEX idx_active (is_active)
);

-- =============================================
-- TABLE: announcements
-- =============================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    posted_by INT NOT NULL,
    category ENUM('general', 'academic', 'exam', 'holiday', 'event') DEFAULT 'general',
    is_published BOOLEAN DEFAULT TRUE,
    published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE NULL,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_published (is_published, published_at),
    INDEX idx_category (category)
);

-- =============================================
-- TABLE: events
-- =============================================
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    event_date DATE NOT NULL,
    event_time TIME NULL,
    end_date DATE NULL,
    location VARCHAR(255) NULL,
    event_type ENUM('academic', 'sports', 'cultural', 'meeting', 'holiday', 'other') DEFAULT 'other',
    created_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date),
    INDEX idx_public (is_public)
);

-- =============================================
-- TABLE: contact_messages
-- =============================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_replied BOOLEAN DEFAULT FALSE,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read, created_at),
    INDEX idx_email (email)
);

-- =============================================
-- TABLE: classes (for school structure)
-- =============================================
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    section VARCHAR(10) NULL,
    class_teacher_id INT NULL,
    room_number VARCHAR(20) NULL,
    capacity INT DEFAULT 40,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (class_teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_class_name (class_name)
);
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- =============================================
-- TABLE: admissions (student applications)
-- =============================================
CREATE TABLE IF NOT EXISTS admissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    applying_for_class VARCHAR(50) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    mother_name VARCHAR(100) NOT NULL,
    parent_email VARCHAR(100) NOT NULL,
    parent_phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    previous_school VARCHAR(200) NULL,
    status ENUM('pending', 'approved', 'rejected', 'waiting') DEFAULT 'pending',
    remarks TEXT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (parent_email)
);

-- =============================================
-- TABLE: login_attempts (rate limiting)
-- =============================================
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100) NULL,
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempt_time)
);

-- =============================================
-- TABLE: settings (site configuration)
-- =============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    setting_type ENUM('text', 'textarea', 'boolean', 'number', 'file') DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- Insert default settings
-- =============================================
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('site_name', 'My School', 'text'),
('site_email', 'info@myschool.com', 'text'),
('site_phone', '+91 1234567890', 'text'),
('site_address', '123, School Road, City - 123456', 'textarea'),
('school_timing', '9:00 AM - 3:30 PM', 'text'),
('about_school', 'Welcome to our school. We provide quality education...', 'textarea');

-- =============================================
-- Create default admin user
-- Username: admin
-- Password: Admin@123 (CHANGE THIS AFTER FIRST LOGIN!)
-- =============================================
INSERT INTO users (username, full_name, email, password_hash, role, is_active) 
VALUES ('admin', 'School Administrator', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE);

-- =============================================
-- Insert sample announcements
-- =============================================
INSERT INTO announcements (title, content, posted_by, category, is_published) VALUES
('Welcome to New Academic Year', 'We are excited to welcome all students back for the new academic year. Classes will commence from June 15th.', 1, 'academic', TRUE),
('Parent-Teacher Meeting', 'Parent-Teacher meeting scheduled for March 20th at 10:00 AM in the school auditorium.', 1, 'event', TRUE),
('Holiday Notice', 'School will remain closed on March 25th on account of Holi.', 1, 'holiday', TRUE);

-- =============================================
-- Insert sample events
-- =============================================
INSERT INTO events (title, description, event_date, event_time, location, event_type, created_by) VALUES
('Annual Sports Day', 'Join us for the annual sports day celebration', DATE_ADD(CURDATE(), INTERVAL 15 DAY), '09:00:00', 'School Ground', 'sports', 1),
('Science Exhibition', 'Students showcase their science projects', DATE_ADD(CURDATE(), INTERVAL 30 DAY), '10:00:00', 'School Auditorium', 'academic', 1),
('PTA Meeting', 'Quarterly PTA meeting for all parents', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '11:00:00', 'Conference Hall', 'meeting', 1);

-- =============================================
-- Insert sample classes
-- =============================================
INSERT INTO classes (class_name, section, sort_order) VALUES
('Nursery', 'A', 1),
('LKG', 'A', 2),
('UKG', 'A', 3),
('1st Standard', 'A', 4),
('1st Standard', 'B', 4),
('2nd Standard', 'A', 5),
('3rd Standard', 'A', 6),
('4th Standard', 'A', 7),
('5th Standard', 'A', 8),
('6th Standard', 'A', 9),
('7th Standard', 'A', 10),
('8th Standard', 'A', 11),
('9th Standard', 'A', 12),
('10th Standard', 'A', 13);

-- =============================================
-- Create indexes for performance
-- =============================================
CREATE INDEX idx_announcements_published ON announcements(published_at DESC);
CREATE INDEX idx_events_upcoming ON events(event_date ASC);
CREATE INDEX idx_admissions_date ON admissions(application_date DESC);
