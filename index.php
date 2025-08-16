<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Home - CrypyedManga';
$featured = fetch_featured_mangas(5);
$latest = fetch_latest_updates(12);
include __DIR__ . '/includes/header.php';
?>
<div id="featuredCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
	<div class="carousel-inner">
		<?php foreach ($featured as $i => $m): ?>
			<div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
				<img src="<?php echo AppConfig::baseUrl() . 'image.php?src=' . urlencode($m['cover_image']); ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($m['title']); ?>">
				<div class="carousel-caption d-none d-md-block">
					<h5><?php echo htmlspecialchars($m['title']); ?></h5>
					<p><?php echo htmlspecialchars(mb_strimwidth($m['description'] ?? '', 0, 150, '…')); ?></p>
					<a href="<?php echo build_manga_url($m); ?>" class="btn btn-primary">Read</a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<button class="carousel-control-prev" type="button" data-bs-target="#featuredCarousel" data-bs-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Previous</span>
	</button>
	<button class="carousel-control-next" type="button" data-bs-target="#featuredCarousel" data-bs-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="visually-hidden">Next</span>
	</button>
</div>

<h3 class="mb-3">Latest Updates</h3>
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
	<?php foreach ($latest as $m): ?>
		<div class="col">
			<div class="card h-100">
				<img src="<?php echo AppConfig::baseUrl() . 'image.php?src=' . urlencode($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
				<div class="card-body">
					<h6 class="card-title text-truncate"><?php echo htmlspecialchars($m['title']); ?></h6>
					<p class="card-text small text-truncate"><?php echo htmlspecialchars(mb_strimwidth($m['description'] ?? '', 0, 80, '…')); ?></p>
					<a href="<?php echo build_manga_url($m); ?>" class="btn btn-sm btn-primary w-100">Open</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>