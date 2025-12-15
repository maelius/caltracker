CREATE TABLE foods (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(120) NOT NULL,
image_url VARCHAR(500) DEFAULT NULL,
description TEXT,
calories_per_100g INT NOT NULL CHECK (calories_per_100g >= 0),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE meals (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) NOT NULL,
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE meal_items (
id INT AUTO_INCREMENT PRIMARY KEY,
meal_id INT NOT NULL,
food_id INT NOT NULL,
quantity_grams INT NOT NULL CHECK (quantity_grams > 0),
FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE,
FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE RESTRICT
);


CREATE TABLE consumptions (
id INT AUTO_INCREMENT PRIMARY KEY,
meal_id INT NOT NULL,
servings DECIMAL(6,2) NOT NULL DEFAULT 1.0,
consumed_at DATETIME NOT NULL,
notes VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE
);


-- Beispiel-Daten
INSERT INTO foods (name, image_url, description, calories_per_100g) VALUES
('Haferflocken', 'https://images.unsplash.com/photo-1517686469429-8bdb88b9f907', 'Vollkorn-Haferflocken', 372),
('Banane', 'https://images.unsplash.com/photo-1571772805064-207c8435df79', 'Reife Banane', 89),
('Magerquark', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9a', 'Proteinreicher Quark, 0.2% Fett', 68);
-- Ende schema.sql