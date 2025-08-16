<?php

// Base URL of the app (with trailing slash)
// Example: http://localhost/
const BASE_URL = 'http://localhost/';

// Database credentials
const DB_HOST = '127.0.0.1';
const DB_NAME = 'crypyedmanga';
const DB_USER = 'root';
const DB_PASS = '';

// Email configuration (used for contact notifications)
const EMAIL_FROM = 'no-reply@crypyedmanga.local';

// Paths
// Absolute filesystem path to the uploads directory
// Adjust if your environment uses a different root path
const UPLOADS_DIR = __DIR__ . '/../uploads/mangas/';

// Language to fetch from MangaDex by default
const DEFAULT_LANGUAGE = 'en';

// Optional environment overrides
function env(string $key, $default = null) {
	$value = getenv($key);
	return $value !== false ? $value : $default;
}

// Compute effective config with env overrides
class AppConfig {
	public static function baseUrl(): string { return env('CRYPYEDMANGA_BASE_URL', BASE_URL); }
	public static function dbHost(): string { return env('CRYPYEDMANGA_DB_HOST', DB_HOST); }
	public static function dbName(): string { return env('CRYPYEDMANGA_DB_NAME', DB_NAME); }
	public static function dbUser(): string { return env('CRYPYEDMANGA_DB_USER', DB_USER); }
	public static function dbPass(): string { return env('CRYPYEDMANGA_DB_PASS', DB_PASS); }
	public static function emailFrom(): string { return env('CRYPYEDMANGA_EMAIL_FROM', EMAIL_FROM); }
	public static function uploadsDir(): string { return UPLOADS_DIR; }
	public static function defaultLanguage(): string { return env('CRYPYEDMANGA_DEFAULT_LANG', DEFAULT_LANGUAGE); }
}