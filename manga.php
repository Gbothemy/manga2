<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Manga - CrypyedManga';

// SEO-friendly slug-id pattern: /manga/slug-123
$id = null;
if (isset($_GET['id'])) {
	$id = (int)$_GET['id'];
} else {
	// Try to parse from PATH_INFO if available
	$path = $_SERVER['REQUEST_URI'] ?? '';
	if (preg_match('~/(?:manga)/[^/]*-(\d+)~', $path, $m)) {
		$id = (int)$m[1];
	}
}

if (!$id) {
	header('Location: ' . AppConfig::baseUrl());
	exit;
}

$manga = fetch_manga($id);
if (!$manga) {
	header('HTTP/1.1 404 Not Found');
	echo 'Manga not found';
	exit;
}

$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT g.id, g.name FROM genres g INNER JOIN manga_genres mg ON mg.genre_id = g.id WHERE mg.manga_id = :id ORDER BY g.name');
$stmt->execute([':id' => $manga['id']]);
$genres = $stmt->fetchAll();
$chapters = fetch_manga_chapters($manga['id']);
$is_bookmarked = current_user_id() ? is_bookmarked(current_user_id(), $manga['id']) : false;
$page_title = htmlspecialchars($manga['title']) . ' - CrypyedManga';
include __DIR__ . '/includes/header.php';
?>
<div class="row g-3">
	<div class="col-md-3">
		<img src="<?php echo AppConfig::baseUrl() . 'image.php?src=' . urlencode($manga['cover_image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($manga['title']); ?>">
	</div>
	<div class="col-md-9">
		<h2><?php echo htmlspecialchars($manga['title']); ?></h2>
		<div class="mb-2">
			<?php foreach ($genres as $g): ?>
				<span class="badge me-1"><?php echo htmlspecialchars($g['name']); ?></span>
			<?php endforeach; ?>
		</div>
		<p><?php echo nl2br(htmlspecialchars($manga['description'] ?? '')); ?></p>
		<div class="text-muted small mb-3">Author: <?php echo htmlspecialchars($manga['author'] ?? 'Unknown'); ?> · Released: <?php echo htmlspecialchars($manga['release_date'] ?? 'N/A'); ?></div>
		<?php if (current_user_id()): ?>
			<form method="post" action="bookmark_toggle.php" class="d-inline">
				<input type="hidden" name="manga_id" value="<?php echo (int)$manga['id']; ?>">
				<input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
				<button class="btn btn-sm btn-<?php echo $is_bookmarked ? 'secondary' : 'primary'; ?>" type="submit"><?php echo $is_bookmarked ? 'Unbookmark' : 'Bookmark'; ?></button>
			</form>
		<?php endif; ?>
	</div>
</div>
<div class="card mt-4">
	<div class="card-header">Chapters</div>
	<div class="list-group list-group-flush">
		<?php foreach ($chapters as $c): ?>
			<a class="list-group-item list-group-item-action" href="<?php echo build_chapter_url($manga, $c); ?>">
				Chapter <?php echo htmlspecialchars($c['chapter_number']); ?> — <?php echo htmlspecialchars($c['title'] ?? ''); ?>
				<span class="float-end text-muted small"><?php echo htmlspecialchars(date('Y-m-d', strtotime($c['created_at']))); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>