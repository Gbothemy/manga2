<?php
require_once __DIR__ . '/../_auth.php';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Admin - CrypyedManga</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo AppConfig::baseUrl(); ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
	<div class="container">
		<a class="navbar-brand" href="dashboard.php">Admin</a>
		<div class="ms-auto">
			<a class="btn btn-sm btn-outline-light" href="<?php echo AppConfig::baseUrl(); ?>">View Site</a>
		</div>
	</div>
</nav>
<div style="height:56px"></div>
<div class="container mt-3">