CREATE DATABASE food_ai;
USE food_ai;
-- USERS
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
age INT,
weight FLOAT,
calories INT
);
-- ALLERGIES
CREATE TABLE allergies (
1
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100)
);
-- PREFERENCES
CREATE TABLE preferences_alimentaires (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100)
);
-- USER ↔ ALLERGIES
CREATE TABLE user_allergies (
user_id INT,
allergy_id INT,
PRIMARY KEY (user_id, allergy_id),
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (allergy_id) REFERENCES allergies(id) ON DELETE CASCADE
);
-- USER ↔ PREFERENCES
CREATE TABLE user_preferences (
user_id INT,
preference_id INT,
PRIMARY KEY (user_id, preference_id),
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (preference_id) REFERENCES preferences_alimentaires(id) ON
DELETE CASCADE
);
-- PRODUCTS
CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
category VARCHAR(100),
calories INT,
ingredients TEXT
);
