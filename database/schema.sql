CREATE DATABASE IF NOT EXISTS namvoy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE namvoy;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('traveler','provider','admin') NOT NULL DEFAULT 'traveler',
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) NULL,
  status ENUM('active','pending','suspended') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE providers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  business_name VARCHAR(190) NOT NULL,
  business_email VARCHAR(190) NULL,
  business_phone VARCHAR(40) NULL,
  description TEXT NULL,
  typical_min_budget DECIMAL(12,2) NULL,
  typical_max_budget DECIMAL(12,2) NULL,
  verification_status ENUM('pending','verified','trusted','preferred','rejected') NOT NULL DEFAULT 'pending',
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  response_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
  cancellation_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_provider_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  provider_id INT UNSIGNED NOT NULL,
  document_type VARCHAR(80) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_doc_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE destinations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  country VARCHAR(100) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  UNIQUE KEY uq_destination (name,country)
) ENGINE=InnoDB;

CREATE TABLE provider_destinations (
  provider_id INT UNSIGNED NOT NULL,
  destination_id INT UNSIGNED NOT NULL,
  expertise_level ENUM('standard','expert') NOT NULL DEFAULT 'standard',
  PRIMARY KEY (provider_id,destination_id),
  CONSTRAINT fk_provider_destination_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_provider_destination_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_categories (
  provider_id INT UNSIGNED NOT NULL,
  category VARCHAR(80) NOT NULL,
  PRIMARY KEY (provider_id,category),
  CONSTRAINT fk_provider_category_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE trip_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  request_number VARCHAR(30) NOT NULL UNIQUE,
  origin VARCHAR(150) NULL,
  destination VARCHAR(150) NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  adults TINYINT UNSIGNED NOT NULL DEFAULT 1,
  children TINYINT UNSIGNED NOT NULL DEFAULT 0,
  budget_min DECIMAL(12,2) NULL,
  budget_max DECIMAL(12,2) NULL,
  currency CHAR(3) NOT NULL DEFAULT 'INR',
  travel_type VARCHAR(80) NULL,
  description TEXT NULL,
  status ENUM('draft','published','receiving_offers','offer_selected','booked','expired','cancelled','completed') NOT NULL DEFAULT 'draft',
  expires_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_trip_user_status (user_id,status),
  INDEX idx_trip_destination_status (destination,status),
  CONSTRAINT fk_trip_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE trip_request_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_request_id INT UNSIGNED NOT NULL,
  item_type VARCHAR(60) NOT NULL,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_trip_item_request FOREIGN KEY (trip_request_id) REFERENCES trip_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_bids (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_request_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  bid_number VARCHAR(30) NOT NULL UNIQUE,
  total_amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'INR',
  description TEXT NULL,
  valid_until DATETIME NULL,
  status ENUM('draft','submitted','shortlisted','accepted','rejected','expired','withdrawn') NOT NULL DEFAULT 'submitted',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_bid_request_provider (trip_request_id,provider_id),
  INDEX idx_bid_request_status (trip_request_id,status),
  CONSTRAINT fk_bid_request FOREIGN KEY (trip_request_id) REFERENCES trip_requests(id) ON DELETE CASCADE,
  CONSTRAINT fk_bid_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_bid_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bid_id INT UNSIGNED NOT NULL,
  item_type VARCHAR(60) NOT NULL,
  item_id INT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_bid_item_bid FOREIGN KEY (bid_id) REFERENCES provider_bids(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_number VARCHAR(30) NOT NULL UNIQUE,
  user_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  trip_request_id INT UNSIGNED NULL,
  bid_id INT UNSIGNED NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax DECIMAL(12,2) NOT NULL DEFAULT 0,
  service_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency CHAR(3) NOT NULL DEFAULT 'INR',
  payment_status ENUM('pending','paid','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  booking_status ENUM('pending','confirmed','cancelled','completed','disputed') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_booking_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_booking_request FOREIGN KEY (trip_request_id) REFERENCES trip_requests(id) ON DELETE SET NULL,
  CONSTRAINT fk_booking_bid FOREIGN KEY (bid_id) REFERENCES provider_bids(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id INT UNSIGNED NOT NULL,
  receiver_id INT UNSIGNED NOT NULL,
  booking_id INT UNSIGNED NULL,
  trip_request_id INT UNSIGNED NULL,
  message TEXT NOT NULL,
  attachment VARCHAR(255) NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_messages_thread (trip_request_id,created_at),
  CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  CONSTRAINT fk_message_request FOREIGN KEY (trip_request_id) REFERENCES trip_requests(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type VARCHAR(60) NOT NULL,
  title VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notifications_user (user_id,is_read,created_at),
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
