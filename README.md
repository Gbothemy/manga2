# CrypyedManga

CrypyedManga is a simple manga reading website built with PHP, MySQL, HTML, CSS, and Bootstrap 5. It includes a public site for readers and an admin panel for managing mangas, chapters, and messages.

## Features
- Home page with featured carousel and latest updates
- Manga list page with search, filter by genre, and sort options (Latest, Popular, A–Z)
- Manga detail page with chapter list
- Chapter reading page with vertical image scroll, next/prev navigation, and light/dark mode
- About and Contact pages (contact stores messages and sends email)
- User registration/login, bookmarks, and reading history
- Admin panel to manage mangas, chapters (multiple images), genres, authors, and contact messages (reply)
- MangaDex API retriever script to auto import/update mangas and chapters
- SEO-friendly URLs via .htaccess
- Fully responsive Bootstrap 5 theme with anime/tech aesthetic

## Requirements
- PHP 8.0+
- MySQL 5.7+/MariaDB 10.3+
- Web server with mod_rewrite (Apache) or equivalent

## Setup
1. Create a MySQL database.
2. Import the SQL file:
   - `database.sql` contains schema and sample data (5 mangas, each with 3 chapters and images).
3. Configure the app:
   - Copy `includes/config.example.php` to `includes/config.php` and set your values.
4. Ensure the following directories are writable by the web server:
   - `uploads/mangas`
5. Configure your web server document root to the project root and enable `.htaccess` rewrites.

## Admin Login
- URL: `/admin/`
- Default admin user is created by the SQL dump.
  - Email: `admin@crypyedmanga.local`
  - Password: `admin123` (change after first login)

## MangaDex Retriever
- Script: `manga_retriever.php`
- Modes:
  - API: Pulls manga, cover, genres, authors, chapters, and chapter images (English)
  - Local: Accepts a ZIP upload via Admin → Upload; extracts images, updates DB
- Cron (daily):
  - Example: `0 2 * * * /usr/bin/php /var/www/html/manga_retriever.php --mode=api --lang=en >> /var/log/crypyedmanga_retriever.log 2>&1`

## Environment Variables (optional)
If you prefer env vars (override config.php):
- `CRYPYEDMANGA_BASE_URL`
- `CRYPYEDMANGA_DB_HOST`
- `CRYPYEDMANGA_DB_NAME`
- `CRYPYEDMANGA_DB_USER`
- `CRYPYEDMANGA_DB_PASS`
- `CRYPYEDMANGA_EMAIL_FROM`

## Security Notes
- Change the default admin password immediately after setup.
- Keep `uploads/` non-executable via server config if possible.

## Development
- Lint PHP: `find . -name "*.php" -not -path "*/vendor/*" -exec php -l {} \;`
- Bootstrap and custom assets are in `assets/`.

## License
MIT