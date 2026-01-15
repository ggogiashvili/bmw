-- მონაცემთა ბაზის შექმნა (თუ არ არსებობს)
CREATE DATABASE IF NOT EXISTS bmw_db;
-- ამ მონაცემთა ბაზის გამოყენება
USE bmw_db;

-- 1. მომხმარებლების ცხრილი
-- ინახავს ყველა რეგისტრირებული მომხმარებლის ინფორმაციას
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,                    -- უნიკალური ID (ავტომატურად იზრდება)
    username VARCHAR(50) NOT NULL UNIQUE,                  -- მომხმარებლის სახელი (უნიკალური, სავალდებულო)
    email VARCHAR(100) NOT NULL UNIQUE,                   -- ელ-ფოსტა (უნიკალური, სავალდებულო)
    password VARCHAR(255) NOT NULL,                       -- დაშიფრული პაროლი (სავალდებულო)
    role VARCHAR(20) DEFAULT 'user',                      -- როლი (admin ან user, default: user)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP        -- რეგისტრაციის თარიღი (ავტომატურად)
);

-- 2. სერიების ცხრილი (მაგ: 3 Series, 5 Series, X Series)
-- ინახავს BMW-ის სხვადასხვა სერიებს
CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,                    -- უნიკალური ID
    name VARCHAR(50) NOT NULL,                            -- სერიის სახელი (სავალდებულო)
    description TEXT                                       -- სერიის აღწერა
);

-- 3. მოდელების ცხრილი (მაგ: M340i, 330i)
-- ინახავს კონკრეტულ მოდელებს, რომლებიც ეკუთვნის გარკვეულ სერიას
CREATE TABLE IF NOT EXISTS models (
    id INT AUTO_INCREMENT PRIMARY KEY,                     -- უნიკალური ID
    series_id INT NOT NULL,                               -- სერიის ID (რომელ სერიას ეკუთვნის)
    name VARCHAR(100) NOT NULL,                           -- მოდელის სახელი (სავალდებულო)
    description TEXT,                                     -- მოდელის აღწერა
    year INT NOT NULL,                                    -- წელი (სავალდებულო)
    price DECIMAL(10, 2) NOT NULL,                        -- ფასი (სავალდებულო, 2 ათობითი ციფრი)
    image VARCHAR(255),                                   -- მთავარი ფოტოს სახელი
    is_slider TINYINT(1) DEFAULT 0,                      -- სლაიდერში ჩანს თუ არა (0=არა, 1=კი)
    FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE  -- კავშირი series ცხრილთან (თუ სერია წაიშლება, მოდელებიც წაიშლება)
);

-- 4. ძრავების ცხრილი
-- ინახავს ძრავის ინფორმაციას თითოეული მოდელისთვის
CREATE TABLE IF NOT EXISTS engines (
    id INT AUTO_INCREMENT PRIMARY KEY,                    -- უნიკალური ID
    model_id INT NOT NULL,                                -- მოდელის ID (რომელ მოდელს ეკუთვნის)
    type VARCHAR(100) NOT NULL,                          -- ძრავის ტიპი (მაგ: 3.0L TwinPower Turbo Inline-6)
    horsepower INT NOT NULL,                             -- ცხენის ძალა (სავალდებულო)
    torque VARCHAR(50) NOT NULL,                         -- თორქი (მაგ: 369 lb-ft)
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE  -- კავშირი models ცხრილთან
);

-- 5. სპეციფიკაციების ცხრილი (წონა, ზომები და სხვა)
-- ინახავს ტექნიკურ მახასიათებლებს თითოეული მოდელისთვის
CREATE TABLE IF NOT EXISTS specifications (
    id INT AUTO_INCREMENT PRIMARY KEY,                    -- უნიკალური ID
    model_id INT NOT NULL,                                -- მოდელის ID
    fuel_economy VARCHAR(50),                            -- საწვავის მოხმარება (მაგ: 23/31 mpg)
    acceleration VARCHAR(50),                            -- აჩქარება (მაგ: 4.1 sec 0-100)
    weight_kg INT,                                        -- წონა კილოგრამებში
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE  -- კავშირი models ცხრილთან
);

-- 6. მანქანების ფოტოების ცხრილი (გალერეა)
-- ინახავს გალერეის ფოტოებს თითოეული მოდელისთვის
CREATE TABLE IF NOT EXISTS car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,                     -- უნიკალური ID
    model_id INT NOT NULL,                                -- მოდელის ID
    image_path VARCHAR(255) NOT NULL,                    -- ფოტოს ფაილის სახელი (სავალდებულო)
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE  -- კავშირი models ცხრილთან
);

-- 7. რჩეულების ცხრილი (მომხმარებლის სურვილების სია)
-- ინახავს რომელი მოდელებია მომხმარებლის რჩეულებში
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,                    -- უნიკალური ID
    user_id INT NOT NULL,                                 -- მომხმარებლის ID
    model_id INT NOT NULL,                                -- მოდელის ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,       -- დამატების თარიღი (ავტომატურად)
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,    -- კავშირი users ცხრილთან
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE,  -- კავშირი models ცხრილთან
    UNIQUE KEY unique_favorite (user_id, model_id)       -- ერთი მომხმარებელი ერთი მოდელი მხოლოდ  (უნიკალური)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;                  -- InnoDB ძრავი და UTF-8 კოდირება

-- ============================================
-- ტესტური მონაცემების ჩასმა
-- ============================================

-- სერიების ტესტური მონაცემების ჩასმა
INSERT INTO series (name, description) VALUES 
('3 Series', 'The definition of the sports sedan segment.'),      -- 3 სერია
('5 Series', 'Executive mid-size luxury sedan.'),                  -- 5 სერია
('M Series', 'High-performance sports cars.');                      -- M სერია

-- მოდელების ტესტური მონაცემების ჩასმა
-- 3 Series-ის მოდელი
INSERT INTO models (series_id, name, year, price, image) VALUES 
(1, 'M340i xDrive', 2024, 59600.00, 'm340i.jpg');

-- M Series-ის მოდელი
INSERT INTO models (series_id, name, year, price, image) VALUES 
(3, 'M5 Competition', 2024, 110000.00, 'm5.jpg');

-- ძრავების ტესტური მონაცემების ჩასმა
INSERT INTO engines (model_id, type, horsepower, torque) VALUES 
(1, '3.0L BMW M TwinPower Turbo Inline-6', 382, '369 lb-ft'),      -- M340i-ის ძრავი
(2, '4.4L BMW M TwinPower Turbo V8', 617, '553 lb-ft');           -- M5-ის ძრავი

-- სპეციფიკაციების ტესტური მონაცემების ჩასმა
INSERT INTO specifications (model_id, fuel_economy, acceleration, weight_kg) VALUES 
(1, '23/31 city/hwy', '4.1s', 1750),                              -- M340i-ის სპეციფიკაციები
(2, '15/21 city/hwy', '3.1s', 1950);                               -- M5-ის სპეციფიკაციები

-- ============================================
-- ადმინისტრატორის მომხმარებლის შექმნა
-- ============================================
-- ელ-ფოსტა: admin@admin.com
-- პაროლი: admin123
-- შენიშვნა: პაროლი დაშიფრულია password_hash-ით
INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `role`) VALUES
	(1, 'admin', 'admin@admin.com', '$2y$10$D5/ErSwOp0qsbazs2Nl3pO/nzjanX197hBGeweVjCudl6RGIsDV5K', '2026-01-04 18:06:12', 'admin');