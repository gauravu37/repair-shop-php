CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial record
INSERT INTO `settings` (`id`, `business_name`, `contact_number`, `logo_path`, `qr_code_path`) 
VALUES (1, 'My Business', '+1234567890', NULL, NULL)