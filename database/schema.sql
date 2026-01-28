
CREATE DATABASE erp_help_center;
USE erp_help_center;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'visitor') DEFAULT 'visitor'
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    is_archived BOOLEAN DEFAULT FALSE
);

-- FAQs table
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    category_id INT NOT NULL,
    upvotes INT DEFAULT 0,
    downvotes INT DEFAULT 0,
    is_archived BOOLEAN DEFAULT FALSE,
    archived_by INT NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (archived_by) REFERENCES users(id)
);

-- Support questions table
CREATE TABLE support_questions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_email VARCHAR(255) NOT NULL,
    question TEXT NOT NULL,
    admin_viewed BOOLEAN DEFAULT FALSE,
    answered BOOLEAN DEFAULT FALSE,
    answered_by INT NULL,
    faq_id INT NULL UNIQUE,
    
    FOREIGN KEY (answered_by) REFERENCES users(id),
    FOREIGN KEY (faq_id) REFERENCES faqs(id)
);

-- Insert default admin (password: admin123)
INSERT INTO users (username, password_hash, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Billing', 'Payment and invoice questions'),
('Account', 'User account management'),
('Reports', 'Data export and reporting');