<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!verify_csrf($_POST['csrf_token'] ?? '')) {
		header('HTTP/1.1 400 Bad Request');
		echo 'Invalid request';
		exit;
	}
	$manga_id = (int)($_POST['manga_id'] ?? 0);
	if ($manga_id > 0) {
		$state = toggle_bookmark(current_user_id(), $manga_id);
	}
}
$ref = $_SERVER['HTTP_REFERER'] ?? AppConfig::baseUrl();
header('Location: ' . $ref);
exit;