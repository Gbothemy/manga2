<?php
require_once __DIR__ . '/../includes/functions.php';
if (!is_admin()) {
	header('Location: index.php');
	exit;
}