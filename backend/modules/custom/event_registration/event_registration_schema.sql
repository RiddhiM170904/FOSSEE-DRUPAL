--
-- Database schema for Event Registration module
-- This file creates the necessary tables for the event_registration module
--

--
-- Table structure for table `event_config`
--

CREATE TABLE IF NOT EXISTS `event_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique event configuration ID.',
  `event_name` varchar(255) NOT NULL COMMENT 'Name of the event.',
  `event_category` varchar(100) NOT NULL COMMENT 'Category of the event.',
  `event_date` varchar(20) NOT NULL COMMENT 'Date of the event.',
  `registration_start_date` varchar(20) NOT NULL COMMENT 'Registration start date.',
  `registration_end_date` varchar(20) NOT NULL COMMENT 'Registration end date.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp when the event was created.',
  PRIMARY KEY (`id`),
  KEY `event_category` (`event_category`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores event configuration details.';

--
-- Table structure for table `event_registration`
--

CREATE TABLE IF NOT EXISTS `event_registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique registration ID.',
  `full_name` varchar(255) NOT NULL COMMENT 'Full name of the registrant.',
  `email` varchar(255) NOT NULL COMMENT 'Email address of the registrant.',
  `college_name` varchar(255) NOT NULL COMMENT 'College name of the registrant.',
  `department` varchar(255) NOT NULL COMMENT 'Department of the registrant.',
  `event_category` varchar(100) NOT NULL COMMENT 'Category of the event.',
  `event_date` varchar(20) NOT NULL COMMENT 'Date of the event.',
  `event_config_id` int(11) NOT NULL COMMENT 'Foreign key to event_config table.',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT 'Timestamp when the registration was created.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_event_date` (`email`,`event_date`),
  KEY `email` (`email`),
  KEY `event_date` (`event_date`),
  KEY `event_config_id` (`event_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores event registration details.';

--
-- Sample data for table `event_config` (optional)
--

INSERT INTO `event_config` (`event_name`, `event_category`, `event_date`, `registration_start_date`, `registration_end_date`, `created`) VALUES
('Introduction to Web Development', 'Online Workshop', '2026-02-15', '2026-01-25', '2026-02-10', UNIX_TIMESTAMP()),
('AI/ML Bootcamp', 'Hackathon', '2026-03-01', '2026-01-25', '2026-02-25', UNIX_TIMESTAMP()),
('Tech Summit 2026', 'Conference', '2026-04-10', '2026-01-25', '2026-04-05', UNIX_TIMESTAMP()),
('Python Basics Workshop', 'One-day Workshop', '2026-02-20', '2026-01-25', '2026-02-18', UNIX_TIMESTAMP());
