CREATE DATABASE IF NOT EXISTS stmichael_school
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stmichael_school;

CREATE TABLE IF NOT EXISTS enquiries (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kind ENUM('admission', 'contact') NOT NULL,
  student_name VARCHAR(120) NOT NULL,
  guardian_name VARCHAR(120) NULL,
  phone VARCHAR(24) NOT NULL,
  class_applying_for VARCHAR(32) NULL,
  email VARCHAR(254) NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_enquiries_created_at (created_at),
  INDEX idx_enquiries_kind_created_at (kind, created_at)
) ENGINE=InnoDB;
