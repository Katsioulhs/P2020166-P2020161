-- no longer needed because dump.sql does this as well and inserts data

-- DROP TABLE IF EXISTS followers;
-- DROP TABLE IF EXISTS videos;
-- DROP TABLE IF EXISTS lists;
-- DROP TABLE IF EXISTS users;

-- CREATE TABLE IF NOT EXISTS users (
-- 	id INT AUTO_INCREMENT PRIMARY KEY,
-- 	name VARCHAR(100) NOT NULL,
-- 	surname VARCHAR(100) NOT NULL,
-- 	username VARCHAR(100) NOT NULL UNIQUE,
-- 	password VARCHAR(255) NOT NULL,
-- 	email VARCHAR(255) NOT NULL UNIQUE
-- );

-- CREATE TABLE IF NOT EXISTS followers (
-- 	user_id INT NOT NULL,
-- 	follows_user_id INT NOT NULL,
-- 	PRIMARY KEY (user_id, follows_user_id),
-- 	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
-- 	FOREIGN KEY (follows_user_id) REFERENCES users(id) ON DELETE CASCADE
-- );

-- CREATE TABLE IF NOT EXISTS lists (
-- 	id INT AUTO_INCREMENT PRIMARY KEY,
-- 	user_id INT NOT NULL,
-- 	title VARCHAR(255) NOT NULL,
-- 	is_public BOOLEAN NOT NULL DEFAULT FALSE,
-- 	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
-- );

-- CREATE TABLE IF NOT EXISTS videos (
-- 	id INT AUTO_INCREMENT PRIMARY KEY,
-- 	title VARCHAR(128) NOT NULL,
-- 	youtube_id VARCHAR(255) NOT NULL,
-- 	list_id INT NOT NULL,
-- 	added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
-- 	FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
-- );
