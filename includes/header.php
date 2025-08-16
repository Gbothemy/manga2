<?php
require_once __DIR__ . '/functions.php';
$page_title = $page_title ?? 'CrypyedManga';
$genres_nav = fetch_genres();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo htmlspecialchars($page_title); ?></title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo AppConfig::baseUrl(); ?>assets/css/style.css" rel="stylesheet">
	<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
	<meta name="description" content="Read manga online with a sleek dark theme reader.">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
	<div class="container">
		<a class="navbar-brand" href="<?php echo AppConfig::baseUrl(); ?>">CrypyedManga</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navMain">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item"><a class="nav-link" href="<?php echo AppConfig::baseUrl(); ?>list">Manga List</a></li>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Genres</a>
					<ul class="dropdown-menu dropdown-menu-dark">
						<?php foreach ($genres_nav as $g): ?>
							<li><a class="dropdown-item" href="<?php echo AppConfig::baseUrl(); ?>list?genre_id=<?php echo (int)$g['id']; ?>"><?php echo htmlspecialchars($g['name']); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</li>
				<li class="nav-item"><a class="nav-link" href="<?php echo AppConfig::baseUrl(); ?>about">About</a></li>
				<li class="nav-item"><a class="nav-link" href="<?php echo AppConfig::baseUrl(); ?>contact">Contact</a></li>
			</ul>
			<form class="d-flex me-2" role="search" action="<?php echo AppConfig::baseUrl(); ?>list" method="get">
				<input class="form-control me-2" type="search" placeholder="Search" name="q" aria-label="Search" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
				<button class="btn btn-outline-light" type="submit">Search</button>
			</form>
			<button class="btn btn-primary me-2" type="button" data-toggle-theme>Light/Dark</button>
			<?php if (current_user_id()): ?>
				<div class="dropdown">
					<button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></button>
					<ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
						<li><a class="dropdown-item" href="<?php echo AppConfig::baseUrl(); ?>bookmarks.php">Bookmarks</a></li>
						<li><a class="dropdown-item" href="<?php echo AppConfig::baseUrl(); ?>logout.php">Logout</a></li>
					</ul>
				</div>
			<?php else: ?>
				<a class="btn btn-outline-light me-2" href="<?php echo AppConfig::baseUrl(); ?>login">Login</a>
				<a class="btn btn-light" href="<?php echo AppConfig::baseUrl(); ?>register">Register</a>
			<?php endif; ?>
		</div>
	</div>
</nav>
<div style="height:56px"></div>
<div class="container mt-3">