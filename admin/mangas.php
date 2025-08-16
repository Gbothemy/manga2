<?php
require_once __DIR__ . '/partials/header.php';
$pdo = get_db_connection();

// Delete
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	$pdo->prepare('DELETE FROM mangas WHERE id = :id')->execute([':id' => $id]);
	echo '<div class="alert alert-success">Manga deleted.</div>';
}

// Create or Update
$editing = null;
if (isset($_GET['edit'])) {
	$id = (int)$_GET['edit'];
	$stmt = $pdo->prepare('SELECT * FROM mangas WHERE id = :id');
	$stmt->execute([':id' => $id]);
	$editing = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = trim($_POST['title'] ?? '');
	$author = trim($_POST['author'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$release_date = $_POST['release_date'] ?? null;
	$is_featured = isset($_POST['is_featured']) ? 1 : 0;
	$slug = slugify($title);
	$cover_path = $editing['cover_image'] ?? 'assets/images/mangas/placeholder_cover.jpg';

	if (!empty($_FILES['cover']['name'])) {
		$ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
		$fname = 'uploads/mangas/cover_' . time() . '_' . rand(1000,9999) . '.' . $ext;
		$abs = __DIR__ . '/../' . $fname;
		if (is_uploaded_file($_FILES['cover']['tmp_name'])) {
			@mkdir(dirname($abs), 0777, true);
			move_uploaded_file($_FILES['cover']['tmp_name'], $abs);
			$cover_path = $fname;
		}
	}

	if ($editing) {
		$stmt = $pdo->prepare('UPDATE mangas SET title=:t, slug=:s, author=:a, description=:d, release_date=:r, is_featured=:f, cover_image=:c WHERE id=:id');
		$stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$author, ':d'=>$description, ':r'=>$release_date, ':f'=>$is_featured, ':c'=>$cover_path, ':id'=>$editing['id']]);
		echo '<div class="alert alert-success">Manga updated.</div>';
	} else {
		$stmt = $pdo->prepare('INSERT INTO mangas (title, slug, author, description, release_date, is_featured, cover_image, created_at) VALUES (:t,:s,:a,:d,:r,:f,:c,NOW())');
		$stmt->execute([':t'=>$title, ':s'=>$slug, ':a'=>$author, ':d'=>$description, ':r'=>$release_date, ':f'=>$is_featured, ':c'=>$cover_path]);
		echo '<div class="alert alert-success">Manga created.</div>';
	}
}

// List
$stmt = $pdo->query('SELECT * FROM mangas ORDER BY created_at DESC');
$mangas = $stmt->fetchAll();
?>
<div class="row">
	<div class="col-md-8">
		<div class="card mb-3">
			<div class="card-header d-flex justify-content-between">Mangas <a class="btn btn-sm btn-primary" href="?new=1">New</a></div>
			<div class="list-group list-group-flush">
				<?php foreach ($mangas as $m): ?>
					<div class="list-group-item d-flex justify-content-between align-items-center">
						<div>
							<strong><?php echo htmlspecialchars($m['title']); ?></strong>
							<div class="small text-muted">#<?php echo (int)$m['id']; ?></div>
						</div>
						<div>
							<a class="btn btn-sm btn-secondary" href="?edit=<?php echo (int)$m['id']; ?>">Edit</a>
							<a class="btn btn-sm btn-danger" href="?delete=<?php echo (int)$m['id']; ?>" onclick="return confirm('Delete this manga?')">Delete</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-header"><?php echo $editing ? 'Edit Manga' : 'New Manga'; ?></div>
			<div class="card-body">
				<form method="post" enctype="multipart/form-data">
					<div class="mb-2">
						<label class="form-label">Title</label>
						<input class="form-control" name="title" value="<?php echo htmlspecialchars($editing['title'] ?? ''); ?>" required>
					</div>
					<div class="mb-2">
						<label class="form-label">Author</label>
						<input class="form-control" name="author" value="<?php echo htmlspecialchars($editing['author'] ?? ''); ?>">
					</div>
					<div class="mb-2">
						<label class="form-label">Release Date</label>
						<input type="date" class="form-control" name="release_date" value="<?php echo htmlspecialchars($editing['release_date'] ?? ''); ?>">
					</div>
					<div class="mb-2">
						<label class="form-label">Description</label>
						<textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($editing['description'] ?? ''); ?></textarea>
					</div>
					<div class="mb-2 form-check">
						<input class="form-check-input" type="checkbox" name="is_featured" <?php echo !empty($editing['is_featured']) ? 'checked' : ''; ?>>
						<label class="form-check-label">Featured</label>
					</div>
					<div class="mb-2">
						<label class="form-label">Cover Image</label>
						<input type="file" class="form-control" name="cover" accept="image/*">
					</div>
					<button class="btn btn-primary w-100" type="submit"><?php echo $editing ? 'Update' : 'Create'; ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>