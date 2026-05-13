CREATE DATABASE appointment_system;

USE appointment_system;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20),
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role VARCHAR(20),
  major VARCHAR(100),
  year INT,
  phone VARCHAR(20)
);

CREATE TABLE time_slots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE,
  start_time TIME,
  end_time TIME,
  status VARCHAR(20),
  admin_id INT,
  FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  time_slot_id INT,
  status VARCHAR(20),
  booking_date DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (time_slot_id) REFERENCES time_slots(id)
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);