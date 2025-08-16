<?php
require_once __DIR__ . '/includes/functions.php';
$page_title = 'Manga List - CrypyedManga';
$q = trim($_GET['q'] ?? '');
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : null;
$sort = $_GET['sort'] ?? 'latest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$genres = fetch_genres();
$result = fetch_mangas(['q' => $q, 'genre_id' => $genre_id], $page, 24, $sort);
$items = $result['items'];
$pagination = $result['pagination'];
include __DIR__ . '/includes/header.php';
?>
<div class="card mb-3">
	<div class="card-body">
		<form class="row g-2 align-items-end" method="get" action="<?php echo AppConfig::baseUrl(); ?>list">
			<div class="col-md-4">
				<label class="form-label">Search</label>
				<input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($q); ?>">
			</div>
			<div class="col-md-3">
				<label class="form-label">Genre</label>
				<select name="genre_id" class="form-select">
					<option value="">All Genres</option>
					<?php foreach ($genres as $g): ?>
						<option value="<?php echo (int)$g['id']; ?>" <?php echo ($genre_id === (int)$g['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($g['name']); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-3">
				<label class="form-label">Sort By</label>
				<select name="sort" class="form-select">
					<option value="latest" <?php echo $sort==='latest'?'selected':''; ?>>Latest</option>
					<option value="popular" <?php echo $sort==='popular'?'selected':''; ?>>Most Popular</option>
					<option value="az" <?php echo $sort==='az'?'selected':''; ?>>A–Z</option>
				</select>
			</div>
			<div class="col-md-2">
				<button class="btn btn-primary w-100" type="submit">Apply</button>
			</div>
		</form>
	</div>
</div>
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
	<?php foreach ($items as $m): ?>
		<div class="col">
			<div class="card h-100">
				<img src="<?php echo AppConfig::baseUrl() . 'image.php?src=' . urlencode($m['cover_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($m['title']); ?>">
				<div class="card-body">
					<h6 class="card-title text-truncate"><?php echo htmlspecialchars($m['title']); ?></h6>
					<a href="<?php echo build_manga_url($m); ?>" class="btn btn-sm btn-primary w-100">Open</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
<?php if ($pagination['total_pages'] > 1): ?>
<nav class="mt-3">
	<ul class="pagination justify-content-center">
		<?php for ($i=1; $i <= $pagination['total_pages']; $i++): ?>
			<li class="page-item <?php echo $i === $pagination['page'] ? 'active' : ''; ?>">
				<a class="page-link" href="<?php echo AppConfig::baseUrl(); ?>list?<?php echo http_build_query(['q'=>$q,'genre_id'=>$genre_id,'sort'=>$sort,'page'=>$i]); ?>"><?php echo $i; ?></a>
			</li>
		<?php endfor; ?>
	</ul>
</nav>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>