CREATE DATABASE IF NOT EXISTS webarchiver;
USE webarchiver;

CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'pass@webuser';

GRANT ALL PRIVILEGES ON webarchiver.* TO 'webuser'@'localhost';

FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS websites (
  id INT PRIMARY KEY AUTO_INCREMENT,
  url VARCHAR(255),
  dir VARCHAR(255),
  date VARCHAR(255),
  mode VARCHAR(255)
);
