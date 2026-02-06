-- Copia el siguiente schema para generar la db en XAMPP

CREATE DATABASE IF NOT EXISTS cv_generator
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE cv_generator;

CREATE TABLE IF NOT EXISTS cv_versions (
id INT AUTO_INCREMENT PRIMARY KEY,
version_num INT NOT NULL,
created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

full_name VARCHAR(120) NOT NULL,
profession VARCHAR(120) NOT NULL,
phone VARCHAR(40) NOT NULL,
email VARCHAR(160) NOT NULL,
address VARCHAR(160) NOT NULL,
about VARCHAR(250) NOT NULL,

company VARCHAR(160) NOT NULL,
position VARCHAR(160) NOT NULL,
work_start DATE NOT NULL,
work_end DATE NULL,
work_description VARCHAR(250) NULL,

school VARCHAR(160) NOT NULL,
qualification VARCHAR(160) NOT NULL,
edu_start DATE NOT NULL,
edu_end DATE NULL,
edu_description VARCHAR(250) NULL,

skills VARCHAR(250) NOT NULL,
languages VARCHAR(250) NULL,

photo_path VARCHAR(255) NULL,

UNIQUE KEY uniq_version (version_num)
) ENGINE=InnoDB;