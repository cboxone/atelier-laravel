CREATE DATABASE atelier_froid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'atelier'@'localhost' IDENTIFIED BY 'CHANGER_CE_MOT_DE_PASSE';
GRANT ALL PRIVILEGES ON atelier_froid.* TO 'atelier'@'localhost';
FLUSH PRIVILEGES;
