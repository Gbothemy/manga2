-- CrypyedManga schema and sample data
-- Compatible with MySQL 5.7+

SET NAMES utf8mb4;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS reading_history;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS chapter_images;
DROP TABLE IF EXISTS chapters;
DROP TABLE IF EXISTS manga_genres;
DROP TABLE IF EXISTS genres;
DROP TABLE IF EXISTS mangas;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(150) NOT NULL,
	email VARCHAR(190) NOT NULL UNIQUE,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('admin','reader') NOT NULL DEFAULT 'reader',
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE mangas (
	id INT AUTO_INCREMENT PRIMARY KEY,
	external_id VARCHAR(64) NULL,
	title VARCHAR(255) NOT NULL,
	slug VARCHAR(255) NOT NULL,
	author VARCHAR(255) NULL,
	description TEXT NULL,
	release_date DATE NULL,
	is_featured TINYINT(1) NOT NULL DEFAULT 0,
	cover_image VARCHAR(512) NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	UNIQUE KEY uq_manga_slug (slug),
	KEY idx_external_id (external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE genres (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE manga_genres (
	manga_id INT NOT NULL,
	genre_id INT NOT NULL,
	PRIMARY KEY (manga_id, genre_id),
	FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
	FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE chapters (
	id INT AUTO_INCREMENT PRIMARY KEY,
	manga_id INT NOT NULL,
	external_id VARCHAR(64) NULL,
	chapter_number VARCHAR(20) NOT NULL,
	title VARCHAR(255) NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
	KEY idx_ch_manga (manga_id),
	UNIQUE KEY uq_manga_external (manga_id, external_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE chapter_images (
	id INT AUTO_INCREMENT PRIMARY KEY,
	chapter_id INT NOT NULL,
	page_number INT NOT NULL,
	image_path VARCHAR(512) NOT NULL,
	FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
	UNIQUE KEY uq_ch_page (chapter_id, page_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(150) NOT NULL,
	email VARCHAR(190) NOT NULL,
	message TEXT NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bookmarks (
	user_id INT NOT NULL,
	manga_id INT NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (user_id, manga_id),
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reading_history (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	manga_id INT NOT NULL,
	chapter_id INT NOT NULL,
	read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (manga_id) REFERENCES mangas(id) ON DELETE CASCADE,
	FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
	KEY idx_history_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin user (password: admin123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin@crypyedmanga.local', '$2y$10$0q7z0mGZ9C9T4jQ6xCkM7uvc1zJm8RKC3Csr0o7qz5TnSxT3pQ7bO', 'admin');
-- The above hash corresponds to 'admin123' generated with PHP password_hash()

-- Genres
INSERT INTO genres (name) VALUES ('Action'),('Adventure'),('Comedy'),('Drama'),('Fantasy'),('Romance'),('Sci-Fi');

-- Sample mangas
INSERT INTO mangas (title, slug, author, description, release_date, is_featured, cover_image) VALUES
('Neon Blade', 'neon-blade', 'Akira Sato', 'Cyberpunk samurai in a neon-lit city.', '2022-01-01', 1, 'assets/images/mangas/placeholder_cover.jpg'),
('Starbound Odyssey', 'starbound-odyssey', 'Luna Takahashi', 'A crew travels the galaxies seeking lost relics.', '2021-05-14', 1, 'assets/images/mangas/placeholder_cover.jpg'),
('Spirit Chronicle', 'spirit-chronicle', 'Hiro Tanaka', 'Spirits and humans coexist with ancient contracts.', '2020-10-10', 0, 'assets/images/mangas/placeholder_cover.jpg'),
('Arcane Academy', 'arcane-academy', 'Mira Hoshino', 'Students learn magic in a high-tech academy.', '2023-02-20', 1, 'assets/images/mangas/placeholder_cover.jpg'),
('Clover Days', 'clover-days', 'Nao Suzuki', 'A slice-of-life romance in spring.', '2019-03-07', 0, 'assets/images/mangas/placeholder_cover.jpg');

-- Link genres to mangas
INSERT INTO manga_genres (manga_id, genre_id) VALUES
(1,1),(1,7),
(2,2),(2,7),
(3,4),(3,5),
(4,5),(4,7),
(5,3),(5,6);

-- Chapters (3 each)
INSERT INTO chapters (manga_id, chapter_number, title) VALUES
(1,'1','First Light'),(1,'2','Blade Runner'),(1,'3','Neon Duel'),
(2,'1','Launch'),(2,'2','Drift'),(2,'3','Starlight'),
(3,'1','Whisper'),(3,'2','Pact'),(3,'3','Echo'),
(4,'1','Awakening'),(4,'2','Trial'),(4,'3','Summon'),
(5,'1','Spring'),(5,'2','Rain'),(5,'3','Sun');

-- Chapter images (3 per chapter -> placeholder)
-- We will use placeholder image paths; ensure files exist under uploads/mangas/
INSERT INTO chapter_images (chapter_id, page_number, image_path) VALUES
(1,1,'uploads/mangas/1/1/001.jpg'),(1,2,'uploads/mangas/1/1/002.jpg'),(1,3,'uploads/mangas/1/1/003.jpg'),
(2,1,'uploads/mangas/1/2/001.jpg'),(2,2,'uploads/mangas/1/2/002.jpg'),(2,3,'uploads/mangas/1/2/003.jpg'),
(3,1,'uploads/mangas/1/3/001.jpg'),(3,2,'uploads/mangas/1/3/002.jpg'),(3,3,'uploads/mangas/1/3/003.jpg'),
(4,1,'uploads/mangas/2/4/001.jpg'),(4,2,'uploads/mangas/2/4/002.jpg'),(4,3,'uploads/mangas/2/4/003.jpg'),
(5,1,'uploads/mangas/2/5/001.jpg'),(5,2,'uploads/mangas/2/5/002.jpg'),(5,3,'uploads/mangas/2/5/003.jpg'),
(6,1,'uploads/mangas/2/6/001.jpg'),(6,2,'uploads/mangas/2/6/002.jpg'),(6,3,'uploads/mangas/2/6/003.jpg'),
(7,1,'uploads/mangas/3/7/001.jpg'),(7,2,'uploads/mangas/3/7/002.jpg'),(7,3,'uploads/mangas/3/7/003.jpg'),
(8,1,'uploads/mangas/3/8/001.jpg'),(8,2,'uploads/mangas/3/8/002.jpg'),(8,3,'uploads/mangas/3/8/003.jpg'),
(9,1,'uploads/mangas/3/9/001.jpg'),(9,2,'uploads/mangas/3/9/002.jpg'),(9,3,'uploads/mangas/3/9/003.jpg'),
(10,1,'uploads/mangas/4/10/001.jpg'),(10,2,'uploads/mangas/4/10/002.jpg'),(10,3,'uploads/mangas/4/10/003.jpg'),
(11,1,'uploads/mangas/4/11/001.jpg'),(11,2,'uploads/mangas/4/11/002.jpg'),(11,3,'uploads/mangas/4/11/003.jpg'),
(12,1,'uploads/mangas/4/12/001.jpg'),(12,2,'uploads/mangas/4/12/002.jpg'),(12,3,'uploads/mangas/4/12/003.jpg'),
(13,1,'uploads/mangas/5/13/001.jpg'),(13,2,'uploads/mangas/5/13/002.jpg'),(13,3,'uploads/mangas/5/13/003.jpg'),
(14,1,'uploads/mangas/5/14/001.jpg'),(14,2,'uploads/mangas/5/14/002.jpg'),(14,3,'uploads/mangas/5/14/003.jpg'),
(15,1,'uploads/mangas/5/15/001.jpg'),(15,2,'uploads/mangas/5/15/002.jpg'),(15,3,'uploads/mangas/5/15/003.jpg');