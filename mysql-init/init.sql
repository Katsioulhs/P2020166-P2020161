DROP TABLE IF EXISTS followers;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS lists;
DROP TABLE IF EXISTS users;

CREATE TABLE IF NOT EXISTS users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL,
	surname VARCHAR(100) NOT NULL,
	username VARCHAR(100) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	email VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS followers (
	user_id INT NOT NULL,
	follows_user_id INT NOT NULL,
	PRIMARY KEY (user_id, follows_user_id),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (follows_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS lists (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	title VARCHAR(255) NOT NULL,
	is_public BOOLEAN NOT NULL DEFAULT FALSE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS videos (
	id INT AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(128) NOT NULL,
	url VARCHAR(255) NOT NULL,
	list_id INT NOT NULL,
	added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);

-- the following are demo data for quick testing

INSERT INTO lists (user_id, title, is_public) VALUES
(1, 'Alice\'s Favorites', TRUE),
(1, 'Alice\'s Watch Later', FALSE),
(1, 'Alice\'s Hidden Gems', TRUE),
(2, 'Bob\'s Picks', TRUE),
(2, 'Bob\'s Study List', FALSE),
(2, 'Bob\'s Music Videos', TRUE),
(3, 'Charlie\'s Chill', TRUE),
(3, 'Charlie\'s Learn', FALSE),
(3, 'Charlie\'s Random', TRUE);

INSERT INTO videos (title, url, list_id) VALUES
-- Alice's lists
('Video A1', 'https://example.com/a1', 1),
('Video A2', 'https://example.com/a2', 1),
('Video A3', 'https://example.com/a3', 1),
('Video A4', 'https://example.com/a4', 2),
('Video A5', 'https://example.com/a5', 2),
('Video A6', 'https://example.com/a6', 2),
('Video A7', 'https://example.com/a7', 3),
('Video A8', 'https://example.com/a8', 3),
('Video A9', 'https://example.com/a9', 3),

-- Bob's lists
('Video B1', 'https://example.com/b1', 4),
('Video B2', 'https://example.com/b2', 4),
('Video B3', 'https://example.com/b3', 4),
('Video B4', 'https://example.com/b4', 5),
('Video B5', 'https://example.com/b5', 5),
('Video B6', 'https://example.com/b6', 5),
('Video B7', 'https://example.com/b7', 6),
('Video B8', 'https://example.com/b8', 6),
('Video B9', 'https://example.com/b9', 6),

-- Charlie's lists
('Video C1', 'https://example.com/c1', 7),
('Video C2', 'https://example.com/c2', 7),
('Video C3', 'https://example.com/c3', 7),
('Video C4', 'https://example.com/c4', 8),
('Video C5', 'https://example.com/c5', 8),
('Video C6', 'https://example.com/c6', 8),
('Video C7', 'https://example.com/c7', 9),
('Video C8', 'https://example.com/c8', 9),
('Video C9', 'https://example.com/c9', 9);
