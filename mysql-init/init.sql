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

INSERT INTO videos (title, youtube_id, list_id, added_at) VALUES
-- Alice's lists
('Video A1', 'dQw4w9WgXcQ', 1, '2025-05-02 08:15:23'),
('Video A2', 'dQw4w9WgXcQ', 1, '2025-05-02 10:32:11'),
('Video A3', 'dQw4w9WgXcQ', 1, '2025-05-02 14:45:50'),
('Video A4', 'dQw4w9WgXcQ', 2, '2025-05-03 09:12:40'),
('Video A5', 'dQw4w9WgXcQ', 2, '2025-05-03 11:37:22'),
('Video A6', 'dQw4w9WgXcQ', 2, '2025-05-03 16:05:31'),
('Video A7', 'dQw4w9WgXcQ', 3, '2025-05-04 08:59:48'),
('Video A8', 'dQw4w9WgXcQ', 3, '2025-05-04 13:14:07'),
('Video A9', 'dQw4w9WgXcQ', 3, '2025-05-04 17:20:35'),

-- Bob's lists
('Video B1', 'dQw4w9WgXcQ', 4, '2025-05-04 09:43:16'),
('Video B2', 'dQw4w9WgXcQ', 4, '2025-05-04 12:05:29'),
('Video B3', 'dQw4w9WgXcQ', 4, '2025-05-04 15:58:10'),
('Video B4', 'dQw4w9WgXcQ', 5, '2025-05-05 07:23:44'),
('Video B5', 'dQw4w9WgXcQ', 5, '2025-05-05 10:47:12'),
('Video B6', 'dQw4w9WgXcQ', 5, '2025-05-05 14:30:58'),
('Video B7', 'dQw4w9WgXcQ', 6, '2025-05-05 17:01:27'),
('Video B8', 'dQw4w9WgXcQ', 6, '2025-05-05 18:45:00'),
('Video B9', 'dQw4w9WgXcQ', 6, '2025-05-05 20:12:19'),

-- Charlie's lists
('Video C1', 'dQw4w9WgXcQ', 7, '2025-05-06 07:45:00'),
('Video C2', 'dQw4w9WgXcQ', 7, '2025-05-06 08:55:32'),
('Video C3', 'dQw4w9WgXcQ', 7, '2025-05-06 10:42:19'),
('Video C4', 'dQw4w9WgXcQ', 8, '2025-05-06 12:05:47'),
('Video C5', 'dQw4w9WgXcQ', 8, '2025-05-06 13:27:03'),
('Video C6', 'dQw4w9WgXcQ', 8, '2025-05-06 14:39:11'),
('Video C7', 'dQw4w9WgXcQ', 9, '2025-05-06 15:12:00'),
('Video C8', 'dQw4w9WgXcQ', 9, '2025-05-06 15:45:20'),
('Video C9', 'dQw4w9WgXcQ', 9, '2025-05-06 16:13:55'),
('Video C10', 'dQw4w9WgXcQ', 9, '2025-05-06 17:21:33'),
('Video C11', 'dQw4w9WgXcQ', 9, '2025-05-06 18:04:40'),
('Video C12', 'dQw4w9WgXcQ', 9, '2025-05-06 19:25:18'),
('Video C13', 'dQw4w9WgXcQ', 9, '2025-05-06 20:10:00'),
('Video C14', 'dQw4w9WgXcQ', 9, '2025-05-06 21:38:49'),
('Video C15', 'dQw4w9WgXcQ', 9, '2025-05-06 22:57:27');
