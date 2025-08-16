<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_login();
$page_title = 'Bookmarks - CrypyedManga';
$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT m.* FROM bookmarks b INNER JOIN mangas m ON m.id = b.manga_id WHERE b.user_id = :uid ORDER BY b.created_at DESC');
$stmt->execute([':uid' => current_user_id()]);
$items = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<h3>Bookmarks</h3>
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
	<?php foreach ($items as $m): ?>
		<div class="col">
			<div class="card h-100">
				<img src="<?php echo AppConfig::baseUrl() . htmlspecialchars($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
				<div class="card-body">
					<h6 class="card-title text-truncate"><?php echo htmlspecialchars($m['title']); ?></h6>
					<a href="<?php echo build_manga_url($m); ?>" class="btn btn-sm btn-primary w-100">Open</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>