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
	youtube_id VARCHAR(255) NOT NULL,
	list_id INT NOT NULL,
	added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);

-- the following are demo data for quick testing

INSERT INTO di_internet_technologies_project.users
(id, name, surname, username, password, email) VALUES
(1, 'a', 'a', 'a', '$2y$10$vyPr.is98AArqwzvXLHSN.lFFEyPou1PTVCmo2JYVxm.RQYlkGXCe', 'a@a'), -- password a
(2, 'b', 'b', 'b', '$2y$10$TKAwF02uF86t4UpICSw2CutsWRfDUqTZn1ba56VC.I0dkql1u5oHm', 'b@b'), -- password b
(3, 'c', 'c', 'c', '$2y$10$N2fAAZfI6Vf93yW56nZoB.PhrOb23qNrI1/snKkDgmFRU8tLMEygK', 'c@c'); -- password c

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

INSERT INTO videos (title, youtube_id, list_id) VALUES
-- Alice's lists
('Video A1', 'dQw4w9WgXcQ', 1),
('Video A2', 'dQw4w9WgXcQ', 1),
('Video A3', 'dQw4w9WgXcQ', 1),
('Video A4', 'dQw4w9WgXcQ', 2),
('Video A5', 'dQw4w9WgXcQ', 2),
('Video A6', 'dQw4w9WgXcQ', 2),
('Video A7', 'dQw4w9WgXcQ', 3),
('Video A8', 'dQw4w9WgXcQ', 3),
('Video A9', 'dQw4w9WgXcQ', 3),

-- Bob's lists
('Video B1', 'dQw4w9WgXcQ', 4),
('Video B2', 'dQw4w9WgXcQ', 4),
('Video B3', 'dQw4w9WgXcQ', 4),
('Video B4', 'dQw4w9WgXcQ', 5),
('Video B5', 'dQw4w9WgXcQ', 5),
('Video B6', 'dQw4w9WgXcQ', 5),
('Video B7', 'dQw4w9WgXcQ', 6),
('Video B8', 'dQw4w9WgXcQ', 6),
('Video B9', 'dQw4w9WgXcQ', 6),

-- Charlie's lists
('Video C1', 'dQw4w9WgXcQ', 7),
('Video C2', 'dQw4w9WgXcQ', 7),
('Video C3', 'dQw4w9WgXcQ', 7),
('Video C4', 'dQw4w9WgXcQ', 8),
('Video C5', 'dQw4w9WgXcQ', 8),
('Video C6', 'dQw4w9WgXcQ', 8),
('Video C7', 'dQw4w9WgXcQ', 9),
('Video C8', 'dQw4w9WgXcQ', 9),
('Video C9', 'dQw4w9WgXcQ', 9),
('Video C10', 'dQw4w9WgXcQ', 9),
('Video C11', 'dQw4w9WgXcQ', 9),
('Video C12', 'dQw4w9WgXcQ', 9),
('Video C13', 'dQw4w9WgXcQ', 9),
('Video C14', 'dQw4w9WgXcQ', 9),
('Video C15', 'dQw4w9WgXcQ', 9);
