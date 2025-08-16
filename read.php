<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Read - CrypyedManga';

$chapter_id = null;
if (isset($_GET['id'])) {
	$chapter_id = (int)$_GET['id'];
} else {
	$path = $_SERVER['REQUEST_URI'] ?? '';
	if (preg_match('~/(?:read)/[^/]+/chapter-[^/]+-(\d+)~', $path, $m)) {
		$chapter_id = (int)$m[1];
	}
}

if (!$chapter_id) {
	header('Location: ' . AppConfig::baseUrl());
	exit;
}

$chapter = fetch_chapter($chapter_id);
if (!$chapter) {
	header('HTTP/1.1 404 Not Found');
	echo 'Chapter not found';
	exit;
}
$manga = fetch_manga((int)$chapter['manga_id']);
$images = fetch_chapter_images($chapter['id']);

// Next/Prev
$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT id FROM chapters WHERE manga_id = :mid AND chapter_number < :num ORDER BY chapter_number DESC LIMIT 1');
$stmt->execute([':mid' => $manga['id'], ':num' => $chapter['chapter_number']]);
$prev_id = $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT id FROM chapters WHERE manga_id = :mid AND chapter_number > :num ORDER BY chapter_number ASC LIMIT 1');
$stmt->execute([':mid' => $manga['id'], ':num' => $chapter['chapter_number']]);
$next_id = $stmt->fetchColumn();

if (current_user_id()) {
	record_reading_history(current_user_id(), (int)$manga['id'], (int)$chapter['id']);
}

$page_title = htmlspecialchars($manga['title']) . ' — Chapter ' . htmlspecialchars($chapter['chapter_number']) . ' - CrypyedManga';
include __DIR__ . '/includes/header.php';
?>
<div class="reader-container">
	<div class="reader-toolbar d-flex justify-content-between align-items-center mb-3">
		<div>
			<a class="btn btn-sm btn-outline-light" href="<?php echo build_manga_url($manga); ?>">Back to manga</a>
		</div>
		<div>
			<button class="btn btn-sm btn-primary" data-toggle-theme>Light/Dark</button>
		</div>
		<div>
			<?php if ($prev_id): ?>
				<a class="btn btn-sm btn-outline-light" href="<?php echo AppConfig::baseUrl() . 'read/' . ($manga['slug'] ?? slugify($manga['title'])) . '-' . $manga['id'] . '/chapter-' . $chapter['chapter_number'] . '-' . $prev_id; ?>">Prev</a>
			<?php endif; ?>
			<?php if ($next_id): ?>
				<a class="btn btn-sm btn-outline-light" href="<?php echo AppConfig::baseUrl() . 'read/' . ($manga['slug'] ?? slugify($manga['title'])) . '-' . $manga['id'] . '/chapter-' . $chapter['chapter_number'] . '-' . $next_id; ?>">Next</a>
			<?php endif; ?>
		</div>
	</div>
	<?php foreach ($images as $img): ?>
		<img class="reader-img" src="<?php echo AppConfig::baseUrl() . 'image.php?src=' . urlencode($img['image_path']); ?>" alt="Page <?php echo (int)$img['page_number']; ?>">
	<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>