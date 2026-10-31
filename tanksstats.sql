SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS tanksstats CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tanksstats;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS tanks;
DROP TABLE IF EXISTS nations;
DROP TABLE IF EXISTS types;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS settings;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE nations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tanks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nation_id INT NULL,
    type_id INT NULL,
    image VARCHAR(255) NULL,
    description TEXT NULL,
    hp INT UNSIGNED NOT NULL DEFAULT 0,
    damage INT UNSIGNED NOT NULL DEFAULT 0,
    penetration INT UNSIGNED NOT NULL DEFAULT 0,
    reload DECIMAL(4,1) NOT NULL DEFAULT 1.0,
    speed INT UNSIGNED NOT NULL DEFAULT 0,
    armor_hull INT UNSIGNED NOT NULL DEFAULT 0,
    armor_turret INT UNSIGNED NOT NULL DEFAULT 0,
    view_range INT UNSIGNED NOT NULL DEFAULT 0,
    in_game TINYINT(1) NOT NULL DEFAULT 1,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tanks_nation FOREIGN KEY (nation_id) REFERENCES nations(id) ON DELETE SET NULL,
    CONSTRAINT fk_tanks_type FOREIGN KEY (type_id) REFERENCES types(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(50) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE favorites (
    user_id INT NOT NULL,
    tank_id INT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, tank_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_tank FOREIGN KEY (tank_id) REFERENCES tanks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    name VARCHAR(50) PRIMARY KEY,
    value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO nations (id, name) VALUES
(1, 'СССР'),
(2, 'Германия'),
(3, 'США'),
(4, 'Великобритания'),
(5, 'Франция'),
(6, 'Китай'),
(7, 'Япония'),
(8, 'Европа');

INSERT INTO types (id, code, name) VALUES
(1, 'ТТ', 'Тяжёлый танк'),
(2, 'СТ', 'Средний танк'),
(3, 'ЛТ', 'Лёгкий танк'),
(4, 'ПТ-САУ', 'ПТ-САУ');

INSERT INTO tanks (id, name, nation_id, type_id, image, description, hp, damage, penetration, reload, speed, armor_hull, armor_turret, view_range, in_game) VALUES
(1, 'ИС-7', 1, 1, 'is-7.webp', 'Советский тяжёлый танк с очень крепкой лобовой бронёй башни и хорошей для своего веса скоростью. Универсальный боец для первой линии.', 2400, 490, 250, 15.3, 59, 150, 240, 250, 1),
(2, 'ИС-4', 1, 1, 'is-4.webp', 'Тяжёлый танк с толстым бронированием корпуса и точным орудием. Хорошо держит удар в затяжных боях.', 2350, 440, 258, 13.6, 43, 140, 250, 250, 1),
(3, 'E 100', 2, 1, 'e-100.webp', 'Немецкий сверхтяжёлый танк с огромным запасом прочности и мощным разовым уроном. Медленный, но очень опасный в ближнем бою.', 2750, 680, 258, 20.0, 30, 200, 250, 250, 1),
(4, 'Maus', 2, 1, 'maus.webp', 'Самый бронированный и самый медленный танк в игре. Огромная прочность позволяет танковать за всю команду.', 3000, 490, 246, 13.0, 20, 220, 240, 240, 1),
(5, 'T110E5', 3, 1, 't110e5.webp', 'Американский тяжёлый танк с быстрой перезарядкой и хорошими углами наклона орудия. Отлично играет от рельефа.', 2350, 400, 258, 10.5, 37, 152, 203, 250, 1),
(6, 'FV215b', 4, 1, 'fv215b.webp', 'Британский тяжёлый танк с кормовой башней. Отличная точность и скорострельность, удобно стрелять из-за укрытий.', 2400, 400, 257, 11.4, 34, 152, 254, 250, 1),
(7, 'WZ-113', 6, 1, 'wz-113.webp', 'Китайский тяжёлый танк с крепкой башней и высоким разовым уроном, хорошо прорывает фланги.', 2450, 440, 250, 13.4, 50, 160, 240, 250, 1),
(8, 'Type 71', 7, 1, 'type-71.webp', 'Японский сверхтяжёлый танк с большим запасом прочности и сильной бронёй лобовой проекции.', 2600, 490, 250, 15.6, 35, 200, 260, 250, 1),
(9, 'Kranvagn', 8, 1, 'kranvagn.webp', 'Европейский тяжёлый танк с барабанным заряжанием: выпускает серию снарядов и уходит на долгую перезарядку. Очень крепкая башня.', 2200, 400, 252, 13.0, 40, 100, 280, 250, 1),
(10, 'AMX 50 B', 5, 1, 'amx-50-b.webp', 'Французский тяжёлый танк с барабаном. Быстрый и опасный, но со слабой бронёй, поэтому играет от внезапности.', 2100, 400, 257, 12.0, 65, 120, 170, 260, 1),
(11, 'Т-62А', 1, 2, 't-62a.webp', 'Советский средний танк с отличной точностью и крепкой башней. Сбалансированная машина для любой позиции.', 1750, 320, 264, 7.0, 50, 100, 240, 270, 1),
(12, 'Объект 140', 1, 2, 'obj-140.webp', 'Быстрый и манёвренный средний танк с хорошим пробитием. Отлично подходит для активной игры.', 1750, 320, 264, 7.2, 55, 100, 240, 270, 1),
(13, 'Leopard 1', 2, 2, 'leopard-1.webp', 'Немецкий средний танк с лучшей точностью среди средних танков. Почти нет брони, поэтому силён на дальней дистанции.', 1750, 330, 268, 8.9, 65, 70, 52, 270, 1),
(14, 'E 50 M', 2, 2, 'e-50-m.webp', 'Немецкий средний танк с хорошей бронёй и большой прочностью. Может играть почти как тяжёлый танк.', 2050, 390, 260, 9.6, 60, 150, 185, 260, 1),
(15, 'M48 Patton', 3, 2, 'm48-patton.webp', 'Американский средний танк с отличным обзором и высокой максимальной скоростью.', 1950, 390, 268, 9.0, 48, 110, 178, 260, 1),
(16, 'FV4202', 4, 2, 'fv4202.webp', 'Британский средний танк с крепкой башней и хорошими углами склонения орудия.', 1950, 390, 258, 8.8, 50, 127, 196, 260, 1),
(17, 'WZ-121', 6, 2, 'wz-121.webp', 'Китайский средний танк с высоким разовым уроном и прочной лобовой бронёй башни.', 1850, 390, 258, 9.6, 56, 120, 240, 260, 1),
(18, 'STB-1', 7, 2, 'stb-1.webp', 'Японский средний танк с гидроподвеской и отличными углами наклона орудия, силён на холмистых картах.', 1800, 390, 268, 8.8, 53, 110, 170, 260, 1),
(19, 'AMX 30 B', 5, 2, 'amx-30-b.webp', 'Французский средний танк с отличной динамикой и обзором. Брони почти нет, ставка на скорость.', 1800, 390, 248, 8.9, 65, 80, 80, 270, 1),
(20, 'Т-100 ЛТ', 1, 3, 't-100-lt.webp', 'Советский лёгкий танк с отличной маскировкой и обзором. Идеальный разведчик.', 1500, 240, 210, 5.6, 72, 90, 75, 270, 1),
(21, 'Rhm. Pzw.', 2, 3, 'rhm-pzw.webp', 'Немецкий лёгкий танк с очень высокой скоростью и хорошим пробитием для своего класса.', 1550, 240, 248, 5.5, 75, 20, 30, 270, 1),
(22, 'Sheridan', 3, 3, 'sheridan.webp', 'Американский лёгкий танк с мощным 152-мм орудием и высоким разовым уроном.', 1500, 360, 244, 10.5, 64, 31, 50, 270, 1),
(23, 'WZ-132-1', 6, 3, 'wz-132-1.webp', 'Китайский лёгкий танк с хорошим уроном и неплохой для лёгкого танка бронёй башни.', 1550, 300, 248, 8.5, 64, 50, 120, 270, 1),
(24, 'Объект 268', 1, 4, 'obj-268.webp', 'Советская ПТ-САУ с огромным разовым уроном и крепкой лобовой бронёй рубки.', 2000, 750, 303, 17.5, 48, 187, 187, 260, 1),
(25, 'Объект 263', 1, 4, 'obj-263.webp', 'Советская ПТ-САУ с наклонной бронёй и высокой скорострельностью. Крепкий лоб и хорошая скорость.', 1900, 490, 330, 12.0, 55, 250, 250, 260, 1),
(26, 'Grille 15', 2, 4, 'grille-15.webp', 'Немецкая ПТ-САУ с вращающейся башней, высоким пробитием и отличной точностью. Брони почти нет.', 1800, 640, 310, 14.5, 60, 50, 30, 270, 1),
(27, 'Jg.Pz. E 100', 2, 4, 'jg-pz-e-100.webp', 'Немецкая ПТ-САУ с одним из самых высоких разовых уронов в игре и толстой бронёй.', 2200, 1050, 299, 25.0, 30, 200, 300, 250, 1),
(28, 'T110E4', 3, 4, 't110e4.webp', 'Американская ПТ-САУ с вращающейся рубкой и мощным орудием. Крепкий лоб рубки.', 2000, 750, 303, 17.5, 35, 150, 254, 260, 1),
(29, 'FV215b (183)', 4, 4, 'fv215b-183.webp', 'Британская ПТ-САУ с огромным 183-мм орудием. Долгая перезарядка, но каждый выстрел наносит огромный урон.', 1850, 1150, 310, 26.0, 50, 76, 254, 260, 1),
(30, 'WZ-113G FT', 6, 4, 'wz-113g-ft.webp', 'Китайская ПТ-САУ с толстой бронёй и хорошим разовым уроном.', 2100, 750, 280, 18.0, 36, 200, 250, 260, 1),
(31, 'Foch 155', 5, 4, 'foch-155.webp', 'Французская ПТ-САУ с барабаном на несколько выстрелов. Может нанести очень много урона за короткое время.', 1900, 750, 300, 26.0, 50, 180, 180, 260, 1);

INSERT INTO users (id, username, email, password_hash, avatar, role) VALUES
(1, 'admin', 'admin@tanksstats.local', '$2y$10$ddLHeXCSe3yfS/gXcaLWqeoDDsimtpnnBfNHCTZOEhVVOcTXjSpxC', 'avatar1.svg', 'admin'),
(2, 'tanker', 'tanker@tanksstats.local', '$2y$10$4xJkHucDkR1N/0qDABM.s.in9DXZRR6/z44SGvSt.ujGxAkEcqf/u', 'avatar5.svg', 'user');

INSERT INTO favorites (user_id, tank_id) VALUES
(2, 13),
(2, 3),
(2, 26);

INSERT INTO settings (name, value) VALUES
('maintenance', '0'),
('maintenance_text', 'Обновляем данные о технике. Попробуйте зайти позже.'),
('welcome_text', 'Войдите, чтобы добавлять танки в избранное.'),
('catalog_intro', 'Характеристики танков 10 уровня из Tanks Blitz. Добавьте два танка в сравнение, чтобы увидеть разницу по каждому параметру.'),
('footer_text', '© 2026 TanksStats. Данные о технике из открытых источников.');
