-- Create the database
CREATE DATABASE IF NOT EXISTS webarchiver;
USE webarchiver;

-- Create the user
CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'pass@webuser';

-- Grant privileges to the user on the database
GRANT ALL PRIVILEGES ON webarchiver.* TO 'webuser'@'localhost';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Create the 'websites' table
CREATE TABLE IF NOT EXISTS websites (
  id INT PRIMARY KEY AUTO_INCREMENT,
  url VARCHAR(255),
  dir VARCHAR(255),
  date VARCHAR(255)
);
